<?php

/**
 *
 * @author        RadiusTheme
 * @package       classified-listing/templates
 * @version       2.0.6
 *
 * @var Payment $payment
 */

use Rtcl\Models\Payment;

?>
<div class="billing-info">
	<h2><?php esc_html_e( 'Billing address', 'classified-listing' ); ?></h2>
	<address>
		<?php echo wp_kses_post( $payment->get_formatted_billing_address( esc_html__( 'N/A', 'classified-listing' ) ) ); ?>

		<?php if ( $payment->get_billing_phone() ) : ?>
			<p class="rtcl-customer-details--phone"><?php echo esc_html( $payment->get_billing_phone() ); ?></p>
		<?php endif; ?>

		<?php if ( $payment->get_billing_email() ) : ?>
			<p class="rtcl-customer-details--email"><?php echo esc_html( $payment->get_billing_email() ); ?></p>
		<?php endif; ?>

		<?php do_action( 'rtcl_order_details_after_customer_address', 'billing', $payment ); ?>
	</address>
</div>
