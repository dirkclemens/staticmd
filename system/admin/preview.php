<?php
/**
 * Live Preview Endpoint
 * 
 * Rendert Markdown-Content server-seitig mit vollständiger
 * MarkdownParser + ShortcodeProcessor Pipeline für 1:1 Preview
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display, we'll capture them
ini_set('log_errors', 1);

// Autoloader and configuration
require_once __DIR__ . '/../autoload.php';
$config = require_once __DIR__ . '/../../config.php';

// Session starten mit konsistenter Konfiguration
$timeout = $config['admin']['session_timeout'];
ini_set('session.gc_maxlifetime', $timeout);
session_set_cookie_params([
    'lifetime' => $timeout,
    'path' => '/',
    'httponly' => true,
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'samesite' => 'Strict'
]);
session_start();

$auth = new \StaticMD\Admin\AdminAuth($config);
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

use StaticMD\Core\MarkdownParser;
use StaticMD\Processors\ShortcodeProcessor;
use StaticMD\Utilities\FrontMatterParser;
use StaticMD\Core\ContentLoader;

// Get POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['content'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No content provided']);
    exit;
}

$markdownContent = $data['content'];
$currentRoute = $data['route'] ?? '/';

try {
    // Parse Front Matter
    $frontMatterParser = new FrontMatterParser();
    $result = $frontMatterParser->parse($markdownContent);
    $content = $result['content'];
    $meta = $result['meta'];
    
    // Initialize ContentLoader (needed for shortcodes)
    $contentLoader = new ContentLoader($config);
    
    // Parse Markdown
    $parser = new MarkdownParser();
    $html = $parser->parse($content);
    
    // Process Shortcodes
    $shortcodeProcessor = new ShortcodeProcessor($config, $contentLoader);
    $html = $shortcodeProcessor->process($html, $currentRoute);
    
    // Wrap in preview container with basic styling
    $previewHtml = '<div class="preview-wrapper">' . $html . '</div>';
    
    // Return HTML
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'html' => $previewHtml,
        'meta' => $meta
    ]);
    
} catch (Throwable $e) {
    error_log('StaticMD preview error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Preview rendering failed',
        'message' => $e->getMessage()
    ]);
}
