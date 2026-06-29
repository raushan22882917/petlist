<?php
/**
 * User Dashboard Shell
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
$tab      = sanitize_text_field($_GET['tab'] ?? 'home');

// Define tabs
$nav = [
    'main' => [
        'home' => [
            'label' => __('Home', 'petslist'),
            'icon'  => 'home',
            'badge' => 0,
        ],
        'dogs' => [
            'label' => __('My Dogs', 'petslist'),
            'icon'  => 'dogs',
            'badge' => count($dogs),
        ],
        'add-dog' => [
            'label' => __('Add Dog', 'petslist'),
            'icon'  => 'add',
            'badge' => 0,
        ],
    ],
    'account' => [
        'subscription' => [
            'label' => __('Subscription', 'petslist'),
            'icon'  => 'star',
        ],
        'billing' => [
            'label' => __('Billing', 'petslist'),
            'icon'  => 'billing',
        ],
        'profile' => [
            'label' => __('Profile', 'petslist'),
            'icon'  => 'profile',
        ],
        'password' => [
            'label' => __('Password', 'petslist'),
            'icon'  => 'lock',
        ],
    ],
];

// SVG icon map
function dd_nav_icon($k) {
    $icons = [
        'home'    => '<i class="fa-solid fa-house" style="font-size: 15px;"></i>',
        'dogs'    => '<i class="fa-solid fa-dog" style="font-size: 15px;"></i>',
        'add'     => '<i class="fa-solid fa-circle-plus" style="font-size: 15px;"></i>',
        'star'    => '<i class="fa-solid fa-star" style="font-size: 15px;"></i>',
        'billing' => '<i class="fa-solid fa-credit-card" style="font-size: 15px;"></i>',
        'profile' => '<i class="fa-solid fa-user" style="font-size: 15px;"></i>',
        'lock'    => '<i class="fa-solid fa-lock" style="font-size: 15px;"></i>',
        'logout'  => '<i class="fa-solid fa-right-from-bracket" style="font-size: 15px;"></i>',
        'alert'   => '<i class="fa-solid fa-triangle-exclamation" style="font-size: 15px;"></i>',
        'dir'     => '<i class="fa-solid fa-compass" style="font-size: 15px;"></i>',
    ];
    return $icons[$k] ?? '';
}
?>

<!-- ═══ USER DASHBOARD SHELL ═══ -->
<div class="ddu-shell" id="ddu-shell">

    <!-- ── SIDEBAR ── -->
    <aside class="ddu-sidebar" id="ddu-sidebar">

        <!-- Workspace header -->
        <div class="ddu-sidebar__workspace">
            <div class="ddu-sidebar__ws-icon">🐾</div>
            <div class="ddu-sidebar__ws-info">
                <span class="ddu-sidebar__ws-name"><?php bloginfo('name'); ?></span>
                <span class="ddu-sidebar__ws-role"><?php echo $sub ? esc_html($sub->plan_name).' Member' : 'Free Account'; ?></span>
            </div>
            <button class="ddu-sidebar__collapse" id="ddu-sidebar-toggle" aria-label="Toggle sidebar">
                <i class="fa-solid fa-angle-left"></i>
            </button>
        </div>

        <!-- User identity -->
        <div class="ddu-sidebar__user">
            <div class="ddu-sidebar__user-avatar-wrap">
                <?php echo get_avatar($uid, 36, '', '', ['class'=>'ddu-sidebar__user-avatar']); ?>
                <span class="ddu-sidebar__user-dot <?php echo $sub ? 'ddu-sidebar__user-dot--active' : ''; ?>"></span>
            </div>
            <div class="ddu-sidebar__user-info">
                <span class="ddu-sidebar__user-name"><?php echo esc_html($user->display_name); ?></span>
                <span class="ddu-sidebar__user-email"><?php echo esc_html($user->user_email); ?></span>
            </div>
        </div>

        <!-- Quick action -->
        <?php if ( $sub ) : ?>
        <div class="ddu-sidebar__quick">
            <a href="<?php echo esc_url(dd_dashboard_url('add-dog')); ?>" class="ddu-sidebar__quick-btn">
                <?php echo dd_nav_icon('add'); ?>
                <span><?php _e('Add Dog','petslist'); ?></span>
            </a>
        </div>
        <?php else : ?>
        <div class="ddu-sidebar__quick">
            <a href="<?php echo esc_url(dd_pricing_url()); ?>" class="ddu-sidebar__quick-btn ddu-sidebar__quick-btn--upgrade">
                <?php echo dd_nav_icon('star'); ?>
                <span><?php _e('Upgrade Plan','petslist'); ?></span>
            </a>
        </div>
        <?php endif; ?>

        <!-- Divider -->
        <div class="ddu-sidebar__divider"><span><?php _e('Navigation','petslist'); ?></span></div>

        <!-- Main nav -->
        <nav class="ddu-sidebar__nav">
            <?php foreach ($nav['main'] as $key => $item) :
                if (!empty($item['hide_no_sub']) && !$sub) continue;
                $active = $tab === $key;
            ?>
            <a href="<?php echo esc_url(dd_dashboard_url($key)); ?>"
               class="ddu-sidebar__nav-item <?php echo $active ? 'ddu-sidebar__nav-item--active' : ''; ?>">
                <span class="ddu-sidebar__nav-icon"><?php echo dd_nav_icon($item['icon']); ?></span>
                <span class="ddu-sidebar__nav-label"><?php echo esc_html($item['label']); ?></span>
                <?php if ($item['badge'] > 0) : ?>
                <span class="ddu-sidebar__nav-badge"><?php echo $item['badge']; ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>

            <!-- Directory link -->
            <a href="<?php echo esc_url(dd_dog_directory_url()); ?>"
               class="ddu-sidebar__nav-item" target="_blank">
                <span class="ddu-sidebar__nav-icon"><?php echo dd_nav_icon('dir'); ?></span>
                <span class="ddu-sidebar__nav-label"><?php _e('Browse Directory','petslist'); ?></span>
                <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 10px; margin-left: auto; opacity: 0.4;"></i>
            </a>
        </nav>

        <!-- Divider -->
        <div class="ddu-sidebar__divider"><span><?php _e('Account','petslist'); ?></span></div>

        <!-- Account nav -->
        <nav class="ddu-sidebar__nav">
            <?php foreach ($nav['account'] as $key => $item) :
                $active = $tab === $key;
            ?>
            <a href="<?php echo esc_url(dd_dashboard_url($key)); ?>"
               class="ddu-sidebar__nav-item <?php echo $active ? 'ddu-sidebar__nav-item--active' : ''; ?>">
                <span class="ddu-sidebar__nav-icon"><?php echo dd_nav_icon($item['icon']); ?></span>
                <span class="ddu-sidebar__nav-label"><?php echo esc_html($item['label']); ?></span>
            </a>
            <?php endforeach; ?>
        </nav>

        <!-- Sidebar footer -->
        <div class="ddu-sidebar__footer">
            <?php if ($sub) : ?>
            <div class="ddu-sidebar__sub-chip">
                <i class="fa-solid fa-star" style="font-size: 11px; margin-right: 4px;"></i>
                <span><?php echo esc_html($sub->plan_name); ?> · <?php printf(__('Exp %s','petslist'), date('M j',strtotime($sub->expires_at))); ?></span>
            </div>
            <?php endif; ?>
            <a href="<?php echo esc_url(wp_logout_url(dd_login_url())); ?>" class="ddu-sidebar__nav-item ddu-sidebar__nav-item--logout">
                <span class="ddu-sidebar__nav-icon"><?php echo dd_nav_icon('logout'); ?></span>
                <span class="ddu-sidebar__nav-label"><?php _e('Sign Out','petslist'); ?></span>
            </a>
        </div>

    </aside><!-- /sidebar -->

    <!-- ── MAIN CONTENT ── -->
    <main class="ddu-main" id="ddu-main">

        <!-- Top bar -->
        <header class="ddu-topbar">
            <div class="ddu-topbar__left">
                <button class="ddu-topbar__menu-btn" id="ddu-mobile-menu" aria-label="Menu">
                    <i class="fa-solid fa-bars" style="font-size: 18px;"></i>
                </button>
                <nav class="ddu-topbar__breadcrumb">
                    <a href="<?php echo esc_url(dd_dashboard_url()); ?>"><?php _e('Dashboard','petslist'); ?></a>
                    <?php
                    $tab_labels = array_merge(
                        array_map(fn($v)=>$v['label'], $nav['main']),
                        array_map(fn($v)=>$v['label'], $nav['account'])
                    );
                    if ($tab !== 'home' && isset($tab_labels[$tab])) :
                    ?>
                    <i class="fa-solid fa-angle-right" style="font-size: 10px; margin: 0 6px; opacity: 0.5;"></i>
                    <span><?php echo esc_html($tab_labels[$tab]); ?></span>
                    <?php endif; ?>
                </nav>
            </div>
            <div class="ddu-topbar__right">
                <?php if (!$sub) : ?>
                <a href="<?php echo esc_url(dd_pricing_url()); ?>" class="ddu-topbar__upgrade-chip">
                    ⚡ <?php _e('Upgrade','petslist'); ?>
                </a>
                <?php endif; ?>
                <div class="ddu-topbar__avatar-btn">
                    <?php echo get_avatar($uid, 32, '', '', ['class'=>'ddu-topbar__avatar']); ?>
                </div>
            </div>
        </header>

        <!-- No-sub alert strip -->
        <?php if (!$sub) : ?>
        <div class="ddu-alert-strip">
            <span><?php echo dd_nav_icon('alert'); ?></span>
            <span><?php _e('You don\'t have an active subscription. Some features are limited.','petslist'); ?></span>
            <a href="<?php echo esc_url(dd_pricing_url()); ?>" class="ddu-alert-strip__cta"><?php _e('View Plans →','petslist'); ?></a>
        </div>
        <?php endif; ?>

        <!-- Tab content -->
        <div class="ddu-content">
            <?php
            $tab_file = get_template_directory() . '/template-parts/dashboard/tab-' . $tab . '.php';
            if (file_exists($tab_file)) {
                include $tab_file;
            } else {
                include get_template_directory() . '/template-parts/dashboard/tab-home.php';
            }
            ?>
        </div>

    </main><!-- /main -->

</div><!-- /ddu-shell -->
