<?php
/**
 * Import tab — JSON settings restore + CSV listings import.
 *
 * Preserves every form/input id and class that admin-ie.js and
 * ListingAdminAjax hook onto; only the surrounding markup is restyled
 * with the new card layout.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="rtcl-import-export rtcl rtcl-ie-panel">

	<section class="rtcl-ie-card">
		<header class="rtcl-ie-card-head">
			<span class="rtcl-ie-card-ico"><?php rtcl_ie_icon( 'file-json' ); ?></span>
			<div>
				<h2><?php esc_html_e( 'Import Location, Categories & Settings', 'classified-listing' ); ?></h2>
				<p><?php esc_html_e( 'Restore taxonomies and settings from a previously exported JSON file.', 'classified-listing' ); ?></p>
			</div>
		</header>
		<div class="rtcl-ie-card-body" id="rtcl-import-wrap">
			<div class="import-location-categories">
				<form class="form rtcl-ie-form" id="rtcl-import-form">
					<div class="rtcl-ie-frow">
						<div class="rtcl-ie-flabel">
							<label for="rtcl-import-file"><?php esc_html_e( 'Select JSON file', 'classified-listing' ); ?></label>
						</div>
						<div class="rtcl-ie-fcontrol">
							<label class="rtcl-ie-file custom-file">
								<span class="rtcl-ie-file-name custom-file-label" id="rtcl-import-file-name"><?php esc_html_e( 'Choose JSON file…', 'classified-listing' ); ?></span>
								<span class="rtcl-ie-file-browse">
									<?php rtcl_ie_icon( 'folder-open' ); ?>
									<?php esc_html_e( 'Browse', 'classified-listing' ); ?>
								</span>
								<input type="file" class="custom-file-input rtcl-import-file" name="import-file" id="rtcl-import-file">
							</label>
							<p class="rtcl-ie-hint description">
								<?php esc_html_e( 'Need a starting point?', 'classified-listing' ); ?>
								<a href="https://gist.github.com/radiustheme/7a15605eac0a6a952d90e5853f5e9c39" target="_blank" rel="noopener">
									<?php esc_html_e( 'Download sample data', 'classified-listing' ); ?>
								</a>
							</p>
						</div>
					</div>

					<div class="rtcl-ie-frow">
						<div class="rtcl-ie-flabel"></div>
						<div class="rtcl-ie-fcontrol">
							<button class="rtcl-btn rtcl-btn-primary rtcl-ie-btn rtcl-ie-btn-primary" type="submit" id="rtcl-import-btn">
								<?php rtcl_ie_icon( 'upload' ); ?>
								<?php esc_html_e( 'Import settings', 'classified-listing' ); ?>
							</button>
						</div>
					</div>
				</form>
			</div>
			<div id="import-response" class="rtcl-ie-json-import-response"></div>
		</div>
	</section>

	<section class="rtcl-ie-card">
		<header class="rtcl-ie-card-head">
			<span class="rtcl-ie-card-ico"><?php rtcl_ie_icon( 'table' ); ?></span>
			<div>
				<h2><?php esc_html_e( 'Import Listings', 'classified-listing' ); ?></h2>
				<p><?php esc_html_e( 'Upload a CSV file, then map its columns to listing fields below.', 'classified-listing' ); ?></p>
			</div>
		</header>
		<div class="rtcl-ie-card-body">
			<div class="import-listings">
				<form class="rtcl-ie-form" method="post" name="rtcl-listings-import" enctype="multipart/form-data" action="">
					<div class="rtcl-ie-frow">
						<div class="rtcl-ie-flabel">
							<label for="rtcl-import-listing-file"><?php esc_html_e( 'Select CSV file', 'classified-listing' ); ?></label>
						</div>
						<div class="rtcl-ie-fcontrol">
							<label class="rtcl-ie-file custom-file">
								<span class="rtcl-ie-file-name custom-file-label" id="rtcl-import-listing-file-name"><?php esc_html_e( 'Choose CSV file…', 'classified-listing' ); ?></span>
								<span class="rtcl-ie-file-browse">
									<?php rtcl_ie_icon( 'folder-open' ); ?>
									<?php esc_html_e( 'Browse', 'classified-listing' ); ?>
								</span>
								<input type="file" class="custom-file-input rtcl-import-listing-file" name="rtcl-import-listing-file" id="rtcl-import-listing-file" required>
							</label>
							<p class="rtcl-ie-hint description">
								<?php esc_html_e( 'Need a starting point?', 'classified-listing' ); ?>
								<a href="https://github.com/radiustheme/classified-listing-sample-data/tree/main/listings" target="_blank" rel="noopener">
									<?php esc_html_e( 'Download sample data', 'classified-listing' ); ?>
								</a>
							</p>
						</div>
					</div>

					<div class="rtcl-ie-frow">
						<div class="rtcl-ie-flabel"></div>
						<div class="rtcl-ie-fcontrol">
							<button class="rtcl-btn rtcl-btn-primary rtcl-ie-btn rtcl-ie-btn-primary" type="submit" id="rtcl-import-listing-btn">
								<?php rtcl_ie_icon( 'upload' ); ?>
								<?php esc_html_e( 'Import listings', 'classified-listing' ); ?>
							</button>
							<div class="rtcl-ie-banner rtcl-ie-banner-warning">
								<?php rtcl_ie_icon( 'server' ); ?>
								<div>
									<div class="rtcl-ie-banner-title"><?php esc_html_e( 'Recommended server limits', 'classified-listing' ); ?></div>
									<div class="rtcl-ie-banner-desc">
										<?php esc_html_e( 'PHP memory limit: 512M · PHP max input variables: 3000. Raise these before large imports.', 'classified-listing' ); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</section>
</div>

<div class="rtcl rtcl-import-notice rtcl-ie-floating-notice">
	<h6><?php esc_html_e( "Please don't refresh the page or click the back button.", 'classified-listing' ); ?></h6>
	<p><?php esc_html_e( 'The import process is still in progress. Refreshing or navigating away may cause errors or data loss. Kindly wait until the process is complete.', 'classified-listing' ); ?></p>
</div>

<div class="rtcl rtcl-ie-app rtcl-listings-import-mapping-wrapper"></div>

<script>
( function ( $ ) {
	'use strict';

	/**
	 * Reflect the picked file's name back into the styled file picker so the
	 * user can see what they chose. Mirrors the legacy "custom-file-label"
	 * behaviour expected by Bootstrap-style file inputs.
	 */
	function bindFileName( inputId, labelId, fallback ) {
		var $input = $( '#' + inputId );
		var $label = $( '#' + labelId );
		if ( ! $input.length || ! $label.length ) { return; }
		$input.on( 'change', function () {
			var f = this.files && this.files[0] ? this.files[0].name : '';
			$label.text( f || fallback ).toggleClass( 'is-set', !! f );
		} );
	}

	bindFileName( 'rtcl-import-file', 'rtcl-import-file-name', '<?php echo esc_js( __( 'Choose JSON file…', 'classified-listing' ) ); ?>' );
	bindFileName( 'rtcl-import-listing-file', 'rtcl-import-listing-file-name', '<?php echo esc_js( __( 'Choose CSV file…', 'classified-listing' ) ); ?>' );

} )( jQuery );
</script>
