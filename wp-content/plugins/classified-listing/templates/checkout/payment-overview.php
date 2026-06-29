<?php
/**
 * @author        RadiusTheme
 * @package       classified-listing/templates
 * @version       1.0.0
 *
 */

use Rtcl\Helpers\Functions;

?>

<div id="rtcl-payment-overview">
	<h3 class="rtcl-checkout-heading"><?php esc_html_e( 'Cart totals', 'classified-listing' ); ?></h3>
	<table class="rtcl-checkout-overview-table rtcl-table">
		<tbody>
		<tr class="cart-subtotal">
			<th><?php esc_html_e( 'Subtotal', 'classified-listing' ); ?></th>
			<td data-title="<?php esc_html_e( 'Subtotal', 'classified-listing' ); ?>">
				<span class="price-amount">
					<span class="checkout-price-currency-symbol"><?php echo Functions::get_order_currency_symbol(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="checkout-price">0.00</span>
				</span>
			</td>
		</tr>
		<tr class="tax-rate">
			<th><?php esc_html_e( 'Tax', 'classified-listing' ); ?></th>
			<td data-title="<?php esc_html_e( 'Tax', 'classified-listing' ); ?>">
				<span class="price-amount">
					<span class="checkout-price-currency-symbol"><?php echo Functions::get_order_currency_symbol(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="checkout-price">0.00</span>
				</span>
			</td>
		</tr>
		<tr class="order-total">
			<th><?php esc_html_e( 'Total', 'classified-listing' ); ?></th>
			<td data-title="<?php esc_html_e( 'Total', 'classified-listing' ); ?>">
				<strong>
					<span class="checkout-price-currency-symbol"><?php echo Functions::get_order_currency_symbol(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="checkout-price">0.00</span>
				</strong>
			</td>
		</tr>
		</tbody>
	</table>
</div>
