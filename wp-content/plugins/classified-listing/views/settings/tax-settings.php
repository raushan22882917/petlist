<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for Payment
 */
$options = array(
	'tax_section'         => [
		'title'       => esc_html__( 'Tax Settings', 'classified-listing' ),
		'type'        => 'section',
		'description' => '',
	],
	'enable_tax'          => [
		'title' => esc_html__( 'Enable Tax', 'classified-listing' ),
		'type'  => 'checkbox',
		'label' => esc_html__( 'Enable tax.', 'classified-listing' ),
	],
	'enable_multiple_tax' => [
		'title'       => esc_html__( 'Enable Multiple Tax', 'classified-listing' ),
		'type'        => 'checkbox',
		'description' => esc_html__( 'Apply multiple tax option for same area.', 'classified-listing' ),
	],
);

return apply_filters( 'rtcl_tax_settings_options', $options );
