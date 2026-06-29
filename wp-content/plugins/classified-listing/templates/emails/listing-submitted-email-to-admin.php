<?php
/**
 * new listing email notification to owner
 * This template can be overridden by copying it to yourtheme/classified-listing/emails/new-post-notification-user.php
 * @author        RadiusTheme
 * @package       ClassifiedListing/Templates/Emails
 * @version       1.3.0
 *
 * @var RtclEmail $email
 * @var Listing $listing
 */

use Rtcl\Models\Listing;
use Rtcl\Models\RtclEmail;
use Rtcl\Helpers\Functions;

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @hooked RtclEmails::email_header() Output the email header
 */
do_action( 'rtcl_email_header', $email ); ?>
	<p style="margin: 0 0 16px;"><?php esc_html_e( 'Hi Administrator,', 'classified-listing' ); ?></p>
	<p style="margin: 0 0 16px;"><?php /* translators:  website url */
		printf( esc_html__( 'You have received a new listing on the website %s.', 'classified-listing' ), esc_html( Functions::get_blogname() ) ) ?></p>
	<p style="margin: 0 0 16px;"><?php printf( '<strong>%1$s</strong> <a href="%2$s">%3$s</a>',
			esc_html__( 'Listing :', 'classified-listing' ),
			esc_url( $listing->get_the_permalink() ),
			esc_html( $listing->get_the_title() )
		); ?></p>
<?php

/**
 * @hooked RtclEmails::email_footer() Output the email footer
 */
do_action( 'rtcl_email_footer', $email );
