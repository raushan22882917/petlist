<?php
/**
 * Admin Dashboard — Ad Approvals & Tracking Tab
 * @package Petslist Dog Directory
 */
if ( ! defined('ABSPATH') ) exit;

$status_filter = sanitize_key($_GET['dog_status'] ?? 'pending');
$search        = sanitize_text_field($_GET['dog_search'] ?? '');
$paged         = max(1, absint($_GET['paged'] ?? 1));
$per_page      = 20;

$counts = wp_count_posts('dd_dog');
$pending_count = (int) ($counts->pending ?? 0);
$publish_count = (int) ($counts->publish ?? 0);
$draft_count   = (int) ($counts->draft ?? 0);
$total_count   = $pending_count + $publish_count + $draft_count;

// Query
$args = [
    'post_type'      => 'dd_dog',
    'post_status'    => $status_filter === 'any' ? ['pending','publish','draft'] : [$status_filter],
    'posts_per_page' => $per_page,
    'paged'          => $paged,
    'orderby'        => 'date',
    'order'          => 'DESC',
];
if ($search) $args['s'] = $search;
$q = new WP_Query($args);

$status_tabs = [
    'pending' => [__('Pending Approval','petslist'), $pending_count],
    'publish' => [__('Approved (Live)','petslist'),  $publish_count],
    'draft'   => [__('Rejected (Draft)','petslist'),  $draft_count],
    'any'     => [__('All Submissions','petslist'),   $total_count],
];
?>

<div class="dda-approvals" style="width: 100%;">

    <!-- Header Banner -->
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px 24px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="font-size: 20px; font-weight: 800; color: #0f172a; margin: 0 0 4px 0; display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 22px;">🛡️</span> <?php _e('Ad Approvals & Tracking', 'petslist'); ?>
            </h2>
            <p style="color: #64748b; font-size: 13px; margin: 0;">
                <?php _e('Review, track, and manually approve or reject incoming stud ad applications.', 'petslist'); ?>
            </p>
        </div>
        <div>
            <a href="<?php echo esc_url(dd_dashboard_url('add-dog')); ?>" style="background: #02c5bd; color: #ffffff; font-weight: 700; font-size: 13px; padding: 9px 18px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(2,197,189,0.3);">
                <i class="fa-solid fa-plus"></i> <?php _e('Add New Stud Ad', 'petslist'); ?>
            </a>
        </div>
    </div>

    <!-- Tracker Stats Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <!-- Pending Approval Card -->
        <a href="<?php echo esc_url(add_query_arg(['tab'=>'approvals','dog_status'=>'pending','paged'=>1], dd_dashboard_url('approvals'))); ?>" style="text-decoration: none; color: inherit;">
            <div style="background: #ffffff; border: 1px solid #fde68a; border-left: 5px solid #f59e0b; border-radius: 12px; padding: 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                <div style="width: 46px; height: 46px; border-radius: 10px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                    ⏳
                </div>
                <div>
                    <div style="font-size: 24px; font-weight: 800; color: #0f172a; line-height: 1.1;"><?php echo $pending_count; ?></div>
                    <div style="font-size: 12px; font-weight: 700; color: #d97706; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px;"><?php _e('Pending Approval', 'petslist'); ?></div>
                </div>
            </div>
        </a>

        <!-- Approved Ads Card -->
        <a href="<?php echo esc_url(add_query_arg(['tab'=>'approvals','dog_status'=>'publish','paged'=>1], dd_dashboard_url('approvals'))); ?>" style="text-decoration: none; color: inherit;">
            <div style="background: #ffffff; border: 1px solid #a7f3d0; border-left: 5px solid #10b981; border-radius: 12px; padding: 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                <div style="width: 46px; height: 46px; border-radius: 10px; background: #d1fae5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                    ✅
                </div>
                <div>
                    <div style="font-size: 24px; font-weight: 800; color: #0f172a; line-height: 1.1;"><?php echo $publish_count; ?></div>
                    <div style="font-size: 12px; font-weight: 700; color: #059669; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px;"><?php _e('Approved & Live', 'petslist'); ?></div>
                </div>
            </div>
        </a>

        <!-- Rejected Ads Card -->
        <a href="<?php echo esc_url(add_query_arg(['tab'=>'approvals','dog_status'=>'draft','paged'=>1], dd_dashboard_url('approvals'))); ?>" style="text-decoration: none; color: inherit;">
            <div style="background: #ffffff; border: 1px solid #fecaca; border-left: 5px solid #ef4444; border-radius: 12px; padding: 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                <div style="width: 46px; height: 46px; border-radius: 10px; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                    ❌
                </div>
                <div>
                    <div style="font-size: 24px; font-weight: 800; color: #0f172a; line-height: 1.1;"><?php echo $draft_count; ?></div>
                    <div style="font-size: 12px; font-weight: 700; color: #dc2626; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px;"><?php _e('Rejected / Draft', 'petslist'); ?></div>
                </div>
            </div>
        </a>

        <!-- Total Submissions Card -->
        <a href="<?php echo esc_url(add_query_arg(['tab'=>'approvals','dog_status'=>'any','paged'=>1], dd_dashboard_url('approvals'))); ?>" style="text-decoration: none; color: inherit;">
            <div style="background: #ffffff; border: 1px solid #bfdbfe; border-left: 5px solid #3b82f6; border-radius: 12px; padding: 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                <div style="width: 46px; height: 46px; border-radius: 10px; background: #dbeafe; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                    📋
                </div>
                <div>
                    <div style="font-size: 24px; font-weight: 800; color: #0f172a; line-height: 1.1;"><?php echo $total_count; ?></div>
                    <div style="font-size: 12px; font-weight: 700; color: #2563eb; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px;"><?php _e('Total Submissions', 'petslist'); ?></div>
                </div>
            </div>
        </a>

    </div>

    <!-- Filter Bar & Search -->
    <div class="dda-filter-bar" style="display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; margin-bottom: 20px; background: #ffffff; padding: 14px 20px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
        <div class="dda-filter-bar__tabs" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <?php foreach ($status_tabs as $st => [$label, $count]) : ?>
            <a href="<?php echo esc_url(add_query_arg(['tab'=>'approvals','dog_status'=>$st,'paged'=>1], dd_dashboard_url('approvals'))); ?>"
               class="dda-filter-tab <?php echo $status_filter === $st ? 'dda-filter-tab--active' : ''; ?>" style="text-decoration: none;">
                <?php echo $label; ?> <span class="dda-filter-tab__count" style="background: rgba(0,0,0,0.06); padding: 2px 7px; border-radius: 10px; font-size: 11px; font-weight: 700; margin-left: 4px;"><?php echo $count; ?></span>
            </a>
            <?php endforeach; ?>
        </div>
        <form class="dda-filter-bar__search" method="get" style="margin: 0;">
            <input type="hidden" name="tab" value="approvals">
            <input type="hidden" name="dog_status" value="<?php echo esc_attr($status_filter); ?>">
            <div style="position: relative; display: flex; align-items: center;">
                <input type="text" name="dog_search" placeholder="<?php esc_attr_e('Search studs or owners...','petslist'); ?>" value="<?php echo esc_attr($search); ?>" style="padding: 8px 36px 8px 14px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 13px; width: 230px; outline: none;">
                <button type="submit" style="position: absolute; right: 8px; background: none; border: none; color: #64748b; cursor: pointer; padding: 4px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                </button>
            </div>
        </form>
    </div>

    <!-- Table Panel with Horizontal Scroll Wrap -->
    <div class="ddu-panel" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="overflow-x: auto; width: 100%;">
            <table class="dda-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                        <th style="padding: 14px 16px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; width: 60px; min-width: 60px;"><?php _e('Photo','petslist'); ?></th>
                        <th style="padding: 14px 16px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; min-width: 170px;"><?php _e('Stud Application','petslist'); ?></th>
                        <th style="padding: 14px 16px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; min-width: 130px;"><?php _e('Breed','petslist'); ?></th>
                        <th style="padding: 14px 16px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; min-width: 170px;"><?php _e('Applicant / Owner','petslist'); ?></th>
                        <th style="padding: 14px 16px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; min-width: 140px;"><?php _e('Status','petslist'); ?></th>
                        <th style="padding: 14px 16px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; min-width: 150px;"><?php _e('Submitted Date','petslist'); ?></th>
                        <th style="padding: 14px 16px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; width: 180px; text-align: right;"><?php _e('Actions','petslist'); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($q->have_posts()) : while ($q->have_posts()) : $q->the_post();
                    $pid   = get_the_ID();
                    $meta  = dd_get_dog_meta($pid);
                    $thumb = get_the_post_thumbnail_url($pid,'thumbnail') ?: dd_placeholder_image();
                    $st    = get_post_status();
                    
                    $st_map = [
                        'publish' => [__('Approved (Live)','petslist'), 'active'],
                        'pending' => [__('Pending Approval','petslist'), 'pending'],
                        'draft'   => [__('Rejected / Expired','petslist'), 'draft'],
                    ];
                    [$stl,$stc] = $st_map[$st] ?? [ucfirst($st),'draft'];
                    
                    $author_name  = get_the_author_meta('display_name');
                    $author_email = get_the_author_meta('user_email');
                ?>
                <tr data-post-id="<?php echo $pid; ?>" style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;">
                    <td style="padding: 14px 16px; vertical-align: middle;">
                        <img src="<?php echo esc_url($thumb); ?>" width="46" height="46" style="border-radius:8px; object-fit:cover; border:1px solid #cbd5e1; display: block;">
                    </td>
                    <td style="padding: 14px 16px; vertical-align: middle;">
                        <strong style="font-size: 14px; color: #0f172a; display: block; line-height: 1.3;"><?php the_title(); ?></strong>
                        <?php if (!empty($meta['gender'])) echo '<span class="ddu-pill" style="font-size:10px; margin-top:4px; display:inline-block;">'.esc_html($meta['gender']).'</span>'; ?>
                    </td>
                    <td style="padding: 14px 16px; vertical-align: middle;">
                        <span style="font-weight: 600; color: #334155;"><?php echo esc_html($meta['breed'] ?? '—'); ?></span>
                    </td>
                    <td style="padding: 14px 16px; vertical-align: middle;">
                        <div style="font-weight: 600; color: #0f172a; line-height: 1.2;"><?php echo esc_html($author_name); ?></div>
                        <div style="font-size: 11px; color: #64748b; margin-top: 2px;"><?php echo esc_html($author_email); ?></div>
                    </td>
                    <td style="padding: 14px 16px; vertical-align: middle;">
                        <span class="ddu-pill ddu-pill--<?php echo $stc; ?>" style="font-weight: 700; white-space: nowrap;">
                            <?php echo $stl; ?>
                        </span>
                    </td>
                    <td style="padding: 14px 16px; vertical-align: middle;">
                        <span style="font-size: 12px; color: #475569; white-space: nowrap;"><?php echo get_the_date('M j, Y g:i a'); ?></span>
                    </td>
                    <td style="padding: 14px 16px; vertical-align: middle; text-align: right;">
                        <div style="display: inline-flex; gap: 5px; align-items: center; justify-content: flex-end; flex-wrap: wrap;">
                            
                            <!-- 1. Inspect Details Button -->
                            <button class="dda-action-btn dda-action-btn--view dd-get-dog-drawer" data-id="<?php echo $pid; ?>" title="<?php esc_attr_e('Inspect Stud Details', 'petslist'); ?>" style="width: 32px; height: 32px; border-radius: 6px; background: #f8fafc; color: #334155; border: 1px solid #cbd5e1; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f8fafc'">
                                <i class="fa-solid fa-eye"></i>
                            </button>

                            <!-- 2. Subscription Info Button -->
                            <a href="<?php echo esc_url(dd_dashboard_url('subscribers') . '&search=' . urlencode($author_email)); ?>" title="<?php esc_attr_e('View Breeder Subscription', 'petslist'); ?>" target="_blank" style="width: 32px; height: 32px; border-radius: 6px; background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; font-size: 13px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='#bae6fd'" onmouseout="this.style.background='#e0f2fe'">
                                <i class="fa-solid fa-id-card"></i>
                            </a>

                            <?php if ($st === 'pending' || $st === 'draft') : ?>
                            <!-- 3. Accept Ad / Approve -->
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

                        </div>
                    </td>
                </tr>
                <?php endwhile; wp_reset_postdata();
                else : ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding: 60px 20px; color: #9ca3af;">
                        <div style="font-size: 38px; margin-bottom: 10px;">🎉</div>
                        <h3 style="font-size: 16px; font-weight: 700; color: #334155; margin: 0 0 6px 0;"><?php _e('No ad applications found', 'petslist'); ?></h3>
                        <p style="font-size: 13px; color: #64748b; max-width: 360px; margin: 0 auto;"><?php _e('There are currently no ad applications matching this status filter.', 'petslist'); ?></p>
                    </td>
                </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($q->max_num_pages > 1) : ?>
        <div class="dda-pagination" style="padding: 16px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: center; gap: 6px;">
            <?php for ($i = 1; $i <= $q->max_num_pages; $i++) : ?>
            <a href="<?php echo esc_url(add_query_arg(['tab'=>'approvals','dog_status'=>$status_filter,'paged'=>$i], dd_dashboard_url('approvals'))); ?>"
               class="dda-pagination__btn <?php echo $paged === $i ? 'dda-pagination__btn--active' : ''; ?>">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- AJAX Response Message -->
    <div id="dd-admin-message" class="dd-auth-message" style="display:none; margin-top:16px;"></div>

</div>
