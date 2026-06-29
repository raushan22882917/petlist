<?php
/**
 * RSS / Atom import tab.
 *
 * Server-renders the saved-feeds table; add/edit/delete/run-now happen via the
 * AJAX endpoints in Rtcl\Controllers\Ajax\ImportSources. Markup restyled to
 * match the new card-based Export / Import design while preserving every id
 * and class the inline jQuery hooks onto.
 */

defined( 'ABSPATH' ) || exit;

use Rtcl\Services\Importers\SourceRepository;

$sources       = SourceRepository::list( [ 'source_type' => 'rss', 'limit' => 100 ] );
$nonce         = wp_create_nonce( rtcl()->nonceText );
$nonce_field   = rtcl()->nonceId;
$status_opts   = [
	'publish' => __( 'Published', 'classified-listing' ),
	'pending' => __( 'Pending Review', 'classified-listing' ),
	'draft'   => __( 'Draft', 'classified-listing' ),
];
$schedule_opts = [
	'off'        => __( 'Off (manual only)', 'classified-listing' ),
	'hourly'     => __( 'Hourly', 'classified-listing' ),
	'twicedaily' => __( 'Twice daily', 'classified-listing' ),
	'daily'      => __( 'Daily', 'classified-listing' ),
	'weekly'     => __( 'Weekly', 'classified-listing' ),
];
?>
<div class="rtcl-import-export rtcl rtcl-import-rss rtcl-ie-panel">

	<section class="rtcl-ie-card">
		<header class="rtcl-ie-card-head">
			<span class="rtcl-ie-card-ico"><?php rtcl_ie_icon( 'rss' ); ?></span>
			<div>
				<h2><?php esc_html_e( 'Add an RSS / Atom Feed', 'classified-listing' ); ?></h2>
				<p><?php esc_html_e( 'Pull listings automatically from any RSS or Atom source.', 'classified-listing' ); ?></p>
			</div>
		</header>
		<div class="rtcl-ie-card-body">
			<form class="rtcl-rss-form rtcl-ie-form" id="rtcl-rss-source-form">
				<input type="hidden" name="id" value="0">
				<input type="hidden" name="<?php echo esc_attr( $nonce_field ); ?>" value="<?php echo esc_attr( $nonce ); ?>">

				<div class="rtcl-ie-frow">
					<div class="rtcl-ie-flabel"><label for="rtcl-rss-url"><?php esc_html_e( 'Feed URL', 'classified-listing' ); ?></label></div>
					<div class="rtcl-ie-fcontrol">
						<input type="url" name="url" id="rtcl-rss-url" class="rtcl-ie-input regular-text" placeholder="https://example.com/feed" required>
					</div>
				</div>

				<div class="rtcl-ie-frow">
					<div class="rtcl-ie-flabel">
						<label for="rtcl-rss-label"><?php esc_html_e( 'Label', 'classified-listing' ); ?></label>
						<span class="rtcl-ie-flabel-sub rtcl-ie-opt"><?php esc_html_e( 'optional', 'classified-listing' ); ?></span>
					</div>
					<div class="rtcl-ie-fcontrol">
						<input type="text" name="label" id="rtcl-rss-label" class="rtcl-ie-input regular-text" placeholder="<?php esc_attr_e( 'Friendly name', 'classified-listing' ); ?>">
					</div>
				</div>

				<div class="rtcl-ie-frow">
					<div class="rtcl-ie-flabel"><label for="rtcl-rss-schedule"><?php esc_html_e( 'Schedule', 'classified-listing' ); ?></label></div>
					<div class="rtcl-ie-fcontrol">
						<select name="schedule" id="rtcl-rss-schedule" class="rtcl-ie-select">
							<?php foreach ( $schedule_opts as $k => $v ) : ?>
								<option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $v ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>

				<div class="rtcl-ie-frow">
					<div class="rtcl-ie-flabel"><label for="rtcl-rss-status"><?php esc_html_e( 'Listing Status', 'classified-listing' ); ?></label></div>
					<div class="rtcl-ie-fcontrol">
						<select name="target_status" id="rtcl-rss-status" class="rtcl-ie-select">
							<option value=""><?php esc_html_e( 'Use global default', 'classified-listing' ); ?></option>
							<?php foreach ( $status_opts as $k => $v ) : ?>
								<option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $v ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>

				<div class="rtcl-ie-frow">
					<div class="rtcl-ie-flabel"><label for="rtcl-rss-category"><?php esc_html_e( 'Default Category', 'classified-listing' ); ?></label></div>
					<div class="rtcl-ie-fcontrol">
						<?php
						wp_dropdown_categories( [
							'taxonomy'          => rtcl()->category,
							'name'              => 'target_category',
							'id'                => 'rtcl-rss-category',
							'class'             => 'rtcl-ie-select',
							'show_option_none'  => __( '— None —', 'classified-listing' ),
							'option_none_value' => 0,
							'hide_empty'        => 0,
							'hierarchical'      => 1,
							'orderby'           => 'name',
						] );
						?>
					</div>
				</div>

				<div class="rtcl-ie-frow">
					<div class="rtcl-ie-flabel"><label for="rtcl-rss-location"><?php esc_html_e( 'Default Location', 'classified-listing' ); ?></label></div>
					<div class="rtcl-ie-fcontrol">
						<?php
						wp_dropdown_categories( [
							'taxonomy'          => rtcl()->location,
							'name'              => 'target_location',
							'id'                => 'rtcl-rss-location',
							'class'             => 'rtcl-ie-select',
							'show_option_none'  => __( '— None —', 'classified-listing' ),
							'option_none_value' => 0,
							'hide_empty'        => 0,
							'hierarchical'      => 1,
							'orderby'           => 'name',
						] );
						?>
					</div>
				</div>

				<div class="rtcl-ie-frow">
					<div class="rtcl-ie-flabel"><label><?php esc_html_e( 'Re-import behavior', 'classified-listing' ); ?></label></div>
					<div class="rtcl-ie-fcontrol">
						<div class="rtcl-ie-opt-row">
							<div class="rtcl-ie-opt-text">
								<div class="rtcl-ie-opt-title"><?php esc_html_e( 'Update existing listings on re-import', 'classified-listing' ); ?></div>
								<div class="rtcl-ie-opt-desc"><?php esc_html_e( 'Dedupe by source + GUID so re-runs refresh existing listings instead of creating duplicates.', 'classified-listing' ); ?></div>
							</div>
							<label class="rtcl-ie-switch">
								<input type="checkbox" name="update_existing" value="1">
								<span class="rtcl-ie-switch-track"></span>
							</label>
						</div>
					</div>
				</div>

				<div class="rtcl-ie-frow">
					<div class="rtcl-ie-flabel"></div>
					<div class="rtcl-ie-fcontrol rtcl-ie-actrow" id="rtcl-import-wrap">
						<button type="button" class="rtcl-btn rtcl-btn-secondary rtcl-ie-btn" id="rtcl-rss-test-btn">
							<?php rtcl_ie_icon( 'flask' ); ?>
							<?php esc_html_e( 'Test feed', 'classified-listing' ); ?>
						</button>
						<button type="submit" class="rtcl-btn rtcl-btn-primary rtcl-ie-btn rtcl-ie-btn-primary" id="rtcl-rss-save-btn">
							<?php rtcl_ie_icon( 'save' ); ?>
							<?php esc_html_e( 'Save feed', 'classified-listing' ); ?>
						</button>
					</div>
				</div>
			</form>

			<div id="rtcl-rss-form-response" class="rtcl-rss-form-response"></div>
			<div id="rtcl-rss-preview" class="rtcl-rss-preview"></div>
		</div>
	</section>

	<section class="rtcl-ie-card">
		<header class="rtcl-ie-card-head">
			<span class="rtcl-ie-card-ico"><?php rtcl_ie_icon( 'list' ); ?></span>
			<div>
				<h2><?php esc_html_e( 'Saved Feeds', 'classified-listing' ); ?></h2>
				<p><?php esc_html_e( "Feeds you've added — run them on demand or remove them.", 'classified-listing' ); ?></p>
			</div>
		</header>
		<div class="rtcl-ie-card-body">
			<table class="widefat striped rtcl-rss-table rtcl-ie-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Label', 'classified-listing' ); ?></th>
						<th><?php esc_html_e( 'URL', 'classified-listing' ); ?></th>
						<th><?php esc_html_e( 'Schedule', 'classified-listing' ); ?></th>
						<th><?php esc_html_e( 'Last Run', 'classified-listing' ); ?></th>
						<th class="rtcl-ie-col-actions"><?php esc_html_e( 'Actions', 'classified-listing' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $sources ) ) : ?>
						<tr><td colspan="5"><?php esc_html_e( 'No saved feeds yet.', 'classified-listing' ); ?></td></tr>
					<?php else : ?>
						<?php foreach ( $sources as $s ) : ?>
							<tr data-id="<?php echo (int) $s->id; ?>">
								<td><?php echo esc_html( $s->label ?: '—' ); ?></td>
								<td><a class="rtcl-ie-link" href="<?php echo esc_url( $s->url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $s->url ); ?></a></td>
								<td><span class="rtcl-ie-tag rtcl-ie-tag-muted"><?php echo esc_html( $schedule_opts[ $s->schedule ] ?? $s->schedule ); ?></span></td>
								<td class="rtcl-ie-cell-muted"><?php echo esc_html( $s->last_run_at ?: '—' ); ?></td>
								<td class="rtcl-ie-col-actions">
									<div class="rtcl-ie-row-actions">
										<button type="button" class="button button-small rtcl-ie-btn rtcl-ie-btn-sm rtcl-rss-run" data-id="<?php echo (int) $s->id; ?>">
											<?php rtcl_ie_icon( 'play' ); ?>
											<?php esc_html_e( 'Run now', 'classified-listing' ); ?>
										</button>
										<button type="button" class="button button-link-delete button-small rtcl-ie-btn rtcl-ie-btn-sm rtcl-ie-btn-danger rtcl-rss-delete" data-id="<?php echo (int) $s->id; ?>">
											<?php rtcl_ie_icon( 'trash' ); ?>
											<?php esc_html_e( 'Delete', 'classified-listing' ); ?>
										</button>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</section>
</div>
