<?php

namespace Rtcl\Services\Importers;

/**
 * Catalog of source fields exposed to the mapping UI per importer type.
 *
 * Each entry has:
 *   - label:    human label shown in the mapping table
 *   - kind:     'text' | 'multiline' | 'number' | 'url' | 'image' | 'taxonomy'
 *               | 'list' | 'object'  (hint for the UI + the AI suggester)
 *   - extract:  callable( NormalizedRow $row ): mixed
 *
 * Only `google_places` is wired today — RSS and CSV still use their existing
 * hard-coded normalizers (per the Phase 5 scope decision).
 */
class FieldCatalog {

	/**
	 * @return array<string,array{label:string,kind:string,extract:callable}>
	 */
	public static function for_source( string $source_type ): array {
		switch ( $source_type ) {
			case 'google_places':
				return self::google_places();
		}
		return [];
	}

	/**
	 * Convenience: same catalog without the extract closures, for serializing
	 * to the front-end and the AI prompt.
	 *
	 * @return array<int,array{key:string,label:string,kind:string}>
	 */
	public static function describe_source( string $source_type ): array {
		$cat = self::for_source( $source_type );
		$out = [];
		foreach ( $cat as $key => $def ) {
			$out[] = [
				'key'   => $key,
				'label' => $def['label'],
				'kind'  => $def['kind'],
			];
		}
		return $out;
	}

	private static function google_places(): array {
		// Extractors read from the NormalizedRow shape produced by
		// GooglePlacesImporter::normalize_place — title holds displayName,
		// editorial summary lives in content, the rest is in meta.
		return [
			'name' => [
				'label'   => __( 'Business name', 'classified-listing' ),
				'kind'    => 'text',
				'extract' => static function ( array $row ) {
					return (string) ( $row['title'] ?? '' );
				},
			],
			'editorial_summary' => [
				'label'   => __( 'Description', 'classified-listing' ),
				'kind'    => 'multiline',
				'extract' => static function ( array $row ) {
					return (string) ( $row['content'] ?? '' );
				},
			],
			'formatted_address' => [
				'label'   => __( 'Address', 'classified-listing' ),
				'kind'    => 'text',
				'extract' => static function ( array $row ) {
					return (string) ( $row['meta']['_rtcl_geo_address'] ?? '' );
				},
			],
			'phone' => [
				'label'   => __( 'Phone', 'classified-listing' ),
				'kind'    => 'text',
				'extract' => static function ( array $row ) {
					return (string) ( $row['meta']['_rtcl_phone'] ?? '' );
				},
			],
			'website' => [
				'label'   => __( 'Website', 'classified-listing' ),
				'kind'    => 'url',
				'extract' => static function ( array $row ) {
					return (string) ( $row['meta']['_rtcl_website'] ?? '' );
				},
			],
			'lat' => [
				'label'   => __( 'Latitude', 'classified-listing' ),
				'kind'    => 'number',
				'extract' => static function ( array $row ) {
					return $row['meta']['_rtcl_lat'] ?? '';
				},
			],
			'lng' => [
				'label'   => __( 'Longitude', 'classified-listing' ),
				'kind'    => 'number',
				'extract' => static function ( array $row ) {
					return $row['meta']['_rtcl_lng'] ?? '';
				},
			],
			'rating' => [
				'label'   => __( 'Google rating', 'classified-listing' ),
				'kind'    => 'number',
				'extract' => static function ( array $row ) {
					return $row['meta']['_rtcl_google_rating'] ?? '';
				},
			],
			'rating_count' => [
				'label'   => __( 'Google rating count', 'classified-listing' ),
				'kind'    => 'number',
				'extract' => static function ( array $row ) {
					return $row['meta']['_rtcl_google_rating_count'] ?? '';
				},
			],
			'types' => [
				'label'   => __( 'Categories (from Google types)', 'classified-listing' ),
				'kind'    => 'taxonomy',
				'extract' => static function ( array $row ) {
					return (array) ( $row['tags'] ?? [] );
				},
			],
			'weekday_hours' => [
				'label'   => __( 'Business hours (Google)', 'classified-listing' ),
				'kind'    => 'list',
				// Return the converted _rtcl_bhs structure (built in
				// GooglePlacesImporter::convert_google_hours_to_bhs) so mapping
				// this onto a form's Business Hours field stores the shape the
				// plugin expects, not the raw human-readable strings.
				'extract' => static function ( array $row ) {
					return (array) ( $row['meta']['_rtcl_bhs'] ?? [] );
				},
			],
			'photos' => [
				'label'   => __( 'Photos (gallery)', 'classified-listing' ),
				'kind'    => 'image',
				'extract' => static function ( array $row ) {
					return (array) ( $row['gallery_urls'] ?? [] );
				},
			],
		];
	}
}
