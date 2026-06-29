<?php

namespace Rtcl\Controllers\Admin;

use Automatic_Upgrader_Skin;
use Plugin_Upgrader;
use Rtcl\Helpers\Functions;
use Rtcl\Models\Form\Form;
use Rtcl\Services\FormBuilder\FBHelper;

class SetupWizard {

	/**
	 * AJAX actions that belong to the setup wizard plugin installer.
	 *
	 * @var string[]
	 */
	private static $plugin_ajax_actions = [
		'rtcl_setup_wizard_install_plugin',
		'rtcl_setup_wizard_activate_plugin',
		'rtcl_setup_wizard_check_plugin_status',
	];

	public function __construct() {
		add_action( 'admin_notices', [ __CLASS__, 'setup_wizard_notice' ] );
		add_action( 'admin_menu', [ __CLASS__, 'add_setup_wizard_menu' ], 60 );
		add_action( 'wp_ajax_rtcl_close_setup_wizard', [ __CLASS__, 'close_setup_wizard' ] );
		add_action( 'wp_ajax_rtcl_handle_setup_wizard', [ __CLASS__, 'handle_setup_wizard' ] );
		add_action( 'wp_ajax_rtcl_setup_wizard_import_form', [ __CLASS__, 'import_demo_form' ] );
		add_action( 'wp_ajax_rtcl_setup_wizard_import_categories', [ __CLASS__, 'import_demo_categories' ] );
		add_action( 'wp_ajax_rtcl_setup_wizard_import_location', [ __CLASS__, 'import_demo_location' ] );
		add_action( 'wp_ajax_rtcl_setup_wizard_import_listings', [ __CLASS__, 'import_demo_listings' ] );
		add_action( 'wp_ajax_rtcl_setup_wizard_install_plugin', [ __CLASS__, 'install_plugin' ] );
		add_action( 'wp_ajax_rtcl_setup_wizard_activate_plugin', [ __CLASS__, 'activate_plugin' ] );
		add_action( 'wp_ajax_rtcl_setup_wizard_check_plugin_status', [ __CLASS__, 'check_plugin_status' ] );
		add_action( 'admin_init', [ __CLASS__, 'redirect_to_setup_wizard' ] );

		// Block redirects from other plugins BEFORE admin_init fires.
		// Newly activated plugins (e.g. Schema Engine AI, Radius Booking) often add
		// an admin_init hook that calls wp_redirect() + exit() to show their setup page.
		// This kills ALL subsequent AJAX requests, including our install/activate handlers.
		// We must block redirects here in the constructor (which runs during plugin loading,
		// before admin_init) so our AJAX handlers can actually execute.
		if ( wp_doing_ajax() ) {
			$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';
			if ( in_array( $action, self::$plugin_ajax_actions, true ) ) {
				add_filter( 'wp_redirect', '__return_false', 999 );
				ob_start();
			}
		}
	}

	public static function add_setup_wizard_menu() {
		add_submenu_page(
			'rtcl-admin',
			__( 'Setup Wizard', 'classified-listing' ),
			__( 'Setup', 'classified-listing' ),
			'manage_rtcl_options',
			'rtcl-setup-wizard',
			[ __CLASS__, 'display_setup_wizard' ],
		);

		if ( 'yes' === get_option( 'rtcl_setup_wizard_completed' ) || ! self::disallow_to_run_for_theme() ) {
			remove_submenu_page( 'rtcl-admin', 'rtcl-setup-wizard' );
		}
	}

	public static function display_setup_wizard() {
		?>
		<div id="rtcl-setup-wizard-wrap">
			<div id="rtcl-setup-wizard-app"></div>
		</div>
		<?php
	}

	public static function redirect_to_setup_wizard() {
		$install_from = get_option( 'rtcl_installed_from' ) ?: '5.1.0';
		if ( version_compare( $install_from, '5.1.0', '>=' ) && 'yes' !== get_option( 'rtcl_setup_wizard_completed' )
		     && get_transient( 'rtcl_activation_setup_wizard_redirect' )
		     && self::disallow_to_run_for_theme()
		) {
			delete_transient( 'rtcl_activation_setup_wizard_redirect' );
			if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
				return;
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			wp_safe_redirect( admin_url( 'admin.php?page=rtcl-setup-wizard' ) );
			exit;
		}
	}

	/**
	 * Check if the setup wizard should run for the current theme
	 *
	 * @return bool
	 */
	public static function disallow_to_run_for_theme() {
		// Themes for which setup wizard should not run
		$theme_list = self::get_theme_list();
		$theme_list = apply_filters( 'rtcl_disable_setup_wizard_theme_list', $theme_list );

		$current_theme = wp_get_theme();
		$template      = $current_theme->get( 'Template' ); // Parent theme folder
		$stylesheet    = $current_theme->get_stylesheet(); // Active theme (child or parent)

		if ( in_array( $template, $theme_list, true ) || in_array( $stylesheet, $theme_list, true ) ) {
			return false;
		}

		return true;
	}

	public static function get_theme_list() {
		return [
			//'classima',
			'cl-classified',
			'radius-directory',
			'homlisti',
			'listpress',
			'classilist',
			'cldirectory',
			'listygo',
			'petslsit',
			'cl-hotel',
			'cl-restaurant',
			'obitore',
			'cldoctor',
			'servlisting',
			'clplace',
			'clawyer',
			'clequipment',
			'clproperty',
			'clcar',
			'clsoftware',
		];
	}

	/**
	 * @return void
	 */
	public static function setup_wizard_notice() {
		if ( get_option( 'rtcl_setup_wizard_completed' ) === 'yes' || ! self::disallow_to_run_for_theme() ) {
			return;
		}
		?>
		<div class="notice notice-info rtcl-setup-wizard-notice"
		     style="display:flex;flex-direction: column;padding-top: 20px; padding-bottom: 20px; border-left-color: #3232FF">
			<h3 style="margin:0;">
				Classified Listing - Let’s Get Your Site Ready!
			</h3>
			<p style="margin-top: 8px; font-size: 14px;">
				<?php
				esc_html_e( 'To make sure everything works perfectly, please run the setup wizard to configure your basic settings. It only takes a minute.',
					'classified-listing' ); ?>
			</p>
			<p style="margin:0;">
				<a class="button button-primary" href="<?php
				echo esc_url( admin_url( 'admin.php?page=rtcl-setup-wizard' ) ); ?>"
				   style="background: #3232FF;">Run Setup Wizard</a>
				<a class="button button-primary" id="rtcl-close-setup-wizard" href="#"
				   style="border-color: #3232FF; background: transparent; color: #3232FF;">Dismiss this notice</a>
			</p>
		</div>
		<script type="text/javascript">
			jQuery(document).on('click', '#rtcl-close-setup-wizard', function (e) {
				e.preventDefault();
				jQuery.post(ajaxurl, {
					action: 'rtcl_close_setup_wizard',
					__rtcl_wpnonce: '<?php echo wp_create_nonce( rtcl()->nonceText ); ?>',
				}, function (response) {
					if (response.success) {
						jQuery('.rtcl-setup-wizard-notice').fadeOut();
					}
				});
			});
		</script>
		<?php
	}

	/**
	 *  Close setup wizard
	 *
	 * @return void
	 */
	public static function close_setup_wizard() {
		$message = self::check_permission();
		if ( ! empty( $message ) ) {
			wp_send_json_error( [ 'message' => $message ] );
		}
		update_option( 'rtcl_setup_wizard_completed', 'yes' );
		wp_send_json_success( [ 'message' => esc_html__( 'Setup process closed.', 'classified-listing' ) ] );
	}

	/**
	 * @return string
	 */
	public static function check_permission() {
		$message = '';
		if ( ! current_user_can( 'manage_options' ) ) {
			$message = esc_html__( 'Permission denied.', 'classified-listing' );
		}

		if ( ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) ) {
			$message = esc_html__( 'Session error!!', 'classified-listing' );
		}

		return $message;
	}

	/**
	 * @return void
	 */
	public static function handle_setup_wizard() {
		$message = self::check_permission();

		if ( ! empty( $message ) ) {
			wp_send_json_error( [ 'message' => $message ] );
		}

		$wizard_data = $_POST['data'] ?? [];

		if ( empty( $wizard_data ) || ! is_array( $wizard_data ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid setup data.', 'classified-listing' ) ] );
		}

		$selected_types = $wizard_data['selectedTypes'] ?? [];

		if ( ! is_array( $selected_types ) || empty( $selected_types ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'No directory types selected.', 'classified-listing' ) ] );
		}

		$share_data = $wizard_data['preferences']['shareData'] ?? false;
		$share_data = filter_var( $share_data, FILTER_VALIDATE_BOOLEAN );

		if ( ! empty( $share_data ) ) {
			update_option( 'rtcl_data_sharing_enabled', 'yes' );
		}

		// Update location type
		$location_type = $wizard_data['location']['locationType'] ?? null;
		if ( $location_type ) {
			$location_options                  = (array) Functions::get_option( 'rtcl_general_location_settings' );
			$location_options['location_type'] = sanitize_text_field( $location_type );
			update_option( 'rtcl_general_location_settings', $location_options );
		}

		// Update map options
		$map         = isset( $wizard_data['location']['map'] ) && filter_var( $wizard_data['location']['map'], FILTER_VALIDATE_BOOLEAN );
		$map_options = (array) Functions::get_option( 'rtcl_misc_map_settings' );
		if ( $map ) {
			$map_type                = isset( $wizard_data['location']['mapType'] ) ? sanitize_text_field( $wizard_data['location']['mapType'] ) : 'osm';
			$map_options['has_map']  = 'yes';
			$map_options['map_type'] = $map_type;
			if ( 'google' === $map_type ) {
				$map_options['map_api_key'] = isset( $wizard_data['location']['mapAPI'] ) ? sanitize_text_field( $wizard_data['location']['mapAPI'] ) : '';
			}
			$map_options['map_center'] = isset( $wizard_data['location']['defaultLocation'] )
				? sanitize_text_field( $wizard_data['location']['defaultLocation'] ) : '';
			update_option( 'rtcl_misc_map_settings', $map_options );
		} else {
			$map_options['has_map'] = false;
			update_option( 'rtcl_misc_map_settings', $map_options );
		}

		// Set features options
		$general_settings = (array) Functions::get_option( 'rtcl_general_settings' );
		$favourite        = isset( $wizard_data['features']['favourite'] )
		                    && filter_var( $wizard_data['features']['favourite'], FILTER_VALIDATE_BOOLEAN );
		$renew            = isset( $wizard_data['features']['renew'] )
		                    && filter_var( $wizard_data['features']['renew'], FILTER_VALIDATE_BOOLEAN );

		$general_settings['has_favourites'] = $favourite ? 'yes' : false;
		$general_settings['renew']          = $renew ? 'yes' : false;
		update_option( 'rtcl_general_settings', $general_settings );

		// Set payment options
		foreach (
			[
				[
					'option'  => 'rtcl_payment_settings',
					'feature' => 'payment',
					'key'     => 'payment',
				],
				[
					'option'  => 'rtcl_payment_offline',
					'feature' => 'offline',
					'key'     => 'enabled',
				],
				[
					'option'  => 'rtcl_payment_paypal',
					'feature' => 'paypal',
					'key'     => 'enabled',
				],
			] as $payment
		) {
			$options = (array) Functions::get_option( $payment['option'] );
			if ( 'rtcl_payment_settings' === $payment['option'] ) {
				$options['billing_address_disabled'] = "no";
			}
			$enabled                    = isset( $wizard_data['features'][ $payment['feature'] ] )
			                              && filter_var( $wizard_data['features'][ $payment['feature'] ], FILTER_VALIDATE_BOOLEAN );
			$options[ $payment['key'] ] = $enabled ? 'yes' : false;
			update_option( $payment['option'], $options );
		}

		// Set listing filter widget to sidebar
		self::build_ajax_filter();
		self::assign_widget_to_sidebar();

		// Add directory types
		update_option( 'rtcl_setup_wizard_directory_types', $selected_types );
		update_option( 'rtcl_setup_wizard_completed', 'yes' );

		wp_send_json_success( [ 'message' => esc_html__( 'Setup process completed.', 'classified-listing' ) ] );
	}

	public static function build_ajax_filter() {
		$rtcl_filter_settings = [
			'archive-filter' => [
				'name'  => 'Archive Filter',
				'items' => [
					[
						'id'          => 'search',
						'title'       => 'Keyword',
						'placeholder' => 'Keyword',
					],
					[
						'id'         => 'category',
						'title'      => 'Category',
						'type'       => 'checkbox',
						'show_count' => 1,
						'more_less'  => 1,
					],
					[
						'id'         => 'location',
						'title'      => 'Location',
						'type'       => 'checkbox',
						'show_count' => 1,
						'more_less'  => 1,
					],
					[
						'id'        => 'price_range',
						'title'     => 'Price Range',
						'max_price' => 50000,
						'step'      => 1000,
					],
				],
			],
		];

		update_option( 'rtcl_filter_settings', $rtcl_filter_settings );
	}

	public static function assign_widget_to_sidebar() {
		$sidebar_id     = 'rtcl-archive-sidebar';  // Archive sidebar ID
		$widget_id_base = 'rtcl-widget-ajax-filter';   // widget id base

		$widgets = [
			[
				'title'     => 'Filter',
				'filter_id' => 'archive-filter',
			],
		];

		// Save updated widget option
		update_option( 'widget_' . $widget_id_base, $widgets );

		// Get widget numeric index (last one we just added)
		end( $widgets );
		$new_widget_number  = key( $widgets );
		$widget_instance_id = $widget_id_base . '-' . $new_widget_number;

		// Get all sidebar widgets
		$sidebars_widgets = get_option( 'sidebars_widgets', [] );

		// Replace sidebar with only listing filter widget
		$sidebars_widgets[ $sidebar_id ] = [ $widget_instance_id ];

		// Save it back
		update_option( 'sidebars_widgets', $sidebars_widgets );
	}

	/**
	 * @return void
	 */
	public static function import_demo_form() {
		$message = self::check_permission();

		if ( ! empty( $message ) ) {
			wp_send_json_error( [ 'message' => $message ] );
		}

		$wizard_data    = $_POST['data'] ?? [];
		$selected_types = $wizard_data['selectedTypes'] ?? [];

		if ( ! is_array( $selected_types ) || empty( $selected_types ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'No directory types selected.', 'classified-listing' ) ] );
		}

		$results = $form_ids = [];

		foreach ( $selected_types as $slug => $name ) {
			$form_file = rtcl()->plugin_path() . "/sample-data/wizard/form/{$slug}.json";

			if ( ! file_exists( $form_file ) ) {
				$results[] = [
					'type'    => $slug,
					'status'  => 'error',
					'message' => sprintf( esc_html__( '%s: Form File Not Found', 'classified-listing' ), $name ),
				];
				continue;
			}

			$form_data = file_get_contents( $form_file );
			$form_json = json_decode( $form_data, true );

			if ( json_last_error() !== JSON_ERROR_NONE ) {
				$results[] = [
					'type'    => $slug,
					'status'  => 'error',
					'message' => sprintf( esc_html__( '%s: Invalid JSON in Form', 'classified-listing' ), $name ),
				];
				continue;
			}

			if ( $form_json && is_array( $form_json ) ) {
				foreach ( $form_json as $formItem ) {
					$title    = ! empty( $formItem['title'] ) ? sanitize_text_field( $formItem['title'] )
						: esc_html__( 'Imported Form', 'classified-listing' );
					$formData = [
						'title'        => $title,
						'slug'         => FBHelper::getUniqueSlug( $title ),
						'status'       => ! empty( $formItem['status'] )
						                  && in_array( $formItem['status'], [
							'publish',
							'draft',
						] ) ? $formItem['status'] : 'publish',
						'default'      => 0,
						'settings'     => ! empty( $formItem['settings'] ) ? $formItem['settings'] : null,
						'fields'       => ! empty( $formItem['fields'] ) ? $formItem['fields'] : null,
						'sections'     => ! empty( $formItem['sections'] ) ? $formItem['sections'] : [],
						'translations' => ! empty( $formItem['translations'] ) ? $formItem['translations'] : null,
						'created_by'   => ! empty( $formItem['created_by'] ) ? absint( $formItem['created_by'] ) : get_current_user_id(),
					];

					if ( empty( $formData['fields'] ) || empty( $formData['sections'] ) ) {
						$results[] = [
							'type'    => $slug,
							'status'  => 'error',
							'message' => sprintf( esc_html__( '%s: You have a faulty JSON file', 'classified-listing' ), $name ),
						];
						continue;
					}

					$form = Form::query()->insert( $formData );
					if ( $form ) {
						do_action( 'rtcl/fb/form_imported', $form->id );
						$form_ids[ $slug ] = $form->id;
						$results[]         = [
							'type'    => $slug,
							'status'  => 'success',
							'message' => sprintf( esc_html__( '%s: Form Imported', 'classified-listing' ), $name ),
						];
					} else {
						$results[] = [
							'type'    => $slug,
							'status'  => 'error',
							'message' => sprintf( esc_html__( '%s: Error while Importing Form', 'classified-listing' ), $name ),
						];
					}
				}
			}
		}

		wp_send_json_success( [
			'message'  => esc_html__( 'Form Import Process Completed.', 'classified-listing' ),
			'results'  => $results,
			'form_ids' => $form_ids,
		] );
	}

	/**
	 * @return void
	 */
	public static function import_demo_categories() {
		$message = self::check_permission();

		if ( ! empty( $message ) ) {
			wp_send_json_error( [ 'message' => $message ] );
		}

		$wizard_data    = $_POST['data'] ?? [];
		$selected_types = $wizard_data['selectedTypes'] ?? [];

		if ( ! is_array( $selected_types ) || empty( $selected_types ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'No Directory Types Selected.', 'classified-listing' ) ] );
		}

		$json_file = rtcl()->plugin_path() . '/sample-data/wizard/categories.json';

		if ( ! file_exists( $json_file ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Categories File Not Found.', 'classified-listing' ) ] );
		}

		$categories = json_decode( file_get_contents( $json_file ), true );
		if ( empty( $categories ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid Categories Format.', 'classified-listing' ) ] );
		}

		$results = [];

		foreach ( $selected_types as $type => $name ) {
			if ( ! isset( $categories[ $type ] ) || ! is_array( $categories[ $type ] ) ) {
				$results[] = [
					'type'    => $type,
					'status'  => 'error',
					'message' => sprintf( esc_html__( '%s: No Categories Found', 'classified-listing' ), $name ),
				];
				continue;
			}

			$inserted_count = 0;
			foreach ( $categories[ $type ] as $cat ) {
				$return = Functions::create_term( rtcl()->category, $cat );
				if ( ! empty( $return['success'] ) ) {
					$inserted_count ++;
					if ( ! empty( $cat['child'] ) ) {
						$children = $cat['child'];
						foreach ( $children as $child ) {
							$parent_term     = $return['data'] ?? [];
							$child['parent'] = ! empty( $parent_term['term_id'] ) ? $parent_term['term_id'] : 0;
							Functions::create_term( rtcl()->category, $child );
						}
					}
				}
			}

			$results[] = [
				'type'    => $type,
				'status'  => 'success',
				'message' => sprintf( esc_html__( '%s: %d Categories Imported', 'classified-listing' ), $name, $inserted_count ),
			];
		}

		wp_send_json_success( [
			'message' => esc_html__( 'Categories Import Process Completed.', 'classified-listing' ),
			'results' => $results,
		] );
	}

	/**
	 * @return void
	 */
	public static function import_demo_location() {
		$message = self::check_permission();

		if ( ! empty( $message ) ) {
			wp_send_json_error( [ 'message' => $message ] );
		}

		$message = esc_html__( "Location imported successfully.", "classified listing" );

		$wizard_data = $_POST['data'] ?? [];
		$country     = $wizard_data['preferences']['country'] ?? '';

		if ( empty( $country ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Please select a country.', 'classified-listing' ) ] );
		}

		$json_file = rtcl()->plugin_path() . '/sample-data/wizard/locations.json';

		if ( ! file_exists( $json_file ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Location file not found.', 'classified-listing' ) ] );
		}

		$location = json_decode( file_get_contents( $json_file ), true );

		if ( empty( $location ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid location format.', 'classified-listing' ) ] );
		}

		$countries_to_import = [];

		if ( strtolower( $country ) === 'all' ) {
			$countries_to_import = $location;
		} else {
			if ( ! isset( $location[ $country ] ) ) {
				wp_send_json_error( [ 'message' => esc_html__( 'Selected country not found in location file.', 'classified-listing' ) ] );
			}
			$countries_to_import[ $country ] = $location[ $country ];
		}

		foreach ( $countries_to_import as $country_code => $country_data ) {
			$regions = $country_data['regions'] ?? [];
			foreach ( $regions as $region ) {
				$return = Functions::create_term( rtcl()->location, $region );
				if ( ! empty( $return['success'] ) ) {
					if ( ! empty( $region['child'] ) ) {
						$cities = $region['child'];
						foreach ( $cities as $city ) {
							$parent_term    = $return['data'] ?? [];
							$city['parent'] = ! empty( $parent_term['term_id'] ) ? $parent_term['term_id'] : 0;
							Functions::create_term( rtcl()->location, $city );
						}
					}
				}
			}
		}

		wp_send_json_success( [ 'message' => $message ] );
	}

	/**
	 * @return void
	 */
	public static function import_demo_listings() {
		$message = self::check_permission();

		if ( ! empty( $message ) ) {
			wp_send_json_error( [ 'message' => $message ] );
		}

		$wizard_data    = $_POST['data'] ?? [];
		$selected_types = $wizard_data['selectedTypes'] ?? [];
		$form_ids       = $wizard_data['form_ids'] ?? [];

		if ( ! is_array( $selected_types ) || empty( $selected_types ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'No directory types selected.', 'classified-listing' ) ] );
		}

		$location_type = $wizard_data['location']['locationType'] ?? null;

		$results = [];

		foreach ( $selected_types as $slug => $name ) {
			$data_file = rtcl()->plugin_path() . "/sample-data/wizard/listings/{$slug}.csv";

			if ( ! file_exists( $data_file ) ) {
				$results[] = [
					'type'    => $slug,
					'status'  => 'error',
					'message' => sprintf( esc_html__( '%s: Listings File Not Found', 'classified-listing' ), $name ),
				];
				continue;
			}

			$file = fopen( $data_file, 'r' );
			if ( ! $file ) {
				$results[] = [
					'type'    => $slug,
					'status'  => 'error',
					'message' => sprintf( esc_html__( '%s: Error Opening File', 'classified-listing' ), $name ),
				];
				continue;
			}

			$header = fgetcsv( $file );

			if ( empty( $header ) || ! is_array( $header ) ) {
				fclose( $file );
				$results[] = [
					'type'    => $slug,
					'status'  => 'error',
					'message' => sprintf( esc_html__( '%s: Invalid CSV Header', 'classified-listing' ), $name ),
				];
				continue;
			}

			$count    = 0;
			$imported = 0;

			while ( ( $row = fgetcsv( $file ) ) !== false && $count < 10 ) {
				if ( count( $header ) !== count( $row ) ) {
					continue;
				}

				$data = array_combine( $header, $row );

				$meta_data = [];

				if ( ! empty( $form_ids[ $slug ] ) && absint( $form_ids[ $slug ] ) > 0 ) {
					$meta_data['_rtcl_form_id'] = absint( $form_ids[ $slug ] );
				}

				if ( ! empty( $data['ad_type'] ) ) {
					$meta_data['ad_type'] = sanitize_text_field( $data['ad_type'] );
				}

				if ( ! empty( $data['phone'] ) ) {
					$meta_data['phone'] = sanitize_text_field( $data['phone'] );
				}

				if ( ! empty( $data['website'] ) ) {
					$meta_data['website'] = esc_url_raw( $data['website'] );
				}

				if ( ! empty( $data['email'] ) ) {
					$meta_data['email'] = sanitize_email( $data['email'] );
				}

				if ( 'local' === $location_type ) {
					if ( ! empty( $data['address'] ) ) {
						$meta_data['address'] = sanitize_text_field( $data['address'] );
					}
					if ( ! empty( $data['zipcode'] ) ) {
						$meta_data['zipcode'] = sanitize_text_field( $data['zipcode'] );
					}
				}

				if ( ! empty( $data['pricing_type'] ) ) {
					$meta_data['_rtcl_listing_pricing'] = sanitize_text_field( $data['pricing_type'] );
				}

				if ( ! empty( $data['price_type'] ) ) {
					$meta_data['price_type'] = sanitize_text_field( $data['price_type'] );
				}

				if ( ! empty( $data['price'] ) ) {
					$meta_data['price'] = floatval( $data['price'] );
				}

				if ( ! empty( $data['max_price'] ) ) {
					$meta_data['_rtcl_max_price'] = floatval( $data['max_price'] );
				}

				if ( ! empty( $data['price_unit'] ) ) {
					$meta_data['_rtcl_price_unit'] = floatval( $data['price_unit'] );
				}

				if ( ! empty( $data['video_url'] ) ) {
					$meta_data['_rtcl_video_urls'] = explode( ',', $data['video_url'] );
				}

				if ( ! empty( $data['social_profiles'] ) ) {
					$meta_data['_rtcl_social_profiles'] = Functions::prepare_listing_social_links( $data['social_profiles'] );
				}

				switch ( $slug ) {
					case 'doctors':
						if ( ! empty( $data['doctor_meet_time'] ) ) {
							$meta_data['doctor_meet_time'] = sanitize_text_field( $data['doctor_meet_time'] );
						}
						break;
					case 'services':
						if ( ! empty( $data['service_area'] ) ) {
							$meta_data['service_area'] = sanitize_text_field( $data['service_area'] );
						}
						break;
					case 'real_estate':
						if ( ! empty( $data['type'] ) ) {
							$meta_data['type'] = sanitize_text_field( $data['type'] );
						}
						if ( ! empty( $data['parking'] ) ) {
							$meta_data['parking'] = sanitize_text_field( $data['parking'] );
						}
						if ( ! empty( $data['sqft'] ) ) {
							$meta_data['sqft'] = absint( $data['sqft'] );
						}
						if ( ! empty( $data['build_year'] ) ) {
							$meta_data['build_year'] = absint( $data['build_year'] );
						}
						break;
					case 'car':
						if ( ! empty( $data['number-of-doors'] ) ) {
							$meta_data['number-of-doors'] = absint( $data['number-of-doors'] );
						}
						if ( ! empty( $data['number-of-seats'] ) ) {
							$meta_data['number-of-seats'] = absint( $data['number-of-seats'] );
						}
						if ( ! empty( $data['engine-capacity-cc'] ) ) {
							$meta_data['engine-capacity-cc'] = absint( $data['engine-capacity-cc'] );
						}
						if ( ! empty( $data['mileage-km'] ) ) {
							$meta_data['mileage-km'] = absint( $data['mileage-km'] );
						}
						if ( ! empty( $data['car-condition'] ) ) {
							$meta_data['car-condition'] = sanitize_text_field( $data['car-condition'] );
						}
						if ( ! empty( $data['select_make'] ) ) {
							$meta_data['select_make'] = sanitize_text_field( $data['select_make'] );
						}
						if ( ! empty( $data['select_model'] ) ) {
							$meta_data['select_model'] = sanitize_text_field( $data['select_model'] );
						}
						if ( ! empty( $data['fuel-type'] ) ) {
							$meta_data['fuel-type'] = sanitize_text_field( $data['fuel-type'] );
						}
						if ( ! empty( $data['drive-type'] ) ) {
							$meta_data['drive-type'] = sanitize_text_field( $data['drive-type'] );
						}
						if ( ! empty( $data['exterior-color'] ) ) {
							$meta_data['exterior-color'] = sanitize_text_field( $data['exterior-color'] );
						}
						if ( ! empty( $data['interior-color'] ) ) {
							$meta_data['interior-color'] = sanitize_text_field( $data['interior-color'] );
						}
						if ( ! empty( $data['year-of-manufacture'] ) ) {
							$meta_data['year-of-manufacture'] = sanitize_text_field( $data['year-of-manufacture'] );
						}
						if ( ! empty( $data['transmission'] ) ) {
							$meta_data['transmission'] = sanitize_text_field( $data['transmission'] );
						}
						if ( ! empty( $data['body-type'] ) ) {
							$meta_data['body-type'] = sanitize_text_field( $data['body-type'] );
						}
						if ( ! empty( $data['transmission-type'] ) ) {
							$meta_data['transmission-type'] = sanitize_text_field( $data['transmission-type'] );
						}
						break;
					case 'classified':
						if ( ! empty( $data['radio_747d21f8'] ) ) {
							$meta_data['radio_747d21f8'] = sanitize_text_field( $data['radio_747d21f8'] );
						}
						if ( ! empty( $data['textarea_m2vi5ypu'] ) ) {
							$meta_data['textarea_m2vi5ypu'] = sanitize_textarea_field( $data['textarea_m2vi5ypu'] );
						}
						if ( ! empty( $data['number_m2vi7was'] ) ) {
							$meta_data['number_m2vi7was'] = absint( $data['number_m2vi7was'] );
						}
						if ( ! empty( $data['number_m2vi93uf'] ) ) {
							$meta_data['number_m2vi93uf'] = absint( $data['number_m2vi93uf'] );
						}
						if ( ! empty( $data['number_m2vib4ix'] ) ) {
							$meta_data['number_m2vib4ix'] = absint( $data['number_m2vib4ix'] );
						}
						if ( ! empty( $data['text_m2vicdiu'] ) ) {
							$meta_data['text_m2vicdiu'] = sanitize_text_field( $data['text_m2vicdiu'] );
						}
						if ( ! empty( $data['radio_m2vj5x0z'] ) ) {
							$meta_data['radio_m2vj5x0z'] = sanitize_text_field( $data['radio_m2vj5x0z'] );
						}
						if ( ! empty( $data['text_m2vk03e6'] ) ) {
							$meta_data['text_m2vk03e6'] = absint( $data['text_m2vk03e6'] );
						}
						break;
				}

				$listing_data = [
					'post_type'    => rtcl()->post_type,
					'post_title'   => sanitize_text_field( $data['title'] ?? '' ),
					'post_content' => wp_kses_post( $data['content'] ?? '' ),
					'post_excerpt' => sanitize_textarea_field( $data['excerpt'] ?? '' ),
					'post_date'    => sanitize_text_field( $data['post_date'] ?? '' ),
					'post_author'  => absint( $data['post_author_id'] ?? 1 ),
					'post_status'  => sanitize_text_field( $data['status'] ?? 'publish' ),
					'meta_input'   => $meta_data,
				];

				if ( empty( $listing_data['post_title'] ) ) {
					continue;
				}

				$post_id = wp_insert_post( $listing_data );

				if ( ! is_wp_error( $post_id ) ) {
					$imported ++;

					if ( ! empty( $data['gallery_images'] ) ) {
						$attachment_ids = Functions::process_listing_image( $data['gallery_images'], $post_id );

						if ( ! empty( $attachment_ids ) && is_array( $attachment_ids ) ) {
							Functions::set_listing_images( $post_id, $attachment_ids );
						}
					}
					if ( ! empty( $data['categories'] ) ) {
						Functions::set_listing_term( $data['categories'], rtcl()->category, $post_id );
					}
					if ( 'local' === $location_type && ! empty( $data['locations'] ) ) {
						$provided_term = trim( $data['locations'] );

						$term_id = null;

						if ( $provided_term ) {
							$term = term_exists( $provided_term, rtcl()->location );
							if ( $term && ! is_wp_error( $term ) ) {
								$term_id = (int) $term['term_id'];
							}
						}

						if ( ! $term_id ) {
							$terms = get_terms( [
								'taxonomy'   => rtcl()->location,
								'hide_empty' => false,
								'fields'     => 'ids',
							] );

							if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
								$term_id = $terms[ array_rand( $terms ) ];
							}
						}

						if ( $term_id ) {
							wp_set_object_terms( $post_id, $term_id, rtcl()->location );
						}
					}
					if ( ! empty( $data['tags'] ) ) {
						$name = trim( $data['tags'] );
						$tags = explode( ',', $name );
						foreach ( $tags as $tag ) {
							$tag = trim( $tag );
							if ( ! empty( $tag ) ) {
								wp_set_object_terms( $post_id, $tag, rtcl()->tag );
							}
						}
					}
				} else {
					error_log( $post_id->get_error_message() );
				}

				$count ++;
			}

			fclose( $file );

			$results[] = [
				'type'    => $slug,
				'status'  => 'success',
				'message' => sprintf(
					esc_html__( '%s: %d Listings Imported', 'classified-listing' ),
					$name,
					$imported,
				),
			];
		}

		wp_send_json_success( [
			'message' => esc_html__( 'Listings Import Process Completed.', 'classified-listing' ),
			'results' => $results,
		] );
	}

	/**
	 * Get the raw recommended plugins list.
	 *
	 * @return array
	 */
	private static function get_recommended_plugins_list() {
		return [
			[
				'slug'        => 'classified-listing-toolkits',
				'name'        => esc_html__( 'Toolkits Addon', 'classified-listing' ),
				'description' => esc_html__( 'Elementor Widgets & Divi Modules for Classified Listing.', 'classified-listing' ),
				'isFocused'   => true,
				'defaultOn'   => true,
			],
			[
				'slug'        => 'review-schema',
				'name'        => esc_html__( 'SchemaEngine AI', 'classified-listing' ),
				'description' => esc_html__( 'Generate AI-powered schema markup for better SEO and rich Google results.', 'classified-listing' ),
				'isFocused'   => true,
				'defaultOn'   => true,
			],
			[
				'slug'        => 'radius-booking',
				'name'        => esc_html__( 'Radius Booking', 'classified-listing' ),
				'description' => esc_html__( 'WordPress booking plugin for appointments, staff management and reminders.', 'classified-listing' ),
				'isFocused'   => true,
				'defaultOn'   => false,
			],
			[
				'slug'        => 'the-post-grid',
				'name'        => esc_html__( 'The Post Grid', 'classified-listing' ),
				'description' => esc_html__( 'Display blog posts in beautiful grid, list, and slider layouts with filters.', 'classified-listing' ),
			],
			[
				'slug'        => 'shopbuilder',
				'name'        => esc_html__( 'ShopBuilder', 'classified-listing' ),
				'description' => esc_html__( 'Build stunning WooCommerce stores with drag-and-drop builder.', 'classified-listing' ),
			],
			[
				'slug'        => 'tlp-food-menu',
				'name'        => esc_html__( 'Food Menu', 'classified-listing' ),
				'description' => esc_html__( 'Create beautiful restaurant menus, categories, and layouts.', 'classified-listing' ),
			],
			[
				'slug'        => 'testimonial-slider-and-showcase',
				'name'        => esc_html__( 'Testimonial Slider', 'classified-listing' ),
				'description' => esc_html__( 'Display customer testimonials with responsive slider and grid layouts.', 'classified-listing' ),
			],
			[
				'slug'        => 'woo-product-variation-gallery',
				'name'        => esc_html__( 'Variation Gallery', 'classified-listing' ),
				'description' => esc_html__( 'WooCommerce plugin for unlimited additional variation image galleries.', 'classified-listing' ),
			],
			[
				'slug'        => 'woo-product-variation-swatches',
				'name'        => esc_html__( 'Variation Swatches', 'classified-listing' ),
				'description' => esc_html__( 'WooCommerce variations into images, colors, labels, and radios.', 'classified-listing' ),
			],
		];
	}

	/**
	 * Get recommended plugin slugs for whitelist validation.
	 *
	 * @return array
	 */
	private static function get_recommended_plugin_slugs() {
		return array_column( self::get_recommended_plugins_list(), 'slug' );
	}

	/**
	 * Get recommended plugins with installed/active status.
	 *
	 * @return array
	 */
	public static function get_recommended_plugins() {
		$plugins = self::get_recommended_plugins_list();

		if ( ! function_exists( 'get_plugins' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$installed_plugins = get_plugins();
		$active_plugins    = get_option( 'active_plugins', [] );

		foreach ( $plugins as $key => $plugin ) {
			$plugin_file = self::get_plugin_file( $plugin['slug'], $installed_plugins );

			$plugins[ $key ]['installed'] = ! empty( $plugin_file );
			$plugins[ $key ]['active']    = ! empty( $plugin_file ) && in_array( $plugin_file, $active_plugins, true );
		}

		return $plugins;
	}

	/**
	 * Get plugin file path from slug.
	 *
	 * @param  string  $slug  Plugin slug.
	 * @param  array  $installed_plugins  Installed plugins list.
	 *
	 * @return string|false
	 */
	private static function get_plugin_file( $slug, $installed_plugins ) {
		foreach ( $installed_plugins as $file => $plugin ) {
			if ( strpos( $file, $slug . '/' ) === 0 || $file === $slug . '.php' ) {
				return $file;
			}
		}

		return false;
	}

	/**
	 * Clean any stray output and send a JSON response.
	 *
	 * Newly activated plugins may output content or send redirect headers
	 * on subsequent AJAX requests (e.g. via admin_init hooks).
	 * This ensures a clean JSON response regardless.
	 *
	 * @param bool  $success Whether the response is a success.
	 * @param array $data    Response data.
	 *
	 * @return void
	 */
	private static function send_json( $success, $data ) {
		while ( ob_get_level() > 0 ) {
			ob_end_clean();
		}
		header_remove( 'Location' );

		if ( $success ) {
			wp_send_json_success( $data );
		} else {
			wp_send_json_error( $data );
		}
	}

	/**
	 * Install a plugin via AJAX.
	 *
	 * @return void
	 */
	public static function install_plugin() {
		$message = self::check_permission();
		if ( ! empty( $message ) ) {
			self::send_json( false, [ 'message' => $message ] );
		}

		if ( ! current_user_can( 'install_plugins' ) ) {
			self::send_json( false, [ 'message' => esc_html__( 'You do not have permission to install plugins.', 'classified-listing' ) ] );
		}

		$slug = isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '';
		if ( empty( $slug ) ) {
			self::send_json( false, [ 'message' => esc_html__( 'Plugin slug is required.', 'classified-listing' ) ] );
		}

		$allowed_slugs = self::get_recommended_plugin_slugs();
		if ( ! in_array( $slug, $allowed_slugs, true ) ) {
			self::send_json( false, [ 'message' => esc_html__( 'Plugin is not in the allowed list.', 'classified-listing' ) ] );
		}

		include_once ABSPATH . 'wp-admin/includes/file.php';
		include_once ABSPATH . 'wp-admin/includes/misc.php';
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		WP_Filesystem();

		$api = plugins_api( 'plugin_information', [
			'slug'   => $slug,
			'fields' => [
				'short_description' => false,
				'sections'          => false,
				'requires'          => false,
				'rating'            => false,
				'ratings'           => false,
				'downloaded'        => false,
				'last_updated'      => false,
				'added'             => false,
				'tags'              => false,
				'compatibility'     => false,
				'homepage'          => false,
				'donate_link'       => false,
			],
		] );

		if ( is_wp_error( $api ) ) {
			self::send_json( false, [ 'message' => esc_html__( 'Plugin not found on WordPress.org.', 'classified-listing' ) ] );
		}

		$upgrader = new Plugin_Upgrader( new Automatic_Upgrader_Skin() );
		$result   = $upgrader->install( $api->download_link );

		if ( is_wp_error( $result ) ) {
			self::send_json( false, [ 'message' => $result->get_error_message() ] );
		}

		if ( ! $result ) {
			self::send_json( false, [ 'message' => esc_html__( 'Plugin installation failed.', 'classified-listing' ) ] );
		}

		self::send_json( true, [ 'message' => esc_html__( 'Plugin installed successfully.', 'classified-listing' ) ] );
	}

	/**
	 * Activate a plugin via AJAX.
	 *
	 * @return void
	 */
	public static function activate_plugin() {
		$message = self::check_permission();
		if ( ! empty( $message ) ) {
			self::send_json( false, [ 'message' => $message ] );
		}

		if ( ! current_user_can( 'activate_plugins' ) ) {
			self::send_json( false, [ 'message' => esc_html__( 'You do not have permission to activate plugins.', 'classified-listing' ) ] );
		}

		$slug = isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '';
		if ( empty( $slug ) ) {
			self::send_json( false, [ 'message' => esc_html__( 'Plugin slug is required.', 'classified-listing' ) ] );
		}

		$allowed_slugs = self::get_recommended_plugin_slugs();
		if ( ! in_array( $slug, $allowed_slugs, true ) ) {
			self::send_json( false, [ 'message' => esc_html__( 'Plugin is not in the allowed list.', 'classified-listing' ) ] );
		}

		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		$installed_plugins = get_plugins();
		$plugin_file       = self::get_plugin_file( $slug, $installed_plugins );

		if ( ! $plugin_file ) {
			self::send_json( false, [ 'message' => esc_html__( 'Plugin is not installed.', 'classified-listing' ) ] );
		}

		// Shutdown handler guarantees a JSON response even if a plugin calls exit()/die() during activation.
		// WordPress updates active_plugins BEFORE firing the activation hook,
		// so the plugin is in the list even if exit() is called during the hook.
		$response_sent = false;

		register_shutdown_function( function () use ( $plugin_file, &$response_sent ) {
			if ( $response_sent ) {
				return;
			}

			while ( ob_get_level() > 0 ) {
				ob_end_clean();
			}

			$active_plugins = get_option( 'active_plugins', [] );
			$is_active      = in_array( $plugin_file, $active_plugins, true );

			if ( ! headers_sent() ) {
				header_remove( 'Location' );
				header( 'Content-Type: application/json; charset=utf-8' );
			}

			echo wp_json_encode( [
				'success' => $is_active,
				'data'    => [
					'message' => $is_active
						? esc_html__( 'Plugin activated successfully.', 'classified-listing' )
						: esc_html__( 'Plugin activation was interrupted.', 'classified-listing' ),
				],
			] );

			// Prevent other shutdown handlers from appending output after our JSON.
			exit;
		} );

		$result = activate_plugin( $plugin_file );

		// Normal path: activate_plugin() returned without exit().
		$response_sent = true;

		if ( is_wp_error( $result ) ) {
			self::send_json( false, [ 'message' => $result->get_error_message() ] );
		}

		self::send_json( true, [ 'message' => esc_html__( 'Plugin activated successfully.', 'classified-listing' ) ] );
	}

	/**
	 * Check actual plugin status via AJAX.
	 *
	 * @return void
	 */
	public static function check_plugin_status() {
		$message = self::check_permission();
		if ( ! empty( $message ) ) {
			self::send_json( false, [ 'message' => $message ] );
		}

		$slug = isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '';
		if ( empty( $slug ) ) {
			self::send_json( false, [ 'message' => esc_html__( 'Plugin slug is required.', 'classified-listing' ) ] );
		}

		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		$installed_plugins = get_plugins();
		$plugin_file       = self::get_plugin_file( $slug, $installed_plugins );
		$active_plugins    = get_option( 'active_plugins', [] );

		self::send_json( true, [
			'installed' => ! empty( $plugin_file ),
			'active'    => ! empty( $plugin_file ) && in_array( $plugin_file, $active_plugins, true ),
		] );
	}

}