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
	'email_sender_option_section' => [
		'title'       => esc_html__( 'Email Sender Options', 'classified-listing' ),
		'type'        => 'section',
		'description' => '',
	],
	'from_name'                   => [
		'title'       => esc_html__( 'From Name', 'classified-listing' ),
		'type'        => 'text',
		'default'     => Functions::get_blogname(),
		'description' => esc_html__( 'The name system generated emails are sent from. This should probably be your site or directory name.',
			'classified-listing' )
	],
	'from_email'                  => [
		'title'       => esc_html__( 'From Email', 'classified-listing' ),
		'type'        => 'text',
		'default'     => get_option( 'admin_email' ),
		'description' => esc_html__( "The sender email address should belong to the site domain.", 'classified-listing' )
	],
	'admin_notice_emails'         => [
		'title'       => esc_html__( 'Admin Notification Emails', 'classified-listing' ),
		'type'        => 'textarea',
		'css'         => 'max-width:400px; height: 75px;',
		'default'     => get_option( 'admin_email' ),
		'description' => esc_html__( 'Enter the email address(es) that should receive admin notification emails, one per line.',
			'classified-listing' )
	],
	'email_template_section'      => [
		'title'       => esc_html__( 'Others Options', 'classified-listing' ),
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
	'email_content_type'          => [
		'title'       => esc_html__( 'Email Content Type', 'classified-listing' ),
		'type'        => 'select',
		'default'     => 'html',
		'class'       => 'rtcl-select2',
		'description' => esc_html__( 'Choose which format of email to send.', 'classified-listing' ),
		'options'     => Options::get_email_type_options()
	],
	'email_header_image'          => [
		'title' => esc_html__( 'Header Image', 'classified-listing' ),
		'type'  => 'image'
	],
	'email_footer_text'           => [
		'title'       => esc_html__( 'Footer Text', 'classified-listing' ),
		'description' => esc_html__( 'The text to appear in the footer of emails.', 'classified-listing' ) . ' '
		                 . sprintf(/* translators: Site title */ esc_html__( 'Available placeholders: %s', 'classified-listing' ), '{site_title}' ),
		'css'         => 'max-width:400px; height: 75px;',
		'placeholder' => esc_html__( 'N/A', 'classified-listing' ),
		'type'        => 'textarea',
		'default'     => '{site_title}'
	],
	'email_base_color'            => [
		'title'       => esc_html__( 'Base Color', 'classified-listing' ),
		'description' => sprintf( /* translators: color code */ esc_html__( 'The base color for email templates. Default %s.',
			'classified-listing' ), '<code>#0071bd</code>' ),
		'type'        => 'color',
		'css'         => 'width:6em;',
		'default'     => '#0071bd',
	],
	'email_background_color'      => [
		'title'       => esc_html__( 'Background Color', 'classified-listing' ),
		'description' => sprintf( /* translators: color code */ esc_html__( 'The background color for email templates. Default %s.',
			'classified-listing' ), '<code>#f7f7f7</code>' ),
		'type'        => 'color',
		'css'         => 'width:6em;',
		'default'     => '#f7f7f7',
	],
	'email_body_background_color' => [
		'title'       => esc_html__( 'Body Background Color', 'classified-listing' ),
		'description' => sprintf( /* translators: color code */ esc_html__( 'The main body background color. Default %s.', 'classified-listing' ),
			'<code>#ffffff</code>' ),
		'type'        => 'color',
		'css'         => 'width:6em;',
		'default'     => '#ffffff',
	],
	'email_text_color'            => [
		'title'       => esc_html__( 'Body Text Color', 'classified-listing' ),
		'description' => sprintf( /* translators: color code */ esc_html__( 'The main body text color. Default %s.', 'classified-listing' ),
			'<code>#3c3c3c</code>' ),
		'type'        => 'color',
		'css'         => 'width:6em;',
		'default'     => '#3c3c3c',
	],
];

return apply_filters( 'rtcl_email_settings_options', $options );
