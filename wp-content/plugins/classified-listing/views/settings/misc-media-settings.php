<?php

use Rtcl\Helpers\Functions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for Payment
 */
$options = array(
	'img_gallery_section'          => [
		'title' => esc_html__( 'Image Sizes', 'classified-listing' ),
		'type'  => 'section',
	],
	'image_size_gallery'           => [
		'title'       => esc_html__( 'Galley Slider', 'classified-listing' ),
		'type'        => 'image_size',
		'default'     => [ 'width' => 800, 'height' => 380, 'crop' => 'yes' ],
		'options'     => [
			'width'  => esc_html__( 'Width', 'classified-listing' ),
			'height' => esc_html__( 'Height', 'classified-listing' ),
			'crop'   => esc_html__( 'Hard Crop', 'classified-listing' ),
		],
		'description' => esc_html__( 'This image size is being used in the image slider on Listing details pages.', "classified-listing" )
	],
	'image_size_gallery_thumbnail' => [
		'title'       => esc_html__( 'Gallery Thumbnail', 'classified-listing' ),
		'type'        => 'image_size',
		'default'     => [ 'width' => 150, 'height' => 105, 'crop' => 'yes' ],
		'options'     => [
			'width'  => esc_html__( 'Width', 'classified-listing' ),
			'height' => esc_html__( 'Height', 'classified-listing' ),
			'crop'   => esc_html__( 'Hard Crop', 'classified-listing' ),
		],
		'description' => esc_html__( 'Gallery thumbnail image size', "classified-listing" )
	],
	'image_size_thumbnail'         => [
		'title'       => esc_html__( 'Thumbnail', 'classified-listing' ),
		'type'        => 'image_size',
		'default'     => [ 'width' => 300, 'height' => 240, 'crop' => 'yes' ],
		'options'     => [
			'width'  => esc_html__( 'Width', 'classified-listing' ),
			'height' => esc_html__( 'Height', 'classified-listing' ),
			'crop'   => esc_html__( 'Hard Crop', 'classified-listing' ),
		],
		'description' => esc_html__( 'Listing thumbnail size will use all listing page', "classified-listing" )
	],
	'image_allowed_type'           => [
		'title'   => esc_html__( 'Allowed Image Type', 'classified-listing' ),
		'type'    => 'multi_checkbox',
		'default' => [ 'png', 'jpeg', 'jpg' ],
		'options' => apply_filters( 'rtcl_gallery_image_support_format', [
			'png'  => esc_html__( 'PNG', 'classified-listing' ),
			'jpg'  => esc_html__( 'JPG', 'classified-listing' ),
			'jpeg' => esc_html__( 'JPEG', 'classified-listing' ),
			'webp' => esc_html__( 'WebP', 'classified-listing' ),
		] )
	],
	'image_allowed_memory'         => [
		'title'       => esc_html__( 'Allowed Image Memory Size', 'classified-listing' ),
		'type'        => 'number',
		'default'     => 2,
		/* translators:  maximum file size */
		'description' => sprintf( __( 'Enter the image memory size, like 2 for 2 MB (only number without MB) <br><span style="color: red">Your hosting allowed maximum %s</span>',
			'classified-listing' ), Functions::formatBytes( Functions::get_wp_max_upload() ) )
	],
	'placeholder_image'            => [
		'title' => esc_html__( 'Placeholder Image', 'classified-listing' ),
		'type'  => 'image',
		'label' => esc_html__( 'Select an Image to display as placeholder if have no image.', 'classified-listing' )
	]
);

return apply_filters( 'rtcl_misc_media_settings_options', $options );
