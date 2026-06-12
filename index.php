<?php

/**
 * StaticMD - Haupteinstiegspunkt
 * Verarbeitet alle Frontend-Anfragen
 */

// Autoloader
require_once __DIR__ . '/system/autoload.php';

// Load configuration
$config = require_once __DIR__ . '/config.php';

// Error reporting (configured by config.php in production)
if ($config['system']['debug'] ?? false) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
}

// Set timezone
date_default_timezone_set($config['system']['timezone']);

// HTTPS detection (supports proxies)
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

// Session configuration for frontend (must match admin settings)
$timeout = $config['admin']['session_timeout'] ?? 86400;
ini_set('session.gc_maxlifetime', $timeout);
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 1000);
ini_set('session.use_strict_mode', '1');

// Start session with consistent cookie settings
session_set_cookie_params([
    'lifetime' => $timeout,  // 24h cookie lifetime matches session timeout
    'path' => '/',
    'httponly' => true,
    'secure' => $isHttps,
    'samesite' => 'Strict'
]);
session_start();

// Store current page URL in session (for return after admin logout)
// Exclude asset requests (favicon, images, CSS, JS, etc.)
$requestUri = $_SERVER['REQUEST_URI'];
if (!preg_match('/\.(ico|png|jpg|jpeg|gif|svg|css|js|woff|woff2|ttf|eot)$/i', parse_url($requestUri, PHP_URL_PATH))) {
    $_SESSION['last_frontend_url'] = $requestUri;
}

// Load settings
$settingsFile = __DIR__ . '/system/settings.json';
$settings = [];
if (file_exists($settingsFile)) {
    $settings = json_decode(file_get_contents($settingsFile), true);
}
$language = $settings['language'] ?? ($config['admin']['language'] ?? 'en');
// Initialize I18n
\StaticMD\Core\I18n::init($language, __DIR__ . '/system/lang');

use StaticMD\Core\Application;

try {
    // Initialize and run application
    $app = new Application($config);
    $app->run();
} catch (Exception $e) {
    // Error handling
    http_response_code(500);
    echo "<h1>Error</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    
    // In development: Show full stack trace
    if (isset($config['system']['debug']) && $config['system']['debug']) {
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
}
