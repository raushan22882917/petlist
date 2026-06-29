<?php

namespace Rtcl\Controllers\Settings;

use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;
use Rtcl\Models\RtclEmail;
use Rtcl\Models\SettingsAPI;
use Rtcl\Services\FormBuilder\FBHelper;
use Rtcl\Services\MaxMindDatabaseService;

class AdminSettings extends SettingsAPI {

	protected $tabs = [];
	protected $option_group = [];
	protected $active_tab;
	protected $current_section;
	protected $gateway_temp_desc;
	protected static $instance = null;
	protected $classMap = [ 'misc' => MiscSettingsController::class ];
	/**
	 * @var array|mixed|void
	 */
	protected $subtabs = [];
	public $maxMindDatabaseService;
	const EXTERNAL_IDS = [];

	public function __construct() {
		$this->classMap = apply_filters( 'rtcl_settings_classMap', $this->classMap );
		add_action( 'admin_menu', [ $this, 'add_main_menu' ] );
		add_action( 'admin_menu', [ $this, 'add_payment_menu' ], 15 );
		add_action( 'admin_menu', [ $this, 'add_form_builder_menu' ] );
		add_action( 'admin_menu', [ $this, 'add_filter_menu' ] );
		add_action( 'admin_menu', [ $this, 'add_settings_menu' ], 50 );
		add_action( 'admin_menu', [ $this, 'add_import_menu' ], 60 );
		add_action( 'admin_menu', [ $this, 'add_addons_themes__menu' ], 99 );
		add_action( 'admin_menu', [ $this, 'add_listing_types_menu' ], 1 );
		add_action( 'admin_init', [ $this, 'preview_emails' ] );
		add_filter( 'plugin_action_links_' . plugin_basename( RTCL_PLUGIN_FILE ), [ $this, 'get_necessary_action' ] );
		add_filter( 'plugin_action_links_' . plugin_basename( RTCL_PLUGIN_FILE ), [ $this, 'get_pro_action' ] );

		if ( apply_filters( 'rtcl_settings_link_on_admin_bar', true ) ) {
			add_action( 'wp_before_admin_bar_render', [ $this, 'add_admin_bar' ], 999 );
		}
		add_filter( 'parent_file', [ $this, 'fix_post_type_menu_new_edit_highlight' ] );
		// Custom column in user table
		add_action( 'manage_users_columns', [ $this, 'register_user_table_column' ], 9 );
		add_action( 'manage_users_custom_column', [ $this, 'manage_user_table_column_view' ], 10, 3 );

		add_action( 'in_admin_header', [ $this, 'remove_all_notice' ], 1000 );

		//TODO : Need to remove when pro plugin released 
		add_action( 'admin_init', [ __CLASS__, 'generate_rest_api_key' ] );
	}

	public static function generate_rest_api_key() {
		if ( isset( $_GET['rtcl_generate_rest_api_key'] ) ) {
			if ( ! isset( $_REQUEST['_wpnonce'] )
				|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'rtcl_generate_rest_api_key' )
			) {
				Functions::add_notice( __( "You are not allow to make this request.", "classified-listing-pro" ), 'error' );
			} else {
				$oldApikey = get_option( 'rtcl_rest_api_key', null );
				update_option( 'rtcl_rest_api_key', wp_generate_uuid4() );
				if ( $oldApikey ) {
					Functions::add_notice( __( "Your Rest API key is regenerated.", "classified-listing-pro" ) );
				} else {
					Functions::add_notice( __( "Your Rest API key is generated.", "classified-listing-pro" ) );
				}
			}
			wp_safe_redirect( admin_url( 'admin.php?page=rtcl-settings&parentId=rtcl_account_settings' ) );
			exit();
		}
	}

	public function remove_all_notice() {
		$screen = get_current_screen();
		if ( ( ! empty( $screen->post_type )
			   && in_array( $screen->post_type, [
				rtcl()->post_type,
				rtcl()->post_type_pricing,
				rtcl()->post_type_cfg,
				rtcl()->post_type_payment,
			] ) )
		) {
			remove_all_actions( 'admin_notices' );
			remove_all_actions( 'all_admin_notices' );
		}
	}

	public function fix_post_type_menu_new_edit_highlight( $parent_file ) {
		global $submenu_file, $current_screen;

		if ( $current_screen->post_type == rtcl()->post_type_pricing ) {
			$submenu_file = 'edit.php?post_type=' . rtcl()->post_type_pricing;
			$parent_file  = 'rtcl-admin';
		}

		if ( $current_screen->post_type == rtcl()->post_type_payment ) {
			$submenu_file = 'edit.php?post_type=' . rtcl()->post_type_payment;
			$parent_file  = 'rtcl-admin';
		}

		return $parent_file;
	}

	function register_user_table_column( $columns ) {
		$columns['rtcl_user_ad_count'] = apply_filters( 'rtcl_user_ac_count_column_title', esc_html__( 'Listings', 'classified-listing' ) );
		if ( Functions::is_user_type_enabled() ) {
			$columns['rtcl_user_type'] = apply_filters( 'rtcl_user_type_column_title', esc_html__( 'Account Type', 'classified-listing' ) );
		}

		return $columns;
	}

	function manage_user_table_column_view( $value, $column_name, $user_id ) {
		if ( $column_name == 'rtcl_user_ad_count' ) {
			$value = count_user_posts( $user_id, rtcl()->post_type );
			if ( $value ) {
				$value = sprintf(
					'<a href="%s" class="edit"><span aria-hidden="true">%s</span><span class="screen-reader-text">%s</span></a>',
					"edit.php?post_type=rtcl_listing",
					$value,
					sprintf(
					/* translators: Hidden accessibility text. %s: Number of posts. */
						_n( '%s listing by this author', '%s posts by this author', $value, 'classified-listing' ),
						number_format_i18n( $value ),
					),
				);
			}
		} elseif ( $column_name == 'rtcl_user_type' ) {
			$user_type = get_user_meta( $user_id, '_rtcl_user_type', true );
			if ( $user_type ) {
				$value = Functions::get_user_type_label( $user_type );
			}
		}

		return $value;
	}

	/**
	 * @param  bool  $new
	 *
	 * @return AdminSettings|null
	 */
	public static function get_instance( $new = false ) {
		// If the single instance hasn't been set, set it now.
		if ( $new || null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function get_necessary_action( $links ) {
		$links[] = '<a target="_blank" href="' . esc_url( 'https://radiustheme.net/publicdemo/classified-listing/' ) . '">Demo</a>';
		$links[] = '<a target="_blank" href="' . esc_url( 'https://www.radiustheme.com/docs/classified-listing/' ) . '">Documentation</a>';

		return $links;
	}

	public function get_pro_action( $links ) {
		$current      = time();
		$currentYear  = gmdate( 'Y' );
		$black_friday = mktime( 0, 0, 0, 11, 10, $currentYear ) <= $current && $current <= mktime( 0, 0, 0, 1, 10, $currentYear + 1 );
		if ( $black_friday ) {
			$links[] = '<a target="_blank" style="color: #FF0000;font-weight: 700;" href="'
					   . esc_url( 'https://www.radiustheme.com/downloads/classified-listing-pro-wordpress/' ) . '">Get 20% Off</a>';
		} else {
			$links[] = '<a target="_blank" style="color: #39b54a;font-weight: 700;" href="'
					   . esc_url( 'https://www.radiustheme.com/downloads/classified-listing-pro-wordpress/' ) . '">Get Pro</a>';
		}

		return $links;
	}

	public function add_admin_bar() {
		if ( ! current_user_can( 'manage_rtcl_options' ) ) {
			return;
		}

		global $wp_admin_bar;
		$url  = add_query_arg( [ 'post_type' => rtcl()->post_type ], admin_url( 'edit.php' ) );
		$args = [
			'id'    => rtcl()->post_type,
			'title' => esc_html__( 'Classified Listing', 'classified-listing' ),
			'href'  => $url,
			'meta'  => [ 'class' => sprintf( '%s-admin-toolbar', rtcl()->post_type ) ],
		];
		$wp_admin_bar->add_menu( $args );

		$category_args = [
			'id'     => rtcl()->post_type . "-category",
			'title'  => esc_html__( 'Categories', 'classified-listing' ),
			'href'   => add_query_arg( [
				'taxonomy'  => rtcl()->category,
				'post_type' => rtcl()->post_type,
			], admin_url( 'edit-tags.php' ) ),
			'parent' => rtcl()->post_type,
			'meta'   => [ 'class' => sprintf( '%s-admin-toolbar-categories', rtcl()->post_type ) ],
		];

		$wp_admin_bar->add_menu( $category_args );

		$location_args = [
			'id'     => rtcl()->post_type . "-location",
			'title'  => esc_html__( 'Locations', 'classified-listing' ),
			'href'   => add_query_arg( [
				'taxonomy'  => rtcl()->location,
				'post_type' => rtcl()->post_type,
			], admin_url( 'edit-tags.php' ) ),
			'parent' => rtcl()->post_type,
			'meta'   => [
				'class' => sprintf( '%s-admin-toolbar-locations', rtcl()->post_type ),
			],
		];

		$wp_admin_bar->add_menu( $location_args );

		$listing_types_args = [
			'id'     => rtcl()->post_type . "-listing-types",
			'title'  => esc_html__( 'Listing Types', 'classified-listing' ),
			'href'   => add_query_arg( [
				'post_type' => rtcl()->post_type,
				'page'      => 'rtcl-listing-type',
			], admin_url( 'edit.php' ) ),
			'parent' => rtcl()->post_type,
			'meta'   => [
				'class' => sprintf( '%s-admin-toolbar-listing-types', rtcl()->post_type ),
			],
		];

		$wp_admin_bar->add_menu( $listing_types_args );
		if ( ! FBHelper::isEnabled() ) {
			$cfg_args = [
				'id'     => rtcl()->post_type . "-custom-fields",
				'title'  => esc_html__( 'Custom Fields', 'classified-listing' ),
				'href'   => add_query_arg( [
					'post_type' => rtcl()->post_type_cfg,
				], admin_url( 'edit.php' ) ),
				'parent' => rtcl()->post_type,
				'meta'   => [
					'class' => sprintf( '%s-admin-toolbar-custom-fields', rtcl()->post_type ),
				],
			];

			$wp_admin_bar->add_menu( $cfg_args );
		}

		$pricing_args = [
			'id'     => rtcl()->post_type . "-pricing",
			'title'  => esc_html__( 'Pricing', 'classified-listing' ),
			'href'   => add_query_arg( [
				'post_type' => rtcl()->post_type_pricing,
			], admin_url( 'edit.php' ) ),
			'parent' => rtcl()->post_type,
			'meta'   => [
				'class' => sprintf( '%s-admin-toolbar-pricing', rtcl()->post_type ),
			],
		];

		$wp_admin_bar->add_menu( $pricing_args );

		$payment_args = [
			'id'     => rtcl()->post_type . "-payment",
			'title'  => esc_html__( 'Payment History', 'classified-listing' ),
			'href'   => add_query_arg( [
				'post_type' => rtcl()->post_type_payment,
			], admin_url( 'edit.php' ) ),
			'parent' => rtcl()->post_type,
			'meta'   => [
				'class' => sprintf( '%s-admin-toolbar-payment', rtcl()->post_type ),
			],
		];

		$wp_admin_bar->add_menu( $payment_args );

		$settings_args = [
			'id'     => rtcl()->post_type . "-settings",
			'title'  => esc_html__( 'Settings', 'classified-listing' ),
			'href'   => add_query_arg( [
				'page' => 'rtcl-settings',
			], admin_url( 'admin.php' ) ),
			'parent' => rtcl()->post_type,
			'meta'   => [
				'class' => sprintf( '%s-admin-toolbar-settings', rtcl()->post_type ),
			],
		];

		$wp_admin_bar->add_menu( $settings_args );

		$settings_args = [
			'id'     => rtcl()->post_type . "-clear-cache",
			'title'  => esc_html__( 'Clear all cache', 'classified-listing' ),
			'href'   => add_query_arg( [
				rtcl()->nonceId    => wp_create_nonce( rtcl()->nonceText ),
				'clear_rtcl_cache' => '',
			], Link::get_current_url() ),
			'parent' => rtcl()->post_type,
			'meta'   => [
				'class' => sprintf( '%s-admin-toolbar-settings', rtcl()->post_type ),
			],
		];

		$wp_admin_bar->add_menu( $settings_args );

		do_action( 'rtcl_admin_bar_menu', $wp_admin_bar, rtcl()->post_type );
	}

	public function add_main_menu() {
		add_menu_page(
			__( 'Classified Listing', 'classified-listing' ),
			__( 'Classified Listing', 'classified-listing' ),
			'manage_rtcl_reports',
			'rtcl-admin',
			[ $this, 'display_reports' ],
			RTCL_URL . '/assets/images/icon-20x20.png',
			5,
		);
		add_submenu_page(
			'rtcl-admin',
			__( 'Home', 'classified-listing' ),
			__( 'Home', 'classified-listing' ),
			'manage_rtcl_reports',
			'rtcl-admin',
			[ $this, 'display_reports' ],
			1,
		);
	}

	public function add_payment_menu() {
		add_submenu_page(
			'rtcl-admin',
			__( 'Payment History', 'classified-listing' ),
			__( 'Payment History', 'classified-listing' ),
			'manage_options',
			'edit.php?post_type=' . rtcl()->post_type_payment,
		);
		add_submenu_page(
			'rtcl-admin',
			__( 'Pricing', 'classified-listing' ),
			__( 'Pricing', 'classified-listing' ),
			'manage_options',
			'edit.php?post_type=' . rtcl()->post_type_pricing,
		);
	}

	public function add_import_menu() {
		add_submenu_page(
			'rtcl-admin',
			__( 'Export / Import', 'classified-listing' ),
			__( 'Export / Import', 'classified-listing' ),
			'manage_rtcl_reports',
			'rtcl-import-export',
			[ $this, 'display_import_export' ],
		);
	}

	public function add_addons_themes__menu() {
		add_submenu_page(
			'rtcl-admin',
			__( 'Get Extensions', 'classified-listing' ),
			__( '<span>Themes & Addons</span>', 'classified-listing' ),
			'manage_options',
			'rtcl-extension',
			[ $this, 'display_extension_view' ],
		);
	}

	public function add_listing_types_menu() {
		add_submenu_page(
			'edit.php?post_type=' . rtcl()->post_type,
			__( 'Listing Types', 'classified-listing' ),
			__( 'Listing Types', 'classified-listing' ),
			'manage_rtcl_options',
			'rtcl-listing-type',
			[ $this, 'display_listing_type' ],
		);
	}

	public function add_form_builder_menu() {
		add_submenu_page(
			'rtcl-admin',
			__( 'Form Builder', 'classified-listing' ),
			__( 'Form Builder', 'classified-listing' ),
			'manage_rtcl_options',
			'rtcl-fb',
			[ $this, 'display_form_builder' ],
		);
	}

	public function add_filter_menu() {
		add_submenu_page(
			'rtcl-admin',
			__( 'Ajax Filter Builder', 'classified-listing' ),
			__( 'Ajax Filter Builder', 'classified-listing' ),
			'manage_rtcl_options',
			'rtcl-ajax-filter',
			[ $this, 'display_ajax_filter' ],
		);
	}

	public function add_settings_menu() {
		add_submenu_page(
			'rtcl-admin',
			__( 'Settings', 'classified-listing' ),
			__( 'Settings', 'classified-listing' ),
			'manage_rtcl_options',
			'rtcl-settings',
			[ $this, 'display_settings_form' ],
		);
	}

	function display_listing_type() {
		require_once RTCL_PATH . 'views/settings/listing-type.php';
	}

	function display_form_builder() {
		?>
		<div id="rtcl-fba-wrap"></div><?php
	}

	function display_ajax_filter() {
		?>
		<div id="rtcl-afb-wrap" class="rtcl-admin-wrap">
			<div class="rtcl-admin-header">
				<h3 class="rtcl-header-title"><?php
					esc_html_e( 'Manage Filter form', 'classified-listing' ); ?></h3>
			</div>
			<div class="rtcl-admin-settings-wrap">
				<div id="rtcl-filter-settings-wrap">
					<div class="rtcl-filter-list">
						<div class="rtcl-filter-list-wrap">
							<?php
							$filterForms = Functions::get_option( 'rtcl_filter_settings' );
							if ( ! empty( $filterForms ) ) {
								foreach ( $filterForms as $filterId => $filterForm ) {
									echo sprintf( '<a data-id="%s" class="rtcl-filter-action-wrap"><span class="rtcl-filter-name">%s</span><span class="rtcl-filter-actions"><i class="rtcl-filter-edit dashicons dashicons-edit"></i><i class="rtcl-filter-remove dashicons dashicons-remove"></i></span></a>',
										esc_attr( $filterId ),
										esc_html( $filterForm['name'] ) );
								}
							}
							?>
						</div>
						<a class="rtcl-admin-btn outline block rtcl-filter-add"
						   title="<?php
						   esc_attr_e( 'Add Filter', 'classified-listing' ); ?>">
							<span
								class="dashicons dashicons-plus-alt2"></span> <?php
							esc_attr_e( 'Add Filter', 'classified-listing' ); ?>
						</a>
					</div>
					<div id="rtcl-filter-wrap"></div>
				</div>
			</div>
		</div>
		<?php
	}

	function display_settings_form() {
		echo "<div id='rtcl-settings-app'></div>";
	}

	function display_import_export() {
		require_once RTCL_PATH . 'views/settings/import-export.php';
	}

	function display_reports() {
		require_once RTCL_PATH . 'views/settings/reports.php';
	}

	function display_extension_view() {
		require_once RTCL_PATH . 'views/settings/extensions/extension.php';
	}

	public function preview_emails() {
		if ( isset( $_GET['preview_rtcl_mail'] ) ) {
			if ( ! ( isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'preview-mail' ) ) ) {
				die( 'Security check' );
			}

			// load the mailer class.
			$mailer = rtcl()->mailer();

			// get the preview email subject.
			$email_heading = __( 'HTML email template', 'classified-listing' );

			// get the preview email content.
			ob_start();
			include( RTCL_PATH . "views/html-email-template-preview.php" );
			$message = ob_get_clean();

			// create a new email.
			$email = new RtclEmail();
			$email->set_heading( $email_heading );

			// wrap the content with the email template and then add styles.
			$message = apply_filters( 'rtcl_mail_content', $message );

			// print the preview email.
			// phpcs:ignore WordPress.Security.EscapeOutput
			echo $message;
			// phpcs:enable
			exit;
		}
	}

	public function maxMindDatabaseService() {
		$this->maxMindDatabaseService = apply_filters( 'rtcl_maxmind_geolocation_database_service', null );
		if ( null === $this->maxMindDatabaseService ) {
			$prefix = $this->get_option( 'maxmind_database_prefix' );
			if ( empty( $prefix ) ) {
				$prefix = wp_generate_password( 32, false );
				$this->update_option( 'maxmind_database_prefix', $prefix );
			}
			$this->maxMindDatabaseService = new MaxMindDatabaseService( $prefix );
		}

		return $this->maxMindDatabaseService;
	}

	public static function save_tax_options() {
		global $wpdb;

		$countries    = array_map( 'sanitize_text_field', $_POST['rtcl_tax_rate_country'] ?? [] );
		$states       = array_map( 'sanitize_text_field', $_POST['rtcl_tax_rate_state'] ?? [] );
		$postcodes    = array_map( 'sanitize_text_field', $_POST['rtcl_tax_rate_postcode'] ?? [] );
		$cities       = array_map( 'sanitize_text_field', $_POST['rtcl_tax_rate_city'] ?? [] );
		$rates        = array_map( 'floatval', $_POST['rtcl_tax_rate'] ?? [] );
		$tax_name     = array_map( 'sanitize_text_field', $_POST['rtcl_tax_rate_name'] ?? [] );
		$tax_priority = array_map( 'intval', $_POST['rtcl_tax_rate_priority'] ?? [] );

		$rows_to_insert = [];
		$param_types    = '%s, %s, %s, %s, %f, %s, %d';

		if ( ! empty( $countries ) ) {
			for ( $i = 0; $i < count( $countries ); $i ++ ) {
				$rows_to_insert[] = [
					$countries[ $i ],
					$states[ $i ] ?? '',
					$cities[ $i ] ?? '',
					$postcodes[ $i ] ?? '',
					$rates[ $i ],
					$tax_name[ $i ],
					$tax_priority[ $i ] ?? '1',
				];
			}
		}

		$table_name = $wpdb->prefix . 'rtcl_tax_rates';

		$query
			= "INSERT INTO {$table_name} (country, country_state, country_city, location_code, tax_rate, tax_rate_name, tax_rate_priority) VALUES ";


		$placeholders = array_fill( 0, count( $rows_to_insert ), "($param_types)" );
		$query        .= implode( ', ', $placeholders );

		$values = [];
		foreach ( $rows_to_insert as $row ) {
			$values = array_merge( $values, $row );
		}

		if ( ! empty( $rows_to_insert ) ) {
			$wpdb->query( "TRUNCATE TABLE $table_name" );
		}

		$result = $wpdb->query( $wpdb->prepare( $query, $values ) );

		if ( false === $result ) {
			$wpdb_error = $wpdb->last_error;
		}
	}
}
