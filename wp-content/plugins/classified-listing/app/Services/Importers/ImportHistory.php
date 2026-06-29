<?php

namespace Rtcl\Services\Importers;

/**
 * Thin DAO around the rtcl_import_history table.
 *
 * Lifecycle: start_run() inserts a row with status='running' and returns its
 * id; the caller increments counters during processing then calls finish_run()
 * with the final status and per-error list.
 */
class ImportHistory {

	const STATUS_RUNNING = 'running';
	const STATUS_SUCCESS = 'success';
	const STATUS_PARTIAL = 'partial';
	const STATUS_FAILED  = 'failed';

	private static function table(): string {
		global $wpdb;
		return $wpdb->prefix . 'rtcl_import_history';
	}

	/**
	 * Record the start of an import run.
	 *
	 * @param string $source_type 'csv' | 'rss' | 'google_places'
	 * @param string $source_key  feed URL, search query, file name, …
	 * @param array  $params      Arbitrary run parameters (json-encoded).
	 * @param int    $total       Known total rows to process (for progress %).
	 *                            0 when unknown — filled in at finish_run().
	 *
	 * @return int|false Insert id, or false on failure.
	 */
	public static function start_run( string $source_type, string $source_key = '', array $params = [], int $total = 0 ) {
		global $wpdb;

		$ok = $wpdb->insert(
			self::table(),
			[
				'source_type' => $source_type,
				'source_key'  => $source_key,
				'params'      => $params ? wp_json_encode( $params ) : null,
				'status'      => self::STATUS_RUNNING,
				'user_id'     => get_current_user_id() ?: null,
				'total'       => max( 0, $total ),
				'started_at'  => current_time( 'mysql' ),
			],
			[ '%s', '%s', '%s', '%s', '%d', '%d', '%s' ]
		);

		return $ok ? (int) $wpdb->insert_id : false;
	}

	/**
	 * Atomically add to a running run's counters and append any new errors.
	 *
	 * Used by the batched (Action Scheduler) Google importer: each background
	 * batch processes a slice and bumps the shared run so the History / import
	 * UI can show live progress before the whole job is done. The run stays
	 * in 'running' status — call finalize() when the last batch completes.
	 *
	 * Counter increments are done in a single UPDATE so concurrent batches
	 * can't clobber each other's count; the errors blob is read-modify-write,
	 * which is safe here because batches of one run fire sequentially (each
	 * batch enqueues the next only after it finishes).
	 *
	 * @param int   $run_id
	 * @param array $deltas     { imported?: int, updated?: int, skipped?: int }
	 * @param array $new_errors Error strings to append.
	 *
	 * @return bool
	 */
	public static function bump_counts( int $run_id, array $deltas, array $new_errors = [] ): bool {
		global $wpdb;

		if ( $run_id <= 0 ) {
			return false;
		}

		$imported = max( 0, (int) ( $deltas['imported'] ?? 0 ) );
		$updated  = max( 0, (int) ( $deltas['updated'] ?? 0 ) );
		$skipped  = max( 0, (int) ( $deltas['skipped'] ?? 0 ) );

		$table = self::table();

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery
		$wpdb->query( $wpdb->prepare(
			"UPDATE {$table} SET imported = imported + %d, updated = updated + %d, skipped = skipped + %d WHERE id = %d",
			$imported,
			$updated,
			$skipped,
			$run_id
		) );

		if ( ! empty( $new_errors ) ) {
			$row      = self::get( $run_id );
			$existing = ( $row && $row->errors ) ? (array) json_decode( $row->errors, true ) : [];
			$merged   = array_merge( $existing, array_values( $new_errors ) );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->update(
				$table,
				[ 'errors' => wp_json_encode( $merged ) ],
				[ 'id' => $run_id ],
				[ '%s' ],
				[ '%d' ]
			);
		}

		return true;
	}

	/**
	 * Close out a run that was driven by bump_counts(): read the accumulated
	 * counters, derive the final status (unless forced), and stamp finished_at.
	 *
	 * @param int         $run_id
	 * @param string|null $status Force a status, or null to auto-derive.
	 *
	 * @return bool
	 */
	public static function finalize( int $run_id, ?string $status = null ): bool {
		global $wpdb;

		$row = self::get( $run_id );
		if ( ! $row ) {
			return false;
		}

		$imported = (int) $row->imported;
		$updated  = (int) $row->updated;
		$skipped  = (int) $row->skipped;
		$errors   = $row->errors ? (array) json_decode( $row->errors, true ) : [];
		$total    = (int) $row->total;
		if ( $total <= 0 ) {
			$total = $imported + $updated + $skipped;
		}

		if ( null === $status ) {
			if ( ( $imported > 0 || $updated > 0 ) && empty( $errors ) ) {
				$status = self::STATUS_SUCCESS;
			} elseif ( $imported > 0 || $updated > 0 ) {
				$status = self::STATUS_PARTIAL;
			} else {
				$status = self::STATUS_FAILED;
			}
		}

		$ok = $wpdb->update(
			self::table(),
			[
				'status'      => $status,
				'total'       => $total,
				'finished_at' => current_time( 'mysql' ),
			],
			[ 'id' => $run_id ],
			[ '%s', '%d', '%s' ],
			[ '%d' ]
		);

		return false !== $ok;
	}

	/**
	 * Mark an import run as finished and write the final counters + errors.
	 *
	 * Status is auto-derived from counters when not provided:
	 *   imported>0, errors=0  → success
	 *   imported>0, errors>0  → partial
	 *   imported=0            → failed
	 */
	public static function finish_run( int $run_id, array $counts, array $errors = [], ?string $status = null ): bool {
		global $wpdb;

		$imported = (int) ( $counts['imported'] ?? 0 );
		$updated  = (int) ( $counts['updated'] ?? 0 );
		$skipped  = (int) ( $counts['skipped'] ?? 0 );
		$total    = (int) ( $counts['total'] ?? ( $imported + $updated + $skipped + count( $errors ) ) );

		if ( null === $status ) {
			if ( $imported > 0 && empty( $errors ) ) {
				$status = self::STATUS_SUCCESS;
			} elseif ( $imported > 0 ) {
				$status = self::STATUS_PARTIAL;
			} else {
				$status = self::STATUS_FAILED;
			}
		}

		$ok = $wpdb->update(
			self::table(),
			[
				'status'      => $status,
				'total'       => $total,
				'imported'    => $imported,
				'updated'     => $updated,
				'skipped'     => $skipped,
				'errors'      => $errors ? wp_json_encode( $errors ) : null,
				'finished_at' => current_time( 'mysql' ),
			],
			[ 'id' => $run_id ],
			[ '%s', '%d', '%d', '%d', '%d', '%s', '%s' ],
			[ '%d' ]
		);

		return false !== $ok;
	}

	/**
	 * Return recent runs, newest first.
	 *
	 * @param array $args { source_type?: string, status?: string, limit?: int, offset?: int }
	 *
	 * @return array<int,object>
	 */
	public static function list( array $args = [] ): array {
		global $wpdb;

		$args = wp_parse_args( $args, [
			'source_type' => '',
			'status'      => '',
			'limit'       => 20,
			'offset'      => 0,
		] );

		$where = [ '1=1' ];
		$prep  = [];

		if ( $args['source_type'] ) {
			$where[] = 'source_type = %s';
			$prep[]  = $args['source_type'];
		}
		if ( $args['status'] ) {
			$where[] = 'status = %s';
			$prep[]  = $args['status'];
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
	 * Total runs matching the same filters as list(). Used for pagination.
	 */
	public static function count( array $args = [] ): int {
		global $wpdb;

		$args = wp_parse_args( $args, [
			'source_type' => '',
			'status'      => '',
		] );

		$where = [ '1=1' ];
		$prep  = [];

		if ( $args['source_type'] ) {
			$where[] = 'source_type = %s';
			$prep[]  = $args['source_type'];
		}
		if ( $args['status'] ) {
			$where[] = 'status = %s';
			$prep[]  = $args['status'];
		}

		$sql = 'SELECT COUNT(*) FROM ' . self::table() . ' WHERE ' . implode( ' AND ', $where );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery
		return (int) $wpdb->get_var( $prep ? $wpdb->prepare( $sql, $prep ) : $sql );
	}

	/**
	 * Remove a single run row. Idempotent — deleting an id that no longer
	 * exists is not treated as an error.
	 */
	public static function delete( int $run_id ): bool {
		global $wpdb;
		if ( $run_id <= 0 ) {
			return false;
		}
		$ok = $wpdb->delete( self::table(), [ 'id' => $run_id ], [ '%d' ] );
		return false !== $ok;
	}

	/**
	 * Bulk-delete runs by id. Returns the number of rows actually removed.
	 * Uses a single DELETE … IN (…) query so it's O(1) round trips regardless
	 * of selection size.
	 */
	public static function delete_bulk( array $ids ): int {
		global $wpdb;

		$ids = array_values( array_unique( array_filter( array_map( 'intval', $ids ), static function ( $v ) { return $v > 0; } ) ) );
		if ( empty( $ids ) ) {
			return 0;
		}

		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$sql = 'DELETE FROM ' . self::table() . ' WHERE id IN (' . $placeholders . ')';

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery
		$result = $wpdb->query( $wpdb->prepare( $sql, $ids ) );
		return false === $result ? 0 : (int) $result;
	}

	/**
	 * Fetch a single run by id, or null if not found.
	 */
	public static function get( int $run_id ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery
		return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE id = %d', $run_id ) );
	}
}
