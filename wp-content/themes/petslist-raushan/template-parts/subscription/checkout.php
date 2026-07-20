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

$plan_slug = sanitize_text_field( $_GET['plan'] ?? 'studs' );
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
$paypal_client_id = dd_paypal_client_id();
?>

<div class="dd-checkout-wrap">
    <div class="dd-checkout-layout">

        <!-- LEFT: Order Summary -->
        <div class="dd-checkout-summary">
            <h2 class="dd-checkout-summary__title"><?php _e( 'Order Summary', 'petslist' ); ?></h2>

            <div class="dd-checkout-plan-box">
                <div class="dd-checkout-plan-box__header">
                    <span class="dd-checkout-plan-box__icon">
                        <?php echo $plan->slug === 'studs' ? '📅' : ( $plan->slug === 'kennels' ? '⭐' : '♾️' ); ?>
                    </span>
                    <div>
                        <div class="dd-checkout-plan-box__name"><?php echo esc_html( $plan->name ); ?> <?php _e( 'Plan', 'petslist' ); ?></div>
                        <div class="dd-checkout-plan-box__period"><?php echo esc_html( $plan->duration ); ?> <?php _e( 'days access', 'petslist' ); ?></div>
                    </div>
                </div>
                <div class="dd-checkout-plan-box__price">
                    <span>$<?php echo number_format( $plan->price, 2 ); ?></span>
                    <small><?php echo $period; ?></small>
                </div>
            </div>

            <ul class="dd-checkout-features">
                <?php foreach ( $features as $f ) : ?>
                <li><i class="icon-pl-tick-mark-fill-circle"></i> <?php echo esc_html( $f ); ?></li>
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
                <i class="fa-solid fa-shield-halved"></i>
                <?php _e( 'Secured by PayPal. Your payment details are processed securely.', 'petslist' ); ?>
            </div>

            <a href="<?php echo esc_url( dd_pricing_url() ); ?>" class="dd-checkout-change-plan">
                <i class="fa-solid fa-arrow-left"></i> <?php _e( 'Change Plan', 'petslist' ); ?>
            </a>
        </div>

        <!-- RIGHT: Payment Form -->
        <div class="dd-checkout-payment">
            <h2 class="dd-checkout-payment__title"><?php _e( 'Payment Details', 'petslist' ); ?></h2>

            <!-- Account Info -->
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

            <div id="dd-checkout-message" class="dd-auth-message" style="display:none"></div>

            <?php if ( ! empty( $paypal_client_id ) ) : ?>
            <!-- PayPal Button Form -->
            <form id="dd-payment-form" class="dd-payment-form">
                <input type="hidden" name="plan" value="<?php echo esc_attr( $plan_slug ); ?>">
                <input type="hidden" name="amount" value="<?php echo esc_attr( $plan->price ); ?>">

                <div id="paypal-button-container" style="margin-top:20px; min-height: 150px;"></div>

                <div class="dd-payment-badges" style="margin-top:25px;">
                    <span class="dd-payment-badge">🔒 SSL</span>
                    <span class="dd-payment-badge">💳 PayPal</span>
                    <span class="dd-payment-badge">🛡️ Secure Checkout</span>
                </div>
            </form>

            <script src="https://www.paypal.com/sdk/js?client-id=<?php echo esc_attr( $paypal_client_id ); ?>&currency=USD"></script>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof paypal === 'undefined') return;

                paypal.Buttons({
                    style: {
                        layout: 'vertical',
                        color:  'gold',
                        shape:  'rect',
                        label:  'paypal'
                    },
                    createOrder: function(data, actions) {
                        return actions.order.create({
                            purchase_units: [{
                                amount: {
                                    value: '<?php echo number_format($plan->price, 2, '.', ''); ?>'
                                },
                                description: '<?php echo esc_js($plan->name); ?> Plan - Dog Directory'
                            }]
                        });
                    },
                    onApprove: function(data, actions) {
                        var msgDiv = document.getElementById('dd-checkout-message');
                        msgDiv.style.display = 'block';
                        msgDiv.className = 'dd-auth-message dd-auth-message--info';
                        msgDiv.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing payment, please wait...';

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
                                        msgDiv.className = 'dd-auth-message dd-auth-message--success';
                                        msgDiv.innerHTML = response.data.message;
                                        setTimeout(function() {
                                            window.location.href = response.data.redirect;
                                        }, 1500);
                                    } else {
                                        msgDiv.className = 'dd-auth-message dd-auth-message--error';
                                        msgDiv.innerHTML = response.data.message;
                                    }
                                },
                                error: function() {
                                    msgDiv.className = 'dd-auth-message dd-auth-message--error';
                                    msgDiv.innerHTML = 'A server error occurred. Please contact support.';
                                }
                            });
                        });
                    },
                    onError: function(err) {
                        var msgDiv = document.getElementById('dd-checkout-message');
                        msgDiv.style.display = 'block';
                        msgDiv.className = 'dd-auth-message dd-auth-message--error';
                        msgDiv.innerHTML = 'An error occurred with PayPal. Please try again.';
                    }
                }).render('#paypal-button-container');
            });
            </script>

            <?php else : ?>
            <!-- PayPal not configured -->
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
