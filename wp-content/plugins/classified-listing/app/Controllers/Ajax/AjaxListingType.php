<?php

namespace Rtcl\Controllers\Ajax;

use Rtcl\Helpers\Functions;

class AjaxListingType {

	function __construct() {
		add_action( 'wp_ajax_rtcl_ajax_add_listing_type', [ $this, 'rtcl_ajax_add_listing_type' ] );
		add_action( 'wp_ajax_rtcl_ajax_delete_listing_type', [ $this, 'rtcl_ajax_delete_listing_type' ] );
		add_action( 'wp_ajax_rtcl_ajax_update_listing_type', [ $this, 'rtcl_ajax_update_listing_type' ] );
		add_action( 'wp_ajax_rtcl_ajax_sort_ad_types', [ $this, 'sort_ad_types' ] );
	}

	function sort_ad_types() {
		if ( !current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'Permission denied.', 'classified-listing' ) );
		}

		if ( !wp_verify_nonce( isset( $_REQUEST[rtcl()->nonceId] ) ? $_REQUEST[rtcl()->nonceId] : null, rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( 'Session error!!', 'classified-listing' ) );
		}
		$types = $_POST['types'] ?? [];
		$ad_types = [];
		$ExistTypes = Functions::get_listing_types();
		foreach ( $types as $id => $name ) {
			$id = Functions::sanitize_title_with_underscores( $id );
			if ( isset( $ExistTypes[$id] ) ) {
				$ad_types[$id] = sanitize_text_field( $name );
			}
		}
		if ( !empty( $ad_types ) ) {
			update_option( rtcl()->get_listing_types_option_id(), $ad_types );
		}

		wp_send_json_success( __( 'Successfully updated.', 'classified-listing' ) );
	}

	function rtcl_ajax_add_listing_type() {
		if ( !current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'Permission denied.', 'classified-listing' ) );
		}

		if ( !wp_verify_nonce( isset( $_REQUEST[rtcl()->nonceId] ) ? $_REQUEST[rtcl()->nonceId] : null, rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( 'Session error!!', 'classified-listing' ) );
		}

		$type = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '';
		if ( !$type ) {
			wp_send_json_error( esc_html__( 'Type field is required', 'classified-listing' ) );
		}

		$id = Functions::sanitize_title_with_underscores( $type );
		$types = Functions::get_listing_types();
		if ( isset( $types[$id] ) ) {
			wp_send_json_error( esc_html__( 'This type already exist.', 'classified-listing' ) );
		} else {
			$types[$id] = $type;
			update_option( rtcl()->get_listing_types_option_id(), $types );
			do_action( 'rtcl_after_save_ad_type', $type, $_POST );
			$data = [
				'id'   => $id,
				'name' => $type
			];
		}

		wp_send_json_success( [ 'message' => esc_html__( 'Successfully added.', 'classified-listing' ), 'data' => $data ] );
	}

	function rtcl_ajax_update_listing_type() {

		if ( !current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'Permission denied.', 'classified-listing' ) );
		}

		if ( !wp_verify_nonce( isset( $_REQUEST[rtcl()->nonceId] ) ? $_REQUEST[rtcl()->nonceId] : null, rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( 'Session error!!', 'classified-listing' ) );
		}

		$old_id = isset( $_POST['old_id'] ) ? sanitize_text_field( $_POST['old_id'] ) : '';
		$id = isset( $_POST['id'] ) ? sanitize_text_field( $_POST['id'] ) : '';
		$name = isset( $_POST['name'] ) ? sanitize_text_field( stripslashes( $_POST['name'] ) ) : '';
		if ( !$old_id || !$id || !$name ) {
			wp_send_json_error( esc_html__( 'Missing required data.', 'classified-listing' ) );
		}

		$old_id = Functions::sanitize_title_with_underscores( $old_id );
		$id = Functions::sanitize_title_with_underscores( $id );
		$types = Functions::get_listing_types();
		if ( empty( $types ) ) {
			wp_send_json_error( esc_html__( 'No types found.', 'classified-listing' ) );
		}
		$data = [];
		$old_exist = false;
		if ( isset( $types[$old_id] ) ) {
			$data = [
				'id'   => $old_id,
				'name' => $types[$old_id]
			];
			$old_exist = true;
		}
		if ( apply_filters( 'rtcl_listing_type_update_no_change', true ) && $old_id === $id && $types[$old_id] === $name ) {
			wp_send_json_error( esc_html__( 'No change found.', 'classified-listing' ) );
		} elseif ( $old_id === $id && $old_exist ) {
			$types[$id] = $name;
			$data = [
				'id'   => $id,
				'name' => $name
			];
			update_option( rtcl()->get_listing_types_option_id(), $types );
			do_action( 'rtcl_after_save_ad_type', $id, $_POST );
		} elseif ( $old_id !== $id && $old_exist && $types[$id] ) {
			wp_send_json_error( esc_html__( 'This type is already exist.', 'classified-listing' ) );
		} elseif ( $old_id !== $id && $old_exist ) {
			$new_types = [];
			foreach ( $types as $typeId => $type ) {
				if ( $typeId == $old_id ) {
					$new_types[$id] = $name;
					$data = [
						'id'   => $id,
						'name' => $name
					];
				} else {
					$new_types[$typeId] = $type;
				}
			}
			update_option( rtcl()->get_listing_types_option_id(), $new_types );
			do_action( 'rtcl_after_save_ad_type', $id, $_POST );
		}
		if ( empty( $data ) ) {
			wp_send_json_error( esc_html__( 'Unknown error!', 'classified-listing' ) );
		}
		wp_send_json_success(
			[
				'message' => esc_html__( 'Successfully updated', 'classified-listing' ),
				'data'    => $data
			]
		);
	}

	function rtcl_ajax_delete_listing_type() {

		if ( !current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'Permission denied.', 'classified-listing' ) );
		}

		if ( !wp_verify_nonce( isset( $_REQUEST[rtcl()->nonceId] ) ? $_REQUEST[rtcl()->nonceId] : null, rtcl()->nonceText ) ) {
			wp_send_json_error( esc_html__( 'Session error!!', 'classified-listing' ) );
		}
		$id = isset( $_POST['id'] ) ? sanitize_text_field( $_POST['id'] ) : '';
		$types = Functions::get_listing_types();
		if ( !$id || !isset( $types[$id] ) ) {
			wp_send_json_error( esc_html__( 'No type found to delete', 'classified-listing' ) );

		}
		unset( $types[$id] );
		update_option( rtcl()->get_listing_types_option_id(), $types );
		do_action( 'rtcl_after_delete_ad_type', $id );
		wp_send_json_success(
			[
				'message' => __( 'Successfully deleted', 'classified-listing' ),
			]
		);
	}
}