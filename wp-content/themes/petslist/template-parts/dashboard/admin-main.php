<?php
/**
 * Admin Dashboard — Full management panel
 * @package Petslist Dog Directory
 */
use RadiusTheme\Petslist\DogDirectory\Subscription;
if ( ! defined('ABSPATH') ) exit;
if ( ! current_user_can('manage_options') ) { wp_die(__('Access denied','petslist')); }

$uid  = get_current_user_id();
$user = wp_get_current_user();
$tab  = sanitize_key($_GET['tab'] ?? 'overview');

// Admin nav
$admin_nav = [
    'overview'    => ['icon'=>'chart',   'label'=>__('Overview','petslist')],
    'dogs'        => ['icon'=>'dogs',    'label'=>__('All Dogs','petslist')],
    'users'       => ['icon'=>'users',   'label'=>__('Users','petslist')],
    'subscribers' => ['icon'=>'star',    'label'=>__('Subscribers','petslist')],
    'payments'    => ['icon'=>'billing', 'label'=>__('Payments','petslist')],
    'plans'       => ['icon'=>'plans',   'label'=>__('Plans','petslist')],
    'settings'    => ['icon'=>'settings','label'=>__('Settings','petslist')],
];

// Admin SVG icons
function dda_icon($k) {
    $i = [
        'chart'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18" height="18"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>',
        'dogs'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18" height="18"><circle cx="9" cy="7" r="4"/><path d="M3 21v-2a4 4 0 014-4h4a4 4 0 014 4v2"/><path d="M16 3.13a4 4 0 010 7.75"/><path d="M21 21v-2a4 4 0 00-3-3.85"/></svg>',
        'users'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18" height="18"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>',
        'star'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18" height="18"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
        'billing' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18" height="18"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>',
        'plans'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18" height="18"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2M12 12h.01M12 16h.01"/></svg>',
        'settings'=> '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18" height="18"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>',
        'exit'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18" height="18"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>',
    ];
    return $i[$k] ?? '';
}
?>

<div class="ddu-shell" id="ddu-shell">

    <!-- SIDEBAR -->
    <aside class="ddu-sidebar" id="ddu-sidebar">

        <!-- Workspace header -->
        <div class="ddu-sidebar__workspace">
            <div class="ddu-sidebar__ws-icon">🐾</div>
            <div class="ddu-sidebar__ws-info">
                <span class="ddu-sidebar__ws-name"><?php bloginfo('name'); ?></span>
                <span class="ddu-sidebar__ws-role"><?php _e('Admin Panel', 'petslist'); ?></span>
            </div>
            <button class="ddu-sidebar__collapse" id="ddu-sidebar-toggle" aria-label="Toggle sidebar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
        </div>

        <!-- User identity -->
        <div class="ddu-sidebar__user">
            <div class="ddu-sidebar__user-avatar-wrap">
                <?php echo get_avatar($uid, 36, '', '', ['class'=>'ddu-sidebar__user-avatar']); ?>
                <span class="ddu-sidebar__user-dot ddu-sidebar__user-dot--active"></span>
            </div>
            <div class="ddu-sidebar__user-info">
                <span class="ddu-sidebar__user-name"><?php echo esc_html($user->display_name); ?></span>
                <span class="ddu-sidebar__user-email"><?php echo esc_html($user->user_email); ?></span>
            </div>
        </div>

        <!-- Divider -->
        <div class="ddu-sidebar__divider"><span><?php _e('Management','petslist'); ?></span></div>

        <!-- Nav -->
        <nav class="ddu-sidebar__nav">
            <?php foreach ($admin_nav as $key => $item) :
                $active = $tab === $key;
            ?>
            <a href="<?php echo esc_url(dd_dashboard_url($key)); ?>"
               class="ddu-sidebar__nav-item <?php echo $active ? 'ddu-sidebar__nav-item--active' : ''; ?>">
                <span class="ddu-sidebar__nav-icon"><?php echo dda_icon($item['icon']); ?></span>
                <span class="ddu-sidebar__nav-label"><?php echo esc_html($item['label']); ?></span>
            </a>
            <?php endforeach; ?>
        </nav>

        <!-- Divider -->
        <div class="ddu-sidebar__divider"><span><?php _e('Shortcuts','petslist'); ?></span></div>

        <!-- Shortcuts nav -->
        <nav class="ddu-sidebar__nav">
            <a href="<?php echo esc_url(dd_dog_directory_url()); ?>" class="ddu-sidebar__nav-item" target="_blank">
                <span class="ddu-sidebar__nav-icon"><?php echo dda_icon('exit'); ?></span>
                <span class="ddu-sidebar__nav-label"><?php _e('View Directory','petslist'); ?></span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11" style="margin-left:auto;opacity:.4"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6M15 3h6v6M10 14L21 3"/></svg>
            </a>
            <a href="<?php echo esc_url(admin_url()); ?>" class="ddu-sidebar__nav-item">
                <span class="ddu-sidebar__nav-icon"><?php echo dda_icon('settings'); ?></span>
                <span class="ddu-sidebar__nav-label"><?php _e('WP Admin','petslist'); ?></span>
            </a>
        </nav>

        <!-- Sidebar footer -->
        <div class="ddu-sidebar__footer">
            <a href="<?php echo esc_url(wp_logout_url(dd_login_url())); ?>" class="ddu-sidebar__nav-item ddu-sidebar__nav-item--logout">
                <span class="ddu-sidebar__nav-icon"><?php echo dda_icon('exit'); ?></span>
                <span class="ddu-sidebar__nav-label"><?php _e('Logout','petslist'); ?></span>
            </a>
        </div>

    </aside>

    <!-- ADMIN MAIN -->
    <main class="ddu-main" id="ddu-main">

        <!-- Top bar -->
        <header class="ddu-topbar">
            <div class="ddu-topbar__left">
                <button class="ddu-topbar__menu-btn" id="ddu-mobile-menu" aria-label="Menu">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
                </button>
                <nav class="ddu-topbar__breadcrumb">
                    <a href="<?php echo esc_url(dd_dashboard_url()); ?>"><?php _e('Admin Dashboard','petslist'); ?></a>
                    <?php if ($tab !== 'overview' && isset($admin_nav[$tab])) : ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M9 18l6-6-6-6"/></svg>
                    <span><?php echo esc_html($admin_nav[$tab]['label']); ?></span>
                    <?php endif; ?>
                </nav>
            </div>
            <div class="ddu-topbar__right">
                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=dd_dog')); ?>" class="ddu-btn-primary" style="font-size: 12px; padding: 4px 12px; height: auto; line-height: 1.5;">
                    + <?php _e('Add Dog','petslist'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=dd-settings')); ?>" class="ddu-btn-outline" style="font-size: 12px; padding: 4px 12px; height: auto; line-height: 1.5; border-color: rgba(255,255,255,0.15); color: var(--dds-text-muted);">
                    <?php echo dda_icon('settings'); ?> <?php _e('Settings','petslist'); ?>
                </a>
                <div class="ddu-topbar__avatar-btn">
                    <?php echo get_avatar($uid, 32, '', '', ['class'=>'ddu-topbar__avatar']); ?>
                </div>
            </div>
        </header>

        <!-- Content -->
        <div class="ddu-content">
            <?php
            $admin_tabs = [
                'overview'    => get_template_directory() . '/template-parts/dashboard/admin-tab-overview.php',
                'dogs'        => get_template_directory() . '/template-parts/dashboard/admin-tab-dogs.php',
                'users'       => get_template_directory() . '/template-parts/dashboard/admin-tab-users.php',
                'subscribers' => get_template_directory() . '/template-parts/dashboard/admin-tab-subscribers.php',
                'payments'    => get_template_directory() . '/template-parts/dashboard/admin-tab-payments.php',
                'plans'       => get_template_directory() . '/template-parts/dashboard/admin-tab-plans.php',
                'settings'    => get_template_directory() . '/template-parts/dashboard/admin-tab-settings.php',
            ];
            $tab_file = $admin_tabs[$tab] ?? $admin_tabs['overview'];
            if ( file_exists($tab_file) ) {
                include $tab_file;
            } else {
                include $admin_tabs['overview'];
            }
            ?>
        </div>

    </main>

</div>

<!-- ── Profile Drawer Panel ── -->
<div class="dd-drawer" id="dd-dog-drawer">
    <div class="dd-drawer__overlay"></div>
    <div class="dd-drawer__content">
        <div class="dd-drawer__header">
            <h3><?php _e('Dog Profile Detail', 'petslist'); ?></h3>
            <button class="dd-drawer__close" id="dd-drawer-close-btn" aria-label="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="dd-drawer__body" id="dd-dog-drawer-body">
            <!-- Profile content loaded via AJAX -->
        </div>
    </div>
</div>
