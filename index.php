<?php

// DEBUG: PHP-Fehlermeldungen aktivieren
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/**
 * StaticMD - Haupteinstiegspunkt
 * Verarbeitet alle Frontend-Anfragen
 */

// Fehlerberichterstattung für Entwicklung
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Autoloader einbinden (falls Composer installiert)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Konfiguration laden
$config = require_once __DIR__ . '/config.php';

// Timezone setzen
date_default_timezone_set($config['system']['timezone']);

// Session starten
session_start();


// Core-Klassen einbinden
require_once __DIR__ . '/system/core/I18n.php';
require_once __DIR__ . '/system/core/Router.php';
require_once __DIR__ . '/system/core/MarkdownParser.php';
require_once __DIR__ . '/system/core/ContentLoader.php';
require_once __DIR__ . '/system/core/TemplateEngine.php';
require_once __DIR__ . '/system/core/Application.php';

// Settings laden
$settingsFile = __DIR__ . '/system/settings.json';
$settings = [];
if (file_exists($settingsFile)) {
    $settings = json_decode(file_get_contents($settingsFile), true);
}
$language = $settings['language'] ?? ($config['admin']['language'] ?? 'de');
// I18n initialisieren
\StaticMD\Core\I18n::init($language, __DIR__ . '/system/lang');

use StaticMD\Core\Application;

try {
    // Anwendung initialisieren und ausführen
    $app = new Application($config);
    $app->run();
} catch (Exception $e) {
    // Fehlerbehandlung
    http_response_code(500);
    echo "<h1>Error</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    
    // In der Entwicklung: Vollständigen Stack-Trace anzeigen
    if (isset($config['system']['debug']) && $config['system']['debug']) {
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
}