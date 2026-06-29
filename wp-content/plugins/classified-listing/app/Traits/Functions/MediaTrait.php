<?php


namespace Rtcl\Traits\Functions;

use Rtcl\Controllers\Hooks\Filters;

trait MediaTrait {

	public static function get_image_sizes() {
		$mms = self::get_option( 'rtcl_misc_media_settings' );

		return apply_filters( 'rtcl_image_sizes', [
			"rtcl-gallery"           => [
				'width'  => isset( $mms['image_size_gallery']['width'] ) ? absint( $mms['image_size_gallery']['width'] ) : 924,
				'height' => isset( $mms['image_size_gallery']['width'] ) ? absint( $mms['image_size_gallery']['height'] ) : 462,
				'crop'   => isset( $mms['image_size_gallery']['crop'] ) && $mms['image_size_gallery']['crop'] === 'yes'
			],
			"rtcl-thumbnail"         => [
				'width'  => isset( $mms['image_size_thumbnail']['width'] ) ? absint( $mms['image_size_thumbnail']['width'] ) : 320,
				'height' => isset( $mms['image_size_thumbnail']['width'] ) ? absint( $mms['image_size_thumbnail']['height'] ) : 240,
				'crop'   => isset( $mms['image_size_thumbnail']['crop'] ) && $mms['image_size_thumbnail']['crop'] === 'yes'
			],
			"rtcl-gallery-thumbnail" => [
				'width'  => isset( $mms['image_size_gallery_thumbnail']['width'] ) ? absint( $mms['image_size_gallery_thumbnail']['width'] ) : 150,
				'height' => isset( $mms['image_size_gallery_thumbnail']['width'] ) ? absint( $mms['image_size_gallery_thumbnail']['height'] ) : 105,
				'crop'   => isset( $mms['image_size_gallery_thumbnail']['crop'] ) && $mms['image_size_gallery_thumbnail']['crop'] === 'yes'
			],
		] );
	}

	public static function get_default_image_sizes() {
		return apply_filters( 'rtcl_default_image_sizes', [
			'thumbnail' => [
				'width'  => (int) get_option( "thumbnail_size_w", 150 ),
				'height' => (int) get_option( "thumbnail_size_h", 150 ),
				'crop'   => (int) get_option( "thumbnail_crop", 1 ),
			],
			'medium'    => [
				'width'  => (int) get_option( "medium_size_w", 300 ),
				'height' => (int) get_option( "medium_size_h", 300 ),
				'crop'   => false,
			],
			'large'     => [
				'width'  => (int) get_option( "large_size_w", 1024 ),
				'height' => (int) get_option( "large_size_h", 1024 ),
				'crop'   => false,
			]
		] );
	}

	/**
	 * Generates attachment meta data and create image sub-sizes for images.
	 *
	 * @param int    $attachment_id Attachment ID to process.
	 * @param string $file          Filepath of the attached image.
	 * @param array  $image_sizes   Filepath of the attached image.
	 *
	 * @return array Metadata for attachment.
	 */
	static function generate_attachment_metadata( $attachment_id, $file, $image_sizes ) {
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$attachment = get_post( $attachment_id );

		$mime_type = get_post_mime_type( $attachment );

		if ( ! ( 'image/heic' === $mime_type || ( preg_match( '!^image/!', $mime_type ) && file_is_displayable_image( $file ) ) ) ) {
			return wp_generate_attachment_metadata( $attachment_id, $file );
		}

		$imagesize = wp_getimagesize( $file );

		if ( empty( $imagesize ) ) {
			// File is not an image.
			return [];
		}

		// Default image meta.
		$image_meta = [
			'width'    => $imagesize[0],
			'height'   => $imagesize[1],
			'file'     => _wp_relative_upload_path( $file ),
			'filesize' => wp_filesize( $file ),
			'sizes'    => [],
		];

		// Fetch additional metadata from EXIF/IPTC.
		$exif_meta = wp_read_image_metadata( $file );

		if ( $exif_meta ) {
			$image_meta['image_meta'] = $exif_meta;
		}

		// Do not scale (large) PNG images. May result in sub-sizes that have greater file size than the original. See #48736.
		if ( 'image/png' !== $imagesize['mime'] ) {

			$threshold = (int) apply_filters( 'big_image_size_threshold', 2560, $imagesize, $file, $attachment_id );

			/*
			 * If the original image's dimensions are over the threshold,
			 * scale the image and use it as the "full" size.
			 */
			$scale_down = false;
			$convert    = false;

			if ( $threshold && ( $image_meta['width'] > $threshold || $image_meta['height'] > $threshold ) ) {
				// The image will be converted if needed on saving.
				$scale_down = true;
			} else {
				// The image may need to be converted regardless of its dimensions.
				$output_format = wp_get_image_editor_output_format( $file, $imagesize['mime'] );

				if (
					is_array( $output_format )
					&& array_key_exists( $imagesize['mime'], $output_format )
					&& $output_format[ $imagesize['mime'] ] !== $imagesize['mime']
				) {
					$convert = true;
				}
			}

			if ( $scale_down || $convert ) {
				$editor = wp_get_image_editor( $file );

				if ( is_wp_error( $editor ) ) {
					// This image cannot be edited.
					return $image_meta;
				}

				if ( $scale_down ) {
					// Resize the image. This will also convet it if needed.
					$resized = $editor->resize( $threshold, $threshold );
				} elseif ( $convert ) {
					// The image will be converted (if possible) when saved.
					$resized = true;
				}

				$rotated = null;

				// If there is EXIF data, rotate according to EXIF Orientation.
				if ( ! is_wp_error( $resized ) && is_array( $exif_meta ) ) {
					$resized = $editor->maybe_exif_rotate();
					$rotated = $resized; // bool true or WP_Error
				}

				if ( ! is_wp_error( $resized ) ) {
					/*
					 * Append "-scaled" to the image file name. It will look like "my_image-scaled.jpg".
					 * This doesn't affect the sub-sizes names as they are generated from the original image (for best quality).
					 */
					if ( $scale_down ) {
						$saved = $editor->save( $editor->generate_filename( 'scaled' ) );
					} elseif ( $convert ) {
						/*
						 * Generate a new file name for the converted image.
						 *
						 * As the image file name will be unique due to the changed file extension,
						 * it does not need a suffix to be unique. However, the generate_filename method
						 * does not allow for an empty suffix, so the "-converted" suffix is required to
						 * be added and subsequently removed.
						 */
						$converted_file_name = $editor->generate_filename( 'converted' );
						$converted_file_name = preg_replace( '/(-converted\.)([a-z0-9]+)$/i', '.$2', $converted_file_name );
						$saved               = $editor->save( $converted_file_name );
					} else {
						$saved = $editor->save();
					}

					if ( ! is_wp_error( $saved ) ) {
						$image_meta = _wp_image_meta_replace_original( $saved, $file, $image_meta, $attachment_id );

						// If the image was rotated update the stored EXIF data.
						if ( true === $rotated && ! empty( $image_meta['image_meta']['orientation'] ) ) {
							$image_meta['image_meta']['orientation'] = 1;
						}
					} else {
						// TODO: Log errors.
					}
				} else {
					// TODO: Log errors.
				}
			} elseif ( ! empty( $exif_meta['orientation'] ) && 1 !== (int) $exif_meta['orientation'] ) {
				// Rotate the whole original image if there is EXIF data and "orientation" is not 1.

				$editor = wp_get_image_editor( $file );

				if ( is_wp_error( $editor ) ) {
					// This image cannot be edited.
					return $image_meta;
				}

				// Rotate the image.
				$rotated = $editor->maybe_exif_rotate();

				if ( true === $rotated ) {
					// Append `-rotated` to the image file name.
					$saved = $editor->save( $editor->generate_filename( 'rotated' ) );

					if ( ! is_wp_error( $saved ) ) {
						$image_meta = _wp_image_meta_replace_original( $saved, $file, $image_meta, $attachment_id );

						// Update the stored EXIF data.
						if ( ! empty( $image_meta['image_meta']['orientation'] ) ) {
							$image_meta['image_meta']['orientation'] = 1;
						}
					} else {
						// TODO: Log errors.
					}
				}
			}
		}

		// Save initial data before generate others data
		wp_update_attachment_metadata( $attachment_id, $image_meta );

		$new_sizes           = [];
		$default_image_sizes = [
			'thumbnail' => self::get_default_image_sizes()['thumbnail'],
		];
		if ( empty( $image_sizes ) ) {
			$new_sizes = $default_image_sizes;
		} else {
			foreach ( $image_sizes as $size_name => $size ) {
				if ( is_string( $size_name ) && isset( $size['width'] ) && isset( $size['height'] ) ) {
					$new_sizes[ $size_name ] = [
						'width'  => (int) $size['width'],
						'height' => (int) $size['height'],
						'crop'   => $size['crop'] ? 1 : 0,
					];
				}
			}
			$new_sizes = ! empty( $new_sizes ) ? $new_sizes : $default_image_sizes;
		}


		$editor = wp_get_image_editor( $file );

		if ( is_wp_error( $editor ) ) {
			return $image_meta;
		}

		if ( method_exists( $editor, 'make_subsize' ) ) {
			foreach ( $new_sizes as $size_name => $new_size_data ) {
				$new_size_meta = $editor->make_subsize( $new_size_data );

				if ( is_wp_error( $new_size_meta ) ) {
					// TODO: Log errors.
				} else {
					// Save the size meta value.
					$image_meta['sizes'][ $size_name ] = $new_size_meta;
					wp_update_attachment_metadata( $attachment_id, $image_meta );
				}
			}
		} else {
			$created_sizes = $editor->multi_resize( $new_sizes );

			if ( ! empty( $created_sizes ) ) {
				$image_meta['sizes'] = array_merge( $image_meta['sizes'], $created_sizes );
				wp_update_attachment_metadata( $attachment_id, $image_meta );
			}
		}

		return apply_filters( 'wp_generate_attachment_metadata', $image_meta, $attachment_id, 'create' );
	}

	public static function process_listing_image( $data, $post_id = 0 ) {
		//$images  = explode( ',', $data );
		$images      = array_map( 'trim', explode( ',', $data ) );
		$gallery_ids = [];

		foreach ( $images as $image_url ) {
			$image_title   = preg_replace( '/\.[^.]+$/', '', basename( $image_url ) );
			$attachment_id = self::upload_image( $image_url, $image_title, $post_id );
			if ( ! is_wp_error( $attachment_id ) ) {
				$gallery_ids[] = $attachment_id;
			}
		}

		return $gallery_ids;
	}

	public static function upload_image( $image_url, $image_title, $post_id = 0 ) {
		set_time_limit( 150 );
		wp_raise_memory_limit( 'image' );
		Filters::beforeUpload();
		$attachment_id = media_sideload_image( $image_url, $post_id, $image_title, 'id' );
		Filters::afterUpload();

		return $attachment_id;
	}

	public static function set_listing_images( $listing_id, $attachment_ids = [] ) {
		$attachment_ids = array_map( 'intval', $attachment_ids );
		$attachment_ids = array_filter( $attachment_ids );
		set_post_thumbnail( $listing_id, $attachment_ids[0] );
		foreach ( $attachment_ids as $attachment_id ) {
			wp_update_post(
				[
					'ID'          => $attachment_id,
					'post_parent' => $listing_id,
				]
			);
		}
		update_post_meta( $listing_id, '_rtcl_attachments_order', $attachment_ids );
	}

}