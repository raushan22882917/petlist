<?php
/**
 * Dog Directory — Email Notifications
 * Hooks into subscription events and dog submission events
 * @package Petslist Dog Directory
 */

namespace RadiusTheme\Petslist\DogDirectory;

if ( ! defined( 'ABSPATH' ) ) exit;

class Notifications {

    protected static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        // Subscription lifecycle
        add_action( 'dd_subscription_activated',  [ $this, 'on_subscription_activated' ],  10, 3 );
        add_action( 'dd_subscription_cancelled',  [ $this, 'on_subscription_cancelled' ],  10, 2 );
        add_action( 'dd_subscription_expired',    [ $this, 'on_subscription_expired' ],    10, 2 );

        // Dog listing
        add_action( 'save_post_dd_dog',           [ $this, 'on_dog_submitted' ],            10, 3 );
        add_action( 'transition_post_status',      [ $this, 'on_dog_approved' ],            10, 3 );

        // WP mail from name / email
        add_filter( 'wp_mail_from_name',  [ $this, 'mail_from_name' ] );
        add_filter( 'wp_mail_from',       [ $this, 'mail_from_email' ] );
        add_filter( 'wp_mail_content_type', [ $this, 'mail_content_type' ] );
    }

    /* ── Mail filters ─────────────────────────────────────── */

    public function mail_from_name( $name ) {
        $custom = get_option( 'dd_email_from_name' );
        return $custom ?: get_bloginfo( 'name' );
    }

    public function mail_from_email( $email ) {
        $custom = get_option( 'dd_email_from_email' );
        return $custom ?: $email;
    }

    public function mail_content_type() {
        return 'text/html';
    }

    /* ── Subscription activated ───────────────────────────── */

    public function on_subscription_activated( $user_id, $plan, $sub_id ) {
        $user = get_user_by( 'id', $user_id );
        if ( ! $user ) return;

        $features = json_decode( $plan->features ?? '[]', true ) ?: [];
        $feat_html = '';
        foreach ( $features as $f ) {
            $feat_html .= '<li style="padding:4px 0;color:#374151;">✅ ' . esc_html($f) . '</li>';
        }

        $expires = '';
        global $wpdb;
        $sub = $wpdb->get_row( $wpdb->prepare(
            "SELECT expires_at FROM {$wpdb->prefix}dd_subscriptions WHERE id = %d", $sub_id
        ) );
        if ( $sub ) {
            $expires = date( 'F j, Y', strtotime( $sub->expires_at ) );
        }

        $subject = sprintf( __( '🎉 Your %s subscription is active — Dog Directory', 'petslist' ), $plan->name );
        $body    = $this->wrap_email(
            '🎉 Subscription Activated!',
            '<p>Hi <strong>' . esc_html($user->display_name) . '</strong>,</p>
             <p>Your <strong>' . esc_html($plan->name) . '</strong> subscription to <strong>' . esc_html(get_bloginfo('name')) . '</strong> is now active.</p>
             ' . ( $expires ? '<p>✅ Active until: <strong>' . esc_html($expires) . '</strong></p>' : '' ) . '
             <p><strong>You now have access to:</strong></p>
             <ul style="margin:10px 0 20px;padding-left:0;list-style:none;">' . $feat_html . '</ul>
             <p><a href="' . esc_url(dd_dashboard_url()) . '" style="display:inline-block;background:#02c5bd;color:#fff;padding:12px 28px;border-radius:50px;text-decoration:none;font-weight:700;">Go to My Dashboard</a></p>
             <p style="margin-top:24px;">To add your first dog, visit your <a href="' . esc_url(dd_dashboard_url('add-dog')) . '">dashboard</a>.</p>'
        );

        wp_mail( $user->user_email, $subject, $body );

        // Notify admin
        $admin_body = $this->wrap_email(
            '📋 New Subscriber',
            '<p><strong>' . esc_html($user->display_name) . '</strong> (' . esc_html($user->user_email) . ') subscribed to the <strong>' . esc_html($plan->name) . '</strong> plan.</p>
             <p><a href="' . esc_url(admin_url('admin.php?page=dd-subscribers')) . '">View All Subscribers</a></p>'
        );
        wp_mail( get_option('admin_email'), '📋 New Dog Directory Subscriber: ' . $user->display_name, $admin_body );
    }

    /* ── Subscription cancelled ───────────────────────────── */

    public function on_subscription_cancelled( $user_id, $sub ) {
        $user = get_user_by( 'id', $user_id );
        if ( ! $user ) return;

        $subject = __( 'Your Dog Directory subscription has been cancelled', 'petslist' );
        $body    = $this->wrap_email(
            'Subscription Cancelled',
            '<p>Hi <strong>' . esc_html($user->display_name) . '</strong>,</p>
             <p>Your subscription to <strong>' . esc_html(get_bloginfo('name')) . '</strong> has been cancelled.</p>
             <p>You will continue to have access until the end of your current billing period.</p>
             <p>Changed your mind? <a href="' . esc_url(dd_pricing_url()) . '">Resubscribe here</a>.</p>'
        );
        wp_mail( $user->user_email, $subject, $body );
    }

    /* ── Subscription expired ─────────────────────────────── */

    public function on_subscription_expired( $user_id, $sub ) {
        $user = get_user_by( 'id', $user_id );
        if ( ! $user ) return;

        $subject = __( '⚠️ Your Dog Directory subscription has expired', 'petslist' );
        $body    = $this->wrap_email(
            '⚠️ Subscription Expired',
            '<p>Hi <strong>' . esc_html($user->display_name) . '</strong>,</p>
             <p>Your subscription to <strong>' . esc_html(get_bloginfo('name')) . '</strong> has expired. Your dog profiles are still saved but are no longer publicly visible.</p>
             <p>Renew today to restore full access and re-publish your listings.</p>
             <p><a href="' . esc_url(dd_pricing_url()) . '" style="display:inline-block;background:#02c5bd;color:#fff;padding:12px 28px;border-radius:50px;text-decoration:none;font-weight:700;">Renew Subscription</a></p>'
        );
        wp_mail( $user->user_email, $subject, $body );
    }

    /* ── Dog submitted (pending review) ──────────────────── */

    public function on_dog_submitted( $post_id, $post, $update ) {
        if ( $update ) return; // Only fire on first insert
        if ( $post->post_status !== 'pending' ) return;

        $author = get_user_by( 'id', $post->post_author );
        if ( ! $author ) return;

        $meta = get_post_meta( $post_id, '_dd_dog_meta', true ) ?: [];

        // Email to submitter
        $subject = sprintf( __( '✅ Dog "%s" submitted for review', 'petslist' ), $post->post_title );
        $body    = $this->wrap_email(
            '✅ Dog Submitted for Review',
            '<p>Hi <strong>' . esc_html($author->display_name) . '</strong>,</p>
             <p>Your dog <strong>"' . esc_html($post->post_title) . '"</strong> has been submitted and is pending review. It will go live within 24 hours.</p>
             <p>Breed: ' . esc_html($meta['breed'] ?? 'N/A') . '</p>
             <p><a href="' . esc_url(dd_dashboard_url('dogs')) . '">Manage your dogs</a></p>'
        );
        wp_mail( $author->user_email, $subject, $body );

        // Email to admin
        $admin_body = $this->wrap_email(
            '🐾 New Dog Listing for Review',
            '<p><strong>' . esc_html($author->display_name) . '</strong> submitted a new dog: <strong>' . esc_html($post->post_title) . '</strong></p>
             <p>Breed: ' . esc_html($meta['breed'] ?? 'N/A') . ' | Gender: ' . esc_html($meta['gender'] ?? 'N/A') . '</p>
             <p><a href="' . esc_url(get_edit_post_link($post_id, 'raw')) . '">Review in Admin</a></p>'
        );
        wp_mail( get_option('admin_email'), '🐾 New Dog Listing Pending Review: ' . $post->post_title, $admin_body );
    }

    /* ── Dog approved (publish transition) ───────────────── */

    public function on_dog_approved( $new_status, $old_status, $post ) {
        if ( $post->post_type !== 'dd_dog' ) return;
        if ( $new_status !== 'publish' || $old_status === 'publish' ) return;

        $author = get_user_by( 'id', $post->post_author );
        if ( ! $author ) return;

        $subject = sprintf( __( '🎉 Your dog "%s" is now live!', 'petslist' ), $post->post_title );
        $body    = $this->wrap_email(
            '🎉 Dog Profile Published!',
            '<p>Hi <strong>' . esc_html($author->display_name) . '</strong>,</p>
             <p>Great news! Your dog profile <strong>"' . esc_html($post->post_title) . '"</strong> has been approved and is now live on the directory.</p>
             <p><a href="' . esc_url(get_permalink($post->ID)) . '" style="display:inline-block;background:#02c5bd;color:#fff;padding:12px 28px;border-radius:50px;text-decoration:none;font-weight:700;">View Your Dog\'s Profile</a></p>'
        );
        wp_mail( $author->user_email, $subject, $body );
    }

    /* ── HTML email wrapper ───────────────────────────────── */

    private function wrap_email( $heading, $content ) {
        $site_name = esc_html( get_bloginfo('name') );
        $site_url  = esc_url( home_url('/') );
        $primary   = '#02c5bd';

        return '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . $heading . '</title></head>
<body style="margin:0;padding:0;background:#f6f9f9;font-family:\'Plus Jakarta Sans\',Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f6f9f9;padding:30px 0;">
    <tr><td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.08);">
        <!-- Header -->
        <tr><td style="background:' . $primary . ';padding:28px 36px;text-align:center;">
          <a href="' . $site_url . '" style="color:#fff;font-size:22px;font-weight:800;text-decoration:none;">🐾 ' . $site_name . '</a>
        </td></tr>
        <!-- Body -->
        <tr><td style="padding:36px;">
          <h2 style="color:#070C3E;font-size:22px;margin:0 0 20px;">' . $heading . '</h2>
          <div style="color:#374151;font-size:15px;line-height:1.7;">' . $content . '</div>
        </td></tr>
        <!-- Footer -->
        <tr><td style="background:#f6f9f9;padding:20px 36px;text-align:center;border-top:1px solid #e1e9e9;">
          <p style="color:#9ca3af;font-size:12px;margin:0;">
            You received this email because you have an account on <a href="' . $site_url . '" style="color:' . $primary . ';">' . $site_name . '</a>.<br>
            <a href="' . $site_url . '" style="color:#9ca3af;">Visit Site</a> &nbsp;|&nbsp; <a href="' . esc_url(dd_dashboard_url()) . '" style="color:#9ca3af;">My Account</a>
          </p>
        </td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>';
    }
}
