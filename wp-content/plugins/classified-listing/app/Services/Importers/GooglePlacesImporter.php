<?php

namespace Rtcl\Services\Importers;

use WP_Error;

/**
 * Pulls listings from Google Places API v1.
 *
 * Two-step flow (unlike RSS which is one-step):
 *   1. search() → admin preview list, no listings created. Cheap call.
 *   2. fetch( [ 'place_ids' => [...] ] ) → Place Details per id + photo URLs.
 *      Expensive — every place_id is a billed Place Details call plus any
 *      number of photo downloads, so callers must throttle (max_per_run cap).
 *
 * Dedupe key: the Google `place_id`. Stable across runs and survives
 * business renames / moves, which is exactly what we want.
 */
class GooglePlacesImporter implements ImporterInterface {

	/** @var GooglePlacesClient */
	private $client;

	public function __construct( ?GooglePlacesClient $client = null ) {
		$this->client = $client ?: new GooglePlacesClient();
	}

	public function get_source_key(): string {
		return 'google_places';
	}

	public function validate_config( array $config ) {
		// Light mode short-circuits — preview_rows is the source of truth, no place_ids needed.
		if ( ! empty( $config['light_mode'] ) ) {
			if ( empty( $config['preview_rows'] ) || ! is_array( $config['preview_rows'] ) ) {
				return new WP_Error( 'rtcl_google_no_preview_rows', __( 'Light import requires search preview data — re-run the search and pick places again.', 'classified-listing' ) );
			}
			return true;
		}
		if ( empty( $config['place_ids'] ) || ! is_array( $config['place_ids'] ) ) {
			return new WP_Error( 'rtcl_google_no_place_ids', __( 'Select at least one place to import.', 'classified-listing' ) );
		}
		return true;
	}

	/**
	 * Search step. Not part of ImporterInterface — only called by the
	 * Ajax\ImportGoogle preview endpoint, never by ImportRunner.
	 *
	 * @param string $query
	 * @param string $region
	 * @param array  $location_bias { lat, lng, radius }
	 * @param int    $limit         How many places to return (1..60).
	 *
	 * @return array|WP_Error Lightweight preview rows.
	 */
	public function search( string $query, string $region = '', array $location_bias = [], int $limit = 20 ) {
		$response = $this->client->searchTextAll( $query, $region, $location_bias, $limit );
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		$places = $response['places'] ?? [];

		$out = [];
		foreach ( $places as $place ) {
			$photo_name = ! empty( $place['photos'][0]['name'] ) ? (string) $place['photos'][0]['name'] : '';
			// Server-side resolve to the public CDN URL so the <img> tag in
			// the preview doesn't carry our API key. Falls back to '' if
			// resolution fails — UI shows an empty placeholder instead of a
			// broken referer-blocked thumbnail.
			$out[] = [
				'place_id'  => (string) ( $place['id'] ?? '' ),
				'name'      => (string) ( $place['displayName']['text'] ?? '' ),
				'address'   => (string) ( $place['formattedAddress'] ?? $place['shortFormattedAddress'] ?? '' ),
				'photo'     => $photo_name ? $this->client->resolvePhotoUri( $photo_name, 400 ) : '',
				'lat'       => $place['location']['latitude']  ?? null,
				'lng'       => $place['location']['longitude'] ?? null,
				'types'     => $place['types'] ?? [],
			];
		}
		return $out;
	}

	/**
	 * Fetch full details for an array of place_ids and normalize each.
	 *
	 * Two modes:
	 *   - Normal: hits Place Details for each place_id (one GetPlaceRequest each).
	 *     Returns full data — phone, website, hours, rating, editorial_summary,
	 *     full photo set.
	 *   - Light (params.light_mode = true, params.preview_rows = [...]): uses
	 *     ONLY the search-response data already paid for. Saves the entire
	 *     Place Details quota at the cost of leaving phone/website/hours/etc.
	 *     empty. Photos are the 400px thumbnails resolved at search time.
	 *
	 * @param array $params { place_ids: string[], max_photos?: int, light_mode?: bool, preview_rows?: array }
	 *
	 * @return array|WP_Error Array of NormalizedRow.
	 */
	public function fetch( array $params ) {
		$check = $this->validate_config( $params );
		if ( is_wp_error( $check ) ) {
			return $check;
		}

		if ( ! empty( $params['light_mode'] ) ) {
			return $this->normalize_from_preview( (array) $params['preview_rows'] );
		}

		$place_ids  = array_values( array_filter( array_map( 'strval', $params['place_ids'] ) ) );
		$max_photos = max( 1, min( 10, (int) ( $params['max_photos'] ?? 5 ) ) );
		// Reviews are opt-in (extra billing) and Google returns at most 5.
		$include_reviews = ! empty( $params['import_reviews'] );
		$max_reviews     = $include_reviews ? max( 1, min( 5, (int) ( $params['max_reviews'] ?? 5 ) ) ) : 0;

		$rows = [];
		foreach ( $place_ids as $place_id ) {
			$place = $this->client->placeDetails( $place_id, $include_reviews );
			if ( is_wp_error( $place ) ) {
				// Surface per-place failures via the row error pipeline rather
				// than aborting the whole run on one bad id.
				$rows[] = [
					'source'    => $this->get_source_key(),
					'source_id' => $place_id,
					'title'     => '',
					'_error'    => $place->get_error_message(),
				];
				continue;
			}
			$rows[] = $this->normalize_place( $place, $max_photos, $max_reviews );
		}
		return $rows;
	}

	/**
	 * Translate a Place Details response into NormalizedRow.
	 *
	 * @param array $place       Place Details response.
	 * @param int   $max_photos  Photos to sideload (1-10).
	 * @param int   $max_reviews Reviews to attach (0 = none; capped at 5 by Google).
	 */
	private function normalize_place( array $place, int $max_photos, int $max_reviews = 0 ): array {
		$lat = $place['location']['latitude']  ?? null;
		$lng = $place['location']['longitude'] ?? null;

		$row = [
			'source'       => $this->get_source_key(),
			'source_id'    => (string) ( $place['id'] ?? '' ),
			'source_url'   => (string) ( $place['websiteUri'] ?? '' ),
			'title'        => (string) ( $place['displayName']['text'] ?? '' ),
			'content'      => (string) ( $place['editorialSummary']['text'] ?? '' ),
			'excerpt'      => '',
			'status'       => '',
			'author_email' => '',
			'categories'   => [],
			'locations'    => [],
			'tags'         => array_slice( (array) ( $place['types'] ?? [] ), 0, 5 ),
			'meta'         => [],
			'gallery_urls' => [],
		];

		$address = (string) ( $place['formattedAddress'] ?? $place['shortFormattedAddress'] ?? '' );
		if ( $address ) {
			$row['meta']['_rtcl_geo_address'] = $address;
		}
		if ( null !== $lat && null !== $lng ) {
			$row['meta']['_rtcl_lat'] = (float) $lat;
			$row['meta']['_rtcl_lng'] = (float) $lng;
			// Some plugin codepaths look for a single combined value too.
			$row['meta']['_rtcl_latitude']  = (float) $lat;
			$row['meta']['_rtcl_longitude'] = (float) $lng;
		}
		if ( ! empty( $place['internationalPhoneNumber'] ) || ! empty( $place['nationalPhoneNumber'] ) ) {
			$row['meta']['_rtcl_phone'] = (string) ( $place['internationalPhoneNumber'] ?? $place['nationalPhoneNumber'] );
		}
		if ( ! empty( $place['websiteUri'] ) ) {
			$row['meta']['_rtcl_website'] = (string) $place['websiteUri'];
		}
		if ( ! empty( $place['rating'] ) ) {
			$rating = (float) $place['rating'];
			$count  = ! empty( $place['userRatingCount'] ) ? (int) $place['userRatingCount'] : 0;

			$row['meta']['_rtcl_google_rating'] = $rating;
			if ( $count > 0 ) {
				$row['meta']['_rtcl_google_rating_count'] = $count;
			}

			// Headline star value mirrors Google's overall average.
			$row['meta']['_rtcl_average_rating'] = number_format( $rating, 2, '.', '' );

			// Fallback rating distribution for when no individual reviews are
			// imported (reviews opt-out, or none returned): Google only exposes a
			// total + average, so bucket the whole count at the rounded average
			// star. When reviews ARE imported, the block below overrides this with
			// the real per-star distribution of those reviews.
			if ( $count > 0 ) {
				$star = max( 1, min( 5, (int) round( $rating ) ) );
				$row['meta']['_rtcl_rating_count'] = [ $star => $count ];
				$row['meta']['_rtcl_review_count'] = $count;
			}
		} elseif ( ! empty( $place['userRatingCount'] ) ) {
			$row['meta']['_rtcl_google_rating_count'] = (int) $place['userRatingCount'];
		}

		// Business hours: convert the Places API regularOpeningHours object
		// (structured `periods`, with a weekdayDescriptions fallback) into the
		// plugin's native _rtcl_bhs format so it renders on the listing's
		// Business Hours field without any extra mapping step.
		$bhs = $this->convert_google_hours_to_bhs( $place['regularOpeningHours'] ?? [] );
		if ( ! empty( $bhs ) ) {
			$row['meta']['_rtcl_bhs'] = $bhs;
		}

		if ( ! empty( $place['photos'] ) && is_array( $place['photos'] ) ) {
			$photos = array_slice( $place['photos'], 0, $max_photos );
			foreach ( $photos as $p ) {
				if ( ! empty( $p['name'] ) ) {
					$row['gallery_urls'][] = $this->client->photoMediaUrl( (string) $p['name'], 1600 );
				}
			}
		}

		// When reviews are imported, the ingester recomputes the listing's
		// aggregate rating (average / count / per-star breakdown) from the actual
		// review comments — see ListingIngester::import_reviews_as_comments() —
		// so the Google-aggregate fallback set above is only used when no reviews
		// are imported.
		if ( $max_reviews > 0 && ! empty( $place['reviews'] ) && is_array( $place['reviews'] ) ) {
			$row['reviews'] = $this->extract_reviews( $place['reviews'], $max_reviews );
		}

		return $row;
	}

	/**
	 * Flatten Place Details `reviews` into a simple, ingest-ready shape.
	 *
	 * Each Places API v1 review looks like:
	 *   { "name": "places/PID/reviews/RID",
	 *     "rating": 5,
	 *     "text": { "text": "...", "languageCode": "en" },
	 *     "originalText": { "text": "...", "languageCode": "en" },
	 *     "authorAttribution": { "displayName": "...", "uri": "...", "photoUri": "..." },
	 *     "publishTime": "2024-01-15T10:00:00Z",
	 *     "relativePublishTimeDescription": "2 months ago" }
	 *
	 * `name` is a stable resource id used downstream to dedupe on re-import.
	 * Reviews with no rating and no text are dropped (nothing to store).
	 *
	 * @param array $reviews     Raw `reviews` array from the API.
	 * @param int   $max_reviews Hard cap (Google itself returns at most 5).
	 *
	 * @return array<int,array> List of { review_id, author, author_url, rating, text, time }.
	 */
	private function extract_reviews( array $reviews, int $max_reviews ): array {
		$out = [];
		foreach ( $reviews as $review ) {
			if ( count( $out ) >= $max_reviews ) {
				break;
			}
			if ( ! is_array( $review ) ) {
				continue;
			}
			$rating = isset( $review['rating'] ) ? (int) $review['rating'] : 0;
			$text   = (string) ( $review['text']['text'] ?? $review['originalText']['text'] ?? '' );
			if ( $rating <= 0 && '' === trim( $text ) ) {
				continue;
			}
			$out[] = [
				'review_id'  => (string) ( $review['name'] ?? '' ),
				'author'     => (string) ( $review['authorAttribution']['displayName'] ?? '' ),
				'author_url' => (string) ( $review['authorAttribution']['uri'] ?? '' ),
				'rating'     => max( 0, min( 5, $rating ) ),
				'text'       => $text,
				'time'       => (string) ( $review['publishTime'] ?? '' ),
			];
		}

		return $out;
	}

	/**
	 * Convert a Places API `regularOpeningHours` object into the plugin's
	 * `_rtcl_bhs` meta structure.
	 *
	 * Uses the structured `periods` array (machine values) rather than the
	 * localized `weekdayDescriptions` strings, so it is locale- and
	 * format-independent. Each period looks like:
	 *
	 *   { "open":  {"day":1,"hour":9,"minute":0},
	 *     "close": {"day":1,"hour":17,"minute":0} }
	 *
	 * `day` is 0=Sunday..6=Saturday, which already matches the `_rtcl_bhs`
	 * day index. A 24/7 place returns a single period with an `open` of
	 * {day:0,hour:0,minute:0} and no `close`.
	 *
	 * Output shape:
	 *   [ 'active' => true,
	 *     'type'   => 'selective' | 247,
	 *     'days'   => [ 0 => ['open'=>false],
	 *                   1 => ['open'=>true,'times'=>[['start'=>'09:00','end'=>'17:00']]], … ] ]
	 *
	 * Falls back to parsing the localized `weekdayDescriptions` strings when
	 * `periods` is missing (some places return only the descriptions).
	 *
	 * @param array $opening_hours The `regularOpeningHours` object.
	 *
	 * @return array Empty array when there is nothing usable to convert.
	 */
	private function convert_google_hours_to_bhs( array $opening_hours ): array {
		$periods = $opening_hours['periods'] ?? [];
		if ( empty( $periods ) || ! is_array( $periods ) ) {
			// No structured periods — try the human-readable descriptions.
			return $this->parse_weekday_descriptions( (array) ( $opening_hours['weekdayDescriptions'] ?? [] ) );
		}

		// Open 24/7: a lone period with an open day/time but no close.
		if ( 1 === count( $periods ) && empty( $periods[0]['close'] ) && isset( $periods[0]['open'] ) ) {
			return [
				'active' => true,
				'type'   => 247,
			];
		}

		// Collect time ranges per weekday index (0-6).
		$times_by_day = [];
		foreach ( $periods as $period ) {
			if ( ! isset( $period['open']['day'] ) || ! isset( $period['close'] ) ) {
				continue;
			}
			$day = (int) $period['open']['day'];
			if ( $day < 0 || $day > 6 ) {
				continue;
			}
			$start = sprintf(
				'%02d:%02d',
				(int) ( $period['open']['hour'] ?? 0 ),
				(int) ( $period['open']['minute'] ?? 0 )
			);
			$end = sprintf(
				'%02d:%02d',
				(int) ( $period['close']['hour'] ?? 0 ),
				(int) ( $period['close']['minute'] ?? 0 )
			);
			$times_by_day[ $day ][] = [
				'start' => $start,
				'end'   => $end,
			];
		}

		if ( empty( $times_by_day ) ) {
			return [];
		}

		// Build all seven days; days absent from the periods are closed.
		$days = [];
		for ( $i = 0; $i <= 6; $i++ ) {
			if ( ! empty( $times_by_day[ $i ] ) ) {
				$days[ $i ] = [
					'open'  => true,
					'times' => $times_by_day[ $i ],
				];
			} else {
				$days[ $i ] = [ 'open' => false ];
			}
		}

		return [
			'active' => true,
			'type'   => 'selective',
			'days'   => $days,
		];
	}

	/**
	 * Fallback: build the `_rtcl_bhs` structure from `weekdayDescriptions`.
	 *
	 * These are localized, human-readable strings such as:
	 *   "Monday: 9:00 AM – 5:00 PM"
	 *   "Tuesday: 9:00 AM – 1:00 PM, 2:00 PM – 6:00 PM"
	 *   "Saturday: Closed"
	 *   "Sunday: Open 24 hours"
	 *
	 * Only the default (English) descriptions are reliably parseable; for other
	 * locales the day names won't match and we simply skip those lines. The
	 * structured `periods` path is always preferred upstream.
	 *
	 * @param array $descriptions weekdayDescriptions string array.
	 *
	 * @return array Empty array when nothing parseable was found.
	 */
	private function parse_weekday_descriptions( array $descriptions ): array {
		if ( empty( $descriptions ) ) {
			return [];
		}

		$day_map = [
			'sunday'    => 0,
			'monday'    => 1,
			'tuesday'   => 2,
			'wednesday' => 3,
			'thursday'  => 4,
			'friday'    => 5,
			'saturday'  => 6,
		];

		$days       = [];
		$any_parsed = false;
		foreach ( $descriptions as $line ) {
			$line = trim( (string) $line );
			if ( '' === $line || strpos( $line, ':' ) === false ) {
				continue;
			}

			list( $day_name, $rest ) = array_map( 'trim', explode( ':', $line, 2 ) );
			$day_key                 = $day_map[ strtolower( $day_name ) ] ?? null;
			if ( null === $day_key ) {
				continue;
			}
			$any_parsed = true;

			$lower = strtolower( $rest );
			if ( '' === $rest || false !== strpos( $lower, 'closed' ) ) {
				$days[ $day_key ] = [ 'open' => false ];
				continue;
			}
			if ( false !== strpos( $lower, '24 hours' ) || false !== strpos( $lower, 'open 24' ) ) {
				$days[ $day_key ] = [ 'open' => true ];
				continue;
			}

			// One or more "9:00 AM – 5:00 PM" ranges separated by commas.
			$times = [];
			foreach ( explode( ',', $rest ) as $range ) {
				// Split on any dash variant (hyphen, en/em dash) with optional spaces.
				$parts = preg_split( '/\s*[–—-]\s*/u', trim( $range ), 2 );
				if ( ! is_array( $parts ) || count( $parts ) !== 2 ) {
					continue;
				}
				$start = $this->clock_to_24h( $parts[0] );
				$end   = $this->clock_to_24h( $parts[1] );
				if ( '' !== $start && '' !== $end ) {
					$times[] = [ 'start' => $start, 'end' => $end ];
				}
			}

			$days[ $day_key ] = ! empty( $times )
				? [ 'open' => true, 'times' => $times ]
				: [ 'open' => false ];
		}

		if ( ! $any_parsed || empty( $days ) ) {
			return [];
		}

		// Fill any weekday the descriptions didn't mention as closed.
		for ( $i = 0; $i <= 6; $i++ ) {
			if ( ! isset( $days[ $i ] ) ) {
				$days[ $i ] = [ 'open' => false ];
			}
		}
		ksort( $days );

		return [
			'active' => true,
			'type'   => 'selective',
			'days'   => $days,
		];
	}

	/**
	 * Convert a clock string like "9:00 AM", "9 AM" or "12:30 PM" into a
	 * 24-hour "HH:MM" string. Returns '' when it can't be parsed.
	 *
	 * @param string $clock
	 *
	 * @return string
	 */
	private function clock_to_24h( string $clock ): string {
		// Normalize non-breaking / narrow spaces Google sometimes inserts.
		$clock = str_replace( [ "\xc2\xa0", "\xe2\x80\xaf" ], ' ', $clock );
		if ( ! preg_match( '/(\d{1,2})(?::(\d{2}))?\s*(AM|PM)?/i', trim( $clock ), $m ) ) {
			return '';
		}
		$hour   = (int) $m[1];
		$minute = isset( $m[2] ) && '' !== $m[2] ? (int) $m[2] : 0;
		$mer    = isset( $m[3] ) ? strtoupper( $m[3] ) : '';

		if ( 'PM' === $mer && $hour < 12 ) {
			$hour += 12;
		} elseif ( 'AM' === $mer && 12 === $hour ) {
			$hour = 0;
		}
		if ( $hour > 23 || $minute > 59 ) {
			return '';
		}

		return sprintf( '%02d:%02d', $hour, $minute );
	}

	/**
	 * Build NormalizedRow[] from search-time preview rows (light import path).
	 *
	 * No Google API calls. Only fields available in the Text Search response
	 * are populated: title, address, lat/lng, types (as tags), and the 400px
	 * thumbnail (already resolved at search time, so no extra Place Photos
	 * call either). Phone, website, hours, rating, editorial_summary stay
	 * empty — Place Details is the only source for those.
	 *
	 * @param array $preview_rows  Rows shaped like GooglePlacesImporter::search() output.
	 *
	 * @return array<int,array>  NormalizedRow array. Invalid rows skipped silently.
	 */
	private function normalize_from_preview( array $preview_rows ): array {
		$rows = [];
		foreach ( $preview_rows as $p ) {
			if ( ! is_array( $p ) ) {
				continue;
			}
			$place_id = (string) ( $p['place_id'] ?? '' );
			$name     = (string) ( $p['name'] ?? '' );
			if ( '' === $place_id || '' === trim( $name ) ) {
				continue;
			}

			$row = [
				'source'       => $this->get_source_key(),
				'source_id'    => $place_id,
				'source_url'   => '',
				'title'        => $name,
				'content'      => '',
				'excerpt'      => '',
				'status'       => '',
				'author_email' => '',
				'categories'   => [],
				'locations'    => [],
				'tags'         => array_slice( (array) ( $p['types'] ?? [] ), 0, 5 ),
				'meta'         => [],
				'gallery_urls' => [],
			];

			$address = (string) ( $p['address'] ?? '' );
			if ( '' !== $address ) {
				$row['meta']['_rtcl_geo_address'] = $address;
			}

			$lat = isset( $p['lat'] ) ? (float) $p['lat'] : 0.0;
			$lng = isset( $p['lng'] ) ? (float) $p['lng'] : 0.0;
			if ( $lat && $lng ) {
				$row['meta']['_rtcl_lat']       = $lat;
				$row['meta']['_rtcl_lng']       = $lng;
				$row['meta']['_rtcl_latitude']  = $lat;
				$row['meta']['_rtcl_longitude'] = $lng;
			}

			$photo = trim( (string) ( $p['photo'] ?? '' ) );
			if ( '' !== $photo ) {
				$row['gallery_urls'][] = $photo;
			}

			$rows[] = $row;
		}
		return $rows;
	}
}
