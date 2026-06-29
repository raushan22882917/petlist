<?php
/**
 * Admin Dashboard — Subscribers Tab
 */
if ( ! defined('ABSPATH') ) exit;
global $wpdb;

$filter = sanitize_key($_GET['sub_status'] ?? 'active');
$paged  = max(1, absint($_GET['paged'] ?? 1));
$per_pg = 25;
$offset = ($paged - 1) * $per_pg;
$where  = $filter !== 'all' ? $wpdb->prepare(" AND s.status = %s", $filter) : '';

$subs = $wpdb->get_results("
    SELECT s.*, u.display_name, u.user_email, p.name as plan_name,
           (SELECT COUNT(*) FROM {$wpdb->prefix}posts WHERE post_author=s.user_id AND post_type='dd_dog') as dog_count
    FROM {$wpdb->prefix}dd_subscriptions s
    LEFT JOIN {$wpdb->prefix}users u ON s.user_id=u.ID
    LEFT JOIN {$wpdb->prefix}dd_plans p ON s.plan_id=p.id
    WHERE 1=1 $where
    ORDER BY s.created_at DESC
    LIMIT $per_pg OFFSET $offset
");
$total = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}dd_subscriptions s WHERE 1=1 $where");
$pages = ceil($total / $per_pg);

// Summary stats
$stats = $wpdb->get_results("SELECT status, COUNT(*) as cnt FROM {$wpdb->prefix}dd_subscriptions GROUP BY status");
$st_map2 = [];
foreach ($stats as $s) $st_map2[$s->status] = $s->cnt;
?>

<div class="dda-subscribers">

    <!-- Summary chips -->
    <div class="dda-sub-summary">
        <?php
        $chips = [
            'all'       => [__('All','petslist'),           array_sum(array_column($stats,'cnt'))],
            'active'    => [__('Active','petslist'),         $st_map2['active'] ?? 0],
            'expired'   => [__('Expired','petslist'),        $st_map2['expired'] ?? 0],
            'cancelled' => [__('Cancelled','petslist'),      $st_map2['cancelled'] ?? 0],
            'pending'   => [__('Pending','petslist'),        $st_map2['pending'] ?? 0],
        ];
        foreach ($chips as $st => [$lbl, $cnt]) :
        ?>
        <a href="<?php echo esc_url(add_query_arg(['tab'=>'subscribers','sub_status'=>$st], dd_dashboard_url('subscribers'))); ?>"
           class="dda-filter-tab <?php echo $filter===$st?'dda-filter-tab--active':''; ?>">
            <?php echo $lbl; ?> <span class="dda-filter-tab__count"><?php echo $cnt; ?></span>
        </a>
        <?php endforeach; ?>
    </div>

    <div class="ddu-panel">
        <table class="dda-table">
            <thead><tr>
                <th><?php _e('User','petslist'); ?></th>
                <th><?php _e('Plan','petslist'); ?></th>
                <th><?php _e('Status','petslist'); ?></th>
                <th><?php _e('Started','petslist'); ?></th>
                <th><?php _e('Expires','petslist'); ?></th>
                <th><?php _e('Dogs','petslist'); ?></th>
                <th><?php _e('Actions','petslist'); ?></th>
            </tr></thead>
            <tbody>
            <?php foreach ($subs as $sub) :
                $st_class = ['active'=>'active','expired'=>'draft','cancelled'=>'pending','pending'=>'pending'][$sub->status] ?? 'draft';
            ?>
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:10px">
                        <?php echo get_avatar($sub->user_id, 32, '', '', ['style'=>'border-radius:50%']); ?>
                        <div>
                            <div><strong><?php echo esc_html($sub->display_name); ?></strong></div>
                            <div style="font-size:12px;color:#9ca3af"><?php echo esc_html($sub->user_email); ?></div>
                        </div>
                    </div>
                </td>
                <td><?php echo esc_html($sub->plan_name); ?></td>
                <td><span class="ddu-pill ddu-pill--<?php echo $st_class; ?>"><?php echo ucfirst($sub->status); ?></span></td>
                <td><?php echo date('M j, Y', strtotime($sub->starts_at)); ?></td>
                <td><?php echo date('M j, Y', strtotime($sub->expires_at)); ?></td>
                <td><strong><?php echo (int)$sub->dog_count; ?></strong></td>
                <td>
                    <a href="<?php echo esc_url(admin_url('user-edit.php?user_id='.$sub->user_id)); ?>" class="dda-action-btn"><?php _e('User','petslist'); ?></a>
                    <a href="<?php echo esc_url(add_query_arg(['tab'=>'dogs','dog_search'=>$sub->display_name], dd_dashboard_url('dogs'))); ?>" class="dda-action-btn"><?php _e('Dogs','petslist'); ?></a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$subs) : ?>
            <tr><td colspan="7" style="text-align:center;padding:40px;color:#9ca3af"><?php _e('No subscribers found.','petslist'); ?></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        <?php if ($pages > 1) : ?>
        <div class="dda-pagination">
            <?php for ($i=1; $i<=$pages; $i++) : ?>
            <a href="<?php echo esc_url(add_query_arg(['tab'=>'subscribers','sub_status'=>$filter,'paged'=>$i], dd_dashboard_url('subscribers'))); ?>"
               class="dda-pagination__btn <?php echo $paged===$i?'dda-pagination__btn--active':''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
