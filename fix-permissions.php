<?php
$base = '/home/comp/public_html/dog/wp-content/';
$plugins = $base . 'plugins';

echo '<pre>';

// Check current state
echo 'plugins exists: ' . (file_exists($plugins) ? 'YES' : 'NO') . "\n";
echo 'plugins is dir: ' . (is_dir($plugins) ? 'YES' : 'NO') . "\n";
echo 'wp-content writable: ' . (is_writable($base) ? 'YES' : 'NO') . "\n";
echo 'current user: ' . get_current_user() . "\n";

// List wp-content contents
echo "\nwp-content contents:\n";
foreach (scandir($base) as $item) {
    if ($item != '.' && $item != '..') {
        echo $item . ' - ' . substr(sprintf('%o', fileperms($base . $item)), -4) . "\n";
    }
}

// Create plugins dir if missing
if (!is_dir($plugins)) {
    if (mkdir($plugins, 0755, true)) {
        echo "\nCreated plugins directory OK\n";
    } else {
        echo "\nFAILED to create plugins directory\n";
    }
} else {
    chmod($plugins, 0755);
    echo "\nplugins already exists, chmod 755 done\n";
}

echo '</pre>';
unlink(__FILE__);
echo 'Script deleted.';
?>
