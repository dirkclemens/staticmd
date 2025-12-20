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

use StaticMD\Utilities\FrontMatterParser;
use StaticMD\Utilities\UnicodeNormalizer;

// Get route parameter
$route = $_GET['route'] ?? '';

if (empty($route)) {
    http_response_code(400);
    echo json_encode(['error' => 'No route specified']);
    exit;
}

// Normalize route (handle Unicode/Umlauts)
$route = UnicodeNormalizer::normalize($route);
$route = trim($route, '/');

// Build file path
$contentPath = $config['paths']['content'];
$possiblePaths = [
    $contentPath . '/' . $route . '.md',
    $contentPath . '/' . $route . '/index.md'
];

// Find the file
$filePath = null;
foreach ($possiblePaths as $path) {
    if (file_exists($path)) {
        $filePath = $path;
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
