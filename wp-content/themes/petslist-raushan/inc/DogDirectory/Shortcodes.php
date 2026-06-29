<?php
/**
 * Dog Directory - Shortcodes
 * @package Petslist Dog Directory
 */

namespace RadiusTheme\Petslist\DogDirectory;

if ( ! defined( 'ABSPATH' ) ) exit;

class Shortcodes {

    protected static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_shortcode( 'dd_login',     [ $this, 'login_form' ] );
        add_shortcode( 'dd_register',  [ $this, 'register_form' ] );
        add_shortcode( 'dd_pricing',   [ $this, 'pricing_page' ] );
        add_shortcode( 'dd_checkout',  [ $this, 'checkout_page' ] );
        add_shortcode( 'dd_dashboard', [ $this, 'dashboard_page' ] );
        add_shortcode( 'dd_directory', [ $this, 'directory_shortcode' ] );
        add_shortcode( 'dd_forgot',    [ $this, 'forgot_form' ] );
        add_shortcode( 'dd_homepage',  [ $this, 'homepage_page' ] );
        // Handle redirect_to after login via WordPress native hooks
        add_action( 'wp_login', [ $this, 'handle_login_redirect' ], 10, 2 );
        add_filter( 'login_redirect', [ $this, 'filter_login_redirect' ], 10, 3 );
        // Handle logout redirect back to login page
        add_filter( 'logout_redirect', [ $this, 'logout_redirect' ], 10, 3 );
        // Filter nav menu items based on login status
    }

    public function login_form( $atts ) {
        if ( is_user_logged_in() ) {
            return '<p class="dd-notice">' . sprintf(__('You are already logged in. <a href="%s">Go to Dashboard</a>', 'petslist'), dd_dashboard_url()) . '</p>';
        }
        ob_start();
        echo $this->auth_styles();
        \RadiusTheme\Petslist\Helper::get_template_part('template-parts/auth/login-form');
        return ob_get_clean();
    }

    public function register_form( $atts ) {
        if ( is_user_logged_in() ) {
            return '<p class="dd-notice">' . sprintf(__('You are already logged in. <a href="%s">Go to Dashboard</a>', 'petslist'), dd_dashboard_url()) . '</p>';
        }
        ob_start();
        echo $this->auth_styles();
        \RadiusTheme\Petslist\Helper::get_template_part('template-parts/auth/register-form');
        return ob_get_clean();
    }

    public function pricing_page( $atts ) {
        ob_start();
        \RadiusTheme\Petslist\Helper::get_template_part('template-parts/subscription/pricing');
        return ob_get_clean();
    }

    public function checkout_page( $atts ) {
        ob_start();
        \RadiusTheme\Petslist\Helper::get_template_part('template-parts/subscription/checkout');
        return ob_get_clean();
    }

    public function dashboard_page( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '<p class="dd-notice">' . sprintf(__('Please <a href="%s">log in</a> to access your dashboard.', 'petslist'), dd_login_url()) . '</p>';
        }
        ob_start();
        // Route: admin gets admin dashboard, everyone else gets user dashboard
        if ( current_user_can( 'manage_options' ) ) {
            \RadiusTheme\Petslist\Helper::get_template_part('template-parts/dashboard/admin-main');
        } else {
            \RadiusTheme\Petslist\Helper::get_template_part('template-parts/dashboard/main');
        }
        return ob_get_clean();
    }

    public function directory_shortcode( $atts ) {
        ob_start();
        \RadiusTheme\Petslist\Helper::get_template_part('template-parts/dog/directory-shortcode');
        return ob_get_clean();
    }

    public function homepage_page( $atts ) {
        ob_start();
        \RadiusTheme\Petslist\Helper::get_template_part('template-parts/home');
        return ob_get_clean();
    }

    public function forgot_form( $atts ) {
        if ( is_user_logged_in() ) {
            return '<p class="dd-notice dd-notice--info">' . sprintf(
                __( 'You are logged in. <a href="%s">Go to Dashboard</a>.', 'petslist' ),
                esc_url( dd_dashboard_url() )
            ) . '</p>';
        }
        ob_start();
        echo $this->auth_styles();
        \RadiusTheme\Petslist\Helper::get_template_part('template-parts/auth/forgot-form');
        return ob_get_clean();
    }

    /**
     * After wp_login fires (native WP login form), redirect appropriately
     */
    public function handle_login_redirect( $user_login, $user ) {
        // Only relevant if someone uses the native wp-login.php (not our AJAX form)
        // Our AJAX form handles its own redirect
    }

    /**
     * After logout, send user to DD login page
     */
    public function logout_redirect( $redirect_to, $requested_redirect_to, $user ) {
        $login_page = get_option('dd_page_login');
        if ( $login_page ) {
            return get_permalink( $login_page );
        }
        return $redirect_to;
    }

    /**
     * Filter native WordPress login redirects to route users to the unified dashboard
     */
    public function filter_login_redirect( $redirect_to, $request, $user ) {
        if ( isset( $user->roles ) && is_array( $user->roles ) ) {
            return dd_dashboard_url();
        }
        return $redirect_to;
    }

    private function auth_styles() {
        return '<style>
.dd-auth-split-layout {
    display: flex !important;
    max-width: 960px !important;
    margin: 0 auto !important;
    background: #fff !important;
    border-radius: 20px !important;
    box-shadow: 0 10px 30px rgba(0,0,0,0.06) !important;
    overflow: hidden !important;
    border: 1px solid #eef2f6 !important;
    min-height: 580px !important;
}
.dd-auth-split-image {
    flex: 1.1 !important;
    background: linear-gradient(135deg, #070c3e 0%, #0d1656 100%) !important;
    position: relative !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 40px !important;
    color: #fff !important;
    min-height: 400px !important;
    overflow: hidden !important;
}
.dd-auth-split-overlay {
    position: absolute !important;
    top: 0; left: 0; right: 0; bottom: 0;
    background: linear-gradient(180deg, rgba(7, 12, 62, 0) 30%, rgba(7, 12, 62, 0.85) 100%) !important;
    display: flex !important;
    flex-direction: column !important;
    justify-content: flex-end !important;
    padding: 40px !important;
    z-index: 3 !important;
}
.dd-auth-split-overlay h3 {
    color: #fff !important;
    font-size: 28px !important;
    font-weight: 800 !important;
    margin-bottom: 12px !important;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3) !important;
    font-family: var(--petslist-heading-font), sans-serif !important;
}
.dd-auth-split-overlay p {
    color: rgba(255,255,255,0.9) !important;
    font-size: 15px !important;
    line-height: 1.6 !important;
    margin: 0 !important;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3) !important;
}
.dd-auth-split-form {
    flex: 1 !important;
    padding: 40px 40px !important;
    display: flex !important;
    flex-direction: column !important;
    justify-content: center !important;
}
.dd-auth-split-form h2 {
    font-size: 32px !important;
    font-weight: 800 !important;
    color: #070c3e !important;
    margin-bottom: 24px !important;
    text-align: left !important;
    font-family: var(--petslist-heading-font), sans-serif !important;
}
.dd-auth-split-form .rtcl-form-group {
    margin-bottom: 20px !important;
}
.dd-auth-split-form .rtcl-form-control {
    border-radius: 12px !important;
    padding: 12px 16px !important;
    height: auto !important;
    font-size: 14px !important;
    border: 1px solid #e2e8f0 !important;
}
.dd-auth-split-form .rtcl-btn {
    border-radius: 12px !important;
    padding: 12px 20px !important;
    font-weight: 700 !important;
    font-size: 15px !important;
    height: auto !important;
}
.content-area { padding-top: 15px !important; padding-bottom: 40px !important; }
@media (max-width: 768px) {
    .dd-auth-split-layout {
        flex-direction: column !important;
        max-width: 100% !important;
        margin: 0 15px !important;
        min-height: auto !important;
    }
    .dd-auth-split-image {
        display: none !important;
    }
    .dd-auth-split-form {
        padding: 40px 24px !important;
    }
}
</style>';
    }
}
