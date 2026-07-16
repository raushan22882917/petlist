<?php
/**
 * Dog Directory - PayPal Payment Integration
 * @package Petslist Dog Directory
 */

namespace RadiusTheme\Petslist\DogDirectory;

if ( ! defined( 'ABSPATH' ) ) exit;

class PayPal {

    protected static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action( 'wp_ajax_dd_paypal_confirm_payment', [ $this, 'confirm_payment' ] );
        add_action( 'init', [ $this, 'register_paypal_settings' ] );
    }

    public function register_paypal_settings() {
        register_setting( 'dd_settings', 'dd_paypal_client_id', 'sanitize_text_field' );
        register_setting( 'dd_settings', 'dd_paypal_secret', 'sanitize_text_field' );
        register_setting( 'dd_settings', 'dd_paypal_mode', 'sanitize_text_field' ); // 'sandbox' | 'live'
    }

    /**
     * Verify the PayPal payment client-side order ID on the backend and activate the subscription
     */
    public function confirm_payment() {
        check_ajax_referer( 'dd_checkout_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error(['message' => __('Not logged in.', 'petslist')]);
        }

        $order_id  = sanitize_text_field( $_POST['order_id'] ?? '' );
        $plan_slug = sanitize_text_field( $_POST['plan'] ?? '' );
        $user_id   = get_current_user_id();

        if ( empty($order_id) ) {
            wp_send_json_error(['message' => __('Missing payment transaction data.', 'petslist')]);
        }

        // Prevent duplicate processing
        $already_processed = get_option('dd_paypal_processed_' . $order_id);
        if ( $already_processed ) {
            wp_send_json_success(['message' => __('Already processed.', 'petslist'), 'redirect' => dd_dashboard_url()]);
        }

        // Let's verify the order with PayPal API to ensure it's paid and valid.
        $client_id = dd_paypal_client_id();
        $secret    = dd_paypal_secret();
        $mode      = dd_paypal_mode();

        $api_url = ($mode === 'live') ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';

        // 1. Get access token
        $token_response = wp_remote_post( "{$api_url}/v1/oauth2/token", [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode( "{$client_id}:{$secret}" ),
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ],
            'body' => 'grant_type=client_credentials',
        ] );

        if ( is_wp_error($token_response) ) {
            wp_send_json_error(['message' => __('Could not connect to PayPal API.', 'petslist')]);
        }

        $token_body = json_decode( wp_remote_retrieve_body($token_response), true );
        $access_token = $token_body['access_token'] ?? '';

        if ( ! $access_token ) {
            wp_send_json_error(['message' => __('PayPal authentication failed. Check Client ID and Secret in settings.', 'petslist')]);
        }

        // 2. Retrieve order details
        $order_response = wp_remote_get( "{$api_url}/v2/checkout/orders/{$order_id}", [
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type'  => 'application/json',
            ],
        ] );

        if ( is_wp_error($order_response) ) {
            wp_send_json_error(['message' => __('Could not verify PayPal order.', 'petslist')]);
        }

        $order = json_decode( wp_remote_retrieve_body($order_response), true );
        $status = $order['status'] ?? '';

        if ( $status !== 'COMPLETED' && $status !== 'APPROVED' ) {
            wp_send_json_error(['message' => sprintf(__('PayPal order is not completed. Status: %s', 'petslist'), $status)]);
        }

        // Activate user subscription
        $plan   = Subscription::get_plan( $plan_slug );
        $sub_id = Subscription::create_subscription( $user_id, $plan->id );

        if ( ! $sub_id ) {
            wp_send_json_error(['message' => __('Subscription activation failed.', 'petslist')]);
        }

        // Record payment in local DB
        $amount = $order['purchase_units'][0]['amount']['value'] ?? $plan->price;
        Subscription::record_payment( $user_id, $sub_id, $amount, 'PayPal', $order_id );

        // Mark order as processed
        update_option('dd_paypal_processed_' . $order_id, 1, false);

        // Send welcome email
        $this->send_subscription_email( $user_id, $plan );

        wp_send_json_success([
            'message'  => sprintf(__('🎉 Welcome! Your %s subscription is now active.', 'petslist'), $plan->name),
            'redirect' => dd_dashboard_url(),
        ]);
    }

    private function send_subscription_email( $user_id, $plan ) {
        $user = get_userdata( $user_id );
        if ( ! $user ) return;

        $subject = sprintf( __('Welcome to Dog Directory - %s Active', 'petslist'), $plan->name );
        $message = sprintf( 
            __("Hi %s,\n\nThank you! Your payment was successful, and your %s subscription is now active. You have full access to view breeder contact details, dog pedigrees, health reports, and create unlimited listings.\n\nManage your profile here: %s\n\nBest regards,\nDog Directory Team", 'petslist'), 
            $user->display_name, 
            $plan->name,
            dd_dashboard_url()
        );

        $headers = [];
        $from_name = get_option('dd_email_from_name');
        $from_email = get_option('dd_email_from_email');
        if ( $from_name && $from_email ) {
            $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
        }

        wp_mail( $user->user_email, $subject, $message, $headers );
    }
}
