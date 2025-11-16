<?php
/**
 * Sicherer Assets Handler für StaticMD
 * Liefert Font-Dateien, Favicons und andere Assets aus /public/assets/ aus
 * Implementiert umfassende Sicherheitschecks
 */

// Security Headers setzen
require_once __DIR__ . '/system/core/SecurityHeaders.php';
use StaticMD\Core\SecurityHeaders;
SecurityHeaders::setAllSecurityHeaders('frontend');

// Asset-Pfad aus URL extrahieren
$assetPath = $_GET['asset'] ?? '';

// Umfassende Sicherheitsvalidierung
function validateAssetPath(string $path): array {
    $errors = [];
    
    // 1. Leer-Check
    if (empty($path)) {
        $errors[] = 'Leerer Asset-Pfad';
        return ['valid' => false, 'errors' => $errors];
    }
    
    // 2. Längen-Check (DoS-Schutz)
    if (strlen($path) > 255) {
        $errors[] = 'Asset-Pfad zu lang';
        return ['valid' => false, 'errors' => $errors];
    }
    
    // 3. URL-Dekodierung (mehrfach für Double-Encoding)
    $decodedPath = urldecode($path);
    $decodedPath = urldecode($decodedPath);
    
    // 4. Path-Traversal-Schutz (erweitert)
    $dangerousPatterns = [
        '..',           // Standard Path-Traversal
        './',           // Relative Pfade
        '/../',         // Versteckte Traversal
        '\\',           // Windows-Pfade
        '%2e%2e',       // URL-kodierte ..
        '%2f',          // URL-kodierte /
        '%5c',          // URL-kodierte \
        'php://',       // PHP-Streams
        'file://',      // File-URLs
        'data://',      // Data-URLs
        'http://',      // HTTP-URLs
        'https://',     // HTTPS-URLs
        'ftp://',       // FTP-URLs
    ];
    
    $lowerPath = strtolower($decodedPath);
    foreach ($dangerousPatterns as $pattern) {
        if (strpos($lowerPath, strtolower($pattern)) !== false) {
            $errors[] = "Gefährliches Pattern gefunden: $pattern";
        }
    }
    
    // 5. Erlaubte Zeichen-Whitelist (alphanumerisch + sicher)
    if (!preg_match('/^[a-zA-Z0-9._\/\-]+$/', $decodedPath)) {
        $errors[] = 'Unerlaubte Zeichen im Asset-Pfad';
    }
    
    // 6. Führende/absolute Pfade verhindern
    if (strpos($decodedPath, '/') === 0) {
        $errors[] = 'Absolute Pfade nicht erlaubt';
    }
    
    // 7. Maximale Verschachtelungstiefe
    $parts = explode('/', $decodedPath);
    if (count($parts) > 5) {
        $errors[] = 'Asset-Pfad zu tief verschachtelt (max. 5 Ebenen)';
    }
    
    // 8. Leere Pfad-Teile
    foreach ($parts as $part) {
        if (empty($part)) {
            $errors[] = 'Leere Pfad-Teile gefunden';
            break;
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'sanitized_path' => $decodedPath
    ];
}

// Asset-Pfad validieren
$validation = validateAssetPath($assetPath);
if (!$validation['valid']) {
    error_log('Assets Security Violation: ' . implode(', ', $validation['errors']) . ' - Path: ' . $assetPath);
    http_response_code(404);
    exit('Asset not found');
}

$sanitizedPath = $validation['sanitized_path'];

// Vollständiger Pfad zur Asset-Datei - unterstützt sowohl assets/ als auch images/
if (strpos($sanitizedPath, 'images/') === 0) {
    // Bilder aus /public/images/ ausliefern
    $assetsDir = __DIR__ . '/public/';
    $fullPath = $assetsDir . $sanitizedPath;
} else {
    // Standard-Assets aus /public/assets/ ausliefern
    $assetsDir = __DIR__ . '/public/assets/';
    $fullPath = $assetsDir . $sanitizedPath;
}

// Realpath-Validierung (finale Sicherheitsprüfung)
$realPath = realpath($fullPath);
$realAssetsDir = realpath($assetsDir);

// Zusätzliche Validierung für images-Pfade
$publicDir = realpath(__DIR__ . '/public/');
$isValidPath = false;

if ($realPath && $realAssetsDir && strpos($realPath, $realAssetsDir) === 0) {
    $isValidPath = true;
} elseif (strpos($sanitizedPath, 'images/') === 0 && $realPath && $publicDir && strpos($realPath, $publicDir) === 0) {
    // Zusätzliche Validierung für images/ Pfade
    $isValidPath = true;
}

if (!$isValidPath) {
    error_log('Assets Security: Path-Traversal verhindert - Path: ' . $fullPath);
    http_response_code(404);
    exit('Asset not found');
}

// Prüfen ob Datei existiert und reguläre Datei ist
if (!file_exists($realPath) || !is_file($realPath)) {
    http_response_code(404);
    exit('Asset not found');
}

// Dateigröße prüfen (DoS-Schutz: max 50MB)
$maxFileSize = 50 * 1024 * 1024; // 50MB
if (filesize($realPath) > $maxFileSize) {
    error_log('Assets Security: Datei zu groß - Path: ' . $realPath);
    http_response_code(413); // Payload Too Large
    exit('File too large');
}

// Dateiendung extrahieren und validieren
$extension = strtolower(pathinfo($sanitizedPath, PATHINFO_EXTENSION));

// Strenge Dateitype-Whitelist
$allowedTypes = [
    // Fonts
    'woff' => 'font/woff',
    'woff2' => 'font/woff2',
    'ttf' => 'font/truetype',
    'otf' => 'font/opentype',
    'eot' => 'application/vnd.ms-fontobject',
    
    // Bilder
    'ico' => 'image/x-icon',
    'png' => 'image/png',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif' => 'image/gif',
    'webp' => 'image/webp',
    
    // SVG (mit Vorsicht)
    'svg' => 'image/svg+xml',
    
    // Stylesheets und Scripts (nur für Assets)
    'css' => 'text/css',
    'js' => 'application/javascript',
    
    // Dokumente (begrenzt)
    'pdf' => 'application/pdf',
    'txt' => 'text/plain'
];

// Extension validieren
if (!isset($allowedTypes[$extension])) {
    error_log('Assets Security: Unerlaubter Dateityp - Extension: ' . $extension . ', Path: ' . $realPath);
    http_response_code(403);
    exit('File type not allowed');
}

$mimeType = $allowedTypes[$extension];

// Zusätzliche MIME-Type-Validierung
$detectedMimeType = mime_content_type($realPath);
$validMimeTypes = [
    'font/woff', 'font/woff2', 'font/truetype', 'font/opentype', 'application/vnd.ms-fontobject',
    'image/x-icon', 'image/png', 'image/jpeg', 'image/gif', 'image/webp', 'image/svg+xml',
    'text/css', 'application/javascript', 'text/javascript',
    'application/pdf', 'text/plain'
];

if (!in_array($detectedMimeType, $validMimeTypes) && !in_array($mimeType, $validMimeTypes)) {
    error_log('Assets Security: MIME-Type nicht erlaubt - Detected: ' . $detectedMimeType . ', Expected: ' . $mimeType . ', Path: ' . $realPath);
    http_response_code(403);
    exit('MIME type not allowed');
}

// Sicherheits-Header für Assets
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// SVG-spezifische Sicherheitsmaßnahmen
if ($extension === 'svg') {
    // CSP für SVG (verhindert Script-Ausführung)
    header('Content-Security-Policy: default-src \'none\'; style-src \'unsafe-inline\'; img-src data:');
    
    // SVG-Inhalt auf schädliche Elemente prüfen
    $svgContent = file_get_contents($realPath);
    $dangerousSvgPatterns = [
        '<script', '<object', '<embed', '<iframe', '<link', '<meta',
        'javascript:', 'data:text/html', 'vbscript:', 'onload=', 'onerror='
    ];
    
    foreach ($dangerousSvgPatterns as $pattern) {
        if (stripos($svgContent, $pattern) !== false) {
            error_log('Assets Security: Gefährlicher SVG-Inhalt - Pattern: ' . $pattern . ', Path: ' . $realPath);
            http_response_code(403);
            exit('SVG content not allowed');
        }
    }
}

// Cache-Header setzen (differenziert nach Dateityp)
if (in_array($extension, ['woff', 'woff2', 'ttf', 'otf', 'eot'])) {
    // Fonts: 1 Jahr Cache
    $expires = 60 * 60 * 24 * 365;
    header('Access-Control-Allow-Origin: *'); // CORS für Fonts
} elseif (in_array($extension, ['css', 'js'])) {
    // CSS/JS: 1 Monat Cache
    $expires = 60 * 60 * 24 * 30;
} elseif (in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'ico'])) {
    // Bilder: 6 Monate Cache
    $expires = 60 * 60 * 24 * 180;
} else {
    // Andere: 1 Tag Cache
    $expires = 60 * 60 * 24;
}

header('Cache-Control: public, max-age=' . $expires);
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');

// ETag für Caching-Optimierung
$etag = md5_file($realPath) . '-' . filemtime($realPath);
header('ETag: "' . $etag . '"');

// If-None-Match prüfen
if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === '"' . $etag . '"') {
    http_response_code(304); // Not Modified
    exit;
}

// Content-Type und Datei ausliefern
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($realPath));

// Range-Requests für große Dateien unterstützen
if (isset($_SERVER['HTTP_RANGE']) && filesize($realPath) > 1024 * 1024) { // > 1MB
    $range = $_SERVER['HTTP_RANGE'];
    if (preg_match('/bytes=(\d+)-(\d*)/', $range, $matches)) {
        $start = intval($matches[1]);
        $end = $matches[2] ? intval($matches[2]) : filesize($realPath) - 1;
        
        if ($start <= $end && $end < filesize($realPath)) {
            header('HTTP/1.1 206 Partial Content');
            header('Accept-Ranges: bytes');
            header('Content-Range: bytes ' . $start . '-' . $end . '/' . filesize($realPath));
            header('Content-Length: ' . ($end - $start + 1));
            
            $file = fopen($realPath, 'rb');
            fseek($file, $start);
            echo fread($file, $end - $start + 1);
            fclose($file);
            exit;
        }
    }
}

// Normale Datei-Ausgabe
readfile($realPath);

// Logging für Monitoring (optional)
error_log('Assets Success: ' . $sanitizedPath . ' - Size: ' . filesize($realPath) . ' - Type: ' . $mimeType);
?>