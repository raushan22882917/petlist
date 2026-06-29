<?php
/* phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.WP.EnqueuedResourceParameters.NotInFooter */

namespace Rtcl\Controllers\Admin;

use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;
use Rtcl\Models\Form\Form;
use Rtcl\Resources\Gallery;
use Rtcl\Resources\Options;
use Rtcl\Services\FormBuilder\AvailableFields;
use Rtcl\Services\FormBuilder\EditorShortCode;
use Rtcl\Services\FormBuilder\ElementCustomization;
use Rtcl\Services\FormBuilder\FBHelper;
use Rtcl\Services\FormBuilder\LocalizedString;
use Rtcl\Services\FormBuilder\ValidationRuleSettings;
use WP_User;

/**
 * Class ScriptLoader
 *
 * @package Rtcl\Controllers\Admin
 */
class ScriptLoader {

	private $suffix;
	private $version;
	private $ajaxurl;

	/**
	 * Contains an array of script handles localized by RTCL.
	 *
	 * @var array
	 */
	private static $wp_localize_scripts = [];

	function __construct() {
		$this->suffix  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		$this->version = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? time() : RTCL_VERSION;
		$this->ajaxurl = admin_url( 'admin-ajax.php' );
		if ( $current_lang = apply_filters( 'rtcl_ajaxurl_current_lang', null, $this->ajaxurl ) ) {
			$this->ajaxurl = add_query_arg( 'lang', $current_lang, $this->ajaxurl );
		}

		// TODO: Need to customize at future version
		add_action( 'enqueue_block_editor_assets', [ $this, 'register_script' ], 1 );

		add_action( 'wp_enqueue_scripts', [ $this, 'register_script' ], 1 );
		add_action( 'wp_enqueue_scripts', [ $this, 'frontend_script' ], 999 );
		add_action( 'admin_init', [ $this, 'register_admin_script' ], 1 );
		add_action( 'admin_enqueue_scripts', [ $this, 'load_admin_script_payment' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'load_admin_script_pricing' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'load_admin_script_setting_page' ], 99999 );
		add_action( 'admin_enqueue_scripts', [ $this, 'load_setup_wizard_script' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'load_admin_script_report_page' ], 99 );
		add_action( 'admin_enqueue_scripts', [ $this, 'load_admin_script_export_import_page' ], 99 );
		add_action( 'admin_enqueue_scripts', [ $this, 'load_admin_script_extension_page' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'load_admin_script_post_type_listing' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'load_admin_script_listing_types_page' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'load_admin_script_page_custom_fields' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'load_admin_script_page_user_profile' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'load_admin_script_taxonomy' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'load_script_at_widget_settings' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'load_script_at_filter_builder' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'load_script_at_form_builder' ] );
		add_action( 'login_enqueue_scripts', [ $this, 'resend_verification_link' ] );
	}

	function resend_verification_link() {
		wp_register_script(
			'rtcl-verify-js',
			rtcl()->get_assets_uri( 'js/login.min.js' ),
			[
				'jquery',
				'rtcl-common',
			],
			$this->version,
			true,
		);
		wp_localize_script(
			'rtcl-verify-js',
			'rtcl',
			[
				'ajaxurl'              => $this->ajaxurl,
				're_send_confirm_text' => esc_html__( 'Are you sure you want to re-send verification link?', 'classified-listing' ),
				rtcl()->nonceId        => wp_create_nonce( rtcl()->nonceText ),
			],
		);
		wp_enqueue_script( 'rtcl-verify-js' );
	}

	function register_script_both_end() {
		wp_register_script( 'rt-field-dependency', rtcl()->get_assets_uri( 'js/rt-field-dependency.min.js' ), [ 'jquery' ], $this->version, true );
		wp_register_script( 'rtcl-common', rtcl()->get_assets_uri( 'js/rtcl-common.min.js' ), [ 'jquery' ], $this->version );
		wp_register_script( 'rtcl-country-select', rtcl()->get_assets_uri( 'js/country-select.min.js' ), [ 'jquery' ], $this->version );
		wp_register_script(
			'rtcl-address-i18n',
			rtcl()->get_assets_uri( 'js/address-i18n.min.js' ),
			[
				'jquery',
				'rtcl-country-select',
			],
			$this->version,
		);
		wp_register_script( 'select2', rtcl()->get_assets_uri( 'vendor/select2/select2.full.min.js' ), [ 'jquery' ], '4.1.0-rc.0' );
		wp_register_script(
			'daterangepicker',
			rtcl()->get_assets_uri( 'vendor/daterangepicker/daterangepicker.js' ),
			[ 'jquery', 'moment' ],
			'3.0.5',
		);
		wp_register_script(
			'jquery-validator',
			rtcl()->get_assets_uri( 'vendor/jquery.validate.min.js' ),
			[ 'jquery' ],
			'1.19.1',
		);
		wp_register_script(
			'rtcl-validator',
			rtcl()->get_assets_uri( "js/rtcl-validator{$this->suffix}.js" ),
			[ 'jquery-validator' ],
			$this->version,
		);
		wp_register_script( 'rtcl-sortablejs', rtcl()->get_assets_uri( 'vendor/sortable.min.js' ), '', '1.15.0' );
		wp_register_script(
			'rtcl-gallery',
			rtcl()->get_assets_uri( "js/rtcl-gallery{$this->suffix}.js" ),
			[
				'jquery',
				'plupload-all',
				'jquery-ui-sortable',
				'jquery-effects-core',
				'jquery-effects-fade',
				'wp-util',
				'jcrop',
				'rtcl-sortablejs',
			],
			$this->version,
			true,
		);

		wp_register_script(
			'rtcl-form-builder',
			rtcl()->get_assets_uri( 'form-builder/form-builder.js' ),
			[
				'jquery',
				is_admin() ? 'rtcl-admin' : 'rtcl-public',
				'wp-tinymce',
			],
			$this->version,
			[
				'strategy'  => 'async',
				'in_footer' => true,
			],
		);
		wp_register_style( 'rtcl-form-builder', rtcl()->get_assets_uri( 'form-builder/public.css' ), '', $this->version );
		wp_register_style(
			'fontawesome',
			apply_filters( 'rtcl_fontawesome_css_source', rtcl()->get_assets_uri( 'vendor/fontawesome/css/all.min.css' ) ),
			'',
			'6.7.1',
		);

		if ( Functions::has_map() ) {
			$map_type = Functions::get_map_type();
			if ( 'google' === $map_type && $map_api_key = Functions::get_option_item( 'rtcl_misc_map_settings', 'map_api_key' ) ) {
				$options        = Options::google_map_script_options();
				$options['key'] = $map_api_key;
				wp_register_script( 'rtcl-google-map', add_query_arg( $options, 'https://maps.googleapis.com/maps/api/js' ), '', $this->version );
				wp_register_script(
					'rtcl-map',
					rtcl()->get_assets_uri( 'js/gmap.js' ),
					[
						'jquery',
						'rtcl-google-map',
					],
					$this->version,
					true,
				);
			} elseif ( 'osm' === $map_type ) {
				wp_register_script( 'rtcl-map', rtcl()->get_assets_uri( 'js/osm-map.js' ), [ 'jquery' ], $this->version, true );
			}

			wp_localize_script( 'rtcl-map', 'rtcl_map', Functions::get_map_localized_options() );
		}
		wp_localize_script(
			'rtcl-gallery',
			'rtcl_gallery_lang',
			[
				'ajaxurl'               => $this->ajaxurl,
				'edit_image'            => esc_html__( 'Edit Image', 'classified-listing' ),
				'delete_image'          => esc_html__( 'Delete Image', 'classified-listing' ),
				'view_image'            => esc_html__( 'View Full Image', 'classified-listing' ),
				'featured'              => esc_html__( 'Main', 'classified-listing' ),
				'error_common'          => esc_html__( 'Error while upload image', 'classified-listing' ),
				/* translators: Image size */
				'error_image_size'      => sprintf( __( 'Image size is more then %s.', 'classified-listing' ),
					Functions::formatBytes( Functions::get_max_upload() ) ),
				'error_image_limit'     => esc_html__( 'Image limit is over.', 'classified-listing' ),
				'error_image_extension' => esc_html__( 'File extension not supported.', 'classified-listing' ),
			],
		);

		wp_localize_script(
			'rtcl-validator',
			'rtcl_validator',
			apply_filters(
				'rtcl_validator_localize',
				[
					'messages'      => [
						'session_expired' => esc_html__( 'Session Expired!!', 'classified-listing' ),
						'server_error'    => esc_html__( 'Server Error!!', 'classified-listing' ),
						'required'        => esc_html__( 'This field is required.', 'classified-listing' ),
						'remote'          => esc_html__( 'Please fix this field.', 'classified-listing' ),
						'email'           => esc_html__( 'Please enter a valid email address.', 'classified-listing' ),
						'url'             => esc_html__( 'Please enter a valid URL.', 'classified-listing' ),
						'date'            => esc_html__( 'Please enter a valid date.', 'classified-listing' ),
						'dateISO'         => esc_html__( 'Please enter a valid date (ISO).', 'classified-listing' ),
						'number'          => esc_html__( 'Please enter a valid number.', 'classified-listing' ),
						'digits'          => esc_html__( 'Please enter only digits.', 'classified-listing' ),
						'equalTo'         => esc_html__( 'Please enter the same value again.', 'classified-listing' ),
						'maxlength'       => esc_html__( 'Please enter no more than {0} characters.', 'classified-listing' ),
						'minlength'       => esc_html__( 'Please enter at least {0} characters.', 'classified-listing' ),
						'rangelength'     => esc_html__( 'Please enter a value between {0} and {1} characters long.', 'classified-listing' ),
						'range'           => esc_html__( 'Please enter a value between {0} and {1}.', 'classified-listing' ),
						'pattern'         => esc_html__( 'Invalid format.', 'classified-listing' ),
						'maxWords'        => esc_html__( 'Please enter {0} words or less.', 'classified-listing' ),
						'minWords'        => esc_html__( 'Please enter at least {0} words.', 'classified-listing' ),
						'rangeWords'      => esc_html__( 'Please enter between {0} and {1} words.', 'classified-listing' ),
						'alphanumeric'    => esc_html__( 'Letters, numbers, and underscores only please', 'classified-listing' ),
						'lettersonly'     => esc_html__( 'Only alphabets and spaces are allowed.', 'classified-listing' ),
						'accept'          => esc_html__( 'Please enter a value with a valid mimetype.', 'classified-listing' ),
						'max'             => esc_html__( 'Please enter a value less than or equal to {0}.', 'classified-listing' ),
						'min'             => esc_html__( 'Please enter a value greater than or equal to {0}.', 'classified-listing' ),
						'step'            => esc_html__( 'Please enter a multiple of {0}.', 'classified-listing' ),
						'extension'       => esc_html__( 'Please Select a value file with a valid extension.', 'classified-listing' ),
						/* translators: Password length */
						'password'        => sprintf( esc_html__( 'Your password must be at least %d characters long.', 'classified-listing' ),
							Functions::password_min_length() ),
						'greaterThan'     => esc_html__( 'Max must be greater than min', 'classified-listing' ),
						'maxPrice'        => esc_html__( 'Max price must be greater than regular price', 'classified-listing' ),
						'cc'              => [
							'number'           => esc_html__( 'Please enter a valid credit card number.', 'classified-listing' ),
							'cvc'              => esc_html__( 'Enter a valid cvc number.', 'classified-listing' ),
							'expiry'           => esc_html__( 'Enter a valid expiry date', 'classified-listing' ),
							'incorrect_number' => esc_html__( 'Your card number is incorrect.', 'classified-listing' ),
							'abort'            => esc_html__( 'A network error has occurred, and you have not been charged. Please try again.',
								'classified-listing' ),
						],
					],
					'pwsL10n'       => [
						'unknown'  => esc_html__( 'Password strength unknown', 'classified-listing' ),
						'short'    => esc_html__( 'Very weak', 'classified-listing' ),
						'bad'      => esc_html__( 'Weak', 'classified-listing' ),
						'good'     => esc_html__( 'Medium', 'classified-listing' ),
						'strong'   => esc_html__( 'Strong', 'classified-listing' ),
						'mismatch' => esc_html__( 'Mismatch', 'classified-listing' ),
					],
					'scroll_top'    => 200,
					'pw_min_length' => Functions::password_min_length(),
				],
			),
		);

		wp_localize_script(
			'rtcl-country-select',
			'rtcl_country_select_params',
			[
				'countries'                 => wp_json_encode( rtcl()->countries->get_allowed_country_states() ),
				'i18n_select_state_text'    => esc_attr__( 'Select an option&hellip;', 'classified-listing' ),
				'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'classified-listing' ),
				'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'classified-listing' ),
				'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'classified-listing' ),
				'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', 'classified-listing' ),
				'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'classified-listing' ),
				'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', 'classified-listing' ),
				'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'classified-listing' ),
				'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'classified-listing' ),
				'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'classified-listing' ),
				'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'classified-listing' ),
			],
		);

		$params = [
			'locale'             => wp_json_encode( rtcl()->countries->get_country_locale() ),
			'locale_fields'      => wp_json_encode( rtcl()->countries->get_country_locale_field_selectors() ),
			'i18n_required_text' => esc_attr__( 'required', 'classified-listing' ),
			'i18n_optional_text' => esc_html__( 'optional', 'classified-listing' ),
		];
		wp_localize_script( 'rtcl-address-i18n', 'rtcl_address_i18n_params', $params );
	}

	function localization_both_end() {
		global $pagenow, $post_type, $post;

		if ( Functions::is_listing_form_page()
		     || ( is_admin()
		          && in_array(
			          $pagenow,
			          [
				          'post.php',
				          'post-new.php',
			          ],
		          )
		          && rtcl()->post_type === $post_type )
		) {
			if ( is_admin() ) {
				$raw_listing_id = $post->ID ?? 0;
			} else {
				$raw_listing_id = 'edit' == get_query_var( 'rtcl_action' ) ? absint( get_query_var( 'rtcl_listing_id', 0 ) ) : 0;
			}
			$listing_id = 0;
			$form       = null;
			$forms      = [];
			if ( $raw_listing_id
			     && ( ( is_admin() && current_user_can( 'edit_rtcl_listing', $raw_listing_id ) )
			          || ( ! is_admin()
			               && Functions::current_user_can( 'edit_' . rtcl()->post_type, $raw_listing_id ) ) )
			) {
				$form_id = absint( get_post_meta( $raw_listing_id, '_rtcl_form_id', true ) );
				if ( $form_id && $_form = Form::query()->find( $form_id ) ) {
					$form = apply_filters( 'rtcl_fb_form', $_form );
				} else {
					$form = Form::query()
					            ->where( 'status', 'publish' )
					            ->where( 'default', 1 )
					            ->one();
				}
				$listing_id = $raw_listing_id;
			}

			$rawForms = Form::query()
			                ->where( 'status', 'publish' )
			                ->order_by( 'created_at', 'DESC' )
			                ->get();
			if ( ! empty( $rawForms ) ) {
				foreach ( $rawForms as $raw_form ) {
					$_form = apply_filters( 'rtcl_fb_form', $raw_form );
					if ( is_a( $_form, Form::class ) ) {
						$forms[] = [ 'defaultValues' => FBHelper::getFormDefaultData( $_form ) ] + $_form->toArray();
					}
				}
			}

			$forms             = apply_filters( 'rtcl_fb_forms', $forms );
			$fromBuilderParams = apply_filters(
				'rtcl_localize_fb_params',
				[
					'fields'                       => AvailableFields::get(),
					'isAdminEnd'                   => is_admin(),
					'hasPro'                       => rtcl()->has_pro(),
					'postStatus'                   => is_admin() && $post ? $post->post_status : null,
					'forms'                        => $forms,
					'form'                         => is_a( $form, Form::class ) ? [ 'defaultValues' => FBHelper::getFormDefaultData( $form ) ] + $form->toArray() : null,
					'listingId'                    => $listing_id ? absint( $listing_id ) : '',
					'formData'                     => FBHelper::getFormData( $listing_id, $form ),
					'options'                      => $this->get_fb_settings_options(),
					'ajaxurl'                      => $this->ajaxurl,
					'apiurl'                       => rest_url(),
					'restNonce'                    => wp_create_nonce( 'wp_rest' ),
					'nonceId'                      => rtcl()->nonceId,
					'nonce'                        => wp_create_nonce( rtcl()->nonceText ),
					'i18n'                         => LocalizedString::public(),
					'enabled_ai_image_enhancement' => Functions::is_image_enhancement_enabled(),
				],
			);
			wp_enqueue_editor();
			wp_localize_script( 'rtcl-form-builder', 'rtclFB', $fromBuilderParams );
		}
	}

	function register_script() {
		global $post;

		$this->register_script_both_end();
		$this->localization_both_end();
		$single_listing_settings = Functions::get_option( 'rtcl_single_listing_settings' );
		$misc_settings           = Functions::get_option( 'rtcl_misc_settings' );
		$rtclPublicDepsStyle     = [];

		$rtclPublicDepsScript = [ 'jquery', 'jquery-ui-autocomplete', 'rtcl-common' ];

		wp_register_script(
			'swiper',
			apply_filters( 'rtcl_swiper_js_source', rtcl()->get_assets_uri( 'vendor/swiper/swiper-bundle.min.js' ) ),
			[
				'jquery',
				'imagesloaded',
			],
			'7.4.1',
		);
		wp_register_script( 'rtcl-single-listing',
			rtcl()->get_assets_uri( "js/single-listing{$this->suffix}.js" ),
			apply_filters( 'rtcl_single_listing_script_dependencies', [ 'swiper' ] ),
			$this->version,
			true );
		self::localize_script( 'rtcl-single-listing' );
		if ( is_singular( rtcl()->post_type ) ) {
			wp_enqueue_script( 'rtcl-single-listing' );
		}

		wp_register_script(
			'rtcl-public-add-post',
			rtcl()->get_assets_uri( "js/public-add-post{$this->suffix}.js" ),
			[
				'jquery',
				'daterangepicker',
			],
			$this->version,
			true,
		);

		$recaptcha_version = ! empty( $misc_settings['recaptcha_version'] ) ? $misc_settings['recaptcha_version'] : 2;
		if ( $recaptcha_version == 3 ) {
			wp_register_script( 'rtcl-recaptcha',
				'https://www.google.com/recaptcha/api.js?render=' . esc_attr( $misc_settings['recaptcha_site_key'] ),
				'',
				RTCL_VERSION );
		} else {
			wp_register_script( 'rtcl-recaptcha',
				'https://www.google.com/recaptcha/api.js?onload=rtcl_on_recaptcha_load&render=explicit',
				'',
				$this->version,
				true );
		}
		$rtclPublicDepsScript = apply_filters( 'rtcl_public_script_dependencies', $rtclPublicDepsScript, $this );
		$rtclPublicDepsStyle  = apply_filters( 'rtcl_public_style_dependencies', $rtclPublicDepsStyle, $this );
		wp_register_script( 'rtcl-public', rtcl()->get_assets_uri( "js/rtcl-public{$this->suffix}.js" ), $rtclPublicDepsScript, $this->version, true );
		wp_register_style( 'rtcl-public', rtcl()->get_assets_uri( "css/rtcl-public{$this->suffix}.css" ), $rtclPublicDepsStyle, $this->version );

		do_action( 'rtcl_before_enqueue_script' );

		// Load script
		wp_enqueue_style( 'rtcl-public' );

		$validator_script = false;

		global $wp;

		if ( Functions::is_account_page() ) {
			wp_enqueue_style( 'fontawesome' );
			if ( ! is_user_logged_in() || isset( $wp->query_vars['lost-password'] ) ) {
				$validator_script = true;
			}
			if ( isset( $wp->query_vars['edit-account'] ) || isset( $wp->query_vars['rtcl_edit_account'] ) ) {
				$validator_script = true;
				wp_enqueue_script( 'rtcl-map' );
				wp_enqueue_script( 'rtcl-public-add-post' );
			}
			if ( ! is_user_logged_in()
			     && ( Functions::get_option_item( 'rtcl_misc_settings', 'recaptcha_forms', 'registration', 'multi_checkbox' )
			          || Functions::get_option_item( 'rtcl_misc_settings', 'recaptcha_forms', 'login', 'multi_checkbox' ) )
			) {
				wp_enqueue_script( 'rtcl-recaptcha' );
			}
		}

		if ( Functions::is_listing_form_page() ) {
			$validator_script = true;
			wp_enqueue_style( 'fontawesome' );
			wp_enqueue_script( 'rtcl-gallery' );
			wp_enqueue_script( 'select2' );
			wp_enqueue_script( 'rt-field-dependency' );
			wp_enqueue_editor();
			if ( FBHelper::isEnabled() ) {
				wp_enqueue_script( 'rtcl-form-builder' );
				wp_enqueue_style( 'rtcl-form-builder' );
			} else {
				wp_enqueue_script( 'rtcl-public-add-post' );
			}
			if ( Functions::get_option_item( 'rtcl_misc_settings', 'recaptcha_forms', 'listing', 'multi_checkbox' )
			     || ( ! is_user_logged_in()
			          && Functions::get_option_item( 'rtcl_misc_settings', 'recaptcha_forms', 'login', 'multi_checkbox' ) )
			) {
				wp_enqueue_script( 'rtcl-recaptcha' );
			}
			wp_enqueue_script( 'rtcl-map' );
		}

		if ( is_singular( rtcl()->post_type ) ) {
			$validator_script = true;
			wp_enqueue_style( 'fontawesome' );
			if ( Functions::get_option_item( 'rtcl_misc_settings', 'recaptcha_forms', 'contact', 'multi_checkbox' )
			     || Functions::get_option_item( 'rtcl_misc_settings', 'recaptcha_forms', 'report_abuse', 'multi_checkbox' )
			) {
				wp_enqueue_script( 'rtcl-recaptcha' );
			}
			wp_enqueue_script( 'rtcl-map' );
		}

		if ( Functions::is_checkout_page() ) {
			$validator_script = true;

			wp_enqueue_style( 'fontawesome' );

			if ( ! is_user_logged_in() && Functions::get_option_item( 'rtcl_misc_settings', 'recaptcha_forms', 'login', 'multi_checkbox' ) ) {
				wp_enqueue_script( 'rtcl-recaptcha' );
			}
			wp_enqueue_script( 'select2' );
			wp_enqueue_script( 'rtcl-country-select' );
			wp_enqueue_script( 'rtcl-address-i18n' );
		}

		if ( $validator_script ) {
			wp_enqueue_script( 'rtcl-validator' );
		}
		wp_enqueue_script( 'daterangepicker' );
		wp_enqueue_script( 'rtcl-public' );

		$rtcl_style_opt = Functions::get_option( 'rtcl_style_settings' );
		$rootVar        = null;
		$style          = null;
		if ( is_array( $rtcl_style_opt ) && ! empty( $rtcl_style_opt ) ) {
			$primary = ! empty( $rtcl_style_opt['primary'] ) ? $rtcl_style_opt['primary'] : null;
			$rootVar = '';
			if ( $primary ) {
				$rootVar .= '--rtcl-primary-color:' . $primary . ';';
				$style   .= ".rtcl .rtcl-icon, 
							.rtcl-chat-form button.rtcl-chat-send, 
							.rtcl-chat-container a.rtcl-chat-card-link .rtcl-cc-content .rtcl-cc-listing-amount,
							.rtcl-chat-container ul.rtcl-messages-list .rtcl-message span.read-receipt-status .rtcl-icon.rtcl-read{color: $primary;}";
				$style   .= '#rtcl-chat-modal {background-color: var(--rtcl-primary-color); border-color: var(--rtcl-primary-color)}';
				$style   .= '#rtcl-compare-btn-wrap a.rtcl-compare-btn, .rtcl-btn, #rtcl-compare-panel-btn, .rtcl-chat-container ul.rtcl-messages-list .rtcl-message-wrap.own-message .rtcl-message-text, .rtcl-sold-out {background : var(--rtcl-primary-color);}';
			}
			$link = ! empty( $rtcl_style_opt['link'] ) ? $rtcl_style_opt['link'] : null;
			if ( $link ) {
				$rootVar .= '--rtcl-link-color:' . $link . ';';
				$style   .= '.rtcl a{ color: var(--rtcl-link-color)}';
			}
			$linkHover = ! empty( $rtcl_style_opt['link_hover'] ) ? $rtcl_style_opt['link_hover'] : null;
			if ( $linkHover ) {
				$rootVar .= '--rtcl-link-hover-color:' . $linkHover . ';';
				$style   .= '.rtcl a:hover{ color: var(--rtcl-link-hover-color)}';
			}
			$sidebarWidth = ! empty( $rtcl_style_opt['sidebar_width'] ) ? $rtcl_style_opt['sidebar_width'] : null;
			if ( is_array( $sidebarWidth ) ) {
				$size    = $sidebarWidth['size'] ?? 28;
				$unit    = $sidebarWidth['unit'] ?? '%';
				$rootVar .= '--rtcl-sidebar-width:' . absint( $size ) . esc_attr( $unit ) . ';';
			}
			// Button
			$button = ! empty( $rtcl_style_opt['button'] ) ? $rtcl_style_opt['button'] : null;
			if ( $button ) {
				$rootVar .= '--rtcl-button-bg-color:' . $button . ';';
				$style   .= '.rtcl .rtcl-btn{ background-color: var(--rtcl-button-bg-color); border-color:var(--rtcl-button-bg-color); }';
			}
			$buttonText = ! empty( $rtcl_style_opt['button_text'] ) ? $rtcl_style_opt['button_text'] : null;
			if ( $buttonText ) {
				$rootVar .= '--rtcl-button-color:' . $buttonText . ';';
				$style   .= '.rtcl .rtcl-btn{ color: var(--rtcl-button-color); }';
				$style   .= '[class*=rtcl-slider] [class*=swiper-button-],.rtcl-carousel-slider [class*=swiper-button-] { color: var(--rtcl-button-color); }';
			}

			// Button hover
			$buttonHover = ! empty( $rtcl_style_opt['button_hover'] ) ? $rtcl_style_opt['button_hover'] : null;
			if ( $buttonHover ) {
				$rootVar .= '--rtcl-button-hover-bg-color:' . $buttonHover . ';';
				$style   .= '.rtcl-pagination ul.page-numbers li span.page-numbers.current,.rtcl-pagination ul.page-numbers li a.page-numbers:hover{ background-color: var(--rtcl-button-hover-bg-color); }';
				$style   .= '.rtcl .rtcl-btn:hover{ background-color: var(--rtcl-button-hover-bg-color); border-color: var(--rtcl-button-hover-bg-color); }';
			}
			$buttonHoverText = ! empty( $rtcl_style_opt['button_hover_text'] ) ? $rtcl_style_opt['button_hover_text'] : null;
			if ( $buttonHoverText ) {
				$rootVar .= '--rtcl-button-hover-color:' . $buttonHoverText . ';';
				$style   .= '.rtcl-pagination ul.page-numbers li a.page-numbers:hover, .rtcl-pagination ul.page-numbers li span.page-numbers.current{ color: var(--rtcl-button-hover-color); }';
				$style   .= '.rtcl .rtcl-btn:hover{ color: var(--rtcl-button-hover-color)}';
				$style   .= '[class*=rtcl-slider] [class*=swiper-button-],.rtcl-carousel-slider [class*=swiper-button-]:hover { color: var(--rtcl-button-hover-color); }';
			}

			// New
			$new = ! empty( $rtcl_style_opt['new'] ) ? $rtcl_style_opt['new'] : null;
			if ( $new ) {
				$rootVar .= '--rtcl-badge-new-bg-color:' . $new . ';';
			}
			$newText = ! empty( $rtcl_style_opt['new_text'] ) ? $rtcl_style_opt['new_text'] : null;
			if ( $newText ) {
				$rootVar .= '--rtcl-badge-new-color:' . $newText . ';';
			}

			// Feature
			$feature = ! empty( $rtcl_style_opt['feature'] ) ? $rtcl_style_opt['feature'] : null;
			if ( $feature ) {
				$rootVar .= '--rtcl-badge-featured-bg-color:' . $feature . ';';
			}
			$featureText = ! empty( $rtcl_style_opt['feature_text'] ) ? $rtcl_style_opt['feature_text'] : null;
			if ( $featureText ) {
				$rootVar .= '--rtcl-badge-featured-color:' . $featureText . ';';
			}
		}

		if ( $rootVar = apply_filters( 'rtcl_public_root_var', $rootVar, $rtcl_style_opt ) ) {
			$rootVar = ':root{' . $rootVar . '}';
			wp_add_inline_style( 'rtcl-public', $rootVar );
		}
		$style = apply_filters( 'rtcl_public_inline_style', $style, $rtcl_style_opt );
		if ( $style ) {
			wp_add_inline_style( 'rtcl-public', $style );
		}

		$category_base = trim( Functions::get_option_item( 'rtcl_advanced_settings', 'category_base', '' ), '/' );
		$location_base = trim( Functions::get_option_item( 'rtcl_advanced_settings', 'location_base', '' ), '/' );

		// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
		$category_base = esc_html_x( $category_base, 'slug', 'classified-listing' );
		// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
		$location_base = esc_html_x( $location_base, 'slug', 'classified-listing' );

		$decimal_separator = Functions::get_decimal_separator();

		$activeTerms = [];
		if ( Functions::is_listings() || Functions::is_listing_tax() ) {
			$catSlug = get_query_var( '__cat' );
			$locSlug = get_query_var( '__loc' );
			$tagSlug = get_query_var( '__tag' );
			if ( $catSlug ) {
				$cat_term = get_term_by( 'slug', $catSlug, rtcl()->category );
				if ( $cat_term && ! is_wp_error( $cat_term ) ) {
					$activeTerms[] = $cat_term;
					//$activeTerms = $cat_term;
				}
			}
			if ( $locSlug ) {
				$loc_term = get_term_by( 'slug', $locSlug, rtcl()->location );
				if ( $loc_term && ! is_wp_error( $loc_term ) ) {
					$activeTerms[] = $loc_term;
				}
			}
			if ( $tagSlug ) {
				$tagTerm = get_term_by( 'slug', $tagSlug, rtcl()->location );
				if ( $tagTerm && ! is_wp_error( $tagTerm ) ) {
					$activeTerms[] = $tagTerm;
				}
			}
			wp_enqueue_style( 'fontawesome' );
			if ( Functions::is_listing_tax() ) {
				$activeTerms[] = get_queried_object();
				if ( ! empty( $_GET['rtcl_location'] ) ) {
					$loc_term = get_term_by( 'slug', wp_unslash( sanitize_text_field( $_GET['rtcl_location'] ) ), rtcl()->location );
					if ( $loc_term && ! is_wp_error( $loc_term ) ) {
						$activeTerms[] = $loc_term;
					}
				}
				if ( ! empty( $_GET['rtcl_category'] ) ) {
					$cat_term = get_term_by( 'slug', wp_unslash( sanitize_text_field( $_GET['rtcl_category'] ) ), rtcl()->category );
					if ( $cat_term && ! is_wp_error( $cat_term ) ) {
						$activeTerms[] = $cat_term;
					}
				}
				if ( ! empty( $_GET['rtcl_tag'] ) ) {
					$tag_term = get_term_by( 'slug', wp_unslash( sanitize_text_field( $_GET['rtcl_tag'] ) ), rtcl()->tag );
					if ( $tag_term && ! is_wp_error( $tag_term ) ) {
						$activeTerms[] = $tag_term;
					}
				}
			}
		}

		$current_user = wp_get_current_user();

		$localize = [
			'plugin_url'                               => RTCL_URL,
			'decimal_point'                            => $decimal_separator,
			'i18n_required_rating_text'                => esc_attr__( 'Please select a rating', 'classified-listing' ),
			/* translators: %s: decimal */
			'i18n_decimal_error'                       => sprintf( __( 'Please enter in decimal (%s) format without thousand separators.',
				'classified-listing' ),
				$decimal_separator ),
			/* translators: %s: price decimal separator */
			'i18n_mon_decimal_error'                   => sprintf( __( 'Please enter in monetary decimal (%s) format without thousand separators and currency symbols.',
				'classified-listing' ),
				$decimal_separator ),
			'is_rtl'                                   => is_rtl(),
			'is_admin'                                 => is_admin(),
			'ajaxurl'                                  => $this->ajaxurl,
			'confirm_text'                             => esc_html__( 'Are you sure?', 'classified-listing' ),
			're_send_confirm_text'                     => esc_html__( 'Are you sure you want to re-send verification link?', 'classified-listing' ),
			rtcl()->nonceId                            => wp_create_nonce( rtcl()->nonceText ),
			'rtcl_listing_base'                        => Link::get_listings_page_link(),
			'rtcl_category'                            => get_query_var( 'rtcl_category' ),
			'rtcl_category_base'                       => $category_base,
			'category_text'                            => esc_html__( 'Category', 'classified-listing' ),
			'location_text'                            => esc_html__( 'Location', 'classified-listing' ),
			'rtcl_location'                            => get_query_var( 'rtcl_location' ),
			'rtcl_location_base'                       => $location_base,
			'user_login_alert_message'                 => esc_html__( 'Sorry, you need to login first.', 'classified-listing' ),
			'is_user_logged_in'                        => is_user_logged_in(),
			/* translators: Image pending count */
			'upload_limit_alert_message'               => esc_html__( 'Sorry, you have only %d images pending.', 'classified-listing' ),
			'delete_label'                             => esc_html__( 'Delete Permanently', 'classified-listing' ),
			'proceed_to_payment_btn_label'             => esc_html__( 'Proceed to payment', 'classified-listing' ),
			'finish_submission_btn_label'              => esc_html__( 'Finish submission', 'classified-listing' ),
			'phone_number_placeholder'                 => apply_filters( 'rtcl_phone_number_placeholder', 'XXX' ),
			'popup_search_widget_auto_form_submission' => apply_filters( 'rtcl_popup_search_widget_auto_form_submission', true ),
			'loading'                                  => esc_html__( 'Loading ...', 'classified-listing' ),
			'is_listing'                               => Functions::is_listing() ? get_the_ID() : 0,
			'is_listings'                              => Functions::is_listings(),
			'listing_term'                             => Functions::is_listing_tax() ? get_queried_object() : '',
			'activeTerms'                              => $activeTerms,
			'is_enable_tax'                            => Functions::is_enable_tax(),
			'payment_currency_symbol'                  => Functions::get_order_currency_symbol(),
			'ai_enabled'                               => Functions::is_ai_enabled(),
			'current_user'                             => [
				'roles' => $current_user instanceof WP_User ? $current_user->roles : [],
			],
			'admin_url'                                => admin_url(),
			'prompt_max_limit'                         => Functions::get_max_prompt_input_limit(),
			'i18n'                                     => [
				/* translators: All of item*/
				'all_of_'                 => esc_html__( 'All of %s', 'classified-listing' ),
				'go_back'                 => esc_html__( 'Go back', 'classified-listing' ),
				'ai_quick_search_loading' => esc_html__( 'Analyzing through AI', 'classified-listing' ),
				'ai_quick_search_heading' => esc_html__( 'Search Results for: ', 'classified-listing' ),
			],
		];

		if ( ! empty( $misc_settings['recaptcha_site_key'] ) && ! empty( $misc_settings['recaptcha_forms'] ) ) {
			$v                     = isset( $misc_settings['recaptcha_version'] ) ? absint( $misc_settings['recaptcha_version'] ) : 2;
			$recaptcha             = [
				'site_key'   => $misc_settings['recaptcha_site_key'],
				'v'          => $v ?: 2,
				'on'         => $misc_settings['recaptcha_forms'],
				'conditions' => [
					'has_contact_form' => ! empty( $single_listing_settings['has_contact_form'] ) && $single_listing_settings['has_contact_form'] == 'yes' ? 1
						: 0,
					'has_report_abuse' => ! empty( $single_listing_settings['has_report_abuse'] ) && $single_listing_settings['has_report_abuse'] == 'yes' ? 1
						: 0,
					'listing'          => in_array( 'listing', $misc_settings['recaptcha_forms'] ) ? 1 : 0,
				],
				'msg'        => [
					'invalid' => esc_html__( "You can't leave Captcha Code empty", 'classified-listing' ),
				],
			];
			$localize['recaptcha'] = $recaptcha;
		}
		if ( is_singular( rtcl()->post_type ) ) {
			$localize['post_id']    = $post->ID;
			$localize['post_title'] = $post->post_title;
			/* translators: 1: related to , 2: Related form */
			$message                = sprintf( esc_html__( "Need to discuss something related to '%1\$s' from %2\$s", 'classified-listing' ),
				$post->post_title,
				get_permalink( $post->ID ) );
			$localize['wa_message'] = apply_filters( 'rtcl_default_wa_message', $message );
		}
		if ( is_author() ) {
			$author              = get_user_by( 'slug', get_query_var( 'author_name' ) );
			$localize['user_id'] = $author->ID ?? null;
			wp_enqueue_style( 'fontawesome' );
		}
		if ( isset( $wp->query_vars['edit-account'] ) || isset( $wp->query_vars['rtcl_edit_account'] ) ) {
			$max_image_size = Functions::get_max_upload();
			$extensions     = (array) Functions::get_option_item( 'rtcl_misc_media_settings', 'image_allowed_type' );
			if ( empty( $extensions ) ) {
				$extensions = [ 'png', 'jpg', 'jpeg' ];
			}
			$localize['image_allowed_type']  = $extensions;
			$localize['max_image_size']      = $max_image_size;
			$localize['error_upload_common'] = esc_html__( 'Error while upload image', 'classified-listing' );
			/* translators: Image size */
			$localize['error_image_size']      = sprintf( __( 'Image size is more then %s.', 'classified-listing' ),
				Functions::formatBytes( $max_image_size ) );
			$localize['error_image_extension'] = esc_html__( 'File extension not supported.', 'classified-listing' );
		}
		wp_localize_script( 'rtcl-public', 'rtcl', apply_filters( 'rtcl_localize_params_public', $localize ) );
		$ajaxFilerLocalize = [
			'clear_all_filter'     => __( 'Clear all filters', 'classified-listing' ),
			'no_result_found'      => __( 'No result found.', 'classified-listing' ),
			'show_all'             => __( 'Show All', 'classified-listing' ),
			'listings_archive_url' => Link::get_listings_page_link(),
			'result_count'         => [
				'all'  => __( 'Showing all % results', 'classified-listing' ),
				'part' => __( 'Showing _ of % results', 'classified-listing' ),
			],
			'filter_scroll_offset' => 50,
		];
		wp_localize_script( 'rtcl-public', 'rtclAjaxFilterObj', apply_filters( 'rtcl_ajax_filter_localize', $ajaxFilerLocalize ) );
		wp_localize_script(
			'rtcl-public-add-post',
			'rtcl_add_post',
			apply_filters(
				'rtcl_localize_params_add_post',
				[
					'hide_ad_type'    => Functions::is_ad_type_disabled(),
					'form_uri'        => is_object( $post ) ? get_permalink( $post->ID ) : null,
					'character_limit' => [
						'title'       => Functions::get_title_character_limit(),
						'description' => Functions::get_description_character_limit(),
					],
					'message'         => [
						'ad_type'    => esc_html__( 'Please select ad type first', 'classified-listing' ),
						'parent_cat' => esc_html__( 'Please select parent category first', 'classified-listing' ),
					],
				],
			),
		);
	}

	function frontend_script() {
		if ( defined( 'ICL_SITEPRESS_VERSION' ) && Functions::is_listing_form_page() ) {
			wp_dequeue_style( 'wpml-blocks' );
		}
	}

	function register_admin_script() {
		$this->register_script_both_end();

		wp_register_script( 'rtcl-fb-admin', rtcl()->get_assets_uri( 'form-builder/admin.js' ), [ 'wp-i18n' ], $this->version, true );
		wp_register_style( 'rtcl-fb-admin', rtcl()->get_assets_uri( 'form-builder/admin.css' ), [ 'rtcl-admin' ], $this->version );
		wp_register_script( 'rtcl-admin-widget', rtcl()->get_assets_uri( "js/admin-widget.min.js" ), [ 'jquery' ], $this->version );
		wp_register_script( 'rtcl-timepicker', rtcl()->get_assets_uri( "vendor/jquery-ui-timepicker-addon.js" ), [ 'jquery' ], $this->version, true );
		wp_register_script( 'rtcl-chart', rtcl()->get_assets_uri( "vendor/chart/chart.min.js" ), [], $this->version, true );
		wp_register_script( 'rtcl-chart-config',
			rtcl()->get_assets_uri( "js/rtcl-admin-chart-config.min.js" ),
			[
				'jquery',
				'rtcl-chart',
			],
			$this->version,
			true );
		wp_register_script( 'rtcl-admin', rtcl()->get_assets_uri( "js/rtcl-admin.min.js" ), [ 'jquery', 'rtcl-common' ], $this->version, true );
		wp_register_style( 'rtcl-admin-settings', rtcl()->get_assets_uri( 'css/rtcl-admin-settings.min.css' ), $this->version );
		wp_register_script( 'rtcl-admin-settings', rtcl()->get_assets_uri( "js/rtcl-admin-settings.min.js" ), [ 'jquery' ], $this->version, true );
		wp_register_script( 'rtcl-admin-ie', rtcl()->get_assets_uri( "js/rtcl-admin-ie.min.js" ), [
			'jquery',
			'rtcl-validator',
		], $this->version, true );
		wp_register_script( 'rtcl-admin-listing-type', rtcl()->get_assets_uri( "js/rtcl-admin-listing-type.min.js" ), [
			'jquery',
			'jquery-ui-sortable',
		], $this->version, true );
		wp_register_script( 'rtcl-admin-taxonomy',
			rtcl()->get_assets_uri( "js/rtcl-admin-taxonomy.min.js" ),
			[ 'jquery' ],
			$this->version,
			true );
		wp_register_script( 'rtcl-admin-custom-fields', rtcl()->get_assets_uri( "js/rtcl-admin-custom-fields.min.js" ), [
			'jquery',
			'rtcl-common',
			'jquery-ui-dialog',
			'jquery-ui-sortable',
			'jquery-ui-draggable',
			'jquery-ui-tabs',
		], $this->version, true );
		wp_register_style( 'rtcl-admin', rtcl()->get_assets_uri( "css/rtcl-admin{$this->suffix}.css" ), '', $this->version );
		wp_register_style( 'jquery-ui', rtcl()->get_assets_uri( 'vendor/jqueryui/1.12.1/themes/smoothness/jquery-ui.css' ), '', '1.12.1' );
		wp_register_script( 'rtcl-admin-widget', rtcl()->get_assets_uri( 'js/admin-widget.min.js' ), [ 'jquery' ], $this->version );
		wp_register_script( 'rtcl-timepicker', rtcl()->get_assets_uri( 'vendor/jquery-ui-timepicker-addon.js' ), [ 'jquery' ], $this->version, true );
		wp_register_script(
			'rtcl-admin',
			rtcl()->get_assets_uri( 'js/rtcl-admin.min.js' ),
			[
				'jquery',
				'rtcl-common',
			],
			$this->version,
			true,
		);
		wp_register_script(
			'rtcl-admin-ie',
			rtcl()->get_assets_uri( 'js/rtcl-admin-ie.min.js' ),
			[
				'jquery',
				'rtcl-validator',
			],
			$this->version,
			true,
		);
		wp_register_script(
			'rtcl-admin-listing-type',
			rtcl()->get_assets_uri( 'js/rtcl-admin-listing-type.min.js' ),
			[
				'jquery',
				'jquery-ui-sortable',
			],
			$this->version,
			true,
		);
		wp_register_script(
			'rtcl-admin-taxonomy',
			rtcl()->get_assets_uri( 'js/rtcl-admin-taxonomy.min.js' ),
			[ 'jquery' ],
			$this->version,
			true,
		);
		wp_register_script(
			'rtcl-admin-custom-fields',
			rtcl()->get_assets_uri( 'js/rtcl-admin-custom-fields.min.js' ),
			[
				'jquery',
				'rtcl-common',
				'jquery-ui-dialog',
				'jquery-ui-sortable',
				'jquery-ui-draggable',
				'jquery-ui-tabs',
			],
			$this->version,
			true,
		);
		wp_register_script( 'rtcl-ajax-filter-admin', rtcl()->get_assets_uri( 'js/admin-filter-setting.min.js' ), [
			'jquery',
			'jquery-ui-sortable',
			'rtcl-common',
			'wp-i18n',
		], $this->version, true );

		$decimal_separator         = Functions::get_decimal_separator();
		$pricing_decimal_separator = Functions::get_decimal_separator( true );
		$current_user              = wp_get_current_user();

		$localize = [
			'ajaxurl'                        => $this->ajaxurl,
			'decimal_point'                  => $decimal_separator,
			'pricing_decimal_point'          => $pricing_decimal_separator,
			/* translators: decimal_separator */
			'i18n_decimal_error'             => sprintf( __( 'Please enter in decimal (%s) format without thousand separators.', 'classified-listing' ),
				$decimal_separator ),
			/* translators: pricing_decimal_separator */
			'i18n_pricing_decimal_error'     => sprintf( __( 'Please enter in decimal (%s) format without thousand separators.', 'classified-listing' ),
				$pricing_decimal_separator ),
			/* translators: decimal_separator */
			'i18n_mon_decimal_error'         => sprintf( __( 'Please enter in monetary decimal (%s) format without thousand separators and currency symbols.',
				'classified-listing' ),
				$decimal_separator ),
			/* translators: pricing_decimal_separator */
			'i18n_mon_pricing_decimal_error' => sprintf( __( 'Please enter in monetary decimal (%s) format without thousand separators and currency symbols.',
				'classified-listing' ),
				$pricing_decimal_separator ),
			'is_admin'                       => is_admin(),
			rtcl()->nonceId                  => wp_create_nonce( rtcl()->nonceText ),
			'expiredOn'                      => esc_html__( 'Expired on:', 'classified-listing' ),
			/* translators: Date formate */
			'dateFormat'                     => esc_html__( '%1$s %2$s, %3$s @ %4$s:%5$s', 'classified-listing' ),
			'i18n_delete_note'               => esc_html__( 'Are you sure you wish to delete this note? This action cannot be undone.', 'classified-listing' ),
			'i18n_message'                   => esc_html__( 'Message', 'classified-listing' ),
			'i18n_send'                      => esc_html__( 'Send', 'classified-listing' ),
			'ai_enabled'                     => Functions::is_ai_enabled(),
			'current_user'                   => [
				'roles' => $current_user instanceof WP_User ? $current_user->roles : [],
			],
			'admin_url'                      => admin_url(),
			'prompt_max_limit'               => Functions::get_max_prompt_input_limit(),
		];
		wp_localize_script( 'rtcl-admin', 'rtcl', apply_filters( 'rtcl_localize_params_admin', $localize ) );

		$chart_localize = [
			'last_week_order_price' => Functions::get_last_week_order_price(),
		];
		wp_localize_script( 'rtcl-chart-config', 'rtcl_chart_vars', apply_filters( 'rtcl_chart_localize_params_admin', $chart_localize ) );
	}

	public function load_admin_script_page_user_profile() {
		$screen = get_current_screen();

		if ( $screen && in_array( $screen->id, [ 'profile', 'user-edit' ] ) ) {
			wp_enqueue_style( 'rtcl-admin' );
		}
	}

	function load_admin_script_page_custom_fields() {
		global $pagenow, $post_type;
		if ( ! in_array( $pagenow, [ 'post.php', 'post-new.php', 'edit.php' ] ) ) {
			return;
		}
		if ( rtcl()->post_type_cfg != $post_type ) {
			return;
		}
		wp_enqueue_style( 'rtcl-admin' );
		wp_enqueue_style( 'wp-jquery-ui-dialog' );
		wp_enqueue_script( 'select2' );
		wp_enqueue_script( 'rtcl-admin-custom-fields' );
		wp_localize_script(
			'rtcl-admin-custom-fields',
			'rtcl_cfg',
			[
				'ajaxurl'       => $this->ajaxurl,
				rtcl()->nonceId => wp_create_nonce( rtcl()->nonceText ),
			],
		);
	}

	function load_admin_script_setting_page() {
		if ( ! empty( $_GET['page'] ) && $_GET['page'] == 'rtcl-settings' ) {
			wp_enqueue_media();
			//wp_enqueue_style( 'rtcl-admin' );
			//wp_enqueue_script( 'rt-field-dependency' );
			//wp_enqueue_script( 'select2' );
			//if ( Functions::has_map() && isset( $_GET['tab'] ) && 'misc' === $_GET['tab'] ) {
			//	wp_enqueue_script( 'rtcl-map' );
			//}
			$notices = Functions::get_notices();
			Functions::clear_notices();
			$rtclObj = [
				'plugin_url'  => RTCL_URL,
				'ajaxurl'     => admin_url( 'admin-ajax.php' ),
				'optionsData' => Functions::getOptionsData(),
				'items'       => Options::option_items(),
				'countryList' => rtcl()->countries->get_countries(),
				'stateList'   => rtcl()->countries->get_states(),
				'notices'     => $notices,
				'rtcl_nonce'  => wp_create_nonce( rtcl()->nonceText ),
			];
			// Add the color picker css file
//			wp_enqueue_style( 'wp-color-picker' );
			wp_dequeue_style( 'astra-theme-builder-style' );
			wp_enqueue_style( 'rtcl-admin-settings' );
			wp_enqueue_script( 'rtcl-admin-settings' );
			wp_localize_script( 'rtcl-admin-settings', 'rtclObj', $rtclObj );
		}
	}

	public function load_setup_wizard_script() {
		wp_register_script( 'rtcl-admin-setup-wizard', rtcl()->get_assets_uri( "js/setup-wizard.js" ), [ 'rtcl-admin' ], $this->version, true );
		if ( ! empty( $_GET['page'] ) && $_GET['page'] == 'rtcl-setup-wizard' ) {
			wp_enqueue_style( 'rtcl-admin' );
			wp_enqueue_style( 'rtcl-fb-admin' );
			wp_enqueue_script( 'rtcl-admin' );
			wp_enqueue_script( 'rtcl-admin-setup-wizard' );
			wp_localize_script( 'rtcl-admin-setup-wizard', 'rtclSetupWizardData', [
				'plugin_dashboard_url' => admin_url( 'admin.php?page=rtcl-admin' ),
				'all_listings_url'     => Link::get_listings_page_link(),
				'logo_url'             => rtcl()->get_assets_uri( 'images/cl-logo-dark.png' ),
				'logo_icon_url'        => rtcl()->get_assets_uri( 'images/icon-64x64.png' ),
				'recommendedPlugins'   => SetupWizard::get_recommended_plugins(),
			] );
		}
	}

	public function load_admin_script_report_page() {
		if ( ! empty( $_GET['page'] ) && $_GET['page'] == 'rtcl-admin' ) {
			wp_enqueue_style( 'rtcl-admin' );
			wp_enqueue_script( 'rtcl-admin' );
			wp_enqueue_script( 'rtcl-chart' );
			wp_enqueue_script( 'daterangepicker' );
			wp_enqueue_script( 'rtcl-chart-config' );
		}
	}

	public function load_admin_script_export_import_page() {
		if ( ! empty( $_GET['page'] ) && $_GET['page'] == 'rtcl-import-export' ) {
			wp_enqueue_style( 'rtcl-admin' );
			wp_enqueue_script( 'rtcl-admin' );
			wp_enqueue_script( 'rtcl-admin-ie' );

			// Google Places import tab: load the Maps JS API so the location bias
			// can be picked on an interactive map instead of typing raw lat/lng.
			// Prefer the "Google Map API Key" (rtcl_misc_map_settings) — it is
			// already authorized for the Maps JavaScript API everywhere else in
			// the plugin. The Places import key only needs the Places API, so it
			// usually fails to load Maps JS ("can't load Google Maps correctly").
			// Fall back to it only when no map key is configured. Init is deferred
			// to rtclGoogleImportMapInit, defined by the import-google view.
			$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
			if ( 'google' === $tab ) {
				$map_key = (string) Functions::get_option_item( 'rtcl_misc_map_settings', 'map_api_key', '' );
				if ( '' === $map_key ) {
					$map_key = (string) Functions::get_option_item( 'rtcl_import_settings', 'google_places_api_key', '' );
				}
				if ( '' !== $map_key ) {
					$options             = Options::google_map_script_options();
					$options['key']      = $map_key;
					$options['callback'] = 'rtclGoogleImportMapInit';
					$options['loading']  = 'async';
					wp_enqueue_script(
						'rtcl-google-import-map',
						add_query_arg( $options, 'https://maps.googleapis.com/maps/api/js' ),
						[],
						$this->version,
						true
					);
				}
			}
		}
	}

	public function load_admin_script_listing_types_page() {
		if ( ! empty( $_GET['post_type'] ) && $_GET['post_type'] == rtcl()->post_type && ! empty( $_GET['page'] ) && $_GET['page'] == 'rtcl-listing-type' ) {
			wp_enqueue_style( 'rtcl-admin' );
			if ( Functions::has_map() ) {
				wp_enqueue_script( 'rtcl-map' );
			}
			wp_enqueue_script( 'rtcl-admin-listing-type' );
			wp_localize_script(
				'rtcl-admin-listing-type',
				'rtcl',
				[
					'ajaxurl' => $this->ajaxurl,
					'nonceId' => rtcl()->nonceId,
					'nonce'   => wp_create_nonce( rtcl()->nonceText ),
				],
			);
		}
	}

	function load_admin_script_extension_page() {
		if ( ! empty( $_GET['page'] ) && $_GET['page'] == 'rtcl-extension' ) {
			wp_enqueue_style( 'rtcl-admin' );
		}
	}

	function load_admin_script_post_type_listing() {
		global $pagenow, $post_type;

		// validate page
		if ( ! in_array( $pagenow, [ 'post.php', 'post-new.php', 'edit.php' ] ) ) {
			return;
		}

		if ( rtcl()->post_type != $post_type ) {
			return;
		}

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'daterangepicker' );
		wp_enqueue_script( 'rtcl-timepicker' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'rtcl-validator' );
		wp_enqueue_script( 'rt-field-dependency' );
		wp_enqueue_script( 'select2' );
		wp_enqueue_script( 'rtcl-admin' );
		wp_enqueue_script( 'rtcl-gallery' );
		wp_enqueue_script( 'plupload-all' );
		wp_enqueue_script( 'suggest' );

		wp_enqueue_style( 'jquery-ui' );
		wp_enqueue_style( 'wp-jquery-ui-dialog' );
		wp_enqueue_style( 'rtcl-admin' );

		if ( Functions::has_map() ) {
			wp_enqueue_script( 'rtcl-map' );
		}

		if ( FBHelper::isEnabled() ) {
			$this->localization_both_end();
			wp_enqueue_script( 'rtcl-form-builder' );
			wp_enqueue_style( 'rtcl-form-builder' );
		}
	}

	public function load_admin_script_payment() {
		global $pagenow, $post_type;

		// validate page
		if ( ! in_array( $pagenow, [ 'post.php', 'post-new.php', 'edit.php' ] ) ) {
			return;
		}

		if ( rtcl()->post_type_payment != $post_type ) {
			return;
		}

		wp_enqueue_style( 'rtcl-admin' );

		wp_enqueue_script( 'rtcl-validator' );
		wp_enqueue_script( 'select2' );
		wp_enqueue_script( 'rtcl-admin' );
	}

	public function load_admin_script_pricing() {
		global $pagenow, $post_type;

		// validate page
		if ( ! in_array( $pagenow, [ 'post.php', 'post-new.php', 'edit.php' ] ) ) {
			return;
		}

		if ( rtcl()->post_type_pricing != $post_type ) {
			return;
		}

		wp_enqueue_style( 'rtcl-admin' );

		wp_enqueue_script( 'select2' );
		wp_enqueue_script( 'rtcl-validator' );
		wp_enqueue_script( 'rtcl-admin' );
	}

	public function load_admin_script_taxonomy() {
		global $pagenow, $post_type;

		wp_enqueue_style( 'fontawesome' );

		// validate page
		if ( ! in_array( $pagenow, [ 'term.php', 'edit-tags.php' ] ) ) {
			return;
		}

		if ( rtcl()->post_type != $post_type ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style( 'rtcl-admin' );
		wp_enqueue_script( 'select2' );
		wp_enqueue_script( 'rtcl-admin-taxonomy' );
	}

	/**
	 * Script load
	 *
	 * @param  string  $hook
	 */
	public function load_script_at_widget_settings( $hook ) {
		if ( 'widgets.php' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'rtcl-admin' );
		wp_enqueue_script( 'rtcl-admin-widget' );
	}

	/**
	 * @param  String  $hook
	 */
	public function load_script_at_form_builder( $hook ) {
		if ( ! preg_match( "#_page_rtcl-fb$#", $hook ) || ! empty( $_GET['page'] ) && $_GET['page'] !== 'rtcl-fb' ) {
			return;
		}

		$formBuilderLocalize = [
			'ajaxurl'          => $this->ajaxurl,
			'pluginUrl'        => RTCL_URL,
			rtcl()->nonceId    => wp_create_nonce( rtcl()->nonceText ),
			'hasPro'           => rtcl()->has_pro(),
			'theme'            => wp_get_theme()->get_stylesheet(),
			'forms'            => Form::query()->get(),
			'settingFields'    => AvailableFields::settings(),
			'optionFields'     => AvailableFields::optionFields(),
			'slSettingsFields' => AvailableFields::singleLayoutSettingsFields(),
			'slFields'         => AvailableFields::singleLayoutFields(),
			'editor'           => [
				'settingsFields'    => ElementCustomization::settingsFields(),
				'settings'          => [],
				'settingsPlacement' => ElementCustomization::getSettingsPlacement(),
				'shortcode'         => EditorShortCode::getGeneralShortCodes(),
			],
			'options'          => $this->get_fb_settings_options( true ),
			'validation'       => ValidationRuleSettings::get(),
			'fields'           => AvailableFields::get(),
			'i18n'             => LocalizedString::admin(),
		];

		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			wp_dequeue_style( 'wpml-ate-jobs-sync-ui' );
		}
		wp_enqueue_editor();
		wp_localize_script( 'rtcl-fb-admin', 'rtclFB', apply_filters( 'rtcl_localize_fb_admin_params', $formBuilderLocalize ) );
		wp_enqueue_style( 'rtcl-fb-admin' );
		wp_enqueue_script( 'rtcl-fb-admin' );
	}

	/**
	 * @param  String  $hook
	 */
	public function load_script_at_filter_builder( $hook ) {
		if ( ! preg_match( "#_page_rtcl-ajax-filter$#", $hook ) || ! empty( $_GET['page'] ) && $_GET['page'] !== 'rtcl-ajax-filter' ) {
			return;
		}

		$rawForms = Form::query()
		                ->where( 'status', 'publish' )
		                ->order_by( 'created_at', 'DESC' )
		                ->get();
		$forms    = [];
		if ( ! empty( $rawForms ) ) {
			foreach ( $rawForms as $raw_form ) {
				$_form = apply_filters( 'rtcl_fb_form', $raw_form );
				if ( is_a( $_form, Form::class ) ) {
					$forms[] = $_form->toArray();
				}
			}
		}

		$forms = apply_filters( 'rtcl_fb_forms', $forms );

		$rtclObj = [
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'filters' => Functions::get_option( 'rtcl_filter_settings' ),
			'items'   => Options::filterFormItems(),
			'forms'   => $forms,
			'nonce'   => wp_create_nonce( rtcl()->nonceText ),
		];
		wp_enqueue_script( 'rtcl-ajax-filter-admin' );
		wp_localize_script( 'rtcl-ajax-filter-admin', 'rtclFilterObj', apply_filters( 'rtcl_ajax_filter_admin_localize', $rtclObj ) );
		wp_enqueue_style( 'rtcl-admin' );
	}

	private static function localize_script( $handle ) {
		if ( ! in_array( $handle, self::$wp_localize_scripts, true ) ) {
			$data = self::get_script_data( $handle );

			if ( ! $data ) {
				return;
			}

			$name                        = str_replace( '-', '_', $handle ) . '_localized_params';
			self::$wp_localize_scripts[] = $handle;
			wp_localize_script( $handle, $name, apply_filters( $name, $data ) );
		}
	}

	/**
	 * Return data for script handles.
	 *
	 * @param  string  $handle  Script handle the data will be attached to.
	 *
	 * @return array|bool
	 */
	private static function get_script_data( $handle ) {
		switch ( $handle ) {
			case 'rtcl-public':
				$params = [];
				break;
			case 'rtcl-single-listing':
				$params = [
					'slider_options' => apply_filters(
						'rtcl_single_listing_slider_options',
						[
							'rtl'        => is_rtl(),
							'autoHeight' => true,
							'nav'        => [
								'allowTouchMove' => [
									'l' => true,
								],
							],
						],
					),
					'slider_enabled' => Functions::is_gallery_slider_enabled(),
				];
				break;
			default:
				$params = false;
		}

		return apply_filters( 'rtcl_get_script_data', $params, $handle );
	}

	/**
	 * @param  boolean  $admin
	 *
	 * @return array
	 */
	private function get_fb_settings_options( bool $admin = false ): array {
		// 'timezones'       => Options::get_timezone_list()
		$currency = Functions::get_currency();
		$options  = [
			'week_days'       => FBHelper::getWeekDays(),
			'listing_types'   => Functions::get_listing_types(),
			'pricing'         => [
				'pricing_types' => Options::get_listing_pricing_types(),
				'price_types'   => Options::get_price_types(),
				'price_units'   => Options::get_price_unit_list(),
				'currency'      => [
					'id'     => $currency,
					'symbol' => Functions::get_currency_symbol( $currency ),
				],
			],
			'social_profiles' => Options::get_social_profiles_list(),
			'recaptcha'       => [
				'version'  => Functions::get_option_item( 'rtcl_misc_settings', 'recaptcha_version', 2 ),
				'site_key' => Functions::get_option_item( 'rtcl_misc_settings', 'recaptcha_site_key' ),
			],
			'image'           => [
				'sizes' => Gallery::rtcl_gallery_explain_size(),
			],
			'map'             => Functions::get_map_localized_options(),
		];

		if ( $admin ) {
			$i18Options = FBHelper::getBackEndi18nOptions();
			if ( ! empty( $i18Options ) ) {
				$options['translation'] = $i18Options;
			}
			$options['section']        = AvailableFields::getSectionField();
			$options['icons']          = Options::get_icon_class_list();
			$options['top_categories'] = Functions::get_one_level_categories();
		} else {
			$i18Options = FBHelper::getFrontEndi18nOptions();
			if ( ! empty( $i18Options ) ) {
				$options['language'] = $i18Options;
			}
		}

		return apply_filters( 'rtcl_fb_localized_options', $options );
	}
}
