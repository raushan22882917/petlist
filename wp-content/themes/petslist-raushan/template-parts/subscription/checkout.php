<?php
/**
 * Checkout Template Part
 * @package Petslist Dog Directory
 */

use RadiusTheme\Petslist\DogDirectory\Subscription;

if ( ! defined( 'ABSPATH' ) ) exit;

// Must be logged in
if ( ! is_user_logged_in() ) {
    echo '<div class="dd-notice dd-notice--warning">';
    printf( __( 'Please <a href="%s">log in</a> or <a href="%s">register</a> to complete your subscription.', 'petslist' ),
        esc_url( dd_login_url() ), esc_url( dd_register_url() ) );
    echo '</div>';
    return;
}

$plan_slug = sanitize_text_field( $_GET['plan'] ?? 'monthly' );
$plan      = Subscription::get_plan( $plan_slug );

if ( ! $plan ) {
    echo '<div class="dd-notice dd-notice--error">' . __( 'Invalid plan selected.', 'petslist' ) . '</div>';
    return;
}

$active_sub = Subscription::get_user_subscription();

if ( Subscription::has_reached_sales_limit() && ( ! $active_sub || $active_sub->plan_slug !== $plan->slug ) ) {
    echo '<div class="dd-notice dd-notice--warning">' . __( 'All monthly packages are currently sold out. Please check back later.', 'petslist' ) . '</div>';
    return;
}

$user       = wp_get_current_user();
$period     = __( '/month', 'petslist' );
$features   = json_decode( $plan->features, true ) ?: [];
$stripe_pub_key   = dd_stripe_publishable_key();
$paypal_client_id = dd_paypal_client_id();
?>

<div class="dd-checkout-wrap">
    <div class="dd-checkout-layout">

        <!-- Order Summary -->
        <div class="dd-checkout-summary">
            <h2 class="dd-checkout-summary__title"><?php _e( 'Order Summary', 'petslist' ); ?></h2>

            <div class="dd-checkout-plan-box">
                <div class="dd-checkout-plan-box__header">
                    <span class="dd-checkout-plan-box__icon">🐾</span>
                    <div>
                        <div class="dd-checkout-plan-box__name"><?php echo esc_html( $plan->name ); ?> <?php _e( 'Plan', 'petslist' ); ?></div>
                        <div class="dd-checkout-plan-box__period"><?php echo esc_html( $plan->duration ); ?> <?php _e( 'days access', 'petslist' ); ?></div>
                    </div>
                    <div class="dd-checkout-plan-box__price">
                        <span>$<?php echo number_format( $plan->price, 2 ); ?></span>
                        <small><?php echo esc_html( $period ); ?></small>
                    </div>
                </div>
            </div>

            <ul class="dd-checkout-features">
                <?php foreach ( $features as $feat ) : ?>
                <li><i class="fa-solid fa-check"></i> <?php echo esc_html( $feat ); ?></li>
                <?php endforeach; ?>
            </ul>

            <div class="dd-checkout-total">
                <div class="dd-checkout-total__row">
                    <span><?php _e( 'Subtotal', 'petslist' ); ?></span>
                    <span id="dd-checkout-subtotal">$<?php echo number_format( $plan->price, 2 ); ?></span>
                </div>
                <div class="dd-checkout-total__row" id="dd-discount-row" style="display:none; color:#16a34a; font-weight:600;">
                    <span><?php _e( 'Promo Discount', 'petslist' ); ?> <small id="dd-discount-code-badge" style="background:#dcfce7; color:#15803d; padding:2px 6px; border-radius:4px; font-size:11px; font-weight:700; margin-left:4px;"></small></span>
                    <span id="dd-discount-val">-$0.00</span>
                </div>
                <div class="dd-checkout-total__row">
                    <span><?php _e( 'Tax', 'petslist' ); ?></span>
                    <span><?php _e( 'Calculated at payment', 'petslist' ); ?></span>
                </div>
                <div class="dd-checkout-total__row dd-checkout-total__row--total">
                    <span><?php _e( 'Total Today', 'petslist' ); ?></span>
                    <span id="dd-checkout-total-val">$<?php echo number_format( $plan->price, 2 ); ?></span>
                </div>
            </div>

            <!-- Promo / Coupon Code Section -->
            <div class="dd-checkout-coupon-box" style="margin: 20px 0; padding: 16px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 12px;">
                <label for="dd-coupon-input" style="display:block; font-size: 13px; font-weight: 700; color: #070c3e; margin-bottom: 8px;">
                    <i class="fa-solid fa-tag" style="color: var(--dd-primary, #bd8c42); margin-right: 5px;"></i>
                    <?php _e( 'Have a promo or coupon code?', 'petslist' ); ?>
                </label>
                <div style="display: flex; gap: 8px;">
                    <input type="text" id="dd-coupon-input" placeholder="<?php esc_attr_e('e.g. FREEMONTH', 'petslist'); ?>" style="flex:1; border: 1px solid #cbd5e1; border-radius: 8px; padding: 9px 12px; font-size: 14px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;" autocomplete="off" />
                    <button type="button" id="dd-apply-coupon-btn" class="dd-btn dd-btn--primary" style="padding: 9px 16px; font-size: 13px; font-weight: 700; border-radius: 8px; white-space: nowrap;">
                        <span><?php _e( 'Apply', 'petslist' ); ?></span>
                        <span class="dd-btn__loader" style="display:none;"><i class="fa-solid fa-spinner fa-spin"></i></span>
                    </button>
                </div>
                <div id="dd-coupon-msg" style="display:none; font-size: 12px; margin-top: 8px; font-weight: 600;"></div>
            </div>

            <div class="dd-checkout-security">
                <i class="fa-solid fa-lock"></i>
                <?php _e( 'Secured connection. Your payment details are processed securely.', 'petslist' ); ?>
            </div>

            <a href="<?php echo esc_url( dd_pricing_url() ); ?>" class="dd-checkout-change-plan">
                <i class="fa-solid fa-arrow-left"></i> <?php _e( 'Change plan', 'petslist' ); ?>
            </a>
        </div>

        <!-- Payment Details -->
        <div class="dd-checkout-payment">
            <h2 class="dd-checkout-payment__title"><?php _e( 'Payment Details', 'petslist' ); ?></h2>

            <div class="dd-checkout-account">
                <i class="icon-pl-account"></i>
                <div>
                    <strong><?php echo esc_html( $user->display_name ); ?></strong>
                    <small><?php echo esc_html( $user->user_email ); ?></small>
                </div>
                <a href="<?php echo esc_url( wp_logout_url( dd_login_url() ) ); ?>" class="dd-checkout-account__logout">
                    <?php _e( 'Not you?', 'petslist' ); ?>
                </a>
            </div>

            <div id="dd-checkout-message" class="dd-auth-message" style="display:none; margin-bottom:20px;"></div>

            <!-- 100% Free Promo Activation Card (shown when promo is active) -->
            <div id="dd-free-activation-card" style="display:none; margin-bottom: 24px;">
                <div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 2px solid #86efac; border-radius: 14px; padding: 22px; text-align: center;">
                    <div style="font-size: 32px; margin-bottom: 6px;">🎁</div>
                    <h3 style="color: #166534; font-size: 20px; font-weight: 800; margin: 0 0 6px 0;"><?php _e('100% Free Subscription Access', 'petslist'); ?></h3>
                    <p style="color: #15803d; font-size: 14px; margin: 0 0 16px 0; line-height: 1.5;">
                        <?php _e('Your promo code has been validated. Enjoy free subscription access to all Dog Directory features without providing any credit card.', 'petslist'); ?>
                    </p>
                    <button type="button" id="dd-claim-free-btn" class="dd-btn dd-btn--primary dd-btn--full dd-btn--lg" style="width: 100%; height: 52px; font-size: 16px; font-weight: 800; background: #16a34a; border-color: #16a34a; box-shadow: 0 4px 14px rgba(22, 163, 74, 0.35); border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <span><i class="fa-solid fa-circle-check"></i> <?php _e('Activate Free Subscription (Instant Access)', 'petslist'); ?></span>
                        <span class="dd-btn__loader" style="display:none;"><i class="fa-solid fa-spinner fa-spin"></i> <?php _e('Activating your access...', 'petslist'); ?></span>
                    </button>
                    <div style="margin-top: 12px; font-size: 12px;">
                        <a href="#" id="dd-remove-coupon-link" style="color: #dc2626; text-decoration: underline; font-weight: 600;"><?php _e('Remove promo code', 'petslist'); ?></a>
                    </div>
                </div>
            </div>

            <!-- Standard Paid Gateways Container -->
            <div id="dd-paid-gateways-wrap">
                <?php if ( ! empty( $stripe_pub_key ) ) : ?>
                <!-- Stripe Hosted Checkout Button -->
                <form id="dd-stripe-hosted-form" style="margin-bottom:20px;">
                    <input type="hidden" name="plan" value="<?php echo esc_attr( $plan_slug ); ?>">
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('dd_checkout_nonce'); ?>">

                    <button type="submit" id="dd-stripe-checkout-btn" class="dd-btn dd-btn--primary dd-btn--full dd-btn--lg" style="width:100%; height:52px; border-radius:8px; font-weight:700; font-size:17px; cursor:pointer; background:#635bff; color:#fff; border:none; display:flex; align-items:center; justify-content:center; gap:10px;">
                        <span>💳 <?php printf(__('Pay $%s with Stripe', 'petslist'), number_format($plan->price, 2)); ?></span>
                        <span class="dd-btn__loader" style="display:none;"><i class="fa-solid fa-spinner fa-spin"></i> <?php _e('Redirecting to Stripe...', 'petslist'); ?></span>
                    </button>

                    <div class="dd-payment-badges" style="margin-top:16px; display:flex; justify-content:center; gap:15px; font-size:12px; color:#64748b;">
                        <span class="dd-payment-badge">🔒 256-bit SSL</span>
                        <span class="dd-payment-badge">💳 Credit / Debit Card & Apple Pay</span>
                        <span class="dd-payment-badge">🛡️ Official Stripe Checkout</span>
                    </div>
                </form>

                <?php if ( ! empty( $paypal_client_id ) ) : ?>
                <div style="text-align:center; margin:25px 0 20px; position:relative;">
                    <hr style="border:0; border-top:1px solid #e2e8f0;">
                    <span style="position:absolute; top:-10px; left:50%; transform:translateX(-50%); background:#fff; padding:0 12px; font-size:12px; color:#94a3b8; font-weight:600; text-transform:uppercase;"><?php _e('Or pay with PayPal', 'petslist'); ?></span>
                </div>

                <div id="paypal-button-container" style="min-height: 150px;"></div>
                <script src="https://www.paypal.com/sdk/js?client-id=<?php echo esc_attr( $paypal_client_id ); ?>&currency=USD"></script>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof paypal === 'undefined') return;
                    paypal.Buttons({
                        style: { layout: 'vertical', color: 'gold', shape: 'rect', label: 'paypal' },
                        createOrder: function(data, actions) {
                            return actions.order.create({
                                purchase_units: [{
                                    amount: { value: '<?php echo number_format($plan->price, 2, '.', ''); ?>' },
                                    description: '<?php echo esc_js($plan->name); ?> Plan - Dog Directory'
                                }]
                            });
                        },
                        onApprove: function(data, actions) {
                            var msgDiv = document.getElementById('dd-checkout-message');
                            msgDiv.style.display = 'block';
                            msgDiv.className = 'dd-notice dd-notice--info';
                            msgDiv.innerHTML = '<span>ℹ️</span> <span>Processing PayPal payment, please wait...</span>';

                            return actions.order.capture().then(function(details) {
                                jQuery.ajax({
                                    url: '<?php echo esc_url(admin_url("admin-ajax.php")); ?>',
                                    type: 'POST',
                                    data: {
                                        action: 'dd_paypal_confirm_payment',
                                        order_id: details.id,
                                        plan: '<?php echo esc_js($plan_slug); ?>',
                                        nonce: '<?php echo wp_create_nonce("dd_checkout_nonce"); ?>'
                                    },
                                    success: function(response) {
                                        if (response.success) {
                                            msgDiv.className = 'dd-notice dd-notice--success';
                                            msgDiv.innerHTML = '<span>✅</span> <span>' + response.data.message + '</span>';
                                            setTimeout(function() {
                                                window.location.href = response.data.redirect;
                                            }, 1500);
                                        } else {
                                            msgDiv.className = 'dd-notice dd-notice--error';
                                            msgDiv.innerHTML = '<span>⚠️</span> <span>' + response.data.message + '</span>';
                                        }
                                    },
                                    error: function() {
                                        msgDiv.className = 'dd-notice dd-notice--error';
                                        msgDiv.innerHTML = '<span>⚠️</span> <span>A server error occurred. Please contact support.</span>';
                                    }
                                });
                            });
                        },
                        onError: function(err) {
                            var msgDiv = document.getElementById('dd-checkout-message');
                            msgDiv.style.display = 'block';
                            msgDiv.className = 'dd-notice dd-notice--error';
                            msgDiv.innerHTML = '<span>⚠️</span> <span>An error occurred with PayPal. Please try again.</span>';
                        }
                    }).render('#paypal-button-container');
                });
                </script>
                <?php endif; ?>

                <?php elseif ( ! empty( $paypal_client_id ) ) : ?>
                <!-- PayPal Only Form -->
                <form id="dd-payment-form" class="dd-payment-form">
                    <input type="hidden" name="plan" value="<?php echo esc_attr( $plan_slug ); ?>">
                    <input type="hidden" name="amount" value="<?php echo esc_attr( $plan->price ); ?>">

                    <div id="paypal-button-container" style="margin-top:20px; min-height: 150px;"></div>
                </form>
                <script src="https://www.paypal.com/sdk/js?client-id=<?php echo esc_attr( $paypal_client_id ); ?>&currency=USD"></script>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof paypal === 'undefined') return;
                    paypal.Buttons({
                        style: { layout: 'vertical', color: 'gold', shape: 'rect', label: 'paypal' },
                        createOrder: function(data, actions) {
                            return actions.order.create({
                                purchase_units: [{
                                    amount: { value: '<?php echo number_format($plan->price, 2, '.', ''); ?>' },
                                    description: '<?php echo esc_js($plan->name); ?> Plan - Dog Directory'
                                }]
                            });
                        },
                        onApprove: function(data, actions) {
                            var msgDiv = document.getElementById('dd-checkout-message');
                            msgDiv.style.display = 'block';
                            msgDiv.className = 'dd-notice dd-notice--info';
                            msgDiv.innerHTML = '<span>ℹ️</span> <span>Processing PayPal payment, please wait...</span>';

                            return actions.order.capture().then(function(details) {
                                jQuery.ajax({
                                    url: '<?php echo esc_url(admin_url("admin-ajax.php")); ?>',
                                    type: 'POST',
                                    data: {
                                        action: 'dd_paypal_confirm_payment',
                                        order_id: details.id,
                                        plan: '<?php echo esc_js($plan_slug); ?>',
                                        nonce: '<?php echo wp_create_nonce("dd_checkout_nonce"); ?>'
                                    },
                                    success: function(response) {
                                        if (response.success) {
                                            msgDiv.className = 'dd-notice dd-notice--success';
                                            msgDiv.innerHTML = '<span>✅</span> <span>' + response.data.message + '</span>';
                                            setTimeout(function() {
                                                window.location.href = response.data.redirect;
                                            }, 1500);
                                        } else {
                                            msgDiv.className = 'dd-notice dd-notice--error';
                                            msgDiv.innerHTML = '<span>⚠️</span> <span>' + response.data.message + '</span>';
                                        }
                                    },
                                    error: function() {
                                        msgDiv.className = 'dd-notice dd-notice--error';
                                        msgDiv.innerHTML = '<span>⚠️</span> <span>A server error occurred. Please contact support.</span>';
                                    }
                                });
                            });
                        },
                        onError: function(err) {
                            var msgDiv = document.getElementById('dd-checkout-message');
                            msgDiv.style.display = 'block';
                            msgDiv.className = 'dd-notice dd-notice--error';
                            msgDiv.innerHTML = '<span>⚠️</span> <span>An error occurred with PayPal. Please try again.</span>';
                        }
                    }).render('#paypal-button-container');
                });
                </script>

                <?php else : ?>
                <!-- Neither configured -->
                <div class="dd-notice dd-notice--warning" style="background:#fffbeb; border:1px solid #fde68a; color:#92400e; padding:16px; border-radius:10px;">
                    <i class="fa-solid fa-triangle-exclamation" style="margin-right:6px;"></i>
                    <?php _e( 'Online credit card payment gateway is currently in maintenance. If you have a promo code (such as <strong>FREEMONTH</strong>), apply it on the left to activate your free access immediately!', 'petslist' ); ?>
                </div>
                <?php endif; ?>
            </div><!-- /#dd-paid-gateways-wrap -->

            <div class="dd-checkout-guarantee">
                <i class="fa-solid fa-rotate-left"></i>
                <?php _e( '7-day money-back guarantee. No questions asked.', 'petslist' ); ?>
            </div>
        </div>

    </div><!-- .dd-checkout-layout -->
</div><!-- .dd-checkout-wrap -->
