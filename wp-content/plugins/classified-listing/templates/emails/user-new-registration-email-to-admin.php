<?php
/**
 * New Registration email to admin
 * This template can be overridden by copying it to yourtheme/classified-listing/emails/user-new-registration-email-to-admin.php
 *
 * @author        RadiusTheme
 * @package       ClassifiedListing/Templates/Emails
 * @version       2.3.0
 *
 * @var RtclEmail $email
 * @var WP_User $user
 */

use Rtcl\Helpers\Functions;
use Rtcl\Models\RtclEmail;

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @hooked RtclEmails::email_header() Output the email header
 */
do_action( 'rtcl_email_header', $email ); ?>
<?php /* translators: %s: Customer billing full name */ ?>
	<p style="margin: 0 0 16px;"><?php esc_html_e( "Hi Admin", "classified-listing" ); ?></p>
	<p style="margin: 0 0 16px;"><?php /* translators:  user registration */
		printf( esc_html__( '%1$s ( %2$s ) has registered to your site %3$s,.', 'classified-listing' ), esc_html( $user->user_login ), esc_html( $user->user_email ), esc_html( Functions::get_blogname() ) ) ?></p>
	<p style="margin: 0 0 16px;"><?php esc_html_e( 'Thanks for reading.', 'classified-listing' ); ?></p>
<?php
/**
 * @hooked RtclEmails::email_footer() Output the email footer
 */
do_action( 'rtcl_email_footer', $email );

