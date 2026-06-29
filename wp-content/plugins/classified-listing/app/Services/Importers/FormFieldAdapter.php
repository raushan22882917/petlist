<?php

namespace Rtcl\Services\Importers;

use Rtcl\Models\Form\Form;

/**
 * Surfaces a Form's mappable target fields to the mapping UI.
 *
 * Form fields fall into three buckets:
 *   - Built-in slots that map to NormalizedRow positions (title, content,
 *     excerpt, categories, locations, gallery, tags). Exposed via the
 *     sentinel keys __title, __content, etc. — never collide with user
 *     field names which never start with `__`.
 *   - Skip-elements that aren't data inputs (buttons, captcha, section
 *     headings, terms checkbox, custom html). Filtered out.
 *   - Everything else: the field's `name` is the meta key.
 */
class FormFieldAdapter {

	/** Sentinel target keys that the FieldMapper recognizes. */
	const SLOT_TITLE      = '__title';
	const SLOT_CONTENT    = '__content';
	const SLOT_EXCERPT    = '__excerpt';
	const SLOT_CATEGORIES = '__categories';
	const SLOT_LOCATIONS  = '__locations';
	const SLOT_GALLERY    = '__gallery';
	const SLOT_TAGS       = '__tags';
	const SKIP            = '__skip';

	/** element name → sentinel slot. */
	private const BUILTIN_ELEMENTS = [
		'title'       => self::SLOT_TITLE,
		'description' => self::SLOT_CONTENT,
		'excerpt'     => self::SLOT_EXCERPT,
		'category'    => self::SLOT_CATEGORIES,
		'location'    => self::SLOT_LOCATIONS,
		'images'      => self::SLOT_GALLERY,
		'gallery'     => self::SLOT_GALLERY,
		'tags'        => self::SLOT_TAGS,
	];

	/** Elements that carry no listing data and never appear as targets. */
	private const SKIP_ELEMENTS = [
		'button',
		'recaptcha',
		'custom_html',
		'section',
		'terms_and_condition',
		'view_count',
	];

	/**
	 * @return array<int,array{key:string,label:string,element:string}>
	 */
	public static function for_form( Form $form ): array {
		$fields  = (array) $form->getFields();
		$out     = [];
		$seen    = [];

		self::collect( $fields, $out, $seen );

		return $out;
	}

	/**
	 * Walk the form's field tree (sections / repeaters can nest fields under
	 * `fields`) and pick the data-bearing ones.
	 */
	private static function collect( array $fields, array &$out, array &$seen ): void {
		foreach ( $fields as $field ) {
			if ( ! is_array( $field ) ) {
				continue;
			}

			// Recurse into sections/repeaters that hold child fields.
			if ( ! empty( $field['fields'] ) && is_array( $field['fields'] ) ) {
				self::collect( $field['fields'], $out, $seen );
			}

			$element = (string) ( $field['element'] ?? '' );
			if ( '' === $element || in_array( $element, self::SKIP_ELEMENTS, true ) ) {
				continue;
			}

			if ( isset( self::BUILTIN_ELEMENTS[ $element ] ) ) {
				$key = self::BUILTIN_ELEMENTS[ $element ];
			} else {
				$key = (string) ( $field['name'] ?? '' );
				if ( '' === $key ) {
					continue;
				}
			}

			if ( isset( $seen[ $key ] ) ) {
				continue; // first occurrence wins (forms can sometimes duplicate)
			}
			$seen[ $key ] = true;

			$label = (string) ( $field['label'] ?? '' );
			if ( '' === $label ) {
				$label = ucwords( str_replace( [ '_', '-' ], ' ', ltrim( $key, '_' ) ) );
			}

			$out[] = [
				'key'     => $key,
				'label'   => $label,
				'element' => $element,
			];
		}
	}

	/**
	 * Returns true if the given target key is a NormalizedRow slot
	 * (vs a custom-field meta key).
	 */
	public static function is_slot( string $target ): bool {
		return '' !== $target && '__' === substr( $target, 0, 2 );
	}
}
