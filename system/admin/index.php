<?php
/**
 * StaticMD Admin - Main Entry Point
 * 
 * Handles all administrative operations and routes requests to
 * appropriate controllers. Configures session, security, and i18n.
 */

// Autoloader and configuration
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
}

$config = require_once __DIR__ . '/../../config.php';

// Include admin classes
require_once __DIR__ . '/AdminAuth.php';
require_once __DIR__ . '/AdminController.php';
require_once __DIR__ . '/../core/I18n.php';
require_once __DIR__ . '/../core/SecurityHeaders.php';

// Include utilities (needed by AdminController for ContentLoader)
require_once __DIR__ . '/../utilities/FrontMatterParser.php';
require_once __DIR__ . '/../utilities/UnicodeNormalizer.php';
require_once __DIR__ . '/../utilities/TitleGenerator.php';
require_once __DIR__ . '/../utilities/UrlHelper.php';

// Include renderers
require_once __DIR__ . '/../renderers/FolderOverviewRenderer.php';
require_once __DIR__ . '/../renderers/BlogListRenderer.php';

// Include processors
require_once __DIR__ . '/../processors/ShortcodeProcessor.php';

// Include NavigationBuilder
require_once __DIR__ . '/../core/NavigationBuilder.php';

// Set security headers (admin context)
use StaticMD\Core\SecurityHeaders;
SecurityHeaders::setAllSecurityHeaders('admin');

// Session configuration (must be set BEFORE session_start())
$timeout = $config['admin']['session_timeout'];

// Set PHP session lifetime parameters
// gc_maxlifetime: How long session data is kept server-side
ini_set('session.gc_maxlifetime', $timeout);

// gc_probability/gc_divisor: Make garbage collection less aggressive
// Default is 1/100, we set to 1/1000 to reduce premature cleanup
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 1000);

// Cache settings
ini_set('session.cache_expire', ceil($timeout / 60));

// Cookie settings: 0 = until browser closes (we handle timeout in PHP)
// This ensures the cookie survives browser sleep/wake cycles
session_set_cookie_params([
    'lifetime' => 0,  // Session cookie (survives browser restarts in most browsers)
    'path' => '/',
    'httponly' => true,
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'samesite' => 'Strict'
]);

// Start session
session_start();

use StaticMD\Admin\AdminAuth;
use StaticMD\Admin\AdminController;
use StaticMD\Core\I18n;

// Error reporting (configured by config.php in production)
if ($config['system']['debug'] ?? false) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

try {
    // Load language from settings (default: en)
    $settingsFile = $config['paths']['system'] . '/settings.json';
    $language = 'en';
    if (file_exists($settingsFile)) {
        $settings = json_decode(file_get_contents($settingsFile), true) ?: [];
        if (!empty($settings['language'])) {
            $language = $settings['language'];
        }
    }

    // Initialize i18n system
    I18n::init($language, $config['paths']['system'] . '/lang');
    if (!function_exists('__')) {
        function __(string $key, array $placeholders = []): string {
            return I18n::t($key, $placeholders);
        }
    }

    // Initialize admin controller
    $auth = new AdminAuth($config);
    $controller = new AdminController($config, $auth);
    
    // Handle incoming request
    $controller->handleRequest();
    
} catch (Exception $e) {
    // Display error template
    http_response_code(500);
    include __DIR__ . '/templates/error.php';
}