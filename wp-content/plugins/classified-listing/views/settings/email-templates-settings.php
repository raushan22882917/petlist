<?php

use Rtcl\Helpers\Functions;
use Rtcl\Resources\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for Payment
 */
$options = [
	'email_template_section'     => [
		'title'       => esc_html__( 'Email Templates', 'classified-listing' ),
		'type'        => 'section',
		'description' => sprintf( '<strong>%s</strong>', esc_html__( "You can use the following placeholders", "classified-listing" ) ) . '<br>' .
		                 '{site_name} - ' . esc_html__( 'Your site name', 'classified-listing' ) . '<br>' .
		                 '{site_link} - ' . esc_html__( 'Your site name with link', 'classified-listing' ) . '<br>' .
		                 '{site_url} - ' . esc_html__( 'Your site url with link', 'classified-listing' ) . '<br>' .
		                 '{admin_email} - ' . esc_html__( 'Administration Email Address', 'classified-listing' ) . '<br>' .
		                 '{renewal_link} - ' . esc_html__( 'Link to renewal page', 'classified-listing' ) . '<br>' .
		                 '{today} - ' . esc_html__( 'Current date', 'classified-listing' ) . '<br>' .
		                 '{now} - ' . esc_html__( 'Current time', 'classified-listing' ) . '<br><br>' .
		                 wp_kses(
		                 /* translators:  link */
			                 sprintf( __( 'This section lets you customize the Classified Listing emails. <a href="%s" target="_blank">Click here to preview your email template.</a>',
				                 "classified-listing" ), wp_nonce_url( admin_url( '?preview_rtcl_mail=true' ), 'preview-mail' ) ),
			                 [
				                 'a' => [
					                 'href'   => true,
					                 'target' => true
				                 ]
			                 ]
		                 )
	],
	'listing_submitted_section'  => [
		'title'       => esc_html__( 'Listing Submitted Email ( Confirmation )', 'classified-listing' ),
		'type'        => 'section',
		'description' => file_exists( Functions::get_theme_template_path( 'emails/listing-submitted-email-to-owner.php' ) )
			?
			sprintf( /* translators: template url */ esc_html__( "Template is override at %s", 'classified-listing' ),
				'<code>' . Functions::get_theme_template_file( 'emails/listing-submitted-email-to-owner.php' ) . '</code>' )
			:
			sprintf( /* translators: template url */ esc_html__( 'To override and edit this email template copy %1$s to your theme folder: %2$s.',
				'classified-listing' ), '<code>' . esc_html( Functions::get_plugin_template_file( 'emails/listing-submitted-email-to-owner.php' ) ) . '</code>',
				'<code>' . esc_html( Functions::get_theme_template_file( 'emails/listing-submitted-email-to-owner.php' ) ) . '</code>'
			)
	],
	'listing_submitted_subject'  => [
		'title'   => esc_html__( 'Subject', 'classified-listing' ),
		'type'    => 'text',
		'default' => esc_html__( '[{site_title}] Listing "{listing_title}" is received', 'classified-listing' ),
	],
	'listing_submitted_heading'  => [
		'title'   => esc_html__( 'Heading', 'classified-listing' ),
		'type'    => 'text',
		'default' => esc_html__( 'Your listing is received', 'classified-listing' ),
	],
	'listing_published_section'  => [
		'title' => esc_html__( 'Listing Published / Approved Email', 'classified-listing' ),
		'type'  => 'section',
	],
	'listing_published_subject'  => [
		'title'   => esc_html__( 'Subject', 'classified-listing' ),
		'type'    => 'text',
		'default' => esc_html__( '[{site_title}] Listing "{listing_title}" is published', 'classified-listing' ),
	],
	'listing_published_heading'  => [
		'title'   => esc_html__( 'Heading', 'classified-listing' ),
		'type'    => 'text',
		'default' => esc_html__( 'Your listing is published', 'classified-listing' )
	],
	'renewal_section'            => [
		'title' => esc_html__( 'Listing Renewal Email', 'classified-listing' ),
		'type'  => 'section',
	],
	'renewal_email_threshold'    => [
		'title'       => esc_html__( 'Listing Renewal Email Threshold (in days)', 'classified-listing' ),
		'type'        => 'number',
		'default'     => 3,
		'description' => esc_html__( 'Configure how many days before listing expiration is the renewal email sent.', 'classified-listing' )
	],
	'renewal_subject'            => [
		'title'   => esc_html__( 'Subject', 'classified-listing' ),
		'type'    => 'text',
		'default' => esc_html__( '[{site_name}] {listing_title} - Expiration notice', 'classified-listing' ),
	],
	'renewal_heading'            => [
		'title'   => esc_html__( 'Heading', 'classified-listing' ),
		'type'    => 'text',
		'default' => esc_html__( 'Expiration notice', 'classified-listing' ),
	],
	'expired_section'            => [
		'title' => esc_html__( 'Listing Expired Email', 'classified-listing' ),
		'type'  => 'section',
	],
	'expired_subject'            => [
		'title'   => esc_html__( 'Subject', 'classified-listing' ),
		'type'    => 'text',
		'default' => esc_html__( '[{site_title}] {listing_title} - Expiration notice', 'classified-listing' ),
	],
	'expired_heading'            => [
		'title'   => esc_html__( 'Heading', 'classified-listing' ),
		'type'    => 'text',
		'default' => esc_html__( 'Expiration notice', 'classified-listing' ),
	],
	'renewal_reminder_section'   => [
		'title' => esc_html__( 'Renewal Reminder Email', 'classified-listing' ),
		'type'  => 'section',
	],
	'renewal_reminder_threshold' => [
		'title'       => esc_html__( 'Listing renewal reminder email threshold (in days)', 'classified-listing' ),
		'type'        => 'number',
		'default'     => 3,
		'description' => esc_html__( 'Configure how many days after the expiration of a listing an email reminder should be sent to the owner.',
			'classified-listing' )
	],
	'renewal_reminder_subject'   => [
		'title'   => esc_html__( 'Subject', 'classified-listing' ),
		'type'    => 'text',
		'default' => esc_html__( '[{site_title}] {listing_title} - Renewal reminder', 'classified-listing' ),
	],
	'renewal_reminder_heading'   => [
		'title'   => esc_html__( 'Heading', 'classified-listing' ),
		'type'    => 'text',
		'default' => esc_html__( 'Renewal reminder', 'classified-listing' ),
	],
	'order_created_section'      => [
		'title' => esc_html__( 'New Order', 'classified-listing' ),
		'type'  => 'section',
	],
	'order_created_subject'      => [
		'title'   => esc_html__( 'Subject', 'classified-listing' ),
		'type'    => 'text',
		'default' => esc_html__( '[{site_title}] #{order_number} Thank you for your order', 'classified-listing' )
	],
	'order_created_heading'      => [
		'title'   => esc_html__( 'Heading', 'classified-listing' ),
		'type'    => 'text',
		'default' => esc_html__( 'New Order: #{order_number}', 'classified-listing' )
	],
	'order_completed_section'    => [
		'title' => esc_html__( 'Order Completed Email', 'classified-listing' ),
		'type'  => 'section',
	],
	'order_completed_subject'    => [
		'title'   => esc_html__( 'Subject', 'classified-listing' ),
		'type'    => 'text',
		'default' => esc_html__( '[{site_title}] : #{order_number} Order is completed.', 'classified-listing' )
	],
	'order_completed_heading'    => [
		'title'   => esc_html__( 'Heading', 'classified-listing' ),
		'type'    => 'text',
		'default' => esc_html__( 'Payment is completed: #{order_number}', 'classified-listing' )
	],
	'listing_contact_section'    => [
		'title' => esc_html__( 'Listing Contact Email', 'classified-listing' ),
		'type'  => 'section',
	],
	'contact_subject'            => [
		'title'   => esc_html__( 'Subject', 'classified-listing' ),
		'type'    => 'text',
		'default' => esc_html__( '[{site_title}] Contact via "{listing_title}"', 'classified-listing' )
	],
	'contact_heading'            => [
		'title'   => esc_html__( 'Heading', 'classified-listing' ),
		'type'    => 'text',
		'default' => esc_html__( 'Thank you for mail', 'classified-listing' )
	]
];

return apply_filters( 'rtcl_email_templates_settings_options', $options );
