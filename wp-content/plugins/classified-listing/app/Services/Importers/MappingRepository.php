<?php

namespace Rtcl\Services\Importers;

/**
 * Persists per-(source_type, form_id) mapping specs.
 *
 * Storage: a single WP option `rtcl_import_mappings`, structured as
 *   [
 *     'google_places' => [
 *        '42' => [  // form_id
 *           'name'              => '__title',     // source_key => target_key
 *           'formatted_address' => '_rtcl_geo_address',
 *           'phone'             => '_rtcl_phone',
 *           …
 *        ],
 *        '99' => [ … ],
 *     ],
 *     'rss' => [ … ],
 *   ]
 *
 * Target keys are either FormFieldAdapter sentinels (__title, __gallery, …)
 * or a form custom-field meta key. `__skip` excludes the source field.
 */
class MappingRepository {

	const OPTION = 'rtcl_import_mappings';

	/**
	 * Saved mapping for the given (source, form). Empty array if none yet.
	 */
	public static function get( string $source_type, int $form_id ): array {
		$all = self::all();
		$key = (string) $form_id;
		return isset( $all[ $source_type ][ $key ] ) && is_array( $all[ $source_type ][ $key ] )
			? $all[ $source_type ][ $key ]
			: [];
	}

	/**
	 * Persist a mapping. Pass an empty array to clear.
	 */
	public static function save( string $source_type, int $form_id, array $map ): bool {
		$all = self::all();
		$map = self::sanitize( $map );

		if ( empty( $map ) ) {
			unset( $all[ $source_type ][ (string) $form_id ] );
			if ( isset( $all[ $source_type ] ) && empty( $all[ $source_type ] ) ) {
				unset( $all[ $source_type ] );
			}
		} else {
			$all[ $source_type ][ (string) $form_id ] = $map;
		}

		return update_option( self::OPTION, $all, false );
	}

	public static function delete( string $source_type, int $form_id ): bool {
		return self::save( $source_type, $form_id, [] );
	}

	private static function all(): array {
		$opt = get_option( self::OPTION, [] );
		return is_array( $opt ) ? $opt : [];
	}

	/**
	 * Drop anything that isn't a string→string pair and trim whitespace.
	 */
	private static function sanitize( array $map ): array {
		$out = [];
		foreach ( $map as $source_key => $target ) {
			$source_key = is_string( $source_key ) ? trim( $source_key ) : '';
			$target     = is_string( $target ) ? trim( $target ) : '';
			if ( '' === $source_key || '' === $target ) {
				continue;
			}
			$out[ $source_key ] = $target;
		}
		return $out;
	}
}
