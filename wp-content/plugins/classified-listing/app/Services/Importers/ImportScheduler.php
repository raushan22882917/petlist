<?php

namespace Rtcl\Services\Importers;

use Rtcl\Helpers\Functions;

/**
 * Action Scheduler bridge for the listing importers.
 *
 * Responsibilities:
 *   - Register the recurring `rtcl/import/rss_sync` action handler so background
 *     workers can run scheduled RSS imports.
 *   - Register the one-shot `rtcl/import/google_run` handler so the admin
 *     "Import selected" button queues instead of running synchronously.
 *   - Translate a saved source row's schedule string ('daily', 'hourly', …)
 *     into an Action Scheduler recurring action, and unschedule it on delete /
 *     "Off".
 */
class ImportScheduler {

	const HOOK        = 'rtcl/import/rss_sync';
	const HOOK_GOOGLE = 'rtcl/import/google_run';
	const GROUP       = 'rtcl_import';

	/** Option name prefix holding a run's batch payload, keyed by run id. */
	const TASK_OPTION_PREFIX = 'rtcl_gimport_task_';

	/**
	 * Called from Rtcl::__init on every request so the worker has the callbacks
	 * registered when Action Scheduler invokes them.
	 */
	public static function register(): void {
		add_action( self::HOOK,        [ __CLASS__, 'on_recurring' ], 10, 1 );
		add_action( self::HOOK_GOOGLE, [ __CLASS__, 'on_google_run' ], 10, 1 );
	}

	/**
	 * Action Scheduler callback. Runs the import for one source.
	 */
	public static function on_recurring( $source_id ): void {
		$source_id = (int) $source_id;
		if ( $source_id <= 0 ) {
			return;
		}
		( new ImportRunner() )->run( $source_id );
	}

	/**
	 * Callback for `rtcl/import/google_run`. Processes ONE batch of a larger
	 * Google import, folds its counts into the shared history run, then either
	 * enqueues the next batch or finalizes the run.
	 *
	 * The whole job is split into batches of max_per_run place_ids. Each batch
	 * fires its successor only after it finishes, so the chain is sequential —
	 * no two batches of the same run ever overlap. This is what makes the
	 * "50 done, then the remaining 10" continuation work, and it lets the
	 * History / import UI poll live progress between batches.
	 *
	 * @param mixed $task Lightweight pointer: { run_id: int, offset: int }. The
	 *                    heavy payload (place_ids, preview_rows, opts) is loaded
	 *                    from the per-run option — it can be far larger than
	 *                    Action Scheduler's 8000-char args limit.
	 */
	public static function on_google_run( $task ): void {
		if ( ! is_array( $task ) ) {
			return;
		}

		$run_id = (int) ( $task['run_id'] ?? 0 );
		$offset = max( 0, (int) ( $task['offset'] ?? 0 ) );
		if ( $run_id <= 0 ) {
			return;
		}

		$payload = get_option( self::TASK_OPTION_PREFIX . $run_id );
		if ( ! is_array( $payload ) ) {
			// Payload missing (already cleaned up, or lost) — nothing to process.
			return;
		}

		$batch_size = max( 1, (int) ( $payload['batch_size'] ?? 50 ) );
		$light      = ! empty( $payload['light_mode'] );
		$opts       = (array) ( $payload['opts'] ?? [] );
		$all_ids    = array_values( (array) ( $payload['place_ids'] ?? [] ) );

		if ( empty( $all_ids ) ) {
			self::cleanup_and_finalize( $run_id );
			return;
		}

		$slice_ids = array_slice( $all_ids, $offset, $batch_size );
		if ( ! empty( $slice_ids ) ) {
			$params = [
				'place_ids'      => $slice_ids,
				'max_photos'     => (int) ( $payload['max_photos'] ?? 5 ),
				'light_mode'     => $light,
				'import_reviews' => ! empty( $payload['import_reviews'] ),
				'max_reviews'    => max( 1, min( 5, (int) ( $payload['max_reviews'] ?? 5 ) ) ),
			];
			if ( $light ) {
				// Keep only the preview rows for this slice's place_ids.
				$wanted = array_flip( $slice_ids );
				$rows   = [];
				foreach ( (array) ( $payload['preview_rows'] ?? [] ) as $row ) {
					$pid = is_array( $row ) ? (string) ( $row['place_id'] ?? '' ) : '';
					if ( '' !== $pid && isset( $wanted[ $pid ] ) ) {
						$rows[] = $row;
					}
				}
				$params['preview_rows'] = $rows;
			}

			$importer = new GooglePlacesImporter();
			( new ImportRunner() )->run_google_batch( $importer, $params, $opts, 'google_places', $run_id );
		}

		$next_offset = $offset + $batch_size;
		if ( $next_offset < count( $all_ids ) ) {
			$next = [ 'run_id' => $run_id, 'offset' => $next_offset ];
			if ( function_exists( 'as_enqueue_async_action' ) ) {
				as_enqueue_async_action( self::HOOK_GOOGLE, [ $next ], self::GROUP );
			} else {
				self::on_google_run( $next );
			}
			return;
		}

		// Last batch — close the shared run and drop its payload.
		self::cleanup_and_finalize( $run_id );
	}

	/**
	 * Finalize a run and remove its stored batch payload.
	 */
	private static function cleanup_and_finalize( int $run_id ): void {
		delete_option( self::TASK_OPTION_PREFIX . $run_id );
		ImportHistory::finalize( $run_id );
	}

	/**
	 * Queue a Google import to run in the background, in batches.
	 *
	 * Opens a single history run upfront (status=running, total=count) so the UI
	 * can show live progress, then enqueues the first batch. Each batch enqueues
	 * the next until the work list is exhausted (see on_google_run()).
	 *
	 * Action Scheduler fires the batches once the queue runner picks them up —
	 * typically within a few seconds, longer if WP-Cron is sluggish. Falls back
	 * to running every batch inline (synchronously) when Action Scheduler isn't
	 * loaded; the AJAX request blocks until completion in that case.
	 *
	 * @param array $bundle  {fetch_params, opts, source_key, params_log}
	 *
	 * @return array { run_id: int, action_id: int, total: int, batches: int }
	 */
	public static function schedule_google_run( array $bundle ): array {
		$fetch  = (array) ( $bundle['fetch_params'] ?? [] );
		$opts   = (array) ( $bundle['opts'] ?? [] );
		$light  = ! empty( $fetch['light_mode'] );

		$place_ids = array_values( array_filter( array_map( 'strval', (array) ( $fetch['place_ids'] ?? [] ) ) ) );
		$total     = count( $place_ids );

		$run_id = (int) ImportHistory::start_run(
			'google_places',
			(string) ( $bundle['source_key'] ?? 'google_search' ),
			(array) ( $bundle['params_log'] ?? [] ),
			$total
		);

		if ( $run_id <= 0 || $total === 0 ) {
			return [ 'run_id' => $run_id, 'action_id' => 0, 'total' => $total, 'batches' => 0 ];
		}

		$batch_size = max( 1, (int) Functions::get_option_item( 'rtcl_import_settings', 'max_per_run', 50 ) );

		// The full payload (place_ids + preview_rows + opts) can be large — far
		// over Action Scheduler's 8000-char args limit. Store it once, keyed by
		// run id, and pass only a {run_id, offset} pointer through the scheduler.
		$payload = [
			'batch_size'     => $batch_size,
			'place_ids'      => $place_ids,
			'max_photos'     => (int) ( $fetch['max_photos'] ?? 5 ),
			'light_mode'     => $light,
			'import_reviews' => ! empty( $fetch['import_reviews'] ),
			'max_reviews'    => max( 1, min( 5, (int) ( $fetch['max_reviews'] ?? 5 ) ) ),
			'preview_rows'   => $light ? array_values( (array) ( $fetch['preview_rows'] ?? [] ) ) : [],
			'opts'           => $opts,
		];
		update_option( self::TASK_OPTION_PREFIX . $run_id, $payload, false );

		$batches = (int) ceil( $total / $batch_size );
		$first   = [ 'run_id' => $run_id, 'offset' => 0 ];

		if ( ! function_exists( 'as_enqueue_async_action' ) ) {
			self::on_google_run( $first );
			return [ 'run_id' => $run_id, 'action_id' => 0, 'total' => $total, 'batches' => $batches ];
		}

		$action_id = (int) as_enqueue_async_action( self::HOOK_GOOGLE, [ $first ], self::GROUP );
		return [ 'run_id' => $run_id, 'action_id' => $action_id, 'total' => $total, 'batches' => $batches ];
	}

	/**
	 * Sync the Action Scheduler state for a source to match its persisted schedule.
	 *
	 * Call after insert / update / delete of a row in rtcl_import_sources.
	 *
	 * @param int    $source_id
	 * @param string $schedule  'off' | 'hourly' | 'twicedaily' | 'daily' | 'weekly'
	 */
	public static function sync_schedule( int $source_id, string $schedule ): void {
		if ( ! function_exists( 'as_schedule_recurring_action' ) ) {
			return;
		}

		$args = [ 'source_id' => $source_id ];
		as_unschedule_all_actions( self::HOOK, $args, self::GROUP );

		$interval = self::schedule_to_seconds( $schedule );
		if ( $interval <= 0 ) {
			return;
		}

		as_schedule_recurring_action(
			time() + $interval,
			$interval,
			self::HOOK,
			$args,
			self::GROUP
		);
	}

	/**
	 * Remove any scheduled action for a deleted source.
	 */
	public static function unschedule( int $source_id ): void {
		if ( ! function_exists( 'as_unschedule_all_actions' ) ) {
			return;
		}
		as_unschedule_all_actions( self::HOOK, [ 'source_id' => $source_id ], self::GROUP );
	}

	/**
	 * Convert a schedule slug to a seconds interval. 0 means "do not schedule".
	 */
	private static function schedule_to_seconds( string $schedule ): int {
		switch ( $schedule ) {
			case 'hourly':
				return HOUR_IN_SECONDS;
			case 'twicedaily':
				return 12 * HOUR_IN_SECONDS;
			case 'daily':
				return DAY_IN_SECONDS;
			case 'weekly':
				return 7 * DAY_IN_SECONDS;
			default:
				return 0;
		}
	}
}
