<?php
/**
 * Admin Dashboard — Subscribers Tab (Complete Subscription Management)
 * Allows Admin to search, filter, grant, extend, edit, cancel, and delete user subscriptions.
 */
if ( ! defined('ABSPATH') ) exit;
global $wpdb;

$filter = sanitize_key($_GET['sub_status'] ?? 'all');
$search = sanitize_text_field($_GET['sub_search'] ?? '');
$paged  = max(1, absint($_GET['paged'] ?? 1));
$per_pg = 20;
$offset = ($paged - 1) * $per_pg;

$where_clauses = ['1=1'];
if ( $filter !== 'all' ) {
    $where_clauses[] = $wpdb->prepare("s.status = %s", $filter);
}
if ( ! empty($search) ) {
    $like = '%' . $wpdb->esc_like($search) . '%';
    $where_clauses[] = $wpdb->prepare("(u.display_name LIKE %s OR u.user_email LIKE %s OR u.user_login LIKE %s OR s.id = %d)", $like, $like, $like, absint($search));
}
$where = 'WHERE ' . implode(' AND ', $where_clauses);

$subs = $wpdb->get_results("
    SELECT s.*, u.display_name, u.user_email, u.user_login, p.name as plan_name, p.slug as plan_slug, p.price as plan_price,
           (SELECT COUNT(*) FROM {$wpdb->prefix}posts WHERE post_author=s.user_id AND post_type='dd_dog') as dog_count
    FROM {$wpdb->prefix}dd_subscriptions s
    LEFT JOIN {$wpdb->prefix}users u ON s.user_id=u.ID
    LEFT JOIN {$wpdb->prefix}dd_plans p ON s.plan_id=p.id
    $where
    ORDER BY s.created_at DESC
    LIMIT $per_pg OFFSET $offset
");

$total = (int)$wpdb->get_var("
    SELECT COUNT(*) 
    FROM {$wpdb->prefix}dd_subscriptions s 
    LEFT JOIN {$wpdb->prefix}users u ON s.user_id=u.ID 
    $where
");
$pages = ceil($total / $per_pg);

// Status counts
$stats = $wpdb->get_results("SELECT status, COUNT(*) as cnt FROM {$wpdb->prefix}dd_subscriptions GROUP BY status");
$st_map = [];
foreach ($stats as $s) $st_map[$s->status] = (int)$s->cnt;
$total_all = array_sum($st_map);

// Plans for dropdowns
$available_plans = $wpdb->get_results("SELECT id, name, slug, price, duration FROM {$wpdb->prefix}dd_plans WHERE is_active=1 ORDER BY price ASC");

// Users for Grant Modal selector
$user_list = $wpdb->get_results("SELECT ID, display_name, user_email, user_login FROM {$wpdb->users} ORDER BY display_name ASC LIMIT 250");
?>

<div class="dda-subscribers">

    <!-- Global Notice Message -->
    <div id="dda-sub-notice" style="display:none;margin-bottom:16px;padding:12px 18px;border-radius:8px;font-size:14px;font-weight:600;box-shadow:0 2px 8px rgba(0,0,0,0.06);"></div>

    <!-- Summary KPI Header Bar -->
    <div class="dda-filter-bar" style="margin-bottom:18px;">
        <div class="dda-filter-bar__tabs">
            <?php
            $chips = [
                'all'       => [__('All Subscribers','petslist'), $total_all],
                'active'    => [__('Active','petslist'),          $st_map['active'] ?? 0],
                'expired'   => [__('Expired','petslist'),         $st_map['expired'] ?? 0],
                'cancelled' => [__('Cancelled','petslist'),       $st_map['cancelled'] ?? 0],
                'pending'   => [__('Pending','petslist'),         $st_map['pending'] ?? 0],
            ];
            foreach ($chips as $st => [$lbl, $cnt]) :
                $url = add_query_arg(['tab'=>'subscribers','sub_status'=>$st, 'sub_search'=>$search ?: false], dd_dashboard_url('subscribers'));
            ?>
            <a href="<?php echo esc_url($url); ?>"
               class="dda-filter-tab <?php echo $filter===$st?'dda-filter-tab--active':''; ?>">
                <?php echo $lbl; ?> <span class="dda-filter-tab__count"><?php echo $cnt; ?></span>
            </a>
            <?php endforeach; ?>
        </div>

        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <!-- Search Form -->
            <form class="dda-filter-bar__search" method="get">
                <input type="hidden" name="tab" value="subscribers">
                <?php if ($filter !== 'all'): ?><input type="hidden" name="sub_status" value="<?php echo esc_attr($filter); ?>"><?php endif; ?>
                <input type="text" name="sub_search" value="<?php echo esc_attr($search); ?>" placeholder="<?php esc_attr_e('Search user, email or ID...','petslist'); ?>" style="width:200px;">
                <button type="submit" title="<?php esc_attr_e('Search','petslist'); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                </button>
            </form>

            <!-- Grant Subscription Button -->
            <button type="button" id="dda-open-grant-btn" class="ddu-btn-primary" style="display:inline-flex;align-items:center;gap:6px;padding:7px 16px;font-size:13px;border-radius:6px;cursor:pointer;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                <?php _e('Grant Subscription', 'petslist'); ?>
            </button>
        </div>
    </div>

    <!-- Collapsible Grant Subscription Panel -->
    <div id="dda-grant-panel" style="display:none;background:#f8fafc;border:1.5px solid #cbd5e1;border-radius:10px;padding:20px;margin-bottom:20px;box-shadow:0 4px 12px rgba(0,0,0,0.04);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;border-bottom:1px solid #e2e8f0;padding-bottom:10px;">
            <h4 style="margin:0;font-size:16px;color:#0f172a;display:flex;align-items:center;gap:8px;">
                <span>🎁</span> <?php _e('Grant New Subscription (Admin Override)', 'petslist'); ?>
            </h4>
            <button type="button" id="dda-close-grant-btn" style="background:none;border:none;font-size:18px;cursor:pointer;color:#64748b;">&times;</button>
        </div>
        <form id="dda-grant-form">
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:16px;margin-bottom:16px;">
                <div class="dd-form-group">
                    <label style="font-weight:600;font-size:12px;text-transform:uppercase;color:#475569;"><?php _e('Select User', 'petslist'); ?> *</label>
                    <select name="user_id" id="dda-grant-user" required style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;background:#fff;font-size:13px;">
                        <option value=""><?php _e('-- Choose User --', 'petslist'); ?></option>
                        <?php foreach ($user_list as $u): ?>
                        <option value="<?php echo $u->ID; ?>">
                            <?php echo esc_html($u->display_name ?: $u->user_login); ?> (<?php echo esc_html($u->user_email); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="dd-form-group">
                    <label style="font-weight:600;font-size:12px;text-transform:uppercase;color:#475569;"><?php _e('Plan', 'petslist'); ?> *</label>
                    <select name="plan_id" required style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;background:#fff;font-size:13px;">
                        <?php foreach ($available_plans as $p): ?>
                        <option value="<?php echo $p->id; ?>">
                            <?php echo esc_html($p->name); ?> ($<?php echo number_format($p->price, 2); ?> / <?php echo $p->duration; ?> days)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="dd-form-group">
                    <label style="font-weight:600;font-size:12px;text-transform:uppercase;color:#475569;"><?php _e('Duration (Days)', 'petslist'); ?> *</label>
                    <input type="number" name="duration_days" value="30" min="1" max="3650" required style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;background:#fff;font-size:13px;">
                </div>

                <div class="dd-form-group">
                    <label style="font-weight:600;font-size:12px;text-transform:uppercase;color:#475569;"><?php _e('Admin Note / Reason', 'petslist'); ?></label>
                    <input type="text" name="notes" placeholder="<?php esc_attr_e('e.g. VIP Member, Cash Payment, Promo Grant', 'petslist'); ?>" style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;background:#fff;font-size:13px;">
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" id="dda-cancel-grant-btn" style="background:#e2e8f0;border:none;padding:8px 18px;border-radius:6px;font-size:13px;font-weight:600;cursor:pointer;color:#334155;">
                    <?php _e('Cancel', 'petslist'); ?>
                </button>
                <button type="submit" id="dda-submit-grant-btn" class="ddu-btn-primary" style="padding:8px 22px;border-radius:6px;font-size:13px;font-weight:700;cursor:pointer;">
                    <?php _e('Grant & Activate Subscription', 'petslist'); ?>
                </button>
            </div>
        </form>
    </div>

    <!-- Subscribers Table Panel -->
    <div class="ddu-panel">
        <table class="dda-table">
            <thead>
                <tr>
                    <th><?php _e('ID / User','petslist'); ?></th>
                    <th><?php _e('Plan','petslist'); ?></th>
                    <th><?php _e('Status','petslist'); ?></th>
                    <th><?php _e('Started','petslist'); ?></th>
                    <th><?php _e('Expires','petslist'); ?></th>
                    <th><?php _e('Studs','petslist'); ?></th>
                    <th style="text-align:right;"><?php _e('Admin Actions','petslist'); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($subs as $sub) :
                $st_class = ['active'=>'active','expired'=>'draft','cancelled'=>'pending','pending'=>'pending'][$sub->status] ?? 'draft';
                $is_active = ($sub->status === 'active');
                $is_expired_time = strtotime($sub->expires_at) < time();
                
                // Expiry relative label
                $expires_ts = strtotime($sub->expires_at);
                $days_diff = round(($expires_ts - time()) / 86400);
                if ($is_active && $days_diff >= 0) {
                    $expiry_badge = '<span style="display:block;font-size:11px;color:#059669;font-weight:600;">' . sprintf(__('%d days left', 'petslist'), $days_diff) . '</span>';
                } elseif ($is_active && $days_diff < 0) {
                    $expiry_badge = '<span style="display:block;font-size:11px;color:#dc2626;font-weight:600;">' . __('Past expiration date', 'petslist') . '</span>';
                } else {
                    $expiry_badge = '<span style="display:block;font-size:11px;color:#94a3b8;">' . ucfirst($sub->status) . '</span>';
                }
            ?>
            <tr id="dda-sub-row-<?php echo $sub->id; ?>" data-sub-id="<?php echo $sub->id; ?>">
                <td>
                    <div style="display:flex;align-items:center;gap:10px">
                        <?php echo get_avatar($sub->user_id, 34, '', '', ['style'=>'border-radius:50%;border:1px solid #e2e8f0;']); ?>
                        <div>
                            <div style="display:flex;align-items:center;gap:6px;">
                                <strong><?php echo esc_html($sub->display_name ?: $sub->user_login); ?></strong>
                                <span style="font-size:10px;background:#f1f5f9;color:#64748b;padding:1px 5px;border-radius:4px;">#<?php echo $sub->id; ?></span>
                            </div>
                            <div style="font-size:12px;color:#64748b"><?php echo esc_html($sub->user_email); ?></div>
                        </div>
                    </div>
                </td>
                <td>
                    <strong style="color:#0f172a"><?php echo esc_html($sub->plan_name ?: 'Custom'); ?></strong>
                    <?php if (!empty($sub->stripe_sub_id)): ?>
                        <div style="font-size:10px;color:#94a3b8;"><?php echo esc_html($sub->stripe_sub_id); ?></div>
                    <?php endif; ?>
                </td>
                <td>
                    <span id="dda-status-pill-<?php echo $sub->id; ?>" class="ddu-pill ddu-pill--<?php echo $st_class; ?>">
                        <?php echo ucfirst($sub->status); ?>
                    </span>
                </td>
                <td style="font-size:13px;"><?php echo date('M j, Y', strtotime($sub->starts_at)); ?></td>
                <td style="font-size:13px;">
                    <span id="dda-expires-val-<?php echo $sub->id; ?>"><?php echo date('M j, Y', strtotime($sub->expires_at)); ?></span>
                    <?php echo $expiry_badge; ?>
                </td>
                <td>
                    <a href="<?php echo esc_url(add_query_arg(['tab'=>'dogs','dog_search'=>$sub->display_name], dd_dashboard_url('dogs'))); ?>" 
                       style="display:inline-flex;align-items:center;gap:4px;color:#02c5bd;font-weight:700;text-decoration:none;" 
                       title="<?php esc_attr_e('View Dogs Listed by User', 'petslist'); ?>">
                        <span style="font-size:14px;">🐕</span> <?php echo (int)$sub->dog_count; ?>
                    </a>
                </td>
                <td style="text-align:right;">
                    <div style="display:inline-flex;gap:5px;align-items:center;justify-content:flex-end;flex-wrap:wrap;">
                        <!-- Quick Extend +30 Days -->
                        <button type="button" 
                                class="dda-action-btn dda-quick-extend-btn" 
                                data-id="<?php echo $sub->id; ?>" 
                                data-days="30"
                                title="<?php esc_attr_e('Extend subscription by 30 days', 'petslist'); ?>" 
                                style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;border-radius:5px;padding:4px 9px;font-size:11px;font-weight:700;cursor:pointer;">
                            +30d
                        </button>

                        <!-- Edit Subscription -->
                        <button type="button" 
                                class="dda-action-btn dda-open-edit-btn" 
                                data-id="<?php echo $sub->id; ?>"
                                data-plan="<?php echo $sub->plan_id; ?>"
                                data-status="<?php echo esc_attr($sub->status); ?>"
                                data-expires="<?php echo date('Y-m-d\TH:i', strtotime($sub->expires_at)); ?>"
                                data-user="<?php echo esc_attr($sub->display_name ?: $sub->user_login); ?>"
                                title="<?php esc_attr_e('Edit subscription details, plan or expiry', 'petslist'); ?>" 
                                style="background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;border-radius:5px;padding:4px 9px;font-size:11px;font-weight:700;cursor:pointer;">
                            <?php _e('Edit', 'petslist'); ?>
                        </button>

                        <!-- Cancel / Reactivate Toggle -->
                        <?php if ($is_active): ?>
                        <button type="button" 
                                class="dda-action-btn dda-cancel-sub-btn" 
                                data-id="<?php echo $sub->id; ?>" 
                                title="<?php esc_attr_e('Cancel subscription access', 'petslist'); ?>" 
                                style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:5px;padding:4px 9px;font-size:11px;font-weight:700;cursor:pointer;">
                            <?php _e('Cancel', 'petslist'); ?>
                        </button>
                        <?php else: ?>
                        <button type="button" 
                                class="dda-action-btn dda-reactivate-sub-btn" 
                                data-id="<?php echo $sub->id; ?>" 
                                title="<?php esc_attr_e('Reactivate subscription for 30 days', 'petslist'); ?>" 
                                style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;border-radius:5px;padding:4px 9px;font-size:11px;font-weight:700;cursor:pointer;">
                            <?php _e('Reactivate', 'petslist'); ?>
                        </button>
                        <?php endif; ?>

                        <!-- Delete Sub -->
                        <button type="button" 
                                class="dda-action-btn dda-delete-sub-btn" 
                                data-id="<?php echo $sub->id; ?>" 
                                title="<?php esc_attr_e('Permanently remove this subscription record', 'petslist'); ?>" 
                                style="background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;border-radius:5px;padding:4px 7px;font-size:11px;cursor:pointer;">
                            🗑️
                        </button>

                        <!-- WP User Link -->
                        <a href="<?php echo esc_url(admin_url('user-edit.php?user_id='.$sub->user_id)); ?>" 
                           class="dda-action-btn" 
                           target="_blank"
                           title="<?php esc_attr_e('View WordPress User Profile', 'petslist'); ?>" 
                           style="background:#f8fafc;color:#475569;border:1px solid #e2e8f0;border-radius:5px;padding:4px 7px;font-size:11px;text-decoration:none;">
                            👤
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$subs) : ?>
            <tr><td colspan="7" style="text-align:center;padding:50px 20px;color:#94a3b8;font-size:14px;"><?php _e('No subscriptions found matching your query.','petslist'); ?></td></tr>
            <?php endif; ?>
            </tbody>
        </table>

        <?php if ($pages > 1) : ?>
        <div class="dda-pagination">
            <?php for ($i=1; $i<=$pages; $i++) : ?>
            <a href="<?php echo esc_url(add_query_arg(['tab'=>'subscribers','sub_status'=>$filter,'sub_search'=>$search ?: false,'paged'=>$i], dd_dashboard_url('subscribers'))); ?>"
               class="dda-pagination__btn <?php echo $paged===$i?'dda-pagination__btn--active':''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Edit Subscription -->
<div id="dda-edit-sub-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.6);z-index:99999;align-items:center;justify-content:center;padding:20px;">
    <div style="background:#fff;border-radius:12px;max-width:480px;width:100%;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,0.2);position:relative;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;border-bottom:1px solid #e2e8f0;padding-bottom:12px;">
            <h3 style="margin:0;font-size:18px;color:#0f172a;display:flex;align-items:center;gap:8px;">
                <span>✏️</span> <?php _e('Edit Subscription', 'petslist'); ?>
            </h3>
            <button type="button" id="dda-close-edit-modal-btn" style="background:none;border:none;font-size:20px;cursor:pointer;color:#64748b;">&times;</button>
        </div>
        <form id="dda-edit-sub-form">
            <input type="hidden" name="sub_id" id="dda-edit-sub-id" value="">
            
            <div style="margin-bottom:14px;">
                <label style="display:block;font-weight:600;font-size:12px;text-transform:uppercase;color:#475569;margin-bottom:5px;"><?php _e('Subscriber', 'petslist'); ?></label>
                <input type="text" id="dda-edit-sub-user" readonly style="width:100%;height:38px;border:1px solid #e2e8f0;border-radius:6px;padding:0 12px;background:#f8fafc;font-size:13px;color:#64748b;">
            </div>

            <div style="margin-bottom:14px;">
                <label style="display:block;font-weight:600;font-size:12px;text-transform:uppercase;color:#475569;margin-bottom:5px;"><?php _e('Plan', 'petslist'); ?> *</label>
                <select name="plan_id" id="dda-edit-plan-id" style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;background:#fff;font-size:13px;">
                    <?php foreach ($available_plans as $p): ?>
                    <option value="<?php echo $p->id; ?>">
                        <?php echo esc_html($p->name); ?> ($<?php echo number_format($p->price, 2); ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-bottom:14px;">
                <label style="display:block;font-weight:600;font-size:12px;text-transform:uppercase;color:#475569;margin-bottom:5px;"><?php _e('Status', 'petslist'); ?> *</label>
                <select name="status" id="dda-edit-sub-status" style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;background:#fff;font-size:13px;">
                    <option value="active"><?php _e('Active', 'petslist'); ?></option>
                    <option value="expired"><?php _e('Expired', 'petslist'); ?></option>
                    <option value="cancelled"><?php _e('Cancelled', 'petslist'); ?></option>
                    <option value="pending"><?php _e('Pending', 'petslist'); ?></option>
                </select>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block;font-weight:600;font-size:12px;text-transform:uppercase;color:#475569;margin-bottom:5px;"><?php _e('Expiration Date & Time', 'petslist'); ?> *</label>
                <input type="datetime-local" name="expires_at" id="dda-edit-expires-at" required style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;background:#fff;font-size:13px;">
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" id="dda-cancel-edit-modal-btn" style="background:#e2e8f0;border:none;padding:9px 18px;border-radius:6px;font-size:13px;font-weight:600;cursor:pointer;color:#334155;">
                    <?php _e('Cancel', 'petslist'); ?>
                </button>
                <button type="submit" id="dda-save-edit-sub-btn" class="ddu-btn-primary" style="padding:9px 22px;border-radius:6px;font-size:13px;font-weight:700;cursor:pointer;">
                    <?php _e('Save Changes', 'petslist'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function($){
    'use strict';

    function getAdminNonce() {
        if (typeof ddVars !== 'undefined' && ddVars.nonces) {
            return ddVars.nonces.admin || ddVars.nonces.dashboard || ddVars.nonces.dog || '';
        }
        if (typeof ddAdminVars !== 'undefined' && ddAdminVars.nonce) {
            return ddAdminVars.nonce;
        }
        return '';
    }

    function showNotice(msg, isSuccess) {
        var $n = $('#dda-sub-notice');
        $n.css({
            'display': 'block',
            'background': isSuccess ? '#ecfdf5' : '#fef2f2',
            'color': isSuccess ? '#065f46' : '#991b1b',
            'border-left': isSuccess ? '4px solid #10b981' : '4px solid #ef4444'
        }).html((isSuccess ? '✅ ' : '❌ ') + msg);
        $('html, body').animate({ scrollTop: $n.offset().top - 40 }, 300);
        setTimeout(function(){ $n.fadeOut(); }, 4000);
    }

    // Toggle Grant Panel
    $('#dda-open-grant-btn').on('click', function(){
        $('#dda-grant-panel').slideToggle(200);
    });
    $('#dda-close-grant-btn, #dda-cancel-grant-btn').on('click', function(){
        $('#dda-grant-panel').slideUp(200);
    });

    // Handle Grant Form Submit
    $('#dda-grant-form').on('submit', function(e){
        e.preventDefault();
        var $btn = $('#dda-submit-grant-btn');
        $btn.prop('disabled', true).text('<?php echo esc_js(__('Granting...', 'petslist')); ?>');

        var data = {
            action:        'dd_admin_grant_subscription',
            nonce:         getAdminNonce(),
            user_id:       $('#dda-grant-user').val(),
            plan_id:       $('select[name="plan_id"]', '#dda-grant-form').val(),
            duration_days: $('input[name="duration_days"]', '#dda-grant-form').val(),
            notes:         $('input[name="notes"]', '#dda-grant-form').val(),
        };

        $.post(ddVars.ajaxUrl, data, function(res){
            $btn.prop('disabled', false).text('<?php echo esc_js(__('Grant & Activate Subscription', 'petslist')); ?>');
            if (res.success) {
                showNotice(res.data.message || '<?php echo esc_js(__('Subscription granted successfully!', 'petslist')); ?>', true);
                $('#dda-grant-panel').slideUp(200);
                setTimeout(function(){ window.location.reload(); }, 1200);
            } else {
                showNotice(res.data ? res.data.message : '<?php echo esc_js(__('Error granting subscription.', 'petslist')); ?>', false);
            }
        }).fail(function(){
            $btn.prop('disabled', false).text('<?php echo esc_js(__('Grant & Activate Subscription', 'petslist')); ?>');
            showNotice('<?php echo esc_js(__('Network error occurred.', 'petslist')); ?>', false);
        });
    });

    // Quick Extend (+30 Days)
    $(document).on('click', '.dda-quick-extend-btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var subId = $btn.data('id');
        var days = $btn.data('days') || 30;

        $btn.prop('disabled', true).text('...');
        $.post(ddVars.ajaxUrl, {
            action: 'dd_admin_extend_subscription',
            nonce:  getAdminNonce(),
            sub_id: subId,
            days:   days
        }, function(res){
            $btn.prop('disabled', false).text('+30d');
            if (res.success) {
                showNotice(res.data.message, true);
                if (res.data.formatted_expires) {
                    $('#dda-expires-val-' + subId).text(res.data.formatted_expires);
                }
                var $pill = $('#dda-status-pill-' + subId);
                $pill.removeClass('ddu-pill--draft ddu-pill--pending').addClass('ddu-pill--active').text('Active');
                $btn.closest('tr').css('background', '#f0fdf4');
                setTimeout(function(){ $btn.closest('tr').css('background', ''); }, 2000);
            } else {
                showNotice(res.data ? res.data.message : '<?php echo esc_js(__('Failed to extend.', 'petslist')); ?>', false);
            }
        }).fail(function(){
            $btn.prop('disabled', false).text('+30d');
            showNotice('<?php echo esc_js(__('Network error occurred.', 'petslist')); ?>', false);
        });
    });

    // Open Edit Modal
    $(document).on('click', '.dda-open-edit-btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        $('#dda-edit-sub-id').val($btn.data('id'));
        $('#dda-edit-sub-user').val($btn.data('user'));
        $('#dda-edit-plan-id').val($btn.data('plan'));
        $('#dda-edit-sub-status').val($btn.data('status'));
        $('#dda-edit-expires-at').val($btn.data('expires'));
        $('#dda-edit-sub-modal').css('display', 'flex');
    });

    $('#dda-close-edit-modal-btn, #dda-cancel-edit-modal-btn').on('click', function(){
        $('#dda-edit-sub-modal').hide();
    });

    // Save Edit Form
    $('#dda-edit-sub-form').on('submit', function(e){
        e.preventDefault();
        var $btn = $('#dda-save-edit-sub-btn');
        $btn.prop('disabled', true).text('<?php echo esc_js(__('Saving...', 'petslist')); ?>');

        var subId = $('#dda-edit-sub-id').val();
        var data = {
            action:     'dd_admin_update_subscription',
            nonce:      getAdminNonce(),
            sub_id:     subId,
            plan_id:    $('#dda-edit-plan-id').val(),
            status:     $('#dda-edit-sub-status').val(),
            expires_at: $('#dda-edit-expires-at').val()
        };

        $.post(ddVars.ajaxUrl, data, function(res){
            $btn.prop('disabled', false).text('<?php echo esc_js(__('Save Changes', 'petslist')); ?>');
            if (res.success) {
                $('#dda-edit-sub-modal').hide();
                showNotice(res.data.message || '<?php echo esc_js(__('Subscription updated.', 'petslist')); ?>', true);
                setTimeout(function(){ window.location.reload(); }, 1000);
            } else {
                showNotice(res.data ? res.data.message : '<?php echo esc_js(__('Error updating subscription.', 'petslist')); ?>', false);
            }
        }).fail(function(){
            $btn.prop('disabled', false).text('<?php echo esc_js(__('Save Changes', 'petslist')); ?>');
            showNotice('<?php echo esc_js(__('Network error occurred.', 'petslist')); ?>', false);
        });
    });

    // Cancel Subscription
    $(document).on('click', '.dda-cancel-sub-btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var subId = $btn.data('id');
        if (!confirm('<?php echo esc_js(__('Are you sure you want to cancel this user\'s subscription access?', 'petslist')); ?>')) {
            return;
        }

        $btn.prop('disabled', true).text('...');
        $.post(ddVars.ajaxUrl, {
            action: 'dd_admin_cancel_subscription',
            nonce:  getAdminNonce(),
            sub_id: subId
        }, function(res){
            $btn.prop('disabled', false).text('<?php echo esc_js(__('Cancel', 'petslist')); ?>');
            if (res.success) {
                showNotice(res.data.message, true);
                var $pill = $('#dda-status-pill-' + subId);
                $pill.removeClass('ddu-pill--active').addClass('ddu-pill--pending').text('Cancelled');
                $btn.replaceWith('<button type="button" class="dda-action-btn dda-reactivate-sub-btn" data-id="' + subId + '" style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;border-radius:5px;padding:4px 9px;font-size:11px;font-weight:700;cursor:pointer;"><?php echo esc_js(__('Reactivate', 'petslist')); ?></button>');
            } else {
                showNotice(res.data ? res.data.message : '<?php echo esc_js(__('Cancel failed.', 'petslist')); ?>', false);
            }
        });
    });

    // Reactivate Subscription
    $(document).on('click', '.dda-reactivate-sub-btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var subId = $btn.data('id');

        $btn.prop('disabled', true).text('...');
        $.post(ddVars.ajaxUrl, {
            action: 'dd_admin_extend_subscription',
            nonce:  getAdminNonce(),
            sub_id: subId,
            days:   30
        }, function(res){
            if (res.success) {
                showNotice(res.data.message, true);
                setTimeout(function(){ window.location.reload(); }, 1000);
            } else {
                $btn.prop('disabled', false).text('<?php echo esc_js(__('Reactivate', 'petslist')); ?>');
                showNotice(res.data ? res.data.message : '<?php echo esc_js(__('Reactivation failed.', 'petslist')); ?>', false);
            }
        });
    });

    // Delete Subscription
    $(document).on('click', '.dda-delete-sub-btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var subId = $btn.data('id');
        if (!confirm('<?php echo esc_js(__('Are you sure you want to permanently delete this subscription record? This action cannot be undone.', 'petslist')); ?>')) {
            return;
        }

        var $tr = $('#dda-sub-row-' + subId);
        $tr.css('opacity', '0.4');

        $.post(ddVars.ajaxUrl, {
            action: 'dd_admin_delete_subscription',
            nonce:  getAdminNonce(),
            sub_id: subId
        }, function(res){
            if (res.success) {
                showNotice(res.data.message, true);
                $tr.fadeOut(300, function(){ $(this).remove(); });
            } else {
                $tr.css('opacity', '1');
                showNotice(res.data ? res.data.message : '<?php echo esc_js(__('Delete failed.', 'petslist')); ?>', false);
            }
        }).fail(function(){
            $tr.css('opacity', '1');
            showNotice('<?php echo esc_js(__('Network error occurred.', 'petslist')); ?>', false);
        });
    });

})(jQuery);
</script>

