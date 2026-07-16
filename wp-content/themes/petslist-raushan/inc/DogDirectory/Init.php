<?php
/**
 * Dog Directory - Bootstrap
 * @package Petslist Dog Directory
 */

namespace RadiusTheme\Petslist\DogDirectory;

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'DD_VERSION', '1.0.0' );

class Init {

    protected static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_helpers();
        $this->boot_classes();
        $this->register_shortcodes();
        add_action( 'after_switch_theme', [ $this, 'on_activation' ] );
        add_filter( 'body_class', [ $this, 'body_classes' ] );
        add_filter( 'get_avatar', [ $this, 'custom_user_avatar' ], 10, 5 );
        add_filter( 'get_avatar_url', [ $this, 'custom_user_avatar_url' ], 10, 3 );

        // Transition all existing pending review dogs to publish status
        global $wpdb;
        $wpdb->query( "UPDATE $wpdb->posts SET post_status = 'publish' WHERE post_type = 'dd_dog' AND post_status = 'pending'" );
    }

    private function load_helpers() {
        require_once get_template_directory() . '/inc/DogDirectory/Helpers.php';
    }

    private function boot_classes() {
        Roles::instance();
        DogCPT::instance();
        Subscription::instance();
        Stripe::instance();
        PayPal::instance();
        Ajax::instance();
        Scripts::instance();
        Shortcodes::instance();
        Notifications::instance();
        AccessControl::instance();
        if ( is_admin() ) {
            Admin::instance();
        }
    }

    private function register_shortcodes() {
        // Shortcodes are registered inside the Shortcodes class
    }

    public function on_activation() {
        // Flush rewrite rules after theme switch
        flush_rewrite_rules();
        // Trigger DB table creation
        Subscription::instance()->create_subscription_tables();
    }

    public function body_classes( $classes ) {
        if ( is_singular('dd_dog') ) {
            $classes[] = 'dd-single-dog';
        }
        if ( is_post_type_archive('dd_dog') ) {
            $classes[] = 'dd-dog-archive';
        }
        if ( Subscription::can_access_directory() ) {
            $classes[] = 'dd-subscriber';
        }
        if ( dd_is_dashboard_page() ) {
            $classes[] = 'dd-dashboard-page';
        }
        if ( dd_is_pricing_page() ) {
            $classes[] = 'dd-pricing-page';
        }
        if ( dd_is_login_page() || dd_is_register_page() ) {
            $classes[] = 'dd-auth-page';
        }
        return $classes;
    }

    /**
     * Override avatar URL with custom uploaded profile picture if set.
     */
    public function custom_user_avatar_url( $url, $id_or_email, $args ) {
        $user_id = 0;
        if ( is_numeric( $id_or_email ) ) {
            $user_id = absint( $id_or_email );
        } elseif ( is_string( $id_or_email ) && ( $user = get_user_by( 'email', $id_or_email ) ) ) {
            $user_id = $user->ID;
        } elseif ( is_object( $id_or_email ) ) {
            if ( isset( $id_or_email->user_id ) && $id_or_email->user_id ) {
                $user_id = absint( $id_or_email->user_id );
            } elseif ( isset( $id_or_email->ID ) && $id_or_email->ID ) {
                $user_id = absint( $id_or_email->ID );
            }
        }

        if ( $user_id ) {
            $pp_id = absint( get_user_meta( $user_id, '_rtcl_pp_id', true ) );
            if ( $pp_id ) {
                $custom_url = wp_get_attachment_image_url( $pp_id, 'thumbnail' );
                if ( $custom_url ) {
                    return $custom_url;
                }
            }
        }

        return $url;
    }

    /**
     * Override avatar HTML output.
     */
    public function custom_user_avatar( $avatar, $id_or_email, $size, $default, $alt ) {
        $user_id = 0;
        if ( is_numeric( $id_or_email ) ) {
            $user_id = absint( $id_or_email );
        } elseif ( is_string( $id_or_email ) && ( $user = get_user_by( 'email', $id_or_email ) ) ) {
            $user_id = $user->ID;
        } elseif ( is_object( $id_or_email ) ) {
            if ( isset( $id_or_email->user_id ) && $id_or_email->user_id ) {
                $user_id = absint( $id_or_email->user_id );
            } elseif ( isset( $id_or_email->ID ) && $id_or_email->ID ) {
                $user_id = absint( $id_or_email->ID );
            }
        }

        if ( $user_id ) {
            $pp_id = absint( get_user_meta( $user_id, '_rtcl_pp_id', true ) );
            if ( $pp_id ) {
                $custom_url = wp_get_attachment_image_url( $pp_id, 'thumbnail' );
                if ( $custom_url ) {
                    $avatar = sprintf(
                        '<img alt="%1$s" src="%2$s" class="avatar avatar-%3$d photo" height="%3$d" width="%3$d" />',
                        esc_attr( $alt ),
                        esc_url( $custom_url ),
                        absint( $size )
                    );
                }
            }
        }

        return $avatar;
    }
}
