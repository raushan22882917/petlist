<?php
/**
 * Admin Dashboard — Payments Tab
 */
if ( ! defined('ABSPATH') ) exit;
global $wpdb;

$paged  = max(1, absint($_GET['paged'] ?? 1));
$per_pg = 25;
$offset = ($paged-1)*$per_pg;

$payments = $wpdb->get_results("
    SELECT py.*, u.display_name, u.user_email, p.name as plan_name
    FROM {$wpdb->prefix}dd_payments py
    LEFT JOIN {$wpdb->prefix}users u ON py.user_id=u.ID
    LEFT JOIN {$wpdb->prefix}dd_subscriptions s ON py.subscription_id=s.id
    LEFT JOIN {$wpdb->prefix}dd_plans p ON s.plan_id=p.id
    ORDER BY py.created_at DESC
    LIMIT $per_pg OFFSET $offset
");

$total   = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}dd_payments");
$pages   = ceil($total / $per_pg);
$rev_all = (float)($wpdb->get_var("SELECT SUM(amount) FROM {$wpdb->prefix}dd_payments WHERE status='completed'") ?: 0);
$rev_30d = (float)($wpdb->get_var("SELECT SUM(amount) FROM {$wpdb->prefix}dd_payments WHERE status='completed' AND created_at>=DATE_SUB(NOW(),INTERVAL 30 DAY)") ?: 0);
$tx_count= (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}dd_payments WHERE status='completed'");
?>

<div class="dda-payments">

    <!-- Revenue summary -->
    <div class="dda-kpi-grid" style="grid-template-columns:repeat(3,1fr)">
        <div class="dda-kpi dda-kpi--green">
            <div class="dda-kpi__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="22" height="22"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg></div>
            <div class="dda-kpi__body"><div class="dda-kpi__num">$<?php echo number_format($rev_all,2); ?></div><div class="dda-kpi__label"><?php _e('Total Revenue','petslist'); ?></div></div>
            <div class="dda-kpi__footer"><?php printf(__('%d transactions','petslist'), $tx_count); ?></div>
        </div>
        <div class="dda-kpi dda-kpi--blue">
            <div class="dda-kpi__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="22" height="22"><path d="M18 20V10M12 20V4M6 20v-6"/></svg></div>
            <div class="dda-kpi__body"><div class="dda-kpi__num">$<?php echo number_format($rev_30d,2); ?></div><div class="dda-kpi__label"><?php _e('Revenue (30 days)','petslist'); ?></div></div>
            <div class="dda-kpi__footer"><?php _e('Last 30 days','petslist'); ?></div>
        </div>
        <div class="dda-kpi dda-kpi--teal">
            <div class="dda-kpi__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="22" height="22"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg></div>
            <div class="dda-kpi__body"><div class="dda-kpi__num"><?php echo number_format($total); ?></div><div class="dda-kpi__label"><?php _e('Total Transactions','petslist'); ?></div></div>
            <div class="dda-kpi__footer"><?php _e('All time','petslist'); ?></div>
        </div>
    </div>

    <div class="ddu-panel" style="margin-top:20px">
        <table class="dda-table">
            <thead><tr>
                <th><?php _e('User','petslist'); ?></th>
                <th><?php _e('Plan','petslist'); ?></th>
                <th><?php _e('Amount','petslist'); ?></th>
                <th><?php _e('Method','petslist'); ?></th>
                <th><?php _e('Status','petslist'); ?></th>
                <th><?php _e('Transaction ID','petslist'); ?></th>
                <th><?php _e('Date','petslist'); ?></th>
            </tr></thead>
            <tbody>
            <?php foreach ($payments as $pay) :
                $stc = ['completed'=>'active','failed'=>'draft','pending'=>'pending','refunded'=>'pending'][$pay->status] ?? 'draft';
            ?>
            <tr>
                <td>
                    <div><strong><?php echo esc_html($pay->display_name); ?></strong></div>
                    <div style="font-size:12px;color:#9ca3af"><?php echo esc_html($pay->user_email); ?></div>
                </td>
                <td><?php echo esc_html($pay->plan_name ?: '—'); ?></td>
                <td><strong style="color:var(--dd-primary)">$<?php echo number_format($pay->amount,2); ?></strong></td>
                <td><?php echo esc_html(ucfirst($pay->payment_method ?: 'card')); ?></td>
                <td><span class="ddu-pill ddu-pill--<?php echo $stc; ?>"><?php echo ucfirst($pay->status); ?></span></td>
                <td><code style="font-size:11px"><?php echo $pay->transaction_id ? esc_html(substr($pay->transaction_id,0,20)).'…' : '—'; ?></code></td>
                <td><?php echo date('M j, Y', strtotime($pay->created_at)); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$payments) : ?>
            <tr><td colspan="7" style="text-align:center;padding:40px;color:#9ca3af"><?php _e('No payments yet.','petslist'); ?></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        <?php if ($pages > 1) : ?>
        <div class="dda-pagination">
            <?php for ($i=1;$i<=$pages;$i++): ?>
            <a href="<?php echo esc_url(add_query_arg(['tab'=>'payments','paged'=>$i], dd_dashboard_url('payments'))); ?>"
               class="dda-pagination__btn <?php echo $paged===$i?'dda-pagination__btn--active':''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
