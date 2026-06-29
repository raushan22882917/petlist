<?php

use Rtcl\Helpers\Functions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for Payment
 */
$options = array(
	'map_section'           => [
		'title' => esc_html__( 'Map', 'classified-listing' ),
		'type'  => 'section',
	],
	'has_map'               => [
		'title' => esc_html__( 'Enable Map', 'classified-listing' ),
		'type'  => 'checkbox',
		'label' => esc_html__( 'Allow users to add map for their listings', 'classified-listing' )
	],
	'map_type'              => [
		'title'   => esc_html__( 'Map Type', 'classified-listing' ),
		'type'    => 'radio',
		'default' => 'osm',
		'options' => [
			'osm'    => esc_html__( 'OpenStreetMap', 'classified-listing' ),
			'google' => esc_html__( 'GoogleMap', 'classified-listing' ),
		]
	],
	'map_api_key'           => [
		'title'       => esc_html__( 'Google Map API Key', 'classified-listing' ),
		'type'        => 'text',
		'description' => sprintf(
			'%1$s <a target="_blank" href="%3$s">%2$s</a>',
			esc_html__( 'How to generate Google Map API key', 'classified-listing' ),
			esc_html__( 'Click here', 'classified-listing' ),
			'https://www.radiustheme.com/docs/classified-listing/docs/main-settings/misc-settings/'
		),
		'dependency'  => [
			'rules' => [
				"input[id^=rtcl_misc_map_settings-map_type]" => [
					'type'  => 'equal',
					'value' => 'google'
				]
			]
		]
	],
	'map_zoom_level'        => [
		'title'   => esc_html__( 'Map Zoom Level', 'classified-listing' ),
		'type'    => 'select',
		'default' => 10,
		'options' => [ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18 ]
	],
	'map_center'            => [
		'title'       => esc_html__( 'Map Default Location', 'classified-listing' ),
		'type'        => 'map_center',
		// phpcs:ignore WordPress.WP.I18n.NoHtmlWrappedStrings
		'description' => 'google' === Functions::get_map_type() ? wp_kses( __( '<span style="color: red">Map Api key is required.</span>',
			'classified-listing' ), [
			'span' => [
				'style' => [ 'color' ]
			]
		] ) : ''
	],
	'maxmind_section'       => [
		'title'       => esc_html__( 'MaxMind Geolocation', 'classified-listing' ),
		'description' => esc_html__( 'An integration for utilizing MaxMind to do Geolocation lookups. Please note that this integration will only do country lookups.',
			'classified-listing' ),
		'type'        => 'section',
	],
	'maxmind_license_key'   => [
		'title'       => __( 'MaxMind License Key', 'classified-listing' ),
		'type'        => 'password',
		'description' => sprintf(
		/* translators: %1$s: Documentation URL */
			__(
				'The key that will be used when dealing with MaxMind Geolocation services. You can read how to generate one in <a href="%1$s">MaxMind Geolocation Integration documentation</a>.',
				'classified-listing'
			),
			'https://docs.woocommerce.com/document/maxmind-geolocation-integration/'
		),
		'default'     => '',
	],
	'maxmind_database_path' => [
		'title'       => __( 'Database File Path', 'classified-listing' ),
		'type'        => 'html',
		'html'        => sprintf( '<strong>%s</strong>', $this->maxMindDatabaseService()->get_database_path() ),
		'description' => esc_html__( 'The location that the MaxMind database should be stored. By default, the integration will automatically save the database here.',
			'classified-listing' )
	],
);

return apply_filters( 'rtcl_misc_map_settings_options', $options );
