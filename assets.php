<?php
/**
 * Assets Handler für StaticMD
 * Liefert Font-Dateien, Favicons und andere Assets aus /public/assets/ aus
 */

// Asset-Pfad aus URL extrahieren
$assetPath = $_GET['asset'] ?? '';

// Sicherheitscheck: Keine Pfad-Traversal-Angriffe
if (empty($assetPath) || strpos($assetPath, '..') !== false || strpos($assetPath, '/') === 0) {
    http_response_code(404);
    exit('Asset not found');
}

// Vollständiger Pfad zur Asset-Datei
$fullPath = __DIR__ . '/public/assets/' . $assetPath;

// Prüfen ob Datei existiert
if (!file_exists($fullPath) || !is_file($fullPath)) {
    http_response_code(404);
    exit('Asset not found');
}

// MIME-Type basierend auf Dateiendung bestimmen
$extension = strtolower(pathinfo($assetPath, PATHINFO_EXTENSION));
$mimeTypes = [
    'woff' => 'font/woff',
    'woff2' => 'font/woff2',
    'ttf' => 'font/truetype',
    'otf' => 'font/opentype',
    'eot' => 'application/vnd.ms-fontobject',
    'ico' => 'image/x-icon',
    'png' => 'image/png',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif' => 'image/gif',
    'svg' => 'image/svg+xml',
    'css' => 'text/css',
    'js' => 'application/javascript'
];

$mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

// Cache-Header setzen (1 Jahr für Assets)
$expires = 60 * 60 * 24 * 365; // 1 Jahr
header('Cache-Control: public, max-age=' . $expires);
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');

// CORS-Header für Font-Dateien
if (in_array($extension, ['woff', 'woff2', 'ttf', 'otf', 'eot'])) {
    header('Access-Control-Allow-Origin: *');
}

// Content-Type und Datei ausliefern
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($fullPath));

// Datei ausgeben
readfile($fullPath);
?>