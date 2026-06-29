<?php

namespace Rtcl\Services\Importers;

/**
 * Thin DAO around the rtcl_import_sources table.
 *
 * Stores per-feed (or per-search) configuration so RSS / Google Places imports
 * can be re-run, scheduled, and listed in the admin UI.
 */
class SourceRepository {

	private static function table(): string {
		global $wpdb;
		return $wpdb->prefix . 'rtcl_import_sources';
	}

	/**
	 * Insert a new source. Returns the new id, or false on failure.
	 *
	 * @param array $data {
	 *     @type string $source_type     'rss' | 'google_places'
	 *     @type string $label
	 *     @type string $url
	 *     @type array  $mapping         Source-specific extra config (json-encoded).
	 *     @type string $schedule        'off' | 'hourly' | 'twicedaily' | 'daily' | 'weekly'
	 *     @type int    $target_category
	 *     @type int    $target_location
	 *     @type string $target_status
	 *     @type bool   $update_existing
	 * }
	 *
	 * @return int|false
	 */
	public static function insert( array $data ) {
		global $wpdb;

		$row = self::normalize_payload( $data );
		$row['created_by'] = get_current_user_id() ?: null;
		$row['created_at'] = current_time( 'mysql' );
		$row['updated_at'] = current_time( 'mysql' );

		$ok = $wpdb->insert( self::table(), $row );

		return $ok ? (int) $wpdb->insert_id : false;
	}

	/**
	 * Partial update by id. Pass only the keys you want changed.
	 */
	public static function update( int $id, array $data ): bool {
		global $wpdb;

		if ( $id <= 0 ) {
			return false;
		}

		$row = self::normalize_payload( $data, true );
		if ( empty( $row ) ) {
			return false;
		}

		$ok = $wpdb->update( self::table(), $row, [ 'id' => $id ] );
		return false !== $ok;
	}

	public static function delete( int $id ): bool {
		global $wpdb;
		if ( $id <= 0 ) {
			return false;
		}
		$ok = $wpdb->delete( self::table(), [ 'id' => $id ], [ '%d' ] );
		return false !== $ok;
	}

	/**
	 * Fetch one source by id. Returns the row object or null.
	 */
	public static function find( int $id ) {
		global $wpdb;
		if ( $id <= 0 ) {
			return null;
		}
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery
		return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE id = %d', $id ) );
	}

	/**
	 * List sources, optionally filtered by source_type or schedule.
	 *
	 * @return array<int,object>
	 */
	public static function list( array $args = [] ): array {
		global $wpdb;

		$args = wp_parse_args( $args, [
			'source_type' => '',
			'schedule'    => '',
			'limit'       => 50,
			'offset'      => 0,
		] );

		$where = [ '1=1' ];
		$prep  = [];

		if ( $args['source_type'] ) {
			$where[] = 'source_type = %s';
			$prep[]  = $args['source_type'];
		}
		if ( $args['schedule'] ) {
			$where[] = 'schedule = %s';
			$prep[]  = $args['schedule'];
		}

		$sql    = 'SELECT * FROM ' . self::table()
		          . ' WHERE ' . implode( ' AND ', $where )
		          . ' ORDER BY id DESC LIMIT %d OFFSET %d';
		$prep[] = (int) $args['limit'];
		$prep[] = (int) $args['offset'];

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $prep ) );
		return is_array( $rows ) ? $rows : [];
	}

	/**
	 * Record a successful run completion. Updates last_run_at and next_run_at.
	 */
	public static function touch_run( int $id, ?string $next_run_at = null ): bool {
		global $wpdb;

		$ok = $wpdb->update(
			self::table(),
			[
				'last_run_at' => current_time( 'mysql' ),
				'next_run_at' => $next_run_at,
			],
			[ 'id' => $id ],
			[ '%s', '%s' ],
			[ '%d' ]
		);
		return false !== $ok;
	}

	/**
	 * Coerce / whitelist incoming payload to the column shape.
	 *
	 * @param array $data
	 * @param bool  $partial When true, omit unset keys (used by update()).
	 *
	 * @return array
	 */
	private static function normalize_payload( array $data, bool $partial = false ): array {
		$allowed = [
			'source_type'     => 'string',
			'label'           => 'string',
			'url'             => 'string',
			'mapping'         => 'json',
			'schedule'        => 'string',
			'target_category' => 'int',
			'target_location' => 'int',
			'target_status'   => 'string',
			'update_existing' => 'bool',
		];

		$row = [];
		foreach ( $allowed as $key => $type ) {
			if ( $partial && ! array_key_exists( $key, $data ) ) {
				continue;
			}
			$value = $data[ $key ] ?? null;
			switch ( $type ) {
				case 'int':
					$row[ $key ] = (int) $value;
					break;
				case 'bool':
					$row[ $key ] = $value ? 1 : 0;
					break;
				case 'json':
					$row[ $key ] = is_array( $value ) ? wp_json_encode( $value ) : ( is_string( $value ) ? $value : null );
					break;
				default:
					$row[ $key ] = null === $value ? null : (string) $value;
			}
		}
		return $row;
	}
}
