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

// Session starten für Admin-Auth Check
session_start();

// Nur für eingeloggte Admins verfügbar
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized', 'debug' => 'Not logged in']);
    exit;
}

// Update admin activity
$_SESSION['admin_last_activity'] = time();

// Load configuration
$config = require_once __DIR__ . '/../../config.php';

// Include required classes
require_once __DIR__ . '/../core/MarkdownParser.php';
require_once __DIR__ . '/../processors/ShortcodeProcessor.php';
require_once __DIR__ . '/../utilities/FrontMatterParser.php';
require_once __DIR__ . '/../utilities/TitleGenerator.php';
require_once __DIR__ . '/../utilities/UnicodeNormalizer.php';
require_once __DIR__ . '/../utilities/UrlHelper.php';
require_once __DIR__ . '/../renderers/FolderOverviewRenderer.php';
require_once __DIR__ . '/../renderers/BlogListRenderer.php';
require_once __DIR__ . '/../core/I18n.php';
require_once __DIR__ . '/../core/NavigationBuilder.php';
require_once __DIR__ . '/../core/ContentLoader.php';
require_once __DIR__ . '/AdminAuth.php';

use StaticMD\Core\MarkdownParser;
use StaticMD\Processors\ShortcodeProcessor;
use StaticMD\Utilities\FrontMatterParser;
use StaticMD\Core\ContentLoader;
use StaticMD\Admin\AdminAuth;

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
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Preview rendering failed',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
}
