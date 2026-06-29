<?php
/**
 * new listing email notification to owner
 * This template can be overridden by copying it to yourtheme/classified-listing/emails/report-abuse-email-to-admin.php
 *
 * @author        RadiusTheme
 * @package       ClassifiedListing/Templates/Emails
 * @version       1.3.0
 *
 * @var array     $data
 * @var RtclEmail $email
 * @var Listing   $listing
 * @var bool      $sent_to_admin
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
	<p style="margin: 0 0 16px;"><?php esc_html_e( 'Hi Administrator,', 'classified-listing' ); ?></p>
	<p style="margin: 0 0 16px;"><?php
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		printf(
		// translators:  abuse report link
			__( 'This is an email abuse report for a listing at %s.', 'classified-listing' ),
			sprintf( '<a href="%1$s">%2$s</a>', esc_url( $listing->get_the_permalink() ), esc_html( $listing->get_the_title() ) )
		) ?></p>
	<?php printf( '<strong>%s</strong> %s', esc_html__( 'Sender name: ', 'classified-listing' ), esc_html( $data['name'] ) ); ?><br>
	<?php printf( '<strong>%s</strong> %s', esc_html__( 'Sender email: ', 'classified-listing' ), esc_html( $data['email'] ) ); ?><br>
	<?php printf( '<strong>%s</strong><br>%s', esc_html__( 'Sender message:', 'classified-listing' ), wp_kses_post( wp_unslash( nl2br( $data['message'] ) ) ) ); ?>
	<br><br>
<?php

/**
 * @hooked RtclEmails::email_footer() Output the email footer
 */
do_action( 'rtcl_email_footer', $email );
