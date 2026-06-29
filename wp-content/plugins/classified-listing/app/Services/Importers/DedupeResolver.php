<?php

namespace Rtcl\Services\Importers;

/**
 * Resolves whether a normalized row has been imported before and stamps the
 * provenance meta on a freshly-inserted listing.
 *
 * Dedupe key is the tuple (_rtcl_import_source, _rtcl_import_source_id). Both
 * meta values are written for every imported listing — never trust just one.
 */
class DedupeResolver {

	const META_SOURCE     = '_rtcl_import_source';
	const META_SOURCE_ID  = '_rtcl_import_source_id';
	const META_SOURCE_URL = '_rtcl_import_source_url';
	const META_IMPORTED   = '_rtcl_imported_at';

	/**
	 * Find a previously-imported listing for the given source identity.
	 *
	 * Restricted to rtcl_listing post type and any-status, so trashed/draft
	 * matches still dedupe.
	 *
	 * @return int|null Post ID, or null if none found.
	 */
	public function find_existing( string $source, string $source_id ): ?int {
		if ( '' === $source || '' === $source_id ) {
			return null;
		}

		$query = new \WP_Query( [
			'post_type'              => rtcl()->post_type,
			'post_status'            => 'any',
			'fields'                 => 'ids',
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'meta_query'             => [
				'relation' => 'AND',
				[
					'key'   => self::META_SOURCE,
					'value' => $source,
				],
				[
					'key'   => self::META_SOURCE_ID,
					'value' => $source_id,
				],
			],
		] );

		if ( empty( $query->posts ) ) {
			return null;
		}

		return (int) $query->posts[0];
	}

	/**
	 * Write the provenance meta onto an imported listing.
	 *
	 * Always call after a successful insert/update so the next run can dedupe.
	 */
	public function stamp( int $post_id, string $source, string $source_id, string $source_url = '' ): void {
		if ( $post_id <= 0 ) {
			return;
		}

		update_post_meta( $post_id, self::META_SOURCE, $source );
		update_post_meta( $post_id, self::META_SOURCE_ID, $source_id );
		if ( '' !== $source_url ) {
			update_post_meta( $post_id, self::META_SOURCE_URL, $source_url );
		}
		update_post_meta( $post_id, self::META_IMPORTED, current_time( 'mysql' ) );
	}
}
