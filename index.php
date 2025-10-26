<?php
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
require_once __DIR__ . '/system/core/Router.php';
require_once __DIR__ . '/system/core/MarkdownParser.php';
require_once __DIR__ . '/system/core/ContentLoader.php';
require_once __DIR__ . '/system/core/TemplateEngine.php';
require_once __DIR__ . '/system/core/Application.php';

use StaticMD\Core\Application;

try {
    // Anwendung initialisieren und ausführen
    $app = new Application($config);
    $app->run();
} catch (Exception $e) {
    // Fehlerbehandlung
    http_response_code(500);
    echo "<h1>Fehler</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    
    // In der Entwicklung: Vollständigen Stack-Trace anzeigen
    if (isset($config['system']['debug']) && $config['system']['debug']) {
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
}