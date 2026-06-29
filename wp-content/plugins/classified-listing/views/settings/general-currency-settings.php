<?php

use Rtcl\Resources\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for Payment
 */
$options = array(
	'currency_section'             => array(
		'title'       => esc_html__( 'Currency Options', 'classified-listing' ),
		'type'        => 'section',
		'description' => esc_html__( 'The following options affect how prices are displayed on the frontend.',
			'classified-listing' ),
	),
	'currency'                     => array(
		'title'   => esc_html__( 'Currency', 'classified-listing' ),
		'type'    => 'select',
		'class'   => 'rtcl-select2',
		'options' => Options::get_currencies(),
	),
	'currency_position'            => array(
		'title'   => esc_html__( 'Currency Position', 'classified-listing' ),
		'type'    => 'select',
		'class'   => 'rtcl-select2',
		'options' => Options::get_currency_positions()
	),
	'currency_thousands_separator' => array(
		'title'       => esc_html__( 'Thousands Separator', 'classified-listing' ),
		'type'        => 'text',
		'css'         => 'width:50px',
		'description' => esc_html__( 'The symbol (usually , or .) to separate thousands.', 'classified-listing' ),
		'default'     => ','
	),
	'currency_decimal_separator'   => array(
		'title'       => esc_html__( 'Decimal Separator', 'classified-listing' ),
		'type'        => 'text',
		'css'         => 'width:50px',
		'description' => esc_html__( 'The symbol (usually , or .) to separate decimal points.',
			'classified-listing' ),
		'default'     => '.'
	),
);

return apply_filters( 'rtcl_general_currency_settings_options', $options );
