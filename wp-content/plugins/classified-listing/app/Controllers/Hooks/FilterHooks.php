<?php

namespace Rtcl\Controllers\Hooks;

use Elementor\Controls_Manager;
use Rtcl\Helpers\Functions;
use Rtcl\Models\Form\Form;
use Rtcl\Services\FormBuilder\FBHelper;
use WP_Error;
use WP_User;

class FilterHooks {

	/**
	 * The array of templates that this plugin tracks.
	 */
	protected static $templates;

	public static function init() {
		self::$templates = [];
		self::applyHook();
		add_filter( 'embed_oembed_html', [ __CLASS__, 'wrap_embed_with_div' ], 10 );
		add_filter( 'rtcl_process_registration_errors', [ __CLASS__, 'password_validation' ], 10, 4 );
		add_filter( 'rtcl_validate_password_reset', [ __CLASS__, 'validate_password_reset_password' ], 10, 3 );
		add_filter( 'rtcl_my_account_validate_password_reset', [ __CLASS__, 'my_account_reset_password' ], 10, 2 );
		add_filter( 'rtcl_ajaxurl_current_lang', [ __CLASS__, 'wpml_ajaxurl_current_lang' ] );
		add_filter( 'rtcl_get_page_id', [ __CLASS__, 'wpml_get_page_object_id' ] );
		add_filter( 'rtcl_i18_get_post_id', [ __CLASS__, 'wpml_get_post_object_id' ] );
		add_filter( 'rtcl_transient_lang_prefix', [ __CLASS__, 'wpml_transient_lang_prefix' ] );
		add_filter( 'rtcl_single_listing_data_options', [ __CLASS__, 'add_map_options_data' ] );
		add_filter( 'wp_get_attachment_image_attributes', [ __CLASS__, 'add_title_attr_img' ], 10, 2 );
		add_filter( 'rtcl_registration_phone_validation', [ __CLASS__, 'required_phone_validation_at_registration' ] );
		if ( Functions::is_registration_page_separate() ) {
			add_filter( 'rtcl_advanced_settings_options', [ __CLASS__, 'add_registration_endpoint_options' ] );
			add_filter( 'rtcl_my_account_endpoint', [ __CLASS__, 'add_registration_end_points' ], 20 );
		}

		add_filter( 'rtcl_fb_form', [ __CLASS__, 'fb_form_apply_translation' ] );
		add_filter( 'theme_page_templates', [ __CLASS__, 'add_page_templates' ] );
		add_filter( 'wp_insert_post_data', [ __CLASS__, 'register_page_templates' ] );
		add_filter( 'template_include', [ __CLASS__, 'assign_page_template' ], 99 );
		// rss feed
		add_filter( 'pre_get_posts', [ __CLASS__, 'rss_posts_per_page' ], 99 );
		add_action( 'rss2_item', [ __CLASS__, 'custom_data_to_rss' ] );
		// Custom page template
		self::$templates = [
			'rtcl-canvas_template' => 'Classified Listing - Dashboard',
		];

		if ( absint( Functions::get_description_character_limit() ) ) {
			add_filter( 'tiny_mce_before_init', [ __CLASS__, 'tiny_mce_add_past_restriction' ], 10, 2 );
		}
		add_filter( 'rtcl_register_settings_group', [ __CLASS__, 'remove_classic_form_settings' ] );
		add_filter( 'rtcl_account_menu_items', [ __CLASS__, 'remove_menu_items_for_buyer' ], 999 );
		add_filter( 'rtcl_my_account_endpoint', [ __CLASS__, 'remove_menu_endpoints_for_buyer' ], 999 );
		// site title
		add_filter( 'pre_get_document_title', [ __CLASS__, 'listing_archive_site_title' ] );
	}

	/**
	 * Filter the document title for the Classified Listing archive
	 *
	 * @param  string  $title  Current document title
	 *
	 * @return string Modified title
	 */
	public static function listing_archive_site_title( $title ) {
		if ( is_post_type_archive( 'rtcl_listing' ) ) {
			$_name = Functions::get_page_id( 'listings' ) ? get_the_title( Functions::get_page_id( 'listings' ) ) : __( 'All Listings', 'classified-listing' );
			$title = esc_html( $_name ) . ' – ' . esc_html( get_bloginfo( 'name' ) );
		}

		return $title;
	}

	/**
	 * @param $items
	 *
	 * @return array
	 */
	public static function remove_menu_items_for_buyer( $items ) {
		if ( Functions::is_user_type_enabled() ) {
			$user_type = get_user_meta( get_current_user_id(), '_rtcl_user_type', true );

			if ( $user_type === 'buyer' ) {
				unset( $items['listings'] );
				unset( $items['add-listing'] );
			}
		}

		return $items;
	}

	/**
	 * @param $endpoints
	 *
	 * @return array
	 */
	public static function remove_menu_endpoints_for_buyer( $endpoints ) {
		if ( Functions::is_user_type_enabled() ) {
			$user_type = get_user_meta( get_current_user_id(), '_rtcl_user_type', true );

			if ( $user_type === 'buyer' ) {
				unset( $endpoints['listings'] );
			}
		}

		return $endpoints;
	}

	public static function remove_classic_form_settings( $group ) {
		if ( FBHelper::isEnabled() && isset( $group['moderation'] ) ) {
			unset( $group['moderation'] );
		}

		return $group;
	}

	public static function rss_posts_per_page( $query ) {
		if ( is_admin() || ! $query->is_main_query() || ! $query->is_feed() ) {
			return;
		}

		if ( Functions::is_listing_taxonomy() || Functions::is_listings() ) {
			$rss_feed_number = apply_filters( 'rtcl_rss_feed_show_numbers',
				Functions::get_option_item( 'rtcl_general_settings', 'rss_feed_number', 10, 'number' ) );
			$query->set( 'posts_per_rss', $rss_feed_number );
		}

		return $query;
	}

	public static function custom_data_to_rss() {
		if ( get_post_type() === 'rtcl_listing' ) {
			$listing       = rtcl()->factory->get_listing( get_the_ID() );
			if ( has_post_thumbnail( $listing->get_id() ) ) :
				$thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $listing->get_id() ), 'rtcl-thumbnail' );
				$thumbnail = $thumbnail[0] ?? '';
				if ( ! empty( $thumbnail ) ) {
					?>
					<image><?php
						echo esc_url( $thumbnail ); ?></image>;
					<?php
				} endif; ?>
			<?php
			if ( $listing->get_price() ): ?>
				<price><?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $listing->get_price_html(); ?></price>
			<?php
			endif; ?>
			<category><?php
				$listing->the_categories(); ?></category>
			<location><?php
				$listing->the_locations(); ?></location>
			<author><?php
				$listing->the_author(); ?></author>
			<views><?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $listing->get_view_counts(); ?></views>
			<?php
		}
	}

	public static function tiny_mce_add_past_restriction( $in, $editor_id ) {
		if ( $editor_id === 'description' ) {
			$maxLimit = absint( Functions::get_description_character_limit() );
			/* translators:  maxLimit*/
			$errorText              = sprintf( esc_html__( 'Pasting this exceeds the maximum allowed number of %s characters for the input.',
				'classified-listing' ),
				$maxLimit );
			$in['paste_preprocess'] = "function(plugin, args){
													const editor = tinymce.get('description');
													const length = editor.getContent({format: 'text'}).length;
													args.content = args.content.replace(/(<([^>]+)>)/gi, '');
													var maxLength = parseInt($maxLimit, 10);
													if (length + args.content.length > maxLength) {
														toastr.error('$errorText');
														args.content = '';
													}
												}";
		}

		return $in;
	}

	/**
	 * Adds our template to the pages cache in order to trick WordPress
	 * into thinking the template file exists where it doens't really exist.
	 */
	public static function register_page_templates( $atts ) {
		// Create the key used for the themes cache
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		// Retrieve the cache list.
		// If it doesn't exist, or it's empty prepare an array
		$templates = wp_get_theme()->get_page_templates();
		if ( empty( $templates ) ) {
			$templates = [];
		}

		// New cache, therefore remove the old one
		wp_cache_delete( $cache_key, 'themes' );

		// Now add our template to the list of templates by merging our templates
		// with the existing templates array from the cache.
		$templates = array_merge( $templates, self::$templates );

		// Add the modified cache to allow WordPress to pick it up for listing
		// available templates
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );

		return $atts;
	}


	/**
	 * Add page templates.
	 *
	 * @param  array  $templates  The list of page templates
	 *
	 * @return array  $templates  The modified list of page templates
	 */
	public static function add_page_templates( $templates ) {
		$templates = array_merge( $templates, self::$templates );

		return $templates;
	}

	/**
	 * Checks if the template is assigned to the page
	 */
	public static function assign_page_template( $template ) {
		// Return the search template if we're searching (instead of the template for the first result)
		if ( is_search() ) {
			return $template;
		}

		// Get global post
		global $post;

		// Return template if post is empty
		if ( ! $post ) {
			return $template;
		}

		// Return default template if we don't have a custom one defined
		if ( ! isset( self::$templates[ get_post_meta(
				$post->ID,
				'_wp_page_template',
				true,
			) ] )
		) {
			return $template;
		}

		// Allows filtering of file path
		$selected_template    = get_post_meta( $post->ID, '_wp_page_template', true );
		$canvas_template_path = apply_filters( 'rtcl_page_template_path', RTCL_PATH ) . 'templates/canvas-template.php';
		$file                 = ( 'rtcl-canvas_template' === $selected_template ) ? $canvas_template_path : '';

		// Just to be safe, we check if the file exist first
		if ( file_exists( $file ) ) {
			return $file;
		}

		// Return template
		return $template;
	}

	/**
	 * @param  Form  $form
	 *
	 * @return Form
	 */
	public static function fb_form_apply_translation( $form ) {
		if ( is_a( $form, Form::class ) && defined( 'ICL_SITEPRESS_VERSION' ) ) {
			$default = apply_filters( 'wpml_default_language', null );
			$current = apply_filters( 'wpml_current_language', null );

			if ( $default && $current && $default !== $current ) {
				$form->translatedForm( $current );
			}
		}

		return $form;
	}

	public static function add_title_attr_img( $attr, $attachment = null ) {
		if ( ! empty( $attachment ) ) {
			$attr['title'] = esc_attr( wp_get_attachment_caption( $attachment->ID ) );
		}

		return $attr;
	}

	/**
	 * @return bool
	 */
	public static function required_phone_validation_at_registration() {
		if ( Functions::get_option_item( 'rtcl_account_settings', 'disable_phone_at_registration', false, 'checkbox' ) ) {
			return false;
		}

		return Functions::get_option_item( 'rtcl_account_settings', 'required_phone_at_registration', false, 'checkbox' );
	}

	public static function remove_registration_name_validation() {
		return false;
	}

	public static function add_map_options_data( $options ) {
		global $listing, $rtcl_has_map_data;
		if ( $rtcl_has_map_data && $listing ) {
			$options = wp_parse_args( Functions::get_map_data( $listing, 'content' ), $options );
		}

		return $options;
	}

	public static function wpml_ajaxurl_current_lang( $current_lang ) {
		if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
			return $current_lang;
		}

		return apply_filters( 'wpml_current_language', null );
	}

	public static function wpml_transient_lang_prefix( $prefix ) {
		if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
			return $prefix;
		}
		global $sitepress;

		return '_' . $sitepress->get_current_language();
	}

	public static function wpml_get_page_object_id( $page_id ) {
		if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
			return $page_id;
		}

		return apply_filters( 'wpml_object_id', absint( $page_id ), 'page', true );
	}

	public static function wpml_get_post_object_id( $post_id ) {
		if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
			return $post_id;
		}

		return apply_filters( 'wpml_object_id', absint( $post_id ), 'post', true );
	}


	/**
	 * @param  WP_Error  $errors
	 * @param  string  $password
	 *
	 * @return WP_Error
	 */
	public static function my_account_reset_password( $errors, $password ) {
		self::min_password_validation_message( $errors, $password );

		return $errors;
	}

	/**
	 * @param  WP_Error  $errors
	 * @param  WP_User  $user
	 * @param  array  $posted_fields
	 *
	 * @return WP_Error
	 */
	public static function validate_password_reset_password( $errors, $user, $posted_fields ) {
		$password = trim( $posted_fields['password_1'] );
		self::min_password_validation_message( $errors, $password );

		return $errors;
	}

	/**
	 * @param  WP_Error  $errors
	 * @param  string  $email
	 * @param  string  $username
	 * @param  string  $password
	 */
	public static function password_validation( $errors, $email, $username, $password ) {
		self::min_password_validation_message( $errors, $password );

		return $errors;
	}

	/**
	 * @param  WP_Error  $errors
	 * @param  string  $password
	 */
	private static function min_password_validation_message( &$errors, $password ) {
		$length = Functions::password_min_length();
		if ( $length && strlen( $password ?? '' ) < $length ) {
			$errors->add( 'rtcl_min_pass_length',
				/* translators: Password length */
				sprintf( esc_html__( "Your password must be at least %d characters long.", "classified-listing" ), Functions::password_min_length() ) );
		}
	}

	public static function wrap_embed_with_div( $html ) {
		return "<div class=\"responsive-container\">" . $html . "</div>";
	}


	private static function applyHook() {
		/**
		 * Short Description (excerpt).
		 */
		if ( function_exists( 'do_blocks' ) ) {
			add_filter( 'rtcl_short_description', 'do_blocks', 9 );
		}
		add_filter( 'rtcl_short_description', 'wptexturize' );
		add_filter( 'rtcl_short_description', 'convert_smilies' );
		add_filter( 'rtcl_short_description', 'convert_chars' );
		add_filter( 'rtcl_short_description', 'wpautop' );
		add_filter( 'rtcl_short_description', 'shortcode_unautop' );
		add_filter( 'rtcl_short_description', 'prepend_attachment' );
		add_filter( 'rtcl_short_description', 'do_shortcode', 11 ); // After wpautop().
		add_filter( 'rtcl_short_description', [ Functions::class, 'format_product_short_description' ], 9999999 );
		add_filter( 'rtcl_short_description', [ Functions::class, 'do_oembeds' ] );
		add_filter( 'rtcl_short_description', [ $GLOBALS['wp_embed'], 'run_shortcode' ], 8 ); // Before wpautop().
	}

	public static function add_registration_endpoint_options( $options ) {
		$position = array_search( 'myaccount_edit_account_endpoint', array_keys( $options ) );

		if ( $position > - 1 ) {
			$newOptions = [
				'myaccount_registration_endpoint' => [
					'title'   => esc_html__( 'Registration', 'classified-listing' ),
					'type'    => 'text',
					'default' => 'registration',
				],
			];
			Functions::array_insert( $options, $position, $newOptions );
		}

		return $options;
	}

	public static function add_registration_end_points( $endpoints ) {
		$endpoints['registration'] = Functions::get_option_item( 'rtcl_advanced_settings', 'myaccount_registration_endpoint', 'registration' );

		return $endpoints;
	}

}