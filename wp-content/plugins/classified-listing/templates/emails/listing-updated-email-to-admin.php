<?php
/**
 * Listing Updated email notification to owner
 * This template can be overridden by copying it to yourtheme/classified-listing/emails/listing-updated-email-to-admin.php
 *
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
		printf(
		/* translators:  Listing update url */
			__( 'Listing %1$s is updated on the website %2$s.', 'classified-listing' ),
			sprintf( '<a href="%1$s">%2$s</a>', esc_url( $listing->get_the_permalink() ), esc_html( $listing->get_the_title() ) ),
			esc_html( Functions::get_blogname() )
		) ?></p>
<?php

/**
 * @hooked RtclEmails::email_footer() Output the email footer
 */
do_action( 'rtcl_email_footer', $email );
