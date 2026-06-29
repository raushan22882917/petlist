<?php
$base = '/home/comp/public_html/dog/wp-content/';
echo '<pre>';

echo "=== THEMES ===\n";
foreach (scandir($base . 'themes') as $item) {
    if ($item != '.' && $item != '..') {
        $style = $base . 'themes/' . $item . '/style.css';
        echo $item . ' - style.css: ' . (file_exists($style) ? 'YES' : 'NO') . "\n";
    }
}

echo "\n=== PLUGINS ===\n";
if (is_dir($base . 'plugins')) {
    foreach (scandir($base . 'plugins') as $item) {
        if ($item != '.' && $item != '..') {
            echo $item . "\n";
        }
    }
} else {
    echo "plugins folder missing!\n";
}

echo '</pre>';
unlink(__FILE__);
?>
