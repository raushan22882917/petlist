<?php
/**
 * Dog Directory Setup Script
 * Run once: http://localhost:8000/?dd_setup=1&dd_key=dogdir2024
 * Delete after running.
 */

$_SERVER['HTTP_HOST'] = 'localhost:8000';
$_SERVER['REQUEST_URI'] = '/';
require dirname(__DIR__, 4) . '/wp-load.php';

if ( empty($_GET['dd_setup']) || $_GET['dd_key'] !== 'dogdir2024' ) {
    die('Not authorized.');
}

echo '<pre>';

// 1. Fix dd_page_dashboard to correct page
update_option('dd_page_dashboard', 4687);
echo "dd_page_dashboard -> 4687 (" . get_the_title(4687) . ")\n";

// 2. Create forgot password page if missing
if (!get_option('dd_page_forgot')) {
    $id = wp_insert_post([
        'post_title'   => 'Forgot Password',
        'post_content' => '[dd_forgot]',
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'post_name'    => 'dog-forgot-password',
    ]);
    update_option('dd_page_forgot', $id);
    echo "Created forgot page: $id -> " . get_permalink($id) . "\n";
} else {
    echo "Forgot page already set: " . get_option('dd_page_forgot') . "\n";
}

// 3. Ensure DB tables exist and have seed data
$sub = new \RadiusTheme\Petslist\DogDirectory\Subscription();
$sub->create_subscription_tables();
echo "DB tables verified.\n";

// 4. Flush rewrite rules
flush_rewrite_rules(true);
echo "Rewrite rules flushed.\n";

// 5. Verify all page options
echo "\n=== PAGE OPTIONS ===\n";
$opts = [
    'dd_page_login', 'dd_page_register', 'dd_page_pricing',
    'dd_page_checkout', 'dd_page_dashboard', 'dd_page_forgot'
];
foreach ($opts as $o) {
    $id = get_option($o);
    $title = $id ? get_the_title($id) : 'NOT SET';
    $url   = $id ? get_permalink($id) : '—';
    $content = $id ? get_post_field('post_content', $id) : '—';
    echo sprintf("%-30s ID=%-6s %-22s %s | content=%s\n", $o, $id, $title, $url, $content);
}

// 6. Test dd_is_dashboard_page via the page ID
echo "\n=== FUNCTION CHECK ===\n";
echo "dd_dashboard_url(): " . dd_dashboard_url() . "\n";
echo "dd_login_url(): "     . dd_login_url() . "\n";
echo "dd_pricing_url(): "   . dd_pricing_url() . "\n";

// 7. Check plans
global $wpdb;
$plans = $wpdb->get_results("SELECT id, name, price, is_active FROM {$wpdb->prefix}dd_plans");
echo "\n=== PLANS ===\n";
foreach ($plans as $p) {
    echo "ID={$p->id} {$p->name} \${$p->price} active={$p->is_active}\n";
}

echo "\n✅ Setup complete! Delete this file: /dd-setup.php\n";
echo '</pre>';
