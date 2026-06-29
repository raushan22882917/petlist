<?php
/**
 * Admin Dashboard — Users Tab
 */
if ( ! defined('ABSPATH') ) exit;
global $wpdb;

$search  = sanitize_text_field($_GET['user_search'] ?? '');
$paged   = max(1, absint($_GET['paged'] ?? 1));
$per_pg  = 20;
$offset  = ($paged - 1) * $per_pg;

$where = '';
if ($search) {
    $like  = '%' . $wpdb->esc_like($search) . '%';
    $where = $wpdb->prepare(" AND (u.display_name LIKE %s OR u.user_email LIKE %s)", $like, $like);
}

$users = $wpdb->get_results("
    SELECT u.ID, u.display_name, u.user_email, u.user_registered,
           s.status as sub_status, p.name as plan_name, s.expires_at,
           (SELECT COUNT(*) FROM {$wpdb->prefix}posts WHERE post_author=u.ID AND post_type='dd_dog' AND post_status IN ('publish','pending')) as dog_count
    FROM {$wpdb->prefix}users u
    LEFT JOIN {$wpdb->prefix}dd_subscriptions s ON s.user_id=u.ID AND s.status='active'
    LEFT JOIN {$wpdb->prefix}dd_plans p ON s.plan_id=p.id
    WHERE 1=1 $where
    ORDER BY u.user_registered DESC
    LIMIT $per_pg OFFSET $offset
");

$total_count = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}users u WHERE 1=1 $where");
$total_pages = ceil($total_count / $per_pg);
?>

<div class="dda-users">
    <div class="dda-filter-bar">
        <span class="dda-filter-bar__count"><?php printf(__('%d total users','petslist'), $total_count); ?></span>
        <form class="dda-filter-bar__search" method="get">
            <input type="hidden" name="tab" value="users">
            <input type="text" name="user_search" value="<?php echo esc_attr($search); ?>" placeholder="<?php esc_attr_e('Search by name or email...','petslist'); ?>">
            <button type="submit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg></button>
        </form>
    </div>

    <div class="ddu-panel">
        <table class="dda-table">
            <thead><tr>
                <th><?php _e('User','petslist'); ?></th>
                <th><?php _e('Email','petslist'); ?></th>
                <th><?php _e('Subscription','petslist'); ?></th>
                <th><?php _e('Expires','petslist'); ?></th>
                <th><?php _e('Dogs','petslist'); ?></th>
                <th><?php _e('Joined','petslist'); ?></th>
                <th><?php _e('Actions','petslist'); ?></th>
            </tr></thead>
            <tbody>
            <?php foreach ($users as $u) :
                $has_sub = !empty($u->sub_status);
            ?>
            <tr data-user-id="<?php echo $u->ID; ?>" style="cursor: pointer;">
                <td>
                    <div style="display:flex;align-items:center;gap:10px">
                        <?php echo get_avatar($u->ID, 32, '', '', ['style'=>'border-radius:50%']); ?>
                        <strong><?php echo esc_html($u->display_name); ?></strong>
                    </div>
                </td>
                <td><?php echo esc_html($u->user_email); ?></td>
                <td>
                    <?php if ($has_sub) : ?>
                    <span class="ddu-pill ddu-pill--active"><?php echo esc_html($u->plan_name); ?></span>
                    <?php else : ?>
                    <span class="ddu-pill ddu-pill--draft"><?php _e('None','petslist'); ?></span>
                    <?php endif; ?>
                </td>
                <td><?php echo $has_sub ? date('M j, Y', strtotime($u->expires_at)) : '—'; ?></td>
                <td><strong><?php echo (int)$u->dog_count; ?></strong></td>
                <td><?php echo date('M j, Y', strtotime($u->user_registered)); ?></td>
                <td>
                    <div style="display:flex;gap:6px;align-items:center">
                        <a href="<?php echo esc_url(admin_url('user-edit.php?user_id='.$u->ID)); ?>" class="dda-action-btn dda-action-btn--edit" title="<?php esc_attr_e('Edit User', 'petslist'); ?>">
                            <i class="fa-solid fa-pencil"></i>
                        </a>
                        <a href="<?php echo esc_url(add_query_arg(['tab'=>'dogs','dog_author'=>$u->ID], dd_dashboard_url('dogs'))); ?>" class="dda-action-btn dda-action-btn--dogs" title="<?php esc_attr_e('View User\'s Dogs', 'petslist'); ?>">
                            <i class="fa-solid fa-dog"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$users) : ?>
            <tr><td colspan="7" style="text-align:center;padding:40px;color:#9ca3af"><?php _e('No users found.','petslist'); ?></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        <?php if ($total_pages > 1) : ?>
        <div class="dda-pagination">
            <?php for ($i=1; $i<=$total_pages; $i++) : ?>
            <a href="<?php echo esc_url(add_query_arg(['tab'=>'users','paged'=>$i], dd_dashboard_url('users'))); ?>"
               class="dda-pagination__btn <?php echo $paged===$i?'dda-pagination__btn--active':''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
