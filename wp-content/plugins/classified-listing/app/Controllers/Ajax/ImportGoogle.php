<?php

namespace Rtcl\Controllers\Ajax;

use Rtcl\Helpers\Functions;
use Rtcl\Services\Importers\GooglePlacesImporter;
use Rtcl\Services\Importers\ImportHistory;
use Rtcl\Services\Importers\ImportScheduler;
use Rtcl\Services\Importers\MappingRepository;

/**
 * AJAX endpoints for the Google Places importer tab.
 *
 * Two endpoints because the Google flow is two-step:
 *   1. rtcl_import_google_search — cheap call, returns ~20 preview rows for
 *                                  the admin to select from.
 *   2. rtcl_import_google_run    — expensive call (Place Details + photo
 *                                  downloads per place_id), runs the ingester.
 */
class ImportGoogle {

	public function __construct() {
		add_action( 'wp_ajax_rtcl_import_google_search',   [ $this, 'search' ] );
		add_action( 'wp_ajax_rtcl_import_google_run',      [ $this, 'run' ] );
		add_action( 'wp_ajax_rtcl_import_progress',        [ $this, 'progress' ] );
	}

	/**
	 * Live progress poll for a running (or finished) import run. The Google
	 * import tab and the History tab poll this to render a progress bar without
	 * reloading the page.
	 */
	public function progress(): void {
		$this->guard();

		$run_id = (int) ( $_POST['run_id'] ?? 0 );
		if ( $run_id <= 0 ) {
			wp_send_json_error( [ 'message' => __( 'Missing run id.', 'classified-listing' ) ] );
		}

		$row = ImportHistory::get( $run_id );
		if ( ! $row ) {
			wp_send_json_error( [ 'message' => __( 'Run not found.', 'classified-listing' ) ] );
		}

		$imported  = (int) $row->imported;
		$updated   = (int) $row->updated;
		$skipped   = (int) $row->skipped;
		$processed = $imported + $updated + $skipped;
		$total     = max( (int) $row->total, $processed );
		$errors    = $row->errors ? array_map( 'strval', (array) json_decode( $row->errors, true ) ) : [];

		wp_send_json_success( [
			'status'    => (string) $row->status,
			'done'      => 'running' !== (string) $row->status,
			'total'     => $total,
			'processed' => $processed,
			'imported'  => $imported,
			'updated'   => $updated,
			'skipped'   => $skipped,
			'errors'    => $errors,
			'percent'   => $total > 0 ? min( 100, (int) floor( $processed / $total * 100 ) ) : 0,
		] );
	}

	public function search(): void {
		$this->guard();

		$query  = sanitize_text_field( wp_unslash( $_POST['query'] ?? '' ) );
		$region = sanitize_text_field( wp_unslash( $_POST['region'] ?? '' ) );
		$lat    = isset( $_POST['lat'] ) ? (float) $_POST['lat'] : 0.0;
		$lng    = isset( $_POST['lng'] ) ? (float) $_POST['lng'] : 0.0;
		$radius = isset( $_POST['radius'] ) ? (int) $_POST['radius'] : 5000;
		// Hard cap at 60 — Google's pagination ceiling for Text Search.
		$limit  = isset( $_POST['limit'] ) ? max( 1, min( 60, (int) $_POST['limit'] ) ) : 20;

		$bias = [];
		if ( $lat && $lng ) {
			$bias = [ 'lat' => $lat, 'lng' => $lng, 'radius' => $radius ];
		}

		$importer = new GooglePlacesImporter();
		$results  = $importer->search( $query, $region, $bias, $limit );
		if ( is_wp_error( $results ) ) {
			wp_send_json_error( [ 'message' => $results->get_error_message() ] );
		}

		wp_send_json_success( [
			'count'   => count( $results ),
			'results' => $results,
		] );
	}

	public function run(): void {
		$this->guard();

		$place_ids = $_POST['place_ids'] ?? [];
		if ( ! is_array( $place_ids ) ) {
			$place_ids = [];
		}
		$place_ids = array_values( array_filter( array_map(
			static function ( $v ) {
				return sanitize_text_field( wp_unslash( (string) $v ) );
			},
			$place_ids
		) ) );

		if ( empty( $place_ids ) ) {
			wp_send_json_error( [ 'message' => __( 'Select at least one place to import.', 'classified-listing' ) ] );
		}

		// Google's Text Search pagination ceiling is 60 places per query, so a
		// single import can target at most 60. The per-run setting no longer
		// truncates the selection — it's now the batch size: the import is split
		// into batches of that size and run across multiple background passes,
		// so e.g. 60 places at a batch size of 50 import as 50 then 10.
		if ( count( $place_ids ) > 60 ) {
			$place_ids = array_slice( $place_ids, 0, 60 );
		}

		$light_mode = ! empty( $_POST['light_mode'] );

		$params = [
			'place_ids'      => $place_ids,
			'max_photos'     => 5,
			'light_mode'     => $light_mode,
			// Reviews are opt-in (extra billing) and only available on the full
			// details fetch — the light/preview path has no review data.
			'import_reviews' => ! empty( $_POST['import_reviews'] ),
			'max_reviews'    => max( 1, min( 5, (int) ( $_POST['max_reviews'] ?? 5 ) ) ),
		];

		if ( $light_mode ) {
			$raw_preview = $_POST['places_data'] ?? [];
			if ( ! is_array( $raw_preview ) ) {
				$raw_preview = [];
			}

			$selected_ids = array_flip( $place_ids );
			$preview_rows = [];
			foreach ( $raw_preview as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				$pid = sanitize_text_field( wp_unslash( (string) ( $row['place_id'] ?? '' ) ) );
				if ( '' === $pid || ! isset( $selected_ids[ $pid ] ) ) {
					continue;
				}
				$types_raw = isset( $row['types'] ) && is_array( $row['types'] ) ? $row['types'] : [];
				$preview_rows[] = [
					'place_id' => $pid,
					'name'     => sanitize_text_field( wp_unslash( (string) ( $row['name'] ?? '' ) ) ),
					'address'  => sanitize_text_field( wp_unslash( (string) ( $row['address'] ?? '' ) ) ),
					'photo'    => esc_url_raw( wp_unslash( (string) ( $row['photo'] ?? '' ) ) ),
					'lat'      => isset( $row['lat'] ) ? (float) $row['lat'] : 0.0,
					'lng'      => isset( $row['lng'] ) ? (float) $row['lng'] : 0.0,
					'types'    => array_values( array_filter( array_map(
						static function ( $t ) {
							return sanitize_text_field( wp_unslash( (string) $t ) );
						},
						$types_raw
					) ) ),
				];
			}
			$params['preview_rows'] = $preview_rows;
		}

		$form_id = (int) ( $_POST['form_id'] ?? 0 );

		// Mapping: prefer client-sent (the admin may have un-saved edits),
		// else fall back to the saved (source, form) mapping in options.
		$mapping = [];
		if ( isset( $_POST['mapping'] ) && is_array( $_POST['mapping'] ) ) {
			foreach ( $_POST['mapping'] as $k => $v ) {
				$k = sanitize_text_field( wp_unslash( (string) $k ) );
				$v = sanitize_text_field( wp_unslash( (string) $v ) );
				if ( '' !== $k && '' !== $v ) {
					$mapping[ $k ] = $v;
				}
			}
		}
		if ( empty( $mapping ) && $form_id > 0 ) {
			$mapping = MappingRepository::get( 'google_places', $form_id );
		}

		$image_source = sanitize_key( wp_unslash( $_POST['image_source'] ?? 'google' ) );
		if ( ! in_array( $image_source, [ 'google', 'fallback', 'none' ], true ) ) {
			$image_source = 'google';
		}
		$fallback_url = (string) Functions::get_option_item( 'rtcl_import_settings', 'default_fallback_image_url', '' );
		if ( 'fallback' === $image_source && '' === trim( $fallback_url ) ) {
			// Configured fallback is empty — fall through to "no image" rather
			// than silently using Google photos and surprising the admin.
			$image_source = 'none';
		}

		$opts = [
			'update_existing'    => ! empty( $_POST['update_existing'] ),
			'target_category'    => (int) ( $_POST['target_category'] ?? 0 ),
			'target_location'    => (int) ( $_POST['target_location'] ?? 0 ),
			'default_status'     => sanitize_key( wp_unslash( $_POST['target_status'] ?? '' ) )
				?: (string) Functions::get_option_item( 'rtcl_import_settings', 'default_import_status', 'pending' ),
			'form_id'            => $form_id,
			'mapping'             => $mapping,
			'enrich'              => ! empty( $_POST['enrich'] ),
			'enrich_cap'          => 10,
			'enrich_description'  => ! empty( $_POST['enrich_description'] ),
			'image_source'        => $image_source,
			'fallback_image_url'  => $fallback_url,
		];

		$bundle = [
			'fetch_params' => $params,
			'opts'         => $opts,
			'source_key'   => $light_mode ? 'google_search_light' : 'google_search',
			'params_log'   => [
				'place_ids' => $place_ids,
				'form_id'   => $form_id,
				'enrich'    => $opts['enrich'],
				'light'     => $light_mode,
			],
		];

		// Idempotency guard. A double-click, an accidental resubmit, or a
		// retried AJAX call would otherwise spawn a second run for the same
		// selection — and two runs importing the same place_ids in parallel
		// race the dedupe check and create duplicate listings. Lock on a
		// signature of the request for a short window; a repeat within that
		// window re-attaches to the in-flight run instead of starting a new one.
		$signature = md5( implode( ',', $place_ids ) . '|' . $form_id . '|' . (int) $light_mode . '|' . (int) $opts['update_existing'] . '|' . get_current_user_id() );
		$lock_key  = 'rtcl_gimport_lock_' . $signature;
		$locked    = (int) get_transient( $lock_key );
		if ( 0 !== $locked ) {
			// Locked: -1 means a sibling request is mid-scheduling (run id not
			// known yet); a positive value is the in-flight run to re-attach to.
			wp_send_json_success( [
				'scheduled'   => true,
				'duplicate'   => true,
				'run_id'      => max( 0, $locked ),
				'action_id'   => 0,
				'total'       => count( $place_ids ),
				'batches'     => 0,
				'count'       => count( $place_ids ),
				'history_url' => admin_url( 'admin.php?page=rtcl-import-export&tab=history' ),
				'message'     => __( 'This import is already running — showing its progress.', 'classified-listing' ),
			] );
		}

		// Reserve the lock (sentinel -1) before scheduling so a near-simultaneous
		// second request sees it. Short TTL: long enough to absorb double-submits
		// and retries, short enough to allow a deliberate re-import later.
		set_transient( $lock_key, -1, 2 * MINUTE_IN_SECONDS );

		$schedule = ImportScheduler::schedule_google_run( $bundle );
		$run_id   = (int) ( $schedule['run_id'] ?? 0 );

		if ( $run_id > 0 ) {
			set_transient( $lock_key, $run_id, 2 * MINUTE_IN_SECONDS );
		} else {
			delete_transient( $lock_key );
		}

		wp_send_json_success( [
			'scheduled'   => true,
			'run_id'      => $run_id,
			'action_id'   => (int) ( $schedule['action_id'] ?? 0 ),
			'total'       => (int) ( $schedule['total'] ?? count( $place_ids ) ),
			'batches'     => (int) ( $schedule['batches'] ?? 1 ),
			'count'       => count( $place_ids ),
			'history_url' => admin_url( 'admin.php?page=rtcl-import-export&tab=history' ),
			'message'     => sprintf(
				/* translators: %d: number of places queued */
				_n(
					'%d place has been queued for background import.',
					'%d places have been queued for background import.',
					count( $place_ids ),
					'classified-listing'
				),
				count( $place_ids )
			),
		] );
	}

	private function guard(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'classified-listing' ) ], 403 );
		}
		$nonce = $_POST[ rtcl()->nonceId ] ?? '';
		if ( ! wp_verify_nonce( $nonce, rtcl()->nonceText ) ) {
			wp_send_json_error( [ 'message' => __( 'Session expired.', 'classified-listing' ) ], 403 );
		}
	}
}
