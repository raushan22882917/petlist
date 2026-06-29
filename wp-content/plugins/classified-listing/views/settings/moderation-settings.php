<?php

use Rtcl\Resources\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for Payment
 */
$options = [
	'general_section'            => [
		'title'       => esc_html__( 'General Settings', 'classified-listing' ),
		'type'        => 'section',
		'description' => '',
	],
	'enable_business_hours'      => [
		'title' => esc_html__( 'Enable Business Hours', 'classified-listing' ),
		'type'  => 'checkbox',
		'label' => esc_html__( 'Enable business hours', 'classified-listing' )
	],
	'enable_social_profiles'     => [
		'title' => esc_html__( 'Enable Social Profiles', 'classified-listing' ),
		'type'  => 'checkbox',
		'label' => esc_html__( 'Enable social profiles', 'classified-listing' )
	],
	'image_edit_cap'             => [
		'title'   => esc_html__( 'User Able to Edit Image', 'classified-listing' ),
		'type'    => 'checkbox',
		'default' => 'yes',
		'label'   => esc_html__( 'User can edit image size , can crop , can make feature', 'classified-listing' )
	],
	'maximum_images_per_listing' => [
		'title'   => esc_html__( 'Maximum Images Allowed Per Listing', 'classified-listing' ),
		'type'    => 'number',
		'default' => 5
	],
	'form_section'               => [
		'title'       => esc_html__( 'Listing Form', 'classified-listing' ),
		'type'        => 'section',
		'description' => '',
	],
	'title_max_limit'            => [
		'title'       => esc_html__( 'Title Character Limit', 'classified-listing' ),
		'type'        => 'number',
		'description' => esc_html__( 'Leave it blank if you like no limit', 'classified-listing' )
	],
	'description_max_limit'      => [
		'title'       => esc_html__( 'Description Character Limit', 'classified-listing' ),
		'type'        => 'number',
		'description' => esc_html__( 'Leave it blank if you like no limit', 'classified-listing' )
	],
	'text_editor'                => array(
		'title'       => esc_html__( 'Text Editor', 'classified-listing' ),
		'type'        => 'radio',
		'default'     => 'wp_editor',
		'options'     => array(
			'wp_editor' => esc_html__( 'WP Editor', 'classified-listing' ),
			'textarea'  => esc_html__( 'Textarea', 'classified-listing' )
		),
		'description' => esc_html__( 'Listing form Editor style', 'classified-listing' ),
	),
	'hide_form_fields'           => [
		'title'   => esc_html__( 'Hide Form Fields', 'classified-listing' ),
		'type'    => 'multi_checkbox',
		'options' => Options::get_listing_form_hide_fields()
	]
];

return apply_filters( 'rtcl_moderation_settings_options', $options );
