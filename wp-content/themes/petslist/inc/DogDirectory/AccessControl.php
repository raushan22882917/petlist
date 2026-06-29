<?php
/**
 * Dog Directory — Access Control & Page Gating
 *
 * Rules:
 *  - Visitor (not logged in):  Browse directory (limited view), see plans, register, login
 *  - Logged-in non-subscriber: Browse directory (limited), access dashboard (no-sub view), see plans
 *  - Subscriber:               Full directory, full dashboard, add/edit/delete own dogs
 *  - Admin:                    Everything
 *
 * @package Petslist Dog Directory
 */

namespace RadiusTheme\Petslist\DogDirectory;

if ( ! defined( 'ABSPATH' ) ) exit;

class AccessControl {

    protected static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        // Front-end template redirect — gate pages before they render
        add_action( 'template_redirect', [ $this, 'gate_pages' ], 5 );

        // Filter page content — add access banners or restrict content inline
        add_filter( 'the_content', [ $this, 'filter_dog_content' ] );

        // Add access gate to single dog profiles when not subscribed
        add_action( 'wp_head', [ $this, 'add_access_meta' ] );
    }

    /**
     * Gate pages via redirect or wp_die
     */
    public function gate_pages() {
        // Admin always passes
        if ( current_user_can( 'manage_options' ) ) return;

        $current_id = get_the_ID();

        /* ── Dashboard page: must be logged in ── */
        if ( dd_is_dashboard_page() ) {
            if ( ! is_user_logged_in() ) {
                wp_safe_redirect( add_query_arg(
                    'redirect_to', urlencode( dd_dashboard_url() ),
                    dd_login_url()
                ) );
                exit;
            }
            // Logged in but no sub → show no-sub view (handled inside dashboard template)
            return;
        }

        /* ── Checkout page: must be logged in ── */
        if ( dd_is_checkout_page() ) {
            if ( ! is_user_logged_in() ) {
                $plan = sanitize_text_field( $_GET['plan'] ?? 'monthly' );
                wp_safe_redirect( add_query_arg(
                    'redirect_to', urlencode( dd_checkout_url( $plan ) ),
                    dd_login_url()
                ) );
                exit;
            }
            return;
        }

        /* ── Login page: redirect logged-in users ── */
        if ( dd_is_login_page() && is_user_logged_in() ) {
            $redirect = sanitize_url( $_GET['redirect_to'] ?? '' );
            wp_safe_redirect( $redirect ?: dd_dashboard_url() );
            exit;
        }

        /* ── Register page: redirect logged-in users ── */
        if ( dd_is_register_page() && is_user_logged_in() ) {
            wp_safe_redirect( dd_dashboard_url() );
            exit;
        }
        /* ── Add Dog form (via dashboard tab): subscription required ── */
        if ( dd_is_dashboard_page() && ( $_GET['tab'] ?? '' ) === 'add-dog' ) {
            if ( is_user_logged_in() && ! Subscription::can_access_directory() ) {
                wp_safe_redirect( dd_pricing_url() );
                exit;
            }
        }
    }

    /**
     * Filter the_content for dog single pages
     * — Show teaser for non-subscribers, full content for subscribers
     */
    public function filter_dog_content( $content ) {
        if ( ! is_singular( 'dd_dog' ) ) return $content;
        if ( current_user_can( 'manage_options' ) ) return $content;
        // Content is already gated inside single-dog.php template
        // This filter is a safety net for excerpt / REST API use
        if ( ! Subscription::can_access_directory() ) {
            // Strip contact / sensitive info from any content output
            $content = wp_strip_all_tags( $content );
            $content = wp_trim_words( $content, 30 );
            $content .= ' <a href="' . esc_url( dd_pricing_url() ) . '" class="dd-btn dd-btn--primary dd-btn--sm">'
                      . __( 'Subscribe to read more', 'petslist' ) . '</a>';
        }
        return $content;
    }

    /**
     * Add noindex for pending dog profiles
     */
    public function add_access_meta() {
        if ( is_singular( 'dd_dog' ) && get_post_status() !== 'publish' ) {
            echo '<meta name="robots" content="noindex,nofollow">' . "\n";
        }
    }

    /* ── Static gate helpers (for use in templates) ── */

    /**
     * Render an access gate card and exit rendering
     * Call this at the top of any template that needs protection.
     */
    public static function require_login_gate( $message = '', $redirect_after_login = '' ) {
        if ( is_user_logged_in() ) return; // passes

        if ( ! $redirect_after_login ) {
            $redirect_after_login = get_permalink() ?: dd_dashboard_url();
        }

        $login_url = add_query_arg( 'redirect_to', urlencode( $redirect_after_login ), dd_login_url() );

        ?>
        <div class="dd-access-gate">
            <div class="dd-access-gate__card">
                <div class="dd-access-gate__icon">🔐</div>
                <h2 class="dd-access-gate__title"><?php _e( 'Login Required', 'petslist' ); ?></h2>
                <p class="dd-access-gate__text"><?php echo $message ?: __( 'Please log in or create an account to access this page.', 'petslist' ); ?></p>
                <div class="dd-access-gate__actions">
                    <a href="<?php echo esc_url( $login_url ); ?>" class="dd-btn dd-btn--primary dd-btn--lg">
                        <i class="icon-pl-account"></i> <?php _e( 'Log In', 'petslist' ); ?>
                    </a>
                    <a href="<?php echo esc_url( dd_register_url() ); ?>" class="dd-btn dd-btn--outline dd-btn--lg">
                        <?php _e( 'Create Account', 'petslist' ); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render a subscription-required gate card.
     * Call this in any template that requires an active subscription.
     */
    public static function require_subscription_gate( $message = '' ) {
        if ( Subscription::can_access_directory() ) return; // passes

        if ( ! is_user_logged_in() ) {
            self::require_login_gate();
            return;
        }
        ?>
        <div class="dd-access-gate">
            <div class="dd-access-gate__card">
                <div class="dd-access-gate__icon">⭐</div>
                <h2 class="dd-access-gate__title"><?php _e( 'Subscription Required', 'petslist' ); ?></h2>
                <p class="dd-access-gate__text">
                    <?php echo $message ?: __( 'This feature is available to subscribers. Choose a plan to get full access to the Dog Directory — including contact details, pedigrees, health records, and unlimited dog profiles.', 'petslist' ); ?>
                </p>
                <div class="dd-access-gate__actions">
                    <a href="<?php echo esc_url( dd_pricing_url() ); ?>" class="dd-btn dd-btn--primary dd-btn--lg">
                        <i class="icon-pl-flash"></i> <?php _e( 'View Plans & Pricing', 'petslist' ); ?>
                    </a>
                    <a href="<?php echo esc_url( dd_dashboard_url() ); ?>" class="dd-btn dd-btn--ghost">
                        <?php _e( 'My Account', 'petslist' ); ?>
                    </a>
                </div>
                <!-- Mini plan preview -->
                <?php
                $plans = Subscription::get_plans();
                if ( $plans ) :
                ?>
                <div class="dd-access-gate__plans">
                    <?php foreach ( $plans as $plan ) :
                        $period = $plan->duration <= 31 ? '/mo' : ( $plan->duration <= 366 ? '/yr' : ' once' );
                    ?>
                    <div class="dd-access-gate__plan">
                        <span class="dd-access-gate__plan-name"><?php echo esc_html($plan->name); ?></span>
                        <span class="dd-access-gate__plan-price">$<?php echo number_format($plan->price,2); ?><small><?php echo $period; ?></small></span>
                        <a href="<?php echo esc_url(dd_checkout_url($plan->slug)); ?>" class="dd-btn dd-btn--outline dd-btn--sm">
                            <?php _e('Choose', 'petslist'); ?>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <style>
                .dd-access-gate__plans{display:flex;gap:12px;margin-top:24px;justify-content:center;flex-wrap:wrap}
                .dd-access-gate__plan{background:#f6f9f9;border:1px solid #e1e9e9;border-radius:10px;padding:14px 18px;text-align:center;display:flex;flex-direction:column;gap:6px;min-width:130px}
                .dd-access-gate__plan-name{font-size:13px;font-weight:700;color:#070C3E}
                .dd-access-gate__plan-price{font-size:20px;font-weight:900;color:#02c5bd}
                .dd-access-gate__plan-price small{font-size:11px;font-weight:400;color:#515167}
                </style>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Check and return access level for current user
     * Returns: 'admin' | 'subscriber' | 'user' | 'visitor'
     */
    public static function get_access_level() {
        if ( current_user_can( 'manage_options' ) ) return 'admin';
        if ( ! is_user_logged_in() ) return 'visitor';
        if ( Subscription::can_access_directory() ) return 'subscriber';
        return 'user'; // logged in, no subscription
    }

    /**
     * Returns what a non-subscriber can see on a dog card (used in templates)
     */
    public static function visible_dog_fields() {
        return [ 'breed', 'gender', 'color', 'dob', 'weight', 'country', 'city' ];
    }

    /**
     * Returns subscriber-only fields on a dog profile
     */
    public static function subscriber_dog_fields() {
        return [ 'registration_no', 'contact_phone', 'contact_email', 'contact_website' ];
    }
}
