<?php

namespace Rtcl\Services\Importers;

use Rtcl\Models\Form\Form;

/**
 * Applies a mapping spec to a NormalizedRow, producing a new NormalizedRow
 * whose slots and meta keys align with a target Form's field schema.
 *
 * The output NormalizedRow is consumed by ListingIngester::ingest_normalized
 * unchanged — the ingester does not need to know about mappings.
 */
class FieldMapper {

	/**
	 * @param array      $row          Source NormalizedRow (from importer).
	 * @param array      $mapping      source_key => target_key. Targets are
	 *                                 either FormFieldAdapter slot sentinels
	 *                                 (__title, __content, …) or a form custom
	 *                                 field's meta key. '__skip' drops.
	 * @param string     $source_type  e.g. 'google_places'. Selects the
	 *                                 FieldCatalog extractors.
	 * @param Form|null  $form         Form to stamp on the listing
	 *                                 (_rtcl_form_id). Pass null to skip.
	 *
	 * @return array Mapped NormalizedRow.
	 */
	public function apply( array $row, array $mapping, string $source_type, ?Form $form = null ): array {
		$catalog = FieldCatalog::for_source( $source_type );

		// Start as an overlay on top of the original NormalizedRow. This is the
		// key reason imports don't silently produce empty-title rows when the
		// admin's mapping omits __title (every dropdown defaults to '— Skip —'
		// in the UI, so a half-customized mapping used to produce empty output
		// and the ingester rejected every row).
		$out = [
			'source'       => $row['source']     ?? $source_type,
			'source_id'    => $row['source_id']  ?? '',
			'source_url'   => $row['source_url'] ?? '',
			'title'        => (string) ( $row['title']   ?? '' ),
			'content'      => (string) ( $row['content'] ?? '' ),
			'excerpt'      => (string) ( $row['excerpt'] ?? '' ),
			'status'       => $row['status']       ?? '',
			'author_email' => $row['author_email'] ?? '',
			'categories'   => isset( $row['categories'] )   ? (array) $row['categories']   : [],
			'locations'    => isset( $row['locations'] )    ? (array) $row['locations']    : [],
			'tags'         => isset( $row['tags'] )         ? (array) $row['tags']         : [],
			'meta'         => isset( $row['meta'] )         ? (array) $row['meta']         : [],
			'gallery_urls' => isset( $row['gallery_urls'] ) ? (array) $row['gallery_urls'] : [],
			// Carried through untouched — reviews aren't mappable fields, but the
			// ingester consumes them (imports them as listing review comments).
			'reviews'      => isset( $row['reviews'] )      ? (array) $row['reviews']      : [],
		];

		foreach ( $catalog as $source_key => $def ) {
			$target = $mapping[ $source_key ] ?? FormFieldAdapter::SKIP;
			if ( FormFieldAdapter::SKIP === $target || '' === $target ) {
				continue;
			}

			$value = ( $def['extract'] )( $row );
			if ( self::is_empty_value( $value ) ) {
				continue;
			}

			$this->assign( $out, $target, $value );
		}

		if ( $form && ! empty( $form->id ) ) {
			$out['meta']['_rtcl_form_id'] = (int) $form->id;
		}

		return $out;
	}

	/**
	 * Place a mapped value at the right place in the NormalizedRow.
	 *
	 * - Sentinel slots write to top-level keys / array slots.
	 * - Anything else is a meta key.
	 */
	private function assign( array &$out, string $target, $value ): void {
		switch ( $target ) {
			case FormFieldAdapter::SLOT_TITLE:
				$out['title'] = is_array( $value ) ? (string) reset( $value ) : (string) $value;
				break;
			case FormFieldAdapter::SLOT_CONTENT:
				$out['content'] = is_array( $value ) ? implode( "\n\n", array_map( 'strval', $value ) ) : (string) $value;
				break;
			case FormFieldAdapter::SLOT_EXCERPT:
				$out['excerpt'] = is_array( $value ) ? (string) reset( $value ) : (string) $value;
				break;
			case FormFieldAdapter::SLOT_CATEGORIES:
				$out['categories'] = array_values( array_filter( array_map( 'strval', (array) $value ) ) );
				break;
			case FormFieldAdapter::SLOT_LOCATIONS:
				$out['locations'] = array_values( array_filter( array_map( 'strval', (array) $value ) ) );
				break;
			case FormFieldAdapter::SLOT_TAGS:
				$out['tags'] = array_values( array_filter( array_map( 'strval', (array) $value ) ) );
				break;
			case FormFieldAdapter::SLOT_GALLERY:
				$out['gallery_urls'] = array_values( array_filter( array_map( 'strval', (array) $value ) ) );
				break;
			default:
				// Treat anything else as a meta key. Lists serialize as-is so
				// repeater-style fields (e.g. weekday hours) stay structured.
				$out['meta'][ $target ] = $value;
		}
	}

	/**
	 * Identify empty values (empty string, empty array, null) so we don't
	 * overwrite existing data with blanks on update.
	 */
	private static function is_empty_value( $value ): bool {
		if ( null === $value ) {
			return true;
		}
		if ( is_string( $value ) && '' === trim( $value ) ) {
			return true;
		}
		if ( is_array( $value ) && empty( $value ) ) {
			return true;
		}
		return false;
	}
}
