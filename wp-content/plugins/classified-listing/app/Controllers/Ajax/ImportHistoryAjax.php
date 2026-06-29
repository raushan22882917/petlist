<?php

namespace Rtcl\Controllers\Ajax;

use Rtcl\Services\Importers\ImportHistory;

/**
 * AJAX endpoints for the Import History admin tab.
 *
 * Only one action for now (delete a single row) — kept in its own class so the
 * history concerns are separate from saved-source CRUD in Ajax\ImportSources
 * and to dodge a class-name collision with Rtcl\Services\Importers\ImportHistory.
 */
class ImportHistoryAjax {

	public function __construct() {
		add_action( 'wp_ajax_rtcl_import_history_delete',      [ $this, 'delete' ] );
		add_action( 'wp_ajax_rtcl_import_history_bulk_delete', [ $this, 'bulk_delete' ] );
	}

	public function delete(): void {
		$this->guard();

		$id = (int) ( $_POST['id'] ?? 0 );
		if ( $id <= 0 ) {
			wp_send_json_error( [ 'message' => __( 'Invalid history id.', 'classified-listing' ) ] );
		}

		if ( ! ImportHistory::delete( $id ) ) {
			wp_send_json_error( [ 'message' => __( 'Failed to delete the history row.', 'classified-listing' ) ] );
		}

		wp_send_json_success( [ 'message' => __( 'History row deleted.', 'classified-listing' ) ] );
	}

	public function bulk_delete(): void {
		$this->guard();

		$raw = $_POST['ids'] ?? [];
		if ( ! is_array( $raw ) ) {
			$raw = [];
		}
		$ids = array_map( 'intval', $raw );

		if ( empty( $ids ) ) {
			wp_send_json_error( [ 'message' => __( 'No rows selected.', 'classified-listing' ) ] );
		}

		$deleted = ImportHistory::delete_bulk( $ids );

		wp_send_json_success( [
			'deleted' => $deleted,
			'message' => sprintf(
				/* translators: %d: number of history rows deleted */
				_n( 'Deleted %d history row.', 'Deleted %d history rows.', $deleted, 'classified-listing' ),
				$deleted
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
