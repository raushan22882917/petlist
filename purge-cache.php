<?php
/**
 * 1-Click Server & LiteSpeed Cache Flush for studs4you.com
 */
$key = $_GET['key'] ?? '';
if ( $key !== 'studs2026' ) {
    die( '<h3 style="color:red;font-family:sans-serif;">Access Denied. Provide ?key=studs2026</h3>' );
}

define( 'WP_USE_THEMES', false );
require_once __DIR__ . '/wp-load.php';

header( 'X-LiteSpeed-Purge: *' );
header( 'Cache-Control: no-cache, must-revalidate, max-age=0' );

// 1. Purge LiteSpeed Cache via API
if ( class_exists( '\LiteSpeed\Purge' ) ) {
    \LiteSpeed\Purge::purge_all();
}
if ( function_exists( 'litespeed_purge_all' ) ) {
    litespeed_purge_all();
}
do_action( 'litespeed_purge_all' );

// 2. Clear WordPress transients and object cache
wp_cache_flush();
global $wpdb;
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE ('_transient_%')" );
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE ('_site_transient_%')" );

// 3. Clear file system cache directories if accessible
$lscache_dir = '/home/studs4you/lscache';
if ( is_dir( $lscache_dir ) ) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator( $lscache_dir, RecursiveDirectoryIterator::SKIP_DOTS ),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ( $files as $fileinfo ) {
        if ( $fileinfo->isDir() ) {
            @rmdir( $fileinfo->getRealPath() );
        } else {
            @unlink( $fileinfo->getRealPath() );
        }
    }
}

// 4. Ensure breeds are synced
if ( function_exists( 'dd_ensure_default_breeds' ) ) {
    delete_option( 'dd_breeds_seeded_v4' );
    dd_ensure_default_breeds();
}

echo '<div style="font-family:sans-serif;max-width:600px;margin:40px auto;padding:25px;background:#f0fdf4;border:1px solid #86efac;border-radius:8px;">';
echo '<h2 style="color:#166534;margin-top:0;">✅ All Caches Flushed Successfully!</h2>';
echo '<p style="color:#15803d;">LiteSpeed server cache, WordPress transients, and taxonomy sync have been refreshed.</p>';
echo '<p><a href="https://studs4you.com/" style="display:inline-block;padding:10px 20px;background:#16a34a;color:#fff;text-decoration:none;border-radius:5px;font-weight:bold;">Visit Homepage &rarr;</a></p>';
echo '</div>';
