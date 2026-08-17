<?php
// One-click Setup script for studs4you.com
$key = $_GET['key'] ?? '';
if ($key !== 'studs2026') {
    die('<h3 style="color:red;font-family:sans-serif;">Access Denied. Provide key=studs2026</h3>');
}

echo '<div style="font-family:sans-serif; max-width:700px; margin:40px auto; padding:25px; border:1px solid #ddd; border-radius:8px; background:#f9f9f9;">';
echo '<h2 style="color:#2271b1;margin-top:0;">Studs4You WordPress Sync & Import</h2>';

// 1. Check wp-config.php
$wp_config_file = __DIR__ . '/wp-config.php';
if (!file_exists($wp_config_file)) {
    die('<p style="color:red;">Error: wp-config.php not found in current directory.</p></div>');
}

require_once $wp_config_file;

// Check table prefix in wp-config.php and update to wp5h_ if needed
$wp_config_content = file_get_contents($wp_config_file);
if (strpos($wp_config_content, "\$table_prefix = 'wp5h_';") === false) {
    $updated_config = preg_replace("/\\\$table_prefix\s*=\s*['\"][^'\"]+['\"];/", "\$table_prefix = 'wp5h_';", $wp_config_content);
    if ($updated_config && $updated_config !== $wp_config_content) {
        file_put_contents($wp_config_file, $updated_config);
        echo '<p style="color:green;">✓ Updated $table_prefix to <b>wp5h_</b> in wp-config.php</p>';
    }
} else {
    echo '<p style="color:green;">✓ $table_prefix is already set to <b>wp5h_</b></p>';
}

// 2. Connect to Database
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($mysqli->connect_error) {
    die('<p style="color:red;">Database Connection Failed: ' . $mysqli->connect_error . '</p></div>');
}
echo '<p style="color:green;">✓ Connected to database: <b>' . htmlspecialchars(DB_NAME) . '</b></p>';

// 3. Read SQL file
$sql_file = __DIR__ . '/studs4you_db.sql';
if (!file_exists($sql_file)) {
    // Check push_to_live.sql as fallback
    $sql_file = __DIR__ . '/push_to_live.sql';
}

if (!file_exists($sql_file)) {
    die('<p style="color:red;">Error: studs4you_db.sql not found.</p></div>');
}

$sql = file_get_contents($sql_file);
echo '<p>Importing database dump (' . number_format(strlen($sql)) . ' bytes)...</p>';

// Execute multi_query
$mysqli->multi_query($sql);
$queries = 0;
do {
    $queries++;
    if ($result = $mysqli->store_result()) {
        $result->free();
    }
} while ($mysqli->more_results() && $mysqli->next_result());

if ($mysqli->errno) {
    echo '<p style="color:orange;">Notice during import: ' . htmlspecialchars($mysqli->error) . '</p>';
} else {
    echo '<p style="color:green;font-weight:bold;">✓ Database imported successfully! (' . $queries . ' statements executed)</p>';
}

// 4. Update siteurl and home to https://studs4you.com
$mysqli->query("UPDATE `wp5h_options` SET `option_value`='https://studs4you.com' WHERE `option_name` IN ('siteurl', 'home')");
echo '<p style="color:green;">✓ Site URLs confirmed set to <b>https://studs4you.com</b></p>';

echo '<div style="margin-top:20px;padding:15px;background:#e7f5ea;border:1px solid #c2e5cb;border-radius:5px;">';
echo '<h4 style="margin:0 0 10px 0;color:#1e7e34;">All Done! 🎉</h4>';
echo '<p style="margin:0;"><a href="https://studs4you.com" target="_blank" style="color:#0073aa;font-weight:bold;">Visit Homepage &rarr;</a> | <a href="https://studs4you.com/wp-admin" target="_blank" style="color:#0073aa;font-weight:bold;">Go to Admin &rarr;</a></p>';
echo '</div>';

$mysqli->close();
echo '</div>';
