<?php
/**
 * new listing email notification to owner
 * This template can be overridden by copying it to yourtheme/classified-listing/emails/new-post-notification-user.php
 *
 * @author        RadiusTheme
 * @package       ClassifiedListing/Templates/Emails
 * @version       1.3.0
 *
 * @var RtclEmail $email
 * @var Listing   $listing
 * @var array     $data
 */

use Rtcl\Models\Listing;
use Rtcl\Models\RtclEmail;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @hooked RtclEmails::email_header() Output the email header
 */
do_action( 'rtcl_email_header', $email ); ?>
	<p style="margin: 0 0 16px;"><?php /* translators:  owner */
		printf( esc_html__( 'Hi %s,', 'classified-listing' ), esc_html( $listing->get_owner_name() ) ); ?></p>
	<p style="margin: 0 0 16px;"><?php printf( '%1$s <a href="%2$s">%3$s.</a>',
			esc_html__( 'You have received a reply from your listing at', 'classified-listing' ),
			esc_url( $listing->get_the_permalink() ),
			esc_html( $listing->get_the_title() ) ) ?></p>
	<?php printf( '<strong>%s</strong> %s', esc_html__( 'Name: ', 'classified-listing' ), esc_html( $data['name'] ) ); ?><br>
	<?php printf( '<strong>%s</strong> %s', esc_html__( 'Email: ', 'classified-listing' ), esc_html( $data['email'] ) ); ?><br>
<?php if ( ! empty( $data['phone'] ) ): ?>
	<?php printf( '<strong>%s</strong> %s', esc_html__( 'Phone: ', 'classified-listing' ), esc_html( $data['phone'] ) ); ?><br>
<?php endif ?>
	<?php printf( '<strong>%s</strong><br>%s', esc_html__( 'Message: ', 'classified-listing' ), wp_kses_post( wp_unslash( nl2br( $data['message'] ) ) ) ); ?>
	<br><br>
<?php

/**
 * @hooked RtclEmails::email_footer() Output the email footer
 */
do_action( 'rtcl_email_footer', $email );
