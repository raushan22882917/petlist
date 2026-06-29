<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for Payment
 */
$options = array(
	'listing_label_section'  => array(
		'title' => esc_html__( 'Listing Labels', 'classified-listing' ),
		'type'  => 'section',
	),
	'new_listing_label'      => [
		'title'       => esc_html__( 'New Listings Label', 'classified-listing' ),
		'type'        => 'text',
		'default'     => esc_html__( "New", 'classified-listing' ),
		'description' => esc_html__( 'Enter the text you want to use inside the "New" tag.', 'classified-listing' )
	],
	'new_listing_threshold'  => [
		'title'       => esc_html__( 'New Listing Threshold (days)', 'classified-listing' ),
		'type'        => 'number',
		'default'     => 3,
		'description' => esc_html__( 'Enter the number of days the listing will be tagged as "New" from the day it is published.',
			'classified-listing' )
	],
	'listing_featured_label' => [
		'title'       => esc_html__( 'Feature Listings Label', 'classified-listing' ),
		'type'        => 'text',
		'default'     => esc_html__( "Featured", 'classified-listing' ),
		'description' => esc_html__( 'Enter the text you want to use inside the "Featured" tag.', 'classified-listing' )
	],
);

return apply_filters( 'rtcl_general_listing_label_settings_options', $options );
