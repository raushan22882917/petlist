<?php
/* phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing*/

namespace Rtcl\Controllers\Hooks;

use Rtcl\Gateways\Store\GatewayStore;
use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;
use Rtcl\Helpers\Text;
use Rtcl\Models\Listing;
use Rtcl\Models\Payment;
use Rtcl\Resources\Options;
use Rtcl\Services\FormBuilder\FBHelper;
use Rtcl\Shortcodes\Checkout;
use Rtcl\Shortcodes\MyAccount;
use Rtcl\Traits\Hooks\TemplateHookTrait;
use Rtcl\Widgets\AjaxFilter;
use Rtcl\Widgets\Filter;

class TemplateHooks {
	use TemplateHookTrait;

	public static function init() {
		add_filter( 'body_class', [ __CLASS__, 'body_class' ] );
		add_filter( 'post_class', [ __CLASS__, 'listing_post_class' ], 20, 3 );

		/**
		 * Listing form hook
		 */
		add_action( "rtcl_listing_form", [ __CLASS__, 'listing_category' ], 5 );
		add_action( "rtcl_listing_form", [ __CLASS__, 'listing_information' ], 10 );
		add_action( "rtcl_listing_form", [ __CLASS__, 'listing_gallery' ], 20 );
		add_action( "rtcl_listing_form", [ __CLASS__, 'listing_contact' ], 30 );
		add_action( "rtcl_listing_form", [ __CLASS__, 'listing_recaptcha' ], 90 );
		add_action( "rtcl_listing_form", [ __CLASS__, 'listing_terms_conditions' ], 100 );
		add_action( "rtcl_listing_form_end", [ __CLASS__, 'add_listing_form_hidden_field' ], 10 );
		add_action( "rtcl_listing_form_end", [ __CLASS__, 'add_wpml_support' ], 20 );
		add_action( "rtcl_listing_form_end", [ __CLASS__, 'listing_form_submit_button' ], 50 );


		add_action( "rtcl_widget_filter_form", [ __CLASS__, 'widget_filter_form_ad_type_item' ], 10 );
		add_action( "rtcl_widget_filter_form", [ __CLASS__, 'widget_filter_form_category_item' ], 20 );
		add_action( "rtcl_widget_filter_form", [ __CLASS__, 'widget_filter_form_tag_item' ], 35 );
		add_action( "rtcl_widget_filter_form", [ __CLASS__, 'widget_filter_form_location_item' ], 30 );
		add_action( "rtcl_widget_filter_form", [ __CLASS__, 'widget_filter_form_radius_item' ], 40, 2 );
		add_action( "rtcl_widget_filter_form", [ __CLASS__, 'widget_filter_form_price_item' ], 90 );
		add_action( "rtcl_widget_filter_form_end", [ __CLASS__, 'add_apply_filter_button' ], 10 );
		add_action( "rtcl_widget_filter_form_end", [ __CLASS__, 'add_wpml_support' ], 90 );
		add_action( "rtcl_widget_search_inline_form", [ __CLASS__, 'add_wpml_support' ] );
		add_action( "rtcl_widget_search_vertical_form", [ __CLASS__, 'add_wpml_support' ] );

		/**
		 * Listing thumbnail hook
		 */
		add_action( 'rtcl_after_listing_thumbnail', [ __CLASS__, 'loop_item_meta_buttons' ], 10 );

		/**
		 * Content Wrappers.
		 *
		 * @see output_content_wrapper()
		 * @see breadcrumb()
		 * @see output_content_wrapper_end()
		 */
		add_action( 'rtcl_before_content_wrapper', [ __CLASS__, 'container_start' ], 1 );
		add_action( 'rtcl_after_content_wrapper', [ __CLASS__, 'container_end' ], 100 );
		add_action( 'rtcl_before_main_content', [ __CLASS__, 'breadcrumb' ], 6 );
		add_action( 'rtcl_before_main_content', [ __CLASS__, 'output_main_wrapper_start' ], 8 );
		add_action( 'rtcl_before_main_content', [ __CLASS__, 'output_content_wrapper' ], 10 );
		add_action( 'rtcl_after_main_content', [ __CLASS__, 'output_content_wrapper_end' ], 10 );
		/**
		 *
		 * Sidebar.
		 *
		 * @see get_sidebar()
		 */
		add_action( 'rtcl_sidebar', [ __CLASS__, 'get_sidebar' ], 10 );
		add_action( 'rtcl_sidebar', [ __CLASS__, 'output_main_wrapper_end' ], 15 );

		add_action( 'rtcl_archive_description', [ __CLASS__, 'taxonomy_archive_description' ], 10 );
		add_action( 'rtcl_archive_description', [ __CLASS__, 'listing_archive_description' ], 10 );

		add_action( 'rtcl_before_listing_loop', [ __CLASS__, 'listing_actions' ], 20 );
		add_action( 'rtcl_listing_loop_action', [ __CLASS__, 'result_count' ], 10 );
		add_action( 'rtcl_listing_loop_action', [ __CLASS__, 'catalog_ordering' ], 20 );
		add_action( 'rtcl_no_listings_found', [ __CLASS__, 'no_listings_found' ] );
		add_action( 'rtcl_shortcode_listings_loop_no_results', [ __CLASS__, 'no_listings_found' ] );

		add_action( 'rtcl_listing_loop_item_start', [ __CLASS__, 'listing_thumbnail' ] );


		add_action( 'rtcl_listing_loop_item', [ __CLASS__, 'loop_item_wrapper_start' ], 10 );
		add_action( 'rtcl_listing_loop_item', [ __CLASS__, 'loop_item_listing_title' ], 20 );
		add_action( 'rtcl_listing_loop_item', [ __CLASS__, 'loop_item_badges' ], 30 );
		add_action( 'rtcl_listing_loop_item', [ __CLASS__, 'loop_item_meta' ], 50 );
		//add_action( 'rtcl_listing_loop_item', [ __CLASS__, 'loop_item_meta_buttons' ], 60 );
		add_action( 'rtcl_listing_loop_item', [ __CLASS__, 'loop_item_excerpt' ], 70 );
		add_action( 'rtcl_listing_loop_item', [ __CLASS__, 'listing_price' ], 80 );
		add_action( 'rtcl_listing_loop_item', [ __CLASS__, 'loop_item_wrapper_end' ], 100 );

		add_action( 'rtcl_after_listing_loop', [ __CLASS__, 'pagination' ], 10 );

		/**
		 * Notice
		 */
		add_action( 'rtcl_before_main_content', [ __CLASS__, 'output_all_notices' ], 7 );

		add_action( 'rtcl_account_navigation', [ __CLASS__, 'account_navigation' ] );
		add_action( 'rtcl_account_content', [ __CLASS__, 'account_content' ] );
		add_action( 'rtcl_account_listings_endpoint', [ __CLASS__, 'account_listings_endpoint' ] );
		add_action( 'rtcl_account_favourites_endpoint', [ __CLASS__, 'account_favourites_endpoint' ] );
		add_action( 'rtcl_account_edit-account_endpoint', [ __CLASS__, 'account_edit_account_endpoint' ] );
		add_action( 'rtcl_account_rtcl_edit_account_endpoint', [ __CLASS__, 'account_edit_account_endpoint' ] );
		add_action( 'rtcl_account_payments_endpoint', [ __CLASS__, 'account_payments_endpoint' ] );
		add_action( 'rtcl_account_profile-settings_endpoint', [ __CLASS__, 'account_profile_settings_endpoint' ] );
		add_action( 'rtcl_checkout_content', [ __CLASS__, 'checkout_content' ] );
		add_action( 'rtcl_checkout_submission_endpoint', [ __CLASS__, 'checkout_submission_endpoint' ], 10, 2 );
		add_action( 'rtcl_checkout_payment-receipt_endpoint', [
			__CLASS__,
			'checkout_payment_receipt_endpoint',
		], 10, 2 );

		add_action( 'rtcl_account_dashboard', [ __CLASS__, 'user_information' ] );

		add_action( 'rtcl_single_listing_content', [ __CLASS__, 'add_single_listing_title' ], 5 );
		add_action( 'rtcl_single_listing_content', [ __CLASS__, 'add_single_listing_meta' ], 10 );
		add_action( 'rtcl_single_listing_content', [ __CLASS__, 'add_single_listing_gallery' ], 30 );
		add_action( 'rtcl_single_listing_content_end', [ __CLASS__, 'single_listing_map_content' ] );

		add_action( 'rtcl_single_listing_review', [ __CLASS__, 'add_single_listing_review' ], 10 );
		add_action( 'rtcl_single_listing_sidebar', [ __CLASS__, 'add_single_listing_sidebar' ], 10 );
		add_action( 'rtcl_single_listing_inner_sidebar', [
			__CLASS__,
			'add_single_listing_inner_sidebar_custom_field',
		], 10 );
		add_action( 'rtcl_single_listing_inner_sidebar', [ __CLASS__, 'add_single_listing_inner_sidebar_action' ], 20 );

		if ( ! Functions::get_option_item( 'rtcl_account_settings', 'disable_name_phone_registration', false, 'checkbox' ) ) {
			add_action( 'rtcl_register_form_start', [ __CLASS__, 'add_name_fields_at_registration_form' ], 10 );
		} else {
			add_filter( 'rtcl_registration_name_validation', function () {
				return false;
			} );
		}

		if ( ! Functions::get_option_item( 'rtcl_account_settings', 'disable_phone_at_registration', false, 'checkbox' ) ) {
			add_action( 'rtcl_register_form_start', [ __CLASS__, 'add_phone_at_registration_form' ], 20 );
		}

		if ( Functions::is_user_type_enabled() ) {
			add_action( 'rtcl_register_form_start', [ __CLASS__, 'add_user_type_at_registration_form' ], 30 );
		}

		/**
		 * Check out form
		 */
		add_action( 'rtcl_before_checkout_form', [ __CLASS__, 'add_checkout_form_instruction' ], 10 );
		add_action( 'rtcl_checkout_form_start', [ __CLASS__, 'add_checkout_form_promotion_options' ], 10, 2 );
		add_action( 'rtcl_checkout_form', [ __CLASS__, 'add_checkout_billing_details' ], 10, 2 );
		add_action( 'rtcl_checkout_form', [ __CLASS__, 'add_checkout_overview' ], 15, 2 );
		add_action( 'rtcl_checkout_form', [ __CLASS__, 'add_checkout_payment_method' ], 20, 2 );
		add_action( 'rtcl_checkout_form', [ __CLASS__, 'checkout_terms_and_conditions' ], 50 );
		add_action( 'rtcl_checkout_form_submit_button', [ __CLASS__, 'checkout_form_submit_button' ], 10 );
		add_action( 'rtcl_checkout_form_end', [ __CLASS__, 'add_checkout_hidden_field' ], 50 );
		add_action( 'rtcl_checkout_form_end', [ __CLASS__, 'add_submission_checkout_hidden_field' ], 60, 2 );
		add_action( 'rtcl_checkout_form_store_gateway', [ __CLASS__, 'checkout_form_store_gateway' ], 10 );


		add_action( 'rtcl_checkout_terms_and_conditions', [ __CLASS__, 'checkout_privacy_policy_text' ], 20 );
		add_action( 'rtcl_checkout_terms_and_conditions', [
			__CLASS__,
			'checkout_terms_and_conditions_page_content',
		], 30 );

		/**
		 * Misc Hooks
		 */
		add_action( 'rtcl_widget_filter_form_end', [ __CLASS__, 'add_hidden_field_filter_form' ], 50 );
		add_action( 'rtcl_login_form_end', [ __CLASS__, 'social_login_shortcode' ], 10 );
		add_action( 'rtcl_login_form_end', [ __CLASS__, 'logged_in_hidden_fields' ], 20 );
		add_action( 'rtcl_register_form', [ __CLASS__, 'registration_privacy_policy' ], 20 );
		add_action( 'rtcl_register_form', [ __CLASS__, 'registration_terms_and_conditions' ], 30 );
		add_action( 'rtcl_register_form_end', [ __CLASS__, 'registration_hidden_fields' ], 100 );
		add_action( 'rtcl_after_login_form', [ __CLASS__, 'login_form_necessary_link' ], 10 );

		add_action( 'rtcl_listing_badges', [ __CLASS__, 'listing_new_badge' ], 10 );
		add_action( 'rtcl_listing_badges', [ __CLASS__, 'listing_featured_badge' ], 20 );

		// Profile page
		add_action( 'rtcl_edit_account_form', [ __CLASS__, 'edit_account_wrapper_column_start' ], 10 );
		add_action( 'rtcl_edit_account_form', [ __CLASS__, 'edit_account_form_social_profile_field' ], 70 );
		add_action( 'rtcl_edit_account_form', [ __CLASS__, 'edit_account_form_location_field' ], 50 );
		if ( Functions::has_map() ) {
			if ( 'geo' === Functions::location_type() ) {
				remove_action( 'rtcl_edit_account_form', [ __CLASS__, 'edit_account_form_location_field' ], 50 );
				add_action( 'rtcl_edit_account_form', [ __CLASS__, 'edit_account_form_geo_location' ], 50 );
			}
			add_action( 'rtcl_edit_account_form', [ __CLASS__, 'edit_account_map_field' ], 60 );
		}
		add_action( 'rtcl_edit_account_form', [ __CLASS__, 'edit_account_wrapper_column_end' ], 60 );
		add_action( 'rtcl_edit_account_form_end', [ __CLASS__, 'edit_account_form_submit_button' ], 10 );
		add_action( 'rtcl_edit_account_form_end', [ __CLASS__, 'edit_account_form_hidden_field' ], 50 );

		add_action( 'rtcl_listing_meta_buttons', [ __CLASS__, 'add_favourite_button' ], 10 );


		// My listing actions
		add_action( 'rtcl_my_listing_actions', [ __CLASS__, 'my_listing_promotion_button' ] );
		add_action( 'rtcl_my_listing_actions', [ __CLASS__, 'my_listing_renew_button' ], 15 );
		add_action( 'rtcl_my_listing_actions', [ __CLASS__, 'my_listing_edit_button' ], 20 );
		add_action( 'rtcl_my_listing_actions', [ __CLASS__, 'my_listing_delete_button' ], 30 );

		// Listing seller contact
		add_action( 'rtcl_listing_seller_information', [ __CLASS__, 'author_information' ], 8 );
		add_action( 'rtcl_listing_seller_information', [ __CLASS__, 'seller_location' ], 10 );
		add_action( 'rtcl_listing_seller_information', [ __CLASS__, 'seller_phone_whatsapp_number' ], 20 );
		add_action( 'rtcl_listing_seller_information', [ __CLASS__, 'seller_telegram' ], 25 );
		add_action( 'rtcl_listing_seller_information', [ __CLASS__, 'seller_email' ], 30 );
		add_action( 'rtcl_listing_seller_information', [ __CLASS__, 'seller_website' ], 50 );

		// payment receipt
		add_action( 'rtcl_payment_receipt_top_offline', [ __CLASS__, 'offline_payment_instruction' ], 10, 2 );
		add_action( 'rtcl_payment_receipt', [ __CLASS__, 'payment_receipt_payment_info' ], 10, 2 );
		add_action( 'rtcl_payment_receipt', [ __CLASS__, 'payment_receipt_pricing_info' ], 20, 2 );
		add_action( 'rtcl_payment_receipt', [ __CLASS__, 'payment_receipt_billing_info' ], 30, 2 );
		add_action( 'rtcl_payment_receipt', [ __CLASS__, 'payment_receipt_actions' ], 50 );
		add_action( 'rtcl_payment_receipt_popup', [ __CLASS__, 'payment_receipt_popup_pricing_info' ], 20, 2 );
		add_action( 'rtcl_payment_receipt_popup', [ __CLASS__, 'payment_receipt_popup_actions' ], 50 );
		add_action( 'rtcl_payment_receipt_details_before_total_amount', [ __CLASS__, 'payment_receipt_tax_info' ], 20 );

		// Ajax filters
		add_action( 'rtcl_widget_ajax_filter_render_search', [ __CLASS__, 'ajax_filter_render_search' ], 10, 3 );
		add_action( 'rtcl_widget_ajax_filter_render_ad_type', [ __CLASS__, 'ajax_filter_render_ad_type' ], 10, 3 );
		add_action( 'rtcl_widget_ajax_filter_render_category', [ __CLASS__, 'ajax_filter_render_category' ], 10, 3 );
		add_action( 'rtcl_widget_ajax_filter_render_location', [ __CLASS__, 'ajax_filter_render_location' ], 10, 3 );
		add_action( 'rtcl_widget_ajax_filter_render_tag', [ __CLASS__, 'ajax_filter_render_tag' ], 10, 3 );
		add_action( 'rtcl_widget_ajax_filter_render_price_range', [
			__CLASS__,
			'ajax_filter_render_price_range',
		], 10, 3 );
		add_action( 'rtcl_widget_ajax_filter_render_radius_filter', [
			__CLASS__,
			'ajax_filter_render_radius_filter',
		], 10, 3 );
	}


	/**
	 * @param  array  $itemData
	 * @param  array  $filterData
	 * @param  AjaxFilter  $object
	 *
	 * @return void
	 */
	public static function ajax_filter_render_search( $itemData, $filterData, $object ) {
		$q                  = ! empty( $_GET['q'] ) ? trim( sanitize_text_field( wp_unslash( $_GET['q'] ) ) )
			: ''; /* phpcs:ignore WordPress.Security.NonceVerification.Recommended */
		$itemData['title']  = ! empty( $itemData['title'] ) ? $itemData['title'] : esc_html__( 'Search', 'classified-listing' );
		$placeholder        = ! empty( $itemData['placeholder'] ) ? $itemData['placeholder'] : esc_html__( 'Search ...', 'classified-listing' );
		$itemData['active'] = ! empty( $q );
		$field_html         = '';
		$filed_class        = Functions::is_semantic_quick_search_enabled() ? 'rtcl-ajax-filter-text rtcl-ai-search-field' : 'rtcl-ajax-filter-text-search rtcl-ajax-filter-text';
		$field_html         = sprintf( '<div class="%1$s">
											<input name="q" type="text"  aria-label="Keyword" autocomplete="off" value="%2$s" class="rtcl-form-control rtcl-filter-text-field" placeholder="%3$s">
											<i class="rtcl-clear-text rtcl-icon-trash"></i>
											<span class="rtcl-ai-quick-search">
												<span class="rtcl-ai-quick-search-inner">
    												<span class="rtcl-ai-quick-search-text">%4$s</span>
												</span>
											</span>
											<span class="rtcl-ajax-filter-search-icon"><svg width="21" height="21" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M8.65429 17.2954C3.88229 17.2954 0 13.4161 0 8.64769C0 3.87933 3.88229 0 8.65429 0C13.4263 0 17.3086 3.87933 17.3086 8.64769C17.3086 13.4161 13.4263 17.2954 8.65429 17.2954ZM8.65429 1.63937C4.78693 1.63937 1.64062 4.78328 1.64062 8.64769C1.64062 12.5121 4.78693 15.656 8.65429 15.656C12.5216 15.656 15.668 12.5121 15.668 8.64769C15.668 4.78328 12.5216 1.63937 8.65429 1.63937ZM20.7598 20.76C21.0801 20.4398 21.0801 19.9208 20.7598 19.6007L17.0889 15.9326C16.7685 15.6125 16.2491 15.6125 15.9287 15.9326C15.6084 16.2527 15.6084 16.7718 15.9287 17.0919L19.5996 20.76C19.7598 20.92 19.9697 21 20.1797 21C20.3897 21 20.5995 20.92 20.7598 20.76Z" fill="#646464"></path>
						</svg></span>
										</div>',
			esc_attr( $filed_class ),
			esc_attr( $q ),
			esc_attr( $placeholder ),
			esc_html__( 'AI Best Matches', 'classified-listing' ),
		);

		if ( Functions::is_semantic_quick_search_enabled() ) {
			$field_html .= '<div class="rtcl-ai-search-result-container"><div class="rtcl-ai-search-result-header"><h4>Analyzing through AI<span class="dots"></span></h4></div><div class="rtcl-ai-search-result-content"></div></div>';
		}

		$options = [ 'name' => 'filter_search', 'filter_key' => 'q', 'field_type' => 'text' ];
		Functions::print_html( $object->render_filter_item( $itemData, $options, $field_html ), true );
	}

	/**
	 * @param  array  $itemData
	 * @param  array  $filterData
	 * @param  AjaxFilter  $object
	 *
	 * @return void
	 */
	public static function ajax_filter_render_ad_type( $itemData, $filterData, $object ) {
		if ( Functions::is_ad_type_disabled() ) {
			return;
		}
		$ad_type        = ! empty( $_GET['filter_ad_type'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_ad_type'] ) )
			: ''; /* phpcs:ignore WordPress.Security.NonceVerification.Recommended */
		$selectedValues = $ad_type ? explode( ',', $ad_type ) : [];
		if ( empty( $selectedValues ) && ! empty( $_GET['filters']['ad_type'] ) ) {
			$selectedValues = [ sanitize_text_field( wp_unslash( $_GET['filters']['ad_type'] ) ) ];
		}
		$itemData['title'] = ! empty( $itemData['title'] ) ? $itemData['title'] : esc_html__( 'Ad Type', 'classified-listing' );
		$fieldType         = ! empty( $itemData['type'] )
							 && in_array( $itemData['type'],
			[
				'checkbox',
				'radio',
				'select',
			] ) ? $itemData['type'] : 'checkbox';
		$options           = [
			'name'       => 'filter_ad_type',
			'field_type' => $fieldType,
			'values'     => $selectedValues,
		];

		$field_html = '<div class="rtcl-ajax-filter-data">';
		$ad_types   = Functions::get_listing_types();
		$count      = 0;
		if ( ! empty( $ad_types ) ) {
			if ( in_array( $fieldType, [ 'checkbox', 'radio' ] ) ) {
				foreach ( $ad_types as $optKey => $optValue ) {
					$count ++;
					$field_html .= sprintf( '<div class="rtcl-ajax-filter-data-item rtcl-filter-checkbox-item rtcl-filter-ad_type-%1$s%6$s">
															<div class="rtcl-ajax-filter-diiWrap">
																<input id="filters-ad-type-value-%1$s" name="%2$s" value="%1$s" type="%3$s" class="rtcl-filter-checkbox"%4$s />
																<label for="filters-ad-type-value-%1$s" class="rtcl-filter-checkbox-label" tabindex="0">
																	<span class="rtcl-filter-checkbox-text">%5$s</span>
																</label>
															</div>
												</div>',
						esc_attr( $optKey ),
						$options['name'],
						$fieldType,
						in_array( $optKey, $selectedValues ) ? ' checked' : '',
						esc_html( $optValue ),
						$count >= 6 ? ' hideAble' : '',
					);
				}
			} elseif ( $fieldType == 'select' ) {
				$field_html .= '<select aria-label="' . esc_attr( $options['name'] ) . '" class="rtcl-filter-select-item rtcl-form-control" name="' . esc_attr( $options['name'] ) . '">';
				$field_html .= '<option value="">' . __( 'Select', 'classified-listing' ) . '</option>';
				foreach ( $ad_types as $optKey => $optValue ) {
					$field_html .= sprintf( '<option value="%1$s"%2$s>%3$s</option>',
						esc_attr( $optKey ),
						in_array( $optKey, $selectedValues ) ? ' selected' : '',
						esc_html( $optValue ),
					);
				}
				$field_html .= '</select>';
			}
		}
		if ( $fieldType !== 'select' && $count >= 6 ) {
			$field_html .= '<div class="rtcl-more-less-btn">
										<div class="text more-text" tabindex="0"><i class="rtcl-icon rtcl-icon-plus-1"></i>' . __( 'More', 'classified-listing' ) . '</div>
										<div class="text less-text" tabindex="0"><i class="rtcl-icon rtcl-icon-minus-1"></i>' . __( 'Less', 'classified-listing' ) . '</div>
								</div>';
		}
		$field_html .= '</div>';

		Functions::print_html( $object->render_filter_item( $itemData, $options, $field_html ), true );
	}

	/**
	 * @param  array  $itemData
	 * @param  array  $filterData
	 * @param  AjaxFilter  $object
	 *
	 * @return void
	 */
	public static function ajax_filter_render_category( $itemData, $filterData, $object ) {
		$fieldType      = ! empty( $itemData['type'] )
						  && in_array( $itemData['type'],
			[
				'checkbox',
				'radio',
			] ) ? $itemData['type'] : 'checkbox';
		$category       = ! empty( $_GET['filter_category'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_category'] ) )
			: ''; /* phpcs:ignore WordPress.Security.NonceVerification.Recommended */
		$selectedValues = $category ? explode( ',', $category ) : [];
		$catSlug        = ! empty( $_GET['rtcl_category'] ) ? wp_unslash( sanitize_text_field( $_GET['rtcl_category'] ) ) : get_query_var( 'rtcl_category' );
		if ( $catSlug ) {
			$cat_term = get_term_by( 'slug', $catSlug, rtcl()->category );
			if ( $cat_term && ! is_wp_error( $cat_term ) ) {
				$selectedValues[] = $cat_term->term_id;
			}
		}
		$itemData['title'] = ! empty( $itemData['title'] ) ? $itemData['title'] : esc_html__( 'Category', 'classified-listing' );
		$options           = wp_parse_args( $itemData,
			[
				'name'       => 'filter_category',
				'taxonomy'   => rtcl()->category,
				'field_type' => $fieldType,
				'values'     => $selectedValues,
				'ajax_load'  => 1,
			] );

		if ( class_exists( 'RtclStore' ) && \RtclStore\Helpers\Functions::is_single_store() ) {
			$options['is_single_store'] = get_the_ID();
		}

		Functions::print_html( $object->render_filter_item( $itemData, $options ), true );
	}


	/**
	 * @param  array  $itemData
	 * @param  array  $filterData
	 * @param  AjaxFilter  $object
	 *
	 * @return string|void
	 */
	public static function ajax_filter_render_location( $itemData, $filterData, $object ) {
		if ( 'geo' === Functions::location_type() ) {
			return '';
		}

		$fieldType      = ! empty( $itemData['type'] )
						  && in_array( $itemData['type'],
			[
				'checkbox',
				'radio',
			] ) ? $itemData['type'] : 'checkbox';
		$locations      = ! empty( $_GET['filter_location'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_location'] ) )
			: ''; /* phpcs:ignore WordPress.Security.NonceVerification.Recommended */
		$selectedValues = $locations ? explode( ',', $locations ) : [];
		$locSlug        = ! empty( $_GET['rtcl_location'] ) ? wp_unslash( sanitize_text_field( $_GET['rtcl_location'] ) ) : get_query_var( 'rtcl_location' );
		if ( $locSlug ) {
			$loc_term = get_term_by( 'slug', $locSlug, rtcl()->location );
			if ( $loc_term && ! is_wp_error( $loc_term ) ) {
				$selectedValues[] = $loc_term->term_id;
			}
		}
		$itemData['title'] = ! empty( $itemData['title'] ) ? $itemData['title'] : esc_html__( 'Category', 'classified-listing' );
		$options           = wp_parse_args( $itemData,
			[
				'name'       => 'filter_location',
				'taxonomy'   => rtcl()->location,
				'field_type' => $fieldType,
				'values'     => $selectedValues,
				'ajax_load'  => 1,
			] );
		Functions::print_html( $object->render_filter_item( $itemData, $options ), true );
	}


	/**
	 * @param  array  $itemData
	 * @param  array  $filterData
	 * @param  AjaxFilter  $object
	 *
	 * @return void
	 */
	public static function ajax_filter_render_tag( $itemData, $filterData, $object ) {
		$fieldType      = ! empty( $itemData['type'] )
						  && in_array( $itemData['type'],
			[
				'checkbox',
				'radio',
			] ) ? $itemData['type'] : 'checkbox';
		$tags           = ! empty( $_GET['filter_tag'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_tag'] ) )
			: ''; /* phpcs:ignore WordPress.Security.NonceVerification.Recommended */
		$selectedValues = $tags ? explode( ',', $tags ) : [];
		$tagSlug        = ! empty( $_GET['rtcl_tag'] ) ? wp_unslash( sanitize_text_field( $_GET['rtcl_tag'] ) ) : get_query_var( 'rtcl_tag' );
		if ( $tagSlug ) {
			$tag_term = get_term_by( 'slug', $tagSlug, rtcl()->tag );
			if ( $tag_term && ! is_wp_error( $tag_term ) ) {
				$selectedValues[] = $tag_term->term_id;
			}
		}
		$itemData['title'] = ! empty( $itemData['title'] ) ? $itemData['title'] : esc_html__( 'Tag', 'classified-listing' );
		$options           = wp_parse_args( $itemData,
			[
				'name'       => 'filter_tag',
				'taxonomy'   => rtcl()->tag,
				'field_type' => $fieldType,
				'values'     => $selectedValues,
				'ajax_load'  => 1,
			] );
		Functions::print_html( $object->render_filter_item( $itemData, $options ), true );
	}

	/**
	 * @param  array  $itemData
	 * @param  array  $filterData
	 * @param  AjaxFilter  $object
	 *
	 * @return void
	 */
	public static function ajax_filter_render_price_range( $itemData, $filterData, $object ) {
		$filterInputPrice   = ! empty( $_GET['filter_price'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_price'] ) )
			: ''; /* phpcs:ignore WordPress.Security.NonceVerification.Recommended */
		$filterInputPrice   = ! empty( $filterInputPrice ) ? explode( ',', $filterInputPrice ) : [ null, null ];
		$minPrice           = ! empty( $itemData['min_price'] ) ? absint( $itemData['min_price'] ) : 0;
		$maxPrice           = ! empty( $itemData['max_price'] ) ? absint( $itemData['max_price'] ) : 50000;
		$step               = ! empty( $itemData['step'] ) ? absint( $itemData['step'] ) : 1000;
		$inputMinPrice      = $filterInputPrice[0] ?? $minPrice;
		$inputMaxPrice      = $filterInputPrice[1] ?? $maxPrice;
		$field_html         = sprintf( '<div class="rtcl-price-range-wrap">
												<div class="rtcl-price-range-slider rtcl-noUiSlider" data-min="%1$d" data-max="%2$d" data-step="%3$d"></div>
												<div class="rtcl-range-slider-input-wrap">
													<input type="number" name="filter_min_price" class="rtcl-form-control rtcl-range-slider-input min" placeholder="%6$s" value="%4$d" min="%1$d" max="%2$d" step="%3$d" aria-label="min-price">
													<input type="number" name="filter_max_price" class="rtcl-form-control rtcl-range-slider-input max" placeholder="%7$s" value="%5$d" min="%1$d" max="%2$d" step="%3$d" aria-label="max-price">
												</div>
											</div>',
			absint( $minPrice ),
			absint( $maxPrice ),
			absint( $step ),
			$inputMinPrice,
			$inputMaxPrice,
			esc_html__( 'min', 'classified-listing' ),
			esc_html__( 'max', 'classified-listing' ),
		);
		$itemData['active'] = $filterInputPrice[0] || $filterInputPrice[1];
		$itemData['title']  = ! empty( $itemData['title'] ) ? $itemData['title'] : esc_html__( 'Price Range', 'classified-listing' );
		$options            = [ 'name' => 'filter_price_range', 'allow_rest' => true ];
		Functions::print_html( $object->render_filter_item( $itemData, $options, $field_html ), true );
	}

	/**
	 * @param  array  $itemData
	 * @param  array  $filterData
	 * @param  AjaxFilter  $object
	 *
	 * @return void
	 */
	public static function ajax_filter_render_radius_filter( $itemData, $filterData, $object ) {
		$rs_data           = Options::radius_search_options();
		$geoAddress        = ! empty( $_GET['geo_address'] ) ? esc_attr( $_GET['geo_address'] )
			: ''; /* phpcs:ignore WordPress.Security.NonceVerification.Recommended */
		$centerLat         = ! empty( $_GET['center_lat'] ) ? esc_attr( $_GET['center_lat'] )
			: ''; /* phpcs:ignore WordPress.Security.NonceVerification.Recommended */
		$centerLng         = ! empty( $_GET['center_lng'] ) ? esc_attr( $_GET['center_lng'] )
			: ''; /* phpcs:ignore WordPress.Security.NonceVerification.Recommended */
		$distance          = absint( ! empty( $_GET['distance'] ) ? absint( $_GET['distance'] )
			: $rs_data['default_distance'] ); /* phpcs:ignore WordPress.Security.NonceVerification.Recommended */
		$field_html        = sprintf( '
                                    <div class="rtcl-radius-search-wrap">
                                        <div class="rtcl-geo-address-field">
                                            <input type="text" name="geo_address" autocomplete="off" value="%1$s" placeholder="%2$s" class="rtcl-form-control rtcl-geo-address-input" />
                                            <i class="rtcl-get-location rtcl-icon rtcl-icon-target"></i>
                                            <input type="hidden" class="latitude" name="center_lat" value="%3$s">
                                            <input type="hidden" class="longitude" name="center_lng" value="%4$s">
                                        </div>
                                        <div class="rtcl-radius-distance-slider-wrap">
							                <div class="rtcl-range-label"><div class="label-txt">%5$s (<span class="rtcl-range-value">%7$d</span> %6$s)</div></div>
							                <div class="rtcl-radius-distance-slider rtcl-noUiSlider" data-min="0" data-default="%8$d" data-current="%7$d" data-max="%9$d" data-step="5"></div>
							            </div>
							        </div>',
			$geoAddress,
			esc_html__( 'Select a location', 'classified-listing' ),
			$centerLat,
			$centerLng,
			esc_html__( 'Radius', 'classified-listing' ),
			in_array( $rs_data['units'],
				[
					'km',
					'kilometers',
				] ) ? esc_html__( 'km', 'classified-listing' ) : esc_html__( 'Miles', 'classified-listing' ),
			$distance,
			$rs_data['default_distance'],
			$rs_data['max_distance'],
		);
		$itemData['title'] = ! empty( $settings['title'] ) ? $settings['title'] : esc_html__( 'Radius Search', 'classified-listing' );

		$itemData['active'] = $centerLat && $centerLng && $distance;
		$options            = [ 'name' => 'filter_radius_filter', 'allow_rest' => true ];
		Functions::print_html( $object->render_filter_item( $itemData, $options, $field_html ), true );
	}

	public static function edit_account_wrapper_column_start() {
		echo '<div class="rtcl-form-group-wrap rtcl-edit-account-location">';
	}

	public static function edit_account_wrapper_column_end() {
		echo '</div>';
	}

	/**
	 * @param  int  $paymentId
	 * @param  Payment  $payment
	 */
	public static function payment_receipt_pricing_info( $paymentId, $payment ) {
		Functions::get_template( "checkout/pricing-info", compact( 'payment' ) );
	}

	/**
	 * @param  int  $paymentId
	 * @param  Payment  $payment
	 */
	public static function payment_receipt_billing_info( $paymentId, $payment ) {
		Functions::get_template( "checkout/billing-info", compact( 'payment' ) );
	}

	/**
	 * Display tax row on payment receipt.
	 *
	 * @param  Payment  $payment
	 */
	public static function payment_receipt_tax_info( $payment ) {
		if ( ! Functions::is_enable_tax() ) {
			return;
		}
		$tax_amount = floatval( get_post_meta( $payment->get_id(), '_tax_amount', true ) );
		if ( $tax_amount > 0 ) {
			?>
			<tr>
				<td class="text-right rtcl-vertical-middle">
					<?php esc_html_e( 'Tax ', 'classified-listing' ); ?>
				</td>
				<td>
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo Functions::get_payment_formatted_price_html( $tax_amount );
					?>
				</td>
			</tr>
			<?php
		}
	}

	public static function payment_receipt_popup_pricing_info( $paymentId, $payment ) {
		Functions::get_template( "myaccount/popup-pricing-info", compact( 'payment' ) );
	}

	/**
	 * @param  int  $paymentId
	 * @param  Payment  $payment
	 */
	public static function payment_receipt_payment_info( $paymentId, $payment ) {
		Functions::get_template( "checkout/payment-info", compact( 'payment' ) );
	}

	public static function payment_receipt_actions() {
		?>
		<div class="action-btn">
			<a href="<?php
			echo esc_url( Link::get_account_endpoint_url( "listings" ) ); ?>"
			   class="rtcl-btn"><?php
				esc_html_e( 'View all my listings', 'classified-listing' ); ?></a>
		</div>
		<?php
	}

	public static function payment_receipt_popup_actions( $order_id ) {
		?>
		<div class="action-btn">
			<a href="<?php
			echo esc_url( Link::get_checkout_endpoint_url( "payment-receipt", $order_id ) ); ?>"
			   class="rtcl-btn">
				<?php
				esc_html_e( 'View details', 'classified-listing' ); ?>
			</a>
		</div>
		<?php
	}

	/**
	 * @param  int  $paymentId
	 * @param  Payment  $payment
	 */
	public static function offline_payment_instruction( $paymentId, $payment ) {
		if ( $payment->get_status() === "rtcl-pending" ) {
			Functions::the_offline_payment_instructions();
		}
	}

	/**
	 * @param  Listing  $listing
	 */
	public static function seller_website( $listing ) {
		if ( is_a( $listing, Listing::class ) && $website = get_post_meta( $listing->get_id(), 'website', true ) ) {
			?>
			<div class='rtcl-website rtcl-list-group-item'>
				<a class="rtcl-website-link rtcl-btn rtcl-btn-primary" href="<?php
				echo esc_url( $website ); ?>"
				   target="_blank"<?php
				echo Functions::is_external( $website ) ? ' rel="nofollow"' : ''; ?>><span
						class='rtcl-icon rtcl-icon-globe text-white'></span><?php
					esc_html_e( "Visit Website", "classified-listing" ) ?>
				</a>
			</div>
			<?php
		}
	}

	/**
	 * @param  Listing  $listing
	 */
	public static function seller_email( $listing ) {
		if ( is_a( $listing, Listing::class ) && Functions::get_option_item( 'rtcl_single_listing_settings', 'has_contact_form', false, 'checkbox' )
			 && $email = get_post_meta( $listing->get_id(), 'email', true )
		) {
			if ( is_user_logged_in() && get_current_user_id() === $listing->get_author_id() ) {
				return;
			}
			?>
			<div class='rtcl-do-email rtcl-list-group-item'>
				<div class='media'>
					<span class='rtcl-icon rtcl-icon-mail mr-2'></span>
					<div class='media-body'>
						<a class="rtcl-do-email-link" href='#'>
							<span><?php
								echo esc_html( Text::get_single_listing_email_button_text() ); ?></span>
						</a>
					</div>
				</div>
				<?php
				$listing->email_to_seller_form(); ?>
			</div>
			<?php
		}
	}

	/**
	 * @param  Listing  $listing
	 */
	public static function seller_phone_whatsapp_number( $listing ) {
		if ( is_a( $listing, Listing::class ) ) {
			$phone           = get_post_meta( $listing->get_id(), 'phone', true );
			$whatsapp_number = get_post_meta( $listing->get_id(), '_rtcl_whatsapp_number', true );
			if ( $phone || ( $whatsapp_number && ! Functions::is_field_disabled( 'whatsapp_number' ) ) ) {
				$mobileClass   = wp_is_mobile() ? " rtcl-mobile" : null;
				$phone_options = [];
				if ( $phone && Functions::check_visibility( $listing->get_author_id(), 'phone' ) ) {
					$phone_options = [
						'safe_phone'   => mb_substr( $phone, 0, mb_strlen( $phone ) - 3 ) . apply_filters( 'rtcl_phone_number_placeholder', 'XXX' ),
						'phone_hidden' => mb_substr( $phone, - 3 ),
					];
				}
				if ( $whatsapp_number && Functions::check_visibility( $listing->get_author_id(), 'whatsapp' ) && ! Functions::is_field_disabled( 'whatsapp_number' ) ) {
					$phone_options['safe_whatsapp_number'] = mb_substr( $whatsapp_number, 0, mb_strlen( $whatsapp_number ) - 3 )
															 . apply_filters( 'rtcl_phone_number_placeholder', 'XXX' );
					$phone_options['whatsapp_hidden']      = mb_substr( $whatsapp_number, - 3 );
				}
				$phone_options = apply_filters( 'rtcl_phone_number_options', $phone_options, [
					'phone'           => $phone,
					'whatsapp_number' => $whatsapp_number,
				] )
				?>
				<div tabindex="0" class='rtcl-list-group-item reveal-phone<?php
				echo esc_attr( $mobileClass ); ?>'
					 data-options="<?php
					 // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					 echo htmlspecialchars( wp_json_encode( $phone_options ) ); ?>"
					 data-id="<?php
					 echo $listing->get_id(); ?>">
					<div class='media'>
						<span class='rtcl-icon rtcl-icon-phone mr-2'></span>
						<div class='media-body'>
							<span><?php
								esc_html_e( "Contact Number", "classified-listing" ); ?></span>
							<div class='numbers'><?php
								if ( $phone && Functions::check_visibility( $listing->get_author_id(), 'phone' ) ) {
									echo esc_html( $phone_options['safe_phone'] );
								} elseif ( $whatsapp_number && Functions::check_visibility( $listing->get_author_id(), 'whatsapp' ) ) {
									echo esc_html( $phone_options['safe_whatsapp_number'] );
								} ?></div>
							<small
								class='text-muted'><?php
								esc_html_e( "Click to reveal phone number", "classified-listing" ) ?></small>
						</div>
					</div>
				</div>
				<?php
			}
		}
	}

	/**
	 * @param  Listing  $listing
	 */
	public static function author_information( $listing ) {
		if ( is_a( $listing, Listing::class ) && $listing->can_show_user() ) {
			?>
			<div class='rtcl-list-group-item rtcl-listing-author-info'>
				<div class='media'>
					<?php
					$pp_id = absint( get_user_meta( $listing->get_owner_id(), '_rtcl_pp_id', true ) );
					if ( $listing->can_add_user_link() ): ?>
						<a href="<?php
						echo esc_url( $listing->get_the_author_url() ); ?>" aria-label="Post Author"><?php
							echo( $pp_id ? wp_get_attachment_image( $pp_id, [
								40,
								40,
							] )
								: get_avatar( $listing->get_author_id(), 40 ) ); ?></a>
					<?php
					else:
						echo( $pp_id ? wp_get_attachment_image( $pp_id, [
							40,
							40,
						] ) : get_avatar( $listing->get_author_id(), 40 ) );
					endif;
					?>
					<div class='media-body'>
						<a class="rtcl-listing-author"
						   href="<?php
						   echo esc_url( $listing->get_the_author_url() ); ?>"><?php
							$listing->the_author(); ?></a>
						<div class="rtcl-author-badge">
							<?php
							do_action( 'rtcl_listing_author_badges', $listing ); ?>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * @param  Listing  $listing
	 */
	public static function seller_location( $listing ) {
		if ( is_a( $listing, Listing::class ) && $location = $listing->user_contact_location_at_single() ) {
			?>
			<div class='rtcl-list-group-item'>
				<div class='media'>
					<span class='rtcl-icon rtcl-icon-location mr-2'></span>
					<div class='media-body'><span><?php
							esc_html_e( "Location", "classified-listing" ) ?></span>
						<div
							class='locations'><?php
							echo implode( '<span class="rtcl-delimiter">,</span> ',
								$location ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					</div>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * @param  Listing  $listing
	 */
	public static function seller_telegram( $listing ) {
		if ( is_a( $listing, Listing::class ) ) {
			$telegram = get_post_meta( $listing->get_id(), '_rtcl_telegram', true );
			/* translators: related something */
			$message = sprintf( esc_html__( "Need to discuss something related to '%1\$s' from %2\$s", "classified-listing" ),
				$listing->get_the_title(),
				get_permalink( $listing->get_id() ) );
			if ( ! empty( $telegram ) ) {
				?>
				<div class='list-group-item'>
					<div class='media'>
						<span class='rtcl-icon rtcl-icon-telegram mr-2'></span>
						<div class='media-body'>
							<a class="rtcl-telegram-message" target="_blank"
							   href="https://t.me/<?php
							   echo esc_attr( $telegram ); ?>/?text=<?php
							   echo esc_attr( $message ); ?>">
								<span><?php
									esc_html_e( "Message to Telegram", "classified-listing" ) ?></span>
							</a>
						</div>
					</div>
				</div>
				<?php
			}
		}
	}

	/**
	 * @param  Listing  $listing
	 */
	public static function my_listing_promotion_button( $listing ) {
		if ( is_a( $listing, Listing::class ) && ! Functions::is_payment_disabled() ) {
			?>
			<a href="<?php
			echo esc_url( Link::get_checkout_endpoint_url( "submission", $listing->get_id() ) ); ?>"
			   class="rtcl-promote-btn">
				<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path
						d="M8.39414 0.505005C8.06856 0.179322 7.63555 5.70138e-07 7.17507 5.70138e-07C6.7146 5.70138e-07 6.28159 0.179322 5.95601 0.504883C5.63031 0.830567 5.45098 1.26343 5.45098 1.72388C5.45098 2.07434 5.55511 2.40857 5.74824 2.6919L2.39148 10.911C2.18273 10.8232 1.95652 10.7771 1.72323 10.7771C1.26287 10.7771 0.829866 10.9563 0.504164 11.2819C-0.167994 11.9541 -0.168116 13.0477 0.504164 13.72L2.27941 15.4951C2.60499 15.8207 3.038 16 3.49847 16C3.95907 16 4.39196 15.8207 4.71754 15.4951C5.04312 15.1696 5.22245 14.7366 5.22257 14.2761C5.22257 14.0428 5.1763 13.8167 5.08853 13.6079L6.79578 12.9108L7.46305 13.578C7.89081 14.0057 8.55662 14.2462 9.19545 14.2462C9.48453 14.2462 9.76812 14.197 10.0215 14.0935L11.6579 13.4252C12.1103 13.2404 12.4173 12.8988 12.5001 12.4879C12.5829 12.0771 12.4322 11.6432 12.0865 11.2976L11.6977 10.9088L13.3079 10.2512C13.5913 10.4443 13.9256 10.5485 14.2761 10.5485C14.7364 10.5485 15.1695 10.3691 15.4951 10.0436C16.1673 9.37134 16.1673 8.27759 15.4951 7.60547L8.39414 0.505005ZM10.6933 10.3063C10.6918 10.3069 10.6902 10.3076 10.6886 10.3082L6.73279 11.9238C6.7312 11.9244 6.72961 11.925 6.72815 11.9257L4.49719 12.8368L3.16264 11.5023L6.4547 3.44165L12.5581 9.5448L10.6933 10.3063ZM4.05454 14.8323C3.90609 14.9807 3.70857 15.0625 3.49847 15.0625C3.28838 15.0625 3.09086 14.9807 2.94229 14.8322L1.16704 13.0571C0.860385 12.7505 0.860385 12.2515 1.16704 11.9448C1.31561 11.7963 1.51313 11.7145 1.72323 11.7145C1.93332 11.7145 2.13084 11.7964 2.27941 11.9449L4.05454 13.72C4.2031 13.8685 4.28502 14.066 4.28502 14.276C4.2849 14.4862 4.2031 14.6836 4.05454 14.8323ZM11.4236 11.9606C11.5434 12.0803 11.6007 12.2051 11.581 12.3027C11.5613 12.4005 11.4601 12.4933 11.3034 12.5573L9.66704 13.2256C9.20156 13.4156 8.48154 13.2705 8.12605 12.9152L7.73712 12.5264L10.7562 11.2933L11.4236 11.9606ZM14.8321 9.38062C14.6836 9.52917 14.4862 9.61096 14.2762 9.61096C14.0661 9.61096 13.8683 9.52905 13.7198 9.38062L6.61889 2.28003C6.47032 2.13159 6.38853 1.93408 6.38853 1.724C6.38853 1.51392 6.47032 1.31641 6.61889 1.16785C6.76746 1.01929 6.96498 0.9375 7.17507 0.9375C7.38517 0.9375 7.58269 1.01929 7.73126 1.16785L14.8321 8.26831C15.1388 8.57507 15.1388 9.07397 14.8321 9.38062Z"
						fill="#646464"/>
					<path
						d="M13.3884 3.08032C13.5082 3.08032 13.6282 3.03455 13.7198 2.94299L15.8626 0.800293C16.0457 0.617188 16.0457 0.320435 15.8626 0.13733C15.6795 -0.0457759 15.3827 -0.0457759 15.1997 0.13733L13.0568 2.28003C12.8738 2.46313 12.8738 2.75989 13.0568 2.94299C13.1484 3.03455 13.2684 3.08032 13.3884 3.08032Z"
						fill="#646464"/>
					<path
						d="M11.258 1.66016C11.378 1.66016 11.498 1.61438 11.5896 1.52283L12.3123 0.800171C12.4954 0.617187 12.4954 0.320435 12.3123 0.137329C12.1293 -0.0457764 11.8324 -0.0457764 11.6493 0.137329L10.9266 0.859985C10.7436 1.04297 10.7436 1.33972 10.9266 1.52283C11.0182 1.61438 11.1382 1.66016 11.258 1.66016Z"
						fill="#646464"/>
					<path
						d="M15.1998 3.6875L14.4771 4.41028C14.294 4.59338 14.294 4.89014 14.4771 5.07324C14.5686 5.16479 14.6885 5.21045 14.8085 5.21045C14.9285 5.21045 15.0485 5.16467 15.14 5.07324L15.8627 4.35046C16.0458 4.16736 16.0458 3.87061 15.8627 3.6875C15.6795 3.50452 15.3828 3.50452 15.1998 3.6875Z"
						fill="#646464"/>
				</svg>
				<span class="rtcl-tooltip"><?php
					esc_html_e( 'Promote', 'classified-listing' ) ?></span>
			</a>
			<?php
		}
	}


	/**
	 * @param  Listing  $listing
	 */
	public static function my_listing_renew_button( Listing $listing ) {
		if ( ! $listing->isExpired() ) {
			return;
		}

		if ( ! apply_filters( 'rtcl_enable_renew_button', Functions::is_enable_renew(), $listing ) ) {
			return;
		}

		?>
		<a href="#" data-id="<?php
		echo absint( $listing->get_id() ) ?>" class="rtcl-renew-btn">
			<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
				<g clip-path="url(#clip0_1407_118)">
					<path
						d="M15.8 3.23726C15.6314 3.15463 15.4349 3.24061 15.361 3.4291L14.5716 5.44388C13.6134 2.36416 10.9872 0.224121 7.99972 0.224121C4.68492 0.224121 1.81439 2.85861 1.17432 6.48835C1.13874 6.69023 1.25628 6.88611 1.43692 6.92588C1.45869 6.9307 1.48036 6.93301 1.50172 6.93301C1.65772 6.93296 1.79709 6.80995 1.82841 6.63242C2.40706 3.35093 5.0025 0.969198 7.99972 0.969198C10.8039 0.969198 13.2563 3.05376 14.0345 6.00978L12.0237 5.31336C11.8477 5.25235 11.6611 5.36239 11.6066 5.55893C11.5521 5.75545 11.6505 5.96416 11.8263 6.02503L14.3969 6.91529L14.3979 6.91564L14.3994 6.91611C14.4005 6.91651 14.4015 6.91661 14.4026 6.91696C14.4143 6.92093 14.4262 6.92404 14.4381 6.92649C14.4416 6.92722 14.445 6.92771 14.4485 6.9283C14.4585 6.93002 14.4685 6.93119 14.4785 6.93183C14.4821 6.93204 14.4857 6.93232 14.4893 6.93244C14.4922 6.93251 14.4951 6.93291 14.4979 6.93291C14.5024 6.93291 14.5069 6.93204 14.5114 6.93183C14.5199 6.93145 14.5285 6.93089 14.537 6.92973C14.5435 6.92886 14.5499 6.92771 14.5564 6.92642C14.565 6.92472 14.5735 6.92263 14.582 6.92018C14.5881 6.91839 14.5942 6.91651 14.6001 6.9143C14.6087 6.91124 14.617 6.90764 14.6253 6.90383C14.6311 6.90117 14.6368 6.89861 14.6424 6.89557C14.6505 6.89126 14.6582 6.8863 14.666 6.88124C14.6714 6.87769 14.677 6.87428 14.6823 6.87037C14.6895 6.86501 14.6964 6.85889 14.7033 6.85286C14.7085 6.84828 14.714 6.84402 14.719 6.83905C14.7196 6.83842 14.7203 6.83795 14.7209 6.83731C14.7277 6.83049 14.7337 6.82293 14.7399 6.81559C14.7437 6.81117 14.7478 6.80715 14.7513 6.80251C14.7613 6.78957 14.7702 6.77585 14.7783 6.76171C14.7808 6.75736 14.7828 6.75258 14.7852 6.74813C14.7909 6.73733 14.7962 6.72644 14.8009 6.7151C14.8017 6.71324 14.8027 6.71162 14.8034 6.70973L15.9716 3.72785C16.0455 3.53948 15.9686 3.31981 15.8 3.23726Z"
						fill="#646464"/>
					<path
						d="M14.5628 9.07404C14.3823 9.03411 14.2069 9.16564 14.1713 9.36752C13.5926 12.649 10.9972 15.0307 7.99998 15.0307C5.19573 15.0307 2.74333 12.9462 1.96517 9.99013L3.97602 10.6866C4.15187 10.7475 4.33859 10.6375 4.39305 10.441C4.44756 10.2445 4.34918 10.0358 4.17335 9.97488L1.60364 9.08493L1.60297 9.0847L1.60026 9.08376C1.5989 9.08328 1.59752 9.0831 1.59616 9.08265C1.58482 9.07886 1.57341 9.07585 1.56197 9.0735C1.55872 9.07281 1.55544 9.0723 1.55219 9.07171C1.54159 9.0699 1.53103 9.06865 1.52043 9.06797C1.51769 9.06778 1.51495 9.06747 1.5122 9.0674C1.49883 9.06691 1.48549 9.06719 1.47226 9.06851L1.47202 9.06853C1.45936 9.06983 1.44683 9.07211 1.43444 9.07498C1.43085 9.0758 1.42733 9.07679 1.42378 9.07778C1.41443 9.08032 1.40523 9.08326 1.39615 9.08667C1.39263 9.08799 1.38915 9.08917 1.38566 9.0906C1.37367 9.09559 1.36189 9.10112 1.35045 9.10761C1.34957 9.10811 1.34872 9.10877 1.34784 9.10928C1.33715 9.1155 1.32682 9.12248 1.31673 9.12999C1.31389 9.13211 1.31111 9.13437 1.30834 9.13658C1.2996 9.1435 1.29117 9.15095 1.28305 9.15886C1.28166 9.16023 1.2801 9.16128 1.27875 9.16265C1.27811 9.16328 1.27756 9.16408 1.27692 9.16472C1.26693 9.17491 1.25739 9.18568 1.24848 9.19731C1.24804 9.19787 1.24771 9.19846 1.24727 9.19905C1.23938 9.20945 1.23207 9.22053 1.22516 9.23204C1.22299 9.23566 1.22099 9.23933 1.21893 9.24305C1.21409 9.25175 1.20959 9.26072 1.20535 9.26994C1.20336 9.27434 1.20132 9.27865 1.19946 9.28312C1.19845 9.28554 1.19723 9.2878 1.19628 9.29023L0.0280893 12.272C-0.0457798 12.4605 0.0310682 12.6802 0.199728 12.7627C0.243263 12.784 0.288594 12.7941 0.333246 12.7941C0.461587 12.7941 0.583968 12.7107 0.638743 12.5708L1.42808 10.5561C2.38627 13.6358 5.01251 15.7758 7.99998 15.7758C11.3148 15.7758 14.1853 13.1413 14.8254 9.51161C14.861 9.30975 14.7434 9.11383 14.5628 9.07404Z"
						fill="#646464"/>
				</g>
				<defs>
					<clipPath id="clip0_1407_118">
						<rect width="16" height="16" fill="white"/>
					</clipPath>
				</defs>
			</svg>
			<span class="rtcl-tooltip"><?php
				esc_html_e( 'Renew', 'classified-listing' ); ?></span>
		</a>
		<?php
	}

	/**
	 * @param  Listing  $listing
	 */
	public static function my_listing_edit_button( $listing ) {
		if ( is_a( $listing, Listing::class ) && Functions::current_user_can( 'edit_' . rtcl()->post_type, $listing->get_id() ) ) {
			?>
			<a href="<?php
			echo esc_url( Link::get_listing_edit_page_link( $listing->get_id() ) ); ?>" class=""
			   data-id="<?php
			   echo esc_attr( $listing->get_id() ) ?>">
				<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path
						d="M11.5 16H1.83337C0.822021 16 0 15.1779 0 14.1665V4.49906C0 3.48762 0.822021 2.66553 1.83337 2.66553H7.5C7.776 2.66553 8 2.88955 8 3.16557C8 3.4416 7.776 3.66561 7.5 3.66561H1.83337C1.37402 3.66561 1 4.03967 1 4.49906V14.1665C1 14.6259 1.37402 14.9999 1.83337 14.9999H11.5C11.9594 14.9999 12.3334 14.6259 12.3334 14.1665V8.49938C12.3334 8.22336 12.5574 7.99934 12.8334 7.99934C13.1094 7.99934 13.3334 8.22275 13.3334 8.49938V14.1665C13.3334 15.1779 12.5114 16 11.5 16Z"
						fill="#646464"/>
					<path
						d="M5.84802 10.6515C5.71667 10.6515 5.58862 10.5995 5.49463 10.5049C5.37598 10.3869 5.32532 10.2168 5.35803 10.0535L5.82935 7.69597C5.84863 7.59868 5.89661 7.50992 5.96594 7.44058L12.8707 0.536085C13.5853 -0.178695 14.748 -0.178695 15.4634 0.536085C15.8093 0.882061 16 1.34218 16 1.83221C16 2.32224 15.8093 2.78224 15.4626 3.12834L8.55872 10.0336C8.48938 10.1035 8.40002 10.1509 8.30334 10.1702L5.94666 10.6415C5.91394 10.6482 5.88062 10.6515 5.84802 10.6515ZM6.78003 8.04073L6.48596 9.51412L7.95862 9.21941L14.756 2.42162C14.9133 2.26352 15 2.05489 15 1.83221C15 1.60954 14.9133 1.40078 14.756 1.24281C14.432 0.918075 13.9033 0.918075 13.5774 1.24281L6.78003 8.04073Z"
						fill="#646464"/>
					<path
						d="M14.1666 4.21839C14.0387 4.21839 13.9106 4.16968 13.8134 4.07165L11.928 2.18551C11.7327 1.99018 11.7327 1.67351 11.928 1.47818C12.1233 1.28285 12.4399 1.28285 12.6354 1.47818L14.5206 3.36432C14.7159 3.55965 14.7159 3.87633 14.5206 4.07165C14.4226 4.16907 14.2947 4.21839 14.1666 4.21839Z"
						fill="#646464"/>
				</svg>
				<span class="rtcl-tooltip"><?php
					esc_html_e( 'Edit', 'classified-listing' ); ?></span>
			</a>
			<?php
		}
	}

	/**
	 * @param  Listing  $listing
	 */
	public static function my_listing_delete_button( $listing ) {
		if ( is_a( $listing, Listing::class ) && Functions::current_user_can( 'delete_' . rtcl()->post_type, $listing->get_id() ) ) {
			?>
			<a href="#" class="rtcl-delete-listing"
			   data-id="<?php
			   echo esc_attr( $listing->get_id() ) ?>"
			   title="<?php
			   esc_attr_e( 'Delete', 'classified-listing' ) ?>">
				<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path
						d="M6.4 2.73171H9.6C9.6 2.31771 9.43143 1.92067 9.13137 1.62793C8.83131 1.33519 8.42435 1.17073 8 1.17073C7.57565 1.17073 7.16869 1.33519 6.86863 1.62793C6.56857 1.92067 6.4 2.31771 6.4 2.73171ZM5.2 2.73171C5.2 2.37297 5.27242 2.01775 5.41314 1.68633C5.55385 1.3549 5.7601 1.05376 6.0201 0.800099C6.28011 0.546436 6.58878 0.34522 6.92849 0.207939C7.2682 0.0706577 7.6323 0 8 0C8.3677 0 8.7318 0.0706577 9.07151 0.207939C9.41123 0.34522 9.7199 0.546436 9.9799 0.800099C10.2399 1.05376 10.4461 1.3549 10.5869 1.68633C10.7276 2.01775 10.8 2.37297 10.8 2.73171H15.4C15.5591 2.73171 15.7117 2.79338 15.8243 2.90316C15.9368 3.01293 16 3.16182 16 3.31707C16 3.47232 15.9368 3.62121 15.8243 3.73099C15.7117 3.84077 15.5591 3.90244 15.4 3.90244H14.344L13.408 13.3549C13.3362 14.0792 12.9904 14.7514 12.4381 15.2404C11.8859 15.7295 11.1666 16.0003 10.4208 16H5.5792C4.83349 16.0001 4.11448 15.7292 3.56236 15.2402C3.01024 14.7512 2.66459 14.0791 2.5928 13.3549L1.656 3.90244H0.6C0.44087 3.90244 0.288258 3.84077 0.175736 3.73099C0.063214 3.62121 0 3.47232 0 3.31707C0 3.16182 0.063214 3.01293 0.175736 2.90316C0.288258 2.79338 0.44087 2.73171 0.6 2.73171H5.2ZM6.8 6.43902C6.8 6.28378 6.73679 6.13489 6.62426 6.02511C6.51174 5.91533 6.35913 5.85366 6.2 5.85366C6.04087 5.85366 5.88826 5.91533 5.77574 6.02511C5.66321 6.13489 5.6 6.28378 5.6 6.43902V12.2927C5.6 12.4479 5.66321 12.5968 5.77574 12.7066C5.88826 12.8164 6.04087 12.878 6.2 12.878C6.35913 12.878 6.51174 12.8164 6.62426 12.7066C6.73679 12.5968 6.8 12.4479 6.8 12.2927V6.43902ZM9.8 5.85366C9.95913 5.85366 10.1117 5.91533 10.2243 6.02511C10.3368 6.13489 10.4 6.28378 10.4 6.43902V12.2927C10.4 12.4479 10.3368 12.5968 10.2243 12.7066C10.1117 12.8164 9.95913 12.878 9.8 12.878C9.64087 12.878 9.48826 12.8164 9.37574 12.7066C9.26321 12.5968 9.2 12.4479 9.2 12.2927V6.43902C9.2 6.28378 9.26321 6.13489 9.37574 6.02511C9.48826 5.91533 9.64087 5.85366 9.8 5.85366ZM3.7872 13.2425C3.83035 13.677 4.0378 14.0802 4.36909 14.3735C4.70039 14.6669 5.1318 14.8294 5.5792 14.8293H10.4208C10.8682 14.8294 11.2996 14.6669 11.6309 14.3735C11.9622 14.0802 12.1697 13.677 12.2128 13.2425L13.1392 3.90244H2.8608L3.7872 13.2425Z"
						fill="#646464"/>
				</svg>
				<span class="rtcl-tooltip"><?php
					esc_html_e( 'Delete', 'classified-listing' ) ?></span>
			</a>
			<?php
		}
	}

	/**
	 * @param  Listing  $listing
	 */
	public static function add_favourite_button( $listing ) {
		if ( Functions::is_enable_favourite() ) { ?>
			<div class="rtcl-tooltip-wrapper rtcl-btn"
				 data-listing_id="<?php
				 echo absint( $listing->get_id() ) ?>">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo Functions::get_favourites_link( $listing->get_id() ) ?>
			</div>
			<?php
		}
	}

	/**
	 * @param  Filter  $object
	 */
	public static function widget_filter_form_category_item( $object ) {
		Functions::print_html( $object->get_category_filter(), true );
	}

	/**
	 * @param  Filter  $object
	 */
	public static function widget_filter_form_tag_item( $object ) {
		Functions::print_html( $object->get_tag_filter(), true );
	}

	/**
	 * @param  Filter  $object
	 */
	public static function widget_filter_form_location_item( $object ) {
		Functions::print_html( $object->get_location_filter(), true );
	}

	/**
	 * @param  Filter  $object
	 */
	public static function widget_filter_form_radius_item( $object ) {
		Functions::print_html( $object->get_radius_search(), true );
	}

	/**
	 * @param  Filter  $object
	 */
	public static function widget_filter_form_price_item( $object ) {
		Functions::print_html( $object->get_price_filter(), true );
	}

	/**
	 * @param  Filter  $object
	 */
	public static function widget_filter_form_ad_type_item( $object ) {
		Functions::print_html( $object->get_ad_type_filter(), true );
	}

	public static function edit_account_form_submit_button() {
		?>
		<div class="rtcl-form-group">
			<div class="rtcl-field-col">
				<input type="submit" name="submit" class="rtcl-btn"
					   value="<?php
					   esc_attr_e( 'Update Account', 'classified-listing' ); ?>"/>
			</div>
		</div>
		<?php
	}

	public static function edit_account_form_social_profile_field() {
		?>
		<div class="rtcl-form-group rtcl-social-wrap-row">
			<label for="rtcl-social" class="rtcl-field-label">
				<?php
				esc_html_e( 'Social Profile', 'classified-listing' ); ?>
			</label>
			<div class="rtcl-field-col">
				<?php
				$social_options = Options::get_social_profiles_list();
				$social_media   = get_current_user_id() ? Functions::get_user_social_profile( get_current_user_id() ) : [];
				foreach ( $social_options as $key => $social_option ) {
					echo sprintf(
						'<input type="url" name="social_media[%1$s]" id="rtcl-account-social-%1$s" value="%2$s" placeholder="%3$s" class="rtcl-form-control"/>',
						esc_attr( $key ),
						esc_url( isset( $social_media[ $key ] ) ? $social_media[ $key ] : '' ),
						esc_html( $social_option ),
					);
				} ?>
			</div>
		</div>
		<?php
	}

	public static function edit_account_form_location_field() {
		$user_id        = get_current_user_id();
		$location_id    = $sub_location_id = 0;
		$user_locations = (array) get_user_meta( $user_id, '_rtcl_location', true );
		$zipcode        = get_user_meta( $user_id, '_rtcl_zipcode', true );
		$address        = get_user_meta( $user_id, '_rtcl_address', true );
		$state_text     = Text::location_level_first();
		$city_text      = Text::location_level_second();
		$town_text      = Text::location_level_third(); ?>
		<div class="rtcl-form-group">
			<div class="rtcl-field-col" id="rtcl-location-row">
				<label for="rtcl-location" class="rtcl-field-label">
					<?php
					echo esc_html( $state_text ); ?>
					<span class="require-star">*</span>
				</label>
				<select id="rtcl-location" name="location"
						class="rtcl-select2 rtcl-select rtcl-form-control rtcl-map-field" required>
					<option value="">--<?php
						esc_html_e( 'Select state', 'classified-listing' ) ?>--
					</option>
					<?php
					$locations = Functions::get_one_level_locations();
					if ( ! empty( $locations ) ) {
						foreach ( $locations as $location ) {
							$slt = '';
							if ( in_array( $location->term_id, $user_locations ) ) {
								$location_id = $location->term_id;
								$slt         = " selected";
							}
							echo "<option value='" . esc_attr( $location->term_id ) . "'" . esc_attr( $slt ) . ">" .
								 // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								 $location->name . "</option>";
						}
					} ?>
				</select>
			</div>
			<?php
			$sub_locations = [];
			if ( $location_id ) {
				$sub_locations = Functions::get_one_level_locations( $location_id );
			} ?>
			<div class="rtcl-field-col<?php
			echo empty( $sub_locations ) ? ' rtcl-hide' : ''; ?>" id="sub-location-row">
				<label class="rtcl-field-label" for='rtcl-sub-location'><?php
					echo esc_html( $city_text ); ?>
					<span class="require-star">*</span>
				</label>
				<select id="rtcl-sub-location" name="sub_location"
						class="rtcl-select2 rtcl-select rtcl-form-control rtcl-map-field" required>
					<option value="">--<?php
						esc_html_e( 'Select location', 'classified-listing' ) ?>--
					</option>
					<?php
					if ( ! empty( $sub_locations ) ) {
						foreach ( $sub_locations as $location ) {
							$slt = '';
							if ( in_array( $location->term_id, $user_locations ) ) {
								$sub_location_id = $location->term_id;
								$slt             = " selected";
							}
							echo "<option value='" . esc_attr( $location->term_id ) . "'" . esc_attr( $slt ) . ">" .
								 // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								 $location->name . "</option>";
						}
					} ?>
				</select>
			</div>
			<?php
			$sub_sub_locations = [];
			if ( $sub_location_id ) {
				$sub_sub_locations = Functions::get_one_level_locations( $sub_location_id );
			} ?>
			<div class="rtcl-field-col<?php
			echo empty( $sub_sub_locations ) ? ' rtcl-hide' : ''; ?>"
				 id="sub-sub-location-row">
				<label for='rtcl-sub-sub-location' class="rtcl-field-label">
					<?php
					echo esc_html( $town_text ); ?>
					<span class="require-star">*</span>
				</label>
				<select id="rtcl-sub-sub-location" name="sub_sub_location"
						class="rtcl-select2 rtcl-select rtcl-form-control rtcl-map-field" required>
					<option value="">--<?php
						esc_html_e( 'Select location', 'classified-listing' ) ?>--
					</option>
					<?php
					if ( ! empty( $sub_sub_locations ) ) {
						foreach ( $sub_sub_locations as $location ) {
							$slt = '';
							if ( in_array( $location->term_id, $user_locations ) ) {
								$slt = " selected";
							}
							echo "<option value='" . esc_attr( $location->term_id ) . "'" . esc_attr( $slt ) . ">" .
								 // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								 $location->name . "</option>";
						}
					} ?>
				</select>
			</div>
			<?php if ( apply_filters( 'rtcl_myaccount_enable_zipcode_field', true ) ) { ?>
				<div class="rtcl-field-col">
					<label for="rtcl-zipcode"
						   class="rtcl-field-label"><?php
						esc_html_e( "Zip Code", "classified-listing" ) ?></label>
					<input type="text" name="zipcode" value="<?php
					echo esc_attr( $zipcode ); ?>"
						   class="rtcl-map-field rtcl-form-control" id="rtcl-zipcode"/>
				</div>
			<?php } ?>
			<?php if ( apply_filters( 'rtcl_myaccount_enable_address_field', true ) ) { ?>
				<div class="rtcl-field-col">
					<label for="rtcl-address"
						   class="rtcl-field-label"><?php
						esc_html_e( "Address", "classified-listing" ) ?></label>
					<textarea name="address" rows="3" class="rtcl-map-field rtcl-form-control"
							  id="rtcl-address"><?php
						echo esc_textarea( $address ); ?></textarea>
				</div>
			<?php } ?>
		</div>
		<?php
	}


	public static function edit_account_form_geo_location() {
		$user_id     = get_current_user_id();
		$geo_address = get_user_meta( $user_id, '_rtcl_geo_address', true ); ?>
		<div class="rtcl-form-group">
			<label class="rtcl-field-label" for="rtcl-geo-address">
				<?php
				esc_html_e( "Location", "classified-listing" ) ?>
			</label>
			<div class="rtcl-field-col">
				<div class="rtcl-geo-address-field">
					<input type="text" name="rtcl_geo_address" autocomplete="off"
						   value="<?php
						   echo esc_attr( $geo_address ) ?>"
						   id="rtcl-geo-address"
						   placeholder="<?php
						   esc_attr_e( "Select a location", "classified-listing" ) ?>"
						   class="rtcl-form-control rtcl-geo-address-input rtcl_geo_address_input"/>
					<i class="rtcl-get-location rtcl-icon rtcl-icon-target" id="rtcl-geo-loc-form"></i>
				</div>
			</div>
		</div>
		<?php
	}

	public static function edit_account_map_field() {
		$user_id   = get_current_user_id();
		$address   = get_user_meta( $user_id, '_rtcl_address', true );
		$latitude  = get_user_meta( $user_id, '_rtcl_latitude', true );
		$longitude = get_user_meta( $user_id, '_rtcl_longitude', true ); ?>
		<div class="rtcl-form-group">
			<label for="rtcl-map" class="rtcl-field-label">
				<?php
				esc_html_e( 'Map', 'classified-listing' ); ?>
			</label>
			<div class="rtcl-field-col">
				<div class="rtcl-map-wrap">
					<div class="rtcl-map" data-type="input">
						<div class="marker" data-latitude="<?php
						echo esc_attr( $latitude ); ?>"
							 data-longitude="<?php
							 echo esc_attr( $longitude ); ?>"
							 data-address="<?php
							 echo esc_attr( $address ); ?>"><?php
							echo esc_html( $address ); ?></div>
					</div>
				</div>
			</div>
		</div>
		<!-- Map Hidden field-->
		<input type="hidden" name="latitude" value="<?php
		echo esc_attr( $latitude ); ?>" id="rtcl-latitude"/>
		<input type="hidden" name="longitude" value="<?php
		echo esc_attr( $longitude ); ?>" id="rtcl-longitude"/>
		<?php
	}

	/**
	 * @param  Listing  $listing
	 */
	public static function listing_featured_badge( $listing ) {
		if ( ! $listing->is_featured() ) {
			return;
		}
		$display_option    = Functions::get_display_options();
		$can_show          = apply_filters( 'rtcl_listing_can_show_featured_badge', true, $listing );
		$can_show_settings = in_array( 'featured', $display_option );

		$can_show_settings = apply_filters( 'rtcl_listing_can_show_featured_badge_settings', $can_show_settings );

		if ( ! $can_show || ! $can_show_settings ) {
			return;
		}
		$label = Functions::get_option_item( 'rtcl_general_listing_label_settings', 'listing_featured_label' );
		$label = $label ?: esc_html__( "Featured", "classified-listing" );
		echo '<span class="badge rtcl-badge-featured">' . esc_html( $label ) . '</span>';
	}

	/**
	 * @param  Listing  $listing
	 */
	public static function listing_new_badge( $listing ) {
		$can_show = apply_filters( 'rtcl_listing_can_show_new_badge', true, $listing );
		if ( ! $can_show || ! $listing->is_new() ) {
			return;
		}
		$display_option    = Functions::get_display_options();
		$can_show_settings = in_array( 'new', $display_option );
		$can_show_settings = apply_filters( 'rtcl_listing_can_show_new_badge_settings', $can_show_settings );
		if ( ! $can_show_settings ) {
			return;
		}

		$label = Functions::get_option_item( 'rtcl_general_listing_label_settings', 'new_listing_label' );
		$label = $label ?: esc_html__( "New", "classified-listing" );
		echo '<span class="badge rtcl-badge-new">' . esc_html( $label ) . '</span>';
	}

	/**
	 * @param  int  $post_id
	 *
	 * @throws \Exception
	 */
	public static function listing_category( $post_id ) {
		if ( $post_id ) {
			$category_id   = wp_get_object_terms( $post_id, rtcl()->category, [ 'fields' => 'ids' ] );
			$category_id   = ( is_array( $category_id ) && ! empty( $category_id ) ) ? end( $category_id ) : 0;
			$selected_type = get_post_meta( $post_id, 'ad_type', true );
		} else {
			$category_id   = isset( $_GET['category'] ) ? absint( $_GET['category'] ) : 0;
			$selected_type = ( isset( $_GET['type'] ) && in_array( $_GET['type'], array_keys( Functions::get_listing_types() ) ) ) ? $_GET['type'] : '';
		}
		Functions::get_template( "listing-form/category-section", compact( 'post_id', 'category_id', 'selected_type' ) );
	}

	/**
	 * @param  int  $post_id
	 *
	 * @throws \Exception
	 */
	public static function listing_information( $post_id ) {
		if ( $post_id ) {
			$category_id   = wp_get_object_terms( $post_id, rtcl()->category, [ 'fields' => 'ids' ] );
			$category_id   = ( is_array( $category_id ) && ! empty( $category_id ) ) ? end( $category_id ) : 0;
			$selected_type = get_post_meta( $post_id, 'ad_type', true );
		} else {
			$category_id   = isset( $_GET['category'] ) ? absint( $_GET['category'] ) : 0;
			$selected_type = ( isset( $_GET['type'] ) && in_array( $_GET['type'], array_keys( Functions::get_listing_types() ) ) ) ? $_GET['type'] : '';
		}
		$moderation_settings = Functions::get_option( 'rtcl_moderation_settings' );
		$editor              = ! empty( $moderation_settings['text_editor'] ) ? $moderation_settings['text_editor'] : 'wp_editor';
		$price               = $post_content = $listing_pricing = $price_type = $title = '';
		$listing             = null;
		$tags                = [];
		if ( $post_id > 0 ) {
			$listing         = new Listing( $post_id );
			$category_id     = wp_get_object_terms( $post_id, rtcl()->category, [ 'fields' => 'ids' ] );
			$category_id     = ( is_array( $category_id ) && ! empty( $category_id ) ) ? end( $category_id ) : 0;
			$price_type      = get_post_meta( $post_id, 'price_type', true );
			$listing_pricing = get_post_meta( $post_id, '_rtcl_listing_pricing', true );
			$price           = get_post_meta( $post_id, 'price', true );
			$tags            = wp_get_object_terms( $post_id, rtcl()->tag );

			global $post;
			$post = get_post( $post_id );
			setup_postdata( $post );
			$title        = get_the_title();
			$post_content = get_the_content();
			wp_reset_postdata();
		}
		Functions::get_template( "listing-form/information", [
			'listing'           => $listing,
			'post_id'           => $post_id,
			'title'             => $title,
			'post_content'      => $post_content,
			'tags'              => $tags,
			'price'             => $price,
			'listing_pricing'   => in_array( $listing_pricing, array_keys( Options::get_listing_pricing_types() ) ) ? $listing_pricing : 'price',
			'price_type'        => $price_type,
			'editor'            => $editor,
			'category_id'       => $category_id,
			'selected_type'     => $selected_type,
			'title_limit'       => Functions::get_title_character_limit(),
			'description_limit' => Functions::get_description_character_limit(),
			'parent_cat_id'     => 0,
			'child_cat_id'      => 0,
			'hidden_fields'     => ( ! empty( $moderation_settings['hide_form_fields'] ) ) ? $moderation_settings['hide_form_fields'] : [],
		] );
	}

	/**
	 * @param $post_id
	 */
	public static function listing_gallery( $post_id ) {
		// Images
		if ( ! Functions::is_gallery_disabled() ) {
			Functions::get_template( "listing-form/gallery", compact( 'post_id' ) );


			// Videos
			if ( ! Functions::is_video_urls_disabled() ) {
				$video_urls = get_post_meta( $post_id, '_rtcl_video_urls', true );
				$video_urls = ! empty( $video_urls ) && is_array( $video_urls ) ? $video_urls : [];
				Functions::get_template( "listing-form/video-urls", compact( 'post_id', 'video_urls' ) );
			}
		}
	}

	public static function listing_recaptcha( $post_id ) {
		$settings = Functions::get_option_item( 'rtcl_misc_settings', 'recaptcha_forms', [] );
		if ( ! empty( $settings ) && is_array( $settings ) && in_array( 'listing', $settings ) ) {
			Functions::get_template( "listing-form/recaptcha", compact( 'post_id' ) );
		}
	}

	public static function listing_terms_conditions( $post_id ) {
		$agreed = get_post_meta( $post_id, 'rtcl_agree', true );
		Functions::get_template( "listing-form/terms-conditions", compact( 'post_id', 'agreed' ) );
	}

	public static function listing_contact( $post_id ) {
		$location_id        = $sub_location_id = $sub_sub_location_id = 0;
		$user_id            = get_current_user_id();
		$user               = get_userdata( $user_id );
		$email              = $user ? $user->user_email : '';
		$phone              = get_user_meta( $user_id, '_rtcl_phone', true );
		$whatsapp_number    = get_user_meta( $user_id, '_rtcl_whatsapp_number', true );
		$telegram           = get_user_meta( $user_id, '_rtcl_telegram', true );
		$website            = get_user_meta( $user_id, '_rtcl_website', true );
		$selected_locations = (array) get_user_meta( $user_id, '_rtcl_location', true );
		$zipcode            = get_user_meta( $user_id, '_rtcl_zipcode', true );
		$geo_address        = get_user_meta( $user_id, '_rtcl_geo_address', true );
		$address            = get_user_meta( $user_id, '_rtcl_address', true );
		$latitude           = get_user_meta( $user_id, '_rtcl_latitude', true );
		$longitude          = get_user_meta( $user_id, '_rtcl_longitude', true );

		if ( $post_id ) {
			$selected_locations = 'local' === Functions::location_type() ? wp_get_object_terms( $post_id, rtcl()->location, [ 'fields' => 'ids' ] ) : [];
			$latitude           = get_post_meta( $post_id, 'latitude', true );
			$longitude          = get_post_meta( $post_id, 'longitude', true );
			$zipcode            = get_post_meta( $post_id, 'zipcode', true );
			$address            = get_post_meta( $post_id, 'address', true );
			$geo_address        = get_post_meta( $post_id, '_rtcl_geo_address', true );
			$phone              = get_post_meta( $post_id, 'phone', true );
			$whatsapp_number    = get_post_meta( $post_id, '_rtcl_whatsapp_number', true );
			$telegram           = get_post_meta( $post_id, '_rtcl_telegram', true );
			$email              = get_post_meta( $post_id, 'email', true );
			$website            = get_post_meta( $post_id, 'website', true );
		}
		$moderation_settings = Functions::get_option( 'rtcl_moderation_settings' );
		$data                = [
			'post_id'                    => $post_id,
			'state_text'                 => Text::location_level_first(),
			'city_text'                  => Text::location_level_second(),
			'town_text'                  => Text::location_level_third(),
			'selected_locations'         => $selected_locations,
			'latitude'                   => $latitude,
			'longitude'                  => $longitude,
			'zipcode'                    => $zipcode,
			'address'                    => $address,
			'geo_address'                => $geo_address,
			'phone'                      => $phone,
			'whatsapp_number'            => $whatsapp_number,
			'telegram'                   => $telegram,
			'email'                      => $email,
			'website'                    => $website,
			'location_id'                => $location_id,
			'sub_location_id'            => $sub_location_id,
			'sub_sub_location_id'        => $sub_sub_location_id,
			'hidden_fields'              => ( ! empty( $moderation_settings['hide_form_fields'] ) ) ? $moderation_settings['hide_form_fields'] : [],
			'enable_post_for_unregister' => ! is_user_logged_in() && Functions::is_enable_post_for_unregister(),
		];
		Functions::get_template( "listing-form/contact", apply_filters( 'rtcl_listing_form_contact_tpl_attributes', $data, $post_id ) );
	}

	public static function add_apply_filter_button() {
		?>
		<div class="ui-buttons has-expanded">
			<button class="rtcl-btn rtcl-btn-primary rtcl-filter-btn">
				<?php
				echo esc_html__( "Apply filters", 'classified-listing' ); ?>
			</button>
			<?php
			if ( isset( $_GET['filters'] ) ): ?>
				<a class="rtcl-btn rtcl-btn-primary rtcl-filter-clear-btn"
				   href="<?php
				   echo esc_url( Link::get_listings_page_link() ) ?>">
					<?php
					echo esc_html__( "Clear filters", 'classified-listing' ); ?>
				</a>
			<?php
			endif; ?>
		</div>
		<?php
	}

	public static function listing_form_submit_button( $post_id ) {
		?>
		<button type="submit" class="btn btn-primary rtcl-submit-btn">
			<?php
			if ( $post_id > 0 ) {
				echo esc_html( apply_filters( 'rtcl_listing_form_update_btn_text', esc_html__( 'Update', 'classified-listing' ) ) );
			} else {
				echo esc_html( apply_filters( 'rtcl_listing_form_submit_btn_text', esc_html__( 'Submit', 'classified-listing' ) ) );
			} ?>
		</button>
		<?php
	}

	public static function add_wpml_support( $post_id ) {
		if ( function_exists( 'icl_object_id' ) && isset( $_REQUEST['lang'] ) ) {
			echo sprintf( '<input type="hidden" name="lang" value="%s" />', esc_attr( $_REQUEST['lang'] ) );
		}
	}

	public static function add_listing_form_hidden_field( $post_id ) {
		echo sprintf( '<input type="hidden" name="_post_id" id="_post_id" value="%d"/>', esc_attr( $post_id ) );
		wp_nonce_field( rtcl()->nonceText, rtcl()->nonceId );
		if ( ! $post_id ) {
			$category_id   = isset( $_GET['category'] ) ? absint( $_GET['category'] ) : 0;
			$selected_type = ( isset( $_GET['type'] ) && in_array( $_GET['type'], array_keys( Functions::get_listing_types() ) ) ) ? $_GET['type'] : '';
			echo sprintf( '<input type="hidden" name="_category_id" id="category-id" value="%d"/>', esc_attr( $category_id ) );
			echo sprintf( '<input type="hidden" name="_ad_type" id="ad-type" value="%s"/>', esc_attr( $selected_type ) );
		}
	}

	public static function add_name_fields_at_registration_form() {
		?>
		<div class="rtcl-form-group name-row">
			<div class="first-name-column">
				<label for="rtcl-reg-first-name" class="rtcl-field-label">
					<?php
					esc_html_e( 'First Name', 'classified-listing' ); ?>
					<strong class="rtcl-required">*</strong>
				</label>
				<input type="text" name="first_name" id="rtcl-reg-first-name"
					   value="<?php
					   if ( ! empty( $_POST['first_name'] ) ) {
						   echo esc_attr( $_POST['first_name'] );
					   } ?>"
					   class="rtcl-form-control" required/>
			</div>
			<div class="second-name-column">
				<label for="rtcl-reg-last-name" class="rtcl-field-label">
					<?php
					esc_html_e( 'Last Name', 'classified-listing' ); ?>
					<strong class="rtcl-required">*</strong>
				</label>
				<input type="text" name="last_name"
					   value="<?php
					   if ( ! empty( $_POST['last_name'] ) ) {
						   echo esc_attr( $_POST['last_name'] );
					   } ?>"
					   id="rtcl-reg-last-name" class="rtcl-form-control" required/>
			</div>
		</div>
		<?php
	}

	/**
	 * @return void
	 */
	public static function add_user_type_at_registration_form() {
		$seller_label = Functions::get_user_type_seller_label();
		$buyer_label  = Functions::get_user_type_buyer_label();
		?>
		<div class="rtcl-form-group user-type-row">
			<p class="rtcl-field-label">
				<?php
				esc_html_e( 'Account Type', 'classified-listing' ); ?>
				<strong class="rtcl-required">*</strong>
			</p>
			<div class="rtcl-form-radio-group rtcl-radio-group-inline">
				<label for="user_type_seller">
					<input type="radio" id="user_type_seller" name="rtcl_user_type" value="seller"
						<?php
						checked( $_POST['rtcl_user_type'] ?? '', 'seller' ); ?> required>
					<?php
					echo esc_html( $seller_label ); ?>
				</label>
				<label for="user_type_buyer">
					<input type="radio" id="user_type_buyer" name="rtcl_user_type" value="buyer"
						<?php
						checked( $_POST['rtcl_user_type'] ?? '', 'buyer' ); ?> required>
					<?php
					echo esc_html( $buyer_label ); ?>
				</label>
			</div>
		</div>
		<?php
	}

	public static function add_phone_at_registration_form() {
		$is_required = (boolean) apply_filters( 'rtcl_registration_phone_validation', false, '' );
		?>
		<div class="rtcl-form-group phone-row">
			<?php
			do_action( 'rtcl_register_form_phone_start' ); ?>
			<label for="rtcl-reg-phone" class="rtcl-field-label phone-label">
				<?php
				esc_html_e( 'Phone Number', 'classified-listing' ); ?>
				<?php
				if ( $is_required ): ?>
					<strong class="rtcl-required">*</strong>
				<?php
				endif; ?>
			</label>
			<div class="rtcl-phone-field-button">
				<input type="text" name="phone"
					   value="<?php
					   if ( ! empty( $_POST['phone'] ) ) {
						   echo esc_attr( $_POST['phone'] );
					   } ?>"
					   id="rtcl-reg-phone" class="rtcl-form-control"<?php
				echo $is_required ? ' required' : '' ?>/>
				<?php do_action( 'rtcl_register_form_phone_inner' ); ?>
			</div>
			<?php do_action( 'rtcl_register_form_phone_end' ); ?>
		</div>
		<?php
	}

	public static function add_single_listing_review() {
		if ( Functions::is_enable_template_support() && ( comments_open() || get_comments_number() ) ) {
			comments_template();
		}
	}

	public static function add_single_listing_sidebar() {
		Functions::get_template( "listing/listing-sidebar" );
	}

	public static function add_single_listing_inner_sidebar_custom_field() {
		/** @var Listing $listing */
		global $listing;
		if ( FBHelper::isEnabled() ) {
			$listing->custom_fields();
		} else {
			$listing->the_custom_fields();
		}
	}

	public static function add_single_listing_inner_sidebar_action() {
		/** @var Listing $listing */
		global $listing;
		$listing->the_actions();
	}

	public static function add_single_listing_gallery() {
		/** @var Listing $listing */
		global $listing;
		$listing->the_gallery();
	}

	/**
	 * @param $listing Listing
	 */
	public static function single_listing_map_content( $listing ) {
		if ( is_a( $listing, Listing::class ) ) {
			if ( ( $form = $listing->getForm() ) && ! $form->getFieldByElement( 'map' ) ) {
				return;
			}
			$latitude  = get_post_meta( $listing->get_id(), 'latitude', true );
			$longitude = get_post_meta( $listing->get_id(), 'longitude', true );
			$address   = null;
			if ( 'geo' === Functions::location_type() ) {
				$address = esc_html( wp_strip_all_tags( get_post_meta( $listing->get_id(), '_rtcl_geo_address', true ) ) );
			}

			if ( ! $address ) {
				$locations    = [];
				$rawLocations = $listing->get_locations();
				if ( count( $rawLocations ) ) {
					foreach ( $rawLocations as $location ) {
						$locations[] = $location->name;
					}
				}
				if ( $zipcode = get_post_meta( $listing->get_id(), 'zipcode', true ) ) {
					$locations[] = esc_html( $zipcode );
				}
				if ( $address = get_post_meta( $listing->get_id(), 'address', true ) ) {
					$locations[] = esc_html( $address );
				}
				$locations = array_reverse( $locations );
				$address   = ! empty( $locations ) ? implode( ',', $locations ) : null;
			}
			$map_options  = [];
			$map_settings = [
				'has_map'     => Functions::has_map() && ! Functions::hide_map( $listing->get_id() ),
				'latitude'    => $latitude,
				'longitude'   => $longitude,
				'address'     => $address,
				'map_options' => $map_options,
			];
			$map_settings = apply_filters( 'rtcl_single_listing_map_settings', $map_settings ); // Filter Added By Rashid
			Functions::get_template( "listing/map", $map_settings );
		}
	}

	public static function add_single_listing_meta() {
		/** @var Listing $listing */
		global $listing; ?>
		<!-- Meta data -->
		<div class="rtcl-listing-meta">
			<?php
			$listing->the_badges(); ?>
			<?php
			$listing->the_meta(); ?>
		</div>
		<?php
	}

	public static function add_single_listing_title() {
		/** @var Listing $listing */
		global $listing; ?>
		<div class="rtcl-listing-title"><h2 class="entry-title"><?php
				$listing->the_title(); ?></h2></div>
		<?php
	}


	public static function listing_actions() {
		Functions::get_template( 'listing/loop/actions' );
	}

	public static function pagination() {
		Functions::pagination();
	}


	/**
	 * Output the Listing sorting options.
	 */
	public static function catalog_ordering() {
		if ( ! Functions::get_loop_prop( 'is_paginated' ) ) {
			return;
		}
		$orderby                 = Functions::get_option_item( 'rtcl_general_settings', 'orderby' );
		$order                   = Functions::get_option_item( 'rtcl_general_settings', 'order' );
		$orderby_order           = $orderby . "-" . $order;
		$catalog_orderby_options = Options::get_listing_orderby_options();

		$default_orderby = Functions::get_loop_prop( 'is_search' ) ? 'relevance' : $orderby_order;
		$orderby         = isset( $_GET['orderby'] ) ? Functions::clean( wp_unslash( $_GET['orderby'] ) )
			: $default_orderby; // WPCS: sanitization ok, input var ok, CSRF ok.

		if ( Functions::get_loop_prop( 'is_search' ) ) {
			$catalog_orderby_options = array_merge( [ 'relevance' => esc_html__( 'Relevance', 'classified-listing' ) ], $catalog_orderby_options );

			unset( $catalog_orderby_options['menu_order'] );
		}

		if ( ! array_key_exists( $orderby, $catalog_orderby_options ) ) {
			$orderby = current( array_keys( $catalog_orderby_options ) );
		}

		Functions::get_template(
			'listing/loop/orderby',
			[
				'catalog_orderby_options' => $catalog_orderby_options,
				'orderby'                 => $orderby,
			],
		);
	}


	/**
	 * Output the result count text (Showing x - x of x results).
	 */
	public static function result_count() {
		if ( ! Functions::get_loop_prop( 'is_paginated' ) ) {
			return;
		}
		$args = [
			'total'    => Functions::get_loop_prop( 'total' ),
			'per_page' => Functions::get_loop_prop( 'per_page' ),
			'current'  => Functions::get_loop_prop( 'current_page' ),
		];

		Functions::get_template( 'listing/loop/result-count', $args );
	}

	/**
	 * Outputs all queued notices on.
	 *
	 * @since 1.5.5
	 */
	public static function output_all_notices() {
		$all_notices = rtcl()->session->get( 'rtcl_notices', [] );
		if ( ! empty( $all_notices ) ) {
			?>
			<div class="rtcl-notices-wrapper">
				<?php
				Functions::print_notices(); ?>
			</div>
			<?php
		}
	}

	public static function loop_item_excerpt() {
		/** @var Listing $listing */
		global $listing;
		if ( empty( $listing ) ) {
			return;
		}
		if ( $listing->can_show_excerpt() ) {
			?>
			<p class="rtcl-excerpt"><?php
				$listing->the_excerpt(); ?></p>
			<?php
		}
	}

	public static function loop_item_meta() {
		/** @var Listing $listing */
		global $listing;
		if ( empty( $listing ) ) {
			return;
		}
		$listing->the_meta();
	}

	public static function loop_item_meta_buttons() {
		Functions::get_template( 'listing/meta-buttons' );
	}

	public static function loop_item_badges() {
		/** @var Listing $listing */
		global $listing;
		if ( empty( $listing ) ) {
			return;
		}
		$listing->the_badges();
	}

	public static function loop_item_listing_title() {
		/** @var Listing $listing */
		global $listing;

		if ( empty( $listing ) ) {
			return;
		}
		echo '<h3 class="' . esc_attr( apply_filters( 'rtcl_listing_loop_title_classes', 'listing-title rtcl-listing-title' ) ) . '"><a href="'
			 . esc_url( $listing->get_the_permalink() ) . '">' .
			 // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			 $listing->get_the_title() . '</a></h3>';
	}

	public static function loop_item_wrapper_start() {
		/** @var Listing $listing */
		global $listing;
		if ( empty( $listing ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo apply_filters( 'rtcl_loop_item_wrapper_start', sprintf( '<div class="item-content%s">', ! $listing->can_show_price() ? ' no-price' : '' ) );
	}

	public static function loop_item_wrapper_end() {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo apply_filters( 'rtcl_loop_item_wrapper_end', '</div>' );
	}

	public static function listing_price() {
		Functions::get_template( 'listing/loop/price' );
	}

	public static function listing_thumbnail() {
		Functions::get_template( 'listing/loop/thumbnail' );
	}

public static function container_start() {
	?>
	<div class="<?php
	echo esc_attr( Functions::add_theme_container_class() ); ?>">
		<?php
		}

		public static function container_end() {
		?>
	</div>
	<?php
}

public static function output_main_wrapper_start() {
	?>
	<div class="rtcl-content-wrapper">
		<?php
		}

		public static function output_content_wrapper() {
			Functions::get_template( 'global/wrapper-start' );
		}

		public static function breadcrumb() {
			Functions::breadcrumb();
		}

		public static function output_main_wrapper_end() {
		?>
	</div>
	<?php
}

	public static function output_content_wrapper_end() {
		Functions::get_template( 'global/wrapper-end' );
	}

	public static function get_sidebar() {
		Functions::get_template( 'global/sidebar' );
	}

	/**
	 * Show an archive description on taxonomy archives.
	 */
	public static function taxonomy_archive_description() {
		$term = false;
		if ( Functions::is_listing_taxonomy() && 0 === absint( get_query_var( 'paged' ) ) ) {
			$term = get_queried_object();
		} elseif ( Functions::is_listings() ) {
			$category = get_query_var( '__cat' );
			if ( ! empty( $category ) ) {
				$term = get_term_by( 'slug', $category, rtcl()->category );
			}
		}
		if ( $term && ! empty( $term->description ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<div class="rtcl-term-description">' . Functions::format_content( $term->description ) . '</div>';
		}
	}

	public static function listing_archive_description() {
		// Don't display the description on search results page.
		if ( is_search() ) {
			return;
		}

		if ( is_post_type_archive( rtcl()->post_type )
			 && in_array( absint( get_query_var( 'paged' ) ), [
				0,
				1,
			], true )
		) {
			$listings_page = get_post( Functions::get_page_id( 'listings' ) );
			if ( $listings_page ) {
				$description = Functions::format_content( $listings_page->post_content );
				if ( $description ) {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo '<div class="rtcl-page-description">' . $description . '</div>';
				}
			}
		}
	}

	public static function no_listings_found() {
		Functions::get_template( 'listing/loop/no-listings-found' );
	}


	/**
	 * Add body classes for Rtcl pages.
	 *
	 * @param  array  $classes  Body Classes.
	 *
	 * @return array
	 */
	public static function body_class( $classes ) {
		$classes = (array) $classes;
		if ( Functions::is_rtcl() || is_author() ) {
			$classes[] = 'rtcl';
			$classes[] = 'rtcl-page';
		} elseif ( Functions::is_checkout_page() ) {
			$classes[] = 'rtcl-checkout';
			$classes[] = 'rtcl-page';
		} elseif ( Functions::is_account_page() ) {
			$classes[] = 'rtcl-account';
			$classes[] = 'rtcl-page';
			if ( Functions::is_registration_page_separate() ) {
				if ( Functions::is_account_page( 'registration' ) ) {
					$classes[] = 'rtcl-page-registration';
				} elseif ( ! is_user_logged_in() ) {
					$classes[] = 'rtcl-page-login';
				}
			}
		} elseif ( Functions::is_listing_form_page() ) {
			$classes[] = 'rtcl-form-page';
			$classes[] = 'rtcl-page';
		}

		if ( Functions::is_listing() && ! is_active_sidebar( 'rtcl-single-sidebar' ) ) {
			$classes[] = 'rtcl-single-no-sidebar';
		} elseif ( ( Functions::is_listings() || Functions::is_listing_taxonomy() ) && ! is_active_sidebar( 'rtcl-archive-sidebar' ) ) {
			$classes[] = 'rtcl-archive-no-sidebar';
		}

		$classes[] = 'rtcl-no-js';

		add_action( 'wp_footer', [ __CLASS__, 'no_js' ] );

		return array_unique( $classes );
	}


	/**
	 * Adds extra post classes for listings via the WordPress post_class hook, if used.
	 *
	 * Note: For performance reasons we instead recommend using listing_class/get_listing_class instead.
	 *
	 * @param  array  $classes  Current classes.
	 * @param  string|array  $class  Additional class.
	 * @param  int  $post_id  Post ID.
	 *
	 * @return array
	 * @since 1.5.4
	 */
	public static function listing_post_class( $classes, $class = '', $post_id = 0 ) {
		if ( ! $post_id || rtcl()->post_type !== get_post_type( $post_id ) ) {
			return $classes;
		}

		$listing = rtcl()->factory->get_listing( $post_id );

		if ( ! $listing ) {
			return $classes;
		}

		$classes[] = 'listing-item';
		$classes[] = 'rtcl-listing-item';

		return $classes;
	}


	/**
	 * NO JS handling.
	 *
	 * @since 1.5.4
	 */
	public static function no_js() {
		?>
		<script type="text/javascript">
			var c = document.body.className;
			c = c.replace(/rtcl-no-js/, 'rtcl-js');
			document.body.className = c;
		</script>
		<?php
	}

	public static function user_information( $current_user ) {
		$note = Functions::get_option_item( 'rtcl_general_settings', 'admin_note_to_users' );
		Functions::get_template( 'myaccount/user-info', compact( 'current_user', 'note' ) );
	}

	public static function account_navigation() {
		Functions::get_template( 'myaccount/navigation' );
	}

	public static function account_content() {
		global $wp;

		if ( ! empty( $wp->query_vars ) ) {
			foreach ( $wp->query_vars as $key => $value ) {
				// Ignore pagename param.
				if ( 'pagename' === $key ) {
					continue;
				}

				if ( has_action( 'rtcl_account_' . $key . '_endpoint' ) ) {
					do_action( 'rtcl_account_' . $key . '_endpoint', $value );

					return;
				}
			}
		}

		// No endpoint found? Default to dashboard.
		Functions::get_template( 'myaccount/dashboard', [
			'current_user' => get_user_by( 'id', get_current_user_id() ),
		] );
	}

	public static function checkout_content() {
		global $wp;

		if ( ! empty( $wp->query_vars ) ) {
			foreach ( $wp->query_vars as $key => $value ) {
				// Ignore pagename param.
				if ( 'pagename' === $key ) {
					continue;
				}

				if ( has_action( 'rtcl_checkout_' . $key . '_endpoint' ) ) {
					do_action( 'rtcl_checkout_' . $key . '_endpoint', $key, $value );

					return;
				}
			}
		}

		// No endpoint found? Default to error.
		Functions::get_template( 'checkout/error' );
	}

	public static function checkout_submission_endpoint( $type, $listing_id ) {
		Checkout::checkout_form( $type, $listing_id );
	}

	public static function checkout_payment_receipt_endpoint( $type, $payment_id ) {
		Checkout::payment_receipt( $payment_id );
	}

	public static function account_edit_account_endpoint() {
		MyAccount::edit_account();
	}

	public static function account_listings_endpoint() {
		MyAccount::my_listings();
	}

	public static function account_favourites_endpoint() {
		MyAccount::favourite_listings();
	}

	public static function account_payments_endpoint() {
		MyAccount::payments_history();
	}

	public static function account_profile_settings_endpoint() {
		MyAccount::profile_settings();
	}

	public static function social_login_shortcode() {
		if ( ! apply_filters( 'rtcl_social_login_shortcode_disabled', false ) ) {
			$shortcode = apply_filters( 'rtcl_social_login_shortcode', Functions::get_option_item( 'rtcl_account_settings', 'social_login_shortcode', '' ) );
			if ( $shortcode ) {
				echo sprintf( '<div class="rtcl-social-login-wrap">%s</div>', do_shortcode( $shortcode ) );
			}
		}
	}

	public static function logged_in_hidden_fields() {
		wp_nonce_field( 'rtcl-login', 'rtcl-login-nonce' );
		if ( ! empty( $_REQUEST['redirect_to'] ) ) {
			$redirect_to = $_REQUEST['redirect_to'];
		} else {
			$redirect_to = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		}
		echo sprintf( '<input type="hidden" name="redirect_to" value="%s" />', esc_url( $redirect_to ) );
	}

	public static function registration_hidden_fields() {
		wp_nonce_field( 'rtcl-register', 'rtcl-register-nonce' );
		if ( ! empty( $_REQUEST['redirect_to'] ) ) {
			$redirect_to = $_REQUEST['redirect_to'];
		} else {
			$redirect_to = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		}
		echo sprintf( '<input type="hidden" name="redirect_to" value="%s" />', esc_url( $redirect_to ) );
	}

	public static function login_form_necessary_link() {
		?>
		<div class="rtcl-form-group-no-margin-bottom">
			<p class="rtcl-forgot-password">
				<?php if ( Functions::is_registration_enabled() ):
					$register_link = Link::get_my_account_page_link();
					if ( Functions::is_registration_page_separate() ) {
						$register_link = Link::get_registration_page_link();
					}
					?>
					<a class="register-link" href="<?php echo esc_url( $register_link ); ?>"><?php esc_html_e( 'Register',
							'classified-listing' ); ?></a><span>|</span>
				<?php endif; ?>
				<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Forgot your password?',
						'classified-listing' ); ?></a>

			</p>
		</div>
		<?php
	}

	/**
	 * Render privacy policy text on the register forms.
	 */
	public static function registration_privacy_policy() {
		Functions::privacy_policy_text( 'registration' );
	}


	public static function add_checkout_form_instruction() {
		?>
		<p><?php
			esc_html_e( 'Please review your order, and click purchase once you are ready to proceed.', 'classified-listing' ); ?></p>
		<?php
	}


	public static function add_checkout_form_promotion_options( $type, $listing_id ) {
		if ( 'submission' === $type ) {
			if ( $listing_id && rtcl()->post_type === get_post_type( $listing_id ) ) {
				$pricing_options = Functions::get_regular_pricing_options();
				Functions::get_template( "checkout/promotions", [
					'pricing_options' => $pricing_options,
					'listing_id'      => $listing_id,
				] );
			} else {
				Functions::add_notice( __( "Given Listing Id is not a valid listing", "classified-listing" ), "error" );
				Functions::get_template( "checkout/error" );
			}
		}
	}

	public static function add_checkout_overview() {
		if ( Functions::is_enable_tax() ) {
			Functions::get_template( "checkout/payment-overview" );
		}
	}

	public static function add_checkout_payment_method() {
		Functions::get_template( "checkout/payment-methods" );
	}

	public static function checkout_form_store_gateway() {
		$gateway = new GatewayStore();
		?>
		<div class="rtcl-store-gateway-info">
			<?php
			echo esc_html( $gateway->get_method_title() ); ?>
			(<small><?php
				echo esc_html( $gateway->get_method_description() ); ?></small>)
		</div>
		<?php
	}

	public static function add_checkout_billing_details() {
		if ( ! Functions::is_billing_address_disabled() ) {
			Functions::get_template( "checkout/form-billing" );
		}
	}


	public static function checkout_terms_and_conditions() {
		Functions::get_template( "checkout/terms-conditions" );
	}

	public static function registration_terms_and_conditions() {
		Functions::get_template( "myaccount/terms-conditions" );
	}


	public static function checkout_form_submit_button() {
		?>
		<div class="rtcl-submit-btn-wrap">
			<a class="rtcl-btn rtcl-btn-primary" href="<?php
			echo esc_url( Link::get_my_account_page_link() ) ?>">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo apply_filters( 'rtcl_checkout_myaccount_btn_text', esc_html__( 'Go to My Account', 'classified-listing' ) ); ?>
			</a>
			<button type="submit" id="rtcl-checkout-submit-btn" name="rtcl-checkout" class="rtcl-btn rtcl-btn-primary"
					value="1">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo apply_filters( 'rtcl_checkout_payment_btn_text', esc_html__( 'Proceed to payment', 'classified-listing' ) ); ?>
			</button>
		</div>
		<?php
	}


	public static function edit_account_form_hidden_field() {
		wp_nonce_field( 'rtcl_update_user_account', 'rtcl_user_account_nonce' ); ?>
		<div class="rtcl-response"></div><?php
	}

	public static function add_checkout_hidden_field( $type ) {
		wp_nonce_field( 'rtcl_checkout', 'rtcl_checkout_nonce' );
		printf( '<input type="hidden" name="type" value="%s"/>', esc_attr( $type ) ); ?><input type="hidden"
																							   name="action"
																							   value="rtcl_ajax_checkout_action"/><?php
	}


	public static function add_submission_checkout_hidden_field( $type, $listing_id ) {
		if ( 'submission' === $type ) {
			printf( '<input type="hidden" name="listing_id" value="%d"/>', absint( $listing_id ) );
		}
	}


	/**
	 * Render privacy policy text on the checkout.
	 */
	public static function checkout_privacy_policy_text() {
		Functions::privacy_policy_text();
	}


	public static function checkout_terms_and_conditions_page_content() {
		$terms_page_id = Functions::get_terms_and_conditions_page_id();

		if ( ! $terms_page_id ) {
			return;
		}

		$page = get_post( $terms_page_id );

		if ( $page && 'publish' === $page->post_status && $page->post_content && ! has_shortcode( $page->post_content, 'rtcl_checkout' ) ) {
			echo '<div class="rtcl-terms-and-conditions" style="display: none; max-height: 200px; overflow: auto;">'
				 . wp_kses_post( Functions::format_content( $page->post_content ) ) . '</div>';
		}
	}


	/**
	 * @param  Filter  $object
	 */
	public static function add_hidden_field_filter_form( $object ) {
		$args             = $object->get_instance();
		$current_category = ! empty( $args['current_taxonomy'][ rtcl()->category ] ) ? $args['current_taxonomy'][ rtcl()->category ]->slug : '';
		$current_tag      = ! empty( $args['current_taxonomy'][ rtcl()->tag ] ) ? $args['current_taxonomy'][ rtcl()->tag ]->slug : '';
		$current_location = ! empty( $args['current_taxonomy'][ rtcl()->location ] ) ? $args['current_taxonomy'][ rtcl()->location ]->slug : ''; ?>
		<input type="hidden" name="rtcl_category" value="<?php
		echo esc_attr( $current_category ) ?>">
		<input type="hidden" name="rtcl_tag" value="<?php
		echo esc_attr( $current_tag ) ?>">
		<input type="hidden" name="rtcl_location" value="<?php
		echo esc_attr( $current_location ) ?>">
		<?php
	}
}
