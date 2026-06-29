<?php

namespace Rtcl\Services\Importers;

use Rtcl\Helpers\Functions;
use WP_Error;

/**
 * Thin HTTP client for the new Google Places API (v1) at places.googleapis.com.
 *
 * The v1 API differs significantly from the legacy Places API:
 *   - Auth via `X-Goog-Api-Key` header (NOT a `?key=` query param for the JSON
 *     endpoints, though the photo media endpoint accepts `?key=` because it
 *     redirects to a CDN).
 *   - A required `X-Goog-FieldMask` header on every request that lists exactly
 *     which response fields you want billed/returned. Empty mask returns
 *     nothing — the API will reject the request.
 *
 * @see https://developers.google.com/maps/documentation/places/web-service/text-search
 * @see https://developers.google.com/maps/documentation/places/web-service/place-details
 * @see https://developers.google.com/maps/documentation/places/web-service/place-photos
 */
class GooglePlacesClient {

	const BASE = 'https://places.googleapis.com/v1';

	/**
	 * Field mask for the search-time preview list. Keep it minimal — broader
	 * masks (e.g. `places.regularOpeningHours`) are billed at a higher SKU.
	 * `nextPageToken` is included so the importer can paginate up to 60 results.
	 */
	const SEARCH_MASK = 'places.id,places.displayName,places.formattedAddress,places.shortFormattedAddress,places.location,places.photos,places.types,nextPageToken';

	/** Hard cap from Google: 3 pages × 20 results = 60 places per query. */
	const MAX_TOTAL_RESULTS = 60;

	/**
	 * Field mask used when fetching a single Place's full details for import.
	 */
	const DETAILS_MASK = 'id,displayName,formattedAddress,shortFormattedAddress,internationalPhoneNumber,nationalPhoneNumber,websiteUri,location,photos,regularOpeningHours,rating,userRatingCount,types,editorialSummary,businessStatus';

	private function api_key(): string {
		return (string) Functions::get_option_item( 'rtcl_import_settings', 'google_places_api_key', '' );
	}

	/**
	 * Text Search — single page. Returns up to 20 results plus an optional
	 * `nextPageToken` for pagination. Most callers should use searchTextAll
	 * which paginates internally up to the requested total.
	 *
	 * @param string $query
	 * @param string $region        ISO-3166-1 alpha-2 region code, e.g. "us".
	 * @param array  $location_bias { lat, lng, radius (m) }.
	 * @param int    $page_size     1..20, default 20.
	 * @param string $page_token    `nextPageToken` from the previous page.
	 *
	 * @return array|WP_Error Decoded response (with `places` + maybe `nextPageToken`).
	 */
	public function searchText( string $query, string $region = '', array $location_bias = [], int $page_size = 20, string $page_token = '' ) {
		$key = $this->api_key();
		if ( '' === $key ) {
			return new WP_Error( 'rtcl_google_missing_key', __( 'Google Places API key is not configured.', 'classified-listing' ) );
		}
		$query = trim( $query );
		if ( '' === $query ) {
			return new WP_Error( 'rtcl_google_missing_query', __( 'Search query is required.', 'classified-listing' ) );
		}

		$body = [
			'textQuery' => $query,
			'pageSize'  => max( 1, min( 20, $page_size ) ),
		];
		if ( '' !== $page_token ) {
			$body['pageToken'] = $page_token;
		}
		if ( $region ) {
			$body['regionCode'] = strtolower( $region );
		}
		if ( ! empty( $location_bias['lat'] ) && ! empty( $location_bias['lng'] ) ) {
			$body['locationBias'] = [
				'circle' => [
					'center' => [
						'latitude'  => (float) $location_bias['lat'],
						'longitude' => (float) $location_bias['lng'],
					],
					'radius' => max( 1.0, min( 50000.0, (float) ( $location_bias['radius'] ?? 5000 ) ) ),
				],
			];
		}

		return $this->request_json( 'POST', '/places:searchText', $body, self::SEARCH_MASK );
	}

	/**
	 * Text Search with built-in pagination up to MAX_TOTAL_RESULTS.
	 *
	 * Makes successive `searchText` calls following `nextPageToken` until the
	 * requested limit is reached, the API stops returning tokens, or we hit
	 * the Google-imposed 60-result ceiling.
	 *
	 * @return array|WP_Error  ['places' => array<int,array>]  matching the single-page shape.
	 */
	public function searchTextAll( string $query, string $region = '', array $location_bias = [], int $limit = 20 ) {
		$limit = max( 1, min( self::MAX_TOTAL_RESULTS, $limit ) );

		$places = [];
		$token  = '';
		$pages  = 0;
		// Cap defensively: limit / 20 rounded up, never more than 3.
		$max_pages = min( 3, (int) ceil( $limit / 20 ) );

		while ( count( $places ) < $limit && $pages < $max_pages ) {
			$page_size = min( 20, $limit - count( $places ) );
			$response  = $this->searchText( $query, $region, $location_bias, $page_size, $token );
			if ( is_wp_error( $response ) ) {
				// If page 1 fails, surface the error. If a later page fails,
				// keep what we already collected — better than 0 results.
				return 0 === $pages ? $response : [ 'places' => $places ];
			}

			$pages++;
			$batch  = isset( $response['places'] ) && is_array( $response['places'] ) ? $response['places'] : [];
			$places = array_merge( $places, $batch );
			$token  = (string) ( $response['nextPageToken'] ?? '' );

			if ( '' === $token || count( $places ) >= $limit ) {
				break;
			}
		}

		return [ 'places' => array_slice( $places, 0, $limit ) ];
	}

	/**
	 * Place Details for a single place_id.
	 *
	 * @param string $place_id
	 * @param bool   $include_reviews When true, append `reviews` to the field
	 *                                mask. Reviews are billed at Google's
	 *                                Enterprise + Atmosphere SKU (the priciest),
	 *                                so this is opt-in. The API returns at most 5
	 *                                reviews regardless of how many we want.
	 *
	 * @return array|WP_Error decoded place object on success.
	 */
	public function placeDetails( string $place_id, bool $include_reviews = false ) {
		$key = $this->api_key();
		if ( '' === $key ) {
			return new WP_Error( 'rtcl_google_missing_key', __( 'Google Places API key is not configured.', 'classified-listing' ) );
		}
		$place_id = trim( $place_id );
		if ( '' === $place_id ) {
			return new WP_Error( 'rtcl_google_missing_id', __( 'Missing place_id.', 'classified-listing' ) );
		}

		$field_mask = $include_reviews ? self::DETAILS_MASK . ',reviews' : self::DETAILS_MASK;

		return $this->request_json( 'GET', '/places/' . rawurlencode( $place_id ), null, $field_mask );
	}

	/**
	 * Build the photo media URL for a place photo. The endpoint 302-redirects
	 * to a CDN image; WP's HTTP layer follows redirects so this URL can be
	 * passed directly to download_url() during sideload.
	 *
	 * Note: NEVER hand this URL to the browser — it embeds the API key. If
	 * the key has HTTP-referer restrictions the browser request will 403.
	 * For preview thumbnails use resolvePhotoUri() instead.
	 *
	 * @param string $photo_name e.g. "places/PLACE_ID/photos/PHOTO_REFERENCE".
	 * @param int    $max_width  1..4800.
	 */
	public function photoMediaUrl( string $photo_name, int $max_width = 1600 ): string {
		$max_width = max( 1, min( 4800, $max_width ) );
		$key       = $this->api_key();
		return self::BASE . '/' . $photo_name . '/media?maxWidthPx=' . $max_width . '&key=' . rawurlencode( $key );
	}

	/**
	 * Resolve a photo to its public CDN URL server-side.
	 *
	 * Uses Google's `?skipHttpRedirect=true` flag, which returns a small JSON
	 * envelope containing `photoUri` (the signed CDN URL) instead of issuing
	 * the 302. The CDN URL works in the browser without needing the API key,
	 * which sidesteps referer-restricted keys and avoids leaking the key into
	 * the rendered DOM. The signed URL has a short TTL but stays valid long
	 * enough for a preview render.
	 *
	 * @return string Resolved CDN URL on success, '' on failure.
	 */
	public function resolvePhotoUri( string $photo_name, int $max_width = 400 ): string {
		$key = $this->api_key();
		if ( '' === $key || '' === $photo_name ) {
			return '';
		}
		$max_width = max( 1, min( 4800, $max_width ) );

		$url = self::BASE . '/' . $photo_name . '/media?maxWidthPx=' . $max_width . '&skipHttpRedirect=true&key=' . rawurlencode( $key );

		$response = wp_remote_get( $url, [ 'timeout' => 10 ] );
		if ( is_wp_error( $response ) ) {
			return '';
		}
		if ( (int) wp_remote_retrieve_response_code( $response ) >= 300 ) {
			return '';
		}

		$body = json_decode( (string) wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $body ) ) {
			return '';
		}
		return isset( $body['photoUri'] ) ? (string) $body['photoUri'] : '';
	}

	/**
	 * Shared request helper. Adds auth + field-mask headers, JSON-encodes body,
	 * decodes response, and converts API error envelopes into WP_Error.
	 *
	 * @param string      $method     'GET' or 'POST'.
	 * @param string      $path       Path after BASE (must start with '/').
	 * @param array|null  $body
	 * @param string      $field_mask
	 *
	 * @return array|WP_Error Decoded body on success.
	 */
	private function request_json( string $method, string $path, ?array $body, string $field_mask ) {
		$args = [
			'method'  => $method,
			'timeout' => 20,
			'headers' => [
				'Content-Type'      => 'application/json',
				'X-Goog-Api-Key'    => $this->api_key(),
				'X-Goog-FieldMask'  => $field_mask,
			],
		];
		if ( null !== $body ) {
			$args['body'] = wp_json_encode( $body );
		}

		$response = wp_remote_request( self::BASE . $path, $args );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code    = wp_remote_retrieve_response_code( $response );
		$raw     = wp_remote_retrieve_body( $response );
		$decoded = $raw ? json_decode( $raw, true ) : null;

		if ( $code < 200 || $code >= 300 ) {
			$msg = is_array( $decoded ) && isset( $decoded['error']['message'] )
				? $decoded['error']['message']
				: sprintf( 'Google Places API error (HTTP %d)', (int) $code );
			return new WP_Error( 'rtcl_google_api_error', $msg, [ 'http_code' => $code, 'body' => $decoded ] );
		}

		return is_array( $decoded ) ? $decoded : [];
	}
}
