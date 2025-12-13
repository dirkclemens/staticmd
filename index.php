<?php

// DEBUG: Enable PHP error messages
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/**
 * StaticMD - Haupteinstiegspunkt
 * Verarbeitet alle Frontend-Anfragen
 */

// Include autoloader (if Composer is installed)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Load configuration
$config = require_once __DIR__ . '/config.php';

// Set timezone
date_default_timezone_set($config['system']['timezone']);

// Session configuration for frontend (must match admin settings)
$timeout = $config['admin']['session_timeout'] ?? 86400;
ini_set('session.gc_maxlifetime', $timeout);
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 1000);

// Start session with consistent cookie settings
session_set_cookie_params([
    'lifetime' => $timeout,  // 24h cookie lifetime matches session timeout
    'path' => '/',
    'httponly' => true,
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'samesite' => 'Strict'
]);
session_start();

// Update admin activity on every frontend request when logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $_SESSION['admin_last_activity'] = time();
}

// Store current page URL in session (for return after admin logout)
// Exclude asset requests (favicon, images, CSS, JS, etc.)
$requestUri = $_SERVER['REQUEST_URI'];
if (!preg_match('/\.(ico|png|jpg|jpeg|gif|svg|css|js|woff|woff2|ttf|eot)$/i', parse_url($requestUri, PHP_URL_PATH))) {
    $_SESSION['last_frontend_url'] = $requestUri;
}

// Include core classes
require_once __DIR__ . '/system/core/I18n.php';
require_once __DIR__ . '/system/core/Router.php';
require_once __DIR__ . '/system/core/MarkdownParser.php';

// Include utilities
require_once __DIR__ . '/system/utilities/FrontMatterParser.php';
require_once __DIR__ . '/system/utilities/UnicodeNormalizer.php';
require_once __DIR__ . '/system/utilities/TitleGenerator.php';
require_once __DIR__ . '/system/utilities/UrlHelper.php';

// Include renderers
require_once __DIR__ . '/system/renderers/FolderOverviewRenderer.php';
require_once __DIR__ . '/system/renderers/BlogListRenderer.php';

// Include processors
require_once __DIR__ . '/system/processors/ShortcodeProcessor.php';

// Include admin classes (for auth checks in shortcodes)
require_once __DIR__ . '/system/admin/AdminAuth.php';

// Include core classes (continued)
require_once __DIR__ . '/system/core/NavigationBuilder.php';
require_once __DIR__ . '/system/core/ContentLoader.php';
require_once __DIR__ . '/system/core/TemplateEngine.php';
require_once __DIR__ . '/system/core/Application.php';

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