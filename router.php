<?php
/**
 * PHP built-in server router for WordPress
 * Handles WordPress URL routing for the built-in PHP server
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// If the file exists directly, serve it
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Otherwise, route to WordPress index
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/index.php';
include __DIR__ . '/index.php';
