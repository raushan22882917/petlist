<?php
/**
 * Admin Dashboard — All Dogs Tab
 */
if ( ! defined('ABSPATH') ) exit;
global $wpdb;

$status_filter = sanitize_key($_GET['dog_status'] ?? 'any');
$search        = sanitize_text_field($_GET['dog_search'] ?? '');
$author_filter = absint($_GET['dog_author'] ?? 0);
$paged         = max(1, absint($_GET['paged'] ?? 1));
$per_page      = 20;

$args = [
    'post_type'      => 'dd_dog',
    'post_status'    => $status_filter === 'any' ? ['publish','pending','draft'] : [$status_filter],
    'posts_per_page' => $per_page,
    'paged'          => $paged,
    'orderby'        => 'date',
    'order'          => 'DESC',
];
if ($search) $args['s'] = $search;
if ($author_filter) $args['author'] = $author_filter;
$q = new WP_Query($args);

$counts = wp_count_posts('dd_dog');
$status_tabs = [
    'any'     => [__('All','petslist'),     ($counts->publish + $counts->pending + $counts->draft)],
    'publish' => [__('Published','petslist'), $counts->publish],
    'pending' => [__('Pending','petslist'),   $counts->pending],
    'draft'   => [__('Draft','petslist'),     $counts->draft],
];
?>

<div class="dda-dogs">

    <!-- Filter bar -->
    <div class="dda-filter-bar" style="display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 20px;">
        <div class="dda-filter-bar__tabs">
            <?php foreach ($status_tabs as $st => [$label, $count]) : ?>
            <a href="<?php echo esc_url(add_query_arg(['tab'=>'dogs','dog_status'=>$st,'paged'=>1], dd_dashboard_url('dogs'))); ?>"
               class="dda-filter-tab <?php echo $status_filter === $st ? 'dda-filter-tab--active' : ''; ?>">
                <?php echo $label; ?> <span class="dda-filter-tab__count"><?php echo $count; ?></span>
            </a>
            <?php endforeach; ?>
        </div>
        <div style="display: flex; align-items: center; gap: 12px;">
            <form class="dda-filter-bar__search" method="get" style="margin: 0;">
                <input type="hidden" name="tab" value="dogs">
                <input type="hidden" name="dog_status" value="<?php echo esc_attr($status_filter); ?>">
                <input type="text" name="dog_search" placeholder="<?php esc_attr_e('Search studs...','petslist'); ?>" value="<?php echo esc_attr($search); ?>">
                <button type="submit">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                </button>
            </form>
            <a href="<?php echo esc_url( dd_dashboard_url('add-dog') ); ?>" class="dd-btn dd-btn--primary" style="white-space: nowrap; display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; background-color: #02c5bd; color: #ffffff; font-weight: 700; border-radius: 8px; text-decoration: none; font-size: 13px; box-shadow: 0 2px 6px rgba(2, 197, 189, 0.3);">
                <i class="fa-solid fa-plus"></i> <?php _e( 'Add New Stud', 'petslist' ); ?>
            </a>
        </div>
    </div>

    <!-- Dogs Table -->
    <div class="ddu-panel">
        <table class="dda-table">
            <thead><tr>
                <th><?php _e('Photo','petslist'); ?></th>
                <th><?php _e('Stud Name','petslist'); ?></th>
                <th><?php _e('Breed','petslist'); ?></th>
                <th><?php _e('Owner','petslist'); ?></th>
                <th><?php _e('Status','petslist'); ?></th>
                <th><?php _e('Date','petslist'); ?></th>
                <th><?php _e('Actions','petslist'); ?></th>
            </tr></thead>
            <tbody>
            <?php if ($q->have_posts()) : while ($q->have_posts()) : $q->the_post();
                $pid   = get_the_ID();
                $meta  = dd_get_dog_meta($pid);
                $thumb = get_the_post_thumbnail_url($pid,'thumbnail') ?: dd_placeholder_image();
                $st    = get_post_status();
                $st_map = ['publish'=>['Live','active'],'pending'=>['Pending','pending'],'draft'=>['Draft','draft']];
                [$stl,$stc] = $st_map[$st] ?? [ucfirst($st),'draft'];
                $author = get_the_author();
            ?>
            <tr data-post-id="<?php echo $pid; ?>">
                <td style="width:52px"><img src="<?php echo esc_url($thumb); ?>" width="44" height="44" style="border-radius:8px;object-fit:cover"></td>
                <td>
                    <strong><?php the_title(); ?></strong>
                    <?php if ($meta['gender']) echo ' <span class="ddu-pill" style="font-size:10px">'.esc_html($meta['gender']).'</span>'; ?>
                </td>
                <td><?php echo esc_html($meta['breed'] ?? '—'); ?></td>
                <td><?php echo esc_html($author); ?></td>
                <td><span class="ddu-pill ddu-pill--<?php echo $stc; ?>"><?php echo $stl; ?></span></td>
                <td><?php echo get_the_date('M j, Y'); ?></td>
                <td>
                    <div style="display:flex; gap:5px; align-items:center; flex-wrap:wrap; justify-content:flex-end;">
                        
                        <!-- 1. Inspect Details Button -->
                        <button class="dda-action-btn dda-action-btn--view dd-get-dog-drawer" data-id="<?php echo $pid; ?>" title="<?php esc_attr_e('Inspect Stud Details', 'petslist'); ?>" style="width: 32px; height: 32px; border-radius: 6px; background: #f8fafc; color: #334155; border: 1px solid #cbd5e1; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f8fafc'">
                            <i class="fa-solid fa-eye"></i>
                        </button>

                        <!-- 2. Subscription Info Button -->
                        <?php $author_email = get_the_author_meta('user_email'); ?>
                        <a href="<?php echo esc_url(dd_dashboard_url('subscribers') . '&search=' . urlencode($author_email)); ?>" title="<?php esc_attr_e('View Breeder Subscription', 'petslist'); ?>" target="_blank" style="width: 32px; height: 32px; border-radius: 6px; background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; font-size: 13px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='#bae6fd'" onmouseout="this.style.background='#e0f2fe'">
                            <i class="fa-solid fa-id-card"></i>
                        </a>

                        <?php if ($st === 'pending' || $st === 'draft') : ?>
                        <!-- 3. Accept / Approve Ad -->
                        <button class="dda-action-btn dda-action-btn--approve dd-approve-dog" data-id="<?php echo $pid; ?>" title="<?php esc_attr_e('Accept Ad (Approve Live)', 'petslist'); ?>" style="width: 32px; height: 32px; border-radius: 6px; background: #02c5bd; color: #ffffff; border: none; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(2, 197, 189, 0.3); transition: all 0.2s;" onmouseover="this.style.background='#02a8a2'" onmouseout="this.style.background='#02c5bd'">
                            <i class="fa-solid fa-check"></i>
                        </button>
                        <?php endif; ?>

                        <?php if ($st === 'publish') : ?>
                        <!-- 4. Unpublish Ad -->
                        <button class="dda-action-btn dda-action-btn--reject dd-reject-dog" data-id="<?php echo $pid; ?>" title="<?php esc_attr_e('Unpublish Ad (Move to Draft)', 'petslist'); ?>" style="width: 32px; height: 32px; border-radius: 6px; background: #fff1f2; color: #e11d48; border: 1px solid #fecdd3; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s;" onmouseover="this.style.background='#ffe4e6'" onmouseout="this.style.background='#fff1f2'">
                            <i class="fa-solid fa-ban"></i>
                        </button>
                        <?php endif; ?>

                        <!-- 5. Expire Listing Button -->
                        <button class="dda-action-btn dd-expire-dog" data-id="<?php echo $pid; ?>" title="<?php esc_attr_e('Mark Listing Expired', 'petslist'); ?>" style="width: 32px; height: 32px; border-radius: 6px; background: #fef3c7; color: #d97706; border: 1px solid #fde68a; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s;" onmouseover="this.style.background='#fde68a'" onmouseout="this.style.background='#fef3c7'">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </button>

                        <?php
                        $is_sponsored = isset($meta['is_sponsored']) && $meta['is_sponsored'] === 'Yes';
                        $star_icon = $is_sponsored ? 'fa-solid fa-star' : 'fa-regular fa-star';
                        $title = $is_sponsored ? esc_html__('Unmark Sponsored Ad', 'petslist') : esc_html__('Mark Sponsored Ad', 'petslist');
                        ?>
                        <!-- 6. Toggle Sponsored -->
                        <button class="dda-action-btn dd-toggle-sponsored" data-id="<?php echo $pid; ?>" title="<?php echo $title; ?>" style="width: 32px; height: 32px; border-radius: 6px; background: #fef9c3; color: #ca8a04; border: 1px solid #fef08a; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s;">
                            <i class="<?php echo $star_icon; ?>"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endwhile; wp_reset_postdata();
            else : ?>
            <tr><td colspan="7" style="text-align:center;padding:40px;color:#9ca3af"><?php _e('No studs found.','petslist'); ?></td></tr>
            <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($q->max_num_pages > 1) : ?>
        <div class="dda-pagination">
            <?php for ($i = 1; $i <= $q->max_num_pages; $i++) : ?>
            <a href="<?php echo esc_url(add_query_arg(['tab'=>'dogs','dog_status'=>$status_filter,'paged'=>$i], dd_dashboard_url('dogs'))); ?>"
               class="dda-pagination__btn <?php echo $paged === $i ? 'dda-pagination__btn--active' : ''; ?>">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- AJAX response -->
    <div id="dd-admin-message" class="dd-auth-message" style="display:none;margin-top:12px"></div>

</div>
