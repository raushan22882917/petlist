<?php


namespace Rtcl\Helpers;


use Rtcl\Models\Roles;

class Upgrade {

	static function init() {
		add_action( 'init', [ __CLASS__, 'run_upgrade' ] );
	}

	public static function run_upgrade() {
//        self::upgrade_to_1_5_5();
//        self::upgrade_to_1_5_59();
		self::upgrade_to_5_3_10();
		self::upgrade_importer_tables();
	}

	public static function upgrade_to_5_3_10() {
		Roles::add_listing_gallery_caps();
	}

	/**
	 * Create the importer-related tables (rtcl_import_history, rtcl_import_sources)
	 * on existing installs without requiring a deactivate/reactivate cycle.
	 *
	 * Guarded by rtcl_importer_db_version so the SHOW TABLES probes run once,
	 * not on every admin init. Bump the constant when adding more importer
	 * tables in future phases.
	 */
	public static function upgrade_importer_tables() {
		$target = 1;
		if ( (int) get_option( 'rtcl_importer_db_version', 0 ) >= $target ) {
			return;
		}

		Installer::migrate();
		update_option( 'rtcl_importer_db_version', $target, false );
	}

	public static function upgrade_to_1_5_59() {
		$old_version = get_option( 'rtcl_pro_version' );
		if ( $old_version && version_compare( $old_version, '1.5.59' ) < 0 ) {
			Roles::remove_default_caps();
			Roles::create_roles();
			update_option( 'rtcl_queue_flush_rewrite_rules', 'yes' );
			self::update_rtcl_version( '1.5.59' );
		}
	}

	public static function upgrade_to_1_5_5() {
		$old_version = get_option( 'rtcl_pro_version' );
		if ( $old_version && version_compare( $old_version, '1.5.5' ) < 0 ) {
			if ( $listings_page_id = Functions::get_page_id( 'listings' ) ) {
				$my_post = [
					'ID'           => $listings_page_id,
					'post_content' => '',
				];
				wp_update_post( $my_post );
			}
			update_option( 'rtcl_queue_flush_rewrite_rules', 'yes' );
			self::update_rtcl_version( '1.5.5' );
		}
	}

	static function update_rtcl_version( $version = '' ) {
		$version = $version ?: RTCL_VERSION;
		delete_option( 'rtcl_pro_version' );
		add_option( 'rtcl_pro_version', $version );
	}
}