<?php

namespace Rtcl\Services\Importers;

use SimplePie_Item;
use WP_Error;

/**
 * Pulls listings from any RSS / Atom feed via WordPress's bundled SimplePie.
 *
 * The mapping from feed item → NormalizedRow is intentionally fixed (no per-field
 * mapping UI) because RSS / Atom field semantics are well-defined: title, link,
 * description, content:encoded, pubDate, dc:creator, media:content, enclosure.
 */
class RssImporter implements ImporterInterface {

	public function get_source_key(): string {
		return 'rss';
	}

	public function validate_config( array $config ) {
		$url = trim( (string) ( $config['url'] ?? '' ) );
		if ( '' === $url ) {
			return new WP_Error( 'rtcl_rss_missing_url', __( 'Feed URL is required.', 'classified-listing' ) );
		}
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return new WP_Error( 'rtcl_rss_invalid_url', __( 'Feed URL is not a valid URL.', 'classified-listing' ) );
		}
		return true;
	}

	/**
	 * Fetch and normalize a feed.
	 *
	 * @param array $params { url: string, limit?: int }
	 *
	 * @return array|WP_Error Array of NormalizedRow on success.
	 */
	public function fetch( array $params ) {
		$check = $this->validate_config( $params );
		if ( is_wp_error( $check ) ) {
			return $check;
		}

		if ( ! function_exists( 'fetch_feed' ) ) {
			require_once ABSPATH . WPINC . '/feed.php';
		}

		// Increase the default 10s SimplePie timeout to avoid cURL 28 errors
		// on slow or large feeds. The filter is removed immediately after so
		// it doesn't affect other feed fetches in the same request.
		$set_timeout = static function ( $feed ) {
			$feed->set_timeout( 30 );
		};
		add_action( 'wp_feed_options', $set_timeout );
		$feed = fetch_feed( $params['url'] );
		remove_action( 'wp_feed_options', $set_timeout );
		if ( is_wp_error( $feed ) ) {
			return $feed;
		}

		$limit = max( 0, (int) ( $params['limit'] ?? 0 ) );
		if ( $limit > 0 ) {
			$max_items = $feed->get_item_quantity( $limit );
		} else {
			$max_items = $feed->get_item_quantity();
		}

		$items = $feed->get_items( 0, $max_items );
		if ( empty( $items ) ) {
			return [];
		}

		$rows = [];
		foreach ( $items as $item ) {
			$row = $this->normalize_item( $item, $params['url'] );
			if ( $row ) {
				$rows[] = $row;
			}
		}
		return $rows;
	}

	/**
	 * Translate a single SimplePie_Item into the NormalizedRow shape.
	 *
	 * @return array|null Row, or null if the item is unusable (no title and no content).
	 */
	private function normalize_item( SimplePie_Item $item, string $feed_url ): ?array {
		$title = trim( (string) $item->get_title() );
		$body  = (string) ( $item->get_content() ?: $item->get_description() );
		$body  = trim( $body );

		if ( '' === $title && '' === $body ) {
			return null;
		}

		$link      = (string) $item->get_link();
		$guid      = (string) $item->get_id( true ); // hash-stable id
		$source_id = $guid ?: ( $link ? md5( $link ) : md5( $title . $body ) );

		$row = [
			'source'       => $this->get_source_key(),
			'source_id'    => $source_id,
			'source_url'   => $link ?: $feed_url,
			'title'        => $title ?: wp_trim_words( wp_strip_all_tags( $body ), 12, '' ),
			'content'      => $body,
			'excerpt'      => wp_trim_words( wp_strip_all_tags( $body ), 40 ),
			'status'       => '',
			'author_email' => $this->extract_author_email( $item ),
			'categories'   => $this->extract_categories( $item ),
			'locations'    => [],
			'tags'         => [],
			'meta'         => [],
			'gallery_urls' => $this->extract_images( $item, $body ),
		];

		$pub = $item->get_date( 'Y-m-d H:i:s' );
		if ( $pub ) {
			$row['meta']['_rtcl_imported_pubdate'] = $pub;
		}
		if ( $link ) {
			$row['meta']['_rtcl_website'] = $link;
		}

		return $row;
	}

	/**
	 * Extract a usable email from dc:creator / author, when present.
	 */
	private function extract_author_email( SimplePie_Item $item ): string {
		$author = $item->get_author();
		if ( ! $author ) {
			return '';
		}
		$email = (string) $author->get_email();
		return filter_var( $email, FILTER_VALIDATE_EMAIL ) ? $email : '';
	}

	/**
	 * SimplePie returns categories as an array of objects with ->term.
	 *
	 * @return string[]
	 */
	private function extract_categories( SimplePie_Item $item ): array {
		$cats = $item->get_categories();
		if ( empty( $cats ) ) {
			return [];
		}
		$out = [];
		foreach ( $cats as $c ) {
			$term = trim( (string) $c->get_term() );
			if ( '' !== $term ) {
				$out[] = $term;
			}
		}
		return $out;
	}

	/**
	 * Pull image URLs from media:content, enclosures, or the first <img> in body.
	 *
	 * @return string[]
	 */
	private function extract_images( SimplePie_Item $item, string $body ): array {
		$urls = [];

		// media:content
		$media = $item->get_enclosures();
		if ( ! empty( $media ) && is_array( $media ) ) {
			foreach ( $media as $enclosure ) {
				$link = (string) $enclosure->get_link();
				$type = (string) $enclosure->get_type();
				if ( $link && ( '' === $type || 0 === strpos( $type, 'image/' ) ) ) {
					$urls[] = $link;
				}
			}
		}

		// First <img src=""> in body as fallback
		if ( empty( $urls ) && $body ) {
			if ( preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/i', $body, $m ) ) {
				$urls[] = $m[1];
			}
		}

		return array_values( array_unique( array_filter( $urls ) ) );
	}
}
