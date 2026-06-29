<?php
/**
 * User Dashboard — Home Tab
 * @package Petslist Dog Directory
 */
use RadiusTheme\Petslist\DogDirectory\Subscription;
if ( ! defined('ABSPATH') ) exit;

$uid      = get_current_user_id();
$user     = wp_get_current_user();
$sub      = Subscription::get_user_subscription($uid);
$dogs     = dd_get_user_dogs($uid, 'any', -1);
$pub      = array_filter($dogs, fn($d) => $d->post_status === 'publish');
$pend     = array_filter($dogs, fn($d) => $d->post_status === 'pending');
$payments = Subscription::get_payment_history($uid, 3);
$hour     = (int) date('G');
$greet    = $hour < 12 ? __('Good morning','petslist') : ($hour < 17 ? __('Good afternoon','petslist') : __('Good evening','petslist'));
?>

<div class="ddu-home">

    <!-- Greeting -->
    <div class="ddu-home__greeting">
        <h1 class="ddu-home__title"><?php echo $greet; ?>, <?php echo esc_html(explode(' ',$user->display_name)[0]); ?> 👋</h1>
        <p class="ddu-home__sub"><?php printf(__("Here's what's happening with your dogs today.",'petslist')); ?></p>
    </div>

    <!-- Stat cards -->
    <div class="ddu-home__stats">

        <div class="ddu-stat-card" style="--card-color:#6366f1;--card-bg:#eef2ff">
            <div class="ddu-stat-card__icon" style="background:#eef2ff;color:#6366f1">
                <i class="fa-solid fa-paw" style="font-size: 20px;"></i>
            </div>
            <div class="ddu-stat-card__body">
                <div class="ddu-stat-card__num"><?php echo count($dogs); ?></div>
                <div class="ddu-stat-card__label"><?php _e('Total Dogs','petslist'); ?></div>
            </div>
            <div class="ddu-stat-card__trend"><?php _e('All time','petslist'); ?></div>
        </div>

        <div class="ddu-stat-card" style="--card-color:#22c55e;--card-bg:#f0fdf4">
            <div class="ddu-stat-card__icon" style="background:#f0fdf4;color:#22c55e">
                <i class="fa-solid fa-circle-check" style="font-size: 20px;"></i>
            </div>
            <div class="ddu-stat-card__body">
                <div class="ddu-stat-card__num"><?php echo count($pub); ?></div>
                <div class="ddu-stat-card__label"><?php _e('Published','petslist'); ?></div>
            </div>
            <div class="ddu-stat-card__trend" style="color:#22c55e"><?php _e('Live','petslist'); ?></div>
        </div>

        <div class="ddu-stat-card" style="--card-color:#f59e0b;--card-bg:#fffbeb">
            <div class="ddu-stat-card__icon" style="background:#fffbeb;color:#f59e0b">
                <i class="fa-solid fa-clock" style="font-size: 20px;"></i>
            </div>
            <div class="ddu-stat-card__body">
                <div class="ddu-stat-card__num"><?php echo count($pend); ?></div>
                <div class="ddu-stat-card__label"><?php _e('Pending Review','petslist'); ?></div>
            </div>
            <div class="ddu-stat-card__trend" style="color:#f59e0b"><?php _e('Awaiting','petslist'); ?></div>
        </div>

        <div class="ddu-stat-card" style="--card-color:<?php echo $sub ? '#02c5bd' : '#ef4444'; ?>;--card-bg:<?php echo $sub ? '#e0faf9' : '#fee2e2'; ?>">
            <div class="ddu-stat-card__icon" style="background:<?php echo $sub ? '#e0faf9' : '#fee2e2'; ?>;color:<?php echo $sub ? '#02c5bd' : '#ef4444'; ?>">
                <i class="fa-solid fa-star" style="font-size: 20px;"></i>
            </div>
            <div class="ddu-stat-card__body">
                <div class="ddu-stat-card__num" style="font-size:16px;margin-top:4px">
                    <?php echo $sub ? esc_html($sub->plan_name) : __('None','petslist'); ?>
                </div>
                <div class="ddu-stat-card__label"><?php _e('Subscription','petslist'); ?></div>
            </div>
            <?php if ($sub) : ?>
            <div class="ddu-stat-card__trend" style="color:#02c5bd"><?php printf(__('Exp %s','petslist'), date('M j', strtotime($sub->expires_at))); ?></div>
            <?php else : ?>
            <a href="<?php echo esc_url(dd_pricing_url()); ?>" class="ddu-stat-card__trend" style="color:#ef4444;text-decoration:none"><?php _e('Subscribe →','petslist'); ?></a>
            <?php endif; ?>
        </div>

    </div><!-- /stats -->

    <!-- Two-column layout: dogs + payments -->
    <div class="ddu-home__grid">

        <!-- Dogs section -->
        <div class="ddu-panel">
            <div class="ddu-panel__head">
                <h3 class="ddu-panel__title"><?php _e('Recent Dogs','petslist'); ?></h3>
                <div class="ddu-panel__actions">
                    <?php if ($sub) : ?>
                    <a href="<?php echo esc_url(dd_dashboard_url('add-dog')); ?>" class="ddu-btn-icon" title="<?php esc_attr_e('Add Dog','petslist'); ?>">
                        <i class="fa-solid fa-plus"></i>
                    </a>
                    <?php endif; ?>
                    <a href="<?php echo esc_url(dd_dashboard_url('dogs')); ?>" class="ddu-panel__see-all"><?php _e('See all','petslist'); ?></a>
                </div>
            </div>

            <?php if ($dogs) :
                $recent = array_slice($dogs, 0, 6);
            ?>
            <div class="ddu-dog-list">
                <?php foreach ($recent as $d) :
                    $meta  = dd_get_dog_meta($d->ID);
                    $thumb = get_the_post_thumbnail_url($d->ID,'thumbnail') ?: dd_placeholder_image();
                    $st    = $d->post_status;
                    $st_map = ['publish'=>['Live','active'],'pending'=>['Review','pending'],'draft'=>['Draft','draft']];
                    [$stl,$stc] = $st_map[$st] ?? [ucfirst($st),'draft'];
                ?>
                <div class="ddu-dog-row">
                    <div class="ddu-dog-row__img">
                        <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($d->post_title); ?>">
                    </div>
                    <div class="ddu-dog-row__info">
                        <div class="ddu-dog-row__name"><?php echo esc_html($d->post_title); ?></div>
                        <div class="ddu-dog-row__meta">
                            <?php echo esc_html($meta['breed'] ?? ''); ?>
                            <?php if ($meta['gender']) echo ' · '.esc_html($meta['gender']); ?>
                            <?php $age = dd_get_dog_age($meta['dob']??''); if ($age) echo ' · '.$age; ?>
                        </div>
                    </div>
                    <div class="ddu-dog-row__right">
                        <span class="ddu-pill ddu-pill--<?php echo $stc; ?>"><?php echo $stl; ?></span>
                        <div class="ddu-dog-row__btns">
                            <a href="<?php echo esc_url(dd_dashboard_url('dogs').'&edit='.$d->ID); ?>" class="ddu-icon-btn" title="Edit">
                                <i class="fa-solid fa-pen-to-square" style="font-size: 13px;"></i>
                            </a>
                            <?php if ($st === 'publish') : ?>
                            <a href="<?php echo esc_url(get_permalink($d->ID)); ?>" target="_blank" class="ddu-icon-btn" title="View">
                                <i class="fa-solid fa-eye" style="font-size: 13px;"></i>
                            </a>
                            <?php endif; ?>
                            <button class="ddu-icon-btn ddu-icon-btn--danger dd-delete-dog" data-id="<?php echo $d->ID; ?>" title="Delete">
                                <i class="fa-solid fa-trash-can" style="font-size: 13px;"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php else : ?>
            <div class="ddu-empty">
                <div class="ddu-empty__icon">🐾</div>
                <p class="ddu-empty__text"><?php _e("You haven't added any dogs yet.","petslist"); ?></p>
                <?php if ($sub) : ?>
                <a href="<?php echo esc_url(dd_dashboard_url('add-dog')); ?>" class="ddu-btn-primary"><?php _e('Add Your First Dog','petslist'); ?></a>
                <?php else : ?>
                <a href="<?php echo esc_url(dd_pricing_url()); ?>" class="ddu-btn-primary"><?php _e('Get a Subscription First','petslist'); ?></a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div><!-- /dogs panel -->

        <!-- Payments + Subscription panel -->
        <div style="display:flex;flex-direction:column;gap:20px">

            <!-- Subscription card -->
            <div class="ddu-panel">
                <div class="ddu-panel__head">
                    <h3 class="ddu-panel__title"><?php _e('Subscription','petslist'); ?></h3>
                    <a href="<?php echo esc_url(dd_dashboard_url('subscription')); ?>" class="ddu-panel__see-all"><?php _e('Manage','petslist'); ?></a>
                </div>
                <?php if ($sub) : ?>
                <div class="ddu-sub-card">
                    <div class="ddu-sub-card__top">
                        <div class="ddu-sub-card__badge">
                            <i class="fa-solid fa-star"></i>
                            <?php echo esc_html($sub->plan_name); ?>
                        </div>
                        <span class="ddu-pill ddu-pill--active"><?php _e('Active','petslist'); ?></span>
                    </div>
                    <div class="ddu-sub-card__expiry">
                        <?php printf(__('Expires <strong>%s</strong>','petslist'), date('F j, Y', strtotime($sub->expires_at))); ?>
                        &nbsp;·&nbsp;
                        <?php
                        $days_left = max(0, ceil((strtotime($sub->expires_at)-time())/86400));
                        printf(_n('%d day left','%d days left',$days_left,'petslist'), $days_left);
                        ?>
                    </div>
                    <?php
                    // Expiry progress bar
                    $total = max(1, ceil((strtotime($sub->expires_at)-strtotime($sub->starts_at))/86400));
                    $elapsed = $total - $days_left;
                    $pct = min(100, round(($elapsed/$total)*100));
                    ?>
                    <div class="ddu-sub-card__bar-wrap">
                        <div class="ddu-sub-card__bar" style="width:<?php echo $pct; ?>%"></div>
                    </div>
                    <a href="<?php echo esc_url(dd_pricing_url()); ?>" class="ddu-btn-outline" style="margin-top:12px"><?php _e('Upgrade Plan','petslist'); ?></a>
                </div>
                <?php else : ?>
                <div class="ddu-sub-card ddu-sub-card--inactive">
                    <p><?php _e('No active subscription. Subscribe to list dogs and access full directory features.','petslist'); ?></p>
                    <a href="<?php echo esc_url(dd_pricing_url()); ?>" class="ddu-btn-primary"><?php _e('View Plans','petslist'); ?></a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Recent payments -->
            <div class="ddu-panel">
                <div class="ddu-panel__head">
                    <h3 class="ddu-panel__title"><?php _e('Recent Payments','petslist'); ?></h3>
                    <a href="<?php echo esc_url(dd_dashboard_url('billing')); ?>" class="ddu-panel__see-all"><?php _e('See all','petslist'); ?></a>
                </div>
                <?php if ($payments) : ?>
                <div class="ddu-pay-list">
                    <?php foreach ($payments as $pay) : ?>
                    <div class="ddu-pay-row">
                        <div class="ddu-pay-row__icon">
                            <i class="fa-solid fa-credit-card"></i>
                        </div>
                        <div class="ddu-pay-row__info">
                            <span><?php echo esc_html($pay->plan_name ?: 'Subscription'); ?></span>
                            <small><?php echo date('M j, Y', strtotime($pay->created_at)); ?></small>
                        </div>
                        <span class="ddu-pay-row__amount">$<?php echo number_format($pay->amount,2); ?></span>
                        <span class="ddu-pill ddu-pill--<?php echo $pay->status==='completed'?'active':'pending'; ?>"><?php echo ucfirst($pay->status); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else : ?>
                <div class="ddu-empty">
                    <p class="ddu-empty__text"><?php _e('No payments yet.','petslist'); ?></p>
                </div>
                <?php endif; ?>
            </div>

        </div><!-- /right col -->

    </div><!-- /home grid -->

    <!-- AJAX message container -->
    <div id="dd-dog-list-message" class="dd-auth-message" style="display:none;margin-top:16px"></div>

</div><!-- /ddu-home -->
