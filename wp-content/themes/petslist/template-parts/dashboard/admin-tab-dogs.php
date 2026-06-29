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
    <div class="dda-filter-bar">
        <div class="dda-filter-bar__tabs">
            <?php foreach ($status_tabs as $st => [$label, $count]) : ?>
            <a href="<?php echo esc_url(add_query_arg(['tab'=>'dogs','dog_status'=>$st,'paged'=>1], dd_dashboard_url('dogs'))); ?>"
               class="dda-filter-tab <?php echo $status_filter === $st ? 'dda-filter-tab--active' : ''; ?>">
                <?php echo $label; ?> <span class="dda-filter-tab__count"><?php echo $count; ?></span>
            </a>
            <?php endforeach; ?>
        </div>
        <form class="dda-filter-bar__search" method="get">
            <input type="hidden" name="tab" value="dogs">
            <input type="hidden" name="dog_status" value="<?php echo esc_attr($status_filter); ?>">
            <input type="text" name="dog_search" placeholder="<?php esc_attr_e('Search dogs...','petslist'); ?>" value="<?php echo esc_attr($search); ?>">
            <button type="submit">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            </button>
        </form>
    </div>

    <!-- Dogs Table -->
    <div class="ddu-panel">
        <table class="dda-table">
            <thead><tr>
                <th><?php _e('Photo','petslist'); ?></th>
                <th><?php _e('Dog Name','petslist'); ?></th>
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
            <tr data-post-id="<?php echo $pid; ?>" style="cursor: pointer;">
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
                    <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
                        <?php if ($st === 'pending') : ?>
                        <button class="dda-action-btn dda-action-btn--approve dd-approve-dog" data-id="<?php echo $pid; ?>"><?php _e('Approve','petslist'); ?></button>
                        <button class="dda-action-btn dda-action-btn--reject dd-reject-dog" data-id="<?php echo $pid; ?>"><?php _e('Reject','petslist'); ?></button>
                        <?php else : ?>
                        <a href="<?php echo esc_url(get_edit_post_link($pid)); ?>" class="dda-action-btn dda-action-btn--edit" title="<?php esc_attr_e('Edit', 'petslist'); ?>">
                            <i class="fa-solid fa-pencil"></i>
                        </a>
                        <?php if ($st === 'publish') : ?>
                        <a href="<?php echo esc_url(get_permalink($pid)); ?>" class="dda-action-btn dda-action-btn--view" target="_blank" title="<?php esc_attr_e('View Profile', 'petslist'); ?>">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endwhile; wp_reset_postdata();
            else : ?>
            <tr><td colspan="7" style="text-align:center;padding:40px;color:#9ca3af"><?php _e('No dogs found.','petslist'); ?></td></tr>
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
