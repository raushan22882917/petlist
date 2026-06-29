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
 * @var array     $data
 * @var Listing   $listing
 */

use Rtcl\Helpers\Functions;
use Rtcl\Models\Listing;
use Rtcl\Models\RtclEmail;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @hooked RtclEmails::email_header() Output the email header
 */
do_action( 'rtcl_email_header', $email ); ?>
	<p style="margin: 0 0 16px;"><?php esc_html_e( 'Hi Administrator,', 'classified-listing' ); ?></p>
	<p style="margin: 0 0 16px;"><?php /* translators: Site name */
		printf( esc_html__( 'A listing on your website %s received a message.', 'classified-listing' ), esc_html( Functions::get_blogname() ) ) ?></p>
	<p style="margin: 0 0 16px;"><?php printf( '<strong>%1$s</strong> <a href="%2$s">%3$s</a>',
			esc_html__( 'Listing :', 'classified-listing' ),
			esc_url( $listing->get_the_permalink() ),
			esc_html( $listing->get_the_title() ) ); ?></p>
	<?php printf( '<strong>%s</strong> %s', esc_html__( 'Sender name: ', 'classified-listing' ), esc_html( $data['name'] ) ); ?><br>
	<?php printf( '<strong>%s</strong> %s', esc_html__( 'Sender email: ', 'classified-listing' ), esc_html( $data['email'] ) ); ?><br>
<?php if ( ! empty( $data['phone'] ) ): ?>
	<?php printf( '<strong>%s</strong> %s', esc_html__( 'Sender phone: ', 'classified-listing' ), esc_html( $data['phone'] ) ); ?><br>
<?php endif ?>
	<?php printf( '<strong>%s</strong><br>%s', esc_html__( 'Sender message: ', 'classified-listing' ), wp_kses_post( wp_unslash( nl2br( $data['message'] ) ) ) ); ?>
	<br><br>
<?php

/**
 * @hooked RtclEmails::email_footer() Output the email footer
 */
do_action( 'rtcl_email_footer', $email );
