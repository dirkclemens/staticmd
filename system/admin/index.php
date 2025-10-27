<?php
// DEBUG: PHP-Fehlermeldungen aktivieren
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/**
 * StaticMD Admin - Haupteinstiegspunkt
 * Verwaltet alle Admin-Funktionen
 */

// Session starten
session_start();

// Autoloader und Konfiguration laden
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
}

$config = require_once __DIR__ . '/../../config.php';

// Admin-Klassen einbinden
require_once __DIR__ . '/AdminAuth.php';
require_once __DIR__ . '/AdminController.php';

use StaticMD\Admin\AdminAuth;
use StaticMD\Admin\AdminController;

// Fehlerberichterstattung
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
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