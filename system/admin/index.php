<?php
// DEBUG: PHP-Fehlermeldungen aktivieren
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/**
 * StaticMD Admin - Haupteinstiegspunkt
 * Verwaltet alle Admin-Funktionen
 */

// Autoloader und Konfiguration laden
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
}

$config = require_once __DIR__ . '/../../config.php';

// Admin-Klassen einbinden
require_once __DIR__ . '/AdminAuth.php';
require_once __DIR__ . '/AdminController.php';
require_once __DIR__ . '/../core/I18n.php';
require_once __DIR__ . '/../core/SecurityHeaders.php';

// Security Headers setzen (Admin-Kontext)
use StaticMD\Core\SecurityHeaders;
SecurityHeaders::setAllSecurityHeaders('admin');

// Session-Konfiguration VOR session_start()
$timeout = $config['admin']['session_timeout'];
ini_set('session.gc_maxlifetime', $timeout);
ini_set('session.cookie_lifetime', $timeout);
ini_set('session.cache_expire', ceil($timeout / 60));

session_set_cookie_params([
    'lifetime' => $timeout,
    'path' => '/',
    'httponly' => true,
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'samesite' => 'Strict'
]);

// Session starten
session_start();

use StaticMD\Admin\AdminAuth;
use StaticMD\Admin\AdminController;
use StaticMD\Core\I18n;

// Fehlerberichterstattung
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Sprache aus Settings laden (Default: en)
    $settingsFile = $config['paths']['system'] . '/settings.json';
    $language = 'en';
    if (file_exists($settingsFile)) {
        $settings = json_decode(file_get_contents($settingsFile), true) ?: [];
        if (!empty($settings['language'])) {
            $language = $settings['language'];
        }
    }

    // I18n initialisieren
    I18n::init($language, $config['paths']['system'] . '/lang');
    if (!function_exists('__')) {
        function __(string $key, array $placeholders = []): string {
            return I18n::t($key, $placeholders);
        }
    }

    // Admin-Controller initialisieren
    $auth = new AdminAuth($config);
    $controller = new AdminController($config, $auth);
    
    // Request verarbeiten
    $controller->handleRequest();
    
} catch (Exception $e) {
    // Fehler-Template anzeigen
    http_response_code(500);
    include __DIR__ . '/templates/error.php';
}