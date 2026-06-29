<?php
define('WP_USE_THEMES', false);
require('./wp-load.php');

global $wpdb;
echo "Table Prefix: " . $wpdb->prefix . "\n";
echo "Show on Front: " . get_option('show_on_front') . "\n";
echo "Page on Front: " . get_option('page_on_front') . "\n";
echo "Page 48 exists: " . ($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE ID = 48") ? 'Yes' : 'No') . "\n";
echo "Page 49 exists: " . ($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE ID = 49") ? 'Yes' : 'No') . "\n";
echo "Theme active: " . get_option('stylesheet') . "\n";
?>
