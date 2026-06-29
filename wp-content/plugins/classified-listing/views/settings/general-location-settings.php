<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for Payment
 */
$options = array(
	'location_section'      => array(
		'title'       => esc_html__( 'Location Settings', 'classified-listing' ),
		'type'        => 'section',
		'description' => '',
	),
	'location_type'         => array(
		'title'   => esc_html__( 'Location Type', 'classified-listing' ),
		'type'    => 'radio',
		'default' => 'local',
		'options' => [
			'local' => esc_html__( 'Local (WordPress default location taxonomy)', 'classified-listing' ),
			'geo'   => __( 'GEO Location (Set Map type => Settings > Misc > Map Type)', 'classified-listing' )
		],
	),
	'location_level_first'  => array(
		'title'   => esc_html__( 'First Level Location', 'classified-listing' ),
		'type'    => 'text',
		'default' => esc_html__( 'State', 'classified-listing' ),
	),
	'location_level_second' => array(
		'title'   => esc_html__( 'Second Level Location', 'classified-listing' ),
		'type'    => 'text',
		'default' => esc_html__( 'City', 'classified-listing' ),
	),
	'location_level_third'  => array(
		'title'   => esc_html__( 'Third Level Location', 'classified-listing' ),
		'type'    => 'text',
		'default' => esc_html__( 'Town', 'classified-listing' ),
	),
);

return apply_filters( 'rtcl_general_location_settings_options', $options );
