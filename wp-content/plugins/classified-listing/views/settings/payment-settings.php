<?php

use Rtcl\Resources\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for Payment
 */
$options = [
	'payment_gs_section'           => [
		'title'       => esc_html__( 'General Settings', 'classified-listing' ),
		'type'        => 'section',
		'description' => '',
	],
	'payment'                      => [
		'title' => esc_html__( 'Enable Payment', 'classified-listing' ),
		'type'  => 'checkbox',
		'label' => esc_html__( 'Charge users for posting classified ads on your site.', 'classified-listing' ),
	],
	'use_https'                    => [
		'title' => esc_html__( 'Enforce SSL on Checkout', 'classified-listing' ),
		'type'  => 'checkbox',
		'label' => esc_html__( 'Check this to force users to be redirected to the secure checkout page. You must have an SSL certificate installed to use this option.',
			'classified-listing' )
	],
	'billing_address_disabled'     => [
		'title' => esc_html__( 'Disable Billing Address', 'classified-listing' ),
		'type'  => 'checkbox',
		'label' => esc_html__( 'Disable billing address', 'classified-listing' )
	],
	'currency_gs_section'          => [
		'title'       => esc_html__( 'Currency Options', 'classified-listing' ),
		'type'        => 'section',
		'description' => esc_html__( 'The following options affect how prices are displayed on the frontend.', 'classified-listing' ),
	],
	'currency'                     => [
		'title'   => __( 'Currency', 'classified-listing' ),
		'type'    => 'select',
		'class'   => 'rtcl-select2',
		'options' => Options::get_currencies(),
	],
	'currency_position'            => [
		'title'   => esc_html__( 'Currency Position', 'classified-listing' ),
		'type'    => 'select',
		'class'   => 'rtcl-select2',
		'options' => Options::get_currency_positions()
	],
	'currency_thousands_separator' => [
		'title'       => esc_html__( 'Thousands Separator', 'classified-listing' ),
		'type'        => 'text',
		'css'         => 'width:50px',
		'description' => esc_html__( 'The symbol (usually , or .) to separate thousands.', 'classified-listing' ),
		'default'     => ',',
	],
	'currency_decimal_separator'   => [
		'title'       => esc_html__( 'Decimal Separator', 'classified-listing' ),
		'type'        => 'text',
		'css'         => 'width:50px',
		'description' => esc_html__( 'The symbol (usually , or .) to separate decimal points.',
			'classified-listing' ),
		'default'     => '.',
	]
];

return apply_filters( 'rtcl_payment_settings_options', $options );
