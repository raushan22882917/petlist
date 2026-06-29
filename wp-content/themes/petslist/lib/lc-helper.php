<?php
/**
 * Check Radius Theme License
 *
 * @since 1.0.0
 */

namespace RTLC;

if ( defined( 'RT_DEBUG' ) && RT_DEBUG ) {
	return;
}

/**
 * Radius Theme License
 */
class Helper {
	/**
	 * Holds the values to be used in the fields callbacks
	 *
	 * @var array
	 */
	private $options;

	/**
	 * License URL
	 *
	 * @var string
	 */
	private $license_url = 'https://envato.radiustheme.com/license-check';

	/**
	 * Theme Name
	 *
	 * @var string
	 */
	private $petslist = '';

	/**
	 * Theme Slug
	 *
	 * @var string
	 */
	private $theme_slug = '';

	/**
	 * Class Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'theme_menu' ] );
		add_action( 'admin_init', [ $this, 'theme_option' ] );
		add_action( 'wp_ajax_rtlc_verification', [ $this, 'rtlc_verification' ] );

		$theme_info      = wp_get_theme();
		$theme_info      = ( $theme_info->parent() ) ? $theme_info->parent() : $theme_info;
		$petslist = $theme_info->get( 'Name' );

		// Theme name.
		$this->petslist = $petslist;

		// Theme slug.
		$petslist  = strtolower( trim( preg_replace( '/[^A-Za-z0-9-]+/', '-', $petslist ) ) );
		$this->theme_slug = $petslist;
	}

	/**
	 * Add options page
	 */
	public function theme_menu() {
		add_theme_page( esc_html__( 'Theme License', 'petslist' ), esc_html__( 'Theme License', 'petslist' ), 'manage_options', 'rtlc', [ $this, 'create_admin_page' ], null, 99 );
	}

	/**
	 * Options page callback
	 *
	 * @return void
	 */
	public function create_admin_page() {
		settings_errors();

		$this->options = get_option( 'rt_licenses' );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Theme License', 'petslist' ); ?></h1>
			<form method="post" action="options.php">
			<?php
				// This prints out all hidden setting fields.
				settings_fields( 'rt_option_group' );
				do_settings_sections( 'petslist-setting' );
			?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register and add settings
	 *
	 * @return void
	 */
	public function theme_option() {
		register_setting(
			'rt_option_group',
			'rt_license',
			[ $this, 'sanitize_text' ]
		);

		add_settings_section(
			'rt_license_section',
			false,
			false,
			'petslist-setting'
		);

		add_settings_field(
			'rt_purchase_code',
			esc_html__( 'Purchase Code', 'petslist' ),
			[ $this, 'purchase_code_callback' ],
			'petslist-setting',
			'rt_license_section'
		);

		add_settings_field(
			'rt_license_status',
			esc_html__( 'License Status', 'petslist' ),
			[ $this, 'license_status_callback' ],
			'petslist-setting',
			'rt_license_section'
		);

		add_settings_field(
			'rt_license_note',
			esc_html__( 'Note:', 'petslist' ),
			[ $this, 'license_note_callback' ],
			'petslist-setting',
			'rt_license_section'
		);

		add_settings_field(
			'rtlc_license_check',
			false,
			[ $this, 'license_check_callback' ],
			'petslist-setting',
			'rt_license_section'
		);
	}


	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys.
	 *
	 * @return array
	 */
	public function sanitize_text( $input ) {
		$new_input = [];

		if ( isset( $input['rt_purchase_code'] ) ) {
			$new_input['rt_purchase_code'] = sanitize_text_field( $input['rt_purchase_code'] );
		}

		return $new_input;
	}

	/**
	 * Get the settings option array and print one of its values
	 *
	 * @return void
	 */
	public function purchase_code_callback() {
		$value = '';

		// this first line is for checking old codebase.
		if ( isset( $this->options[ $this->theme_slug . '_license_key' ] ) ) {
			$value = esc_attr( $this->options[ $this->theme_slug . '_license_key' ] );
		} elseif ( isset( $this->options[ $this->theme_slug . '_license' ] ) && isset( $this->options[ $this->theme_slug . '_license' ]['key'] ) ) {
			$value = esc_attr( $this->options[ $this->theme_slug . '_license' ]['key'] );
		}

		printf(
			'<input type="text" class="regular-text" id="rt_purchase_code" name="rt_license[%1$s_license_key]" value="%2$s" />',
			esc_attr( $this->theme_slug ),
			esc_attr( $value )
		);
	}

	/**
	 * Check license status
	 *
	 * @return void
	 */
	public function license_status_callback() {
		$verify = false;

		$status_text = esc_html__( 'Not Activated', 'petslist' );

		if ( rtlc_is_valid()['success'] ) {
			$verify      = true;
			$status_text = __( 'Activated', 'petslist' );
		} elseif ( isset( rtlc_is_valid()['domain_match'] ) && ! rtlc_is_valid()['domain_match'] ) {
			$status_text = __( 'Domain Mismatch', 'petslist' );
		}

		$class = ( $verify ) ? 'verified' : 'unverified';
		echo '<span class="rtlc-status-btn rtlc-' . esc_attr( $class ) . '">' . esc_html( $status_text ) . '</span>';
	}

	/**
	 * License note callback
	 *
	 * @return void
	 */
	public function license_note_callback() {
		$support = 'https://www.radiustheme.com/contact/';
		$status  = sprintf(
				/* translators: Support Center */
			__( 'Please keep in mind, you can activate one license in one domain, if you face any problem in activation, please contact our <a href="%s" target="_blank">Support Center</a>', 'petslist' ),
			esc_url( $support )
		);

		echo '<span class="rtcl-note">' . wp_kses(
			$status,
			[
				'a' => [
					'href'   => [],
					'target' => [],
				],
			]
		) . '</span> <br><pre>';
	}

	/**
	 * Active license button
	 *
	 * @return void
	 */
	public function license_check_callback() {
		printf(
			'<input type="button" id="rtlc_license_check" class="button button-primary rtcl-active-btn" value="%s" /> <span class="rtlc-loader"><i class="dashicons dashicons-update spin"></i></span>',
			esc_html__( 'Activate License', 'petslist' )
		);
	}

	/**
	 * Ajax action function to verify license
	 *
	 * @return array|void
	 */
	public function rtlc_verification() {
		$purchase_code = ( ! empty( $_REQUEST['purchase_code'] ) ) ? wp_unslash( sanitize_text_field( $_REQUEST['purchase_code'] ) ) : '';

		if ( $purchase_code ) {
			$rt_license_server = $this->license_url;

			if ( ! $rt_license_server ) {
				return;
			}

			$petslist = $this->petslist;
			$domain_name     = rtlc_get_domain_name();

			$api_url     = "{$rt_license_server}/?theme_name={$petslist}&purchase_code=" . $purchase_code . '&domain_name=' . $domain_name;
			$envato_data = wp_remote_get( $api_url );

			if ( is_wp_error( $envato_data ) ) {
				return [];
			}

			$envato_data = wp_remote_retrieve_body( $envato_data );

			if ( $envato_data ) {
				if ( $envato_data == '"true"' ) {
					$arr_inputs = get_option( 'rt_licenses' );

					$arr_inputs[ $this->theme_slug . '_license' ] = [
						'key'    => sanitize_text_field( $purchase_code ),
						'domain' => esc_html( $domain_name ),
					];

					update_option( 'rt_licenses', $arr_inputs );
				}

				echo json_decode( $envato_data );
			}
		}

		die();
	}
}

if ( is_admin() ) {
	new Helper();
}
