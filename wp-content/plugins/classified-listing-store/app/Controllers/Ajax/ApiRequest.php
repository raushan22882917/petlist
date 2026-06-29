<?php
/**
 * Created by PhpStorm.
 * User: mahbubur
 * Date: 7/24/18
 * Time: 12:10 PM
 */

namespace RtclStore\Controllers\Ajax;


use Rtcl\Helpers\Functions;

class ApiRequest {

	public static function init() {
		add_action( 'wp_ajax_rtcl_get_all_membership_list', [ __CLASS__, 'rtcl_get_all_membership_list' ] );
		add_action( 'wp_ajax_rtcl_delete_membership', [ __CLASS__, 'rtcl_delete_membership' ] );
		add_action( 'wp_ajax_rtcl_update_membership_data', [ __CLASS__, 'rtcl_update_membership_data' ] );
	}

	public static function rtcl_update_membership_data() {
		$success = false;
		$message = [];
		if ( Functions::verify_nonce() ) {
			$id    = ! empty( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
			$key   = ! empty( $_POST['key'] ) && in_array( $_POST['key'], [
				'ads',
				'posted_ads',
				'expiry_date'
			] ) ? sanitize_key( $_POST['key'] ) : '';
			$value = ! empty( $_POST['value'] ) ? sanitize_text_field( $_POST['value'] ) : '';
			if ( $id && $key && $value ) {
				$success = true;
				global $wpdb;
				if ( $wpdb->update(
					$wpdb->prefix . 'rtcl_membership',
					[ $key => $value ],
					[ 'id' => $id ],
					in_array( $key, [ 'ads', 'posted_ads' ] ? [ '%d' ] : [ '%s' ] )
				) ) {
					$success = true;
				}
			} else {
				array_push( $message, __( "Please select a column to update", "classified-listing-store" ) );
			}
		} else {
			array_push( $message, __( "Session not valid", "classified-listing-store" ) );
		}
		wp_send_json( [
			'success' => $success,
			'message' => $message
		] );
	}

	public static function rtcl_delete_membership() {
		$success = false;
		$message = $ids = [];
		if ( Functions::verify_nonce() ) {
			$ready_ids = ! empty( $_POST['ids'] ) && is_array( $_POST['ids'] ) ? $_POST['ids'] : [];
			if ( ! empty( $ready_ids ) ) {
				$success = true;
				global $wpdb;
				foreach ( $ready_ids as $id ) {
					if ( $wpdb->delete( $wpdb->prefix . 'rtcl_membership',
						[ 'id' => $id ], [ '%d' ] ) ) {
						$ids[] = $id;
					}
				}
				$ids = $ready_ids;
			} else {
				array_push( $message, __( "Please select a row to delete", "classified-listing-store" ) );
			}
		} else {
			array_push( $message, __( "Session not valid", "classified-listing-store" ) );
		}
		wp_send_json( [
			'success' => $success,
			'message' => $message,
			'ids'     => $ids
		] );
	}

	static function rtcl_get_all_membership_list() {
		$success = false;
		$data    = [];
		$message = [];
		if ( Functions::verify_nonce() ) {
			$success = true;
			$data    = self::get_membership_list();
		} else {
			$message[] = __( "Session not valid", "classified-listing-store" );
		}
		wp_send_json( [
			'success' => $success,
			'data'    => $data
		] );
	}

	private static function get_membership_list() {

		global $wpdb;
		$prefix = is_multisite() ? $wpdb->base_prefix : $wpdb->prefix;

		return $wpdb->get_results(
			"SELECT m.*, u.user_email as email , u.user_nicename as user_name, u.ID as user_id 
					  FROM {$wpdb->prefix}rtcl_membership m, {$prefix}users u
					  WHERE m.user_id = u.ID
					  ORDER BY m.id DESC
					  " );
	}

}