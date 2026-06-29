<?php

use Rtcl\Helpers\Functions;
use Rtcl\Resources\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for misc
 */
$options = [
	'recaptcha_section'    => [
		'title' => esc_html__( 'Google reCAPTCHA', 'classified-listing' ),
		'type'  => 'section',
	],
	'recaptcha_forms'      => [
		'title'   => esc_html__( 'Enable reCAPTCHA in', 'classified-listing' ),
		'type'    => 'multi_checkbox',
		'options' => Options::get_recaptcha_form_list()
	],
	'recaptcha_version'    => [
		'title'       => esc_html__( 'reCAPTCHA Version', 'classified-listing' ),
		'type'        => 'radio',
		'default'     => 2,
		'options'     => [
			3 => esc_html__( 'reCAPTCHA v3', 'classified-listing' ),
			2 => esc_html__( 'reCAPTCHA v2', 'classified-listing' ),
		],
		'description' => esc_html__( 'Google reCAPTCHA v2 will show in the form and reCAPTCHA v3 will show in the browser corner.', 'classified-listing' )
	],
	'recaptcha_site_key'   => [
		'title'       => esc_html__( 'Site Key', 'classified-listing' ),
		'type'        => 'text',
		'description' => sprintf(
			'<span style="color:#c90808; font-weight: 500">%1$s</span> %2$s <a target="_blank" href="%4$s">%3$s</a>',
			esc_html__( 'Google reCAPTCHA v2 and v3, site key and secrect key will be different.', 'classified-listing' ),
			esc_html__( 'How to generate reCAPTCHA', 'classified-listing' ),
			esc_html__( 'Click here', 'classified-listing' ),
			'https://www.radiustheme.com/docs/faqs/add-re-captcha/'
		)
	],
	'recaptcha_secret_key' => [
		'title' => esc_html__( 'Secret Key', 'classified-listing' ),
		'type'  => 'text'
	],
];

return apply_filters( 'rtcl_misc_settings_options', $options );