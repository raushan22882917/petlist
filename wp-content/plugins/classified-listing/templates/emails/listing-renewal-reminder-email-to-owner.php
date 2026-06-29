<?php
/**
 * User email to renew listing
 * This template can be overridden by copying it to yourtheme/classified-listing/emails/listing-renewal-reminder-email-to-owner.php
 *
 * @author        RadiusTheme
 * @package       ClassifiedListing/Templates/Emails
 * @version       1.3.0
 * @var RtclEmail $email
 * @var Listing $listing
 */

use Rtcl\Models\Listing;
use Rtcl\Models\RtclEmail;

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @hooked RtclEmails::email_header() Output the email header
 */
do_action( 'rtcl_email_header', $email ); ?>
	<p style="margin: 0 0 16px;"><?php /* translators: user name */
		printf( esc_html__( 'Hi %s,', 'classified-listing' ), esc_html( $listing->get_owner_name() ) ); ?></p>
	<p style="margin: 0 0 16px;"><?php
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		printf(
		/* translators: Renew listing notice */
			__( 'We\'ve noticed that you haven\'t renewed your listing %1$s at %2$s and just wanted to remind you that it expired on %3$s. Please remember you can still renew it here: %4$s', 'classified-listing' ),
			sprintf('<strong>%s</strong>',esc_html( $listing->get_the_title() )),
			esc_url( $email->get_placeholders_item( '{site_link}' ) ),
			sprintf('<strong>%s</strong>',esc_html( $email->get_placeholders_item( '{expiration_date}' ) )),
			esc_url( $email->get_placeholders_item( '{renewal_link}' ) )
		) ?></p>
<?php

/**
 * @hooked RtclEmails::email_footer() Output the email footer
 */
do_action( 'rtcl_email_footer', $email );
