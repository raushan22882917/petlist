<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for Style
 */
$options = array(
	'gs_section'        => array(
		'title'       => esc_html__( 'Global Style', 'classified-listing' ),
		'type'        => 'section',
		'description' => '',
	),
	'primary'           => array(
		'title' => esc_html__( 'Primary', 'classified-listing' ),
		'type'  => 'color',
	),
	'link'              => array(
		'title' => esc_html__( 'Link Color', 'classified-listing' ),
		'type'  => 'color',
	),
	'link_hover'        => array(
		'title' => esc_html__( 'Link Color on Hover', 'classified-listing' ),
		'type'  => 'color',
	),
	'button'            => array(
		'title' => esc_html__( 'Button Background', 'classified-listing' ),
		'type'  => 'color',
	),
	'button_hover'      => array(
		'title' => esc_html__( 'Button Hover Background', 'classified-listing' ),
		'type'  => 'color',
	),
	'button_text'       => array(
		'title' => esc_html__( 'Button Text Color', 'classified-listing' ),
		'type'  => 'color',
	),
	'button_hover_text' => array(
		'title' => esc_html__( 'Button Text Color on Hover', 'classified-listing' ),
		'type'  => 'color',
	),
	'lbl_section'       => array(
		'title' => esc_html__( 'Label Style', 'classified-listing' ),
		'type'  => 'section',
	),
	'new'               => [
		'title' => esc_html__( 'New Label Background Color', 'classified-listing' ),
		'type'  => 'color',
	],
	'new_text'          => [
		'title' => esc_html__( 'New Label Text Color', 'classified-listing' ),
		'type'  => 'color',
	],
	'feature'           => [
		'title' => esc_html__( 'Feature Label Background Color', 'classified-listing' ),
		'type'  => 'color',
	],
	'feature_text'      => [
		'title' => esc_html__( 'Feature Label Text Color', 'classified-listing' ),
		'type'  => 'color',
	],
	'misc_section'      => [
		'title' => esc_html__( 'Others Style', 'classified-listing' ),
		'type'  => 'section',
	],
	'container_class'   => [
		'title'       => esc_html__( 'Container Class', 'classified-listing' ),
		'type'        => 'text',
		'description' => esc_html__( 'Add theme container class here to adjust width', 'classified-listing' ),
	],
	'sidebar_width'     => [
		'title'        => esc_html__( 'Sidebar Width', 'classified-listing' ),
		'type'         => 'range',
		'units'        => [
			'px' => esc_html__( 'px', 'classified-listing' ),
			'%'  => esc_html__( '%', 'classified-listing' ),
		],
		'default'      => '28',
		'default_unit' => '%',
	],
);

return apply_filters( 'rtcl_style_settings_options', $options );
