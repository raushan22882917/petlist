<?php
/**
 * User Import email to user
 * This template can be overridden by copying it to yourtheme/classified-listing/emails/user-import-email-to-user.php
 *
 * @author        RadiusTheme
 * @package       ClassifiedListing/Templates/Emails
 * @version       2.3.0
 *
 * @var RtclEmail $email
 * @var WP_User   $user
 * @var array     $data
 */

use Rtcl\Helpers\Functions;
use Rtcl\Models\RtclEmail;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @hooked RtclEmails::email_header() Output the email header
 */
do_action( 'rtcl_email_header', $email ); ?>
<?php /* translators: %s: Customer billing full name */ ?>
	<p style="margin: 0 0 16px;"><?php /* translators:  username */
		printf( esc_html__( "Hi %s", "classified-listing" ), esc_html( $user->user_login ) ); ?></p>
	<p style="margin: 0 0 16px;"><?php /* translators:  site url */
		printf( esc_html__( "Your account is migrated to site %s", 'classified-listing' ), esc_html( Functions::get_blogname() ) ) ?></p>
	<p style="margin: 0 0 16px;"><?php /* translators:  username */
		printf( esc_html__( "Username: %s", 'classified-listing' ), esc_html( $user->user_login ) ) ?></p>
	<p style="margin: 0 0 16px;"><?php /* translators:  password */
		printf( esc_html__( "Password: %s", 'classified-listing' ), esc_html( $data['user_pass'] ) ) ?></p>

	<br>
	<p style="margin: 0 0 16px;"><?php esc_html_e( 'Thanks for reading.', 'classified-listing' ); ?></p>
<?php
/**
 * @hooked RtclEmails::email_footer() Output the email footer
 */
do_action( 'rtcl_email_footer', $email );

