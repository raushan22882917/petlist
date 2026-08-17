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

        // WP mail from name / email & SMTP configuration
        add_filter( 'wp_mail_from_name',  [ $this, 'mail_from_name' ] );
        add_filter( 'wp_mail_from',       [ $this, 'mail_from_email' ] );
        add_filter( 'wp_mail_content_type', [ $this, 'mail_content_type' ] );
        add_action( 'phpmailer_init',     [ $this, 'configure_smtp' ] );
        add_filter( 'retrieve_password_notification_email', [ $this, 'format_retrieve_password_email' ], 10, 4 );
    }

    /* ── Mail filters & SMTP ───────────────────────────────── */

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

    /**
     * Configure PHPMailer to use SMTP if enabled in Admin Settings
     */
    public function configure_smtp( $phpmailer ) {
        if ( ! get_option( 'dd_smtp_enable' ) ) {
            return;
        }

        $host = get_option( 'dd_smtp_host' );
        if ( empty( $host ) ) {
            return;
        }

        $phpmailer->isSMTP();
        $phpmailer->Host       = $host;
        $phpmailer->Port       = (int) get_option( 'dd_smtp_port', 587 );
        $phpmailer->SMTPAuth   = (bool) get_option( 'dd_smtp_auth', 1 );

        if ( $phpmailer->SMTPAuth ) {
            $phpmailer->Username = get_option( 'dd_smtp_username' );
            $phpmailer->Password = get_option( 'dd_smtp_password' );
        }

        $encryption = get_option( 'dd_smtp_encryption', 'tls' );
        if ( 'tls' === $encryption || 'ssl' === $encryption ) {
            $phpmailer->SMTPSecure = $encryption;
        } else {
            $phpmailer->SMTPSecure = '';
        }

        $from_name  = get_option( 'dd_email_from_name', get_bloginfo( 'name' ) );
        $from_email = get_option( 'dd_email_from_email', get_option( 'admin_email' ) );
        if ( ! empty( $from_email ) ) {
            $phpmailer->From     = $from_email;
            $phpmailer->FromName = $from_name;
        }
    }

    /**
     * Format WordPress retrieve password email into HTML
     */
    public function format_retrieve_password_email( $defaults, $key, $user_login, $user_data ) {
        $user = get_user_by( 'login', $user_login );
        if ( ! $user ) {
            $user = $user_data;
        }

        $reset_url = add_query_arg([
            'action' => 'rp',
            'key'    => $key,
            'login'  => rawurlencode( $user_login ),
        ], wp_lostpassword_url());

        $defaults['subject'] = sprintf( __( '🔐 Password Reset Request — %s', 'petslist' ), get_bloginfo( 'name' ) );
        $defaults['message'] = $this->get_formatted_password_reset_email( $user, $reset_url );
        $defaults['headers'] = [ 'Content-Type: text/html; charset=UTF-8' ];

        return $defaults;
    }

    /**
     * Generate HTML Formatted Password Reset Email
     */
    public function get_formatted_password_reset_email( $user, $reset_url ) {
        $user_name = $user ? ( $user->display_name ?: $user->user_login ) : __( 'Valued User', 'petslist' );

        $content = '
            <p style="font-size:16px;margin-bottom:16px;">Hi <strong>' . esc_html( $user_name ) . '</strong>,</p>
            <p style="margin-bottom:20px;">We received a request to reset your password for your <strong>' . esc_html( get_bloginfo( 'name' ) ) . '</strong> account.</p>
            <p style="margin-bottom:24px;text-align:center;">
                <a href="' . esc_url( $reset_url ) . '" style="display:inline-block;background:#02c5bd;color:#ffffff;padding:14px 32px;border-radius:50px;text-decoration:none;font-weight:700;font-size:15px;box-shadow:0 4px 12px rgba(2,197,189,0.3);">
                    🔑 Reset My Password
                </a>
            </p>
            <p style="font-size:13px;color:#6b7280;margin-bottom:16px;">Or copy and paste this link into your browser address bar:<br>
                <a href="' . esc_url( $reset_url ) . '" style="color:#02c5bd;word-break:break-all;">' . esc_url( $reset_url ) . '</a>
            </p>
            <div style="background:#fef3c7;border-left:4px solid #f59e0b;padding:12px 16px;border-radius:6px;margin-top:20px;">
                <p style="margin:0;font-size:13px;color:#92400e;">⚠️ <strong>Note:</strong> This link is valid for 24 hours. If you did not request a password reset, you can safely ignore this email — your account password will remain unchanged.</p>
            </div>
        ';

        return $this->wrap_email( __( 'Password Reset Request', 'petslist' ), $content );
    }

    /* ── Subscription activated (Sends HTML Invoice Receipt via SMTP) ── */

    public function on_subscription_activated( $user_id, $plan, $sub_id ) {
        $user = get_user_by( 'id', $user_id );
        if ( ! $user ) return;

        global $wpdb;
        $sub = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dd_subscriptions WHERE id = %d", $sub_id
        ) );

        $invoice_html = $this->generate_subscription_invoice_html( $user, $plan, $sub );

        $inv_code = sprintf( 'INV-%05d', $sub_id );
        $subject  = sprintf( __( '🧾 Payment Receipt & Invoice %s — %s Subscription', 'petslist' ), $inv_code, $plan->name );
        $headers  = [ 'Content-Type: text/html; charset=UTF-8' ];

        // Send full HTML Invoice Receipt to User
        wp_mail( $user->user_email, $subject, $invoice_html, $headers );

        // Send itemized order notice to Admin
        $admin_subject = sprintf( __( '📋 New Paid Subscription: %s (%s Plan)', 'petslist' ), $user->display_name, $plan->name );
        wp_mail( get_option( 'admin_email' ), $admin_subject, $invoice_html, $headers );
    }

    /* ── Subscription cancelled ───────────────────────────── */

    public function on_subscription_cancelled( $user_id, $sub ) {
        $user = get_user_by( 'id', $user_id );
        if ( ! $user ) return;

        $subject = sprintf( __( '🚫 Your Subscription Has Been Cancelled — %s', 'petslist' ), get_bloginfo( 'name' ) );
        $content = '
            <p style="font-size:16px;margin-bottom:16px;">Hi <strong>' . esc_html( $user->display_name ) . '</strong>,</p>
            <p style="margin-bottom:16px;">Your subscription to <strong>' . esc_html( get_bloginfo( 'name' ) ) . '</strong> has been cancelled.</p>
            <div style="background:#fff7ed;border-left:4px solid #f97316;padding:14px 18px;border-radius:6px;margin:20px 0;">
                <p style="margin:0;font-size:14px;color:#9a3412;">ℹ️ You will continue to have access to your subscription privileges until the end of your current billing period.</p>
            </div>
            <p style="margin-top:24px;text-align:center;">
                <a href="' . esc_url( dd_pricing_url() ) . '" style="display:inline-block;background:#02c5bd;color:#ffffff;padding:12px 30px;border-radius:50px;text-decoration:none;font-weight:700;">
                    Resubscribe Anytime
                </a>
            </p>
        ';
        $body    = $this->wrap_email( __( 'Subscription Cancelled', 'petslist' ), $content );
        $headers = [ 'Content-Type: text/html; charset=UTF-8' ];
        wp_mail( $user->user_email, $subject, $body, $headers );
    }

    /* ── Subscription expired ─────────────────────────────── */

    public function on_subscription_expired( $user_id, $sub ) {
        $user = get_user_by( 'id', $user_id );
        if ( ! $user ) return;

        $subject = sprintf( __( '⚠️ Subscription Expired — Renew Your Access on %s', 'petslist' ), get_bloginfo( 'name' ) );
        $content = '
            <p style="font-size:16px;margin-bottom:16px;">Hi <strong>' . esc_html( $user->display_name ) . '</strong>,</p>
            <p style="margin-bottom:16px;">Your <strong>Dog Directory</strong> subscription on <strong>' . esc_html( get_bloginfo( 'name' ) ) . '</strong> has expired. Your existing listings are saved safely in your account but are currently hidden from public search.</p>
            <div style="background:#fef2f2;border-left:4px solid #ef4444;padding:14px 18px;border-radius:6px;margin:20px 0;">
                <p style="margin:0;font-size:14px;color:#991b1b;">⚠️ Renew your subscription plan today to restore public directory visibility and list new dogs.</p>
            </div>
            <p style="margin-top:24px;text-align:center;">
                <a href="' . esc_url( dd_pricing_url() ) . '" style="display:inline-block;background:#02c5bd;color:#ffffff;padding:14px 32px;border-radius:50px;text-decoration:none;font-weight:700;box-shadow:0 4px 14px rgba(2,197,189,0.35);">
                    🔄 Renew Subscription Plan
                </a>
            </p>
        ';
        $body    = $this->wrap_email( __( 'Subscription Expired', 'petslist' ), $content );
        $headers = [ 'Content-Type: text/html; charset=UTF-8' ];
        wp_mail( $user->user_email, $subject, $body, $headers );
    }

    /**
     * Generate Full Itemized HTML Subscription Invoice Receipt Email Body
     */
    public function generate_subscription_invoice_html( $user, $plan, $sub, $payment = null ) {
        global $wpdb;
        $site_name   = get_bloginfo( 'name' );
        $site_url    = home_url( '/' );
        $user_name   = $user->display_name ?: $user->user_login;
        $user_email  = $user->user_email;
        $sub_id      = is_object( $sub ) ? $sub->id : (int) $sub;

        if ( ! $payment && $sub_id ) {
            $pay_table = $wpdb->prefix . 'dd_payments';
            $payment   = $wpdb->get_row( $wpdb->prepare(
                "SELECT * FROM {$pay_table} WHERE subscription_id = %d ORDER BY id DESC LIMIT 1", $sub_id
            ) );
        }

        $inv_code   = $payment ? sprintf( 'INV-%05d', $payment->id ) : sprintf( 'INV-%05d', $sub_id );
        $paid_date  = $payment ? date( 'M j, Y H:i', strtotime( $payment->created_at ) ) : date( 'M j, Y H:i' );
        $txn_id     = $payment ? ( $payment->transaction_id ?: $payment->stripe_pi_id ?: 'N/A' ) : 'DD-SUB-' . $sub_id;
        $pay_method = $payment ? strtoupper( $payment->payment_method ?: 'Stripe' ) : 'Credit Card / Online';
        $amount     = $payment ? number_format( (float) $payment->amount, 2 ) : number_format( (float) $plan->price, 2 );

        $starts_at  = is_object( $sub ) && isset( $sub->starts_at ) ? date( 'M j, Y', strtotime( $sub->starts_at ) ) : date( 'M j, Y' );
        $expires_at = is_object( $sub ) && isset( $sub->expires_at ) ? date( 'M j, Y', strtotime( $sub->expires_at ) ) : date( 'M j, Y', strtotime( "+{$plan->duration} days" ) );

        $features      = json_decode( $plan->features ?? '[]', true ) ?: [];
        $features_list = '';
        foreach ( $features as $f ) {
            $features_list .= '<li style="padding:4px 0;color:#15803d;">✅ ' . esc_html( $f ) . '</li>';
        }
        if ( ! $features_list ) {
            $features_list = '<li style="padding:4px 0;color:#15803d;">✅ Full Access to Dog Directory & Listing Tools</li>';
        }

        $dashboard_url = esc_url( dd_dashboard_url() );
        $support_email = esc_html( get_option( 'dd_email_from_email', get_option( 'admin_email' ) ) );

        return '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Invoice Receipt ' . $inv_code . '</title></head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:\'Plus Jakarta Sans\',Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:40px 0;">
    <tr><td align="center">
      <table width="640" cellpadding="0" cellspacing="0" style="max-width:640px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.08);">
        
        <!-- Header -->
        <tr>
          <td style="background:#070C3E;padding:30px 40px;">
            <table width="100%">
              <tr>
                <td>
                  <a href="' . $site_url . '" style="color:#02c5bd;font-size:22px;font-weight:800;text-decoration:none;">🐾 ' . esc_html( $site_name ) . '</a>
                </td>
                <td align="right">
                  <span style="background:#10b981;color:#ffffff;font-size:12px;font-weight:700;padding:6px 14px;border-radius:50px;text-transform:uppercase;letter-spacing:1px;">PAID RECEIPT</span>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Summary Title -->
        <tr>
          <td style="padding:32px 40px 16px;">
            <h1 style="color:#070C3E;font-size:22px;margin:0 0 8px;">Subscription Invoice & Payment Receipt</h1>
            <p style="color:#6b7280;font-size:14px;margin:0;">Thank you for your purchase! Below are the complete transaction and billing details for your subscription.</p>
          </td>
        </tr>

        <!-- Customer & Invoice Info Table -->
        <tr>
          <td style="padding:0 40px 24px;">
            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border-radius:8px;padding:20px;border:1px solid #e2e8f0;">
              <tr>
                <td width="50%" valign="top" style="font-size:13px;line-height:1.6;color:#334155;">
                  <strong style="color:#070C3E;font-size:14px;">Billed To:</strong><br>
                  <strong>' . esc_html( $user_name ) . '</strong><br>
                  Email: ' . esc_html( $user_email ) . '<br>
                  Customer ID: #' . esc_html( $user->ID ) . '
                </td>
                <td width="50%" valign="top" align="right" style="font-size:13px;line-height:1.6;color:#334155;">
                  <strong style="color:#070C3E;font-size:14px;">Invoice Details:</strong><br>
                  Invoice #: <strong>' . esc_html( $inv_code ) . '</strong><br>
                  Date: <strong>' . esc_html( $paid_date ) . '</strong><br>
                  Gateway: <strong>' . esc_html( $pay_method ) . '</strong><br>
                  Txn ID: <span style="font-family:monospace;font-size:12px;">' . esc_html( $txn_id ) . '</span>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Itemized Table -->
        <tr>
          <td style="padding:0 40px 24px;">
            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">
              <thead>
                <tr style="background:#f1f5f9;color:#070C3E;font-size:13px;font-weight:700;text-align:left;">
                  <th style="padding:14px 16px;border-bottom:1px solid #cbd5e1;">Subscription Plan</th>
                  <th style="padding:14px 16px;border-bottom:1px solid #cbd5e1;">Duration</th>
                  <th style="padding:14px 16px;border-bottom:1px solid #cbd5e1;text-align:right;">Amount</th>
                </tr>
              </thead>
              <tbody>
                <tr style="font-size:14px;color:#334155;border-bottom:1px solid #f1f5f9;">
                  <td style="padding:16px;">
                    <strong style="color:#070C3E;">' . esc_html( $plan->name ) . ' Plan</strong><br>
                    <span style="font-size:12px;color:#64748b;">Active Period: ' . esc_html( $starts_at ) . ' — ' . esc_html( $expires_at ) . '</span>
                  </td>
                  <td style="padding:16px;color:#64748b;font-size:13px;">' . esc_html( $plan->duration ) . ' Days</td>
                  <td style="padding:16px;text-align:right;font-weight:700;color:#070C3E;">$' . esc_html( $amount ) . '</td>
                </tr>
              </tbody>
              <tfoot>
                <tr style="background:#f8fafc;">
                  <td colspan="2" align="right" style="padding:12px 16px;font-size:13px;font-weight:600;color:#64748b;">Subtotal:</td>
                  <td align="right" style="padding:12px 16px;font-size:13px;font-weight:700;color:#334155;">$' . esc_html( $amount ) . '</td>
                </tr>
                <tr style="background:#f8fafc;">
                  <td colspan="2" align="right" style="padding:12px 16px;font-size:13px;font-weight:600;color:#64748b;">Tax (0%):</td>
                  <td align="right" style="padding:12px 16px;font-size:13px;font-weight:700;color:#334155;">$0.00</td>
                </tr>
                <tr style="background:#070C3E;color:#ffffff;">
                  <td colspan="2" align="right" style="padding:16px;font-size:15px;font-weight:700;">Total Paid:</td>
                  <td align="right" style="padding:16px;font-size:18px;font-weight:800;color:#02c5bd;">$' . esc_html( $amount ) . ' USD</td>
                </tr>
              </tfoot>
            </table>
          </td>
        </tr>

        <!-- Included Plan Features -->
        <tr>
          <td style="padding:0 40px 24px;">
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:20px;">
              <h3 style="color:#166534;font-size:15px;margin:0 0 12px;">✅ Included Plan Privileges & Features:</h3>
              <ul style="margin:0;padding-left:20px;color:#15803d;font-size:14px;line-height:1.7;">
                ' . $features_list . '
              </ul>
            </div>
          </td>
        </tr>

        <!-- Action Button -->
        <tr>
          <td align="center" style="padding:10px 40px 32px;">
            <a href="' . $dashboard_url . '" style="display:inline-block;background:#02c5bd;color:#ffffff;padding:14px 36px;border-radius:50px;text-decoration:none;font-weight:700;font-size:15px;box-shadow:0 4px 14px rgba(2,197,189,0.35);">
              🚀 Go to Subscriber Dashboard
            </a>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="background:#f8fafc;padding:24px 40px;text-align:center;border-top:1px solid #e2e8f0;">
            <p style="color:#94a3b8;font-size:12px;margin:0 0 8px;">
              If you have any questions regarding this invoice, please contact support at <a href="mailto:' . $support_email . '" style="color:#02c5bd;">' . $support_email . '</a>.
            </p>
            <p style="color:#94a3b8;font-size:11px;margin:0;">
              © ' . date( 'Y' ) . ' ' . esc_html( $site_name ) . '. All rights reserved. Outbound emails delivered securely via SMTP mailer.
            </p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>';
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
