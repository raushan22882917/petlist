<?php
/**
 * Admin Dashboard — Overview Tab
 */
use RadiusTheme\Petslist\DogDirectory\Subscription;
if ( ! defined('ABSPATH') ) exit;
global $wpdb;

$total_dogs   = wp_count_posts('dd_dog');
$active_subs  = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}dd_subscriptions WHERE status='active'");
$total_rev    = (float) ($wpdb->get_var("SELECT SUM(amount) FROM {$wpdb->prefix}dd_payments WHERE status='completed'") ?: 0);
$new_30d      = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}dd_subscriptions WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$total_users  = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}users");
$revenue_30d  = (float) ($wpdb->get_var("SELECT SUM(amount) FROM {$wpdb->prefix}dd_payments WHERE status='completed' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)") ?: 0);

// Recent activity
$recent_dogs = $wpdb->get_results("
    SELECT p.ID, p.post_title, p.post_status, p.post_date, u.display_name as author
    FROM {$wpdb->prefix}posts p
    LEFT JOIN {$wpdb->prefix}users u ON p.post_author = u.ID
    WHERE p.post_type='dd_dog' AND p.post_status IN ('publish','pending')
    ORDER BY p.post_date DESC LIMIT 8
");
$recent_subs = $wpdb->get_results("
    SELECT s.*, u.display_name, u.user_email, p.name as plan_name
    FROM {$wpdb->prefix}dd_subscriptions s
    LEFT JOIN {$wpdb->prefix}users u ON s.user_id = u.ID
    LEFT JOIN {$wpdb->prefix}dd_plans p ON s.plan_id = p.id
    ORDER BY s.created_at DESC LIMIT 6
");
$pending_dogs = (int)$total_dogs->pending;
?>

<div class="dda-overview">

    <!-- KPI Row -->
    <div class="dda-kpi-grid">
        <div class="dda-kpi dda-kpi--blue">
            <div class="dda-kpi__icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="22" height="22"><circle cx="9" cy="7" r="4"/><path d="M3 21v-2a4 4 0 014-4h4a4 4 0 014 4v2"/><path d="M16 3.13a4 4 0 010 7.75"/><path d="M21 21v-2a4 4 0 00-3-3.85"/></svg>
            </div>
            <div class="dda-kpi__body">
                <div class="dda-kpi__num"><?php echo number_format($total_users); ?></div>
                <div class="dda-kpi__label"><?php _e('Total Users','petslist'); ?></div>
            </div>
            <div class="dda-kpi__footer"><?php printf(__('%d new (30d)','petslist'), $new_30d); ?></div>
        </div>

        <div class="dda-kpi dda-kpi--teal">
            <div class="dda-kpi__icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="22" height="22"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            </div>
            <div class="dda-kpi__body">
                <div class="dda-kpi__num"><?php echo number_format($active_subs); ?></div>
                <div class="dda-kpi__label"><?php _e('Active Subscribers','petslist'); ?></div>
            </div>
            <div class="dda-kpi__footer"><?php printf(__('%d joined (30d)','petslist'), $new_30d); ?></div>
        </div>

        <div class="dda-kpi dda-kpi--amber">
            <div class="dda-kpi__icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="22" height="22"><path d="M10 3H6a2 2 0 00-2 2v14a2 2 0 002 2h12a2 2 0 002-2v-6"/><path d="M8 21V11l4-7 4 7v5"/></svg>
            </div>
            <div class="dda-kpi__body">
                <div class="dda-kpi__num"><?php echo number_format((int)$total_dogs->publish); ?></div>
                <div class="dda-kpi__label"><?php _e('Published Dogs','petslist'); ?></div>
            </div>
            <div class="dda-kpi__footer">
                <?php if ($pending_dogs > 0) : ?>
                <a href="<?php echo esc_url(dd_dashboard_url('dogs')); ?>" style="color:inherit">
                    ⚠️ <?php printf(__('%d pending','petslist'), $pending_dogs); ?>
                </a>
                <?php else : ?>
                <?php _e('All reviewed','petslist'); ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="dda-kpi dda-kpi--green">
            <div class="dda-kpi__icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="22" height="22"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
            </div>
            <div class="dda-kpi__body">
                <div class="dda-kpi__num">$<?php echo number_format($total_rev, 2); ?></div>
                <div class="dda-kpi__label"><?php _e('Total Revenue','petslist'); ?></div>
            </div>
            <div class="dda-kpi__footer">$<?php echo number_format($revenue_30d, 2); ?> <?php _e('this month','petslist'); ?></div>
        </div>
    </div>

    <?php if ($pending_dogs > 0) : ?>
    <!-- Pending approval alert -->
    <div class="dda-alert-banner">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        <strong><?php printf(__('%d dog(s) pending review','petslist'), $pending_dogs); ?></strong>
        <span><?php _e('Review and approve submissions to make them live.','petslist'); ?></span>
        <a href="<?php echo esc_url(dd_dashboard_url('dogs')); ?>" class="ddu-btn-primary" style="margin-left:auto;font-size:13px"><?php _e('Review Now →','petslist'); ?></a>
    </div>
    <?php endif; ?>

    <!-- Two-column activity area -->
    <div class="dda-overview-grid">

        <!-- Recent Dog Submissions -->
        <div class="ddu-panel">
            <div class="ddu-panel__head">
                <h3 class="ddu-panel__title"><?php _e('Recent Dog Submissions','petslist'); ?></h3>
                <a href="<?php echo esc_url(dd_dashboard_url('dogs')); ?>" class="ddu-panel__see-all"><?php _e('All Dogs →','petslist'); ?></a>
            </div>
            <table class="dda-table">
                <thead><tr>
                    <th><?php _e('Dog','petslist'); ?></th>
                    <th><?php _e('Owner','petslist'); ?></th>
                    <th><?php _e('Status','petslist'); ?></th>
                    <th><?php _e('Date','petslist'); ?></th>
                    <th><?php _e('Actions','petslist'); ?></th>
                </tr></thead>
                <tbody>
                <?php foreach ($recent_dogs as $dog) :
                    $st = $dog->post_status;
                    $st_map = ['publish'=>['Live','active'],'pending'=>['Pending','pending'],'draft'=>['Draft','draft']];
                    [$stl,$stc] = $st_map[$st] ?? [ucfirst($st),'draft'];
                ?>
                <tr data-post-id="<?php echo $dog->ID; ?>" style="cursor: pointer;">
                    <td><strong><?php echo esc_html($dog->post_title); ?></strong></td>
                    <td><?php echo esc_html($dog->author); ?></td>
                    <td><span class="ddu-pill ddu-pill--<?php echo $stc; ?>"><?php echo $stl; ?></span></td>
                    <td><?php echo date('M j', strtotime($dog->post_date)); ?></td>
                    <td>
                        <div style="display:flex;gap:6px;align-items:center">
                            <?php if ($st === 'pending') : ?>
                            <button class="dda-action-btn dda-action-btn--approve dd-approve-dog" data-id="<?php echo $dog->ID; ?>"><?php _e('Approve','petslist'); ?></button>
                            <button class="dda-action-btn dda-action-btn--reject dd-reject-dog" data-id="<?php echo $dog->ID; ?>"><?php _e('Reject','petslist'); ?></button>
                            <?php else : ?>
                            <a href="<?php echo esc_url(get_edit_post_link($dog->ID)); ?>" class="dda-action-btn dda-action-btn--edit" title="<?php esc_attr_e('Edit', 'petslist'); ?>">
                                <i class="fa-solid fa-pencil"></i>
                            </a>
                            <?php if ($st === 'publish') : ?>
                            <a href="<?php echo esc_url(get_permalink($dog->ID)); ?>" class="dda-action-btn dda-action-btn--view" target="_blank" title="<?php esc_attr_e('View Profile', 'petslist'); ?>">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$recent_dogs) : ?>
                <tr><td colspan="5" style="text-align:center;color:#9ca3af;padding:30px"><?php _e('No dogs yet.','petslist'); ?></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Recent Subscribers -->
        <div class="ddu-panel">
            <div class="ddu-panel__head">
                <h3 class="ddu-panel__title"><?php _e('Recent Subscribers','petslist'); ?></h3>
                <a href="<?php echo esc_url(dd_dashboard_url('subscribers')); ?>" class="ddu-panel__see-all"><?php _e('All →','petslist'); ?></a>
            </div>
            <div class="dda-sub-list">
                <?php foreach ($recent_subs as $sub) :
                    $st_map = ['active'=>'active','expired'=>'draft','cancelled'=>'pending','pending'=>'pending'];
                    $stc = $st_map[$sub->status] ?? 'draft';
                ?>
                <div class="dda-sub-row">
                    <div class="dda-sub-row__avatar">
                        <?php echo get_avatar($sub->user_id, 36, '', '', ['class'=>'dda-sub-row__img']); ?>
                    </div>
                    <div class="dda-sub-row__info">
                        <strong><?php echo esc_html($sub->display_name); ?></strong>
                        <span><?php echo esc_html($sub->user_email); ?></span>
                    </div>
                    <div class="dda-sub-row__plan">
                        <span class="ddu-pill ddu-pill--<?php echo $stc; ?>"><?php echo esc_html($sub->plan_name); ?></span>
                        <small><?php echo date('M j', strtotime($sub->created_at)); ?></small>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (!$recent_subs) : ?>
                <div style="text-align:center;color:#9ca3af;padding:30px"><?php _e('No subscribers yet.','petslist'); ?></div>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /grid -->

    <!-- Plans summary -->
    <?php
    $plans_data = $wpdb->get_results("
        SELECT p.name, p.price, COUNT(s.id) as sub_count,
               COALESCE(SUM(py.amount),0) as revenue
        FROM {$wpdb->prefix}dd_plans p
        LEFT JOIN {$wpdb->prefix}dd_subscriptions s ON p.id=s.plan_id AND s.status='active'
        LEFT JOIN {$wpdb->prefix}dd_payments py ON s.id=py.subscription_id AND py.status='completed'
        WHERE p.is_active=1
        GROUP BY p.id ORDER BY p.price ASC
    ");
    ?>
    <div class="ddu-panel" style="margin-top:20px">
        <div class="ddu-panel__head">
            <h3 class="ddu-panel__title"><?php _e('Plan Performance','petslist'); ?></h3>
            <a href="<?php echo esc_url(dd_dashboard_url('plans')); ?>" class="ddu-panel__see-all"><?php _e('Manage Plans →','petslist'); ?></a>
        </div>
        <div class="dda-plans-row">
            <?php foreach ($plans_data as $pl) : ?>
            <div class="dda-plan-stat">
                <div class="dda-plan-stat__name"><?php echo esc_html($pl->name); ?></div>
                <div class="dda-plan-stat__price">$<?php echo number_format($pl->price, 2); ?></div>
                <div class="dda-plan-stat__subs"><?php echo (int)$pl->sub_count; ?> <span><?php _e('active','petslist'); ?></span></div>
                <div class="dda-plan-stat__rev">$<?php echo number_format($pl->revenue, 2); ?> <span><?php _e('rev','petslist'); ?></span></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>
