<?php

namespace Rtcl\Controllers;

use Rtcl\Helpers\Functions;
use Rtcl\Models\EmbeddingModel;
use Rtcl\Services\EmbeddingService;

class EmbeddingController {

	public function __construct() {
		if ( ! Functions::is_semantic_search_enabled() ) {
			return;
		}
		add_action( 'init', [ EmbeddingModel::class, 'create_table' ] );
		add_action( 'save_post_rtcl_listing', [ $this, 'handle_listing_save' ], 10, 2 );
		add_action( 'rtcl_listing_form_after_save_or_update', [ $this, 'handle_listing_frontend_save' ], 99 );
		// cron process to manage embeddings for existing listings
		add_action( 'rtcl_embedding_cron_run', [ $this, 'process_batch' ] );
		add_action( 'admin_notices', [ $this, 'show_notice' ] );
		add_action( 'init', [ $this, 'start_cron' ] );
	}

	/**
	 * Show admin notice during processing
	 */
	public function show_notice() {
		// Show error notice if there was an error
		$error = get_option( 'rtcl_embedding_error' );
		if ( $error ) {
			?>
			<div class="notice notice-error is-dismissible">
				<p>
					<strong><?php esc_html_e( 'Classified Listing', 'classified-listing' ); ?></strong> -
					<?php esc_html_e( 'AI Search data training failed:', 'classified-listing' ); ?>
					<?php echo esc_html( $error ); ?>
				</p>
			</div>
			<?php
			delete_option( 'rtcl_embedding_error' );

			return;
		}

		if ( ! get_option( 'rtcl_embedding_in_progress' ) ) {
			return;
		}

		$progress  = get_option( 'rtcl_embedding_progress', [ 'processed' => 0, 'total' => 0, 'failed' => 0 ] );
		$total     = max( 1, intval( $progress['total'] ) );
		$done      = intval( $progress['processed'] );
		$failed    = isset( $progress['failed'] ) ? intval( $progress['failed'] ) : 0;
		$remaining = Functions::need_listings_embedding();
		$percent   = min( 100, round( ( ( $done + $failed ) / $total ) * 100 ) );
		?>
		<div class="notice notice-warning">
			<p>
				<strong><?php esc_html_e( 'Classified Listing', 'classified-listing' ); ?></strong> -
				<?php esc_html_e( 'AI Search data training in progress...', 'classified-listing' ); ?>
			</p>
			<p>
				<?php
				printf(
					/* translators: 1: processed count, 2: total count */
					esc_html__( 'Processed: %1$d / %2$d', 'classified-listing' ),
					$done,
					$total
				);

				if ( $failed > 0 ) {
					echo ' | ';
					printf(
						/* translators: %d: failed count */
						esc_html__( 'Failed: %d', 'classified-listing' ),
						$failed
					);
				}

				if ( $remaining > 0 ) {
					echo ' | ';
					printf(
						/* translators: %d: remaining count */
						esc_html__( 'Remaining: %d', 'classified-listing' ),
						$remaining
					);
				}
				?>
			</p>
			<div style="background: #e0e0e0; border-radius: 3px; height: 20px; margin: 10px 0; overflow: hidden;">
				<div style="background: #2271b1; height: 100%; width: <?php echo esc_attr( $percent ); ?>%; transition: width 0.3s;"></div>
			</div>
		</div>
		<?php
	}

	/**
	 * @return void
	 */
	public function start_cron() {
		if ( isset( $_GET['action'] ) && sanitize_text_field( $_GET['action'] ) === 'rtcl_start_embedding_process' ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( __( 'Access denied.', 'classified-listing' ) );
			}

			if ( ! Functions::verify_nonce() ) {
				wp_die( __( 'Invalid request.', 'classified-listing' ) );
			}

			if ( ! wp_next_scheduled( 'rtcl_embedding_cron_run' ) ) {
				wp_schedule_single_event( time() + 2, 'rtcl_embedding_cron_run' );
			}

			update_option( 'rtcl_embedding_in_progress', true );
			update_option( 'rtcl_embedding_progress', [ 'processed' => 0, 'total' => Functions::need_listings_embedding() ] );
		}
	}

	/**
	 * @return void
	 */
	public function process_batch() {
		// Get listings that don't have embedding and haven't failed
		$listings = get_posts( [
			'post_type'      => 'rtcl_listing',
			'post_status'    => 'publish',
			'posts_per_page' => 25,
			'fields'         => 'ids',
			'meta_query'     => [
				'relation' => 'AND',
				[
					'key'     => '_has_embedding',
					'compare' => 'NOT EXISTS',
				],
				[
					'key'     => '_embedding_failed',
					'compare' => 'NOT EXISTS',
				],
			],
		] );

		if ( empty( $listings ) ) {
			delete_option( 'rtcl_embedding_in_progress' );
			update_option( 'rtcl_embedding_process_completed', time() );

			return; // all done
		}

		// Try to create service, handle exception if AI not configured
		try {
			$service = new EmbeddingService();
		} catch ( \Exception $e ) {
			// AI service not configured, stop the process
			delete_option( 'rtcl_embedding_in_progress' );
			update_option( 'rtcl_embedding_error', $e->getMessage() );

			return;
		}

		$processed = 0;
		$failed    = 0;

		foreach ( $listings as $id ) {
			$title   = get_the_title( $id );
			$content = get_post_field( 'post_content', $id );

			try {
				$result = $service->generate_and_store( $id, $title, $content );

				if ( $result ) {
					update_post_meta( $id, '_has_embedding', 1 );
					delete_post_meta( $id, '_embedding_failed' );
					$processed++;
				} else {
					// API returned empty/invalid response
					update_post_meta( $id, '_embedding_failed', time() );
					$failed++;
				}
			} catch ( \Exception $e ) {
				// Mark as failed to prevent infinite retry
				update_post_meta( $id, '_embedding_failed', time() );
				$failed++;
			}
		}

		// Update progress
		$progress              = get_option( 'rtcl_embedding_progress', [ 'processed' => 0, 'total' => 0, 'failed' => 0 ] );
		$progress['processed'] = isset( $progress['processed'] ) ? $progress['processed'] + $processed : $processed;
		$progress['failed']    = isset( $progress['failed'] ) ? $progress['failed'] + $failed : $failed;
		update_option( 'rtcl_embedding_progress', $progress );

		// Schedule the next batch
		if ( ! wp_next_scheduled( 'rtcl_embedding_cron_run' ) ) {
			wp_schedule_single_event( time() + 5, 'rtcl_embedding_cron_run' );
		}
	}

	/**
	 * @param $listing
	 *
	 * @return void
	 */
	public function handle_listing_frontend_save( $listing ): void {
		if ( ! $listing ) {
			return;
		}

		if ( $listing->get_status() !== 'publish' ) {
			return;
		}

		$title   = $listing->get_the_title();
		$content = $listing->get_the_content();

		$service = new EmbeddingService();
		$service->generate_and_store( $listing->get_id(), $title, $content );
	}

	/**
	 * Generate embeddings automatically when listing is saved
	 */
	public function handle_listing_save( $post_id, $post ) {
		if ( $post->post_status !== 'publish' ) {
			return;
		}

		$title   = $post->post_title;
		$content = $post->post_content;

		$service = new EmbeddingService();
		$service->generate_and_store( $post_id, $title, $content );
	}

	/**
	 * Handle REST API search request
	 */
	public function search_listings( $request ) {
		$params = $request->get_json_params();
		$query  = sanitize_text_field( $params['query'] ?? '' );

		$service = new EmbeddingService();
		$results = $service->search( $query );

		return rest_ensure_response( [ 'results' => $results ] );
	}
}