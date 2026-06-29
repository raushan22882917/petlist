<?php
/**
 * Dog Directory - Global Helper Functions
 * @package Petslist Dog Directory
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// -------------------------------------------------------
// URL HELPERS
// -------------------------------------------------------

function dd_page_url( $option_key, $fallback_slug = '' ) {
    $page_id = get_option( $option_key );
    if ( $page_id ) return get_permalink( $page_id );
    return home_url( '/' . trim($fallback_slug, '/') . '/' );
}

function dd_login_url()     { return dd_page_url('dd_page_login',    'login'); }
function dd_register_url()  { return dd_page_url('dd_page_register', 'register'); }
function dd_pricing_url( $plan = '' ) {
    $url = dd_page_url('dd_page_pricing', 'dog-directory-plans');
    return $plan ? add_query_arg('plan', $plan, $url) : $url;
}
function dd_checkout_url( $plan = '' ) {
    $url = dd_page_url('dd_page_checkout', 'dog-checkout');
    return $plan ? add_query_arg('plan', $plan, $url) : $url;
}
function dd_dashboard_url( $tab = '' ) {
    $url = dd_page_url('dd_page_dashboard', 'my-account');
    return $tab ? add_query_arg('tab', $tab, $url) : $url;
}
function dd_dog_directory_url() {
    return get_post_type_archive_link('dd_dog') ?: home_url('/dog-directory/');
}

// -------------------------------------------------------
// PAGE DETECTION
// -------------------------------------------------------

function dd_is_page( $option_key ) {
    if ( ! is_page() ) return false;
    return (int) get_option($option_key) === (int) get_the_ID();
}

function dd_is_login_page()     { return dd_is_page('dd_page_login'); }
function dd_is_register_page()  { return dd_is_page('dd_page_register'); }
function dd_is_pricing_page()   { return dd_is_page('dd_page_pricing'); }
function dd_is_checkout_page()  { return dd_is_page('dd_page_checkout'); }
function dd_is_dashboard_page() { return dd_is_page('dd_page_dashboard'); }
function dd_is_forgot_page()    { return dd_is_page('dd_page_forgot'); }

// -------------------------------------------------------
// DOG DATA HELPERS
// -------------------------------------------------------

function dd_get_dog_meta( $post_id, $key = '' ) {
    $meta = get_post_meta( $post_id, '_dd_dog_meta', true ) ?: [];
    return $key ? ( $meta[$key] ?? '' ) : $meta;
}

function dd_get_dog_health( $post_id, $key = '' ) {
    $health = get_post_meta( $post_id, '_dd_dog_health', true ) ?: [];
    return $key ? ( $health[$key] ?? '' ) : $health;
}

function dd_get_dog_age( $dob ) {
    if ( empty($dob) ) return '';
    try {
        $birth = new DateTime($dob);
        $now   = new DateTime();
        $diff  = $now->diff($birth);
        if ( $diff->y > 0 ) {
            return $diff->y . ' ' . _n('year', 'years', $diff->y, 'petslist');
        }
        return $diff->m . ' ' . _n('month', 'months', $diff->m, 'petslist');
    } catch ( Exception $e ) {
        return '';
    }
}

function dd_get_front_photo_url( $post_id, $size = 'large' ) {
    $id = get_post_meta($post_id, '_dd_front_photo', true);
    if ( $id ) return wp_get_attachment_image_url($id, $size);
    return get_the_post_thumbnail_url($post_id, $size) ?: dd_placeholder_image();
}

function dd_get_side_photo_url( $post_id, $size = 'large' ) {
    $id = get_post_meta($post_id, '_dd_side_photo', true);
    if ( $id ) return wp_get_attachment_image_url($id, $size);
    return dd_placeholder_image();
}

function dd_placeholder_image() {
    return get_template_directory_uri() . '/assets/img/dog-placeholder.svg';
}

// -------------------------------------------------------
// USER DOGS
// -------------------------------------------------------

function dd_get_user_dogs( $user_id = 0, $status = 'any', $per_page = -1 ) {
    if ( ! $user_id ) $user_id = get_current_user_id();
    return get_posts([
        'post_type'      => 'dd_dog',
        'post_status'    => $status,
        'author'         => $user_id,
        'posts_per_page' => $per_page,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);
}

function dd_get_user_dog_count( $user_id = 0 ) {
    if ( ! $user_id ) $user_id = get_current_user_id();
    $query = new WP_Query([
        'post_type'   => 'dd_dog',
        'post_status' => ['publish','pending','draft'],
        'author'      => $user_id,
        'fields'      => 'ids',
    ]);
    return $query->found_posts;
}

// -------------------------------------------------------
// SUBSCRIPTION DISPLAY
// -------------------------------------------------------

function dd_subscription_badge( $user_id = 0 ) {
    $sub = \RadiusTheme\Petslist\DogDirectory\Subscription::get_user_subscription($user_id);
    if ( ! $sub ) {
        return '<span class="dd-badge dd-badge--inactive">' . __('No Subscription', 'petslist') . '</span>';
    }
    $expires = human_time_diff(strtotime($sub->expires_at), time());
    $label   = $sub->status === 'active'
        ? sprintf(__('Active — %s (%s left)', 'petslist'), esc_html($sub->plan_name), $expires)
        : ucfirst($sub->status);
    return '<span class="dd-badge dd-badge--' . esc_attr($sub->status) . '">' . $label . '</span>';
}

// -------------------------------------------------------
// BREED LIST (for search filters)
// -------------------------------------------------------

function dd_get_breeds( $limit = 0 ) {
    $args = [
        'taxonomy'   => 'dd_breed',
        'orderby'    => 'name',
        'hide_empty' => false,
    ];
    if ( $limit ) $args['number'] = $limit;
    return get_terms($args);
}

function dd_get_locations() {
    return get_terms(['taxonomy' => 'dd_location', 'orderby' => 'name', 'hide_empty' => false]);
}

// -------------------------------------------------------
// ACCESS GATES
// -------------------------------------------------------

function dd_require_login( $redirect = '' ) {
    if ( ! is_user_logged_in() ) {
        $url = $redirect ?: dd_login_url();
        wp_safe_redirect( add_query_arg('redirect_to', urlencode(get_permalink()), $url) );
        exit;
    }
}

function dd_require_subscription( $redirect = '' ) {
    dd_require_login();
    if ( ! \RadiusTheme\Petslist\DogDirectory\Subscription::can_access_directory() ) {
        wp_safe_redirect( $redirect ?: dd_pricing_url() );
        exit;
    }
}

// -------------------------------------------------------
// STRIPE HELPERS
// -------------------------------------------------------

function dd_stripe_publishable_key() {
    return get_option('dd_stripe_publishable_key', '');
}

function dd_stripe_secret_key() {
    return get_option('dd_stripe_secret_key', '');
}

function dd_stripe_webhook_secret() {
    return get_option('dd_stripe_webhook_secret', '');
}

// -------------------------------------------------------
// MISC
// -------------------------------------------------------

function dd_format_price( $amount, $currency = 'USD' ) {
    return '$' . number_format($amount, 2);
}

function dd_gender_icon( $gender ) {
    if ( $gender === 'Male' ) return '<i class="icon-pl-account dd-icon--male" title="Male"></i>';
    if ( $gender === 'Female' ) return '<i class="icon-pl-account-fill dd-icon--female" title="Female"></i>';
    return '';
}
