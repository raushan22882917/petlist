<?php

namespace Rtcl\Controllers\Ajax;

use Exception;
use Rtcl\Controllers\Hooks\Filters;
use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;
use Rtcl\Helpers\ListingStats;
use Rtcl\Helpers\Pagination;
use Rtcl\Helpers\Text;
use Rtcl\Models\Cipher;
use Rtcl\Models\VStore;
use Rtcl\Resources\Options;
use WP_Error;
use WP_Taxonomy;
use WP_Term;

class PublicUser {
	public function __construct() {
		add_action( 'wp_ajax_rtcl_post_new_listing', [ $this, 'rtcl_post_new_listing' ] );
		add_action( 'wp_ajax_rtcl_tag_search', [ $this, 'rtcl_tag_search' ] );
		add_action(
			'wp_ajax_rtcl_get_one_level_category_select_list',
			[
				$this,
				'rtcl_get_one_level_category_select_list',
			],
		);
		add_action(
			'wp_ajax_rtcl_get_one_level_category_select_list_by_type',
			[
				$this,
				'rtcl_get_one_level_category_select_list_by_type',
			],
		);

		if ( ! is_user_logged_in() && Functions::is_enable_post_for_unregister() ) {
			add_action( 'wp_ajax_nopriv_rtcl_post_new_listing', [ $this, 'rtcl_post_new_listing' ] );

			add_action(
				'wp_ajax_nopriv_rtcl_get_one_level_category_select_list',
				[
					$this,
					'rtcl_get_one_level_category_select_list',
				],
			);
			add_action(
				'wp_ajax_nopriv_rtcl_get_one_level_category_select_list_by_type',
				[
					$this,
					'rtcl_get_one_level_category_select_list_by_type',
				],
			);
			add_action( 'wp_ajax_nopriv_rtcl_tag_search', [ $this, 'rtcl_tag_search' ] );
		}
		add_action( 'wp_ajax_rtcl_delete_listing', [ $this, 'rtcl_delete_listing' ] );
		add_action( 'wp_ajax_rtcl_public_add_remove_favorites', [ $this, 'rtcl_add_remove_favorites' ] );

		add_action( 'wp_ajax_rtcl_public_report_abuse', [ $this, 'rtcl_report_abuse' ] );
		add_action( 'wp_ajax_nopriv_rtcl_public_report_abuse', [ $this, 'rtcl_report_abuse' ] );
		add_action( 'wp_ajax_rtcl_public_send_contact_email', [ $this, 'send_contact_email' ] );
		add_action( 'wp_ajax_nopriv_rtcl_public_send_contact_email', [ &$this, 'send_contact_email' ] );

		// get dropdown terms
		add_action( 'wp_ajax_rtcl_child_dropdown_terms', [ $this, 'dropdown_terms' ] );
		add_action( 'wp_ajax_nopriv_rtcl_child_dropdown_terms', [ $this, 'dropdown_terms' ] );

		add_action( 'wp_ajax_rtcl_update_user_account', [ $this, 'rtcl_update_user_account' ] );
		add_action( 'wp_ajax_rtcl_update_profile_settings', [ $this, 'update_user_profile_settings' ] );

		// From request
		add_action( 'wp_ajax_nopriv_rtcl_login_request', [ __CLASS__, 'rtcl_login_request_handler' ] );
		add_action( 'wp_ajax_nopriv_rtcl_registration_request', [ __CLASS__, 'rtcl_registration_request_handler' ] );

		// profile Picture upload
		add_action( 'wp_ajax_rtcl_ajax_user_profile_picture_upload', [ __CLASS__, 'profile_picture_upload' ] );
		add_action( 'wp_ajax_rtcl_ajax_user_profile_picture_delete', [ __CLASS__, 'profile_picture_delete' ] );

		// show payment details
		add_action( 'wp_ajax_rtcl_payment_details_popup', [ __CLASS__, 'show_payment_details' ] );

		// Load More
		add_action( 'wp_ajax_rtcl_user_ad_load_more', [ __CLASS__, 'rtcl_user_ad_load_more' ] );
		add_action( 'wp_ajax_nopriv_rtcl_user_ad_load_more', [ __CLASS__, 'rtcl_user_ad_load_more' ] );
		add_action( 'wp_ajax_rtcl_ajax_renew_listing', [ __CLASS__, 'renew_listing' ] );
		// listing statistics
		add_action( 'wp_ajax_nopriv_rtcl_phone_whatsapp_revealed', [ __CLASS__, 'phone_whatsapp_reveal_count' ] );
		add_action( 'wp_ajax_rtcl_phone_whatsapp_revealed', [ __CLASS__, 'phone_whatsapp_reveal_count' ] );
		add_action( 'wp_ajax_nopriv_rtcl_phone_click', [ __CLASS__, 'phone_click_count' ] );
		add_action( 'wp_ajax_rtcl_phone_click', [ __CLASS__, 'phone_click_count' ] );
		add_action( 'wp_ajax_nopriv_rtcl_whatsapp_click', [ __CLASS__, 'whatsapp_click_count' ] );
		add_action( 'wp_ajax_rtcl_whatsapp_click', [ __CLASS__, 'whatsapp_click_count' ] );
		// My Listings
		add_action( 'wp_ajax_rtcl_my_listings_search', [ __CLASS__, 'my_listings_search' ] );
		add_filter( 'get_pagenum_link', [ __CLASS__, 'paginate_link_ajax_callback' ], 99, 2 );
	}

	public static function paginate_link_ajax_callback( $result, $pagenum ) {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'rtcl_my_listings_search' ) {
			$base   = Link::get_my_account_page_link( 'listings' );
			$result = trailingslashit( $base ) . 'page/' . $pagenum . '/';
		}

		return esc_url( $result );
	}

	public static function my_listings_search() {
		if ( ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( 'Authentication error!!', 'classified-listing' ) );
		}

		if ( ! get_current_user_id() ) {
			wp_send_json_error( esc_html__( 'You are not authorized to display it.', 'classified-listing' ) );
		}

		$search           = ! empty( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
		$archive_settings = Functions::get_option( 'rtcl_archive_listing_settings' );
		$paged            = Pagination::get_page_number();

		$args = [
			'post_type'      => rtcl()->post_type,
			'post_status'    => 'any',
			'posts_per_page' => ! empty( $archive_settings['listings_per_page'] ) ? absint( $archive_settings['listings_per_page'] ) : 10,
			'paged'          => $paged,
			'author'         => get_current_user_id(),
		];

		if ( ! empty( $search ) ) {
			$args['s'] = $search;
		}

		if ( ! empty( $_REQUEST['status'] )
		     && $status = sanitize_text_field( $_REQUEST['status'] )
		) { /* phpcs:ignore WordPress.Security.NonceVerification.Recommended */
			$args['post_status'] = $status;
		}

		if ( ! empty( $_REQUEST['directory'] )
		     && $form_id = absint( $_REQUEST['directory'] )
		) { /* phpcs:ignore WordPress.Security.NonceVerification.Recommended */
			$args['meta_query'] = [
				[
					'key'   => '_rtcl_form_id',
					'value' => $form_id,
				],
			];
		}

		$args = apply_filters( 'rtcl_my_listings_args', $args );

		$rtcl_query = new \WP_Query( $args );

		ob_start();
		Functions::get_template( "myaccount/my-listings-table", [ 'rtcl_query' => $rtcl_query ] );
		$html = ob_get_clean();

		wp_send_json_success( [ 'html' => $html ] );
	}

	public static function whatsapp_click_count() {
		if ( ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( 'Authentication error!!', 'classified-listing' ) );
		}

		$listing_id = absint( $_POST['listing_id'] );
		if ( ! $listing_id ) {
			wp_send_json_error( esc_html__( 'Listing Id is missing', 'classified-listing' ) );
		}
		$listing = rtcl()->factory->get_listing( $listing_id );

		if ( ! $listing ) {
			wp_send_json_error( esc_html__( 'Listing is not found.', 'classified-listing' ) );
		}
		$click = absint( get_post_meta( $listing_id, '_rtcl_whatsapp_click', true ) );
		$click = $click + 1;
		update_post_meta( $listing_id, '_rtcl_whatsapp_click', $click );
		ListingStats::record( $listing_id, 'whatsapp_click' );
		wp_send_json_success();
	}

	public static function phone_click_count() {
		if ( ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( 'Authentication error!!', 'classified-listing' ) );
		}

		$listing_id = absint( $_POST['listing_id'] );
		if ( ! $listing_id ) {
			wp_send_json_error( esc_html__( 'Listing Id is missing', 'classified-listing' ) );
		}
		$listing = rtcl()->factory->get_listing( $listing_id );

		if ( ! $listing ) {
			wp_send_json_error( esc_html__( 'Listing is not found.', 'classified-listing' ) );
		}
		$click = absint( get_post_meta( $listing_id, '_rtcl_phone_click', true ) );
		$click = $click + 1;
		update_post_meta( $listing_id, '_rtcl_phone_click', $click );
		ListingStats::record( $listing_id, 'phone_click' );
		wp_send_json_success();
	}

	public static function phone_whatsapp_reveal_count() {
		if ( ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( 'Authentication error!!', 'classified-listing' ) );
		}

		$listing_id = absint( $_POST['listing_id'] );
		if ( ! $listing_id ) {
			wp_send_json_error( esc_html__( 'Listing Id is missing', 'classified-listing' ) );
		}
		$listing = rtcl()->factory->get_listing( $listing_id );

		if ( ! $listing ) {
			wp_send_json_error( esc_html__( 'Listing is not found.', 'classified-listing' ) );
		}
		$reveal = absint( get_post_meta( $listing_id, '_rtcl_reveal_phone_whatsapp', true ) );
		$reveal = $reveal + 1;
		update_post_meta( $listing_id, '_rtcl_reveal_phone_whatsapp', $reveal );
		ListingStats::record( $listing_id, 'reveal' );
		wp_send_json_success();
	}

	public static function show_payment_details() {
		if ( ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( 'Authentication error!!', 'classified-listing' ) );
		}

		$current_user_id = get_current_user_id();
		if ( ! $current_user_id ) {
			wp_send_json_error( esc_html__( 'You are not authorized to display it.', 'classified-listing' ) );
		}

		$order_id = isset( $_POST['order_id'] ) ? absint( Functions::clean( $_POST['order_id'] ) ) : 0;

		if ( ! $order_id ) {
			wp_send_json_error( esc_html__( 'Order ID is missing.', 'classified-listing' ) );
		}

		$order = rtcl()->factory->get_order( $order_id );

		if ( ! $order ) {
			wp_send_json_error( esc_html__( 'Order not found.', 'classified-listing' ) );
		}
		
		$order_customer_id = (int) $order->get_customer_id();
		if ( $order_customer_id !== (int) $current_user_id ) {
			wp_send_json_error( esc_html__( 'You are not authorized to view this order.', 'classified-listing' ) );
		}

		ob_start();
		do_action( 'rtcl_payment_receipt_popup', $order->get_id(), $order );
		$html = ob_get_clean();

		wp_send_json_success(
			[
				'html' => $html,
			],
		);
	}

	public static function rtcl_tag_search() {
		if ( ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) ) {
			wp_die( - 1 );
		}

		$taxonomy        = rtcl()->tag;
		$taxonomy_object = get_taxonomy( $taxonomy );

		if ( ! $taxonomy_object ) {
			wp_die( 0 );
		}

		if ( ! current_user_can( $taxonomy_object->cap->assign_terms ) ) {
			wp_die( - 1 );
		}

		$search = wp_unslash( $_GET['q'] );

		$comma = _x( ',', 'tag delimiter', 'classified-listing' );
		if ( ',' !== $comma ) {
			$search = str_replace( $comma, ',', $search );
		}

		if ( str_contains( $search, ',' ) ) {
			$search = explode( ',', $search );
			$search = $search[ count( $search ) - 1 ];
		}

		$search = trim( $search );

		/**
		 * Filters the minimum number of characters required to fire a tag search via Ajax.
		 *
		 * @param  int  $characters  The minimum number of characters required. Default 2.
		 * @param  WP_Taxonomy  $taxonomy_object  The taxonomy object.
		 * @param  string  $search  The search term.
		 *
		 * @since 4.0.0
		 */
		$term_search_min_chars = (int) apply_filters( 'rtcl_term_search_min_chars', 2, $taxonomy_object, $search );

		/*
		 * Require $term_search_min_chars chars for matching (default: 2)
		 * ensure it's a non-negative, non-zero integer.
		 */
		if ( ( 0 == $term_search_min_chars ) || ( strlen( $search ) < $term_search_min_chars ) ) {
			wp_die();
		}

		$results = get_terms(
			[
				'taxonomy'   => $taxonomy,
				'name__like' => $search,
				'fields'     => 'names',
				'hide_empty' => false,
				'number'     => isset( $_GET['number'] ) ? (int) $_GET['number'] : 0,
			],
		);

		/**
		 * Filters the Ajax term search results.
		 *
		 * @param  string[]  $results  Array of term names.
		 * @param  WP_Taxonomy  $taxonomy_object  The taxonomy object.
		 * @param  string  $search  The search term.
		 *
		 * @since 6.1.0
		 */
		$results = apply_filters( 'rtcl_ajax_term_search_results', $results, $taxonomy_object, $search );

		$success = false;

		$html = '';
		if ( ! empty( $results ) ) {
			$success = true;
			$html    .= '<ul>';
			foreach ( $results as $name ) {
				$html .= "<li>$name</li>";
			}
			$html .= '</ul>';
		}

		$response = [
			'success' => $success,
			'list'    => $html,
		];

		wp_send_json( $response );
	}

	public static function renew_listing() {
		if ( ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( 'Authentication error!!', 'classified-listing' ) );
		}

		$listingId = absint( Functions::clean( $_POST['listingId'] ) );
		if ( ! $listingId ) {
			wp_send_json_error( esc_html__( 'Listing Id is missing', 'classified-listing' ) );
		}
		$listing = rtcl()->factory->get_listing( $listingId );

		if ( ! $listing ) {
			wp_send_json_error( esc_html__( 'Listing is not found.', 'classified-listing' ) );
		}

		if ( ! apply_filters( 'rtcl_enable_renew_button', Functions::is_enable_renew(), $listing ) ) {
			wp_send_json_error( esc_html__( 'Unauthorized access.', 'classified-listing' ) );
		}

		if ( $listing->get_owner_id() !== get_current_user_id() ) {
			wp_send_json_error( esc_html__( 'You are not authorized to renew this listing.', 'classified-listing' ) );
		}

		if ( 'rtcl-expired' !== $listing->get_status() ) {
			wp_send_json_error( esc_html__( 'Listing is not expired.', 'classified-listing' ) );
		}

		$wp_error = new WP_Error();
		$vStore   = new VStore();

		do_action( 'rtcl_before_renew_listing', $listing, $vStore, $wp_error, $_REQUEST );

		if ( $wp_error->has_errors() ) {
			wp_send_json_error( $wp_error->get_error_message() );
		}

		$post_arg         = [
			'ID'          => $listing->get_id(),
			'post_status' => 'publish',
		];
		$updatedListingId = wp_update_post( $post_arg );
		if ( is_wp_error( $updatedListingId ) ) {
			return $updatedListingId;
		}
		Functions::add_default_expiry_date( $listing->get_id() );
		$wp_error = new WP_Error();
		do_action( 'rtcl_after_renew_listing', $listing, $vStore, $wp_error, $_REQUEST );

		if ( $wp_error->has_errors() ) {
			$post_arg = [
				'ID'          => $listing->get_id(),
				'post_status' => 'rtcl-expired',
			];
			wp_update_post( $post_arg );
			wp_send_json_error( $wp_error->get_error_message() );
		}

		if ( get_post_meta( $listing->get_id(), 'never_expires', true ) ) {
			$expire_at = esc_html__( 'Never Expires', 'classified-listing' );
		} elseif ( $expiry_date = get_post_meta( $listing->get_id(), 'expiry_date', true ) ) {
			$expire_at = date_i18n(
				get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
				strtotime( $expiry_date ),
			);
		} else {
			$expire_at = 'N/A';
		}

		wp_send_json_success(
			[
				'expire_at' => $expire_at,
				'status'    => 'publish',
				'message'   => esc_html__( 'Your listing is renewed!!', 'classified-listing' ),
			],
		);
	}

	public static function profile_picture_delete() {
		$message = null;
		if ( wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText )
		     && $user_id = get_current_user_id()
		) {
			$pp_id = absint( get_user_meta( $user_id, '_rtcl_pp_id', true ) );
			if ( $pp_id && wp_delete_attachment( $pp_id ) ) {
				delete_user_meta( $user_id, '_rtcl_pp_id' );
				wp_send_json_success( esc_html__( 'Successfully deleted', 'classified-listing' ) );
			} else {
				$message = esc_html__( 'File could not be deleted.', 'classified-listing' );
			}
		} else {
			$message = esc_html__( 'Authentication error!!', 'classified-listing' );
		}
		wp_send_json_error( $message );
	}

	public static function profile_picture_upload() {
		$msg = null;
		if ( wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) && isset( $_FILES['pp'] )
		     && $user_id = get_current_user_id()
		) {
			Filters::beforeUpload();
			$status = wp_handle_upload( $_FILES['pp'], [ 'test_form' => false ] );
			Filters::afterUpload();
			if ( $status && ! isset( $status['error'] ) ) {
				// $filename should be the path to a file in the upload directory.
				$filename = $status['file'];
				// Check the type of tile. We'll use this as the 'post_mime_type'.
				$fileType = wp_check_filetype( basename( $filename ), null );

				// Get the path to the upload directory.
				$wp_upload_dir = wp_upload_dir();

				// Prepare an array of post data for the attachment.
				$attachment = [
					'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
					'post_mime_type' => $fileType['type'],
					'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
					'post_content'   => '',
					'post_status'    => 'inherit',
				];

				// Insert the attachment.
				$attach_id = wp_insert_attachment( $attachment, $filename );
				if ( ! is_wp_error( $attach_id ) ) {
					if ( $existing_pp = get_user_meta( $user_id, '_rtcl_pp_id', true ) ) {
						wp_delete_attachment( $existing_pp, true );
					}
					update_user_meta( $user_id, '_rtcl_pp_id', $attach_id );
					wp_update_attachment_metadata( $attach_id, wp_generate_attachment_metadata( $attach_id, $filename ) );
					$src  = wp_get_attachment_image_src( $attach_id );
					$data = [
						'pp_id'   => $attach_id,
						'src'     => $src[0],
						'message' => esc_html__( 'Successfully updated.', 'classified-listing' ),
					];
					do_action( 'rtcl_user_pp_updated', $data, $user_id, $attach_id, $_REQUEST );
					wp_send_json_success( $data );
				}
			} else {
				$msg = $status['error'];
			}
		} else {
			$msg = esc_html__( 'Authentication error!!', 'classified-listing' );
		}
		wp_send_json_error( $msg );
	}

	public static function rtcl_registration_request_handler() {
		if ( ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( 'Session Expired!!', 'classified-listing' ) );
		}

		if ( ! Functions::is_registration_enabled() ) {
			wp_send_json_error( esc_html__( 'User registration is disabled', 'classified-listing' ) );
		}

		$username = isset( $_POST['username'] ) ? trim( $_POST['username'] ) : '';
		$password = isset( $_POST['password'] ) ? trim( $_POST['password'] ) : '';
		$email    = isset( $_POST['email'] ) ? trim( $_POST['email'] ) : '';
		$args     = [];
		if ( ! empty( $_POST['first_name'] ) ) {
			$args['first_name'] = sanitize_text_field( $_POST['first_name'] );
		}
		if ( ! empty( $_POST['last_name'] ) ) {
			$args['last_name'] = sanitize_text_field( $_POST['last_name'] );
		}
		if ( ! empty( $_POST['phone'] ) ) {
			$args['phone'] = sanitize_text_field( $_POST['phone'] );
		}

		try {
			$validation_error = new WP_Error();
			$validation_error = apply_filters( 'rtcl_process_registration_errors', $validation_error, $email, $username, $password, $_POST );

			if ( $validation_error->get_error_code() ) {
				throw new Exception( $validation_error->get_error_message() );
			}

			$new_user_id = Functions::create_new_user( sanitize_email( $email ), Functions::clean( $username ), $password, $args );

			if ( is_wp_error( $new_user_id ) ) {
				throw new Exception( $new_user_id->get_error_message() );
			}

			if ( ! empty( $_POST['redirect_to'] ) ) {
				$redirect = wp_sanitize_redirect( $_POST['redirect_to'] );
			} elseif ( Functions::get_raw_referer() ) {
				$redirect = Functions::get_raw_referer();
			} else {
				$redirect = Link::get_page_permalink( 'myaccount' );
			}

			if ( ! apply_filters( 'rtcl_registration_need_auth_new_user', false, $new_user_id ) ) {
				Functions::set_customer_auth_cookie( $new_user_id );
				$message = esc_html__( 'You have successfully registered.', 'classified-listing' );
			} else {
				$redirect = '';
				$message
				          = esc_html__( 'You have successfully registered on our website, Please check your email and click on the link, we sent a verification mail to verify your email address.',
					'classified-listing' );
			}

			wp_send_json_success(
				apply_filters(
					'rtcl_registration_request_success_data',
					[
						'redirect_url' => wp_validate_redirect( apply_filters( 'rtcl_login_redirect', $redirect, $_POST ) ),
						'message'      => $message,
					],
					$new_user_id,
					$_POST,
				),
			);
		} catch ( \Exception $e ) {
			wp_send_json_error( apply_filters( 'rtcl_registration_request_error_data', $e->getMessage(), $_POST ) );
		}
	}

	public static function rtcl_login_request_handler() {
		$validation_error = new WP_Error();

		if ( ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) ) {
			$validation_error->add( 'rtcl_session_error', esc_html__( 'Session Expired!!', 'classified-listing' ) );
		}
		$creds            = [
			'user_login'    => Cipher::decrypt( $_POST['username'], $_POST['__rtcl_wpnonce'] ),
			'user_password' => Cipher::decrypt( $_POST['password'], $_POST['__rtcl_wpnonce'] ),
			'remember'      => isset( $_POST['rememberme'] ),
		];
		$validation_error = apply_filters( 'rtcl_process_login_errors', $validation_error, $_POST['username'], $_POST['password'], $_POST );

		if ( is_wp_error( $validation_error ) && ! empty( $validation_error->errors ) ) {
			$error = apply_filters( 'wp_login_errors', $validation_error, '' );
			wp_send_json_error( apply_filters( 'rtcl_login_request_error', $error->get_error_message(), $error ) );
		}
		
		$user = wp_signon( apply_filters( 'rtcl_login_credentials', $creds, $_POST ), is_ssl() );

		if ( is_wp_error( $user ) && ! empty( $user->errors ) ) {
			$error = apply_filters( 'wp_login_errors', $user, '' );
			wp_send_json_error( apply_filters( 'rtcl_login_request_error', $error->get_error_message(), $error ) );
		}

		if ( is_multisite() && ! is_user_member_of_blog( $user->ID, get_current_blog_id() ) ) {
			add_user_to_blog( get_current_blog_id(), $user->ID, 'customer' );
		}

		if ( ! empty( $_POST['redirect_to'] ) ) {
			$redirect = $_POST['redirect_to'];
		} elseif ( Functions::get_raw_referer() ) {
			$redirect = Functions::get_raw_referer();
		} else {
			$redirect = Link::get_my_account_page_link();
		}

		wp_send_json_success(
			apply_filters(
				'rtcl_login_request_success_data',
				[
					'redirect_url' => wp_validate_redirect( apply_filters( 'rtcl_login_redirect', $redirect, $user, $_POST ) ),
					'message'      => esc_html__( 'You have successfully logged in.', 'classified-listing' ),
				],
				$user,
				$_POST,
			),
		);
	}

	public static function update_user_profile_settings() {
		$errors = new WP_Error();

		if ( ! apply_filters( 'rtcl_listing_form_remove_nonce', false )
		     && ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText )
		) {
			$errors->add( 'rtcl_session_error', esc_html__( 'Session Expired!!', 'classified-listing' ) );
		}

		if ( ! is_user_logged_in() ) {
			$errors->add( 'rtcl_unauthorized_error', esc_html__( 'Unauthorized request!!', 'classified-listing' ) );
		}

		if ( is_wp_error( $errors ) && $errors->has_errors() ) {
			wp_send_json_error( [ 'error' => $errors->get_error_message() ] );
		}

		$user_id            = get_current_user_id();
		$allowed_visibility = [ 'yes', 'logged_in', 'no' ];

		if ( ! empty( $_POST['user_display_name'] ) ) {
			$display_name = sanitize_text_field( $_POST['user_display_name'] );
			wp_update_user( [
				'ID'           => $user_id,
				'display_name' => $display_name,
			] );
		}

		$display_phone = sanitize_text_field( $_POST['display_phone'] ?? 'yes' );
		if ( in_array( $display_phone, $allowed_visibility, true ) ) {
			update_user_meta(
				$user_id,
				'_rtcl_display_phone_public',
				$display_phone,
			);
		}

		$display_whatsapp = sanitize_text_field( $_POST['display_whatsapp'] ?? 'yes' );
		if ( in_array( $display_whatsapp, $allowed_visibility, true ) ) {
			update_user_meta(
				$user_id,
				'_rtcl_display_whatsapp_public',
				$display_whatsapp,
			);
		}

		$display_email = sanitize_text_field( $_POST['display_email'] ?? 'yes' );
		if ( in_array( $display_email, $allowed_visibility, true ) ) {
			update_user_meta(
				$user_id,
				'_rtcl_display_email_public',
				$display_email,
			);
		}

		wp_send_json_success( [ 'message' => esc_html__( 'Settings updated successfully.', 'classified-listing' ) ] );
	}

	public function rtcl_update_user_account() {
		$errors = new WP_Error();

		if ( ! apply_filters( 'rtcl_listing_form_remove_nonce', false )
		     && ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText )
		) {
			$errors->add( 'rtcl_session_error', esc_html__( 'Session Expired!!', 'classified-listing' ) );
		}

		$error      = true;
		$msg        = [];
		$user_id    = get_current_user_id();
		$first_name = sanitize_text_field( $_POST['first_name'] );
		$last_name  = sanitize_text_field( $_POST['last_name'] );

		// Validate email
		$email = sanitize_email( $_POST['email'] );

		if ( ! is_email( $email ) ) {
			$errors->add( 'rtcl_invalid_email', esc_html__( 'Invalid email address.', 'classified-listing' ) );
		}

		if ( $id = email_exists( $email ) ) {
			if ( $id !== $user_id ) {
				$errors->add( 'rtcl_email_exist', esc_html__( 'Sorry, that email address already exists!', 'classified-listing' ) );
			}
		}

		// Validate password
		$password = '';

		if ( isset( $_POST['change_password'] ) && $_POST['change_password'] == 1 ) {
			$password = sanitize_text_field( $_POST['pass1'] );
			if ( empty( $password ) ) {
				// Password is empty
				$errors->add( 'rtcl_my_account_password_empty', esc_html__( 'The password field is empty.', 'classified-listing' ) );
			}

			if ( $password != $_POST['pass2'] ) {
				// Passwords don't match
				$errors->add( 'rtcl_my_account_password_not_matched', esc_html__( "The two passwords you entered don't match.", 'classified-listing' ) );
			}
			do_action( 'rtcl_my_account_validate_password_reset', $errors, $password );
		}

		// Generate the password so that the subscriber will have to check email...
		$user_data = [
			'ID'         => $user_id,
			'user_email' => $email,
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'nickname'   => $first_name,
		];

		do_action( 'rtcl_my_account_validate_user_data', $errors, $user_data );

		if ( is_wp_error( $errors ) && $errors->has_errors() ) {
			wp_send_json_error(
				[
					'error' => apply_filters( 'rtcl_update_user_account_error', $errors->get_error_message(), $errors ),
				],
			);

			return;
		}

		if ( ! empty( $password ) ) {
			$user_data['user_pass'] = $password;
		}

		$user_id   = wp_update_user( $user_data );
		$user_meta = [];

		$user_meta['_rtcl_phone']           = ! empty( $_POST['phone'] ) ? esc_attr( $_POST['phone'] ) : null;
		$user_meta['_rtcl_whatsapp_number'] = ! empty( $_POST['whatsapp_number'] ) ? esc_attr( $_POST['whatsapp_number'] ) : null;
		$user_meta['_rtcl_website']         = ! empty( $_POST['website'] ) ? esc_url_raw( $_POST['website'] ) : null;
		$user_meta['_rtcl_zipcode']         = ! empty( $_POST['zipcode'] ) ? esc_attr( $_POST['zipcode'] ) : null;
		$user_meta['_rtcl_address']         = ! empty( $_POST['address'] ) ? esc_textarea( $_POST['address'] ) : null;

		if ( Functions::has_map() && 'geo' === Functions::location_type() ) {
			$user_meta['_rtcl_geo_address'] = ! empty( $_POST['rtcl_geo_address'] ) ? esc_textarea( $_POST['rtcl_geo_address'] ) : null;
			$user_meta['_rtcl_latitude']    = ! empty( $_POST['latitude'] ) ? esc_attr( $_POST['latitude'] ) : null;
			$user_meta['_rtcl_longitude']   = ! empty( $_POST['longitude'] ) ? esc_attr( $_POST['longitude'] ) : null;
		} else {
			$location = [];
			if ( ! empty( $_POST['location'] ) && $state = absint( $_POST['location'] ) ) {
				$location[] = $state;
			}
			if ( ! empty( $_POST['sub_location'] ) && $city = absint( $_POST['sub_location'] ) ) {
				$location[] = $city;
			}
			if ( ! empty( $_POST['sub_sub_location'] ) && $town = absint( $_POST['sub_sub_location'] ) ) {
				$location[] = $town;
			}
			$user_meta['_rtcl_location'] = $location;
		}

		if ( isset( $_POST['social_media'] ) ) {
			delete_user_meta( $user_id, '_rtcl_social' );
			if ( is_array( $_POST['social_media'] ) && ! empty( $_POST['social_media'] ) ) {
				$_social = [];
				foreach ( $_POST['social_media'] as $_sm_key => $_sm_url ) {
					if ( ! empty( $_sm_url ) ) {
						$_social[ sanitize_text_field( $_sm_key ) ] = esc_url_raw( $_sm_url );
					}
				}
				if ( ! empty( $_social ) ) {
					$user_meta['_rtcl_social'] = $_social;
				}
			}
		}

		$user_meta = apply_filters( 'rtcl_update_user_account_data', $user_meta, $user_id, $_POST );

		if ( ! empty( $user_meta ) ) {
			foreach ( $user_meta as $metaKey => $metaValue ) {
				update_user_meta( $user_id, $metaKey, $metaValue );
			}
		}

		do_action( 'rtcl_update_user_account', $user_id, $_POST );
		wp_send_json_success( [ 'message' => esc_html__( 'Your account has been updated.', 'classified-listing' ) ] );
	}

	public function dropdown_terms() {
		if ( ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( 'Session Expired!!', 'classified-listing' ) );
		}
		$output = '';
		if ( isset( $_POST['taxonomy'] ) && isset( $_POST['parent'] ) ) {
			$args = [
				'taxonomy'  => sanitize_text_field( $_POST['taxonomy'] ),
				'base_term' => 0,
				'parent'    => (int) $_POST['parent'],
			];

			if ( isset( $_POST['class'] ) && '' != trim( $_POST['class'] ) ) {
				$args['class'] = sanitize_text_field( $_POST['class'] );
			}

			if ( $args['parent'] != $args['base_term'] ) {
				ob_start();
				Functions::dropdown_terms( $args );
				$output = ob_get_clean();
			}
		}
		wp_send_json_success( $output );
	}

	public function send_contact_email() {
		$error = new WP_Error();

		if ( ! apply_filters( 'rtcl_listing_form_remove_nonce', false )
		     && ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText )
		) {
			$error->add( 'rtcl_session_error', esc_html__( 'Session Expired!!', 'classified-listing' ) );
		}
		if ( ! Functions::is_human( 'contact' ) ) {
			$error->add( 'rtcl_recaptcha_error', esc_html__( 'Invalid Captcha: Please try again.', 'classified-listing' ) );
		}

		$post_id = (int) $_POST['post_id'];
		$name    = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
		$email   = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
		$phone   = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';

		$message = isset( $_POST['message'] ) ? stripslashes(
			wp_kses(
				nl2br( $_POST['message'] ),
				[
					'a'      => [
						'href'  => true,
						'title' => true,
					],
					'br'     => [],
					'ul'     => [],
					'ol'     => [],
					'li'     => [],
					'strong' => [],
				],
			),
		) : '';

		$data = [
			'post_id' => $post_id,
			'name'    => $name,
			'email'   => $email,
			'phone'   => $phone,
			'message' => $message,
		];

		$data = apply_filters( 'rtcl_listing_seller_contact_form_data', $data, $_POST, $_FILES, $error );

		do_action( 'rtcl_listing_seller_contact_form_validation', $error, $data );

		if ( is_wp_error( $error ) && ! empty( $error->errors ) ) {
			wp_send_json_error(
				[
					'error' => apply_filters( 'rtcl_seller_contact_form_error', $error->get_error_message(), $error ),
				],
			);
		}

		// All good to go
		if ( ! Functions::get_option_item( 'rtcl_email_notifications_settings', 'notify_users', 'disable_contact_email', 'multi_checkbox' ) ) {
			rtcl()->mailer()->emails['Listing_Contact_Email_To_Owner']->trigger( $post_id, $data );
		}
		if ( Functions::get_option_item( 'rtcl_email_notifications_settings', 'notify_admin', 'listing_contact', 'multi_checkbox' ) ) {
			rtcl()->mailer()->emails['Listing_Contact_Email_To_Admin']->trigger( $post_id, $data );
		}
		$notification = absint( get_post_meta( $post_id, '_notification_by_visitor', true ) ) + 1;
		update_post_meta( $post_id, '_notification_by_visitor', $notification );
		ListingStats::record( $post_id, 'contact' );
		$response['message'] = esc_html__( 'Your message sent successfully.', 'classified-listing' );
		wp_send_json_success( $response );
	}

	public function rtcl_delete_listing() {
		$success = false;
		$message = $msg_class = $redirect_url = $post_id = null;
		if ( wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) ) {
			$post_id = absint( Functions::request( 'post_id' ) );
			if ( $post_id && Functions::current_user_can( 'delete_' . rtcl()->post_type, $post_id ) ) {
				$children = get_children(
					apply_filters(
						'rtcl_before_delete_listing_attachment_query_args',
						[
							'post_parent'    => $post_id,
							'post_type'      => 'attachment',
							'posts_per_page' => - 1,
							'post_status'    => 'inherit',
						],
						$post_id,
					),
				);
				if ( ! empty( $children ) ) {
					foreach ( $children as $child ) {
						wp_delete_attachment( $child->ID, true );
					}
				}

				do_action( 'rtcl_before_delete_listing', $post_id );
				Functions::delete_post( $post_id );
				$success      = true;
				$message      .= esc_html__( 'Successfully deleted.', 'classified-listing' );
				$redirect_url = Link::get_account_endpoint_url( 'listings' );
			} else {
				$message .= esc_html__( 'Permission Error.', 'classified-listing' );
			}
		} else {
			$message .= esc_html__( 'Session expired.', 'classified-listing' );
		}

		wp_send_json(
			apply_filters(
				'rtcl_delete_listing_ajax_response',
				[
					'success'      => $success,
					'post_id'      => $post_id,
					'message'      => $message,
					'redirect_url' => $redirect_url,
				],
			),
		);
	}

	public function rtcl_report_abuse() {
		$error = new WP_Error();

		if ( ! apply_filters( 'rtcl_listing_form_remove_nonce', false )
		     && ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText )
		) {
			$error->add( 'rtcl_session_error', esc_html__( 'Session Expired!!', 'classified-listing' ) );
		}
		if ( ! Functions::is_human( 'report_abuse' ) ) {
			$error->add( 'rtcl_recaptcha_error', esc_html__( 'Invalid Captcha: Please try again.', 'classified-listing' ) );
		}
		$post_id = (int) $_POST['post_id'];
		$message = esc_textarea( $_POST['message'] );
		$data = [
			'post_id' => $post_id,
			'message' => $message,
		];
		do_action( 'rtcl_listing_report_abuse_form_validation', $error, $data );

		if ( is_wp_error( $error ) && ! empty( $error->errors ) ) {
			wp_send_json_error(
				[
					'error' => apply_filters( 'rtcl_listing_report_abuse_form_error', $error->get_error_message(), $error ),
				],
			);
		}

		$is_send = rtcl()->mailer()->emails['Report_Abuse_Email_To_Admin']->trigger( $post_id, $data );
		if ( ! $is_send ) {
			wp_send_json_error(
				[
					'error' => esc_html__( 'Sorry! Please try again.', 'classified-listing' ),
				],
			);
		}
		$notification = absint( get_post_meta( $post_id, '_abuse_report_by_visitor', true ) ) + 1;
		update_post_meta( $post_id, '_abuse_report_by_visitor', $notification );
		wp_send_json_success(
			[
				'message' => esc_html__( 'Your message sent successfully.', 'classified-listing' ),
			],
		);
	}

	public function rtcl_add_remove_favorites() {
		$success = false;
		$message = null;
		$action  = null;
		$post_id = 0;
		if ( apply_filters( 'rtcl_listing_form_remove_nonce', false )
		     || wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText )
		) {
			$post_id = ! empty( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
			if ( $post_id ) {
				$favourites = (array) get_user_meta( get_current_user_id(), 'rtcl_favourites', true );

				if ( in_array( $post_id, $favourites ) ) {
					if ( ( $key = array_search( $post_id, $favourites ) ) !== false ) {
						unset( $favourites[ $key ] );
						$action  = 'removed';
						$message = esc_html__( 'Removed from your favourites.', 'classified-listing' );
					}
				} else {
					$favourites[] = $post_id;
					$action       = 'added';
					$message      = esc_html__( 'Added to your favourites list.', 'classified-listing' );
				}

				$favourites = array_filter( $favourites );
				$favourites = array_values( $favourites );
				update_user_meta( get_current_user_id(), 'rtcl_favourites', $favourites );
				$success = true;
			} else {
				$message = esc_html__( 'Add post id to remove', 'classified-listing' );
			}
		} else {
			$message = esc_html__( 'Session Expired!', 'classified-listing' );
		}
		wp_send_json(
			[
				'success' => $success,
				'post_id' => $post_id,
				'action'  => $action,
				'html'    => Functions::get_favourites_link( $post_id ),
				'message' => $message,
			],
		);
	}

	public function rtcl_post_new_listing() {
		Functions::clear_notices();// Clear previous notice
		$success = false;
		$post_id = 0;
		$type    = 'new';
		if ( apply_filters( 'rtcl_listing_form_remove_nonce', false )
		     || wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText )
		) {
			if ( ! Functions::is_human( 'listing' ) ) {
				Functions::add_notice(
					apply_filters(
						'rtcl_listing_form_recaptcha_error_text',
						esc_html__( 'Invalid Captcha: Please try again.', 'classified-listing' ),
						$_REQUEST,
					),
					'error',
				);
			}

			$agree = isset( $_POST['rtcl_agree'] ) ? 1 : null;
			if ( Functions::is_enable_terms_conditions() && ! $agree ) {
				Functions::add_notice(
					apply_filters(
						'rtcl_listing_form_terms_conditions_text_responses',
						esc_html__( 'Please agree with the terms and conditions.', 'classified-listing' ),
						$_REQUEST,
					),
					'error',
				);
			} else {
				$raw_cat_id   = isset( $_POST['_category_id'] ) ? absint( $_POST['_category_id'] ) : 0;
				$listing_type = isset( $_POST['_ad_type'] ) && in_array( $_POST['_ad_type'], array_keys( Functions::get_listing_types() ) )
					? esc_attr( $_POST['_ad_type'] ) : '';
				$post_id      = is_user_logged_in() ? absint( Functions::request( '_post_id' ) ) : 0;
				if ( ! $raw_cat_id && ! $post_id ) {
					Functions::add_notice(
						apply_filters(
							'rtcl_listing_form_category_not_select_responses',
							sprintf(
							/* translators:  Category not selected */
								esc_html__( 'Category not selected. <a href="%s">Click here to set category</a>', 'classified-listing' ),
								Link::get_listing_form_page_link(),
							),
						),
						'error',
					);
				} else {
					// Check if user has not any post remaining
					do_action( 'rtcl_before_add_edit_listing_before_category_condition', $post_id );
					$category_id = 0;
					if ( Functions::is_gallery_image_required() && ( ! $post_id || ! count( Functions::get_listing_images( $post_id ) ) ) ) {
						Functions::add_notice(
							esc_html__( 'Image is required. Please select an image.', 'classified-listing' ),
							'error',
							'rtcl_listing_gallery_image_required',
						);
					}
					if ( ! Functions::notice_count( 'error' ) ) {
						if ( ( ! $post_id || ( ( $post = get_post( $post_id ) ) && $post->post_type == rtcl()->post_type ) && $post->post_status = 'rtcl-temp' )
						     && $raw_cat_id
						) {
							$category = get_term_by( 'id', $raw_cat_id, rtcl()->category );
							if ( is_a( $category, WP_Term::class ) ) {
								$category_id = $category->term_id;
								$parent_id   = Functions::get_term_top_most_parent_id( $category_id, rtcl()->category );
								if ( Functions::term_has_children( $category_id ) ) {
									Functions::add_notice( esc_html__( 'Please select ad type and category', 'classified-listing' ), 'error' );
								}
								if ( ! Functions::is_ad_type_disabled() && ! $listing_type ) {
									Functions::add_notice( esc_html__( 'Please select an ad type', 'classified-listing' ), 'error' );
								}
								$cats_on_type = wp_list_pluck( Functions::get_one_level_categories( 0, $listing_type ), 'term_id' );
								if ( ! in_array( $parent_id, $cats_on_type ) ) {
									Functions::add_notice( esc_html__( 'Please select correct type and category', 'classified-listing' ), 'error' );
								}
								do_action( 'rtcl_before_add_edit_listing_into_category_condition', $post_id, $category_id );
							} else {
								Functions::add_notice( esc_html__( 'Category is not valid', 'classified-listing' ), 'error' );
							}
						}
						if ( ! $post_id && ! $category_id ) {
							Functions::add_notice( __( 'Category not selected', 'classified-listing' ), 'error' );
						}
					}
					if ( ! Functions::notice_count( 'error' ) ) {
						$cats = [ $category_id ];

						$meta = [];
						if ( Functions::is_enable_terms_conditions() && $agree ) {
							$meta['rtcl_agree'] = 1;
						}
						if ( isset( $_POST['_rtcl_listing_pricing'] ) && $listing_pricing_type = sanitize_text_field( $_POST['_rtcl_listing_pricing'] ) ) {
							$meta['_rtcl_listing_pricing'] = in_array( $listing_pricing_type, array_keys( Options::get_listing_pricing_types() ) )
								? $listing_pricing_type : 'price';
							if ( isset( $_POST['_rtcl_max_price'] ) && 'range' === $listing_pricing_type ) {
								$meta['_rtcl_max_price'] = Functions::format_decimal( $_POST['_rtcl_max_price'] );
							}
						}
						if ( isset( $_POST['price_type'] ) ) {
							$meta['price_type'] = Functions::sanitize( $_POST['price_type'] );
						}
						if ( isset( $_POST['price'] ) ) {
							$meta['price'] = Functions::format_decimal( $_POST['price'] );
						}
						if ( isset( $_POST['_rtcl_price_unit'] ) ) {
							$meta['_rtcl_price_unit'] = Functions::sanitize( $_POST['_rtcl_price_unit'] );
						}

						if ( ! Functions::is_video_urls_disabled() && isset( $_POST['_rtcl_video_urls'] ) ) {
							$meta['_rtcl_video_urls'] = Functions::sanitize( $_POST['_rtcl_video_urls'], 'video_urls' );
						}

						if ( 'geo' === Functions::location_type() ) {
							if ( isset( $_POST['rtcl_geo_address'] ) ) {
								$meta['_rtcl_geo_address'] = Functions::sanitize( $_POST['rtcl_geo_address'] );
							}
						} else {
							if ( isset( $_POST['zipcode'] ) ) {
								$meta['zipcode'] = Functions::sanitize( $_POST['zipcode'] );
							}
							if ( isset( $_POST['address'] ) ) {
								$meta['address'] = Functions::sanitize( $_POST['address'] );
							}
						}

						if ( isset( $_POST['phone'] ) ) {
							$meta['phone'] = Functions::sanitize( $_POST['phone'] );
						}
						if ( isset( $_POST['_rtcl_whatsapp_number'] ) ) {
							$meta['_rtcl_whatsapp_number'] = Functions::sanitize( $_POST['_rtcl_whatsapp_number'] );
						}
						if ( isset( $_POST['_rtcl_telegram'] ) ) {
							$meta['_rtcl_telegram'] = Functions::sanitize( $_POST['_rtcl_telegram'] );
						}
						if ( isset( $_POST['email'] ) ) {
							$meta['email'] = Functions::sanitize( $_POST['email'], 'email' );
						}
						if ( isset( $_POST['website'] ) ) {
							$meta['website'] = Functions::sanitize( $_POST['website'], 'url' );
						}
						if ( isset( $_POST['latitude'] ) ) {
							$meta['latitude'] = Functions::sanitize( $_POST['latitude'] );
						}
						if ( isset( $_POST['longitude'] ) ) {
							$meta['longitude'] = Functions::sanitize( $_POST['longitude'] );
						}

						$meta['hide_map']    = isset( $_POST['hide_map'] ) ? 1 : null;
						$title               = isset( $_POST['title'] ) ? Functions::sanitize( $_POST['title'], 'title' ) : '';
						$post_arg            = [
							'post_title'   => $title,
							'post_content' => isset( $_POST['description'] ) ? Functions::sanitize( $_POST['description'], 'content' ) : '',
						];
						$post                = get_post( $post_id );
						$user_id             = get_current_user_id();
						$post_for_unregister = Functions::is_enable_post_for_unregister();
						if ( ! is_user_logged_in() && $post_for_unregister ) {
							$new_user_id = Functions::do_registration_from_listing_form( [ 'email' => $meta['email'] ] );
							if ( $new_user_id && is_numeric( $new_user_id ) ) {
								$user_id = $new_user_id;
								/* translators:  Account registered email sent  */
								Functions::add_notice(
									apply_filters(
										'rtcl_listing_new_registration_success_message',
										sprintf(
										// translators: Email address
											esc_html__( 'A new account is registered, password is sent to your email(%s).', 'classified-listing' ),
											$meta['email'],
										),
										$meta['email'],
									),
								);
							}
						}
						if ( $user_id ) {
							$new_listing_status = Functions::get_option_item( 'rtcl_general_settings', 'new_listing_status', 'pending' );
							if ( $post_id && is_object( $post ) && $post->post_type == rtcl()->post_type ) {
								if ( Functions::current_user_can( 'edit_' . rtcl()->post_type, $post_id ) ) {
									if ( $post->post_status === 'rtcl-temp' ) {
										$post_arg['post_name']   = $title;
										$post_arg['post_status'] = $new_listing_status;
									} else {
										$type              = 'update';
										$status_after_edit = Functions::get_option_item( 'rtcl_general_settings', 'edited_listing_status' );
										if ( 'publish' === $post->post_status && $status_after_edit && $post->post_status !== $status_after_edit ) {
											$post_arg['post_status'] = $status_after_edit;
										}
									}

									$post_arg['ID'] = $post_id;
									$success        = wp_update_post( apply_filters( 'rtcl_listing_save_update_args', $post_arg, $type ) );
								}
							} else {
								$post_arg['post_status'] = $new_listing_status;
								$post_arg['post_author'] = $user_id;
								$post_arg['post_type']   = rtcl()->post_type;
								$post_id                 = $success = wp_insert_post( apply_filters( 'rtcl_listing_save_update_args', $post_arg, $type ) );
							}

							if ( $success && $post_id ) {
								if ( isset( $_POST['rtcl_listing_tag'] ) ) {
									$tags          = Functions::sanitize( $_POST['rtcl_listing_tag'] );
									$tags_as_array = ! empty( $tags ) ? explode( ',', $tags ) : [];
									wp_set_object_terms( $post_id, $tags_as_array, rtcl()->tag );
								}

								if ( $type == 'new' ) {
									wp_set_object_terms( $post_id, $cats, rtcl()->category );
									$meta['ad_type'] = $listing_type;
								}
								if ( 'local' === Functions::location_type() ) {
									$locations = [];
									if ( $loc = Functions::request( 'location' ) ) {
										$locations[] = absint( $loc );
									}
									if ( $loc = Functions::request( 'sub_location' ) ) {
										$locations[] = absint( $loc );
									}
									if ( $loc = Functions::request( 'sub_sub_location' ) ) {
										$locations[] = absint( $loc );
									}
									wp_set_object_terms( $post_id, $locations, rtcl()->location );
								}

								// Custom Meta field
								if ( isset( $_POST['rtcl_fields'] ) ) {
									foreach ( $_POST['rtcl_fields'] as $key => $value ) {
										$field_id = (int) str_replace( '_field_', '', $key );
										if ( $field = rtcl()->factory->get_custom_field( $field_id ) ) {
											$field->saveSanitizedValue( $post_id, $value );
										}
									}
								}

								/* meta data */
								if ( ! empty( $meta ) ) {
									foreach ( $meta as $key => $value ) {
										update_post_meta( $post_id, $key, $value );
									}
								}
							}

							// send emails

							if ( $success && $post_id && ( $listing = rtcl()->factory->get_listing( $post_id ) ) ) {
								if ( $type == 'new' ) {
									update_post_meta( $post_id, 'featured', 0 );
									update_post_meta( $post_id, '_views', 0 );
									$current_user_id = get_current_user_id();
									$ads             = absint( get_user_meta( $current_user_id, '_rtcl_ads', true ) );
									update_user_meta( $current_user_id, '_rtcl_ads', $ads + 1 );
									if ( 'publish' === $new_listing_status ) {
										Functions::add_default_expiry_date( $post_id );
									}
									Functions::add_notice(
										apply_filters(
											'rtcl_listing_success_message',
											esc_html__( 'Thank you for submitting your ad!', 'classified-listing' ),
											$post_id,
											$type,
											$_REQUEST,
										),
									);
								} elseif ( $type == 'update' ) {
									Functions::add_notice(
										apply_filters(
											'rtcl_listing_success_message',
											esc_html__( 'Successfully updated !!!', 'classified-listing' ),
											$post_id,
											$type,
											$_REQUEST,
										),
									);
								}

								do_action(
									'rtcl_listing_form_after_save_or_update',
									$listing,
									$type,
									$category_id,
									$new_listing_status,
									[
										'data'  => $_REQUEST,
										'files' => $_FILES,
									],
								);
							} else {
								Functions::add_notice(
									apply_filters( 'rtcl_listing_error_message', esc_html__( 'Error!!', 'classified-listing' ), $_REQUEST ),
									'error',
								);
							}
						}
					}
				}
			}
		} else {
			Functions::add_notice(
				apply_filters( 'rtcl_listing_session_error_message', esc_html__( 'Session Error !!', 'classified-listing' ), $_REQUEST ),
				'error',
			);
		}

		$message = Functions::get_notices( 'error' );
		if ( $success ) {
			$message = Functions::get_notices( 'success' );
		}
		Functions::clear_notices(); // Clear all notice created by checkin

		wp_send_json(
			apply_filters(
				'rtcl_listing_form_after_save_or_update_responses',
				[
					'message'      => $message,
					'success'      => $success,
					'post_id'      => $post_id,
					'type'         => $type,
					'redirect_url' => apply_filters(
						'rtcl_listing_form_after_save_or_update_responses_redirect_url',
						Functions::get_listing_redirect_url_after_edit_post( $type, $post_id, $success ),
						$type,
						$post_id,
						$success,
						$message,
					),
				],
			),
		);
	}

	public function rtcl_get_one_level_category_select_list() {
		if ( ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) ) {
			wp_send_json(
				apply_filters(
					'rtcl_ajax_category_selection_before_post',
					[
						'message'    => esc_html__( 'Session error !!', 'classified-listing' ),
						'success'    => false,
						'child_cats' => null,
						'cat_id'     => isset( $_POST['term_id'] ) ? absint( $_POST['term_id'] ) : 0,
					],
				),
			);
		}

		Functions::clear_notices();
		do_action( 'rtcl_set_local' );
		$success    = false;
		$message    = [];
		$cat_id     = isset( $_POST['term_id'] ) ? absint( $_POST['term_id'] ) : 0;
		$child_cats = null;
		if ( $cat_id ) {
			$success   = true;
			$childCats = Functions::get_one_level_categories( $cat_id );
			if ( ! empty( $childCats ) ) {
				$child_cats .= sprintf( "<option value=''>%s</option>", esc_html( Text::get_select_category_text() ) );
				foreach ( $childCats as $child_cat ) {
					$child_cats .= "<option value='{$child_cat->term_id}'>{$child_cat->name}</option>";
				}
			}
		} else {
			Functions::add_notice( __( 'Category not selected.', 'classified-listing' ), 'error' );
		}
		if ( Functions::notice_count( 'error' ) ) {
			$message = Functions::get_notices( 'error' );
		}
		Functions::clear_notices();
		$response = [
			'message'    => $message,
			'success'    => $success,
			'child_cats' => $child_cats,
			'cat_id'     => $cat_id,
		];
		wp_send_json( apply_filters( 'rtcl_ajax_category_selection_before_post', $response ) );
	}

	public function rtcl_get_one_level_category_select_list_by_type() {
		if ( ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) ) {
			wp_send_json(
				[
					'message' => esc_html__( 'Session error !!', 'classified-listing' ),
					'success' => false,
					'cats'    => null,
				],
			);
		}

		Functions::clear_notices();
		$success    = false;
		$message    = [];
		$type       = ( isset( $_POST['type'] ) && in_array( $_POST['type'], array_keys( Functions::get_listing_types() ) ) ) ? $_POST['type'] : null;
		$child_cats = null;

		do_action( 'rtcl_listing_form_before_category_retrieve', $type );

		if ( $type ) {
			$childCats = Functions::get_one_level_categories( 0, $type );
			if ( ! empty( $childCats ) ) {
				$success    = true;
				$child_cats .= sprintf( "<option value=''>%s</option>", esc_html( Text::get_select_category_text() ) );
				foreach ( $childCats as $child_cat ) {
					$child_cats .= "<option value='{$child_cat->term_id}'>{$child_cat->name}</option>";
				}
			} else {
				Functions::add_notice( __( 'No category found.', 'classified-listing' ), 'error' );
			}
		} else {
			Functions::add_notice( __( 'Type is not selected.', 'classified-listing' ), 'error' );
		}
		if ( Functions::notice_count( 'error' ) ) {
			$message = Functions::get_notices( 'error' );
		}
		Functions::clear_notices();
		$response = [
			'message' => $message,
			'success' => $success,
			'cats'    => $child_cats,
		];
		wp_send_json( $response );
	}

	static function rtcl_user_ad_load_more() {
		$complete       = false;
		$html           = '';
		$current_page   = isset( $_POST['current_page'] ) ? absint( $_POST['current_page'] ) : 0;
		$max_num_pages  = isset( $_POST['max_num_pages'] ) ? absint( $_POST['max_num_pages'] ) : 0;
		$user_id        = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;
		$posts_per_page = isset( $_POST['posts_per_page'] ) ? absint( $_POST['posts_per_page'] ) : - 1;

		if ( $current_page && $max_num_pages && $user_id && $max_num_pages > $current_page ) {
			$current_page ++;
			$complete       = true;
			$args           = [
				'post_type'      => rtcl()->post_type,
				'post_status'    => 'publish',
				'posts_per_page' => $posts_per_page ? $posts_per_page : - 1,
				'author'         => $user_id,
				'paged'          => $current_page,
				'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				                      [
					                      'key'     => '_rtcl_manager_id',
					                      'compare' => 'NOT EXISTS',
				                      ],
				],
			];
			$user_ads_query = new \WP_Query( $args );

			if ( ! empty( $user_ads_query->posts ) ) {
				$global_listing        = null;
				$GLOBALS['rtclIsAjax'] = true;
				if ( isset( $GLOBALS['listing'] ) ) {
					$global_listing = $GLOBALS['listing'];
				}
				$view = 'list';
				foreach ( $user_ads_query->posts as $post_id ) {
					$GLOBALS['listing'] = rtcl()->factory->get_listing( $post_id );
					ob_start();
					Functions::get_template( 'content-listing', compact( 'view', 'post_id' ) );
					$html .= ob_get_clean();
				}
				if ( $global_listing ) {
					$GLOBALS['listing'] = $global_listing;
				} else {
					unset( $GLOBALS['listing'] );
				}
				unset( $GLOBALS['rtclIsAjax'] );
			}
		} else {
			$current_page = $max_num_pages;
		}

		wp_send_json(
			[
				'complete'     => $complete,
				'current_page' => $current_page,
				'html'         => $html,
			],
		);
	}
}
