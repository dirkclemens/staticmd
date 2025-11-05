<?php

// DEBUG: Enable PHP error messages
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/**
 * StaticMD - Haupteinstiegspunkt
 * Verarbeitet alle Frontend-Anfragen
 */

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include autoloader (if Composer is installed)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Load configuration
$config = require_once __DIR__ . '/config.php';

// Set timezone
date_default_timezone_set($config['system']['timezone']);

// Start session
session_start();

// Include core classes
require_once __DIR__ . '/system/core/I18n.php';
require_once __DIR__ . '/system/core/Router.php';
require_once __DIR__ . '/system/core/MarkdownParser.php';
require_once __DIR__ . '/system/core/ContentLoader.php';
require_once __DIR__ . '/system/core/TemplateEngine.php';
require_once __DIR__ . '/system/core/Application.php';

// Load settings
$settingsFile = __DIR__ . '/system/settings.json';
$settings = [];
if (file_exists($settingsFile)) {
    $settings = json_decode(file_get_contents($settingsFile), true);
}
$language = $settings['language'] ?? ($config['admin']['language'] ?? 'de');
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