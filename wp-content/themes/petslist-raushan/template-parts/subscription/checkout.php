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
                    <span>$<?php echo number_format( $plan->price, 2 ); ?></span>
                </div>
                <div class="dd-checkout-total__row">
                    <span><?php _e( 'Tax', 'petslist' ); ?></span>
                    <span><?php _e( 'Calculated at payment', 'petslist' ); ?></span>
                </div>
                <div class="dd-checkout-total__row dd-checkout-total__row--total">
                    <span><?php _e( 'Total Today', 'petslist' ); ?></span>
                    <span>$<?php echo number_format( $plan->price, 2 ); ?></span>
                </div>
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
            <div class="dd-notice dd-notice--warning">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <?php _e( 'Payment processing is not yet configured. Please contact the site administrator.', 'petslist' ); ?>
            </div>
            <?php endif; ?>

            <div class="dd-checkout-guarantee">
                <i class="fa-solid fa-rotate-left"></i>
                <?php _e( '7-day money-back guarantee. No questions asked.', 'petslist' ); ?>
            </div>
        </div>

    </div><!-- .dd-checkout-layout -->
</div><!-- .dd-checkout-wrap -->
