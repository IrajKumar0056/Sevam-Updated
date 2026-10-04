<?php
// router.php - Router script for PHP built-in development web server
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// If static file exists, return false to let PHP serve it
$staticFile = __DIR__ . $uri;
if ($uri !== '/' && file_exists($staticFile) && !is_dir($staticFile)) {
    // Check if it's not a php file
    if (pathinfo($staticFile, PATHINFO_EXTENSION) !== 'php') {
        return false;
    }
}

// If direct PHP file exists
if (file_exists($staticFile) && is_file($staticFile) && pathinfo($staticFile, PATHINFO_EXTENSION) === 'php') {
    require $staticFile;
    return true;
}

// If directory requested, check for index.php
if (is_dir($staticFile)) {
    $dirIndex = rtrim($staticFile, '/') . '/index.php';
    if (file_exists($dirIndex)) {
        require $dirIndex;
        return true;
    }
}

// If extension omitted (e.g. /login instead of /login.php)
if (file_exists($staticFile . '.php')) {
    require $staticFile . '.php';
    return true;
}

// Root path
if ($uri === '/' || $uri === '') {
    require __DIR__ . '/index.php';
    return true;
}

// 404 Fallback
http_response_code(404);
echo "<!DOCTYPE html><html><head><title>404 Not Found - Sevam</title><link rel='stylesheet' href='/css/style.css'></head><body><div class='container' style='padding:5rem 1rem; text-align:center;'><h1>404 Page Not Found</h1><p style='margin:1rem 0;'>The requested page does not exist on the Sevam platform.</p><a href='/' class='btn btn-primary'>Return to Home</a></div></body></html>";
return true;
