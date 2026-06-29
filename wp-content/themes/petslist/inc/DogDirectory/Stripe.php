<?php
/**
 * Dog Directory - Stripe Payment Integration
 * @package Petslist Dog Directory
 */

namespace RadiusTheme\Petslist\DogDirectory;

if ( ! defined( 'ABSPATH' ) ) exit;

class Stripe {

    protected static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action( 'wp_ajax_dd_create_payment_intent', [ $this, 'create_payment_intent' ] );
        add_action( 'wp_ajax_nopriv_dd_create_payment_intent', [ $this, 'create_payment_intent_nopriv' ] );
        add_action( 'wp_ajax_dd_confirm_payment', [ $this, 'confirm_payment' ] );
        add_action( 'wp_ajax_dd_stripe_webhook', [ $this, 'handle_webhook' ] );
        add_action( 'wp_ajax_nopriv_dd_stripe_webhook', [ $this, 'handle_webhook' ] );
        add_action( 'init', [ $this, 'register_stripe_settings' ] );
    }

    public function register_stripe_settings() {
        register_setting( 'dd_settings', 'dd_stripe_publishable_key', 'sanitize_text_field' );
        register_setting( 'dd_settings', 'dd_stripe_secret_key', 'sanitize_text_field' );
        register_setting( 'dd_settings', 'dd_stripe_webhook_secret', 'sanitize_text_field' );
        register_setting( 'dd_settings', 'dd_stripe_mode', 'sanitize_text_field' ); // 'test' | 'live'
    }

    /**
     * Create a Stripe PaymentIntent for one-time charges
     */
    public function create_payment_intent() {
        check_ajax_referer( 'dd_checkout_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error(['message' => __('Please log in.', 'petslist')]);
        }

        $plan_slug = sanitize_text_field( $_POST['plan'] ?? '' );
        $plan      = Subscription::get_plan( $plan_slug );

        if ( ! $plan ) {
            wp_send_json_error(['message' => __('Invalid plan.', 'petslist')]);
        }

        $secret_key = dd_stripe_secret_key();
        if ( empty($secret_key) ) {
            wp_send_json_error(['message' => __('Payment system not configured. Please contact admin.', 'petslist')]);
        }

        $amount_cents = (int) round( $plan->price * 100 );

        $response = wp_remote_post( 'https://api.stripe.com/v1/payment_intents', [
            'headers' => [
                'Authorization' => 'Bearer ' . $secret_key,
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ],
            'body' => [
                'amount'                    => $amount_cents,
                'currency'                  => 'usd',
                'payment_method_types[]'    => 'card',
                'description'               => sprintf( 'Dog Directory — %s Plan', $plan->name ),
                'metadata[user_id]'         => get_current_user_id(),
                'metadata[plan_id]'         => $plan->id,
                'metadata[plan_slug]'       => $plan->slug,
            ],
        ] );

        if ( is_wp_error($response) ) {
            wp_send_json_error(['message' => __('Payment service unavailable.', 'petslist')]);
        }

        $body = json_decode( wp_remote_retrieve_body($response), true );

        if ( ! empty($body['error']) ) {
            wp_send_json_error(['message' => $body['error']['message'] ?? __('Stripe error.', 'petslist')]);
        }

        wp_send_json_success([
            'clientSecret' => $body['client_secret'],
            'amount'       => $plan->price,
            'plan'         => $plan->name,
        ]);
    }

    public function create_payment_intent_nopriv() {
        wp_send_json_error(['message' => __('Please log in to subscribe.', 'petslist'), 'redirect' => dd_login_url()]);
    }

    /**
     * After Stripe confirms client-side, verify and activate subscription
     */
    public function confirm_payment() {
        check_ajax_referer( 'dd_checkout_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error(['message' => __('Not logged in.', 'petslist')]);
        }

        $pi_id     = sanitize_text_field( $_POST['payment_intent_id'] ?? '' );
        $plan_slug = sanitize_text_field( $_POST['plan'] ?? '' );
        $user_id   = get_current_user_id();

        if ( empty($pi_id) ) {
            wp_send_json_error(['message' => __('Missing payment data.', 'petslist')]);
        }

        $secret_key = dd_stripe_secret_key();

        // Retrieve PaymentIntent from Stripe
        $response = wp_remote_get( "https://api.stripe.com/v1/payment_intents/{$pi_id}", [
            'headers' => ['Authorization' => 'Bearer ' . $secret_key],
        ] );

        if ( is_wp_error($response) ) {
            wp_send_json_error(['message' => __('Could not verify payment.', 'petslist')]);
        }

        $pi = json_decode( wp_remote_retrieve_body($response), true );

        if ( ($pi['status'] ?? '') !== 'succeeded' ) {
            wp_send_json_error(['message' => __('Payment not completed. Status: ' . ($pi['status'] ?? 'unknown'), 'petslist')]);
        }

        // Prevent duplicate processing
        $already_processed = get_option('dd_pi_processed_' . $pi_id);
        if ( $already_processed ) {
            wp_send_json_success(['message' => __('Already processed.', 'petslist'), 'redirect' => dd_dashboard_url()]);
        }

        $plan   = Subscription::get_plan( $plan_slug );
        $sub_id = Subscription::create_subscription( $user_id, $plan->id );

        if ( ! $sub_id ) {
            wp_send_json_error(['message' => __('Subscription activation failed.', 'petslist')]);
        }

        $amount = $pi['amount'] / 100;
        Subscription::record_payment( $user_id, $sub_id, $amount, $pi['id'], $pi['id'] );

        // Mark PI as processed
        update_option('dd_pi_processed_' . $pi_id, 1, false);

        // Send welcome email
        $this->send_subscription_email( $user_id, $plan );

        wp_send_json_success([
            'message'  => sprintf(__('🎉 Welcome! Your %s subscription is now active.', 'petslist'), $plan->name),
            'redirect' => dd_dashboard_url(),
        ]);
    }

    /**
     * Handle Stripe Webhooks (for renewals, disputes, etc.)
     */
    public function handle_webhook() {
        $payload   = file_get_contents('php://input');
        $sig       = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        $secret    = dd_stripe_webhook_secret();

        if ( empty($secret) ) {
            http_response_code(400);
            exit('Webhook secret not configured.');
        }

        // Verify signature manually (simple version)
        $event = json_decode($payload, true);

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            http_response_code(400);
            exit('Invalid payload.');
        }

        $type = $event['type'] ?? '';
        $data = $event['data']['object'] ?? [];

        switch ( $type ) {
            case 'payment_intent.succeeded':
                $this->webhook_payment_succeeded($data);
                break;
            case 'invoice.payment_failed':
                $this->webhook_payment_failed($data);
                break;
            case 'customer.subscription.deleted':
                $this->webhook_subscription_deleted($data);
                break;
        }

        http_response_code(200);
        exit('ok');
    }

    private function webhook_payment_succeeded( $pi ) {
        $user_id  = $pi['metadata']['user_id'] ?? 0;
        $plan_id  = $pi['metadata']['plan_id'] ?? 0;
        if ( $user_id && $plan_id ) {
            Subscription::create_subscription( (int)$user_id, (int)$plan_id, $pi['id'] ?? '' );
        }
    }

    private function webhook_payment_failed( $invoice ) {
        $stripe_sub_id = $invoice['subscription'] ?? '';
        if ( ! $stripe_sub_id ) return;
        global $wpdb;
        $subs = $wpdb->prefix . 'dd_subscriptions';
        $sub  = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $subs WHERE stripe_sub_id = %s", $stripe_sub_id) );
        if ( $sub ) {
            $wpdb->update($subs, ['status' => 'expired'], ['id' => $sub->id]);
            update_user_meta($sub->user_id, 'dd_subscription_status', 'expired');
        }
    }

    private function webhook_subscription_deleted( $stripe_sub ) {
        $stripe_sub_id = $stripe_sub['id'] ?? '';
        if ( ! $stripe_sub_id ) return;
        global $wpdb;
        $subs = $wpdb->prefix . 'dd_subscriptions';
        $sub  = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $subs WHERE stripe_sub_id = %s", $stripe_sub_id) );
        if ( $sub ) {
            $wpdb->update($subs, ['status' => 'cancelled'], ['id' => $sub->id]);
            update_user_meta($sub->user_id, 'dd_subscription_status', 'cancelled');
        }
    }

    private function send_subscription_email( $user_id, $plan ) {
        $user    = get_user_by('id', $user_id);
        $subject = sprintf( __('Your Dog Directory %s Subscription is Active!', 'petslist'), $plan->name );
        $message = sprintf(
            __("Hi %s,\n\nYour %s subscription to Dog Directory is now active.\n\nYou can now:\n✓ Add unlimited dog profiles\n✓ Browse the full directory\n✓ Upload photos\n\nManage your account: %s\n\nThank you!", 'petslist'),
            $user->display_name,
            $plan->name,
            dd_dashboard_url()
        );
        wp_mail( $user->user_email, $subject, $message );
    }
}
