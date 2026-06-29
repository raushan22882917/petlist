<?php

use Rtcl\Resources\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for Payment
 */
$options = array(
	'social_section'  => [
		'title' => esc_html__( 'Social Share Settings', 'classified-listing' ),
		'type'  => 'section',
	],
	'social_services' => [
		'title'   => esc_html__( 'Enable Social Share', 'classified-listing' ),
		'type'    => 'multi_checkbox',
		'default' => [ 'facebook', 'twitter' ],
		'options' => Options::social_services_options()
	],
	'social_pages'    => [
		'title'   => esc_html__( 'Show Buttons in', 'classified-listing' ),
		'type'    => 'multi_checkbox',
		'default' => [ 'listing' ],
		'options' => [
			'listing'    => esc_html__( 'Listing detail page', 'classified-listing' ),
			'listings'   => esc_html__( 'Listings page', 'classified-listing' ),
			'categories' => esc_html__( 'Categories page', 'classified-listing' ),
			'locations'  => esc_html__( 'Locations page', 'classified-listing' )
		]
	],
);

return apply_filters( 'rtcl_general_social_share_settings_options', $options );
