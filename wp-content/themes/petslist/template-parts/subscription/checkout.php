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

$user       = wp_get_current_user();
$active_sub = Subscription::get_user_subscription();
$period     = $plan->duration <= 31 ? __( '/month', 'petslist' ) : ( $plan->duration <= 366 ? __( '/year', 'petslist' ) : __( ' once', 'petslist' ) );
$features   = json_decode( $plan->features, true ) ?: [];
$pub_key    = dd_stripe_publishable_key();
?>

<div class="dd-checkout-wrap">
    <div class="dd-checkout-layout">

        <!-- LEFT: Order Summary -->
        <div class="dd-checkout-summary">
            <h2 class="dd-checkout-summary__title"><?php _e( 'Order Summary', 'petslist' ); ?></h2>

            <div class="dd-checkout-plan-box">
                <div class="dd-checkout-plan-box__header">
                    <span class="dd-checkout-plan-box__icon">
                        <?php echo $plan->slug === 'monthly' ? '📅' : ( $plan->slug === 'yearly' ? '⭐' : '♾️' ); ?>
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
                <?php _e( 'Secured by Stripe. Your card details are never stored on our servers.', 'petslist' ); ?>
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

            <?php if ( ! empty( $pub_key ) ) : ?>
            <!-- Stripe Card Form -->
            <form id="dd-payment-form" class="dd-payment-form">
                <input type="hidden" name="plan" value="<?php echo esc_attr( $plan_slug ); ?>">
                <input type="hidden" name="amount" value="<?php echo esc_attr( $plan->price ); ?>">

                <div class="dd-form-group">
                    <label><?php _e( 'Card Number', 'petslist' ); ?></label>
                    <div id="dd-card-number" class="dd-stripe-element"></div>
                </div>
                <div class="dd-payment-form__row">
                    <div class="dd-form-group">
                        <label><?php _e( 'Expiry Date', 'petslist' ); ?></label>
                        <div id="dd-card-expiry" class="dd-stripe-element"></div>
                    </div>
                    <div class="dd-form-group">
                        <label><?php _e( 'CVC', 'petslist' ); ?></label>
                        <div id="dd-card-cvc" class="dd-stripe-element"></div>
                    </div>
                </div>

                <div id="dd-card-errors" class="dd-stripe-errors" role="alert"></div>

                <button type="submit" id="dd-pay-btn" class="dd-btn dd-btn--primary dd-btn--full dd-btn--lg">
                    <i class="fa-solid fa-lock"></i>
                    <span class="dd-btn__text">
                        <?php printf( __( 'Pay $%s Securely', 'petslist' ), number_format( $plan->price, 2 ) ); ?>
                    </span>
                    <span class="dd-btn__loader" style="display:none">
                        <i class="fa-solid fa-spinner fa-spin"></i> <?php _e( 'Processing...', 'petslist' ); ?>
                    </span>
                </button>

                <div class="dd-payment-badges">
                    <span class="dd-payment-badge">🔒 SSL</span>
                    <span class="dd-payment-badge">💳 Stripe</span>
                    <span class="dd-payment-badge">🛡️ 3D Secure</span>
                </div>
            </form>

            <?php else : ?>
            <!-- Stripe not configured -->
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
