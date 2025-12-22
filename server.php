<?php
// Simple router for PHP built-in server to serve Laravel
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// If the requested resource exists as a file within public/, let the server handle it
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false;
}

// Otherwise, forward to Laravel's front controller
require __DIR__ . '/public/index.php';
