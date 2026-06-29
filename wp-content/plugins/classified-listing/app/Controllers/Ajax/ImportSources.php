<?php

namespace Rtcl\Controllers\Ajax;

use Rtcl\Services\Importers\ImportRunner;
use Rtcl\Services\Importers\ImportScheduler;
use Rtcl\Services\Importers\RssImporter;
use Rtcl\Services\Importers\SourceRepository;

/**
 * AJAX endpoints for managing saved import sources (RSS for now; Google Places
 * lands in Phase 3).
 *
 * All endpoints require manage_options + the rtcl wp-nonce.
 */
class ImportSources {

	public function __construct() {
		add_action( 'wp_ajax_rtcl_import_rss_save',    [ $this, 'save_rss' ] );
		add_action( 'wp_ajax_rtcl_import_rss_delete',  [ $this, 'delete_source' ] );
		add_action( 'wp_ajax_rtcl_import_rss_run',     [ $this, 'run_source' ] );
		add_action( 'wp_ajax_rtcl_import_rss_preview', [ $this, 'preview_rss' ] );
	}

	public function save_rss(): void {
		$this->guard();

		$id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;

		$payload = [
			'source_type'     => 'rss',
			'label'           => sanitize_text_field( wp_unslash( $_POST['label'] ?? '' ) ),
			'url'             => esc_url_raw( wp_unslash( $_POST['url'] ?? '' ) ),
			'schedule'        => $this->sanitize_schedule( wp_unslash( $_POST['schedule'] ?? 'off' ) ),
			'target_category' => (int) ( $_POST['target_category'] ?? 0 ),
			'target_location' => (int) ( $_POST['target_location'] ?? 0 ),
			'target_status'   => sanitize_key( wp_unslash( $_POST['target_status'] ?? '' ) ),
			'update_existing' => ! empty( $_POST['update_existing'] ),
		];

		$check = ( new RssImporter() )->validate_config( $payload );
		if ( is_wp_error( $check ) ) {
			wp_send_json_error( [ 'message' => $check->get_error_message() ] );
		}

		if ( $id > 0 ) {
			$ok = SourceRepository::update( $id, $payload );
			$saved_id = $ok ? $id : 0;
		} else {
			$saved_id = SourceRepository::insert( $payload );
		}

		if ( ! $saved_id ) {
			wp_send_json_error( [ 'message' => __( 'Failed to save feed.', 'classified-listing' ) ] );
		}

		ImportScheduler::sync_schedule( (int) $saved_id, $payload['schedule'] );

		wp_send_json_success( [
			'id'      => (int) $saved_id,
			'message' => __( 'Feed saved.', 'classified-listing' ),
		] );
	}

	public function delete_source(): void {
		$this->guard();

		$id = (int) ( $_POST['id'] ?? 0 );
		if ( $id <= 0 ) {
			wp_send_json_error( [ 'message' => __( 'Invalid id.', 'classified-listing' ) ] );
		}

		ImportScheduler::unschedule( $id );
		if ( ! SourceRepository::delete( $id ) ) {
			wp_send_json_error( [ 'message' => __( 'Failed to delete feed.', 'classified-listing' ) ] );
		}

		wp_send_json_success( [ 'message' => __( 'Feed deleted.', 'classified-listing' ) ] );
	}

	public function run_source(): void {
		$this->guard();

		$id = (int) ( $_POST['id'] ?? 0 );
		if ( $id <= 0 ) {
			wp_send_json_error( [ 'message' => __( 'Invalid id.', 'classified-listing' ) ] );
		}

		$result = ( new ImportRunner() )->run( $id );

		wp_send_json_success( [
			'imported' => (int) $result['imported'],
			'updated'  => (int) $result['updated'],
			'skipped'  => (int) $result['skipped'],
			'errors'   => $result['errors'],
			'message'  => sprintf(
				/* translators: 1: inserted count, 2: updated count, 3: skipped count */
				__( 'Imported %1$d, updated %2$d, skipped %3$d.', 'classified-listing' ),
				(int) $result['imported'],
				(int) $result['updated'],
				(int) $result['skipped']
			),
		] );
	}

	/**
	 * Validate-and-fetch endpoint used by the "Test feed" button before saving.
	 * Returns the first few items so the admin can confirm the URL is alive.
	 */
	public function preview_rss(): void {
		$this->guard();

		$url   = esc_url_raw( wp_unslash( $_POST['url'] ?? '' ) );
		$rows  = ( new RssImporter() )->fetch( [ 'url' => $url, 'limit' => 5 ] );
		if ( is_wp_error( $rows ) ) {
			wp_send_json_error( [ 'message' => $rows->get_error_message() ] );
		}

		$preview = array_map( function ( $r ) {
			return [
				'title'      => $r['title'] ?? '',
				'source_url' => $r['source_url'] ?? '',
				'excerpt'    => $r['excerpt'] ?? '',
				'images'     => array_slice( (array) ( $r['gallery_urls'] ?? [] ), 0, 1 ),
			];
		}, $rows );

		wp_send_json_success( [
			'count' => count( $preview ),
			'items' => $preview,
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

	private function sanitize_schedule( string $value ): string {
		$allowed = [ 'off', 'hourly', 'twicedaily', 'daily', 'weekly' ];
		return in_array( $value, $allowed, true ) ? $value : 'off';
	}
}
