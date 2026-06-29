<?php

namespace Rtcl\Helpers;


use Rtcl\Database\DbMigration;
use Rtcl\Database\Migrations\Forms;
use Rtcl\Models\Roles;

class Installer {

	const DB_VERSION = '5.1.0';

	private static array $db_updates
		= [
			'5.0.0' => [ 'migrate_settings_500' ],
			'5.1.0' => [ 'add_single_layout_column_at_form_table_db_510' ],
		];


	const STATS_TABLE_VERSION = '1.0.0';

	public static function init() {
		add_action( 'init', [ __CLASS__, 'check_version' ], 5 );
		add_action( 'init', [ __CLASS__, 'maybe_create_stats_table' ], 5 );
	}

	/**
	 * Create the listing stats table if it is missing or out of date.
	 *
	 * Runs independently of the plugin version bump so the analytics table is
	 * self-healing; the option guard keeps it to a single dbDelta per version.
	 *
	 * @return void
	 */
	public static function maybe_create_stats_table() {
		$current_version = get_option( 'rtcl_stats_table_version', '0' );

		if ( $current_version === self::STATS_TABLE_VERSION ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( self::get_stats_table_schema() );
		update_option( 'rtcl_stats_table_version', self::STATS_TABLE_VERSION );

		// Run the one-time meta-to-stats migration independently.
		// Uses its own option flag so it can retry on next load if it fails.
		if ( ! get_option( 'rtcl_stats_meta_migrated' ) ) {
			self::migrate_meta_to_stats_table();
		}
	}

	/**
	 * One-time migration of existing post-meta engagement counters into the
	 * rtcl_listing_stats table so that card totals and chart data are
	 * consistent for listings that already had activity before daily tracking
	 * was introduced.
	 *
	 * All pre-existing counts are stored under yesterday's date so they never
	 * conflict with genuinely new daily rows recorded from today onward.
	 *
	 * Guarded by the `rtcl_stats_meta_migrated` option so it runs only once.
	 * On fresh installs (no listings exist) the method sets the flag and
	 * returns immediately without running any INSERT queries.
	 *
	 * @return void
	 */
	private static function migrate_meta_to_stats_table() {
		global $wpdb;

		$table = $wpdb->prefix . 'rtcl_listing_stats';

		// Verify the stats table actually exists before attempting migration.
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			return; // Don't set the flag — let it retry after table is created.
		}

		$post_type = function_exists( 'rtcl' ) && ! empty( rtcl()->post_type )
			? rtcl()->post_type
			: 'rtcl_listing';

		// Fresh install check: skip migration if no listings exist at all.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$has_listings = (bool) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT 1 FROM {$wpdb->posts} WHERE post_type = %s LIMIT 1",
				$post_type
			)
		);

		if ( ! $has_listings ) {
			update_option( 'rtcl_stats_meta_migrated', 1, true );

			return;
		}

		$yesterday = gmdate( 'Y-m-d', strtotime( current_time( 'Y-m-d' ) . ' -1 day' ) );

		$meta_to_stat = [
			'_views'                      => 'view',
			'_rtcl_reveal_phone_whatsapp' => 'reveal',
			'_rtcl_phone_click'           => 'phone_click',
			'_rtcl_whatsapp_click'        => 'whatsapp_click',
			'_notification_by_visitor'    => 'contact',
		];

		$has_error = false;

		foreach ( $meta_to_stat as $meta_key => $stat_key ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			$result = $wpdb->query(
				$wpdb->prepare(
					"INSERT INTO {$table} (listing_id, stat_date, stat_key, stat_count)
					SELECT pm.post_id, %s, %s, CAST(pm.meta_value AS UNSIGNED)
					FROM {$wpdb->postmeta} pm
					INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id AND p.post_type = %s
					WHERE pm.meta_key = %s AND CAST(pm.meta_value AS UNSIGNED) > 0
					ON DUPLICATE KEY UPDATE stat_count = GREATEST(stat_count, VALUES(stat_count))",
					$yesterday,
					$stat_key,
					$post_type,
					$meta_key
				)
			);

			if ( false === $result ) {
				$has_error = true;
			}
		}

		// Only mark as complete if all queries succeeded.
		// On failure the flag stays unset so migration retries on next load.
		if ( ! $has_error ) {
			update_option( 'rtcl_stats_meta_migrated', 1, true );
		}
	}


	public static function check_version() {
		if ( version_compare( get_option( 'rtcl_version' ), RTCL_VERSION, '<' ) ) {
			self::install();
			self::handleTables( false );
			do_action( 'rtcl_upgraded' );
		}
	}

	/**
	 * Is a DB update needed?
	 *
	 * @return boolean
	 */
	public static function needs_db_update(): bool {
		$current_db_version = get_option( 'rtcl_db_version', null );
		$updates            = self::get_db_update_callbacks();
		$update_versions    = array_keys( $updates );
		usort( $update_versions, 'version_compare' );

		return ! is_null( $current_db_version ) && version_compare( $current_db_version, end( $update_versions ), '<' );
	}


	/**
	 * See if we need to show or run database updates during install.
	 *
	 */
	private static function maybe_update_db_version() {
		if ( self::needs_db_update() ) {
			self::doDBUpdate();
		} else {
			self::update_db_version();
		}
	}

	/**
	 * Get list of DB update callbacks.
	 *
	 * @return array
	 * @since  1.5.58
	 */
	public static function get_db_update_callbacks(): array {
		return self::$db_updates;
	}


	private static function doDBUpdate() {
		$current_db_version = get_option( 'rtcl_db_version' );
		foreach ( self::get_db_update_callbacks() as $version => $update_callbacks ) {
			if ( version_compare( $current_db_version, $version, '<' ) ) {
				foreach ( $update_callbacks as $update_callback ) {
					if ( is_callable( self::class, $update_callback ) ) {
						self::$update_callback();
					}
				}
				self::update_db_version( $version );
			}
		}
	}


	public static function install( $network_wide = null ) {
		if ( ! is_blog_installed() ) {
			return;
		}

		// Check if we are not already running this routine.
		if ( 'yes' === get_transient( 'rtcl_installing' ) ) {
			return;
		}

		// If we made it till here nothing is running yet, lets set the transient now.
		set_transient( 'rtcl_installing', 'yes', MINUTE_IN_SECONDS * 10 );

		if ( ! get_option( 'rtcl_version' ) ) {
			self::create_options();
			if ( ! get_option( 'rtcl_installed_from' ) ) {
				add_option( 'rtcl_installed_from', RTCL_VERSION );
			}
		} else {
			if ( ! get_option( 'rtcl_installed_from' ) ) {
				add_option( 'rtcl_installed_from', get_option( 'rtcl_version', RTCL_VERSION ) );
			}
		}

		self::create_tables();
		self::handleTables( $network_wide );
		self::create_roles();
		self::create_cron_jobs();
		self::update_rtcl_version();
		self::maybe_update_db_version();

		delete_transient( 'rtcl_installing' );

		set_transient( 'rtcl_activation_setup_wizard_redirect', 1, 30 );

		do_action( 'rtcl_flush_rewrite_rules' );
		do_action( 'rtcl_installed' );
	}

	private static function update_rtcl_version() {
		update_option( 'rtcl_version', RTCL_VERSION );
	}


	/**
	 * Update DB version to current.
	 *
	 * @param  string|null  $version  New WooCommerce DB version or null.
	 */
	public static function update_db_version( string $version = null ) {
		update_option( 'rtcl_db_version', is_null( $version ) ? self::DB_VERSION : $version );
	}

	private static function create_options() {
		// Insert plugin settings and default values for the first time
		$options = [
			'rtcl_general_settings'               => [
				'include_results_from'    => [ 'child_categories', 'child_locations' ],
				'listing_duration'        => 15,
				'delete_expired_listings' => 15,
				'renew'                   => 'no',
				'new_listing_status'      => 'pending',
				'edited_listing_status'   => 'pending',
				'redirect_new_listing'    => 'submission',
				'redirect_update_listing' => 'submission',
				'has_favourites'          => 'yes',
			],
			'rtcl_general_listing_label_settings' => [
				'new_listing_label'         => esc_html__( "New", 'classified-listing' ),
				'new_listing_threshold'     => 3,
				'popular_listing_threshold' => 1000,
				'popular_listing_label'     => esc_html__( "Popular", 'classified-listing' ),
				'listing_featured_label'    => esc_html__( "Featured", 'classified-listing' ),
			],
			'rtcl_general_location_settings'      => [
				'location_type'         => 'local',
				'location_level_first'  => esc_html__( "State", 'classified-listing' ),
				'location_level_second' => esc_html__( "City", 'classified-listing' ),
				'location_level_third'  => esc_html__( "Town", 'classified-listing' ),
			],
			'rtcl_general_currency_settings'      => [
				'currency'                     => 'USD',
				'currency_position'            => 'right',
				'currency_thousands_separator' => ',',
				'currency_decimal_separator'   => '.',
			],
			'rtcl_general_social_share_settings'  => [
				'social_services' => [ 'facebook', 'twitter' ],
				'social_pages'    => [ 'listing' ],
			],
			'rtcl_archive_listing_settings'       => [
				'listings_per_page' => 20,
				'default_view'      => 'grid',
				'orderby'           => 'date',
				'order'             => 'desc',
				'taxonomy_orderby'  => 'name',
				'taxonomy_order'    => 'asc',
				'display_options'   => [
					'date',
					'user',
					'views',
					'category',
					'location',
					'price',
				],
			],
			'rtcl_single_listing_settings'        => [
				'related_posts_per_page'       => 4,
				'has_report_abuse'             => 'yes',
				'has_contact_form'             => 'yes',
				'detail_page_sidebar_position' => 'right',
				'display_options_detail'       => [
					'date',
					'user',
					'views',
					'category',
					'location',
					'price',
				],
			],
			'rtcl_moderation_settings'            => [
				'text_editor'                => 'wp_editor',
				'enable_business_hours'      => 'no',
				'enable_social_profiles'     => 'no',
				'maximum_images_per_listing' => 5,
				'image_edit_cap'             => 'yes',
			],
			'rtcl_payment_settings'               => [
				'payment'                      => 'yes',
				'use_https'                    => 'yes',
				'billing_address_disabled'     => 'no',
				'currency'                     => 'USD',
				'currency_position'            => 'right',
				'currency_thousands_separator' => ',',
				'currency_decimal_separator'   => '.',
			],
			'rtcl_payment_offline'                => [
				'enabled'      => 'yes',
				'title'        => esc_html__( 'Direct Bank Transfer', 'classified-listing' ),
				'description'  => esc_html__( "Make your payment directly in our bank account. Please use your Order ID as payment reference. Your order won't get approved until the funds have cleared in our account.",
					'classified-listing' ),
				'instructions' => esc_html__( 'Make your payment directly in our bank account. Please use your Order ID as payment reference. Your order won\'t get approved until the funds have cleared in our account.
Account details :
		
Account Name : YOUR ACCOUNT NAME
Account Number : YOUR ACCOUNT NUMBER
Bank Name : YOUR BANK NAME
		
If we don\'t receive your payment within 48 hrs, we will cancel the order.',
					'classified-listing' ),
			],
			'rtcl_email_settings'                 => [
				'from_name'           => get_option( 'blogname' ),
				'from_email'          => get_option( 'admin_email' ),
				'admin_notice_emails' => get_option( 'admin_email' ),
				'email_type'          => 'html',
			],
			'rtcl_email_notifications_settings'   => [
				'notify_admin' => [
					'register_new_user',
					'listing_submitted',
					'order_created',
					'payment_received',
				],
				'notify_users' => [
					'listing_submitted',
					'listing_published',
					'listing_renewal',
					'listing_expired',
					'remind_renewal',
					'order_created',
					'order_completed',
				],
			],
			'rtcl_email_templates_settings'       => [
				'listing_submitted_subject'  => esc_html__( '[{site_title}] {listing_title} - is received', 'classified-listing' ),
				'listing_submitted_heading'  => esc_html__( 'Your listing is received', 'classified-listing' ),
				'listing_published_subject'  => esc_html__( '[{site_title}] {listing_title} - is published', 'classified-listing' ),
				'listing_published_heading'  => esc_html__( 'Your listing is published', 'classified-listing' ),
				'renewal_email_threshold'    => 3,
				'renewal_subject'            => esc_html__( '[{site_name}] {listing_title} - Expiration notice', 'classified-listing' ),
				'renewal_heading'            => esc_html__( 'Expiration notice', 'classified-listing' ),
				'expired_subject'            => esc_html__( '[{site_title}] {listing_title} - Expiration notice', 'classified-listing' ),
				'expired_heading'            => esc_html__( 'Expiration notice', 'classified-listing' ),
				'renewal_reminder_threshold' => 3,
				'renewal_reminder_subject'   => esc_html__( '[{site_title}] {listing_title} - Renewal reminder', 'classified-listing' ),
				'renewal_reminder_heading'   => esc_html__( 'Renewal reminder', 'classified-listing' ),
				'order_created_subject'      => esc_html__( '[{site_title}] #{order_number} Thank you for your order', 'classified-listing' ),
				'order_created_heading'      => esc_html__( 'New Order: #{order_number}', 'classified-listing' ),
				'order_completed_subject'    => esc_html__( '[{site_title}] : #{order_number} Order is completed.', 'classified-listing' ),
				'order_completed_heading'    => esc_html__( 'Payment is completed: #{order_number}', 'classified-listing' ),
				'contact_subject'            => esc_html__( '[{site_title}] Contact via {listing_title}', 'classified-listing' ),
				'contact_heading'            => esc_html__( 'Thank you for mail', 'classified-listing' ),
			],
			'rtcl_account_settings'               => [
				'enable_myaccount_registration' => "yes",
				'enable_user_type'              => "no",
				'seller_user_type_label'        => "Seller",
				'buyer_user_type_label'         => "Buyer",
			],
			'rtcl_style_settings'                 => [
				'primary'       => "#0066bf",
				'link'          => "#111111",
				'link_hover'    => "#0066bf",
				'button'        => "#0066bf",
				'button_hover'  => "#3065c1",
				'button_text'   => "#ffffff",
				'sidebar_width' => [
					'size' => 28,
					'unit' => '%',
				],
			],
			'rtcl_misc_settings'                  => [],
			'rtcl_misc_media_settings'            => [
				'image_size_gallery'           => [ 'width' => 924, 'height' => 462, 'crop' => 'yes' ],
				'image_size_gallery_thumbnail' => [ 'width' => 150, 'height' => 105, 'crop' => 'yes' ],
				'image_size_thumbnail'         => [ 'width' => 320, 'height' => 240, 'crop' => 'yes' ],
				'image_allowed_type'           => [ 'png', 'jpg', 'jpeg', 'webp' ],
				'image_allowed_memory'         => 2,
			],
			'rtcl_misc_map_settings'              => [
				'has_map'        => 'yes',
				'map_type'       => 'osm',
				'map_zoom_level' => 10,
				'map_center'     => [
					'address' => '',
					'lat'     => 0,
					'lng'     => 0,
				],
			],
			'rtcl_chat_settings'                  => [
				'enable'                                => 'yes',
				'unread_message_email'                  => 'yes',
				'remove_inactive_conversation_duration' => 30,
			],
			'rtcl_advanced_settings'              => [
				'template_base'                       => 'rtcl_template',
				'permalink'                           => 'rtcl_listing',
				'category_base'                       => esc_html_x( 'listing-category', 'slug', 'classified-listing' ),
				'location_base'                       => esc_html_x( 'listing-location', 'slug', 'classified-listing' ),
				'tag_base'                            => esc_html_x( 'listing-tag', 'slug', 'classified-listing' ),
				'myaccount_listings_endpoint'         => 'listings',
				'myaccount_favourites_endpoint'       => 'favourites',
				'myaccount_chat_endpoint'             => 'chat',
				'myaccount_edit_account_endpoint'     => 'edit-account',
				'myaccount_payments_endpoint'         => 'payments',
				'myaccount_profile_settings_endpoint' => 'privacy-settings',
				'myaccount_lost_password_endpoint'    => 'lost-password',
				'myaccount_logout_endpoint'           => 'logout',
				'checkout_submission_endpoint'        => 'submission',
				'checkout_promote_endpoint'           => 'promote',
				'checkout_payment_receipt_endpoint'   => 'payment-receipt',
				'checkout_payment_failure_endpoint'   => 'payment-failure',
			],
			'rtcl_ai_settings'                    => [
				'minimum_matching_percentage' => 40,
				'best_matching_percentage'    => 75,
			],
			'rtcl_fb_options'                     => [
				'active' => 1,
			],
		];

		foreach ( $options as $option_name => $defaults ) {
			if ( false === get_option( $option_name ) ) {
				add_option( $option_name, apply_filters( $option_name . '_defaults', $defaults ) );
			}
		}

		$pages = Functions::insert_custom_pages();
		if ( ! empty( $pages ) ) {
			$pSettings = get_option( 'rtcl_advanced_settings', [] );
			foreach ( $pages as $pSlug => $pId ) {
				if ( $pId > 0 ) {
					$pSettings[ $pSlug ] = $pId;
				}
			}
			update_option( 'rtcl_advanced_settings', $pSettings );
		}
	}

	private static function handleTables( $network_wide ) {
		global $wpdb;
		if ( $network_wide ) {
			// Retrieve all site IDs from this network (WordPress >= 4.6 provides easy to use functions for that).
			if ( function_exists( 'get_sites' ) && function_exists( 'get_current_network_id' ) ) {
				$site_ids = get_sites( [ 'fields' => 'ids', 'network_id' => get_current_network_id() ] );
			} else {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$site_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs WHERE site_id = $wpdb->siteid;" );
			}
			// Install the plugin for all these sites.
			foreach ( $site_ids as $site_id ) {
				switch_to_blog( $site_id );
				self::migrate();
				restore_current_blog();
			}
		} else {
			self::migrate();
		}
	}

	public static function migrate() {
		DbMigration::run();
	}

	private static function create_tables() {
		global $wpdb;

		$wpdb->hide_errors();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		//$tables = array_merge( self::get_tax_table_schema(), [] );
		$tables = array_merge( self::get_tax_table_schema(), self::get_stats_table_schema() );
		dbDelta( $tables );
	}

	/**
	 * Schema for the per-listing daily engagement stats table.
	 *
	 * Stores one row per listing / day / metric so the My Account analytics
	 * modal can plot daily trends. dbDelta keeps this idempotent.
	 *
	 * @return array
	 */
	private static function get_stats_table_schema() {
		global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		$table_name = $wpdb->prefix . 'rtcl_listing_stats';

		return [
			"CREATE TABLE {$table_name} (
				stat_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				listing_id BIGINT(20) UNSIGNED NOT NULL,
				stat_date DATE NOT NULL,
				stat_key VARCHAR(32) NOT NULL,
				stat_count BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
				PRIMARY KEY  (stat_id),
				UNIQUE KEY listing_date_key (listing_id, stat_date, stat_key),
				KEY listing_id (listing_id)
			) $collate;",
		];
	}

	/**
	 * @return array
	 */
	static function get_tax_table_schema() {
		global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}
		$tax_table_name = $wpdb->prefix . "rtcl_tax_rates";
		$table_schema   = [];

		if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $tax_table_name ) ) !== $tax_table_name ) {
			$table_schema[] = "CREATE TABLE $tax_table_name (
                          tax_rate_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                          country varchar(2) NOT NULL DEFAULT '',
                          country_state varchar(200) NOT NULL DEFAULT '',
                          country_city varchar(200),
                          location_code varchar(200),
                          tax_rate varchar(8) NOT NULL DEFAULT '',
                          tax_rate_name varchar(200) NOT NULL DEFAULT '',
                          tax_rate_priority BIGINT(20) UNSIGNED NOT NULL,
                          PRIMARY KEY (tax_rate_id)
                        ) $collate;";
		}

		return $table_schema;
	}

	private static function get_schema() {
		global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		return [
			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rtcl_sessions (
						  session_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
						  session_key char(32) NOT NULL,
						  session_value longtext NOT NULL,
						  session_expiry BIGINT UNSIGNED NOT NULL,
						  PRIMARY KEY  (session_key),
						  UNIQUE KEY session_id (session_id)
						) $collate;",
		];
	}

	public static function deactivate() {
		self::clean_cron_jobs();
	}

	public static function clean_cron_jobs() {
		// Un-schedules all previously-scheduled cron jobs
		wp_clear_scheduled_hook( 'rtcl_hourly_scheduled_events' );
		wp_clear_scheduled_hook( 'rtcl_daily_scheduled_events' );
		wp_clear_scheduled_hook( 'rtcl_cleanup_sessions' );
		wp_clear_scheduled_hook( 'rtcl_cleanup_temp_listings' );
	}

	/**
	 * Create cron jobs (clear them first).
	 */
	private static function create_cron_jobs() {
		self::clean_cron_jobs();
		if ( ! wp_next_scheduled( 'rtcl_cleanup_sessions' ) ) {
			wp_schedule_event( time() + ( 6 * HOUR_IN_SECONDS ), 'twicedaily', 'rtcl_cleanup_sessions' );
		}

		if ( ! wp_next_scheduled( 'rtcl_cleanup_temp_listings' ) ) {
			wp_schedule_event( time() + ( 6 * HOUR_IN_SECONDS ), 'twicedaily', 'rtcl_cleanup_temp_listings' );
		}

		if ( ! wp_next_scheduled( 'rtcl_hourly_scheduled_events' ) ) {
			wp_schedule_event( time(), 'hourly', 'rtcl_hourly_scheduled_events' );
		}

		if ( ! wp_next_scheduled( 'rtcl_daily_scheduled_events' ) ) {
			$ve = get_option( 'gmt_offset' ) > 0 ? '-' : '+';
			wp_schedule_event( strtotime( '00:00 tomorrow ' . $ve . absint( get_option( 'gmt_offset' ) ) . ' HOURS' ), 'daily', 'rtcl_daily_scheduled_events' );
		}
	}

	public static function create_roles() {
		Roles::create_roles();
	}

	public static function add_single_layout_column_at_form_table_db_510() {
		Forms::add_single_layout_column();
	}

	public static function migrate_settings_500() {
		if ( get_option( 'rtcl_settings_migrated_500' ) ) {
			return;
		}

		$migration_map = [
			'rtcl_general_settings'           => [
				'rtcl_general_location_settings' => [
					'location_type',
					'location_level_first',
					'location_level_second',
					'location_level_third',
				],
				'rtcl_general_currency_settings' => [
					'currency',
					'currency_position',
					'currency_thousands_separator',
					'currency_decimal_separator',
				],
				'rtcl_archive_listing_settings'  => [
					'listings_per_page',
					'orderby',
					'order',
					'taxonomy_orderby',
					'taxonomy_order',
					'default_view',
				],
				'rtcl_single_listing_settings'   => [
					'related_posts_per_page',
				],
				'rtcl_moderation_settings'       => [
					'text_editor',
				],
			],
			'rtcl_general_directory_settings' => [
				'rtcl_moderation_settings' => [
					'enable_business_hours',
					'enable_social_profiles',
				],
			],
			'rtcl_moderation_settings'        => [
				'rtcl_general_settings'               => [
					'listing_duration',
					'delete_expired_listings',
					'renew',
					'new_listing_status',
					'edited_listing_status',
					'redirect_new_listing',
					'redirect_new_listing_custom',
					'redirect_update_listing',
					'redirect_update_listing_custom',
					'pending_listing_status_after_promotion',
					'has_favourites',
				],
				'rtcl_misc_map_settings'              => [
					'has_map',
				],
				'rtcl_general_listing_label_settings' => [
					'new_listing_label',
					'new_listing_threshold',
					'listing_featured_label',
					'popular_listing_label',
					'popular_listing_threshold',
					'listing_top_label',
					'listing_bump_up_label',
				],
				'rtcl_archive_listing_settings'       => [
					'display_options',
					'listing_enable_top_listing',
					'listing_top_per_page',
				],
				'rtcl_single_listing_settings'        => [
					'has_report_abuse',
					'has_contact_form',
					'has_comment_form',
					'enable_review_rating',
					'enable_update_rating',
					'registered_only',
					'detail_page_sidebar_position',
					'display_options_detail',
				],
			],
			'rtcl_email_settings'             => [
				'rtcl_email_notifications_settings' => [
					'notify_admin',
					'notify_users',
				],
				'rtcl_email_templates_settings'     => [
					'listing_submitted_subject',
					'listing_submitted_heading',
					'listing_published_subject',
					'listing_published_heading',
					'renewal_email_threshold',
					'renewal_subject',
					'renewal_heading',
					'expired_subject',
					'expired_heading',
					'renewal_reminder_threshold',
					'renewal_reminder_subject',
					'renewal_reminder_heading',
					'order_created_subject',
					'order_created_heading',
					'order_completed_subject',
					'order_completed_heading',
					'contact_subject',
					'contact_heading',
				],
			],
			'rtcl_misc_settings'              => [
				'rtcl_general_social_share_settings' => [
					'social_services',
					'social_pages',
				],
				'rtcl_moderation_settings'           => [
					'required_gallery_image',
					'image_edit_cap',
				],
				'rtcl_single_listing_settings'       => [
					'disable_gallery_slider',
					'disable_gallery_video',
					'disable_gallery_zoom',
					'disable_gallery_photoswipe',
				],
				'rtcl_misc_media_settings'           => [
					'image_size_gallery',
					'image_size_gallery_thumbnail',
					'image_size_thumbnail',
					'store_banner_size',
					'store_logo_size',
					'image_allowed_type',
					'image_allowed_memory',
					'placeholder_image',
				],
				'rtcl_misc_map_settings'             => [
					'map_type',
					'map_api_key',
					'map_zoom_level',
					'map_center',
					'maxmind_license_key',
					'maxmind_database_path',
				],
			],
		];

		foreach ( $migration_map as $source_option => $targets ) {
			$source_data = get_option( $source_option, [] );

			if ( ! is_array( $source_data ) ) {
				continue;
			}

			foreach ( $targets as $target_option => $keys ) {
				$target_data = get_option( $target_option, [] );

				if ( ! is_array( $target_data ) ) {
					$target_data = [];
				}

				foreach ( $keys as $key ) {
					if ( isset( $source_data[ $key ] ) ) {
						$target_data[ $key ] = $source_data[ $key ];
						//unset( $source_data[ $key ] );
					}
				}
				update_option( $target_option, $target_data );
			}
			//update_option( $source_option, $source_data );
		}

		update_option( 'rtcl_settings_migrated_500', 1 );
	}
}