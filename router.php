<?php
/**
 * PHP built-in server router for WordPress (local dev).
 *
 * The live site lives in the /dog subfolder, so locally we serve everything
 * under the same /dog prefix to keep routes identical. WordPress core files
 * physically live in this directory (the doc root), so we strip the /dog
 * prefix to find the real file, then:
 *   - execute it if it's a PHP file (wp-admin, wp-login.php, etc.)
 *   - stream it if it's a static asset (css/js/images/fonts)
 *   - otherwise hand the request to WordPress (index.php) for routing.
 */

$base = '/dog';
$uri  = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Map the public URI to a path relative to this directory (the doc root).
if ($uri === $base || $uri === $base . '/') {
    $path = '/';
} elseif (strpos($uri, $base . '/') === 0) {
    $path = substr($uri, strlen($base)); // e.g. /wp-content/uploads/x.png
} else {
    $path = $uri; // requests without the /dog prefix (back-compat)
}

$file = realpath(__DIR__ . $path);

// Resolve directory requests to their index.php (e.g. /dog/wp-admin/).
if ($file !== false && is_dir($file) && is_file($file . '/index.php')) {
    $file = $file . '/index.php';
}

// Security: never serve anything outside the doc root.
$docroot = realpath(__DIR__);
if ($file !== false && is_file($file) && strpos($file, $docroot) === 0 && $path !== '/') {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    if ($ext === 'php') {
        chdir(dirname($file));
        $_SERVER['SCRIPT_FILENAME'] = $file;
        $_SERVER['SCRIPT_NAME']     = $uri;
        $_SERVER['PHP_SELF']        = $uri;
        require $file;
        return true;
    }

    $mimes = array(
        'css'   => 'text/css',
        'js'    => 'application/javascript',
        'mjs'   => 'application/javascript',
        'json'  => 'application/json',
        'xml'   => 'application/xml',
        'html'  => 'text/html',
        'htm'   => 'text/html',
        'txt'   => 'text/plain',
        'svg'   => 'image/svg+xml',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'webp'  => 'image/webp',
        'avif'  => 'image/avif',
        'ico'   => 'image/x-icon',
        'bmp'   => 'image/bmp',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
        'otf'   => 'font/otf',
        'eot'   => 'application/vnd.ms-fontobject',
        'mp4'   => 'video/mp4',
        'webm'  => 'video/webm',
        'mp3'   => 'audio/mpeg',
        'pdf'   => 'application/pdf',
        'map'   => 'application/json',
    );
    if (isset($mimes[$ext])) {
        header('Content-Type: ' . $mimes[$ext]);
    }
    header('Content-Length: ' . filesize($file));
    readfile($file);
    return true;
}

// Everything else is a WordPress (pretty-permalink) request.
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/index.php';
require __DIR__ . '/index.php';
