<?php
/**
 * Dog Directory - Scripts & Styles
 * @package Petslist Dog Directory
 */

namespace RadiusTheme\Petslist\DogDirectory;

if ( ! defined( 'ABSPATH' ) ) exit;

class Scripts {

    protected static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend' ], 20 );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin' ] );
        add_action( 'admin_head', [ $this, 'admin_inline_styles' ] );
    }

    public function enqueue_frontend() {
        $v   = ( defined('WP_DEBUG') && WP_DEBUG ) ? time() : DD_VERSION;
        $uri = get_template_directory_uri();

        $load_dd_css = is_singular('dd_dog')
            || is_post_type_archive('dd_dog')
            || is_tax(['dd_breed','dd_kennel','dd_location'])
            || dd_is_pricing_page()
            || dd_is_checkout_page()
            || dd_is_dashboard_page()
            || is_front_page();

        if ( $load_dd_css ) {
            wp_enqueue_style(
                'dd-main',
                $uri . '/assets/css/dog-directory.css',
                ['petslist-main'],
                $v
            );
        }

        // Dashboard CSS (both user + admin)
        if ( dd_is_dashboard_page() ) {
            wp_enqueue_style(
                'dd-dashboard',
                $uri . '/assets/css/dashboard.css',
                ['dd-main'],
                $v
            );
        }

        // Stripe.js is replaced by inline PayPal script on checkout page


        // Main DD JS — load on all DD pages + auth pages!
        $load_dd_js = $load_dd_css 
            || dd_is_login_page()
            || dd_is_register_page()
            || dd_is_forgot_page();

        if ( ! $load_dd_js ) return;

        wp_enqueue_script(
            'dd-main',
            $uri . '/assets/js/dog-directory.js',
            ['jquery'],
            $v, true
        );

        // Dashboard JS (user + admin)
        if ( dd_is_dashboard_page() ) {
            wp_enqueue_script(
                'dd-dashboard',
                $uri . '/assets/js/dashboard.js',
                ['jquery'],
                $v, true
            );
            wp_enqueue_media();
        }

        // Localise
        wp_localize_script( 'dd-main', 'ddVars', [
            'ajaxUrl'      => admin_url('admin-ajax.php'),
            'nonces'       => [
                'dog'       => wp_create_nonce('dd_dog_nonce'),
                'auth'      => wp_create_nonce('dd_auth_nonce'),
                'dashboard' => wp_create_nonce('dd_dashboard_nonce'),
                'upload'    => wp_create_nonce('dd_upload_nonce'),
                'subscribe' => wp_create_nonce('dd_subscribe_nonce'),
                'cancel'    => wp_create_nonce('dd_cancel_sub_nonce'),
            ],
            'isLoggedIn'   => is_user_logged_in(),
            'isSubscriber' => Subscription::can_access_directory(),
            'loginUrl'     => dd_login_url(),
            'registerUrl'  => dd_register_url(),
            'pricingUrl'   => dd_pricing_url(),
            'dashboardUrl' => dd_dashboard_url(),
            'directoryUrl' => get_post_type_archive_link('dd_dog') ?: home_url('/dog-directory/'),
            'strings'      => [
                'confirmDelete'   => __('Are you sure you want to delete this dog? This cannot be undone.', 'petslist'),
                'confirmCancel'   => __('Cancel your subscription? You will lose access at the end of your billing period.', 'petslist'),
                'saving'          => __('Saving...', 'petslist'),
                'uploading'       => __('Uploading...', 'petslist'),
                'error'           => __('Something went wrong. Please try again.', 'petslist'),
                'subscribePrompt' => __('Subscribe to add dog profiles and access the full directory.', 'petslist'),
            ],
        ] );
    }

    public function enqueue_admin( $hook ) {
        global $post_type;
        if ( $post_type === 'dd_dog' || strpos($hook, 'dd_') !== false ) {
            $v = ( defined('WP_DEBUG') && WP_DEBUG ) ? time() : DD_VERSION;
            wp_enqueue_style('dd-admin', get_template_directory_uri() . '/assets/css/dog-directory-admin.css', [], $v);
            wp_enqueue_script('dd-admin', get_template_directory_uri() . '/assets/js/dog-directory-admin.js', ['jquery', 'wp-util'], $v, true);
            wp_localize_script('dd-admin', 'ddAdminVars', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('dd_admin_nonce'),
            ]);
        }
    }

    public function admin_inline_styles() {
        echo '<style>
        .dd-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; padding: 10px 0; }
        .dd-meta-field { display: flex; flex-direction: column; gap: 5px; }
        .dd-meta-field--full { grid-column: 1 / -1; }
        .dd-meta-field label { font-weight: 600; font-size: 13px; color: #1d2327; }
        .dd-meta-field input, .dd-meta-field select, .dd-meta-field textarea { width: 100%; border: 1px solid #ddd; border-radius: 4px; padding: 6px 10px; }
        .dd-status { padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: 600; }
        .dd-status--publish { background: #e6f9ef; color: #1a7e42; }
        .dd-status--pending { background: #fff8e1; color: #b45309; }
        .dd-status--draft { background: #f3f4f6; color: #6b7280; }
        </style>';
    }
}
