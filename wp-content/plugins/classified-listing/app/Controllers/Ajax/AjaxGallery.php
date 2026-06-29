<?php

namespace Rtcl\Controllers\Ajax;

use Rtcl\Controllers\Hooks\Filters;
use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Helper;
use Rtcl\Helpers\UploadHelper;
use stdClass;

class AjaxGallery {

	/**
	 * Verify that the current user owns (or can edit) the given listing.
	 * Returns true when the request should be allowed, false otherwise.
	 *
	 * For brand-new listings ($post_id === 0) the caller only needs to be
	 * logged-in (or unregistered posting must be enabled).
	 *
	 * For existing listings the user must be the author or an administrator,
	 * with a special exception for guest temp posts (post_status 'rtcl-temp',
	 * author 0, unregistered posting enabled).
	 */
	private function current_user_can_edit_listing( $post_id ) {
		$post_id = absint( $post_id );

		$listing = rtcl()->factory->get_listing( $post_id );
		if ( ! $listing ) {
			return false;
		}

		$post        = $listing->get_listing();
		$post_author = (int) $post->post_author;

		// Guest temp posts created during unregistered posting.
		if ( 'rtcl-temp' === $post->post_status && 0 === $post_author && Functions::is_enable_post_for_unregister() ) {
			return true;
		}

		return Functions::current_user_can( 'edit_' . rtcl()->post_type, $post_id );
	}

	public function __construct() {
		add_action( 'wp_ajax_rtcl_gallery_upload', [ $this, 'gallery_upload' ] );
		add_action( 'wp_ajax_rtcl_gallery_update_order', [ $this, 'gallery_update_order' ] );
		add_action( 'wp_ajax_rtcl_gallery_delete', [ $this, 'gallery_delete' ] );
		add_action( 'wp_ajax_rtcl_gallery_image_save', [ $this, 'gallery_image_save' ] );
		add_action( 'wp_ajax_rtcl_gallery_image_restore', [ $this, 'gallery_image_restore' ] );
		add_action( 'wp_ajax_rtcl_gallery_update', [ $this, 'gallery_update' ] );
		add_action( 'wp_ajax_rtcl_gallery_image_stream', [ $this, 'gallery_image_stream' ] );


		if ( ! is_user_logged_in() && Functions::is_enable_post_for_unregister() ) {
			add_action( 'wp_ajax_nopriv_rtcl_gallery_upload', [ $this, 'gallery_upload' ] );
			add_action( 'wp_ajax_nopriv_rtcl_gallery_update_order', [ $this, 'gallery_update_order' ] );
			add_action( 'wp_ajax_nopriv_rtcl_gallery_delete', [ $this, 'gallery_delete' ] );
			add_action( 'wp_ajax_nopriv_rtcl_gallery_image_save', [ $this, 'gallery_image_save' ] );
			add_action( 'wp_ajax_nopriv_rtcl_gallery_image_restore', [ $this, 'gallery_image_restore' ] );
			add_action( 'wp_ajax_nopriv_rtcl_gallery_update', [ $this, 'gallery_update' ] );
			add_action( 'wp_ajax_nopriv_rtcl_gallery_image_stream', [ $this, 'gallery_image_stream' ] );
		}
	}

	function gallery_delete() {
		if ( ! check_ajax_referer( 'rtcl-gallery', '_ajax_nonce', false ) ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => __( "Invalid Session. Please refresh the page and try again.", "classified-listing" ),
			] );

			exit;
		}

		if ( ! is_user_logged_in() && apply_filters( 'rtcl_is_disable_post_for_unregister', true ) ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => __( "Registration required to delete listing image.", "classified-listing" ),
			] );

			exit;
		}

		$post_id = absint( Functions::request( "post_id" ) );
		if ( $post_id > 0 && ! $this->current_user_can_edit_listing( $post_id ) ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => __( "You do not have permission to delete images for this listing.", "classified-listing" ),
			] );

			exit;
		}

		$attach_id = intval( $_POST["attach_id"] );
		$attach    = get_post( $attach_id );

		if ( $attach === null ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => __( "Attachment does not exist.", "classified-listing" ),
			] );

			exit;
		}

		if ( is_user_logged_in() && ( ! current_user_can( 'delete_post', $attach_id ) && ! current_user_can( 'manage_rtcl_listing_images' ) ) ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => __( "You are not allowed to delete listing images.", "classified-listing" ),
			] );

			exit;
		}

		if ( $attach->post_parent != absint( Functions::request( "post_id" ) ) ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => __( "Incorrect attachment ID.", "classified-listing" ),
			] );
		} elseif ( wp_delete_attachment( $attach_id ) ) {
			echo wp_json_encode( [ "result" => 1 ] );
		} else {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => __( "File could not be deleted.", "classified-listing" ),
			] );
		}

		exit;
	}

	function gallery_update_order() {
		if ( ! check_ajax_referer( 'rtcl-gallery', '_ajax_nonce', false ) ) {
			wp_send_json_error( [ "error" => __( "Invalid Session. Please refresh the page and try again.", "classified-listing" ) ] );
		}

		if ( ! is_user_logged_in() && apply_filters( 'rtcl_is_disable_post_for_unregister', true ) ) {
			wp_send_json_error( [ "error" => __( "Registration required to reorder listing image.", "classified-listing" ) ] );
		}

		if ( is_user_logged_in() && ( ! current_user_can( 'upload_files' ) && ! current_user_can( 'manage_rtcl_listing_images' ) ) ) {
			wp_send_json_error( [ "error" => __( "You are not allowed to reorder listing images.", "classified-listing" ) ] );
		}

		$post_id = intval( Functions::request( "post_id" ) );

		if ( $post_id > 0 && ! $this->current_user_can_edit_listing( $post_id ) ) {
			wp_send_json_error( [ "error" => __( "You do not have permission to reorder images for this listing.", "classified-listing" ) ] );
		}

		$ordered_keys = ! empty( $_POST['ordered_keys'] ) && is_array( $_POST['ordered_keys'] ) ? $_POST['ordered_keys'] : [];
		$ordered_keys = $ordered_keys ? array_map( 'intval', $ordered_keys ) : [];
		$ordered_keys = $ordered_keys ? array_filter( $ordered_keys ) : [];
		if ( ! empty( $ordered_keys ) ) {
			update_post_meta( $post_id, '_rtcl_attachments_order', $ordered_keys );
		}
		wp_send_json_success( $ordered_keys );
	}

	function gallery_upload() {
		if ( ! check_ajax_referer( 'rtcl-gallery', '_ajax_nonce', false ) ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => __( "Invalid Session. Please refresh the page and try again.", "classified-listing" ),
			] );

			exit;
		}

		if ( ! is_user_logged_in() && apply_filters( 'rtcl_is_disable_post_for_unregister', true ) ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => __( "Registration required to upload listing image.", "classified-listing" ),
			] );

			exit;
		}

		if ( is_user_logged_in() && ( ! current_user_can( 'upload_files' ) && ! current_user_can( 'manage_rtcl_listing_images' ) ) ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => __( "You are not allowed to upload listing images.", "classified-listing" ),
			] );

			exit;
		}

		$parent_post_id = isset( $_POST["post_id"] ) ? absint( $_POST["post_id"] ) : 0;
		if ( $parent_post_id > 0 && ! $this->current_user_can_edit_listing( $parent_post_id ) ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => __( "You do not have permission to upload images for this listing.", "classified-listing" ),
			] );

			exit;
		}

		$v           = new UploadHelper();
		$field_name  = Functions::request( "field_name" );
		$form_params = [
			"form_scheme" => Functions::request( "form_scheme" ),
		];
		$form_scheme = apply_filters( "rtcl_form_scheme", Helper::instance()->get( "form" ), $form_params );
		$form_scheme = apply_filters( "rtcl_form_load", $form_scheme );
		if ( ! empty( $form_scheme["field"] ) && is_array( $form_scheme["field"] ) ) {
			foreach ( $form_scheme["field"] as $key => $field ) {
				if ( $field["name"] == $field_name ) {
					if ( isset( $field["validator"] ) && is_array( $field["validator"] ) ) {
						foreach ( $field["validator"] as $vcallback ) {
							$v->add_validator( $vcallback );
						}
					}
				}
			}
		}

		do_action( 'rtcl_gallery_image_before_upload', $_FILES );

		Filters::beforeUpload();
		// you can use WP's wp_handle_upload() function:
		$status = wp_handle_upload( $_FILES['async-upload'], [
			'test_form' => true,
			'action'    => 'rtcl_gallery_upload',
		] );

		if ( isset( $status['error'] ) ) {
			Filters::afterUpload();
			echo wp_json_encode( $status );
			exit;
		}

		// $filename should be the path to a file in the upload directory.
		$filename = $status['file'];

		// The ID of the post this attachment is for.
		$parent_post_id = intval( $_POST["post_id"] );

		// Check the type of tile. We'll use this as the 'post_mime_type'.
		$filetype = wp_check_filetype( basename( $filename ), null );

		// Get the path to the upload directory.
		$wp_upload_dir = wp_upload_dir();

		// Prepare an array of post data for the attachment.
		$attachment = [
			'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
			'post_mime_type' => $filetype['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		];

		// Create post if does not exist
		if ( $parent_post_id < 1 ) {
			add_filter( "post_type_link", "__return_empty_string" );

			$parent_post_id = wp_insert_post( apply_filters( "rtcl_insert_temp_post_for_gallery", [
				'post_title'      => __( 'RTCL Auto Temp', "classified-listing" ),
				'post_content'    => '',
				'post_status'     => Functions::get_temp_listing_status(),
				'post_author'     => wp_get_current_user()->ID,
				'post_type'       => rtcl()->post_type,
				'comments_status' => 'closed',
			] ) );

			remove_filter( "post_type_link", "__return_empty_string" );
		}

		// Insert the attachment.
		$attach_id = wp_insert_attachment( $attachment, $filename, $parent_post_id );
		if ( ! is_wp_error( $attach_id ) ) {
			wp_update_attachment_metadata( $attach_id, Functions::generate_attachment_metadata( $attach_id, $filename, Functions::get_image_sizes() ) );
		}
		// Fix the image guid url
		Filters::afterUpload();
		echo wp_json_encode( Functions::upload_item_data( $attach_id ) );
		exit;
	}

	function gallery_image_save() {
		if ( ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) && ! check_ajax_referer( 'rtcl-gallery', '_ajax_nonce', false ) ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => __( "Invalid Session. Please refresh the page and try again.", "classified-listing" ),
			] );

			exit;
		}

		if ( ! is_user_logged_in() && apply_filters( 'rtcl_is_disable_post_for_unregister', true ) ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => __( "Registration required to save listing image.", "classified-listing" ),
			] );

			exit;
		}

		if ( is_user_logged_in() && ( ! current_user_can( 'manage_rtcl_options' ) && ! current_user_can( 'manage_rtcl_listing_images' ) ) ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => __( "You are not allowed to save listing images.", "classified-listing" ),
			] );

			exit;
		}

		if ( ! Functions::user_can_edit_image() ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => __( "You cannot edit images.", "classified-listing" ),
			] );
			exit;
		}

		$attach_id       = absint( Functions::request( "attach_id" ) );
		$action_type     = Functions::request( "action_type" );
		$history_encoded = Functions::request( "history" );
		$post_id         = absint( Functions::request( "post_id" ) );

		if ( $post_id > 0 && ! $this->current_user_can_edit_listing( $post_id ) ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => __( "You do not have permission to edit images for this listing.", "classified-listing" ),
			] );
			exit;
		}

		$size_dash = Functions::request( "size" );
		$size      = str_replace( "_", "-", Functions::request( "size" ) );

		$attach  = get_post( $attach_id );
		$history = json_decode( $history_encoded );


		if ( $attach->post_parent != $post_id ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => __( "Incorrect Post or Attachment ID.", "classified-listing" ),
			] );
			exit;
		}

		if ( ! is_array( $history ) ) {
			$history = [];
		}

		$attached_file = get_attached_file( $attach_id );

		$file_name = pathinfo( $attached_file, PATHINFO_FILENAME );

		if ( $size && $action_type == "edit" ) {
			$upload        = Functions::upload_item_data( $attach_id );
			$attached_file = dirname( $attached_file ) . "/" . basename( $upload["sizes"][ $size_dash ]["url"] );
		} elseif ( $action_type == "create" ) {
			$upload        = Functions::upload_item_data( $attach_id );
			$attached_file = dirname( $attached_file ) . "/" . basename( $upload["sizes"]["full"]["url"] );
		}

		$image = wp_get_image_editor( $attached_file );

		if ( is_wp_error( $image ) ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => $image->get_error_message(),
			] );
			exit;
		}

		foreach ( $history as $c ) {
			if ( ! isset( $c->a ) ) {
				continue;
			}

			if ( $c->a == "c" ) {
				$image->crop( $c->x, $c->y, $c->w, $c->h );
			} elseif ( $c->a == "ro" ) {
				$image->rotate( $c->v );
			} elseif ( $c->a == "re" ) {
				// resize
				$image->resize( $c->w, $c->h );
			} elseif ( $c->a == "f" ) {
				$image->flip( $c->h, $c->v );
			}
		}

		$return = new stdClass();

		$backup_sizes = get_post_meta( $attach_id, '_wp_attachment_backup_sizes', true );
		$backup_sizes = ! is_array( $backup_sizes ) ? [] : get_post_meta( $attach_id, '_wp_attachment_backup_sizes', true );
		$meta         = wp_get_attachment_metadata( $attach_id );

		$basename = pathinfo( $attached_file, PATHINFO_BASENAME );
		$dirname  = pathinfo( $attached_file, PATHINFO_DIRNAME );
		$ext      = pathinfo( $attached_file, PATHINFO_EXTENSION );
		$filename = pathinfo( $attached_file, PATHINFO_FILENAME );
		$suffix   = time() . wp_rand( 100, 999 );

		$is_resized = preg_match( '/-e([0-9]+)$/', $filename );

		if ( $action_type == "create" && $size != "full" ) {
			$sizes    = rtcl()->gallery['image_sizes'];
			$filename = sprintf( "%s-%dx%d", $filename, $sizes[ $size ]["width"], $sizes[ $size ]["height"] );
		}

		while ( true ) {
			$filename     = preg_replace( '/-e([0-9]+)$/', '', $filename );
			$filename     .= "-e{$suffix}";
			$new_filename = "{$filename}.{$ext}";
			$new_path     = "{$dirname}/$new_filename";
			if ( file_exists( $new_path ) ) {
				$suffix ++;
			} else {
				break;
			}
		}

		$saved = $image->save( $new_path );

		if ( ! $saved ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => $image->get_error_message(),
			] );
			exit;
		}

		if ( $is_resized ) {
			// working on already resized file, just delete the old file and set
			// new file name in meta $size
			$s = $meta["sizes"][ $size ];

			if ( ! empty( $s['file'] ) ) {
				// delete old resized file
				$delete_file = path_join( $dirname, $s['file'] );
				wp_delete_file( $delete_file );
			}
		} else {
			// working on new image, save the new file name in meta and set backup size
			$tag = "$size-orig";

			if ( ! isset( $meta['sizes'][ $size ] ) ) {
				$backup_sizes[ $tag ] = [
					"file"   => basename( $meta["file"] ),
					"width"  => $meta["width"],
					"height" => $meta["height"],
				];
			} else {
				$backup_sizes[ $tag ] = $meta['sizes'][ $size ];
			}
		}

		$meta["sizes"][ $size ] = [
			"file"   => $new_filename,
			"width"  => $saved["width"],
			"height" => $saved["height"],
		];


		if ( $size == "full" && Functions::request( "apply_to_all" ) == "1" ) {
			$save_path = $dirname;
			$new_file  = $new_path;

			$sizes     = rtcl()->gallery['image_sizes'];
			$size_keys = array_keys( $sizes );

			foreach ( $size_keys as $size_key ) {
				// 1. IF exists delete backup file
				// 2. MOVE size to backup_size
				// 3. generate new size
				// 4. save new size

				if ( ! isset( $backup_sizes[ $size_key . '-orig' ] ) ) {
					$backup_sizes[ $size_key . '-orig' ] = $meta["sizes"][ $size_key ];
				}

				if ( isset( $meta["sizes"][ $size_key ] ) ) {
					wp_delete_file( $meta["sizes"][ $size_key ]["file"] );
				}

				$cs               = $sizes[ $size_key ];
				$interm_file_name = sprintf( "%s-%dx%d-e%s.png", $file_name, $cs["width"], $cs["height"], $suffix );

				$image = wp_get_image_editor( $new_file );
				$image->resize( $cs["width"], $cs["height"], $cs["crop"] );

				$file = $image->save( dirname( $new_file ) . "/" . $interm_file_name );

				$meta["sizes"][ $size_key ] = [
					"file"      => $file["file"],
					"width"     => $file["width"],
					"height"    => $file["height"],
					"mime-type" => $file["mime-type"],
				];
			}
		}

		wp_update_attachment_metadata( $attach_id, $meta );
		update_post_meta( $attach_id, '_wp_attachment_backup_sizes', $backup_sizes );

		$return->result = 1;
		$return->file   = Functions::upload_item_data( $attach_id );
		echo wp_json_encode( $return );

		exit;
	}

	function gallery_image_restore() {
		if ( ! check_ajax_referer( 'rtcl-gallery', '_ajax_nonce', false ) ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => __( "Invalid Session. Please refresh the page and try again.", "classified-listing" ),
			] );

			exit;
		}

		if ( ! is_user_logged_in() && apply_filters( 'rtcl_is_disable_post_for_unregister', true ) ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => __( "Registration required to restore listing image.", "classified-listing" ),
			] );

			exit;
		}

		if ( is_user_logged_in() && ( ! current_user_can( 'manage_rtcl_options' ) && ! current_user_can( 'manage_rtcl_listing_images' ) ) ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => __( "You are not allowed to restore listing images.", "classified-listing" ),
			] );

			exit;
		}

		if ( ! Functions::user_can_edit_image() ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => __( "You cannot edit images.", "classified-listing" ),
			] );

			exit;
		}

		$size      = Functions::request( "size" );
		$attach_id = Functions::request( "attach_id" );
		$post_id   = intval( Functions::request( "post_id" ) );

		if ( $post_id > 0 && ! $this->current_user_can_edit_listing( $post_id ) ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => __( "You do not have permission to restore images for this listing.", "classified-listing" ),
			] );

			exit;
		}

		$attach = get_post( $attach_id );

		if ( $attach->post_parent != $post_id ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => __( "Incorrect Post or Attachment ID.", "classified-listing" ),
			] );
			exit;
		}

		if ( $size === "full" ) {
			// restore all
			$keys    = array_keys( rtcl()->gallery['image_sizes'] );
			$restore = array_merge( [ "full" ], $keys );
		} else {
			$restore = [ str_replace( "_", "-", $size ) ];
		}

		$meta           = wp_get_attachment_metadata( $attach_id );
		$attachment_dir = dirname( get_attached_file( $attach_id ) );
		$backup_sizes   = get_post_meta( $attach_id, '_wp_attachment_backup_sizes', true );

		foreach ( $restore as $r ) {
			if ( isset( $backup_sizes[ $r . '-orig' ] ) ) {
				wp_delete_file( $attachment_dir . "/" . $meta["sizes"][ $r ]["file"] );
				$meta["sizes"][ $r ] = $backup_sizes[ $r . '-orig' ];
				unset( $backup_sizes[ $r . '-orig' ] );
			}
		}

		wp_update_attachment_metadata( $attach_id, $meta );
		update_post_meta( $attach_id, '_wp_attachment_backup_sizes', $backup_sizes );

		$result         = new stdClass();
		$result->result = 1;
		$result->file   = Functions::upload_item_data( $attach_id );

		echo wp_json_encode( $result );
		exit;
	}

	function gallery_update() {
		if ( ! check_ajax_referer( 'rtcl-gallery', '_ajax_nonce', false ) ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => __( "Invalid Session. Please refresh the page and try again.", "classified-listing" ),
			] );

			exit;
		}

		if ( ! is_user_logged_in() && apply_filters( 'rtcl_is_disable_post_for_unregister', true ) ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => __( "Registration required to update listing image.", "classified-listing" ),
			] );

			exit;
		}

		if ( is_user_logged_in() && ( ! current_user_can( 'manage_rtcl_options' ) && ! current_user_can( 'manage_rtcl_listing_images' ) ) ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => __( "You are not allowed to update listing images.", "classified-listing" ),
			] );

			exit;
		}

		$post_id = intval( $_POST["post_id"] );
		if ( $post_id > 0 && ! $this->current_user_can_edit_listing( $post_id ) ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => __( "You do not have permission to update images for this listing.", "classified-listing" ),
			] );

			exit;
		}
		$attach_id = intval( $_POST["attach_id"] );
		$caption   = trim( Functions::request( "caption", "" ) );
		$content   = trim( Functions::request( "content", "" ) );
		$featured  = intval( $_POST["featured"] );

		$attach = get_post( $attach_id );

		if ( $attach->post_parent != $post_id ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => __( "Incorrect Post or Attachment ID.", "classified-listing" ),
			] );
			exit;
		}

		$result = wp_update_post( [
			"ID"           => $attach_id,
			"post_content" => $content,
			"post_excerpt" => $caption,
		] );

		if ( $result instanceof \WP_Error ) {
			echo wp_json_encode( [ "result" => 0, "error" => $result->get_error_message() ] );
			exit;
		}

		$featured_id = get_post_meta( $post_id, '_thumbnail_id', true );

		if ( $featured == "1" ) {
			update_post_meta( $post_id, '_thumbnail_id', $attach_id );
		} elseif ( $featured_id == $attach_id ) {
			delete_post_meta( $post_id, '_thumbnail_id' );
		}

		echo wp_json_encode( [ "result" => 1, "file" => Functions::upload_item_data( $attach_id ) ] );
		exit;
	}

	function gallery_image_stream() {
		if ( ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) && ! check_ajax_referer( 'rtcl-gallery', '_ajax_nonce', false ) ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => __( "Invalid Session. Please refresh the page and try again.", "classified-listing" ),
			] );

			exit;
		}

		if ( ! is_user_logged_in() && apply_filters( 'rtcl_is_disable_post_for_unregister', true ) ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => __( "Registration required to edit listing image.", "classified-listing" ),
			] );

			exit;
		}

		if ( is_user_logged_in() && ( ! current_user_can( 'manage_rtcl_options' ) && ! current_user_can( 'manage_rtcl_listing_images' ) ) ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => __( "You are not allowed to edit listing images.", "classified-listing" ),
			] );

			exit;
		}

		if ( ! Functions::user_can_edit_image() ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => __( "You cannot edit images.", "classified-listing" ),
			] );

			exit;
		}

		$attach_id       = Functions::request( "attach_id" );
		$history_encoded = Functions::request( "history" );
		$size            = Functions::request( "size" );
		$post_id         = absint( Functions::request( "post_id" ) );

		if ( $post_id > 0 && ! $this->current_user_can_edit_listing( $post_id ) ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => __( "You do not have permission to access images for this listing.", "classified-listing" ),
			] );
			exit;
		}

		$attach = get_post( $attach_id );

		if ( $attach->post_parent != $post_id ) {
			echo wp_json_encode( [
				"result" => 0,
				"error"  => __( "Incorrect Post or Attachment ID.", "classified-listing" ),
			] );
			exit;
		}

		$history = json_decode( $history_encoded );

		if ( ! is_array( $history ) ) {
			$history = [];
		}


		if ( wp_attachment_is_image( $attach_id ) ) {
			$attached_file = get_attached_file( $attach_id );
		} else {
			$attached_file = wp_get_attachment_image_src( $attach_id, "full" );
			$attached_file = dirname( get_attached_file( $attach_id ) ) . "/" . basename( $attached_file[0] );
		}

		if ( $size ) {
			$upload        = Functions::upload_item_data( $attach_id );
			$attached_file = dirname( $attached_file ) . "/" . basename( $upload["sizes"][ $size ]["url"] );
		} else {
			$upload = Functions::upload_item_data( $attach_id );
			if ( isset( $upload["sizes"]["full"]["is_intermidiate"] ) && $upload["sizes"]["full"]["is_intermidiate"] ) {
				$attached_file = $upload["sizes"]["full"]["url"];
			}
		}

		$image = wp_get_image_editor( $attached_file );

		if ( ! empty( $history ) ) {
			foreach ( $history as $c ) {
				if ( ! isset( $c->a ) ) {
					continue;
				}
				if ( $c->a == "c" ) {
					$image->crop( intval( $c->x ), intval( $c->y ), $c->w, $c->h );
				} elseif ( $c->a == "ro" ) {
					$image->rotate( $c->v );
				} elseif ( $c->a == "re" ) {
					// resize
					$image->resize( $c->w, $c->h );
				} elseif ( $c->a == "f" ) {
					$image->flip( $c->h, $c->v );
				}
			}
		}

		if ( ! is_wp_error( $image ) ) {
			$image->stream();
		}

		exit;
	}

}