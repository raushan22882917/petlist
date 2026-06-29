<?php
/**
 * Dog Directory - Subscription System
 * @package Petslist Dog Directory
 */

namespace RadiusTheme\Petslist\DogDirectory;

if ( ! defined( 'ABSPATH' ) ) exit;

class Subscription {

    protected static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action( 'init', [ $this, 'create_subscription_tables' ] );
        add_action( 'wp_ajax_dd_subscribe', [ $this, 'handle_subscription' ] );
        add_action( 'wp_ajax_nopriv_dd_subscribe', [ $this, 'handle_subscription_guest' ] );
        add_action( 'wp_ajax_dd_cancel_subscription', [ $this, 'cancel_subscription' ] );
        add_action( 'dd_check_expired_subscriptions', [ $this, 'check_expired_subscriptions' ] );
        add_filter( 'user_row_actions', [ $this, 'user_subscription_action' ], 10, 2 );

        if ( ! wp_next_scheduled( 'dd_check_expired_subscriptions' ) ) {
            wp_schedule_event( time(), 'daily', 'dd_check_expired_subscriptions' );
        }
    }

    /**
     * Create DB tables for subscriptions & payments
     */
    public function create_subscription_tables() {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();

        $subs_table = $wpdb->prefix . 'dd_subscriptions';
        $pay_table  = $wpdb->prefix . 'dd_payments';
        $plan_table = $wpdb->prefix . 'dd_plans';

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta( "CREATE TABLE IF NOT EXISTS $plan_table (
            id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name        VARCHAR(100) NOT NULL,
            slug        VARCHAR(100) NOT NULL,
            price       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            duration    INT(11) NOT NULL DEFAULT 30,
            features    TEXT,
            is_active   TINYINT(1) NOT NULL DEFAULT 1,
            created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug)
        ) $charset;" );

        dbDelta( "CREATE TABLE IF NOT EXISTS $subs_table (
            id              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id         BIGINT(20) UNSIGNED NOT NULL,
            plan_id         BIGINT(20) UNSIGNED NOT NULL,
            status          ENUM('active','expired','cancelled','pending') NOT NULL DEFAULT 'pending',
            starts_at       DATETIME NOT NULL,
            expires_at      DATETIME NOT NULL,
            stripe_sub_id   VARCHAR(255),
            created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY status (status)
        ) $charset;" );

        dbDelta( "CREATE TABLE IF NOT EXISTS $pay_table (
            id              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id         BIGINT(20) UNSIGNED NOT NULL,
            subscription_id BIGINT(20) UNSIGNED NOT NULL,
            amount          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            currency        VARCHAR(10) NOT NULL DEFAULT 'USD',
            payment_method  VARCHAR(100),
            status          ENUM('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
            transaction_id  VARCHAR(255),
            stripe_pi_id    VARCHAR(255),
            invoice_url     TEXT,
            created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY subscription_id (subscription_id)
        ) $charset;" );

        // Seed default plans if empty
        $count = $wpdb->get_var( "SELECT COUNT(*) FROM $plan_table" );
        if ( '0' === $count ) {
            $this->seed_default_plans();
        }
    }

    private function seed_default_plans() {
        global $wpdb;
        $table = $wpdb->prefix . 'dd_plans';
        $plans = [
            [
                'name'      => 'Monthly',
                'slug'      => 'monthly',
                'price'     => 9.99,
                'duration'  => 30,
                'features'  => json_encode([
                    'Unlimited dog profiles',
                    'Full directory access',
                    'Photo uploads (front + side)',
                    'Advanced search & filters',
                    'Priority listing',
                ]),
                'is_active' => 1,
            ],
            [
                'name'      => 'Yearly',
                'slug'      => 'yearly',
                'price'     => 79.99,
                'duration'  => 365,
                'features'  => json_encode([
                    'Everything in Monthly',
                    '2 months FREE',
                    'Gallery uploads (up to 10 photos)',
                    'Featured badge on profiles',
                    'Early access to new features',
                ]),
                'is_active' => 1,
            ],
            [
                'name'      => 'Lifetime',
                'slug'      => 'lifetime',
                'price'     => 199.99,
                'duration'  => 36500,
                'features'  => json_encode([
                    'Everything in Yearly',
                    'One-time payment forever',
                    'Unlimited gallery photos',
                    'Premium support',
                    'API access',
                ]),
                'is_active' => 1,
            ],
        ];

        foreach ( $plans as $plan ) {
            $wpdb->insert( $table, $plan );
        }
    }

    /**
     * Get all active plans
     */
    public static function get_plans() {
        global $wpdb;
        $table = $wpdb->prefix . 'dd_plans';
        return $wpdb->get_results( "SELECT * FROM $table WHERE is_active = 1 ORDER BY price ASC" );
    }

    /**
     * Get a single plan by slug
     */
    public static function get_plan( $slug ) {
        global $wpdb;
        $table = $wpdb->prefix . 'dd_plans';
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE slug = %s AND is_active = 1", $slug ) );
    }

    /**
     * Get user's active subscription
     */
    public static function get_user_subscription( $user_id = 0 ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }
        if ( ! $user_id ) return null;

        global $wpdb;
        $subs  = $wpdb->prefix . 'dd_subscriptions';
        $plans = $wpdb->prefix . 'dd_plans';

        return $wpdb->get_row( $wpdb->prepare(
            "SELECT s.*, p.name as plan_name, p.slug as plan_slug, p.price as plan_price, p.features as plan_features
             FROM $subs s
             LEFT JOIN $plans p ON s.plan_id = p.id
             WHERE s.user_id = %d AND s.status = 'active' AND s.expires_at > NOW()
             ORDER BY s.expires_at DESC LIMIT 1",
            $user_id
        ) );
    }

    /**
     * Check if user has active subscription
     */
    public static function user_has_subscription( $user_id = 0 ) {
        return (bool) self::get_user_subscription( $user_id );
    }

    /**
     * Check if current user is subscriber or admin
     */
    public static function can_access_directory() {
        if ( ! is_user_logged_in() ) return false;
        if ( current_user_can( 'manage_options' ) ) return true;
        return self::user_has_subscription();
    }

    /**
     * Create subscription record
     */
    public static function create_subscription( $user_id, $plan_id, $stripe_sub_id = '' ) {
        global $wpdb;
        $subs  = $wpdb->prefix . 'dd_subscriptions';
        $plans = $wpdb->prefix . 'dd_plans';

        $plan = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $plans WHERE id = %d", $plan_id ) );
        if ( ! $plan ) return false;

        $starts  = current_time( 'mysql' );
        $expires = date( 'Y-m-d H:i:s', strtotime( "+{$plan->duration} days" ) );

        // Expire any existing active subs
        $wpdb->update( $subs,
            [ 'status' => 'expired' ],
            [ 'user_id' => $user_id, 'status' => 'active' ]
        );

        $result = $wpdb->insert( $subs, [
            'user_id'       => $user_id,
            'plan_id'       => $plan_id,
            'status'        => 'active',
            'starts_at'     => $starts,
            'expires_at'    => $expires,
            'stripe_sub_id' => $stripe_sub_id,
        ] );

        if ( $result ) {
            update_user_meta( $user_id, 'dd_subscription_status', 'active' );
            update_user_meta( $user_id, 'dd_subscription_plan', $plan->slug );
            update_user_meta( $user_id, 'dd_subscription_expires', $expires );
            // Add subscriber role
            $user = new \WP_User( $user_id );
            $user->add_role( 'dd_subscriber' );
            do_action( 'dd_subscription_activated', $user_id, $plan, $wpdb->insert_id );
            return $wpdb->insert_id;
        }
        return false;
    }

    /**
     * Record a payment
     */
    public static function record_payment( $user_id, $sub_id, $amount, $transaction_id, $stripe_pi_id = '', $invoice_url = '' ) {
        global $wpdb;
        $table = $wpdb->prefix . 'dd_payments';
        return $wpdb->insert( $table, [
            'user_id'         => $user_id,
            'subscription_id' => $sub_id,
            'amount'          => $amount,
            'currency'        => 'USD',
            'payment_method'  => 'stripe',
            'status'          => 'completed',
            'transaction_id'  => $transaction_id,
            'stripe_pi_id'    => $stripe_pi_id,
            'invoice_url'     => $invoice_url,
        ] );
    }

    /**
     * Get user payment history
     */
    public static function get_payment_history( $user_id = 0, $limit = 20 ) {
        if ( ! $user_id ) $user_id = get_current_user_id();
        global $wpdb;
        $pay  = $wpdb->prefix . 'dd_payments';
        $subs = $wpdb->prefix . 'dd_subscriptions';
        $plans = $wpdb->prefix . 'dd_plans';
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT py.*, p.name as plan_name
             FROM $pay py
             LEFT JOIN $subs s ON py.subscription_id = s.id
             LEFT JOIN $plans p ON s.plan_id = p.id
             WHERE py.user_id = %d
             ORDER BY py.created_at DESC
             LIMIT %d",
            $user_id, $limit
        ) );
    }

    /**
     * Cancel subscription
     */
    public function cancel_subscription() {
        check_ajax_referer( 'dd_cancel_sub_nonce', 'nonce' );
        if ( ! is_user_logged_in() ) wp_send_json_error( ['message' => __('Not logged in.', 'petslist')] );

        $user_id = get_current_user_id();
        global $wpdb;
        $subs = $wpdb->prefix . 'dd_subscriptions';

        $sub = self::get_user_subscription( $user_id );
        if ( ! $sub ) {
            wp_send_json_error( ['message' => __('No active subscription found.', 'petslist')] );
        }

        $wpdb->update( $subs, [ 'status' => 'cancelled' ], [ 'id' => $sub->id ] );
        update_user_meta( $user_id, 'dd_subscription_status', 'cancelled' );

        do_action( 'dd_subscription_cancelled', $user_id, $sub );
        wp_send_json_success( ['message' => __('Subscription cancelled successfully.', 'petslist')] );
    }

    /**
     * Handle subscription AJAX (non-Stripe flow for demo)
     */
    public function handle_subscription() {
        check_ajax_referer( 'dd_subscribe_nonce', 'nonce' );
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( ['message' => __('Please log in first.', 'petslist'), 'redirect' => dd_login_url()] );
        }
        $plan_slug = sanitize_text_field( $_POST['plan'] ?? '' );
        $plan = self::get_plan( $plan_slug );
        if ( ! $plan ) {
            wp_send_json_error( ['message' => __('Invalid plan selected.', 'petslist')] );
        }
        wp_send_json_success( [
            'plan'     => $plan,
            'checkout' => dd_checkout_url( $plan_slug ),
        ] );
    }

    public function handle_subscription_guest() {
        wp_send_json_error( ['message' => __('Please log in to subscribe.', 'petslist'), 'redirect' => dd_login_url()] );
    }

    /**
     * Daily cron: expire old subscriptions
     */
    public function check_expired_subscriptions() {
        global $wpdb;
        $subs = $wpdb->prefix . 'dd_subscriptions';
        $expired = $wpdb->get_results(
            "SELECT * FROM $subs WHERE status = 'active' AND expires_at < NOW()"
        );
        foreach ( $expired as $sub ) {
            $wpdb->update( $subs, [ 'status' => 'expired' ], [ 'id' => $sub->id ] );
            update_user_meta( $sub->user_id, 'dd_subscription_status', 'expired' );
            do_action( 'dd_subscription_expired', $sub->user_id, $sub );
        }
    }

    public function user_subscription_action( $actions, $user ) {
        $sub = self::get_user_subscription( $user->ID );
        if ( $sub ) {
            $actions['dd_sub'] = '<span style="color:#02c5bd">✓ Subscriber (' . esc_html($sub->plan_name) . ')</span>';
        }
        return $actions;
    }
}
