<?php
/* phpcs:disable WordPress.Security.NonceVerification.Recommended */

namespace Rtcl\Controllers\Ajax;


use Rtcl\Helpers\Functions;
use Rtcl\Models\RtclCFGField;
use Rtcl\Resources\Options;

class AjaxCFG {
	public function __construct() {
		add_action( 'wp_ajax_rtcl_edit_field_choose', [ $this, 'edit_field_choose' ] );
		add_action( 'wp_ajax_rtcl_edit_field_insert', [ $this, 'edit_field_insert' ] );
		add_action( 'wp_ajax_rtcl_edit_field_delete', [ $this, 'edit_field_delete' ] );
	}

	function edit_field_delete() {
		$data = null;
		$error = true;
		if ( !Functions::verify_nonce() ) {
			$msg = esc_html__( "Session expired", "classified-listing" );
		} elseif ( !current_user_can( 'manage_rtcl_options' ) ) {
			$msg = esc_html__( "You do not have permission to delete custom fields.", "classified-listing" );
		} else {
			$post_id = !empty( $_REQUEST['id'] ) ? absint( $_REQUEST['id'] ) : 0;
			if ( $post_id && ( $post = get_post( $post_id ) ) && $post->post_type === rtcl()->post_type_cf ) {
				$p = wp_delete_post( $post_id, true );
				if ( $p ) {
					delete_metadata( 'post', 0, '_field_' . $post_id, '', true );
				}
				$error = false;
				$data = $p;
				$msg = __( "Success", "classified-listing" );
			} else {
				$data = $_REQUEST;
				$msg = esc_html__( "Field was not selected", "classified-listing" );
			}
		}
		wp_send_json( [
			'data'  => $data,
			'error' => $error,
			'msg'   => $msg
		] );
	}

	function edit_field_choose() {
		if ( !Functions::verify_nonce() ) {
			esc_html_e( "Session expired", "classified-listing" );
			die();
		}
		if ( !current_user_can( 'manage_rtcl_options' ) ) {
			esc_html_e( "You do not have permission to view custom fields.", "classified-listing" );
			die();
		}
		$html = null;
		$fields = Options::get_custom_field_list();
		$html .= "<p>" . esc_html__( "You can choose from the available fields:", "classified-listing" ) . "</p>";
		foreach ( $fields as $type => $field ) {
			$html .= "<span class='button rtcl-field-item rtcl-field-button-insert' data-type='{$type}'><i class='rtcl-icon rtcl-icon-{$field['symbol']}'></i>{$field['name']}</span>";
		}
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $html;
		die();
	}

	function edit_field_insert() {
		$data = null;
		$error = true;
		$type = !empty( $_REQUEST['type'] ) && array_key_exists( $_REQUEST['type'], Options::get_custom_field_list() ) ? esc_attr( $_REQUEST['type'] ) : 'text';
		if ( !Functions::verify_nonce() ) {
			$msg = esc_html__( "Session expired", "classified-listing" );
		} elseif ( !current_user_can( 'manage_rtcl_options' ) ) {
			$msg = esc_html__( "You do not have permission to insert custom fields.", "classified-listing" );
		} else {
			$parent_id = !empty( $_REQUEST['id'] ) ? absint( $_REQUEST['id'] ) : 0;
			if ( $type && $parent_id ) {
				$field_id = wp_insert_post( [
						'post_status' => 'draft',
						'post_type'   => 'rtcl_cf',
						'post_parent' => $parent_id
					]
				);
				update_post_meta( $field_id, '_type', $type );
				$field = new RtclCFGField( $field_id );
				$data = $field->get_field_data();
				$error = false;
				$msg = esc_html__( "Success", "classified-listing" );
			} else {
				$data = $_REQUEST;
				$msg = esc_html__( "Select a field type", "classified-listing" );
			}
		}
		wp_send_json( [
			'data'  => $data,
			'error' => $error,
			'msg'   => $msg
		] );
		die();
	}
}