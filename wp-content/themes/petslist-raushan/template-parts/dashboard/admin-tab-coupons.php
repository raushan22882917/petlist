<?php
/**
 * Admin Dashboard — Promo Codes & Free Subscriptions Tab
 * @package Petslist Dog Directory
 */
use RadiusTheme\Petslist\DogDirectory\Subscription;
if ( ! defined('ABSPATH') ) exit;
if ( ! dd_is_admin() ) { wp_die(__('Access denied','petslist')); }

$saved = false;
$msg   = '';
$msg_type = 'success';

// Handle quick form save
if ( isset($_POST['dd_save_coupon_nonce']) && wp_verify_nonce($_POST['dd_save_coupon_nonce'], 'dd_save_coupon') ) {
    $res = Subscription::save_coupon($_POST['coupon'] ?? []);
    if ( is_wp_error($res) ) {
        $saved = true;
        $msg = $res->get_error_message();
        $msg_type = 'error';
    } else {
        $saved = true;
        $msg = __('Promo code saved successfully!', 'petslist');
        $msg_type = 'success';
    }
}

// Handle delete / toggle / send_email via GET action
if ( isset($_GET['action']) && isset($_GET['coupon_id']) && check_admin_referer('dd_admin_coupon_action', 'dd_c_nonce') ) {
    $c_id = absint($_GET['coupon_id']);
    if ( $_GET['action'] === 'delete' ) {
        Subscription::delete_coupon($c_id);
        $saved = true;
        $msg = __('Promo code deleted.', 'petslist');
        $msg_type = 'success';
    } elseif ( $_GET['action'] === 'toggle' ) {
        $c = Subscription::get_coupon($c_id);
        if ($c) {
            global $wpdb;
            $wpdb->update($wpdb->prefix . 'dd_coupons', ['is_active' => $c->is_active ? 0 : 1], ['id' => $c_id]);
            $saved = true;
            $msg = $c->is_active ? __('Promo code deactivated.', 'petslist') : __('Promo code activated.', 'petslist');
            $msg_type = 'success';
        }
    } elseif ( $_GET['action'] === 'send_email' ) {
        $res = Subscription::send_coupon_email($c_id);
        $saved = true;
        if ( is_wp_error($res) ) {
            $msg = $res->get_error_message();
            $msg_type = 'error';
        } else {
            $msg = $res['message'];
            $msg_type = 'success';
        }
    }
}

$coupons     = Subscription::get_coupons(true);
$redemptions = Subscription::get_coupon_redemptions(30);
$plans       = Subscription::get_plans();

// Fetch users for assignment dropdown
$all_users   = get_users([
    'number'  => 300,
    'orderby' => 'display_name',
    'order'   => 'ASC',
    'fields'  => ['ID', 'user_login', 'user_email', 'display_name'],
]);

$total_codes       = count($coupons);
$active_codes      = count(array_filter($coupons, fn($c) => (int)$c->is_active === 1));
$total_redemptions = array_reduce($coupons, fn($acc, $c) => $acc + (int)$c->times_used, 0);
$private_codes     = count(array_filter($coupons, fn($c) => (!empty($c->assigned_user_id) && (int)$c->assigned_user_id > 0) || !empty($c->assigned_user_email)));
?>

<div class="dda-coupons">

    <div class="ddu-section-header" style="margin-bottom:24px;">
        <div>
            <h2 style="font-size:22px; font-weight:800; color:var(--dd-heading, #070c3e); margin:0 0 4px 0;">
                🎟️ <?php _e('Promo Codes & Free Subscriptions', 'petslist'); ?>
            </h2>
            <p style="margin:0; color:#64748b; font-size:14px;">
                <?php _e('Create and manage redeemable voucher codes that grant users free monthly subscription access.', 'petslist'); ?>
            </p>
        </div>
    </div>

    <?php if ($saved) : ?>
    <div class="dd-notice dd-notice--<?php echo esc_attr($msg_type); ?>" style="margin-bottom:20px;">
        <span><?php echo $msg_type === 'success' ? '✅' : '⚠️'; ?></span>
        <span><?php echo esc_html($msg); ?></span>
    </div>
    <?php endif; ?>

    <!-- Summary KPI cards -->
    <div class="dda-stats-grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:28px;">
        <div class="dda-stat-card" style="background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:20px;">
            <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase; margin-bottom:6px;"><?php _e('Total Promo Codes', 'petslist'); ?></div>
            <div style="font-size:28px; font-weight:800; color:#070c3e;"><?php echo $total_codes; ?></div>
        </div>
        <div class="dda-stat-card" style="background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:20px;">
            <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase; margin-bottom:6px;"><?php _e('Active Promo Codes', 'petslist'); ?></div>
            <div style="font-size:28px; font-weight:800; color:#16a34a;"><?php echo $active_codes; ?></div>
        </div>
        <div class="dda-stat-card" style="background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:20px;">
            <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase; margin-bottom:6px;"><?php _e('Total Redemptions', 'petslist'); ?></div>
            <div style="font-size:28px; font-weight:800; color:var(--dd-primary, #bd8c42);"><?php echo $total_redemptions; ?></div>
        </div>
        <div class="dda-stat-card" style="background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:20px;">
            <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase; margin-bottom:6px;"><?php _e('Private / Assigned', 'petslist'); ?></div>
            <div style="font-size:28px; font-weight:800; color:#0284c7;"><?php echo $private_codes; ?></div>
        </div>
    </div>

    <!-- Create Promo Code Form -->
    <div class="ddu-panel" style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:24px; margin-bottom:30px; box-shadow:0 4px 16px rgba(0,0,0,0.02);">
        <div style="margin-bottom:20px;">
            <h3 style="font-size:17px; font-weight:800; color:#070c3e; margin:0 0 4px 0;">➕ <?php _e('Create New Promo Code', 'petslist'); ?></h3>
            <p style="font-size:13px; color:#64748b; margin:0;"><?php _e('Generate a custom code to give users complimentary free access for 30 days or custom duration.', 'petslist'); ?></p>
        </div>

        <form method="post" action="">
            <?php wp_nonce_field('dd_save_coupon', 'dd_save_coupon_nonce'); ?>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px; margin-bottom:18px;">
                <div class="dd-form-group">
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;">
                        <?php _e('Code String *', 'petslist'); ?>
                    </label>
                    <input type="text" name="coupon[code]" placeholder="e.g. VIP-JOHN or PROMO2026" required style="width:100%; border:1px solid #cbd5e1; border-radius:8px; padding:10px 12px; font-size:14px; text-transform:uppercase; font-weight:700; letter-spacing:0.5px;" />
                </div>
                <div class="dd-form-group">
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;">
                        <?php _e('Name / Description', 'petslist'); ?>
                    </label>
                    <input type="text" name="coupon[name]" placeholder="e.g. VIP Free 1 Month Invitation" style="width:100%; border:1px solid #cbd5e1; border-radius:8px; padding:10px 12px; font-size:14px;" />
                </div>
                <div class="dd-form-group">
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;">
                        <?php _e('Target Plan', 'petslist'); ?>
                    </label>
                    <select name="coupon[plan_slug]" style="width:100%; border:1px solid #cbd5e1; border-radius:8px; padding:10px 12px; font-size:14px;">
                        <option value="all"><?php _e('All Plans', 'petslist'); ?></option>
                        <?php foreach ($plans as $p) : ?>
                        <option value="<?php echo esc_attr($p->slug); ?>"><?php echo esc_html($p->name); ?> ($<?php echo number_format($p->price, 2); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="dd-form-group">
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;">
                        <?php _e('Free Duration (Days)', 'petslist'); ?>
                    </label>
                    <input type="number" name="coupon[duration_days]" value="30" min="1" max="365" style="width:100%; border:1px solid #cbd5e1; border-radius:8px; padding:10px 12px; font-size:14px;" />
                </div>
                <div class="dd-form-group">
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;">
                        <?php _e('Max Redemptions', 'petslist'); ?> <small style="color:#94a3b8; font-weight:normal;">(0 = unlimited, 1 = single-use)</small>
                    </label>
                    <input type="number" name="coupon[max_uses]" value="1" min="0" style="width:100%; border:1px solid #cbd5e1; border-radius:8px; padding:10px 12px; font-size:14px;" />
                </div>
                <div class="dd-form-group">
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;">
                        <?php _e('Expiry Date (Optional)', 'petslist'); ?>
                    </label>
                    <input type="date" name="coupon[expires_at]" style="width:100%; border:1px solid #cbd5e1; border-radius:8px; padding:10px 12px; font-size:14px;" />
                </div>
            </div>

            <!-- Specific User Assignment Section -->
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:18px; margin-bottom:20px;">
                <div style="font-size:14px; font-weight:800; color:#0f172a; margin-bottom:12px; display:flex; align-items:center; gap:8px;">
                    <span>👤</span>
                    <span><?php _e('Assign To Specific User (Exclusive Private Voucher)', 'petslist'); ?></span>
                </div>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:14px; margin-bottom:12px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:5px;">
                            <?php _e('Select Registered User', 'petslist'); ?>
                        </label>
                        <select name="coupon[assigned_user_id]" id="dd_coupon_user_select" style="width:100%; border:1px solid #cbd5e1; border-radius:8px; padding:9px 12px; font-size:13px; background:#fff;">
                            <option value="0" data-email=""><?php _e('🌐 Public (Anyone can use)', 'petslist'); ?></option>
                            <?php foreach ($all_users as $u) : ?>
                            <option value="<?php echo esc_attr($u->ID); ?>" data-email="<?php echo esc_attr($u->user_email); ?>">
                                <?php echo esc_html($u->display_name ?: $u->user_login); ?> (<?php echo esc_html($u->user_email); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:5px;">
                            <?php _e('Assigned Email Address', 'petslist'); ?>
                        </label>
                        <input type="email" name="coupon[assigned_user_email]" id="dd_coupon_user_email" placeholder="e.g. member@example.com" style="width:100%; border:1px solid #cbd5e1; border-radius:8px; padding:9px 12px; font-size:13px; background:#fff;" />
                    </div>
                </div>

                <div style="padding-top:8px; border-top:1px solid #edf2f7;">
                    <label style="display:inline-flex; align-items:center; gap:8px; cursor:pointer; font-size:13px; font-weight:700; color:#0f172a;">
                        <input type="checkbox" name="coupon[send_email]" id="dd_coupon_send_email" value="1" checked style="width:16px; height:16px; accent-color:#02c5bd;" />
                        <span>✉️ <?php _e('Send notification email with promo voucher code & 1-click activation link to the assigned user', 'petslist'); ?></span>
                    </label>
                    <p style="margin:4px 0 0 24px; font-size:12px; color:#64748b;">
                        <?php _e('When assigned, this voucher is locked to the selected account and cannot be redeemed by anyone else.', 'petslist'); ?>
                    </p>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end;">
                <button type="submit" class="dd-btn dd-btn--primary" style="padding:10px 24px; font-weight:700; border-radius:8px;">
                    <i class="fa-solid fa-plus" style="margin-right:6px;"></i> <?php _e('Create Promo Code', 'petslist'); ?>
                </button>
            </div>
        </form>
    </div>

    <!-- Active Promo Codes Table -->
    <div class="ddu-panel" style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:24px; margin-bottom:30px; box-shadow:0 4px 16px rgba(0,0,0,0.02);">
        <div style="margin-bottom:18px;">
            <h3 style="font-size:17px; font-weight:800; color:#070c3e; margin:0 0 4px 0;">📋 <?php _e('All Promo Codes', 'petslist'); ?></h3>
            <p style="font-size:13px; color:#64748b; margin:0;"><?php _e('Manage codes, view assigned recipients, and resend voucher invitation emails.', 'petslist'); ?></p>
        </div>

        <?php if (!empty($coupons)) : ?>
        <div class="ddu-table-wrap" style="overflow-x:auto;">
            <table class="ddu-table" style="width:100%; border-collapse:collapse; font-size:14px;">
                <thead>
                    <tr style="border-bottom:2px solid #e2e8f0; text-align:left; color:#64748b; font-size:12px; text-transform:uppercase;">
                        <th style="padding:12px 14px;"><?php _e('Code', 'petslist'); ?></th>
                        <th style="padding:12px 14px;"><?php _e('Name', 'petslist'); ?></th>
                        <th style="padding:12px 14px;"><?php _e('Assigned To', 'petslist'); ?></th>
                        <th style="padding:12px 14px;"><?php _e('Plan', 'petslist'); ?></th>
                        <th style="padding:12px 14px;"><?php _e('Duration', 'petslist'); ?></th>
                        <th style="padding:12px 14px;"><?php _e('Redemptions', 'petslist'); ?></th>
                        <th style="padding:12px 14px;"><?php _e('Expires', 'petslist'); ?></th>
                        <th style="padding:12px 14px;"><?php _e('Status', 'petslist'); ?></th>
                        <th style="padding:12px 14px; text-align:right;"><?php _e('Actions', 'petslist'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($coupons as $c) :
                        $is_expired = !empty($c->expires_at) && strtotime($c->expires_at) < time();
                        $toggle_url = wp_nonce_url(add_query_arg(['tab'=>'coupons', 'action'=>'toggle', 'coupon_id'=>$c->id]), 'dd_admin_coupon_action', 'dd_c_nonce');
                        $delete_url = wp_nonce_url(add_query_arg(['tab'=>'coupons', 'action'=>'delete', 'coupon_id'=>$c->id]), 'dd_admin_coupon_action', 'dd_c_nonce');
                        $email_url  = wp_nonce_url(add_query_arg(['tab'=>'coupons', 'action'=>'send_email', 'coupon_id'=>$c->id]), 'dd_admin_coupon_action', 'dd_c_nonce');
                        $has_assignee = (!empty($c->assigned_user_id) && $c->assigned_user_id > 0) || !empty($c->assigned_user_email);
                    ?>
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:14px; font-weight:800; font-family:monospace; color:#070c3e; font-size:15px;">
                            <span style="background:#f1f5f9; padding:4px 8px; border-radius:6px;"><?php echo esc_html($c->code); ?></span>
                            <button type="button" class="dd-copy-code-btn" data-code="<?php echo esc_attr($c->code); ?>" title="<?php esc_attr_e('Copy code', 'petslist'); ?>" style="background:none; border:none; cursor:pointer; color:#94a3b8; margin-left:4px;">
                                <i class="fa-regular fa-copy"></i>
                            </button>
                        </td>
                        <td style="padding:14px; color:#334155;"><?php echo esc_html($c->name ?: '—'); ?></td>
                        <td style="padding:14px;">
                            <?php if ($has_assignee) : 
                                $assignee_user = !empty($c->assigned_user_id) ? get_userdata($c->assigned_user_id) : null;
                                $display_name  = $assignee_user ? ($assignee_user->display_name ?: $assignee_user->user_login) : '';
                                $display_email = $c->assigned_user_email ?: ($assignee_user ? $assignee_user->user_email : '');
                            ?>
                                <span style="background:#e0f2fe; color:#0369a1; padding:3px 8px; border-radius:6px; font-weight:700; font-size:11px; display:inline-block; margin-bottom:2px;">
                                    🔒 <?php echo esc_html($display_name ?: __('Private User', 'petslist')); ?>
                                </span>
                                <?php if ($display_email) : ?>
                                <div style="font-size:11px; color:#64748b;"><?php echo esc_html($display_email); ?></div>
                                <?php endif; ?>
                            <?php else : ?>
                                <span style="background:#f1f5f9; color:#64748b; padding:3px 8px; border-radius:6px; font-size:11px; font-weight:600;">
                                    🌐 <?php _e('Public', 'petslist'); ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:14px; color:#64748b;"><?php echo esc_html(ucfirst($c->plan_slug)); ?></td>
                        <td style="padding:14px; color:#334155; font-weight:600;"><?php echo (int)$c->duration_days; ?> <?php _e('days', 'petslist'); ?></td>
                        <td style="padding:14px; color:#334155;">
                            <strong><?php echo (int)$c->times_used; ?></strong>
                            <span style="color:#94a3b8;">/ <?php echo $c->max_uses > 0 ? (int)$c->max_uses : '∞'; ?></span>
                        </td>
                        <td style="padding:14px; font-size:13px; color:#64748b;">
                            <?php if ($c->expires_at) : ?>
                                <span style="<?php echo $is_expired ? 'color:#dc2626; font-weight:700;' : ''; ?>">
                                    <?php echo date('M j, Y', strtotime($c->expires_at)); ?>
                                    <?php if ($is_expired) echo ' (' . __('Expired', 'petslist') . ')'; ?>
                                </span>
                            <?php else : ?>
                                <span><?php _e('Never', 'petslist'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:14px;">
                            <?php if ($c->is_active && !$is_expired) : ?>
                            <span style="background:#dcfce7; color:#15803d; font-size:12px; font-weight:700; padding:3px 10px; border-radius:20px;">
                                <?php _e('Active', 'petslist'); ?>
                            </span>
                            <?php else : ?>
                            <span style="background:#fee2e2; color:#b91c1c; font-size:12px; font-weight:700; padding:3px 10px; border-radius:20px;">
                                <?php echo $is_expired ? __('Expired', 'petslist') : __('Inactive', 'petslist'); ?>
                            </span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:14px; text-align:right; white-space:nowrap;">
                            <?php if ($has_assignee) : ?>
                            <a href="<?php echo esc_url($email_url); ?>" class="dd-btn dd-btn--sm dd-btn--ghost" style="padding:4px 8px; font-size:12px; margin-right:4px; color:#0369a1;" title="<?php esc_attr_e('Send or resend notification email to assigned user', 'petslist'); ?>">
                                <i class="fa-solid fa-paper-plane"></i> <?php _e('Email', 'petslist'); ?>
                            </a>
                            <?php endif; ?>
                            <a href="<?php echo esc_url($toggle_url); ?>" class="dd-btn dd-btn--sm dd-btn--ghost" style="padding:4px 8px; font-size:12px; margin-right:4px;">
                                <?php echo $c->is_active ? __('Deactivate', 'petslist') : __('Activate', 'petslist'); ?>
                            </a>
                            <a href="<?php echo esc_url($delete_url); ?>" class="dd-btn dd-btn--sm dd-btn--danger dd-btn--ghost" onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this promo code?', 'petslist'); ?>');" style="padding:4px 8px; font-size:12px;">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else : ?>
        <p style="color:#64748b; font-size:14px; text-align:center; padding:30px 0;"><?php _e('No promo codes found.', 'petslist'); ?></p>
        <?php endif; ?>
    </div>

    <!-- Recent Redemptions Log -->
    <div class="ddu-panel" style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:24px; box-shadow:0 4px 16px rgba(0,0,0,0.02);">
        <div style="margin-bottom:18px;">
            <h3 style="font-size:17px; font-weight:800; color:#070c3e; margin:0 0 4px 0;">📜 <?php _e('Recent Free Access Redemptions', 'petslist'); ?></h3>
            <p style="font-size:13px; color:#64748b; margin:0;"><?php _e('Log of users who unlocked free monthly subscriptions using promo codes.', 'petslist'); ?></p>
        </div>

        <?php if (!empty($redemptions)) : ?>
        <div class="ddu-table-wrap" style="overflow-x:auto;">
            <table class="ddu-table" style="width:100%; border-collapse:collapse; font-size:14px;">
                <thead>
                    <tr style="border-bottom:2px solid #e2e8f0; text-align:left; color:#64748b; font-size:12px; text-transform:uppercase;">
                        <th style="padding:12px 14px;"><?php _e('User', 'petslist'); ?></th>
                        <th style="padding:12px 14px;"><?php _e('Email', 'petslist'); ?></th>
                        <th style="padding:12px 14px;"><?php _e('Code Redeemed', 'petslist'); ?></th>
                        <th style="padding:12px 14px;"><?php _e('Sub ID', 'petslist'); ?></th>
                        <th style="padding:12px 14px;"><?php _e('Date & Time', 'petslist'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($redemptions as $r) : ?>
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:14px; font-weight:700; color:#070c3e;"><?php echo esc_html($r->display_name ?: $r->user_login); ?></td>
                        <td style="padding:14px; color:#64748b;"><?php echo esc_html($r->user_email); ?></td>
                        <td style="padding:14px; font-weight:700; font-family:monospace; color:#16a34a;">
                            <span style="background:#dcfce7; padding:3px 8px; border-radius:6px;"><?php echo esc_html($r->code); ?></span>
                        </td>
                        <td style="padding:14px; color:#64748b;">#<?php echo (int)$r->subscription_id; ?></td>
                        <td style="padding:14px; color:#64748b; font-size:13px;"><?php echo date('M j, Y H:i', strtotime($r->redeemed_at)); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else : ?>
        <div style="text-align:center; padding:30px 0; color:#94a3b8; font-size:14px;">
            <p><?php _e('No promo codes have been redeemed yet. Share your code to get started!', 'petslist'); ?></p>
        </div>
        <?php endif; ?>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var copyBtns = document.querySelectorAll('.dd-copy-code-btn');
    copyBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var code = this.getAttribute('data-code');
            if (navigator.clipboard) {
                navigator.clipboard.writeText(code);
                var icon = this.querySelector('i');
                if (icon) {
                    icon.className = 'fa-solid fa-check';
                    icon.style.color = '#16a34a';
                    setTimeout(function() {
                        icon.className = 'fa-regular fa-copy';
                        icon.style.color = '#94a3b8';
                    }, 2000);
                }
            }
        });
    });

    // Auto sync user dropdown with email field
    var userSelect = document.getElementById('dd_coupon_user_select');
    var emailInput = document.getElementById('dd_coupon_user_email');
    var sendEmailCheckbox = document.getElementById('dd_coupon_send_email');

    if (userSelect && emailInput) {
        userSelect.addEventListener('change', function() {
            var selectedOpt = this.options[this.selectedIndex];
            var email = selectedOpt.getAttribute('data-email') || '';
            if (email) {
                emailInput.value = email;
                if (sendEmailCheckbox) {
                    sendEmailCheckbox.checked = true;
                }
            } else if (this.value === '0') {
                emailInput.value = '';
            }
        });
    }
});
</script>
