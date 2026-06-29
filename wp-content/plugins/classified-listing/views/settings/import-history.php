<?php
/**
 * Import History tab.
 *
 * Read-only paginated view of the rtcl_import_history table. The only mutating
 * action exposed here is "Run again" for RSS rows — it POSTs to the existing
 * rtcl_import_rss_run endpoint with the source_id pulled from the history
 * row's params blob. Markup restyled to match the card-based UI; all classes
 * / ids that the inline JS hooks onto are preserved.
 */

defined( 'ABSPATH' ) || exit;

use Rtcl\Services\Importers\ImportHistory;

$nonce       = wp_create_nonce( rtcl()->nonceText );
$nonce_field = rtcl()->nonceId;

$per_page = 20;
$page     = max( 1, isset( $_GET['paged'] ) ? (int) $_GET['paged'] : 1 );
$type_f   = isset( $_GET['source_type'] ) ? sanitize_key( wp_unslash( $_GET['source_type'] ) ) : '';
$status_f = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '';

$args = [
	'source_type' => $type_f,
	'status'      => $status_f,
	'limit'       => $per_page,
	'offset'      => ( $page - 1 ) * $per_page,
];

$runs        = ImportHistory::list( $args );
$total_runs  = ImportHistory::count( $args );
$total_pages = $per_page > 0 ? (int) ceil( $total_runs / $per_page ) : 1;

$type_labels = [
	'csv'           => __( 'CSV', 'classified-listing' ),
	'rss'           => __( 'RSS', 'classified-listing' ),
	'google_places' => __( 'Google Places', 'classified-listing' ),
	'google_search' => __( 'Google Places', 'classified-listing' ),
];
$status_labels = [
	'running' => __( 'Running', 'classified-listing' ),
	'success' => __( 'Success', 'classified-listing' ),
	'partial' => __( 'Partial', 'classified-listing' ),
	'failed'  => __( 'Failed', 'classified-listing' ),
];
$status_colors = [
	'running' => '#777',
	'success' => '#46b450',
	'partial' => '#dba617',
	'failed'  => '#dc3232',
];
$status_kinds = [
	'running' => 'muted',
	'success' => 'success',
	'partial' => 'warning',
	'failed'  => 'critical',
];

$base_url = add_query_arg( [ 'page' => 'rtcl-import-export', 'tab' => 'history' ], admin_url( 'admin.php' ) );

if ( ! function_exists( 'rtcl_import_history_page_url' ) ) {
	/**
	 * Build a filter-preserving URL for the history-tab pagination links.
	 *
	 * @param string $base_url The current history-tab admin URL.
	 * @param int    $page     Page number to link to.
	 * @param string $type_f   Currently selected source-type filter.
	 * @param string $status_f Currently selected status filter.
	 */
	function rtcl_import_history_page_url( $base_url, $page, $type_f, $status_f ) {
		return esc_url( add_query_arg( array_filter( [
			'paged'       => $page,
			'source_type' => $type_f,
			'status'      => $status_f,
		], static function ( $v ) { return $v !== '' && $v !== 0; } ), $base_url ) );
	}
}
?>
<div class="rtcl-import-export rtcl rtcl-import-history rtcl-ie-panel">

	<section class="rtcl-ie-card">
		<header class="rtcl-ie-card-head">
			<span class="rtcl-ie-card-ico"><?php rtcl_ie_icon( 'history' ); ?></span>
			<div>
				<h2><?php esc_html_e( 'Import History', 'classified-listing' ); ?></h2>
				<p><?php esc_html_e( 'Every import run, with counts and per-run actions.', 'classified-listing' ); ?></p>
			</div>
		</header>
		<div class="rtcl-ie-card-body">

			<form method="get" class="rtcl-history-filters rtcl-ie-filterbar">
				<input type="hidden" name="page" value="rtcl-import-export">
				<input type="hidden" name="tab" value="history">

				<span class="rtcl-ie-fb-label"><?php esc_html_e( 'Source', 'classified-listing' ); ?></span>
				<select name="source_type" class="rtcl-ie-select">
					<option value=""><?php esc_html_e( 'All sources', 'classified-listing' ); ?></option>
					<?php foreach ( $type_labels as $k => $v ) : if ( 'google_search' === $k ) { continue; } ?>
						<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $type_f, $k ); ?>><?php echo esc_html( $v ); ?></option>
					<?php endforeach; ?>
				</select>

				<span class="rtcl-ie-fb-label"><?php esc_html_e( 'Status', 'classified-listing' ); ?></span>
				<select name="status" class="rtcl-ie-select">
					<option value=""><?php esc_html_e( 'All statuses', 'classified-listing' ); ?></option>
					<?php foreach ( $status_labels as $k => $v ) : ?>
						<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $status_f, $k ); ?>><?php echo esc_html( $v ); ?></option>
					<?php endforeach; ?>
				</select>

				<button type="submit" class="button rtcl-ie-btn rtcl-ie-btn-sm">
					<?php rtcl_ie_icon( 'filter' ); ?>
					<?php esc_html_e( 'Filter', 'classified-listing' ); ?>
				</button>
				<?php if ( $type_f || $status_f ) : ?>
					<a class="button-link rtcl-ie-link" href="<?php echo esc_url( $base_url ); ?>"><?php esc_html_e( 'Reset', 'classified-listing' ); ?></a>
				<?php endif; ?>

				<span class="rtcl-ie-fb-count rtcl-history-total">
					<?php
					printf(
						/* translators: %d: total number of runs */
						esc_html( _n( '%d run', '%d runs', $total_runs, 'classified-listing' ) ),
						(int) $total_runs
					);
					?>
				</span>
			</form>

			<?php if ( ! empty( $runs ) ) : ?>
				<div class="tablenav top rtcl-ie-bulkbar">
					<button type="button" class="button rtcl-ie-btn rtcl-ie-btn-sm rtcl-ie-btn-danger" id="rtcl-history-bulk-delete" disabled>
						<?php rtcl_ie_icon( 'trash' ); ?>
						<?php esc_html_e( 'Delete selected', 'classified-listing' ); ?>
					</button>
					<span class="rtcl-history-bulk-count description"></span>
				</div>
			<?php endif; ?>

			<table class="widefat striped rtcl-history-table rtcl-ie-table">
				<thead>
					<tr>
						<td class="check-column rtcl-ie-col-check"><input type="checkbox" id="rtcl-history-select-all" title="<?php esc_attr_e( 'Select all on this page', 'classified-listing' ); ?>"></td>
						<th><?php esc_html_e( 'Started', 'classified-listing' ); ?></th>
						<th><?php esc_html_e( 'Source', 'classified-listing' ); ?></th>
						<th><?php esc_html_e( 'Key', 'classified-listing' ); ?></th>
						<th><?php esc_html_e( 'Status', 'classified-listing' ); ?></th>
						<th class="rtcl-ie-num"><?php esc_html_e( 'In', 'classified-listing' ); ?></th>
						<th class="rtcl-ie-num"><?php esc_html_e( 'Upd', 'classified-listing' ); ?></th>
						<th class="rtcl-ie-num"><?php esc_html_e( 'Skip', 'classified-listing' ); ?></th>
						<th class="rtcl-ie-num"><?php esc_html_e( 'Errs', 'classified-listing' ); ?></th>
						<th class="rtcl-ie-col-actions"><?php esc_html_e( 'Actions', 'classified-listing' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $runs ) ) : ?>
						<tr><td colspan="10"><?php esc_html_e( 'No import runs recorded yet.', 'classified-listing' ); ?></td></tr>
					<?php else : ?>
						<?php foreach ( $runs as $row ) :
							$status = (string) $row->status;
							$kind   = $status_kinds[ $status ] ?? 'muted';
							$color  = $status_colors[ $status ] ?? '#777';
							$errors = $row->errors ? (array) json_decode( $row->errors, true ) : [];
							$params = $row->params ? (array) json_decode( $row->params, true ) : [];
							$rss_source_id = ( 'rss' === $row->source_type && ! empty( $params['source_id'] ) ) ? (int) $params['source_id'] : 0;
							$processed = (int) $row->imported + (int) $row->updated + (int) $row->skipped;
							$row_total = max( (int) $row->total, $processed );
							$pct       = $row_total > 0 ? min( 100, (int) floor( $processed / $row_total * 100 ) ) : 0;
							?>
							<tr data-run-id="<?php echo (int) $row->id; ?>" data-status="<?php echo esc_attr( $status ); ?>">
								<th scope="row" class="check-column rtcl-ie-col-check">
									<input type="checkbox" class="rtcl-history-pick" value="<?php echo (int) $row->id; ?>">
								</th>
								<td class="rtcl-ie-cell-muted">
									<span title="<?php echo esc_attr( $row->started_at ); ?>"><?php echo esc_html( $row->started_at ); ?></span>
								</td>
								<td><?php echo esc_html( $type_labels[ $row->source_type ] ?? $row->source_type ); ?></td>
								<td class="rtcl-ie-cell-truncate">
									<?php
									if ( $row->source_key ) {
										if ( filter_var( $row->source_key, FILTER_VALIDATE_URL ) ) {
											echo '<a class="rtcl-ie-link" href="' . esc_url( $row->source_key ) . '" target="_blank" rel="noopener">' . esc_html( $row->source_key ) . '</a>';
										} else {
											echo esc_html( $row->source_key );
										}
									} else {
										echo '—';
									}
									?>
								</td>
								<td>
									<span class="rtcl-history-status-badge rtcl-ie-tag rtcl-ie-tag-<?php echo esc_attr( $kind ); ?>" style="--rtcl-ie-tag-color:<?php echo esc_attr( $color ); ?>;">
										<?php echo esc_html( $status_labels[ $status ] ?? $status ); ?>
									</span>
									<div class="rtcl-history-progress" style="<?php echo 'running' === $status ? '' : 'display:none;'; ?>">
										<div class="rtcl-ie-progress">
											<span class="rtcl-history-bar" style="width:<?php echo (int) $pct; ?>%"></span>
										</div>
										<small class="rtcl-history-progress-text"><?php echo (int) $processed . ' / ' . (int) $row_total; ?></small>
									</div>
								</td>
								<td class="rtcl-history-imported rtcl-ie-num"><?php echo (int) $row->imported; ?></td>
								<td class="rtcl-history-updated rtcl-ie-num"><?php echo (int) $row->updated; ?></td>
								<td class="rtcl-history-skipped rtcl-ie-num"><?php echo (int) $row->skipped; ?></td>
								<td class="rtcl-history-errs rtcl-ie-num"><?php echo count( $errors ); ?></td>
								<td class="rtcl-ie-col-actions">
									<div class="rtcl-ie-row-actions">
										<?php if ( $rss_source_id > 0 ) : ?>
											<button type="button" class="button button-small rtcl-ie-btn rtcl-ie-btn-sm rtcl-history-rerun" data-source-id="<?php echo (int) $rss_source_id; ?>">
												<?php rtcl_ie_icon( 'play' ); ?>
												<?php esc_html_e( 'Run again', 'classified-listing' ); ?>
											</button>
										<?php endif; ?>
										<button type="button" class="button button-small button-link-delete rtcl-ie-btn rtcl-ie-btn-sm rtcl-ie-btn-danger rtcl-history-delete" data-id="<?php echo (int) $row->id; ?>">
											<?php rtcl_ie_icon( 'trash' ); ?>
											<?php esc_html_e( 'Delete', 'classified-listing' ); ?>
										</button>
									</div>
									<?php if ( ! empty( $errors ) ) : ?>
										<details class="rtcl-ie-errlist">
											<summary>
												<?php
												printf(
													/* translators: %d: error count */
													esc_html( _n( '%d error', '%d errors', count( $errors ), 'classified-listing' ) ),
													count( $errors )
												);
												?>
											</summary>
											<ul>
												<?php foreach ( $errors as $err ) : ?>
													<li><?php echo esc_html( is_string( $err ) ? $err : wp_json_encode( $err ) ); ?></li>
												<?php endforeach; ?>
											</ul>
										</details>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>

			<?php if ( $total_pages > 1 ) : ?>
				<div class="tablenav rtcl-ie-pager">
					<div class="tablenav-pages">
						<?php
						$prev = max( 1, $page - 1 );
						$next = min( $total_pages, $page + 1 );
						?>
						<a class="button rtcl-ie-btn rtcl-ie-btn-sm<?php echo $page === 1 ? ' disabled' : ''; ?>"
							href="<?php echo rtcl_import_history_page_url( $base_url, $prev, $type_f, $status_f ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_url applied inside helper. ?>">‹</a>
						<span class="rtcl-ie-pager-label">
							<?php
							printf(
								/* translators: 1: current page 2: total pages */
								esc_html__( 'Page %1$d of %2$d', 'classified-listing' ),
								(int) $page,
								(int) $total_pages
							);
							?>
						</span>
						<a class="button rtcl-ie-btn rtcl-ie-btn-sm<?php echo $page === $total_pages ? ' disabled' : ''; ?>"
							href="<?php echo rtcl_import_history_page_url( $base_url, $next, $type_f, $status_f ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_url applied inside helper. ?>">›</a>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</section>
</div>

<script>
( function ( $ ) {
	'use strict';

	var ajaxUrl    = '<?php echo esc_url_raw( admin_url( 'admin-ajax.php' ) ); ?>';
	var nonceField = <?php echo wp_json_encode( $nonce_field ); ?>;
	var nonce      = <?php echo wp_json_encode( $nonce ); ?>;

	var STATUS_LABELS = <?php echo wp_json_encode( $status_labels ); ?>;
	var STATUS_COLORS = <?php echo wp_json_encode( $status_colors ); ?>;

	/**
	 * Attach the request nonce to the given payload.
	 */
	function withNonce( data ) {
		data = data || {};
		data[ nonceField ] = nonce;
		return data;
	}

	/**
	 * Poll the progress endpoint for a single running row and update its
	 * counters / bar in place.
	 */
	function pollRunningRow( $row ) {
		var runId = parseInt( $row.data( 'run-id' ), 10 );
		if ( ! runId ) { return; }

		$.post( ajaxUrl, withNonce( { action: 'rtcl_import_progress', run_id: runId } ), function ( res ) {
			if ( ! res || ! res.success ) { return; }
			var p = res.data;

			$row.find( '.rtcl-history-imported' ).text( p.imported | 0 );
			$row.find( '.rtcl-history-updated' ).text( p.updated | 0 );
			$row.find( '.rtcl-history-skipped' ).text( p.skipped | 0 );
			$row.find( '.rtcl-history-errs' ).text( ( p.errors && p.errors.length ) | 0 );
			$row.find( '.rtcl-history-bar' ).css( 'width', ( p.percent | 0 ) + '%' );
			$row.find( '.rtcl-history-progress-text' ).text( ( p.processed | 0 ) + ' / ' + ( p.total | 0 ) );

			if ( p.done ) {
				$row.attr( 'data-status', p.status );
				var label = STATUS_LABELS[ p.status ] || p.status;
				var color = STATUS_COLORS[ p.status ] || '#777';
				$row.find( '.rtcl-history-status-badge' ).text( label ).css( '--rtcl-ie-tag-color', color );
				$row.find( '.rtcl-history-progress' ).hide();
			}
		} );
	}

	/**
	 * Poll every row whose status is "running" and stop the timer when none remain.
	 */
	function tickProgress() {
		var $running = $( 'tr[data-status="running"]' );
		if ( ! $running.length ) {
			if ( progressInterval ) { window.clearInterval( progressInterval ); progressInterval = null; }
			return;
		}
		$running.each( function () { pollRunningRow( $( this ) ); } );
	}

	var progressInterval = null;
	if ( $( 'tr[data-status="running"]' ).length ) {
		tickProgress();
		progressInterval = window.setInterval( tickProgress, 3000 );
	}

	$( document ).on( 'click', '.rtcl-history-rerun', function () {
		var $btn = $( this );
		var sourceId = $btn.data( 'source-id' );
		if ( ! sourceId ) { return; }

		$btn.prop( 'disabled', true );

		var data = { action: 'rtcl_import_rss_run', id: sourceId };
		data[ nonceField ] = nonce;

		$.post( ajaxUrl, data, function ( res ) {
			$btn.prop( 'disabled', false );
			if ( res && res.success ) {
				alert( res.data.message );
				window.location.reload();
			} else {
				alert( ( res && res.data && res.data.message ) || 'Run failed.' );
			}
		} ).fail( function () {
			$btn.prop( 'disabled', false );
			alert( 'Network error.' );
		} );
	} );

	/**
	 * Sync the bulk-delete button and select-all checkbox with the picked rows.
	 */
	function updateBulkSelectionUI() {
		var n = $( '.rtcl-history-pick:checked' ).length;
		var total = $( '.rtcl-history-pick' ).length;
		$( '#rtcl-history-bulk-delete' ).prop( 'disabled', n === 0 );
		$( '.rtcl-history-bulk-count' ).text( n ? n + ' selected' : '' );
		var $all = $( '#rtcl-history-select-all' );
		if ( total === 0 ) {
			$all.prop( 'checked', false ).prop( 'indeterminate', false );
		} else if ( n === 0 ) {
			$all.prop( 'checked', false ).prop( 'indeterminate', false );
		} else if ( n === total ) {
			$all.prop( 'checked', true ).prop( 'indeterminate', false );
		} else {
			$all.prop( 'checked', false ).prop( 'indeterminate', true );
		}
	}

	$( '#rtcl-history-select-all' ).on( 'change', function () {
		$( '.rtcl-history-pick' ).prop( 'checked', this.checked );
		updateBulkSelectionUI();
	} );

	$( document ).on( 'change', '.rtcl-history-pick', updateBulkSelectionUI );

	$( '#rtcl-history-bulk-delete' ).on( 'click', function () {
		var $btn = $( this );
		var ids = $( '.rtcl-history-pick:checked' ).map( function () { return parseInt( this.value, 10 ); } ).get();
		if ( ! ids.length ) { return; }

		if ( ! confirm( 'Delete ' + ids.length + ' history row(s)? The imported listings are not affected — only the run records are removed.' ) ) {
			return;
		}

		$btn.prop( 'disabled', true );

		var payload = { action: 'rtcl_import_history_bulk_delete' };
		payload[ nonceField ] = nonce;
		ids.forEach( function ( id, i ) { payload[ 'ids[' + i + ']' ] = id; } );

		$.post( ajaxUrl, payload, function ( res ) {
			if ( res && res.success ) {
				ids.forEach( function ( id ) {
					$( 'tr[data-run-id="' + id + '"]' ).fadeOut( 200, function () { $( this ).remove(); updateBulkSelectionUI(); } );
				} );
			} else {
				$btn.prop( 'disabled', false );
				alert( ( res && res.data && res.data.message ) || 'Bulk delete failed.' );
			}
		} ).fail( function () {
			$btn.prop( 'disabled', false );
			alert( 'Network error.' );
		} );
	} );

	$( document ).on( 'click', '.rtcl-history-delete', function () {
		var $btn = $( this );
		var id = $btn.data( 'id' );
		if ( ! id ) { return; }
		if ( ! confirm( 'Delete this history row? The imported listings are not affected — only the run record is removed.' ) ) {
			return;
		}

		$btn.prop( 'disabled', true );

		var data = { action: 'rtcl_import_history_delete', id: id };
		data[ nonceField ] = nonce;

		$.post( ajaxUrl, data, function ( res ) {
			if ( res && res.success ) {
				$btn.closest( 'tr' ).fadeOut( 200, function () { $( this ).remove(); } );
			} else {
				$btn.prop( 'disabled', false );
				alert( ( res && res.data && res.data.message ) || 'Delete failed.' );
			}
		} ).fail( function () {
			$btn.prop( 'disabled', false );
			alert( 'Network error.' );
		} );
	} );

} )( jQuery );
</script>
