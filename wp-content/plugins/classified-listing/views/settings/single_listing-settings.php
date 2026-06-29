<?php

use Rtcl\Resources\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for Payment
 */
$options = array(
	'listings_section'             => [
		'title'       => esc_html__( 'Listing Details Settings', 'classified-listing' ),
		'type'        => 'section',
		'description' => '',
	],
	'has_report_abuse'             => [
		'title'   => esc_html__( 'Enable Report Abuse', 'classified-listing' ),
		'type'    => 'checkbox',
		'default' => 'yes',
		'label'   => esc_html__( 'Check this to enable Report abuse', 'classified-listing' )
	],
	'has_contact_form'             => [
		'title'   => esc_html__( 'Enable Contact Form', 'classified-listing' ),
		'type'    => 'checkbox',
		'default' => 'yes',
		'label'   => esc_html__( 'Allows visitors to contact listing authors privately. Authors will receive the messages via email.', 'classified-listing' )
	],
	'has_comment_form'             => [
		'title' => esc_html__( 'Enable Review Form', 'classified-listing' ),
		'type'  => 'checkbox',
		'label' => esc_html__( 'Allow visitors to review your listing.', 'classified-listing' )
	],
	'disable_gallery_slider'       => [
		'title' => esc_html__( 'Disable Gallery Slider', 'classified-listing' ),
		'type'  => 'checkbox',
		'label' => esc_html__( 'Disable', 'classified-listing' ),
	],
	'disable_gallery_video'        => [
		'title' => esc_html__( 'Disable Gallery Video', 'classified-listing' ),
		'type'  => 'checkbox',
		'label' => esc_html__( 'Disable', 'classified-listing' ),
	],
	'related_posts_per_page'       => array(
		'title'       => esc_html__( 'Number of Related Listings', 'classified-listing' ),
		'type'        => 'number',
		'default'     => 4,
		'css'         => 'width:50px',
		'description' => esc_html__( 'Number of listings to show as related listing', 'classified-listing' )
	),
	'detail_page_sidebar_position' => [
		'title'   => esc_html__( 'Sidebar Position', 'classified-listing' ),
		'type'    => 'select',
		'class'   => 'rtcl-select2',
		'default' => 'right',
		'options' => Options::detail_page_sidebar_position()
	],
	'display_options_detail'       => [
		'title'   => esc_html__( 'Show in Listing Detail Page', 'classified-listing' ),
		'type'    => 'multi_checkbox',
		'default' => [ 'date', 'user', 'views', 'category', 'location', 'price' ],
		'options' => Options::get_listing_detail_page_display_options()
	],
);

return apply_filters( 'rtcl_single_listing_settings_options', $options );
