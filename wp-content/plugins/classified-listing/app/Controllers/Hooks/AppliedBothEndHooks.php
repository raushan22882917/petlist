<?php

namespace Rtcl\Controllers\Hooks;

use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Text;
use Rtcl\Models\Listing;
use Rtcl\Models\Payment;
use Rtcl\Models\PaymentGateway;
use Rtcl\Models\Pricing;
use Rtcl\Resources\Options;
use WP_Post;

class AppliedBothEndHooks {

	static public function init() {

		add_action( 'rtcl_new_user_created', [ __CLASS__, 'new_user_notification_email_admin' ], 10 );
		add_action( 'rtcl_new_user_created', [ __CLASS__, 'new_user_notification_email_user' ], 10, 3 );
		add_action( 'rtcl_transition_listing_status', [ __CLASS__, 'new_post_notification_email_user_submitted' ], 10, 3 );
		add_action( 'rtcl_transition_listing_status', [ __CLASS__, 'new_post_notification_email_user_published' ], 20, 3 );
		add_action( 'rtcl_transition_listing_status', [ __CLASS__, 'new_post_notification_email_admin' ], 30, 3 );
		add_action( 'rtcl_listing_form_after_save_or_update', [ __CLASS__, 'update_post_notification_email_admin' ], 40, 3 );

		add_filter( 'rtcl_my_account_endpoint', [ __CLASS__, 'my_account_end_point_filter' ], 10 );
		add_filter( 'rtcl_account_menu_item_classes', [
			__CLASS__,
			'my_account_menu_item_classes_filter_edit_account_for_wc'
		], 10, 3 );

		add_filter( 'rtcl_account_menu_item_classes', [
			__CLASS__,
			'my_account_menu_item_classes_filter_chat'
		], 10, 3 );

		add_action( 'rtcl_listing_form_price_unit', [ __CLASS__, 'rtcl_listing_form_price_unit_cb' ], 10, 2 );
		add_filter( 'rtcl_price_meta_html', [ __CLASS__, 'add_price_unit_to_price' ], 10, 3 );
		add_filter( 'rtcl_price_meta_html', [ __CLASS__, 'add_price_type_to_price' ], 20, 3 );

		add_filter( 'rtcl_checkout_validation_errors', [ __CLASS__, 'add_rtcl_checkout_validation' ], 10, 4 );
		add_filter( 'rtcl_checkout_process_new_order_args', [ __CLASS__, 'add_listing_id_at_regular_order' ], 10, 4 );

		add_action( 'rtcl_checkout_process_success', [ __CLASS__, 'add_checkout_process_notice' ], 10 );

		add_filter( 'rtcl_listing_get_custom_field_group_ids', [ __CLASS__, 'get_custom_field_group_ids' ], 10, 2 );
	}

	static function get_custom_field_group_ids( $ids, $category_id ) {
		$group_ids = is_array( $ids ) && ! empty( $ids ) ? $ids : [];
		// Get category fields
		if ( $category_id > 0 ) {

			// Get global fields
			$args = [
				'post_type'        => rtcl()->post_type_cfg,
				'post_status'      => 'publish',
				'posts_per_page'   => - 1,
				'fields'           => 'ids',
				'orderby'          => 'menu_order',
				'order'            => 'ASC',
				'suppress_filters' => false,
				'meta_query'       => [  // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query	
				                         [
					                         'key'   => 'associate',
					                         'value' => 'all'
				                         ],
				]
			];

			$group_ids = get_posts( $args );

			$args = [
				'post_type'        => rtcl()->post_type_cfg,
				'post_status'      => 'publish',
				'posts_per_page'   => - 1,
				'fields'           => 'ids',
				'orderby'          => 'menu_order',
				'order'            => 'ASC',
				'suppress_filters' => false,
				'tax_query'        => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				                        [
					                        'taxonomy'         => rtcl()->category,
					                        'field'            => 'term_id',
					                        'terms'            => $category_id,
					                        'include_children' => false,
				                        ],
				],
				'meta_query'       => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query	
				                        [
					                        'key'   => 'associate',
					                        'value' => 'categories'
				                        ],
				]
			];

			$category_groups = get_posts( $args );

			$group_ids = array_merge( $group_ids, $category_groups );
			$group_ids = array_unique( $group_ids );

		}

		return $group_ids;
	}

	/**
	 * @param Payment $payment
	 */
	static function add_checkout_process_notice( $payment ) {
		if ( $payment->gateway ) {
			if ( 'paypal' === $payment->gateway->id ) {
				Functions::add_notice( esc_html__( "Redirecting to paypal.", "classified-listing" ) );
			} else if ( 'offline' === $payment->gateway->id ) {
				Functions::add_notice( esc_html__( "Payment made pending confirmation.", "classified-listing" ) );
			} else {
				Functions::add_notice( apply_filters( 'rtcl_payment_method_redirect_notice',
					esc_html__( "Payment successfully made.", "classified-listing" ), $payment ) );
			}
		}
	}

	/**
	 * @param \WP_Error      $errors
	 * @param array          $checkout_data
	 * @param Pricing        $pricing
	 * @param PaymentGateway $gateway
	 *
	 * @return \WP_Error
	 */
	static function add_rtcl_checkout_validation( $errors, $checkout_data, $pricing, $gateway ) {
		if ( ! is_a( $pricing, Pricing::class ) && ! $pricing->exists() ) {
			$errors->add( 'rtcl_checkout_error_empty_pricing', __( "No pricing selected to make payment.", "classified-listing" ) );
		}
		if ( ! $gateway || ! is_object( $gateway ) ) {
			$errors->add( 'rtcl_checkout_error_empty_payment_gateway', __( "No payment Gateway selected.", "classified-listing" ) );
		}

		if ( ( $pricing && 'regular' === $pricing->getType() )
		     && ( ! isset( $checkout_data['listing_id'] )
		          || ! rtcl()->factory->get_listing( $checkout_data['listing_id'] ) )
		) {
			$errors->add( 'rtcl_checkout_error_empty_listing', __( "No ad selected to make payment.", "classified-listing" ) );
		}

		return $errors;
	}

	/**
	 * @param array          $new_payment_args
	 * @param Pricing        $pricing
	 * @param PaymentGateway $gateway
	 * @param array          $checkout_data
	 *
	 * @return array
	 */
	static function add_listing_id_at_regular_order( $new_payment_args, $pricing, $gateway, $checkout_data ) {
		if ( $pricing && 'regular' === $pricing->getType() ) {
			$new_payment_args['meta_input']['listing_id'] = isset( $checkout_data['listing_id'] ) ? absint( $checkout_data['listing_id'] ) : 0;
		}

		return $new_payment_args;
	}

	/**
	 * @param string  $price_meta_html
	 * @param string  $price
	 * @param Listing $listing
	 *
	 * @return string
	 */
	public static function add_price_type_to_price( $price_meta_html, $price, $listing ) {
		if ( is_a( $listing, Listing::class ) ) {
			$is_single  = Functions::get_option_item( 'rtcl_single_listing_settings', 'display_options_detail', 'price_type', 'multi_checkbox' );
			$is_listing = Functions::get_option_item( 'rtcl_archive_listing_settings', 'display_options', 'price_type', 'multi_checkbox' );
			if ( ( $is_single && is_singular( rtcl()->post_type ) ) || ( $is_listing && ! is_singular( rtcl()->post_type ) ) ) {
				$price_type      = $listing->get_price_type();
				$price_type_html = null;
				if ( $price_type == "negotiable" ) {
					$price_type_html = sprintf( '<span class="rtcl-price-type-label rtcl-price-type-negotiable">(%s)</span>',
						esc_html( Text::price_type_negotiable() ) );
				} elseif ( $price_type == "fixed" ) {
					$price_type_html = sprintf( '<span class="rtcl-price-type-label rtcl-price-type-fixed">(%s)</span>', esc_html( Text::price_type_fixed() ) );
				} elseif ( $price_type === "on_call" ) {
					$price_type_html = sprintf( '<span class="rtcl-price-type-label rtcl-on_call">%s</span>', esc_html( Text::price_type_on_call() ) );
				}
				$price_meta_html .= apply_filters( 'rtcl_add_price_type_to_price', $price_type_html, $price_type, $listing );
			}
		}

		return $price_meta_html;
	}

	/**
	 * @param string  $price_meta_html
	 * @param string  $price
	 * @param Listing $listing
	 *
	 * @return string
	 */
	public static function add_price_unit_to_price( $price_meta_html, $price, $listing ) {
		if ( is_a( $listing, Listing::class ) && $listing->get_price_type() !== 'on_call' && $price_unit = $listing->get_price_unit() ) {
			$price_unit_html = null;
			$price_units     = Options::get_price_unit_list();
			if ( in_array( $price_unit, array_keys( $price_units ) ) ) {
				$price_unit_html = sprintf( '<span class="rtcl-price-unit-label rtcl-price-unit-%s">%s</span>', $price_unit,
					$price_units[ $price_unit ]['short'] );
			}
			$price_meta_html .= apply_filters( 'rtcl_add_price_unit_to_price', $price_unit_html, $price_unit, $listing );
		}

		return $price_meta_html;
	}


	static function my_account_menu_item_classes_filter_edit_account_for_wc( $classes, $endpoint, $query_vars ) {
		if ( $endpoint === 'edit-account' && Functions::is_wc_activated() && isset( $query_vars['rtcl_edit_account'] )
		     && $query_vars['rtcl_edit_account'] === $endpoint
		     && ! in_array( 'is-active', $classes )
		) {
			$classes[] = 'is-active';
		}

		return $classes;
	}

	static function my_account_menu_item_classes_filter_chat( $classes, $endpoint ) {
		if ( $endpoint === 'chat' ) {
			$classes[] = 'rtcl-chat-unread-count';
		}

		return $classes;
	}

	/**
	 * @param $endpoints
	 *
	 * @return mixed
	 */
	public static function my_account_end_point_filter( $endpoints ) {

		// Remove payment endpoint
		if ( Functions::is_payment_disabled() ) {
			unset( $endpoints['payments'] );
		}

		// Remove favourites endpoint
		if ( Functions::is_favourites_disabled() ) {
			unset( $endpoints['favourites'] );
		}

		return $endpoints;
	}

	static public function new_user_notification_email_admin( $user_id ) {
		if ( Functions::get_option_item( 'rtcl_email_notifications_settings', 'notify_admin', 'register_new_user', 'multi_checkbox' ) ) {
			rtcl()->mailer()->emails['User_New_Registration_Email_To_Admin']->trigger( $user_id );
		}
	}

	static public function new_user_notification_email_user( $user_id, $new_user_data, $password_generated ) {
		if ( $password_generated ) {
			$new_user_data['password_generated'] = true;
		}
		if ( Functions::get_option_item( 'rtcl_email_notifications_settings', 'notify_users', 'register_new_user', 'multi_checkbox' ) ) {
			rtcl()->mailer()->emails['User_New_Registration_Email_To_User']->trigger( $user_id, $new_user_data );
		}
	}

	/**
	 * @param Listing $listing
	 * @param         $type
	 */
	static public function update_post_notification_email_admin( Listing $listing, $type ) {
		if ( $type == 'update' && Functions::get_option_item( 'rtcl_email_notifications_settings', 'notify_admin', 'listing_edited', 'multi_checkbox' ) ) {
			rtcl()->mailer()->emails['Listing_Update_Email_To_Admin']->trigger( $listing->get_id() );
		}
	}

	/**
	 * @param         $new_status
	 * @param         $old_status
	 * @param WP_Post $post
	 */
	static public function new_post_notification_email_admin( $new_status, $old_status, WP_Post $post ) {
		if ( ( 'new' === $old_status || 'rtcl-temp' === $old_status ) && 'rtcl-temp' !== $post->post_status
		     && Functions::get_option_item( 'rtcl_email_notifications_settings', 'notify_admin', 'listing_submitted', 'multi_checkbox' )
		     && $listing = rtcl()->factory->get_listing( $post->ID )
		) {
			rtcl()->mailer()->emails['Listing_Submitted_Email_To_Admin']->trigger( $listing->get_id() );
		}
	}

	/**
	 * @param         $new_status
	 * @param         $old_status
	 * @param WP_Post $post
	 */
	static public function new_post_notification_email_user_submitted( $new_status, $old_status, WP_Post $post ) {
		if ( ( 'new' === $old_status || 'rtcl-temp' === $old_status ) && 'rtcl-temp' !== $post->post_status
		     && Functions::get_option_item( 'rtcl_email_notifications_settings', 'notify_users', 'listing_submitted', 'multi_checkbox' )
		     && $listing = rtcl()->factory->get_listing( $post->ID )
		) {
			rtcl()->mailer()->emails['Listing_Submitted_Email_To_Owner']->trigger( $listing->get_id() );
		}
	}

	/**
	 * @param $new_status
	 * * @param $old_status
	 * * @param WP_Post $post
	 */
	static public function new_post_notification_email_user_published( $new_status, $old_status, WP_Post $post ) {
		if ( 'publish' === $new_status && $old_status !== $new_status && Functions::get_option_item( 'rtcl_email_notifications_settings', 'notify_users', 'listing_published', 'multi_checkbox' ) && $listing = rtcl()->factory->get_listing( $post->ID ) ) {
			rtcl()->mailer()->emails['Listing_Published_Email_To_Owner']->trigger( $listing->get_id() );
		}
	}

	/**
	 * @param     $listing Listing
	 * @param int $category_id
	 */
	static public function rtcl_listing_form_price_unit_cb( $listing, $category_id = 0 ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo Functions::get_listing_form_price_unit_html( $category_id, $listing );
	}

}
