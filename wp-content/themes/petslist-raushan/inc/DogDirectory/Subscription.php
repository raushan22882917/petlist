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

        // Table: dd_coupons (Promo codes for free monthly or custom subscription access)
        $coupons_table     = $wpdb->prefix . 'dd_coupons';
        $redemptions_table = $wpdb->prefix . 'dd_coupon_redemptions';

        dbDelta( "CREATE TABLE IF NOT EXISTS $coupons_table (
            id                  BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            code                VARCHAR(50) NOT NULL,
            name                VARCHAR(100) NOT NULL DEFAULT '',
            discount_type       VARCHAR(20) NOT NULL DEFAULT 'free',
            plan_slug           VARCHAR(100) NOT NULL DEFAULT 'all',
            duration_days       INT(11) NOT NULL DEFAULT 30,
            max_uses            INT(11) NOT NULL DEFAULT 0,
            times_used          INT(11) NOT NULL DEFAULT 0,
            assigned_user_id    BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            assigned_user_email VARCHAR(100) NOT NULL DEFAULT '',
            expires_at          DATETIME NULL,
            is_active           TINYINT(1) NOT NULL DEFAULT 1,
            created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY code (code),
            KEY assigned_user_id (assigned_user_id)
        ) $charset;" );

        // Ensure new assigned columns exist if table was already created
        $has_col = $wpdb->get_var( "SHOW COLUMNS FROM $coupons_table LIKE 'assigned_user_id'" );
        if ( ! $has_col ) {
            $wpdb->query( "ALTER TABLE $coupons_table ADD COLUMN assigned_user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 AFTER times_used, ADD COLUMN assigned_user_email VARCHAR(100) NOT NULL DEFAULT '' AFTER assigned_user_id" );
        }

        dbDelta( "CREATE TABLE IF NOT EXISTS $redemptions_table (
            id              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            coupon_id       BIGINT(20) UNSIGNED NOT NULL,
            user_id         BIGINT(20) UNSIGNED NOT NULL,
            subscription_id BIGINT(20) UNSIGNED NOT NULL,
            code            VARCHAR(50) NOT NULL,
            redeemed_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY coupon_id (coupon_id),
            UNIQUE KEY user_coupon (user_id, coupon_id)
        ) $charset;" );

        // Remove legacy default FREEMONTH / FREE30 codes if present
        $wpdb->query( "DELETE FROM $coupons_table WHERE code IN ('FREEMONTH', 'FREE30') AND times_used = 0" );

        // Seed default plans if empty
        $count = $wpdb->get_var( "SELECT COUNT(*) FROM $plan_table" );
        if ( '0' === $count ) {
            $this->seed_default_plans();
        }

        // Migrate existing plans to single $5.99 Monthly plan (automatic update for production/cPanel)
        if ( ! get_option( 'dd_plans_updated_v2' ) ) {
            $wpdb->update( 
                $plan_table, 
                [ 'price' => 5.99, 'is_active' => 1 ], 
                [ 'slug' => 'monthly' ] 
            );
            $wpdb->update( 
                $plan_table, 
                [ 'is_active' => 0 ], 
                [ 'slug' => 'yearly' ] 
            );
            $wpdb->update( 
                $plan_table, 
                [ 'is_active' => 0 ], 
                [ 'slug' => 'lifetime' ] 
            );
            update_option( 'dd_plans_updated_v2', 1 );
        }

        // Migrate to Studs ($10), Kennels ($20), Businesses/Companies ($50) plans
        if ( ! get_option( 'dd_plans_updated_v3' ) ) {
            $wpdb->query( "TRUNCATE TABLE $plan_table" );
            $this->seed_default_plans();
            update_option( 'dd_plans_updated_v3', 1 );
        }

        // Restore $5.99 Standard Listing plan alongside Ad Packages (v4)
        if ( ! get_option( 'dd_plans_updated_v4' ) ) {
            $wpdb->query( "TRUNCATE TABLE $plan_table" );
            $this->seed_default_plans();
            update_option( 'dd_plans_updated_v4', 1 );
        }
    }

    private function seed_default_plans() {
        global $wpdb;
        $table = $wpdb->prefix . 'dd_plans';
        $plans = [
            [
                'name'      => 'Standard Listing',
                'slug'      => 'monthly',
                'price'     => 5.99,
                'duration'  => 30,
                'features'  => json_encode([
                    'List unlimited dogs',
                    'Full directory access',
                    'Pedigree & health clearance access',
                    'Direct owner contact',
                ]),
                'is_active' => 1,
            ],
            [
                'name'      => 'Studs',
                'slug'      => 'studs',
                'price'     => 10.00,
                'duration'  => 30,
                'features'  => json_encode([
                    'List your stud dogs',
                    'Full directory access',
                    'Photo uploads (front + side)',
                    'Search & filter access',
                ]),
                'is_active' => 1,
            ],
            [
                'name'      => 'Kennels',
                'slug'      => 'kennels',
                'price'     => 20.00,
                'duration'  => 30,
                'features'  => json_encode([
                    'Everything in Studs',
                    'Featured badges',
                    'Gallery uploads',
                    'Priority listing',
                ]),
                'is_active' => 1,
            ],
            [
                'name'      => 'Businesses/Companies',
                'slug'      => 'businesses-companies',
                'price'     => 50.00,
                'duration'  => 30,
                'features'  => json_encode([
                    'Everything in Kennels',
                    'Premium homepage placement',
                    'Unlimited listings',
                    'Priority admin support',
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
        if ( dd_is_admin() ) return true;
        return self::user_has_subscription();
    }

    public static function get_active_subscriptions_count() {
        global $wpdb;
        $table = $wpdb->prefix . 'dd_subscriptions';
        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE status = 'active' AND expires_at > NOW()" );
    }

    public static function has_reached_sales_limit() {
        return self::get_active_subscriptions_count() >= 9;
    }

    /**
     * Create subscription record
     */
    public static function create_subscription( $user_id, $plan_id, $stripe_sub_id = '', $duration_days = 0, $bypass_limit = false ) {
        if ( ! $bypass_limit && self::has_reached_sales_limit() ) {
            return false;
        }

        global $wpdb;
        $subs  = $wpdb->prefix . 'dd_subscriptions';
        $plans = $wpdb->prefix . 'dd_plans';

        $plan = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $plans WHERE id = %d", $plan_id ) );
        if ( ! $plan ) return false;

        $duration = (int) ( $duration_days > 0 ? $duration_days : $plan->duration );
        $starts   = current_time( 'mysql' );
        $expires  = date( 'Y-m-d H:i:s', strtotime( "+{$duration} days" ) );

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

            // Sync with wp_dog_users table if it exists
            $dog_users_table = $wpdb->prefix . 'dog_users';
            if ( $wpdb->get_var( "SHOW TABLES LIKE '$dog_users_table'" ) === $dog_users_table ) {
                $wpdb->query( $wpdb->prepare(
                    "UPDATE $dog_users_table SET role = 'subscriber', subscription_id = 1 WHERE wp_user_id = %d",
                    $user_id
                ) );
            }

            do_action( 'dd_subscription_activated', $user_id, $plan, $wpdb->insert_id );
            return $wpdb->insert_id;
        }
        return false;
    }

    /**
     * Record a payment
     */
    public static function record_payment( $user_id, $sub_id, $amount, $transaction_id, $stripe_pi_id = '', $invoice_url = '', $payment_method = 'stripe' ) {
        global $wpdb;
        $table = $wpdb->prefix . 'dd_payments';
        $inserted = $wpdb->insert( $table, [
            'user_id'         => $user_id,
            'subscription_id' => $sub_id,
            'amount'          => $amount,
            'currency'        => 'USD',
            'payment_method'  => $payment_method,
            'status'          => 'completed',
            'transaction_id'  => $transaction_id,
            'stripe_pi_id'    => $stripe_pi_id,
            'invoice_url'     => $invoice_url,
        ] );

        if ( $inserted ) {
            $payment_id = $wpdb->insert_id;
            do_action( 'dd_subscription_payment_recorded', $payment_id, $user_id, $sub_id, $amount, $transaction_id, $payment_method );
            return $payment_id;
        }
        return false;
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

    // =========================================================
    // PROMO CODES / COUPONS SYSTEM
    // =========================================================

    /**
     * Validate a promo code
     */
    public static function validate_coupon( $code, $plan_slug = '', $user_id = 0 ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }
        $code = strtoupper( trim( sanitize_text_field( $code ) ) );
        if ( empty( $code ) ) {
            return [ 'valid' => false, 'message' => __( 'Please enter a promo code.', 'petslist' ) ];
        }

        global $wpdb;
        $coupons_table     = $wpdb->prefix . 'dd_coupons';
        $redemptions_table = $wpdb->prefix . 'dd_coupon_redemptions';

        // Check table exists
        if ( $wpdb->get_var( "SHOW TABLES LIKE '$coupons_table'" ) !== $coupons_table ) {
            self::instance()->create_subscription_tables();
        }

        $coupon = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $coupons_table WHERE UPPER(code) = %s",
            $code
        ) );

        if ( ! $coupon ) {
            return [ 'valid' => false, 'message' => __( 'Invalid promo code. Please check and try again.', 'petslist' ) ];
        }

        if ( ! $coupon->is_active ) {
            return [ 'valid' => false, 'message' => __( 'This promo code is currently inactive.', 'petslist' ) ];
        }

        if ( ! empty( $coupon->expires_at ) && strtotime( $coupon->expires_at ) < time() ) {
            return [ 'valid' => false, 'message' => __( 'This promo code has expired.', 'petslist' ) ];
        }

        if ( $coupon->max_uses > 0 && $coupon->times_used >= $coupon->max_uses ) {
            return [ 'valid' => false, 'message' => __( 'This promo code has reached its maximum usage limit.', 'petslist' ) ];
        }        if ( ! empty( $plan_slug ) && $coupon->plan_slug !== 'all' && $coupon->plan_slug !== $plan_slug ) {
            return [ 'valid' => false, 'message' => sprintf( __( 'This promo code is only valid for the %s plan.', 'petslist' ), esc_html( ucfirst( $coupon->plan_slug ) ) ) ];
        }

        // Exclusive assigned user check (private promo code)
        $assigned_uid   = ! empty( $coupon->assigned_user_id ) ? (int) $coupon->assigned_user_id : 0;
        $assigned_email = ! empty( $coupon->assigned_user_email ) ? strtolower( trim( $coupon->assigned_user_email ) ) : '';

        if ( $assigned_uid > 0 || ! empty( $assigned_email ) ) {
            if ( ! $user_id ) {
                return [
                    'valid'   => false,
                    'message' => __( 'Invalid promo code. Please check and try again.', 'petslist' ),
                ];
            }

            $current_user  = get_userdata( $user_id );
            $current_email = $current_user ? strtolower( trim( $current_user->user_email ) ) : '';

            $matches_id    = ( $assigned_uid > 0 && (int) $user_id === $assigned_uid );
            $matches_email = ( ! empty( $assigned_email ) && $current_email === $assigned_email );

            if ( ! $matches_id && ! $matches_email ) {
                return [
                    'valid'   => false,
                    'message' => __( 'Invalid promo code. Please check and try again.', 'petslist' ),
                ];
            }
        }

        if ( $user_id ) {
            $already_used = (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM $redemptions_table WHERE user_id = %d AND coupon_id = %d",
                $user_id,
                $coupon->id
            ) );
            if ( $already_used > 0 ) {
                return [ 'valid' => false, 'message' => __( 'You have already redeemed this promo code.', 'petslist' ) ];
            }
        }

        return [
            'valid'         => true,
            'coupon'        => $coupon,
            'discount_type' => $coupon->discount_type,
            'duration_days' => (int) $coupon->duration_days,
            'message'       => __( 'Promo code applied! 100% Free monthly subscription access granted.', 'petslist' ),
        ];
    }

    /**
     * Redeem a promo code for a user
     */
    public static function redeem_coupon( $code, $plan_slug = 'monthly', $user_id = 0 ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }
        if ( ! $user_id ) {
            return [ 'success' => false, 'message' => __( 'Please log in to redeem a promo code.', 'petslist' ) ];
        }

        $check = self::validate_coupon( $code, $plan_slug, $user_id );
        if ( ! $check['valid'] ) {
            return [ 'success' => false, 'message' => $check['message'] ];
        }

        $coupon = $check['coupon'];
        $plan   = self::get_plan( $plan_slug );
        if ( ! $plan ) {
            // Fallback to monthly / first active plan
            $plans = self::get_plans();
            $plan  = $plans[0] ?? null;
        }

        if ( ! $plan ) {
            return [ 'success' => false, 'message' => __( 'No active plan found to apply promo code.', 'petslist' ) ];
        }

        $duration = (int) ( $coupon->duration_days > 0 ? $coupon->duration_days : $plan->duration );
        // Bypass sales cap for valid promotional invitations
        $sub_id = self::create_subscription( $user_id, $plan->id, 'promo_' . strtolower( $coupon->code ), $duration, true );

        if ( ! $sub_id ) {
            return [ 'success' => false, 'message' => __( 'Could not activate subscription. Please try again.', 'petslist' ) ];
        }

        global $wpdb;
        $coupons_table     = $wpdb->prefix . 'dd_coupons';
        $redemptions_table = $wpdb->prefix . 'dd_coupon_redemptions';

        // Record $0.00 payment in transaction logs
        $txn_id = 'promo_' . strtoupper( $coupon->code ) . '_' . strtoupper( wp_generate_password( 8, false ) );
        self::record_payment( $user_id, $sub_id, 0.00, $txn_id, '', '', 'Promo Code: ' . $coupon->code );

        // Track redemption to prevent abuse
        $wpdb->insert( $redemptions_table, [
            'coupon_id'       => $coupon->id,
            'user_id'         => $user_id,
            'subscription_id' => $sub_id,
            'code'            => $coupon->code,
            'redeemed_at'     => current_time( 'mysql' ),
        ] );

        // Increment times used
        $wpdb->query( $wpdb->prepare(
            "UPDATE $coupons_table SET times_used = times_used + 1 WHERE id = %d",
            $coupon->id
        ) );

        do_action( 'dd_coupon_redeemed', $user_id, $coupon, $sub_id );

        $duration_msg = ( $duration >= 3650 )
            ? __( 'Lifetime', 'petslist' )
            : sprintf( __( '%d-day', 'petslist' ), $duration );

        return [
            'success'  => true,
            'message'  => sprintf( __( 'Congratulations! Code "%s" successfully redeemed. Your free %s subscription is now active!', 'petslist' ), esc_html( $coupon->code ), $duration_msg ),
            'redirect' => add_query_arg( [ 'tab' => 'subscription', 'promo' => 'success' ], dd_dashboard_url() ),
        ];
    }

    /**
     * Get coupons list
     */
    public static function get_coupons( $include_inactive = true ) {
        global $wpdb;
        $table = $wpdb->prefix . 'dd_coupons';
        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) !== $table ) {
            self::instance()->create_subscription_tables();
        }
        $where = $include_inactive ? '1=1' : 'is_active = 1';
        return $wpdb->get_results( "SELECT * FROM $table WHERE $where ORDER BY created_at DESC" );
    }

    /**
     * Get single coupon by ID or code
     */
    public static function get_coupon( $id_or_code ) {
        global $wpdb;
        $table = $wpdb->prefix . 'dd_coupons';
        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) !== $table ) {
            self::instance()->create_subscription_tables();
        }
        if ( is_numeric( $id_or_code ) ) {
            return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id_or_code ) );
        }
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE UPPER(code) = %s", strtoupper( trim( $id_or_code ) ) ) );
    }

    /**
     * Save coupon (insert or update)
     */
    public static function save_coupon( $data ) {
        global $wpdb;
        $table = $wpdb->prefix . 'dd_coupons';
        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) !== $table ) {
            self::instance()->create_subscription_tables();
        }
        $code  = strtoupper( trim( sanitize_text_field( $data['code'] ?? '' ) ) );
        if ( empty( $code ) ) {
            return new \WP_Error( 'invalid_code', __( 'Coupon code is required.', 'petslist' ) );
        }

        $id = absint( $data['id'] ?? 0 );

        // Resolve assigned user & email
        $assigned_user_id    = absint( $data['assigned_user_id'] ?? 0 );
        $assigned_user_email = sanitize_email( $data['assigned_user_email'] ?? '' );

        if ( $assigned_user_id > 0 ) {
            $assigned_user = get_userdata( $assigned_user_id );
            if ( $assigned_user && empty( $assigned_user_email ) ) {
                $assigned_user_email = $assigned_user->user_email;
            }
        } elseif ( ! empty( $assigned_user_email ) ) {
            $user_by_email = get_user_by( 'email', $assigned_user_email );
            if ( $user_by_email ) {
                $assigned_user_id = $user_by_email->ID;
            }
        }

        $payload = [
            'code'                => $code,
            'name'                => sanitize_text_field( $data['name'] ?? '' ),
            'discount_type'       => sanitize_text_field( $data['discount_type'] ?? 'free' ),
            'plan_slug'           => sanitize_text_field( $data['plan_slug'] ?? 'all' ),
            'duration_days'       => max( 1, absint( $data['duration_days'] ?? 30 ) ),
            'max_uses'            => absint( $data['max_uses'] ?? 0 ),
            'assigned_user_id'    => $assigned_user_id,
            'assigned_user_email' => $assigned_user_email,
            'is_active'           => isset( $data['is_active'] ) ? absint( $data['is_active'] ) : 1,
            'expires_at'          => ! empty( $data['expires_at'] ) ? date( 'Y-m-d 23:59:59', strtotime( $data['expires_at'] ) ) : null,
        ];

        if ( $id ) {
            $wpdb->update( $table, $payload, [ 'id' => $id ] );
            $coupon_id = $id;
        } else {
            // Check if code already exists
            $exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE UPPER(code) = %s", $code ) );
            if ( $exists ) {
                return new \WP_Error( 'duplicate_code', __( 'A promo code with this code already exists.', 'petslist' ) );
            }
            $payload['created_at'] = current_time( 'mysql' );
            $wpdb->insert( $table, $payload );
            $coupon_id = $wpdb->insert_id;
        }

        // Send email if requested
        if ( ! empty( $data['send_email'] ) && ( $assigned_user_id > 0 || ! empty( $assigned_user_email ) ) ) {
            self::send_coupon_email( $coupon_id );
        }

        return $coupon_id;
    }

    /**
     * Send email with coupon details to assigned user
     */
    public static function send_coupon_email( $coupon_or_id, $override_email = '' ) {
        $coupon = is_object( $coupon_or_id ) ? $coupon_or_id : self::get_coupon( $coupon_or_id );
        if ( ! $coupon ) {
            return new \WP_Error( 'coupon_not_found', __( 'Coupon not found.', 'petslist' ) );
        }

        $recipient_email = $override_email ?: $coupon->assigned_user_email;
        $recipient_name  = 'Valued Member';

        if ( ! empty( $coupon->assigned_user_id ) ) {
            $user = get_userdata( $coupon->assigned_user_id );
            if ( $user ) {
                $recipient_name = $user->display_name ?: $user->user_login;
                if ( empty( $recipient_email ) ) {
                    $recipient_email = $user->user_email;
                }
            }
        }

        if ( empty( $recipient_email ) || ! is_email( $recipient_email ) ) {
            return new \WP_Error( 'no_recipient_email', __( 'No valid recipient email found for this coupon.', 'petslist' ) );
        }

        $plan_label = 'All Directory Plans';
        if ( ! empty( $coupon->plan_slug ) && $coupon->plan_slug !== 'all' ) {
            $plan = self::get_plan( $coupon->plan_slug );
            $plan_label = $plan ? $plan->name : ucfirst( $coupon->plan_slug );
        }

        $duration_days = (int) ( $coupon->duration_days ?: 30 );
        $expiry_text   = ! empty( $coupon->expires_at ) ? date( 'F j, Y', strtotime( $coupon->expires_at ) ) : __( 'No expiration date', 'petslist' );
        $duration_badge_text = ( $duration_days >= 3650 )
            ? sprintf( __( '✓ Lifetime 100%% Free Access (%s)', 'petslist' ), esc_html( $plan_label ) )
            : sprintf( __( '✓ %d Days 100%% Free Access (%s)', 'petslist' ), $duration_days, esc_html( $plan_label ) );

        $site_name    = get_bloginfo( 'name' );
        $checkout_url = dd_checkout_url( $coupon->plan_slug !== 'all' ? $coupon->plan_slug : 'monthly' );
        $redeem_url   = add_query_arg( [ 'coupon' => $coupon->code ], $checkout_url );

        $subject = sprintf( __( '🎟️ Exclusive Voucher: %s Complimentary Access (%s)', 'petslist' ), $site_name, $coupon->code );

        $content = '
        <div style="font-family:-apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; max-width:600px; margin:0 auto; color:#1e293b;">
            <p style="font-size:16px; line-height:1.6; margin-bottom:16px;">Hello <strong>' . esc_html( $recipient_name ) . '</strong>,</p>
            <p style="font-size:15px; line-height:1.6; color:#475569; margin-bottom:24px;">
                You have been granted an exclusive promo voucher by the administrator on <strong>' . esc_html( $site_name ) . '</strong>. This voucher grants you <strong>100% complimentary free subscription access</strong>!
            </p>

            <div style="background:#f8fafc; border:2px dashed #02c5bd; border-radius:12px; padding:24px; text-align:center; margin:28px 0;">
                <div style="font-size:12px; text-transform:uppercase; letter-spacing:1px; color:#64748b; font-weight:700; margin-bottom:8px;">Your Private Promo Voucher</div>
                <div style="font-size:32px; font-weight:900; letter-spacing:3px; color:#0f172a; font-family:monospace; background:#ffffff; border:1px solid #e2e8f0; border-radius:8px; padding:12px 20px; display:inline-block; margin-bottom:12px;">' . esc_html( $coupon->code ) . '</div>
                <div style="font-size:14px; color:#02c5bd; font-weight:700;">' . $duration_badge_text . '</div>
                <div style="font-size:12px; color:#94a3b8; margin-top:6px;">' . sprintf( __( 'Valid until: %s', 'petslist' ), esc_html( $expiry_text ) ) . '</div>
            </div>

            <div style="text-align:center; margin:32px 0;">
                <a href="' . esc_url( $redeem_url ) . '" style="background:#02c5bd; color:#ffffff; font-weight:700; font-size:16px; padding:14px 32px; border-radius:50px; text-decoration:none; display:inline-block; box-shadow:0 4px 14px rgba(2,197,189,0.35);">
                    🚀 ' . __( 'Claim Your Free Access Now', 'petslist' ) . '
                </a>
            </div>

            <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:8px; padding:16px; font-size:13px; color:#64748b; line-height:1.6;">
                <strong>' . __( 'How to activate:', 'petslist' ) . '</strong><br>
                1. Click the button above or visit <a href="' . esc_url( $checkout_url ) . '" style="color:#02c5bd;">' . esc_html( $checkout_url ) . '</a>.<br>
                2. Log into your account (<strong>' . esc_html( $recipient_email ) . '</strong>).<br>
                3. Enter your private code <strong>' . esc_html( $coupon->code ) . '</strong> at checkout.<br>
                4. Your subscription activates immediately with $0.00 charge.
            </div>

            <p style="font-size:12px; color:#94a3b8; margin-top:24px; text-align:center;">
                ' . __( 'Note: This promo code is strictly assigned to your email and cannot be used by other accounts.', 'petslist' ) . '
            </p>
        </div>';

        $body = Notifications::instance()->wrap_email( __( 'Your Exclusive Promo Voucher', 'petslist' ), $content );

        $headers = [ 'Content-Type: text/html; charset=UTF-8' ];
        $sent = wp_mail( $recipient_email, $subject, $body, $headers );

        if ( ! $sent ) {
            return new \WP_Error( 'mail_failed', sprintf( __( 'Failed to send email to %s. Please check SMTP / mail settings.', 'petslist' ), $recipient_email ) );
        }

        return [
            'success' => true,
            'email'   => $recipient_email,
            'message' => sprintf( __( 'Promo code email sent successfully to %s!', 'petslist' ), $recipient_email ),
        ];
    }

    /**
     * Delete coupon
     */
    public static function delete_coupon( $id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'dd_coupons';
        return $wpdb->delete( $table, [ 'id' => absint( $id ) ] );
    }

    /**
     * Get recent coupon redemptions
     */
    public static function get_coupon_redemptions( $limit = 50 ) {
        global $wpdb;
        $r_table = $wpdb->prefix . 'dd_coupon_redemptions';
        $u_table = $wpdb->users;
        if ( $wpdb->get_var( "SHOW TABLES LIKE '$r_table'" ) !== $r_table ) {
            return [];
        }
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT r.*, u.user_login, u.user_email, u.display_name
             FROM $r_table r
             LEFT JOIN $u_table u ON r.user_id = u.ID
             ORDER BY r.redeemed_at DESC LIMIT %d",
            $limit
        ) );
    }

    // =========================================================
    // ADMIN SUBSCRIPTION MANAGEMENT CONTROLS
    // =========================================================

    /**
     * Admin manually grants a subscription to any user
     */
    public static function admin_grant_subscription( $user_id, $plan_id, $duration_days = 30, $notes = '' ) {
        $user_id = absint( $user_id );
        $plan_id = absint( $plan_id );
        $duration_days = max( 1, absint( $duration_days ?: 30 ) );

        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return new \WP_Error( 'invalid_user', __( 'User not found.', 'petslist' ) );
        }

        global $wpdb;
        $plan = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}dd_plans WHERE id = %d", $plan_id ) );
        if ( ! $plan ) {
            return new \WP_Error( 'invalid_plan', __( 'Selected plan not found.', 'petslist' ) );
        }

        // Create subscription with bypass limit
        $sub_id = self::create_subscription( $user_id, $plan_id, 'admin_grant', $duration_days, true );
        if ( ! $sub_id ) {
            return new \WP_Error( 'create_failed', __( 'Failed to create subscription record.', 'petslist' ) );
        }

        // Record zero-dollar administrative transaction in payments
        $tx_id = 'ADMIN-' . strtoupper( wp_generate_password( 8, false ) );
        self::record_payment( $user_id, $sub_id, 0.00, $tx_id, '', '', 'admin_grant' );

        if ( ! empty( $notes ) ) {
            update_user_meta( $user_id, 'dd_admin_sub_notes', sanitize_text_field( $notes ) );
        }

        self::admin_sync_user_status( $user_id );

        $grant_duration_text = ( $duration_days >= 3650 )
            ? __( 'Lifetime', 'petslist' )
            : sprintf( __( '%d days', 'petslist' ), $duration_days );

        return [
            'success'         => true,
            'subscription_id' => $sub_id,
            'message'         => sprintf( __( 'Subscription "%s" (%s) successfully granted to %s.', 'petslist' ), esc_html( $plan->name ), $grant_duration_text, esc_html( $user->display_name ) ),
        ];
    }

    /**
     * Admin extends an existing subscription by N days
     */
    public static function admin_extend_subscription( $sub_id, $days = 30 ) {
        global $wpdb;
        $subs_table = $wpdb->prefix . 'dd_subscriptions';
        $sub = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $subs_table WHERE id = %d", absint( $sub_id ) ) );
        if ( ! $sub ) {
            return new \WP_Error( 'not_found', __( 'Subscription not found.', 'petslist' ) );
        }

        $days = max( 1, absint( $days ?: 30 ) );
        $now = current_time( 'mysql' );

        // If subscription is active and in the future, add to current expires_at
        if ( $sub->status === 'active' && strtotime( $sub->expires_at ) > strtotime( $now ) ) {
            $base_time = strtotime( $sub->expires_at );
            $new_expires = date( 'Y-m-d H:i:s', strtotime( "+{$days} days", $base_time ) );
            $new_starts  = $sub->starts_at;
        } else {
            // If expired or cancelled, restart from now
            $new_starts  = $now;
            $new_expires = date( 'Y-m-d H:i:s', strtotime( "+{$days} days", strtotime( $now ) ) );
        }

        $wpdb->update(
            $subs_table,
            [
                'status'     => 'active',
                'starts_at'  => $new_starts,
                'expires_at' => $new_expires,
                'updated_at' => current_time( 'mysql' ),
            ],
            [ 'id' => $sub->id ]
        );

        self::admin_sync_user_status( $sub->user_id );

        return [
            'success'           => true,
            'new_expires'       => $new_expires,
            'formatted_expires' => date( 'M j, Y', strtotime( $new_expires ) ),
            'message'           => sprintf( __( 'Subscription extended by %d days. New expiration: %s.', 'petslist' ), $days, date( 'M j, Y', strtotime( $new_expires ) ) ),
        ];
    }

    /**
     * Admin updates subscription details (plan, status, custom expiration)
     */
    public static function admin_update_subscription( $sub_id, $data ) {
        global $wpdb;
        $subs_table = $wpdb->prefix . 'dd_subscriptions';
        $sub = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $subs_table WHERE id = %d", absint( $sub_id ) ) );
        if ( ! $sub ) {
            return new \WP_Error( 'not_found', __( 'Subscription not found.', 'petslist' ) );
        }

        $update = [];

        if ( isset( $data['plan_id'] ) ) {
            $plan_id = absint( $data['plan_id'] );
            if ( $plan_id > 0 ) {
                $update['plan_id'] = $plan_id;
            }
        }

        if ( isset( $data['status'] ) ) {
            $allowed = [ 'active', 'expired', 'cancelled', 'pending' ];
            if ( in_array( $data['status'], $allowed, true ) ) {
                $update['status'] = $data['status'];
            }
        }

        if ( ! empty( $data['expires_at'] ) ) {
            $time = strtotime( $data['expires_at'] );
            if ( $time ) {
                $update['expires_at'] = date( 'Y-m-d H:i:s', $time );
            }
        }

        if ( empty( $update ) ) {
            return new \WP_Error( 'no_data', __( 'No fields to update.', 'petslist' ) );
        }

        $update['updated_at'] = current_time( 'mysql' );
        $wpdb->update( $subs_table, $update, [ 'id' => $sub->id ] );

        self::admin_sync_user_status( $sub->user_id );

        return [
            'success' => true,
            'message' => __( 'Subscription successfully updated.', 'petslist' ),
        ];
    }

    /**
     * Admin cancels a subscription
     */
    public static function admin_cancel_subscription( $sub_id ) {
        global $wpdb;
        $subs_table = $wpdb->prefix . 'dd_subscriptions';
        $sub = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $subs_table WHERE id = %d", absint( $sub_id ) ) );
        if ( ! $sub ) {
            return new \WP_Error( 'not_found', __( 'Subscription not found.', 'petslist' ) );
        }

        $wpdb->update( $subs_table, [ 'status' => 'cancelled', 'updated_at' => current_time( 'mysql' ) ], [ 'id' => $sub->id ] );

        self::admin_sync_user_status( $sub->user_id );

        return [
            'success' => true,
            'message' => __( 'Subscription cancelled.', 'petslist' ),
        ];
    }

    /**
     * Admin permanently deletes a subscription record
     */
    public static function admin_delete_subscription( $sub_id ) {
        global $wpdb;
        $subs_table = $wpdb->prefix . 'dd_subscriptions';
        $sub = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $subs_table WHERE id = %d", absint( $sub_id ) ) );
        if ( ! $sub ) {
            return new \WP_Error( 'not_found', __( 'Subscription not found.', 'petslist' ) );
        }

        $user_id = $sub->user_id;
        $wpdb->delete( $subs_table, [ 'id' => $sub->id ] );

        self::admin_sync_user_status( $user_id );

        return [
            'success' => true,
            'message' => __( 'Subscription deleted successfully.', 'petslist' ),
        ];
    }

    /**
     * Sync user status, metadata, and roles based on their current active subscriptions
     */
    public static function admin_sync_user_status( $user_id ) {
        $user_id = absint( $user_id );
        if ( ! $user_id ) return;

        global $wpdb;
        $subs_table  = $wpdb->prefix . 'dd_subscriptions';
        $plans_table = $wpdb->prefix . 'dd_plans';

        $active_sub = $wpdb->get_row( $wpdb->prepare(
            "SELECT s.*, p.slug as plan_slug
             FROM $subs_table s
             LEFT JOIN $plans_table p ON s.plan_id = p.id
             WHERE s.user_id = %d AND s.status = 'active' AND s.expires_at > NOW()
             ORDER BY s.expires_at DESC LIMIT 1",
            $user_id
        ) );

        $user = new \WP_User( $user_id );

        if ( $active_sub ) {
            update_user_meta( $user_id, 'dd_subscription_status', 'active' );
            update_user_meta( $user_id, 'dd_subscription_plan', $active_sub->plan_slug );
            update_user_meta( $user_id, 'dd_subscription_expires', $active_sub->expires_at );
            if ( ! in_array( 'dd_subscriber', (array) $user->roles, true ) ) {
                $user->add_role( 'dd_subscriber' );
            }

            // Sync dog_users table
            $dog_users_table = $wpdb->prefix . 'dog_users';
            if ( $wpdb->get_var( "SHOW TABLES LIKE '$dog_users_table'" ) === $dog_users_table ) {
                $wpdb->query( $wpdb->prepare(
                    "UPDATE $dog_users_table SET role = 'subscriber', subscription_id = 1 WHERE wp_user_id = %d",
                    $user_id
                ) );
            }
        } else {
            // Find latest subscription for history status
            $latest_sub = $wpdb->get_row( $wpdb->prepare(
                "SELECT * FROM $subs_table WHERE user_id = %d ORDER BY created_at DESC LIMIT 1",
                $user_id
            ) );

            $status = $latest_sub ? $latest_sub->status : 'expired';
            update_user_meta( $user_id, 'dd_subscription_status', $status );

            // If user is not an administrator, remove dd_subscriber role
            if ( ! in_array( 'administrator', (array) $user->roles, true ) ) {
                $user->remove_role( 'dd_subscriber' );
            }

            // Sync dog_users table
            $dog_users_table = $wpdb->prefix . 'dog_users';
            if ( $wpdb->get_var( "SHOW TABLES LIKE '$dog_users_table'" ) === $dog_users_table ) {
                $wpdb->query( $wpdb->prepare(
                    "UPDATE $dog_users_table SET role = 'user' WHERE wp_user_id = %d AND role = 'subscriber'",
                    $user_id
                ) );
            }
        }
    }
}

