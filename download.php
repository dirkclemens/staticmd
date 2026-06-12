<?php
/**
 * Download Markdown Content
 * API-Endpoint zum Download des reinen Markdown-Contents ohne Front-Matter
 */

// Load configuration
$config = require_once __DIR__ . '/config.php';

// Include utilities
require_once __DIR__ . '/system/utilities/FrontMatterParser.php';
require_once __DIR__ . '/system/utilities/UnicodeNormalizer.php';
require_once __DIR__ . '/system/admin/AdminAuth.php';

use StaticMD\Utilities\FrontMatterParser;
use StaticMD\Utilities\UnicodeNormalizer;

// Get route parameter
$route = $_GET['route'] ?? '';

if (empty($route)) {
    http_response_code(400);
    echo json_encode(['error' => 'No route specified']);
    exit;
}

// Normalize route (handle Unicode/Umlauts) and strip path traversal sequences
$route = UnicodeNormalizer::normalize($route);
$route = str_replace(['..', './'], '', $route);
$route = trim($route, '/');

// Build file path
$contentPath = realpath($config['paths']['content']);
$possiblePaths = [
    $config['paths']['content'] . '/' . $route . '.md',
    $config['paths']['content'] . '/' . $route . '/index.md'
];

// Find the file
$filePath = null;
foreach ($possiblePaths as $path) {
    $realPath = realpath($path);
    // Containment check: resolved path must stay inside the content directory
    if ($realPath !== false && $contentPath !== false && str_starts_with($realPath, $contentPath . DIRECTORY_SEPARATOR)) {
        $filePath = $realPath;
        break;
    }
}

if (!$filePath) {
    http_response_code(404);
    echo json_encode(['error' => 'File not found']);
    exit;
}

// Read file content
$rawContent = file_get_contents($filePath);

// Enforce visibility: private pages are not downloadable by unauthenticated users
$meta = FrontMatterParser::extractMeta($rawContent);
$visibility = $meta['Visibility'] ?? $meta['visibility'] ?? 'public';
if ($visibility === 'private') {
    // Start session to check admin login state (matching index.php session config)
    $timeout = $config['admin']['session_timeout'] ?? 86400;
    ini_set('session.gc_maxlifetime', $timeout);
    session_set_cookie_params(['lifetime' => $timeout, 'path' => '/', 'httponly' => true, 'samesite' => 'Strict']);
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $adminAuth = new \StaticMD\Admin\AdminAuth($config);
    if (!$adminAuth->isLoggedIn()) {
        http_response_code(404);
        echo json_encode(['error' => 'File not found']);
        exit;
    }
}

// Extract content without front-matter
$markdownContent = FrontMatterParser::extractContent($rawContent);

// Generate filename from route
$filename = basename($route);
if (empty($filename) || $filename === 'index') {
    // Extract from front-matter title if available
    $meta = FrontMatterParser::extractMeta($rawContent);
    $filename = $meta['titleslug'] ?? $meta['title'] ?? 'page';
    // Sanitize filename
    $filename = preg_replace('/[^a-z0-9äöüß\s-]/i', '', $filename);
    $filename = preg_replace('/\s+/', '-', $filename);
    $filename = strtolower($filename);
}

$filename .= '.md';

// Set headers for download
header('Content-Type: text/markdown; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($markdownContent));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Output markdown content
echo $markdownContent;
exit;
