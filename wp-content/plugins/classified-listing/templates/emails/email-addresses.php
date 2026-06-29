<?php
/**
 * Email Addresses
 * This template can be overridden by copying it to yourtheme/classified-listing/emails/email-addresses.php.
 *
 * @package ClassifiedListing/Templates/Emails
 * @version 2.3.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Rtcl\Helpers\Functions;
use Rtcl\Models\Payment;

/**
 * @var         $email ;
 * @var Payment $order
 * @var string  $sent_to_admin
 */

$text_align = is_rtl() ? 'right' : 'left';
$address    = $order->get_formatted_billing_address();

?>
<table id="addresses" cellspacing="0" cellpadding="0" style="width: 100%; max-width: 600px; vertical-align: top; margin-bottom: 40px; padding:0;" border="0">
	<tr>
		<td style="text-align:<?php echo esc_attr( $text_align ); ?>; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; border:0; padding:0;"
			valign="top" width="50%">
			<h2 style="<?php echo esc_attr( Functions::email_h2_style( $email ) ); ?>"><?php esc_html_e( 'Billing address', 'classified-listing' ); ?></h2>

			<address style="<?php echo esc_attr( Functions::email_class_address_style( $email ) ) ?>">
				<?php echo wp_kses_post( $address ? $address : esc_html__( 'N/A', 'classified-listing' ) ); ?>
				<?php if ( $order->get_billing_phone() ) : ?>
					<br/><?php echo esc_html( $order->get_billing_phone() ); ?>
				<?php endif; ?>
				<?php if ( $order->get_billing_email() ) : ?>
					<br/><?php echo esc_html( $order->get_billing_email() ); ?>
				<?php endif; ?>
			</address>
		</td>
	</tr>
</table>
