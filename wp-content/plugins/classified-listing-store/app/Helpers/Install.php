<?php

namespace RtclStore\Helpers;

class Install {

	/**
	 * DB updates and callbacks that need to be run per version.
	 *
	 * @var array
	 */
	private static array $db_updates = [
		'1.4.24' => [
			'rtcl_store_update_postingLog_table'
		]
	];

	public static function init() {
		add_action( 'init', [ __CLASS__, 'check_version' ], 5 );
	}

	public static function check_version() {
		if ( version_compare( get_option( 'rtcl_store_version' ), RTCL_STORE_VERSION, '<' ) ) {
			self::install();
			do_action( 'rtcl_store_updated' );
		}
	}

	/**
	 * Check if classified parent plugin is activated or not
	 *
	 * @return bool
	 */
	public static function _is_parent_activated() {

		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		if ( is_plugin_active( 'classified-listing-pro/classified-listing-pro.php' ) ) {
			return true;
		}

		return false;
	}

	static function _install() {
		if ( ! self::_is_parent_activated() ) {
			deactivate_plugins( RTCL_STORE_PLUGIN_FILE );
			wp_die( __( '<strong>Classified Listing Store</strong> plugin requires <strong>Classified Listing Pro</strong> to be installed and activated.', 'classified-listing-store' ), __( 'Error', 'classified-listing-store' ), [ 'back_link' => true ] );
		}
	}

	public static function install() {

		if ( ! is_blog_installed() ) {
			return;
		}

		// Check if we are not already running this routine.
		if ( 'yes' === get_transient( 'rtcl_store_installing' ) ) {
			return;
		}

		// If we made it till here nothing is running yet, lets set the transient now.
		set_transient( 'rtcl_store_installing', 'yes', MINUTE_IN_SECONDS * 10 );

		if ( ! get_option( 'rtcl_store_version' ) ) {
			self::create_options();
		}
		self::create_tables();
		self::update_rtcl_version();
		self::maybe_update_db_version();

		delete_transient( 'rtcl_store_installing' );
		do_action( 'rtcl_flush_rewrite_rules' );
		do_action( 'rtcl_store_installed' );
	}


	/**
	 * Get list of DB update callbacks.
	 *
	 * @return array
	 * @since  3.0.0
	 */
	public static function get_db_update_callbacks(): array {
		return self::$db_updates;
	}

	/**
	 * Is a DB update needed?
	 *
	 * @return boolean
	 */
	public static function needs_db_update(): bool {
		$current_db_version = get_option( 'rtcl_store_db_version', null );
		$updates            = self::get_db_update_callbacks();
		$update_versions    = array_keys( $updates );
		usort( $update_versions, 'version_compare' );

		return ! is_null( $current_db_version ) && version_compare( $current_db_version, end( $update_versions ), '<' );
	}

	/**
	 * See if we need to show or run database updates during install.
	 *
	 * @since 3.2.0
	 */
	private static function maybe_update_db_version() {
		if ( self::needs_db_update() ) {
			self::update();
		} else {
			self::update_db_version();
		}
	}

	private static function update() {
		$current_db_version = get_option( 'rtcl_store_db_version' );
		$loop               = 0;

		foreach ( self::get_db_update_callbacks() as $version => $update_callbacks ) {
			if ( version_compare( $current_db_version, $version, '<' ) ) {
				foreach ( $update_callbacks as $update_callback ) {
					if ( is_callable( self::class, $update_callback ) ) {
						self::$update_callback();
					}
				}
				self::update_db_version( $version);
			}
		}
	}

	/**
	 * Update DB version to current.
	 *
	 * @param string|null $version New WooCommerce DB version or null.
	 */
	public static function update_db_version( string $version = null ) {
		update_option( 'rtcl_store_db_version', is_null( $version ) ? RTCL_STORE_VERSION : $version );
	}

	public static function deactivate() {

	}

	private static function create_options() {
		$advSettings = get_option( 'rtcl_advanced_settings', [] );
		if ( ! isset( $advSettings['myaccount_store_endpoint'] ) ) {
			$advSettings['myaccount_store_endpoint'] = 'store';
		}
		if ( ! isset( $advSettings['permalink_store'] ) ) {
			$advSettings['permalink_store'] = 'store';
		}
		if ( ! isset( $advSettings['store_category_base'] ) ) {
			$advSettings['store_category_base'] = 'store-category';
		}
		if ( ! empty( $advSettings['store'] ) ) {
			$id                   = wp_insert_post(
				[
					'post_title'     => __( 'Store', 'classified-listing-store' ),
					'post_content'   => '',
					'post_status'    => 'publish',
					'post_author'    => 1,
					'post_type'      => 'page',
					'comment_status' => 'closed',
				]
			);
			$advSettings['store'] = $id;
		}
		update_option( 'rtcl_advanced_settings', $advSettings );
		$miscSettings = get_option( 'rtcl_misc_settings', [] );
		if ( ! isset( $miscSettings['store_banner_size'] ) ) {
			$miscSettings['store_banner_size'] = [
				'width'  => 992,
				'height' => 300,
				'crop'   => 'yes',
			];
		}
		if ( ! isset( $miscSettings['store_logo_size'] ) ) {
			$miscSettings['store_logo_size'] = [
				'width'  => 200,
				'height' => 150,
				'crop'   => 'yes',
			];
		}
		update_option( 'rtcl_misc_settings', $miscSettings );

		if ( ! get_option( 'rtcl_membership_settings', ) ) {
			$defaults = [
				'number_of_free_ads'        => 3,
				'renewal_days_for_free_ads' => 30,
			];
			add_option( 'rtcl_membership_settings', apply_filters( 'rtcl_membership_settings' . '_defaults', $defaults ) );
		}
	}

	private static function create_tables() {
		global $wpdb;

		$wpdb->hide_errors();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( self::get_schema() );
	}

	private static function update_rtcl_version() {
		delete_option( 'rtcl_store_version' );
		add_option( 'rtcl_store_version', RTCL_STORE_VERSION );
	}

	private static function get_schema() {
		global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}
		$log_table             = $wpdb->prefix . 'rtcl_posting_log';
		$membership_table      = $wpdb->prefix . 'rtcl_membership';
		$membership_meta_table = $wpdb->prefix . 'rtcl_membership_meta';
		$table_schema          = [];
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $log_table ) ) !== $log_table ) {
			$table_schema[] = "CREATE TABLE `$log_table` (
					  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
					  post_id int(11) NOT NULL,
					  user_id int(11) NOT NULL,
					  cat_id int(11) NOT NULL,
					  status ENUM('new', 'renew') NOT NULL DEFAULT 'new',
					  created_at DATETIME NOT NULL,
					  PRIMARY KEY  (id),
					  KEY post_id (post_id),
					  KEY user_id (user_id)
					) $collate;";
		} else {
			$row = $wpdb->get_results( "SHOW COLUMNS from $log_table WHERE field = 'status';" );
			if ( empty( $row ) ) {
				$wpdb->query( "ALTER TABLE $log_table DROP INDEX post_id, ADD KEY post_id (post_id)" );
				$wpdb->query( "ALTER TABLE $log_table ADD COLUMN status ENUM('new', 'renew') NOT NULL DEFAULT 'new' AFTER `cat_id`" );
			}
		}


		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $membership_table ) ) !== $membership_table ) {
			$table_schema[] = "CREATE TABLE `$membership_table` (
                                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                user_id int(11) NOT NULL,
                                ads int(11) NOT NULL DEFAULT '0',
                                posted_ads int(11) NOT NULL DEFAULT '0',
                                expiry_date DATETIME NOT NULL DEFAULT '0000-01-02 00:00:00',
                                member_since DATETIME NOT NULL DEFAULT '0000-01-02 00:00:00',
                                active boolean NOT NULL DEFAULT TRUE,
                                UNIQUE KEY user_id (user_id)
                            ) $collate;";
		}

		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $membership_meta_table ) ) !== $membership_meta_table ) {
			$table_schema[] = "CREATE TABLE `$membership_meta_table` (
                                meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                membership_id bigint(20) unsigned NOT NULL,
                                meta_key varchar(191) NOT NULL,
                                meta_value longtext,
                                KEY membership_id (membership_id),
                                KEY meta_key (meta_key)
                            ) $collate;";
		}

		return $table_schema;

	}

	/**
	 * Drop Rtcl store tables.
	 *
	 * @return void
	 */
	public static function drop_tables() {
		global $wpdb;

		$tables = self::get_tables();

		foreach ( $tables as $table ) {
			$wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore WordPress.WP.PreparedSQL.NotPrepared
		}
	}

	public static function get_tables() {
		global $wpdb;

		$tables = [
			"{$wpdb->prefix}rtcl_posting_log",
			"{$wpdb->prefix}rtcl_membership",
			"{$wpdb->prefix}rtcl_membership_meta",
		];

		return apply_filters( 'rtcl_store_install_get_tables', $tables );

	}

}
