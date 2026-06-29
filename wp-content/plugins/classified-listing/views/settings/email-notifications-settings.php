<?php

use Rtcl\Resources\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for Payment
 */
$options = [
	'gs_section'   => [
		'title'       => esc_html__( 'Enable / Disable Notifications', 'classified-listing' ),
		'type'        => 'section',
		'description' => '',
	],
	'notify_admin' => [
		'title'   => esc_html__( 'Notify Admin via Email when', 'classified-listing' ),
		'type'    => 'multi_checkbox',
		'default' => [ 'register_new_user', 'listing_submitted', 'order_created', 'payment_received' ],
		'options' => Options::get_admin_email_notification_options()
	],
	'notify_users' => [
		'title'   => esc_html__( 'Notify Users via Email when Their', 'classified-listing' ),
		'type'    => 'multi_checkbox',
		'default' => [
			'register_new_user',
			'listing_submitted',
			'listing_published',
			'listing_renewal',
			'listing_expired',
			'remind_renewal',
			'order_created',
			'user_import',
			'order_completed'
		],
		'options' => Options::get_user_email_notification_options()
	]
];

return apply_filters( 'rtcl_email_notifications_settings_options', $options );
