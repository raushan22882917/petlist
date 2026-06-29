<?php

namespace RtclPro\Api\V1;

use Rtcl\Helpers\Functions;
use RtclPro\Helpers\Api;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

class V1_PrivacyApi {
	public function register_routes() {

		register_rest_route( 'rtcl/v1', 'privacy/user/block', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'getBlockedUserList' ],
				'permission_callback' => [ Api::class, 'permission_check' ]
			],
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'addUserToBlockList' ],
				'permission_callback' => [ Api::class, 'permission_check' ],
				'args'                => [
					'user_id' => [
						'required'    => true,
						'type'        => 'integer',
						'description' => esc_html__( 'user_id id is required', 'classified-listing-pro' ),
					]
				]
			],
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [ $this, 'deleteUserFromBlockList' ],
				'permission_callback' => [ Api::class, 'permission_check' ],
				'args'                => [
					'user_id' => [
						'required'    => true,
						'type'        => 'integer',
						'description' => esc_html__( 'user_id id is required', 'classified-listing-pro' ),
					]
				]
			]
		] );

		register_rest_route( 'rtcl/v1', 'privacy/listing/block', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'getBlockedListingList' ],
				'permission_callback' => [ Api::class, 'permission_check' ]
			],
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'addListingToBlockList' ],
				'permission_callback' => [ Api::class, 'permission_check' ],
				'args'                => [
					'listing_id' => [
						'required'    => true,
						'type'        => 'integer',
						'description' => esc_html__( 'listing_id id is required', 'classified-listing-pro' ),
					]
				]
			],
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [ $this, 'deleteListingFromBlockList' ],
				'permission_callback' => [ Api::class, 'permission_check' ],
				'args'                => [
					'listing_id' => [
						'required'    => true,
						'type'        => 'integer',
						'description' => esc_html__( 'listing_id id is required', 'classified-listing-pro' ),
					]
				]
			]
		] );
	}

	public function getBlockedUserList( WP_REST_Request $request ) {
		Api::is_valid_auth_request();
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return new WP_Error( "rtcl_rest_authentication_error", __( 'You are not logged in.', 'classified-listing-pro' ), [ 'status' => 404 ] );
		}

		$blockedUserList = Functions::getBlockedUserList( $user_id );

		return rest_ensure_response( $blockedUserList );
	}

	public function addUserToBlockList( WP_REST_Request $request ) {
		Api::is_valid_auth_request();
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return new WP_Error( "rtcl_rest_authentication_error", __( 'You are not logged in.', 'classified-listing-pro' ), [ 'status' => 404 ] );
		}

		$blocked_user_id = $request->get_param( 'user_id' );
		$blockedUser     = get_user_by( 'id', $blocked_user_id );
		if ( ! $blockedUser ) {
			return new WP_Error( "rtcl_rest_validation_error", __( 'User not found.', 'classified-listing-pro' ), [ 'status' => 404 ] );
		}

		$blockedUserIds   = Functions::getBlockedUserIds( $user_id );
		$blockedUserIds[] = $blocked_user_id;
		Functions::updateBlockedUserIds( $user_id, $blockedUserIds );
		$blockedUserList = Functions::getBlockedUserList( $user_id );

		return rest_ensure_response( $blockedUserList );
	}

	public function deleteUserFromBlockList( WP_REST_Request $request ) {
		Api::is_valid_auth_request();
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return new WP_Error( "rtcl_rest_authentication_error", __( 'You are not logged in.', 'classified-listing-pro' ), [ 'status' => 404 ] );
		}

		$blocked_user_id = $request->get_param( 'user_id' );
		$blockedUser     = get_user_by( 'id', $blocked_user_id );
		if ( ! $blockedUser ) {
			return new WP_Error( "rtcl_rest_validation_error", __( 'User not found.', 'classified-listing-pro' ), [ 'status' => 404 ] );
		}

		$blockedUserIds = Functions::getBlockedUserIds( $user_id );
		if ( empty( $blockedUserIds ) || !in_array( $blocked_user_id, $blockedUserIds ) ) {
			return new WP_Error( "rtcl_rest_validation_error", __( 'User not exist at blocked list.', 'classified-listing-pro' ), [ 'status' => 404 ] );
		}
		$blockedUserIds = array_filter( $blockedUserIds, function ( $id ) use ( $blocked_user_id ) {
			return $id !== $blocked_user_id;
		} );
		Functions::updateBlockedUserIds( $user_id, $blockedUserIds );
		$blockedUserList = Functions::getBlockedUserList( $user_id );

		return rest_ensure_response( $blockedUserList );
	}

	public function getBlockedListingList( WP_REST_Request $request ) {
		Api::is_valid_auth_request();
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return new WP_Error( "rtcl_rest_authentication_error", __( 'You are not logged in.', 'classified-listing-pro' ), [ 'status' => 404 ] );
		}

		$blockedListingList = Functions::getBlockedListingList( $user_id );

		return rest_ensure_response( $blockedListingList );
	}

	public function addListingToBlockList( WP_REST_Request $request ) {
		Api::is_valid_auth_request();
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return new WP_Error( "rtcl_rest_authentication_error", __( 'You are not logged in.', 'classified-listing-pro' ), [ 'status' => 404 ] );
		}

		$blocked_listing_id = $request->get_param( 'listing_id' );
		$blockedListing     = rtcl()->factory->get_listing($blocked_listing_id);
		if ( ! $blockedListing ) {
			return new WP_Error( "rtcl_rest_validation_error", __( 'Listing not found.', 'classified-listing-pro' ), [ 'status' => 404 ] );
		}

		$blockedListingIds   = Functions::getBlockedListingIds( $user_id );
		$blockedListingIds[] = $blocked_listing_id;
		Functions::updateBlockedListingIds( $user_id, $blockedListingIds );
		$blockedListingList = Functions::getBlockedListingList( $user_id );

		return rest_ensure_response( $blockedListingList );
	}

	public function deleteListingFromBlockList( WP_REST_Request $request ) {
		Api::is_valid_auth_request();
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return new WP_Error( "rtcl_rest_authentication_error", __( 'You are not logged in.', 'classified-listing-pro' ), [ 'status' => 404 ] );
		}

		$blocked_listing_id = $request->get_param( 'listing_id' );
		$blockedListing     = rtcl()->factory->get_listing($blocked_listing_id);
		if ( ! $blockedListing ) {
			return new WP_Error( "rtcl_rest_validation_error", __( 'Listing not found.', 'classified-listing-pro' ), [ 'status' => 404 ] );
		}

		$blockedListingIds = Functions::getBlockedListingIds( $user_id );
		if ( empty( $blockedListingIds ) || !in_array( $blocked_listing_id, $blockedListingIds ) ) {
			return new WP_Error( "rtcl_rest_validation_error", __( 'Listing not exist at blocked list.', 'classified-listing-pro' ), [ 'status' => 404 ] );
		}
		$blockedListingIds = array_filter( $blockedListingIds, function ( $id ) use ( $blocked_listing_id ) {
			return $id !== $blocked_listing_id;
		} );
		Functions::updateBlockedListingIds( $user_id, $blockedListingIds );
		$blockedListingList = Functions::getBlockedListingList( $user_id );

		return rest_ensure_response( $blockedListingList );
	}

}