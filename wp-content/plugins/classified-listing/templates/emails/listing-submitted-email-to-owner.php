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

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @hooked RtclEmails::email_header() Output the email header
 */
do_action( 'rtcl_email_header', $email ); ?>
	<p style="margin: 0 0 16px;"><?php printf( /* translators: Owner name */ esc_html__( 'Hi %s,', 'classified-listing' ), esc_html( $listing->get_owner_name() ) ); ?></p>
	<p style="margin: 0 0 16px;"><?php
		/* phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped */
		printf(
		/* translators: Listing title */
			__( 'Your listing %s has been received and it\'s pending review. This review process could take up to 48 hours.', 'classified-listing' ),
			sprintf( '<strong>%s</strong>', esc_html( $listing->get_the_title() ) )
		) ?></p>
<?php

/**
 * @hooked RtclEmails::email_footer() Output the email footer
 */
do_action( 'rtcl_email_footer', $email );
