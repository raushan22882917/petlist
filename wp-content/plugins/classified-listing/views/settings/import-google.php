<?php
/**
 * Google Places import tab.
 *
 * Two-step flow: search (cheap) → preview grid → import selected (expensive).
 * Markup restyled to match the card-based Export / Import UI. Every id and
 * class hooked by the inline jQuery and by ImportGoogle.php is preserved.
 */

defined( 'ABSPATH' ) || exit;

use Rtcl\Helpers\Functions;
use Rtcl\Models\Form\Form;

$api_key       = (string) Functions::get_option_item( 'rtcl_import_settings', 'google_places_api_key', '' );
// Key that renders the bias map. Prefer the Maps-JS-authorized "Google Map API
// Key"; fall back to the Places import key. Must mirror ScriptLoader so the map
// markup is only shown when a key was actually enqueued. See
// load_admin_script_export_import_page().
$map_picker_key = (string) Functions::get_option_item( 'rtcl_misc_map_settings', 'map_api_key', '' );
if ( '' === $map_picker_key ) {
	$map_picker_key = $api_key;
}
$nonce         = wp_create_nonce( rtcl()->nonceText );
$nonce_field   = rtcl()->nonceId;
$status_opts   = [
	'publish' => __( 'Published', 'classified-listing' ),
	'pending' => __( 'Pending Review', 'classified-listing' ),
	'draft'   => __( 'Draft', 'classified-listing' ),
];

$forms           = Form::query()->where( 'status', 'publish' )->order_by( 'created_at', 'DESC' )->get();
$default_form    = Form::query()->where( 'default', 1 )->one();
$default_form_id = ( $default_form && ! empty( $default_form->id ) ) ? (int) $default_form->id : 0;
$ai_enabled      = Functions::is_ai_enabled();
$fallback_image  = (string) Functions::get_option_item( 'rtcl_import_settings', 'default_fallback_image_url', '' );
?>
<div class="rtcl-import-export rtcl rtcl-import-google rtcl-ie-panel">

	<?php if ( '' === $api_key ) : ?>
		<div class="rtcl-ie-banner rtcl-ie-banner-warning">
			<?php rtcl_ie_icon( 'alert-triangle' ); ?>
			<div>
				<div class="rtcl-ie-banner-title"><?php esc_html_e( 'Google Places API key missing', 'classified-listing' ); ?></div>
				<div class="rtcl-ie-banner-desc">
					<?php
					printf(
						/* translators: %s: settings page URL */
						wp_kses(
							__( 'Add the key in <a href="%s">Settings → Import</a> before searching.', 'classified-listing' ),
							[ 'a' => [ 'href' => [] ] ]
						),
						esc_url( admin_url( 'admin.php?page=rtcl-settings&parentId=rtcl_import_settings' ) )
					);
					?>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<section class="rtcl-ie-card">
		<header class="rtcl-ie-card-head">
			<span class="rtcl-ie-card-ico"><?php rtcl_ie_icon( 'search' ); ?></span>
			<div>
				<h2><?php esc_html_e( 'Search Google Places', 'classified-listing' ); ?></h2>
				<p><?php esc_html_e( 'Find businesses on Google, then choose which ones to import as listings.', 'classified-listing' ); ?></p>
			</div>
		</header>
		<div class="rtcl-ie-card-body">

			<form class="rtcl-google-form rtcl-ie-form" id="rtcl-google-search-form">
				<input type="hidden" name="<?php echo esc_attr( $nonce_field ); ?>" value="<?php echo esc_attr( $nonce ); ?>">

				<div class="rtcl-ie-frow">
					<div class="rtcl-ie-flabel"><label for="rtcl-google-query"><?php esc_html_e( 'Search', 'classified-listing' ); ?></label></div>
					<div class="rtcl-ie-fcontrol">
						<input type="text" name="query" id="rtcl-google-query" class="rtcl-ie-input regular-text" placeholder="<?php esc_attr_e( 'e.g. pizza in Manhattan, plumber Austin TX', 'classified-listing' ); ?>" required>
					</div>
				</div>

				<div class="rtcl-ie-frow">
					<div class="rtcl-ie-flabel">
						<label for="rtcl-google-region"><?php esc_html_e( 'Region', 'classified-listing' ); ?></label>
						<span class="rtcl-ie-flabel-sub">
							<?php esc_html_e( 'ISO-3166-1 α-2', 'classified-listing' ); ?>
							<span class="rtcl-ie-opt">· <?php esc_html_e( 'optional', 'classified-listing' ); ?></span>
						</span>
					</div>
					<div class="rtcl-ie-fcontrol">
						<input type="text" name="region" id="rtcl-google-region" class="rtcl-ie-input regular-text rtcl-ie-maxw-220" maxlength="2" placeholder="<?php esc_attr_e( 'us, gb, bd…', 'classified-listing' ); ?>">
					</div>
				</div>

				<div class="rtcl-ie-frow">
					<div class="rtcl-ie-flabel">
						<label><?php esc_html_e( 'Bias by location', 'classified-listing' ); ?></label>
						<span class="rtcl-ie-flabel-sub rtcl-ie-opt"><?php esc_html_e( 'optional', 'classified-listing' ); ?></span>
					</div>
					<div class="rtcl-ie-fcontrol">
						<?php if ( '' !== $map_picker_key ) : ?>
							<div class="rtcl-ie-map-wrap">
								<div id="rtcl-google-map-fallback" class="notice notice-warning inline rtcl-ie-map-fallback" style="display:none;">
									<p>
										<?php esc_html_e( 'The map could not be loaded. Your Google API key needs the “Maps JavaScript API” (and “Places API”) enabled in Google Cloud. You can still set the location bias by typing the latitude, longitude and radius below.', 'classified-listing' ); ?>
									</p>
								</div>
								<div id="rtcl-google-map-searchbar" class="rtcl-google-map-searchbar rtcl-ie-map-search">
									<input type="text" id="rtcl-google-map-search" class="rtcl-ie-input regular-text" autocomplete="off" placeholder="<?php esc_attr_e( 'Search a place or address (e.g. Dhaka) and press Enter…', 'classified-listing' ); ?>">
									<button type="button" class="button rtcl-ie-btn rtcl-ie-btn-sm" id="rtcl-google-map-locate">
										<?php rtcl_ie_icon( 'crosshair' ); ?>
										<?php esc_html_e( 'Locate', 'classified-listing' ); ?>
									</button>
								</div>
								<span id="rtcl-google-map-msg" class="rtcl-google-map-msg"></span>
								<div id="rtcl-google-bias-map" class="rtcl-google-bias-map rtcl-ie-map-canvas"></div>
							</div>
							<p class="rtcl-ie-hint description" id="rtcl-google-map-hint"><?php esc_html_e( 'Type a place name and press Enter (or click Locate), or click / drag the pin to set the center. Drag the circle edge or change the radius to set how far around it to search. Leave the fields empty for no location bias.', 'classified-listing' ); ?></p>
						<?php endif; ?>
						<div class="rtcl-google-bias-fields rtcl-ie-coord-row">
							<label class="rtcl-ie-coord"><span><?php esc_html_e( 'Lat', 'classified-listing' ); ?></span>
								<input type="text" name="lat" id="rtcl-google-lat" class="rtcl-ie-input" placeholder="<?php esc_attr_e( 'Latitude', 'classified-listing' ); ?>">
							</label>
							<label class="rtcl-ie-coord"><span><?php esc_html_e( 'Lng', 'classified-listing' ); ?></span>
								<input type="text" name="lng" id="rtcl-google-lng" class="rtcl-ie-input" placeholder="<?php esc_attr_e( 'Longitude', 'classified-listing' ); ?>">
							</label>
							<label class="rtcl-ie-coord"><span><?php esc_html_e( 'Radius (m)', 'classified-listing' ); ?></span>
								<input type="number" name="radius" id="rtcl-google-radius" class="rtcl-ie-input" placeholder="<?php esc_attr_e( 'Radius (m)', 'classified-listing' ); ?>" value="5000" min="1" max="50000">
							</label>
							<button type="button" class="button rtcl-ie-btn rtcl-ie-btn-sm" id="rtcl-google-bias-clear">
								<?php rtcl_ie_icon( 'x' ); ?>
								<?php esc_html_e( 'Clear', 'classified-listing' ); ?>
							</button>
						</div>
					</div>
				</div>

				<div class="rtcl-ie-frow">
					<div class="rtcl-ie-flabel"><label for="rtcl-google-limit"><?php esc_html_e( 'Max results', 'classified-listing' ); ?></label></div>
					<div class="rtcl-ie-fcontrol">
						<input type="number" name="limit" id="rtcl-google-limit" class="rtcl-ie-input rtcl-ie-maxw-120" value="20" min="1" max="60">
						<p class="rtcl-ie-hint description"><?php esc_html_e( 'Google caps Text Search at 60 results per query (3 pages × 20). Higher values make multiple billed calls.', 'classified-listing' ); ?></p>
					</div>
				</div>

				<div class="rtcl-ie-frow">
					<div class="rtcl-ie-flabel"></div>
					<div class="rtcl-ie-fcontrol" id="rtcl-import-wrap">
						<button type="submit" class="rtcl-btn rtcl-btn-primary rtcl-ie-btn rtcl-ie-btn-primary" id="rtcl-google-search-btn">
							<?php rtcl_ie_icon( 'search' ); ?>
							<?php esc_html_e( 'Search', 'classified-listing' ); ?>
						</button>
					</div>
				</div>
			</form>

			<div id="rtcl-google-search-status" class="rtcl-google-status"></div>
		</div>
	</section>

	<div id="rtcl-google-results-wrap" class="rtcl-ie-results-wrap" style="display:none">

		<div class="rtcl-ie-sec-title">
			<h2><?php esc_html_e( 'Select places to import', 'classified-listing' ); ?></h2>
			<span class="rtcl-ie-sec-count" id="rtcl-google-selected-count"></span>
		</div>

		<div class="rtcl-ie-sel-toolbar">
			<label class="rtcl-ie-chk">
				<input type="checkbox" id="rtcl-google-select-all">
				<span class="rtcl-ie-chk-box"><?php rtcl_ie_icon( 'check' ); ?></span>
				<span><?php esc_html_e( 'Select / deselect all', 'classified-listing' ); ?></span>
			</label>
			<span class="rtcl-ie-badge-count" id="rtcl-google-badge-count"></span>
		</div>

		<div id="rtcl-google-results" class="rtcl-google-results rtcl-ie-places"></div>

		<section class="rtcl-ie-card">
			<header class="rtcl-ie-card-head">
				<span class="rtcl-ie-card-ico"><?php rtcl_ie_icon( 'git-merge' ); ?></span>
				<div>
					<h2><?php esc_html_e( 'Target Form & Field Mapping', 'classified-listing' ); ?></h2>
					<p><?php esc_html_e( 'Choose the form that receives imported data and match each Google field to it.', 'classified-listing' ); ?></p>
				</div>
			</header>
			<div class="rtcl-ie-card-body">
				<div class="rtcl-ie-frow">
					<div class="rtcl-ie-flabel">
						<label for="rtcl-google-form-picker"><?php esc_html_e( 'Target Form', 'classified-listing' ); ?></label>
						<span class="rtcl-ie-flabel-sub"><?php esc_html_e( 'Pick the form whose fields should receive the imported data', 'classified-listing' ); ?></span>
					</div>
					<div class="rtcl-ie-fcontrol">
						<select id="rtcl-google-form-picker" class="rtcl-ie-select rtcl-ie-maxw-280">
							<option value="0"><?php esc_html_e( '— Use default form —', 'classified-listing' ); ?></option>
							<?php if ( ! empty( $forms ) ) : foreach ( $forms as $f ) : ?>
								<option value="<?php echo (int) $f->id; ?>" <?php selected( $default_form_id, (int) $f->id ); ?>>
									<?php echo esc_html( $f->title ?: ( '#' . (int) $f->id ) ); ?>
									<?php if ( ! empty( $f->default ) ) : ?>
										(<?php esc_html_e( 'default', 'classified-listing' ); ?>)
									<?php endif; ?>
								</option>
							<?php endforeach; endif; ?>
						</select>
					</div>
				</div>

				<div id="rtcl-google-mapping-wrap" style="display:none">
					<div class="rtcl-mapping-toolbar rtcl-ie-map-tools">
						<button type="button" class="button rtcl-ie-btn rtcl-ie-btn-sm rtcl-ie-btn-magic" id="rtcl-google-ai-suggest" <?php echo $ai_enabled ? '' : 'disabled title="' . esc_attr__( 'Configure an AI provider under Settings → AI Integration to enable.', 'classified-listing' ) . '"'; ?>>
							<?php rtcl_ie_icon( 'sparkles' ); ?>
							<?php esc_html_e( 'Auto-suggest with AI', 'classified-listing' ); ?>
						</button>
						<button type="button" class="button rtcl-ie-btn rtcl-ie-btn-sm" id="rtcl-google-mapping-save">
							<?php rtcl_ie_icon( 'save' ); ?>
							<?php esc_html_e( 'Save mapping', 'classified-listing' ); ?>
						</button>
						<button type="button" class="button button-link-delete rtcl-ie-btn rtcl-ie-btn-sm" id="rtcl-google-mapping-reset">
							<?php rtcl_ie_icon( 'rotate-ccw' ); ?>
							<?php esc_html_e( 'Reset mapping', 'classified-listing' ); ?>
						</button>
						<span id="rtcl-google-mapping-status" class="description rtcl-ie-ai-note"></span>
					</div>

					<table class="widefat striped rtcl-ie-table rtcl-ie-mtable" id="rtcl-google-mapping-table">
						<thead>
							<tr>
								<th class="rtcl-ie-mtable-src"><?php esc_html_e( 'Google Places field', 'classified-listing' ); ?></th>
								<th class="rtcl-ie-mtable-arrow"></th>
								<th><?php esc_html_e( 'Form field', 'classified-listing' ); ?></th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>
			</div>
		</section>

		<form class="rtcl-google-form rtcl-ie-form" id="rtcl-google-import-form">
			<input type="hidden" name="<?php echo esc_attr( $nonce_field ); ?>" value="<?php echo esc_attr( $nonce ); ?>">

			<section class="rtcl-ie-card">
				<header class="rtcl-ie-card-head">
					<span class="rtcl-ie-card-ico"><?php rtcl_ie_icon( 'sliders' ); ?></span>
					<div>
						<h2><?php esc_html_e( 'Listing defaults', 'classified-listing' ); ?></h2>
						<p><?php esc_html_e( 'Applied to every imported listing.', 'classified-listing' ); ?></p>
					</div>
				</header>
				<div class="rtcl-ie-card-body">
					<div class="rtcl-ie-frow">
						<div class="rtcl-ie-flabel"><label for="rtcl-google-status"><?php esc_html_e( 'Listing Status', 'classified-listing' ); ?></label></div>
						<div class="rtcl-ie-fcontrol">
							<select name="target_status" id="rtcl-google-status" class="rtcl-ie-select rtcl-ie-maxw-220">
								<option value=""><?php esc_html_e( 'Use global default', 'classified-listing' ); ?></option>
								<?php foreach ( $status_opts as $k => $v ) : ?>
									<option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $v ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<div class="rtcl-ie-frow">
						<div class="rtcl-ie-flabel"><label for="rtcl-google-category"><?php esc_html_e( 'Default Category', 'classified-listing' ); ?></label></div>
						<div class="rtcl-ie-fcontrol">
							<?php
							wp_dropdown_categories( [
								'taxonomy'          => rtcl()->category,
								'name'              => 'target_category',
								'id'                => 'rtcl-google-category',
								'class'             => 'rtcl-ie-select rtcl-ie-maxw-220',
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
						<div class="rtcl-ie-flabel"><label for="rtcl-google-location"><?php esc_html_e( 'Default Location', 'classified-listing' ); ?></label></div>
						<div class="rtcl-ie-fcontrol">
							<?php
							wp_dropdown_categories( [
								'taxonomy'          => rtcl()->location,
								'name'              => 'target_location',
								'id'                => 'rtcl-google-location',
								'class'             => 'rtcl-ie-select rtcl-ie-maxw-220',
								'show_option_none'  => __( '— None —', 'classified-listing' ),
								'option_none_value' => 0,
								'hide_empty'        => 0,
								'hierarchical'      => 1,
								'orderby'           => 'name',
							] );
							?>
						</div>
					</div>
				</div>
			</section>

			<section class="rtcl-ie-card">
				<header class="rtcl-ie-card-head">
					<span class="rtcl-ie-card-ico"><?php rtcl_ie_icon( 'settings' ); ?></span>
					<div>
						<h2><?php esc_html_e( 'Import options', 'classified-listing' ); ?></h2>
						<p><?php esc_html_e( 'Control quota usage, images, and AI enrichment.', 'classified-listing' ); ?></p>
					</div>
				</header>
				<div class="rtcl-ie-card-body">

					<div class="rtcl-ie-opt-row">
						<div class="rtcl-ie-opt-text">
							<div class="rtcl-ie-opt-title"><?php esc_html_e( 'Light import (skip Place Details — save quota)', 'classified-listing' ); ?></div>
							<div class="rtcl-ie-opt-desc"><?php esc_html_e( 'Uses only the data already loaded by Search — no GetPlaceRequest calls. Imports title, address, location, categories, and the 400px preview thumbnail. Phone, website, business hours, rating, and editorial description will NOT be imported (those require Place Details).', 'classified-listing' ); ?></div>
						</div>
						<label class="rtcl-ie-switch">
							<input type="checkbox" name="light_mode" value="1" id="rtcl-google-light-mode">
							<span class="rtcl-ie-switch-track"></span>
						</label>
					</div>

					<div class="rtcl-ie-opt-row rtcl-ie-opt-row-stacked">
						<div class="rtcl-ie-opt-text">
							<div class="rtcl-ie-opt-title"><?php esc_html_e( 'Featured image source', 'classified-listing' ); ?></div>
							<div class="rtcl-ie-opt-desc"><?php esc_html_e( 'Choose how listings get a featured image. "No image" is useful when Place Photos quota is exhausted or when you just want the textual data.', 'classified-listing' ); ?></div>
						</div>
						<select name="image_source" id="rtcl-google-image-source" class="rtcl-ie-select rtcl-ie-maxw-360">
							<option value="google"><?php esc_html_e( 'Use Google place photos (billed)', 'classified-listing' ); ?></option>
							<option value="fallback" <?php disabled( '' === trim( $fallback_image ) ); ?>>
								<?php esc_html_e( 'Use default fallback image', 'classified-listing' ); ?>
								<?php echo '' === trim( $fallback_image ) ? ' — ' . esc_html__( 'configure URL in Settings → Import', 'classified-listing' ) : ''; ?>
							</option>
							<option value="none"><?php esc_html_e( 'No image (skip photo downloads)', 'classified-listing' ); ?></option>
						</select>
					</div>

					<div class="rtcl-ie-opt-row">
						<div class="rtcl-ie-opt-text">
							<div class="rtcl-ie-opt-title"><?php esc_html_e( 'Refresh existing listings (dedupe by Google place_id)', 'classified-listing' ); ?></div>
						</div>
						<label class="rtcl-ie-switch">
							<input type="checkbox" name="update_existing" value="1">
							<span class="rtcl-ie-switch-track"></span>
						</label>
					</div>

					<div class="rtcl-ie-opt-row">
						<div class="rtcl-ie-opt-text">
							<div class="rtcl-ie-opt-title"><?php esc_html_e( 'Import Google reviews as listing reviews', 'classified-listing' ); ?></div>
							<div class="rtcl-ie-opt-desc"><?php esc_html_e( 'Fetch each place\'s reviews and store them as native (approved) listing reviews with their star rating. Google returns at most 5 reviews per place, and this uses a higher-cost API tier — leave off if you don\'t need reviews.', 'classified-listing' ); ?></div>
						</div>
						<label class="rtcl-ie-switch">
							<input type="checkbox" name="import_reviews" value="1" id="rtcl-google-import-reviews">
							<span class="rtcl-ie-switch-track"></span>
						</label>
					</div>

					<div class="rtcl-ie-opt-row rtcl-ie-opt-row-stacked" id="rtcl-google-max-reviews-row" style="display:none;">
						<div class="rtcl-ie-opt-text">
							<div class="rtcl-ie-opt-title"><?php esc_html_e( 'Max reviews per place', 'classified-listing' ); ?></div>
							<div class="rtcl-ie-opt-desc"><?php esc_html_e( 'How many reviews to import per place (1-5). Google never returns more than 5.', 'classified-listing' ); ?></div>
						</div>
						<input type="number" name="max_reviews" id="rtcl-google-max-reviews" class="rtcl-ie-input rtcl-ie-maxw-120" value="5" min="1" max="5">
					</div>

					<div class="rtcl-ie-opt-row">
						<div class="rtcl-ie-opt-text">
							<div class="rtcl-ie-opt-title"><?php esc_html_e( 'Auto-generate description with AI when source has none', 'classified-listing' ); ?></div>
							<div class="rtcl-ie-opt-desc"><?php esc_html_e( 'Google only provides a description for a small subset of businesses. With this on, the AI writes a short 2-3 sentence description from name / address / categories when the source is empty. One AI call per record.', 'classified-listing' ); ?></div>
						</div>
						<label class="rtcl-ie-switch" title="<?php echo $ai_enabled ? '' : esc_attr__( 'Configure an AI provider under Settings → AI Integration to enable.', 'classified-listing' ); ?>">
							<input type="checkbox" name="enrich_description" value="1" <?php disabled( ! $ai_enabled ); ?>>
							<span class="rtcl-ie-switch-track"></span>
						</label>
					</div>

					<div class="rtcl-ie-opt-row">
						<div class="rtcl-ie-opt-text">
							<div class="rtcl-ie-opt-title"><?php esc_html_e( 'Use AI to fill all empty form fields per record', 'classified-listing' ); ?></div>
							<div class="rtcl-ie-opt-desc"><?php esc_html_e( 'Broader fill (every empty target field). Capped at 10 records per run. Each enriched record is one extra AI call.', 'classified-listing' ); ?></div>
						</div>
						<label class="rtcl-ie-switch" title="<?php echo $ai_enabled ? '' : esc_attr__( 'Configure an AI provider under Settings → AI Integration to enable.', 'classified-listing' ); ?>">
							<input type="checkbox" name="enrich" value="1" <?php disabled( ! $ai_enabled ); ?>>
							<span class="rtcl-ie-switch-track"></span>
						</label>
					</div>

				</div>
			</section>

			<div class="rtcl-ie-import-bar">
				<button type="submit" class="rtcl-btn rtcl-btn-primary rtcl-ie-btn rtcl-ie-btn-cta" id="rtcl-google-import-btn" disabled>
					<?php rtcl_ie_icon( 'download' ); ?>
					<?php esc_html_e( 'Import selected', 'classified-listing' ); ?>
				</button>
				<span class="rtcl-ie-ib-note"><?php esc_html_e( 'Each selected place is one Place Details + photo download. Costs apply on your Google Cloud project.', 'classified-listing' ); ?></span>
			</div>
		</form>

		<div id="rtcl-google-import-status" class="rtcl-google-status"></div>
	</div>
</div>

<script>
( function ( $ ) {
	'use strict';

	var ajaxUrl    = '<?php echo esc_url_raw( admin_url( 'admin-ajax.php' ) ); ?>';
	var nonceField = <?php echo wp_json_encode( $nonce_field ); ?>;
	var nonce      = <?php echo wp_json_encode( $nonce ); ?>;
	var lastResults = [];

	// Inline SVG placeholder for results where Google did not return a photo,
	// where photo resolution failed (e.g. Places API not enabled, billing
	// disabled), or where the signed CDN URL expired before render.
	var PLACEHOLDER_IMG = 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(
		'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80">' +
			'<rect width="80" height="80" fill="#f0f0f1"/>' +
			'<circle cx="28" cy="30" r="5" fill="#c3c4c7"/>' +
			'<path d="M12 62 L32 38 L44 50 L56 34 L68 62 Z" fill="#c3c4c7"/>' +
			'<text x="40" y="76" font-family="sans-serif" font-size="9" fill="#787c82" text-anchor="middle">no photo</text>' +
		'</svg>'
	);

	/**
	 * Inline error fallback for broken / expired Google photo URLs.
	 * Exposed globally so per-image onerror handlers can reach it.
	 */
	window.rtclGooglePhotoFallback = function ( img ) {
		img.onerror = null;
		img.src = PLACEHOLDER_IMG;
	};

	/**
	 * Attach the request nonce to the given payload.
	 */
	function withNonce( data ) {
		data = data || {};
		data[ nonceField ] = nonce;
		return data;
	}

	/**
	 * Render a small inline notice in the given target container.
	 */
	function notice( $target, isError, message ) {
		$target.html( '<div class="notice ' + ( isError ? 'notice-error' : 'notice-success' ) + ' inline"><p>' + message + '</p></div>' );
	}

	/**
	 * Minimal HTML-entity escape for untrusted strings interpolated into markup.
	 */
	function escapeHtml( s ) {
		return $( '<div>' ).text( s == null ? '' : s ).html();
	}

	/**
	 * Render the place-grid cards from a Search response.
	 */
	function renderResults( items ) {
		if ( ! items.length ) {
			$( '#rtcl-google-results' ).html( '<p class="rtcl-ie-empty-msg">' + 'No matches.' + '</p>' );
			$( '#rtcl-google-results-wrap' ).show();
			return;
		}
		var html = items.map( function ( it, i ) {
			var img;
			if ( it.photo ) {
				img = '<img src="' + escapeHtml( it.photo ) + '" alt="" loading="lazy" onerror="rtclGooglePhotoFallback(this)">';
			} else {
				img = '<img src="' + PLACEHOLDER_IMG + '" alt="" loading="lazy">';
			}
			return '' +
				'<label class="rtcl-ie-place">' +
					'<span class="rtcl-ie-place-thumb">' + img + '</span>' +
					'<span class="rtcl-ie-place-meta">' +
						'<span class="rtcl-ie-place-name">' + escapeHtml( it.name ) + '</span>' +
						'<span class="rtcl-ie-place-addr">' + escapeHtml( it.address ) + '</span>' +
					'</span>' +
					'<span class="rtcl-ie-place-chk">' +
						'<input type="checkbox" class="rtcl-google-pick" data-index="' + i + '" value="' + escapeHtml( it.place_id ) + '">' +
					'</span>' +
				'</label>';
		} ).join( '' );
		$( '#rtcl-google-results' ).html( html );
		$( '#rtcl-google-results-wrap' ).show();
		updateSelectedCount();
	}

	/**
	 * Update the "(n selected)" counter and toggle the import button.
	 */
	function updateSelectedCount() {
		var n = $( '.rtcl-google-pick:checked' ).length;
		var total = $( '.rtcl-google-pick' ).length;
		$( '#rtcl-google-selected-count' ).text( total ? '(' + n + ' selected)' : '' );
		$( '#rtcl-google-badge-count' ).text( total ? n + ' of ' + total : '' );
		$( '#rtcl-google-import-btn' ).prop( 'disabled', n === 0 );
		$( '#rtcl-google-select-all' ).prop( 'checked', total > 0 && n === total ).prop( 'indeterminate', n > 0 && n < total );
	}

	// Toggle "selected" state on the card when the inner checkbox flips.
	$( document ).on( 'change', '.rtcl-google-pick', function () {
		$( this ).closest( '.rtcl-ie-place' ).toggleClass( 'is-selected', this.checked );
		updateSelectedCount();
	} );

	// ─── Location-bias map picker ──────────────────────────────────────────

	var biasMap, biasMarker, biasCircle, biasGeocoder, mapAuthFailed = false;

	/**
	 * Swap the (gray / broken) map UI for a plain notice; the manual
	 * lat / lng / radius inputs stay usable.
	 */
	function biasMapFailed() {
		mapAuthFailed = true;
		$( '#rtcl-google-map-searchbar, #rtcl-google-bias-map, #rtcl-google-map-hint' ).hide();
		$( '#rtcl-google-map-fallback' ).show();
	}

	window.gm_authFailure = biasMapFailed;

	/**
	 * Return the current radius input clamped to the allowed range.
	 */
	function biasRadius() {
		var r = parseInt( $( '#rtcl-google-radius' ).val(), 10 );
		if ( isNaN( r ) || r < 1 ) { r = 5000; }
		return Math.min( 50000, Math.max( 1, r ) );
	}

	/**
	 * Write coordinates back into the visible lat / lng inputs.
	 */
	function setBiasInputs( lat, lng ) {
		$( '#rtcl-google-lat' ).val( ( lat === '' || lat == null ) ? '' : Number( lat ).toFixed( 6 ) );
		$( '#rtcl-google-lng' ).val( ( lng === '' || lng == null ) ? '' : Number( lng ).toFixed( 6 ) );
	}

	/**
	 * Drop the marker + radius circle at the given coordinate and (optionally)
	 * pan the map there.
	 */
	function placeBias( lat, lng, pan ) {
		if ( ! biasMap ) { return; }
		var pos = { lat: Number( lat ), lng: Number( lng ) };

		if ( ! biasMarker ) {
			biasMarker = new google.maps.Marker( { map: biasMap, draggable: true, position: pos } );
			biasMarker.addListener( 'dragend', function ( e ) {
				setBiasInputs( e.latLng.lat(), e.latLng.lng() );
				if ( biasCircle ) { biasCircle.setCenter( e.latLng ); }
			} );
		} else {
			biasMarker.setPosition( pos );
		}

		if ( ! biasCircle ) {
			biasCircle = new google.maps.Circle( {
				map: biasMap,
				center: pos,
				radius: biasRadius(),
				editable: true,
				fillColor: '#2a6df4',
				fillOpacity: 0.12,
				strokeColor: '#2a6df4',
				strokeOpacity: 0.6,
				strokeWeight: 1
			} );
			biasCircle.addListener( 'radius_changed', function () {
				var r = Math.min( 50000, Math.max( 1, Math.round( biasCircle.getRadius() ) ) );
				$( '#rtcl-google-radius' ).val( r );
				if ( Math.round( biasCircle.getRadius() ) !== r ) { biasCircle.setRadius( r ); }
			} );
			biasCircle.addListener( 'center_changed', function () {
				var c = biasCircle.getCenter();
				if ( biasMarker ) { biasMarker.setPosition( c ); }
				setBiasInputs( c.lat(), c.lng() );
			} );
		} else {
			biasCircle.setCenter( pos );
			biasCircle.setRadius( biasRadius() );
		}

		setBiasInputs( lat, lng );
		if ( pan ) {
			biasMap.panTo( pos );
			if ( biasMap.getZoom() < 11 ) { biasMap.setZoom( 12 ); }
		}
	}

	/**
	 * Remove the marker / circle and blank the lat / lng inputs.
	 */
	function clearBias() {
		if ( biasMarker ) { biasMarker.setMap( null ); biasMarker = null; }
		if ( biasCircle ) { biasCircle.setMap( null ); biasCircle = null; }
		setBiasInputs( '', '' );
	}

	/**
	 * Init callback invoked by the Google Maps JS loader (see ScriptLoader::callback).
	 */
	window.rtclGoogleImportMapInit = function () {
		if ( mapAuthFailed ) { return; }
		var el = document.getElementById( 'rtcl-google-bias-map' );
		if ( ! el || typeof google === 'undefined' || ! google.maps ) { return; }

		$( '#rtcl-google-map-fallback' ).hide();
		$( '#rtcl-google-map-searchbar, #rtcl-google-bias-map, #rtcl-google-map-hint' ).show();

		biasGeocoder = new google.maps.Geocoder();

		var startLat = parseFloat( $( '#rtcl-google-lat' ).val() );
		var startLng = parseFloat( $( '#rtcl-google-lng' ).val() );
		var hasStart = ! isNaN( startLat ) && ! isNaN( startLng );

		biasMap = new google.maps.Map( el, {
			center: hasStart ? { lat: startLat, lng: startLng } : { lat: 20, lng: 0 },
			zoom: hasStart ? 12 : 2,
			streetViewControl: false,
			mapTypeControl: false,
			fullscreenControl: false
		} );

		if ( hasStart ) {
			placeBias( startLat, startLng, false );
		} else if ( navigator.geolocation ) {
			navigator.geolocation.getCurrentPosition( function ( pos ) {
				if ( ! biasMap ) { return; }
				biasMap.setCenter( { lat: pos.coords.latitude, lng: pos.coords.longitude } );
				biasMap.setZoom( 12 );
			}, function () { /* permission denied or unavailable — keep the wide view */ }, { timeout: 8000 } );
		}

		biasMap.addListener( 'click', function ( e ) {
			placeBias( e.latLng.lat(), e.latLng.lng(), false );
			mapMsg( '', 'ok' );
		} );
	};

	/**
	 * Write a status message under the map search bar.
	 */
	function mapMsg( text, kind, link ) {
		var $m = $( '#rtcl-google-map-msg' ).removeClass( 'is-error is-ok' ).empty();
		if ( ! text ) { return; }
		$m.text( text );
		if ( link && link.url ) {
			$m.append( ' ' );
			$( '<a>', { href: link.url, target: '_blank', rel: 'noopener' } )
				.text( link.label || link.url )
				.appendTo( $m );
		}
		$m.addClass( kind === 'error' ? 'is-error' : 'is-ok' );
	}

	// Google's billing-enable page; surfaced when the Geocoder reports a
	// billing/quota rejection so the admin can act without opening the console.
	var BILLING_LINK = { url: 'https://console.cloud.google.com/project/_/billing/enable', label: 'Enable billing →' };

	/**
	 * Geocode the typed text and center the map on the result.
	 */
	function geocodeBiasSearch( query ) {
		query = $.trim( query || '' );
		if ( ! query ) { return; }

		if ( ! biasMap || ! biasGeocoder ) {
			mapMsg( 'Map is not loaded — enable the "Maps JavaScript API" on your Google key. Meanwhile, type Latitude/Longitude below.', 'error' );
			return;
		}

		mapMsg( 'Locating "' + query + '"…', 'ok' );
		var $btn = $( '#rtcl-google-map-locate' ).prop( 'disabled', true );
		biasGeocoder.geocode( { address: query }, function ( results, status ) {
			$btn.prop( 'disabled', false );
			if ( status !== 'OK' || ! results || ! results[0] ) {
				window.console && console.warn( 'rtcl bias geocode failed:', status );
				if ( status === 'ZERO_RESULTS' ) {
					mapMsg( 'No location found for "' + query + '". Try a more specific name.', 'error' );
				} else if ( status === 'REQUEST_DENIED' ) {
					mapMsg( 'Google rejected the request. Enable Billing on your Google Cloud project and make sure the "Geocoding API" is on, then reload.', 'error', BILLING_LINK );
				} else if ( status === 'OVER_QUERY_LIMIT' ) {
					mapMsg( 'Google returned a billing / quota error. Enable Billing on your Google Cloud project, then reload.', 'error', BILLING_LINK );
				} else {
					mapMsg( 'Could not locate that (' + status + ').', 'error' );
				}
				return;
			}
			mapMsg( '', 'ok' );
			var r   = results[0];
			var loc = r.geometry.location;
			placeBias( loc.lat(), loc.lng(), false );
			if ( r.geometry.viewport ) {
				biasMap.fitBounds( r.geometry.viewport );
			} else {
				biasMap.panTo( loc );
				biasMap.setZoom( 12 );
			}
			if ( ! $( '#rtcl-google-region' ).val() && r.address_components ) {
				r.address_components.forEach( function ( comp ) {
					if ( comp.types && comp.types.indexOf( 'country' ) !== -1 && comp.short_name ) {
						$( '#rtcl-google-region' ).val( comp.short_name.toLowerCase() );
					}
				} );
			}
		} );
	}

	$( '#rtcl-google-map-search' ).on( 'keydown', function ( e ) {
		if ( e.key === 'Enter' ) {
			e.preventDefault();
			geocodeBiasSearch( this.value );
		}
	} );

	$( '#rtcl-google-map-locate' ).on( 'click', function () {
		geocodeBiasSearch( $( '#rtcl-google-map-search' ).val() );
	} );

	$( '#rtcl-google-lat, #rtcl-google-lng' ).on( 'change', function () {
		var lat = parseFloat( $( '#rtcl-google-lat' ).val() );
		var lng = parseFloat( $( '#rtcl-google-lng' ).val() );
		if ( ! isNaN( lat ) && ! isNaN( lng ) ) { placeBias( lat, lng, true ); }
	} );

	$( '#rtcl-google-radius' ).on( 'change', function () {
		if ( biasCircle ) {
			biasCircle.setRadius( biasRadius() );
			if ( biasCircle.getBounds() ) { biasMap.fitBounds( biasCircle.getBounds() ); }
		}
	} );

	$( '#rtcl-google-bias-clear' ).on( 'click', clearBias );

	if ( document.getElementById( 'rtcl-google-bias-map' ) ) {
		window.setTimeout( function () {
			if ( ! biasMap && ! mapAuthFailed ) { biasMapFailed(); }
		}, 8000 );
	}

	$( '#rtcl-google-search-form' ).on( 'submit', function ( e ) {
		e.preventDefault();
		var $btn = $( '#rtcl-google-search-btn' );
		$btn.prop( 'disabled', true );
		notice( $( '#rtcl-google-search-status' ), false, 'Searching…' );

		var data = $( this ).serializeArray().reduce( function ( acc, kv ) { acc[ kv.name ] = kv.value; return acc; }, {} );
		data.action = 'rtcl_import_google_search';

		$.post( ajaxUrl, withNonce( data ), function ( res ) {
			$btn.prop( 'disabled', false );
			if ( res && res.success ) {
				lastResults = res.data.results || [];
				$( '#rtcl-google-search-status' ).empty();
				renderResults( lastResults );
			} else {
				$( '#rtcl-google-results-wrap' ).hide();
				notice( $( '#rtcl-google-search-status' ), true, ( res && res.data && res.data.message ) || 'Search failed.' );
			}
		} ).fail( function () {
			$btn.prop( 'disabled', false );
			notice( $( '#rtcl-google-search-status' ), true, 'Network error.' );
		} );
	} );

	$( '#rtcl-google-select-all' ).on( 'change', function () {
		$( '.rtcl-google-pick' ).prop( 'checked', this.checked ).each( function () {
			$( this ).closest( '.rtcl-ie-place' ).toggleClass( 'is-selected', this.checked );
		} );
		updateSelectedCount();
	} );

	// ─── Field mapping state ───────────────────────────────────────────────

	var sourceFields = [];
	var formFields   = [];
	var currentMap   = {};

	/**
	 * Update the inline mapping toolbar status text.
	 */
	function setMappingStatus( msg, isError ) {
		var $s = $( '#rtcl-google-mapping-status' );
		$s.text( msg || '' );
		$s.css( 'color', isError ? '#dc3232' : '' );
	}

	/**
	 * Re-render the field-mapping table from the current source / form catalogs.
	 */
	function renderMappingTable() {
		var $body = $( '#rtcl-google-mapping-table tbody' ).empty();
		if ( ! sourceFields.length || ! formFields.length ) {
			$( '#rtcl-google-mapping-wrap' ).hide();
			return;
		}

		var optHtml = '<option value="__skip">— Skip —</option>';
		formFields.forEach( function ( f ) {
			optHtml += '<option value="' + escapeHtml( f.key ) + '">' +
				escapeHtml( f.label ) +
				( f.element ? ' (' + escapeHtml( f.element ) + ')' : '' ) +
				'</option>';
		} );

		sourceFields.forEach( function ( src ) {
			$body.append(
				'<tr>' +
					'<td><div class="rtcl-ie-gp-field">' + escapeHtml( src.label ) + '</div>' +
						'<div class="rtcl-ie-gp-meta">' + escapeHtml( src.key ) + ' · ' + escapeHtml( src.kind ) + '</div></td>' +
					'<td class="rtcl-ie-mtable-arrow">' +
						'<svg class="rtcl-ie-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>' +
					'</td>' +
					'<td><select class="rtcl-google-map-target rtcl-ie-select" data-source="' + escapeHtml( src.key ) + '">' + optHtml + '</select></td>' +
				'</tr>'
			);
		} );

		$( '.rtcl-google-map-target' ).each( function () {
			var src = $( this ).data( 'source' );
			var val = currentMap[ src ] || '__skip';
			$( this ).val( val );
			if ( $( this ).val() !== val ) { $( this ).val( '__skip' ); }
			$( this ).toggleClass( 'is-skip', $( this ).val() === '__skip' );
		} );

		$( '#rtcl-google-mapping-wrap' ).show();
	}

	// Toggle the "skip" styling whenever the user changes a target select.
	$( document ).on( 'change', '.rtcl-google-map-target', function () {
		$( this ).toggleClass( 'is-skip', this.value === '__skip' );
	} );

	/**
	 * Collect the current mapping table into a source_key → target_key object.
	 */
	function harvestMapping() {
		var out = {};
		$( '.rtcl-google-map-target' ).each( function () {
			var src = $( this ).data( 'source' );
			var tgt = $( this ).val();
			if ( src && tgt ) { out[ src ] = tgt; }
		} );
		return out;
	}

	/**
	 * Pull form fields and any saved mapping for the selected target form.
	 */
	function loadFormFieldsAndMapping( formId ) {
		if ( ! formId ) {
			$( '#rtcl-google-mapping-wrap' ).hide();
			formFields = [];
			currentMap = {};
			return;
		}
		setMappingStatus( 'Loading form fields…' );

		var p1 = $.post( ajaxUrl, withNonce( { action: 'rtcl_import_form_fields', form_id: formId } ) );
		var p2 = $.post( ajaxUrl, withNonce( { action: 'rtcl_import_mapping_get', source_type: 'google_places', form_id: formId } ) );

		$.when( p1, p2 ).done( function ( r1, r2 ) {
			var fieldsRes = r1[0];
			var mapRes    = r2[0];
			if ( fieldsRes && fieldsRes.success ) {
				formFields = fieldsRes.data.fields || [];
			} else {
				formFields = [];
			}
			if ( mapRes && mapRes.success ) {
				currentMap   = mapRes.data.mapping || {};
				sourceFields = mapRes.data.source_fields || sourceFields;
			}
			renderMappingTable();
			setMappingStatus( '' );
		} ).fail( function () {
			setMappingStatus( 'Failed to load form fields.', true );
		} );
	}

	$( '#rtcl-google-form-picker' ).on( 'change', function () {
		loadFormFieldsAndMapping( parseInt( $( this ).val(), 10 ) || 0 );
	} );

	// Show the "Max reviews" input only when review import is enabled.
	$( '#rtcl-google-import-reviews' ).on( 'change', function () {
		$( '#rtcl-google-max-reviews-row' ).toggle( this.checked );
	} ).trigger( 'change' );

	var initialFormId = parseInt( $( '#rtcl-google-form-picker' ).val(), 10 ) || 0;
	if ( initialFormId ) {
		loadFormFieldsAndMapping( initialFormId );
	}

	$( '#rtcl-google-ai-suggest' ).on( 'click', function () {
		var formId = parseInt( $( '#rtcl-google-form-picker' ).val(), 10 ) || 0;
		if ( ! formId ) {
			setMappingStatus( 'Pick a form first.', true );
			return;
		}
		var $btn = $( this );
		$btn.prop( 'disabled', true );
		setMappingStatus( 'Asking AI…' );

		$.post( ajaxUrl, withNonce( {
			action: 'rtcl_import_mapping_suggest',
			source_type: 'google_places',
			form_id: formId
		} ), function ( res ) {
			$btn.prop( 'disabled', false );
			if ( res && res.success ) {
				currentMap = res.data.mapping || {};
				renderMappingTable();
				setMappingStatus( 'AI suggestion applied — review and save.', false );
			} else {
				setMappingStatus( ( res && res.data && res.data.message ) || 'AI suggestion failed.', true );
			}
		} ).fail( function () {
			$btn.prop( 'disabled', false );
			setMappingStatus( 'Network error.', true );
		} );
	} );

	$( '#rtcl-google-mapping-reset' ).on( 'click', function () {
		var formId = parseInt( $( '#rtcl-google-form-picker' ).val(), 10 ) || 0;
		if ( ! formId ) {
			setMappingStatus( 'Pick a form first.', true );
			return;
		}
		if ( ! confirm( 'Clear all mapping selections and delete the saved mapping for this form?' ) ) {
			return;
		}
		var $btn = $( this );
		$btn.prop( 'disabled', true );
		setMappingStatus( 'Resetting…' );

		$.post( ajaxUrl, withNonce( {
			action: 'rtcl_import_mapping_reset',
			source_type: 'google_places',
			form_id: formId
		} ), function ( res ) {
			$btn.prop( 'disabled', false );
			if ( res && res.success ) {
				currentMap = {};
				$( '.rtcl-google-map-target' ).val( '__skip' ).addClass( 'is-skip' );
				setMappingStatus( res.data.message || 'Reset.', false );
			} else {
				setMappingStatus( ( res && res.data && res.data.message ) || 'Reset failed.', true );
			}
		} ).fail( function () {
			$btn.prop( 'disabled', false );
			setMappingStatus( 'Network error.', true );
		} );
	} );

	$( '#rtcl-google-mapping-save' ).on( 'click', function () {
		var formId = parseInt( $( '#rtcl-google-form-picker' ).val(), 10 ) || 0;
		if ( ! formId ) {
			setMappingStatus( 'Pick a form first.', true );
			return;
		}
		var map = harvestMapping();
		currentMap = map;

		var payload = { action: 'rtcl_import_mapping_save', source_type: 'google_places', form_id: formId };
		payload[ nonceField ] = nonce;
		Object.keys( map ).forEach( function ( k ) {
			payload[ 'mapping[' + k + ']' ] = map[ k ];
		} );

		$.post( ajaxUrl, payload, function ( res ) {
			if ( res && res.success ) {
				setMappingStatus( res.data.message || 'Saved.', false );
			} else {
				setMappingStatus( ( res && res.data && res.data.message ) || 'Save failed.', true );
			}
		} ).fail( function () {
			setMappingStatus( 'Network error.', true );
		} );
	} );

	// ─── Live import progress ──────────────────────────────────────────────

	var progressTimer = null;
	var importInFlight = false;

	/**
	 * Re-enable the Import-selected button after a run completes / fails.
	 */
	function releaseImport() {
		importInFlight = false;
		$( '#rtcl-google-import-btn' ).prop( 'disabled', $( '.rtcl-google-pick:checked' ).length === 0 );
	}

	/**
	 * Render the initial progress card before the first poll lands.
	 */
	function renderProgressShell( d ) {
		var historyLink = d.history_url
			? '<a href="' + escapeHtml( d.history_url ) + '"><?php echo esc_js( __( 'History tab', 'classified-listing' ) ); ?> &rarr;</a>'
			: '';
		var html =
			'<div class="notice notice-info inline"><div class="rtcl-ie-import-progress">' +
				'<p>' + escapeHtml( d.message || '' ) + '</p>' +
				'<div class="rtcl-ie-progress"><span class="rtcl-ie-progress-bar" style="width:0%"></span></div>' +
				'<p class="rtcl-ie-progress-stats">' + 'Starting…' + '</p>' +
			'</div></div>' +
			'<p class="rtcl-ie-import-hint" style="margin-top:8px;color:#646970;font-style:italic;">' +
				'<?php echo esc_js( __( 'You don\'t need to stay on this page — you can check the progress anytime from the', 'classified-listing' ) ); ?> ' + historyLink +
			'</p>';
		$( '#rtcl-google-import-status' ).html( html );
	}

	/**
	 * Patch the live progress UI with the latest poll payload.
	 */
	function updateProgress( p, historyUrl ) {
		var total = p.total | 0;
		var pct   = p.percent | 0;
		$( '.rtcl-ie-progress-bar' ).css( 'width', pct + '%' );
		$( '.rtcl-ie-progress-stats' ).text(
			'Processed ' + ( p.processed | 0 ) + ' / ' + total +
			'  —  Imported ' + ( p.imported | 0 ) +
			', Updated ' + ( p.updated | 0 ) +
			', Skipped ' + ( p.skipped | 0 ) +
			( p.errors && p.errors.length ? ', Errors ' + p.errors.length : '' )
		);

		if ( p.done ) {
			var $bar = $( '.rtcl-ie-progress-bar' ).css( 'width', '100%' );
			$bar.addClass( p.status === 'success' ? 'is-done' : ( p.status === 'failed' ? 'is-failed' : 'is-partial' ) );

			$( '#rtcl-google-import-status .notice' )
				.removeClass( 'notice-info' )
				.addClass( p.status === 'failed' ? 'notice-error' : ( p.status === 'partial' ? 'notice-warning' : 'notice-success' ) );

			var doneMsg = 'Import finished — ' + ( p.imported | 0 ) + ' imported, ' +
				( p.updated | 0 ) + ' updated, ' + ( p.skipped | 0 ) + ' skipped.';
			$( '.rtcl-ie-import-progress > p' ).first().text( doneMsg );

			if ( p.errors && p.errors.length ) {
				var first = p.errors.slice( 0, 8 );
				var eh = '<details open class="rtcl-ie-errlist"><summary><strong>' + p.errors.length + ' issue(s):</strong></summary><ul>';
				first.forEach( function ( e ) { eh += '<li>' + escapeHtml( String( e ) ) + '</li>'; } );
				if ( p.errors.length > first.length ) {
					eh += '<li>… (' + ( p.errors.length - first.length ) + ' more)</li>';
				}
				eh += '</ul></details>';
				$( '.rtcl-ie-import-progress' ).append( eh );
			}
			if ( historyUrl ) {
				$( '.rtcl-ie-import-progress' ).append( '<p class="rtcl-ie-history-link"><a href="' + escapeHtml( historyUrl ) + '">View in the History tab →</a></p>' );
			}
			releaseImport();
		}
	}

	/**
	 * Begin polling the rtcl_import_progress endpoint for the given run.
	 */
	function startImportProgress( d ) {
		if ( progressTimer ) { window.clearInterval( progressTimer ); progressTimer = null; }
		renderProgressShell( d );

		var runId = d.run_id;
		var historyUrl = d.history_url;

		function poll() {
			$.post( ajaxUrl, withNonce( { action: 'rtcl_import_progress', run_id: runId } ), function ( res ) {
				if ( res && res.success ) {
					updateProgress( res.data, historyUrl );
					if ( res.data.done && progressTimer ) {
						window.clearInterval( progressTimer );
						progressTimer = null;
					}
				}
			} );
		}

		poll();
		progressTimer = window.setInterval( poll, 2500 );
	}

	// ─── Import submission ─────────────────────────────────────────────────

	$( '#rtcl-google-import-form' ).on( 'submit', function ( e ) {
		e.preventDefault();

		if ( importInFlight ) { return; }

		var ids = $( '.rtcl-google-pick:checked' ).map( function () { return this.value; } ).get();
		if ( ! ids.length ) { return; }

		importInFlight = true;

		var formId = parseInt( $( '#rtcl-google-form-picker' ).val(), 10 ) || 0;
		var liveMap = harvestMapping();

		var data = $( this ).serializeArray().reduce( function ( acc, kv ) { acc[ kv.name ] = kv.value; return acc; }, {} );
		data.action              = 'rtcl_import_google_run';
		data.update_existing     = $( this ).find( '[name=update_existing]' ).is( ':checked' ) ? 1 : 0;
		data.enrich              = $( this ).find( '[name=enrich]' ).is( ':checked' ) ? 1 : 0;
		data.enrich_description  = $( this ).find( '[name=enrich_description]' ).is( ':checked' ) ? 1 : 0;
		data.import_reviews      = $( this ).find( '[name=import_reviews]' ).is( ':checked' ) ? 1 : 0;
		data.max_reviews         = parseInt( $( this ).find( '[name=max_reviews]' ).val(), 10 ) || 5;
		data.image_source        = $( this ).find( '[name=image_source]' ).val() || 'google';
		data.light_mode          = $( '#rtcl-google-light-mode' ).is( ':checked' ) ? 1 : 0;
		data.place_ids           = ids;
		data.form_id             = formId;

		var payload = $.extend( {}, data );
		payload[ nonceField ] = nonce;
		Object.keys( liveMap ).forEach( function ( k ) {
			payload[ 'mapping[' + k + ']' ] = liveMap[ k ];
		} );

		var $btn = $( '#rtcl-google-import-btn' );
		$btn.prop( 'disabled', true );
		notice( $( '#rtcl-google-import-status' ), false, 'Importing ' + ids.length + ' places…' );

		ids.forEach( function ( id, i ) {
			payload[ 'place_ids[' + i + ']' ] = id;
		} );
		delete payload.place_ids;

		if ( data.light_mode ) {
			var idSet = {};
			ids.forEach( function ( id ) { idSet[ id ] = true; } );
			var pickedRows = lastResults.filter( function ( r ) { return idSet[ r.place_id ]; } );
			pickedRows.forEach( function ( r, i ) {
				payload[ 'places_data[' + i + '][place_id]' ] = r.place_id || '';
				payload[ 'places_data[' + i + '][name]' ]     = r.name     || '';
				payload[ 'places_data[' + i + '][address]' ]  = r.address  || '';
				payload[ 'places_data[' + i + '][photo]' ]    = r.photo    || '';
				payload[ 'places_data[' + i + '][lat]' ]      = r.lat != null ? r.lat : '';
				payload[ 'places_data[' + i + '][lng]' ]      = r.lng != null ? r.lng : '';
				( r.types || [] ).forEach( function ( t, j ) {
					payload[ 'places_data[' + i + '][types][' + j + ']' ] = t;
				} );
			} );
		}

		$.post( ajaxUrl, payload, function ( res ) {
			if ( res && res.success ) {
				var d = res.data || {};

				if ( d.scheduled ) {
					if ( d.run_id ) {
						startImportProgress( d );
					} else {
						var schedLink = d.history_url
							? '<a href="' + escapeHtml( d.history_url ) + '"><?php echo esc_js( __( 'History tab', 'classified-listing' ) ); ?> &rarr;</a>'
							: '';
						var schedHtml = '<p>' + escapeHtml( d.message || '' ) + '</p>' +
							'<p style="margin-top:4px;color:#646970;font-style:italic;">' +
								'<?php echo esc_js( __( 'You don\'t need to stay on this page — you can check the progress anytime from the', 'classified-listing' ) ); ?> ' + schedLink +
							'</p>';
						$( '#rtcl-google-import-status' ).html(
							'<div class="notice notice-info inline">' + schedHtml + '</div>'
						);
						releaseImport();
					}
					return;
				}

				releaseImport();

				var html = '<p>' + escapeHtml( d.message || '' ) + '</p>';

				if ( ( d.imported | 0 ) === 0 && ( d.updated | 0 ) === 0 && ( d.skipped | 0 ) > 0 && ( ! d.errors || ! d.errors.length ) ) {
					html += '<p><em>' + d.skipped + ' place(s) were already imported. Tick <strong>Refresh existing listings</strong> to update them.</em></p>';
				}

				if ( d.errors && d.errors.length ) {
					var first = d.errors.slice( 0, 8 );
					html += '<details open class="rtcl-ie-errlist"><summary><strong>' + d.errors.length + ' issue(s):</strong></summary><ul>';
					first.forEach( function ( e ) { html += '<li>' + escapeHtml( String( e ) ) + '</li>'; } );
					if ( d.errors.length > first.length ) {
						html += '<li>… (' + ( d.errors.length - first.length ) + ' more — see browser console)</li>';
					}
					html += '</ul></details>';
					console.warn( 'rtcl google import errors', d.errors );
				}

				$( '#rtcl-google-import-status' ).html(
					'<div class="notice ' + ( ( d.imported | 0 ) || ( d.updated | 0 ) ? 'notice-success' : 'notice-warning' ) + ' inline">' + html + '</div>'
				);
			} else {
				releaseImport();
				notice( $( '#rtcl-google-import-status' ), true, ( res && res.data && res.data.message ) || 'Import failed.' );
			}
		} ).fail( function () {
			releaseImport();
			notice( $( '#rtcl-google-import-status' ), true, 'Network error.' );
		} );
	} );

} )( jQuery );
</script>
