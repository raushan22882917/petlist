<?php
/**
 * @author        RadiusTheme
 * @package       classified-listing/templates
 * @version       1.0.0
 *
 * @var array $pricing_options
 */


use Rtcl\Helpers\Functions;
use Rtcl\Resources\Options;

$currency        = Functions::get_order_currency();
$currency_symbol = Functions::get_currency_symbol( $currency );
?>
<div id="rtcl-checkout-form-data">
	<div class="rtcl-checkout-pricing-wrapper rtcl-row rtcl-form-group">
		<?php
		if ( ! empty( $pricing_options ) ):
			foreach ( $pricing_options as $pricing ) :
				$price = get_post_meta( $pricing->ID, 'price', true );
				$visible = get_post_meta( $pricing->ID, 'visible', true );
				$featured = get_post_meta( $pricing->ID, 'featured', true );
				$top = get_post_meta( $pricing->ID, '_top', true );
				$bump_up = get_post_meta( $pricing->ID, '_bump_up', true );
				$description = get_post_meta( $pricing->ID, 'description', true );
				?>
				<div class="rtcl-col-md-4 rtcl-col-12">
					<div class="rtcl-checkout-pricing">
						<h3 class="rtcl-pricing-title"><?php echo esc_html( $pricing->post_title ); ?></h3>
						<div class="rtcl-checkout-pricing-inner">
							<?php if ( $description ): ?>
								<p class="rtcl-pricing-description"><?php echo wp_kses_post( $description ); ?></p>
							<?php endif; ?>
							<span class="rtcl-pricing-price"><?php echo Functions::get_payment_formatted_price_html( $price ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped  ?> </span>
							<div class="rtcl-pricing-features">
								<?php
								$promotions = Options::get_listing_promotions();
								if ( ! empty( $promotions ) ) {
									?>
									<div class="rtcl-membership-promotions">
										<?php

										foreach ( $promotions as $promo_id => $promotion ) {
											if ( get_post_meta( $pricing->ID, $promo_id, true ) ) {
												?>
												<div class="promotion-item">
													<span>
														<?php echo esc_html( $promotion ); ?>:
														<?php
														printf( '<span>%s</span>',
															esc_html( sprintf( /* translators: Days */ _n( '%s Day', '%s Days', absint( $visible ),
																'classified-listing' ),
																esc_html( number_format_i18n( absint( $visible ) ) ) ) ) );
														?>
													</span>
												</div>
												<?php
											}
										}
										?>
									</div>
									<?php
								}
								?>
							</div>
							<div class="rtcl-pricing-btn">
								<?php
								printf( '<input type="radio" name="%s" id="pricing_id_%s" value="%s" class="rtcl-checkout-pricing" required data-price="%s"/><label for="pricing_id_%s">%s</label>',
									'pricing_id', esc_attr( $pricing->ID ), esc_attr( $pricing->ID ), esc_attr( $price ), esc_attr( $pricing->ID ),
									esc_html__( 'Select This Package', 'classified-listing' ) );
								?>
							</div>
						</div>
					</div>
				</div>
			<?php endforeach;
		else: ?>
			<div>
				<span><?php esc_html_e( "No promotion plan found.", "classified-listing" ); ?></span>
			</div>
		<?php endif; ?>
	</div>
</div>