<?php

namespace Rtcl\Services\Importers;

use WP_Error;

/**
 * Contract every external-source importer implements.
 *
 * Implementations (RSS, GooglePlaces, …) translate their native payload into
 * the NormalizedRow array shape consumed by ListingIngester::ingest_normalized.
 *
 * NormalizedRow shape (associative array):
 *   source        string  Stable source key, e.g. 'rss', 'google_places'.
 *   source_id     string  Stable per-record id (feed guid, place_id, row hash).
 *   source_url    string  Optional canonical URL for the record.
 *   title         string
 *   content       string
 *   excerpt       string
 *   status        string  'publish' | 'pending' | 'draft' | ...
 *   author_email  string
 *   categories    string[]  Hierarchy strings ("Parent > Child").
 *   locations     string[]  Hierarchy strings.
 *   tags          string[]
 *   meta          array     Listing meta keys → values (_rtcl_phone, _rtcl_bhs, …).
 *   gallery_urls  string[]  Remote image URLs to sideload.
 */
interface ImporterInterface {

	/**
	 * Stable lowercase identifier for the source (e.g. 'rss', 'google_places').
	 *
	 * Persisted into the rtcl_import_history table and the _rtcl_import_source
	 * post meta — never rename a value once shipped.
	 */
	public function get_source_key(): string;

	/**
	 * Validate a configuration array (mapping, credentials, target taxonomies, …)
	 * before a fetch is attempted.
	 *
	 * @return true|WP_Error true on success, WP_Error describing what is missing.
	 */
	public function validate_config( array $config );

	/**
	 * Pull records from the source and return them as NormalizedRow arrays.
	 *
	 * Implementations should not insert listings here; they only normalize and
	 * return. The ingester / caller handles dedupe and persistence.
	 *
	 * @param array $params Source-specific parameters (feed URL, search query, page size, …).
	 *
	 * @return array<int,array>|WP_Error Array of NormalizedRow on success.
	 */
	public function fetch( array $params );
}
