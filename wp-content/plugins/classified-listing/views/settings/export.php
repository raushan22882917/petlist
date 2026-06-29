<?php
/**
 * Export tab — categories / locations / settings JSON and listings CSV.
 *
 * Two action cards with a single primary button each. The export-CSV link
 * targets the existing rtcl_listings_export admin-ajax handler so the
 * browser handles the download directly (no JS needed).
 */

defined( 'ABSPATH' ) || exit;

$nonce = wp_create_nonce( 'rtcl_nonce_secret' );
?>
<div class="rtcl-import-export rtcl rtcl-ie-panel">

	<section class="rtcl-ie-card rtcl-export-group">
		<header class="rtcl-ie-card-head">
			<span class="rtcl-ie-card-ico"><?php rtcl_ie_icon( 'file-json' ); ?></span>
			<div>
				<h2><?php esc_html_e( 'Export Categories, Location & Settings', 'classified-listing' ); ?></h2>
				<p><?php esc_html_e( 'Download your taxonomies and plugin settings as a single JSON file.', 'classified-listing' ); ?></p>
			</div>
		</header>
		<div class="rtcl-ie-card-body">
			<div class="rtcl-ie-actrow">
				<a id="rtcl-export-cat-loc-json" class="rtcl-ie-btn rtcl-ie-btn-primary">
					<?php rtcl_ie_icon( 'download' ); ?>
					<?php esc_html_e( 'Export JSON', 'classified-listing' ); ?>
				</a>
				<span class="rtcl-ie-hint">
					<?php esc_html_e( 'Includes categories, locations, and global settings.', 'classified-listing' ); ?>
				</span>
			</div>
		</div>
	</section>

	<section class="rtcl-ie-card rtcl-export-group">
		<header class="rtcl-ie-card-head">
			<span class="rtcl-ie-card-ico"><?php rtcl_ie_icon( 'table' ); ?></span>
			<div>
				<h2><?php esc_html_e( 'Export Listings', 'classified-listing' ); ?></h2>
				<p><?php esc_html_e( 'Download all listings as a CSV file you can edit and re-import.', 'classified-listing' ); ?></p>
			</div>
		</header>
		<div class="rtcl-ie-card-body">
			<div class="rtcl-ie-actrow">
				<a id="rtcl-export-listings-csv"
					href="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>?action=rtcl_listings_export&__rtcl_wpnonce=<?php echo esc_attr( $nonce ); ?>"
					class="rtcl-ie-btn rtcl-ie-btn-primary">
					<?php rtcl_ie_icon( 'download' ); ?>
					<?php esc_html_e( 'Export CSV', 'classified-listing' ); ?>
				</a>
				<span class="rtcl-ie-hint">
					<?php esc_html_e( 'Large catalogs are split across multiple files of 100 listings each.', 'classified-listing' ); ?>
				</span>
			</div>
		</div>
	</section>

</div>
