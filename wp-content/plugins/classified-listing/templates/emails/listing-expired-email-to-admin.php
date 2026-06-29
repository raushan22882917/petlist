<?php
/**
 * User email to renew listing
 * This template can be overridden by copying it to yourtheme/classified-listing/emails/listing-renewal-email-to-owner.php
 * @author        RadiusTheme
 * @package       ClassifiedListing/Templates/Emails
 * @version       1.3.0
 *
 * @var RtclEmail $email
 * @var Listing $listing
 */

use Rtcl\Helpers\Functions;
use Rtcl\Models\Listing;
use Rtcl\Models\RtclEmail;

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @hooked RtclEmails::email_header() Output the email header
 */
do_action( 'rtcl_email_header', $email ); ?>
	<p style="margin: 0 0 16px;"><?php esc_html_e( 'Hi Administrator,', 'classified-listing' ); ?></p>
	<p style="margin: 0 0 16px;"><?php
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		printf( /* translators: Notification links */__( 'This notification was for the listing on the website %1$s "%2$s" and is expired.', 'classified-listing' ),
			sprintf( '<strong>%s</strong>', esc_html( Functions::get_blogname() ) ),
			esc_html( $listing->get_the_title() ) ) ?></p>
	<p style="margin: 0 0 16px;"><?php printf( '<strong>%1$s</strong> <a href="%2$s">%3$s</a>',
			esc_html__( 'Listing :', 'classified-listing' ),
			esc_url( get_edit_post_link( $listing->get_id() ) ),
			esc_html( $listing->get_the_title() ) ); ?></p>
	<p style="margin: 0 0 16px;"><?php printf( '<strong>%s</strong> %s', esc_html__( 'Expired on:', 'classified-listing' ), esc_html( $email->get_placeholders_item( '{expiration_date}' ) ) ); ?></p>
	<p style="margin: 0 0 16px;"><?php esc_html_e( 'Please do not respond to this message. It is automatically generated and is for information purposes only.', 'classified-listing' ); ?></p>
<?php

/**
 * @hooked RtclEmails::email_footer() Output the email footer
 */
do_action( 'rtcl_email_footer', $email );
