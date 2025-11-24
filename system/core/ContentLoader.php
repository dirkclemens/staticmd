<?php

namespace StaticMD\Core;

use StaticMD\Utilities\FrontMatterParser;
use StaticMD\Utilities\UnicodeNormalizer;
use StaticMD\Utilities\TitleGenerator;
use StaticMD\Utilities\UrlHelper;
use StaticMD\Processors\ShortcodeProcessor;
use StaticMD\Renderers\FolderOverviewRenderer;
use StaticMD\Renderers\BlogListRenderer;

/**
 * ContentLoader-Klasse (Refactored)
 * Fokus auf: File I/O, Content Loading, Folder Scanning
 * HTML-Rendering und Shortcodes wurden in separate Klassen ausgelagert
 */
class ContentLoader
{
    private array $config;
    private MarkdownParser $parser;
    private ShortcodeProcessor $shortcodeProcessor;
    private NavigationBuilder $navigationBuilder;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->parser = new MarkdownParser();
        $this->shortcodeProcessor = new ShortcodeProcessor($config, $this);
        $this->navigationBuilder = new NavigationBuilder($config);
    }

    /**
     * Lädt Content für eine bestimmte Route
     */
    public function load(string $route): ?array
    {
        if (!$this->validateRouteDepth($route)) {
            return null;
        }
        
        $contentPath = $this->findContentFile($route);
        
        if ($contentPath === null || !is_readable($contentPath)) {
            $folderOverview = $this->generateFolderOverview($route);
            if ($folderOverview !== null) {
                return $folderOverview;
            }
            return null;
        }

        $rawContent = file_get_contents($contentPath);
        $parsed = FrontMatterParser::parse($rawContent);
        
        // Process shortcodes BEFORE Markdown parsing
        $contentWithShortcodes = $this->shortcodeProcessor->process($parsed['content'], $route);
        
        // Convert Markdown to HTML
        $htmlContent = $this->parser->parse($contentWithShortcodes);
        
        // Process accordion shortcodes after Markdown parsing
        $htmlContent = $this->processAccordionShortcodes($htmlContent);
        
        return [
            'title' => $parsed['meta']['title'] ?? '',
            'content' => $htmlContent,
            'meta' => $parsed['meta'],
            'route' => $route,
            'file_path' => $contentPath,
            'visibility' => $parsed['meta']['Visibility'] ?? $parsed['meta']['visibility'] ?? 'public'
        ];
    }
    
    /**
     * Validiert Route-Tiefe
     */
    private function validateRouteDepth(string $route): bool
    {
        $parts = explode('/', trim($route, '/'));
        $maxDepth = 10;
        
        if (count($parts) > $maxDepth) {
            return false;
        }
        
        foreach ($parts as $part) {
            if (empty(trim($part)) || strlen($part) > 100) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Generiert automatische Ordner-Übersichtsseite
     */
    private function generateFolderOverview(string $route): ?array
    {
        $contentDir = $this->config['paths']['content'];
        $folderPath = $contentDir . '/' . $route;
        
        if (!is_dir($folderPath)) {
            return null;
        }
        
        $files = [];
        $subfolders = [];
        $extension = $this->config['markdown']['file_extension'];
        
        $iterator = new \DirectoryIterator($folderPath);
        foreach ($iterator as $item) {
            if ($item->isDot()) {
                continue;
            }
            
            if ($item->isDir()) {
                $subfolderData = $this->getSubfolderData($item, $route);
                if ($subfolderData) {
                    $subfolders[] = $subfolderData;
                }
                continue;
            }
            
            if (!$item->isFile()) {
                continue;
            }
            
            $filename = $item->getFilename();
            if (!str_ends_with($filename, $extension) || $filename === 'index' . $extension) {
                continue;
            }
            
            $fileData = $this->getFileData($item, $route, $extension);
            if ($fileData) {
                $files[] = $fileData;
            }
        }
        
        usort($files, function($a, $b) {
            return strcasecmp($a['title'], $b['title']);
        });
        
        $folderTitle = TitleGenerator::fromFolderPath($route);
        $html = FolderOverviewRenderer::renderFull($folderTitle, $files, $subfolders, $route);
        
        return [
            'title' => I18n::t('core.folder_overview_title', ['folder' => $folderTitle]),
            'content' => $html,
            'meta' => [
                'title' => I18n::t('core.folder_overview_title', ['folder' => $folderTitle]),
                'description' => I18n::t('core.folder_overview_description', ['folder' => $folderTitle]),
                'folder_overview' => true,
                'folder_route' => $route,
                'file_count' => count($files),
                'subfolder_count' => count($subfolders)
            ],
            'route' => $route,
            'file_path' => $folderPath,
            'modified' => filemtime($folderPath)
        ];
    }
    
    /**
     * Holt Daten für einen Unterordner
     */
    private function getSubfolderData(\DirectoryIterator $item, string $parentRoute): ?array
    {
        $subfolderName = $item->getFilename();
        $subfolderRoute = empty($parentRoute) ? $subfolderName : $parentRoute . '/' . $subfolderName;
        $subfolderPath = $item->getPathname();
        
        if (!$this->hasMarkdownFiles($subfolderPath)) {
            return null;
        }
        
        return [
            'name' => $subfolderName,
            'route' => $subfolderRoute,
            'title' => $this->getSubfolderTitle($subfolderPath, $subfolderName),
            'description' => $this->getSubfolderDescription($subfolderPath),
            'file_count' => $this->countMarkdownFiles($subfolderPath)
        ];
    }
    
    /**
     * Holt Daten für eine Datei
     */
    private function getFileData(\DirectoryIterator $item, string $parentRoute, string $extension): ?array
    {
        $filename = $item->getFilename();
        $filePath = $item->getPathname();
        $fileRoute = empty($parentRoute) 
            ? substr($filename, 0, -strlen($extension)) 
            : $parentRoute . '/' . substr($filename, 0, -strlen($extension));
        
        $rawContent = file_get_contents($filePath);
        $parsed = FrontMatterParser::parse($rawContent);
        
        $visibility = $parsed['meta']['Visibility'] ?? $parsed['meta']['visibility'] ?? 'public';
        if ($visibility === 'private' && !$this->shouldShowPrivateContent()) {
            return null;
        }
        
        $title = $parsed['meta']['Title'] ?? $parsed['meta']['title'] 
            ?? TitleGenerator::fromRoute(basename($fileRoute));
        
        $description = $parsed['meta']['description'] ?? $this->extractPreview($parsed['content']);
        
        return [
            'title' => $title,
            'route' => $fileRoute,
            'description' => $description,
            'modified' => filemtime($filePath),
            'tags' => $parsed['meta']['Tag'] ?? $parsed['meta']['tags'] ?? '',
            'author' => $parsed['meta']['Author'] ?? $parsed['meta']['author'] ?? '',
            'visibility' => $visibility
        ];
    }
    
    /**
     * Extrahiert Vorschau aus Content
     */
    private function extractPreview(string $content, int $maxLength = 150): string
    {
        $contentLines = explode("\n", trim($content));
        foreach ($contentLines as $line) {
            $line = trim($line);
            if (!empty($line) && !str_starts_with($line, '#')) {
                $preview = substr($line, 0, $maxLength);
                if (strlen($line) > $maxLength) {
                    $preview .= '...';
                }
                return $preview;
            }
        }
        return '';
    }

    /**
     * Verarbeitet Accordion-Shortcodes (nach Markdown-Parsing)
     */
    private function processAccordionShortcodes(string $content): string
    {
        $patterns = [
            '/(?:<p>)?\[(spoilerstart)\s+([a-zA-Z0-9_-]+)\s+"([^"]+)"\](?:<\/p>)?([\s\S]*?)(?:<p>)?\[spoilerstop(?:\s+[^\]]*?)?\](?:<\/p>)?/i',
            '/(?:<p>)?\[(accordionstart)\s+([a-zA-Z0-9_-]+)\s+"([^"]+)"\](?:<\/p>)?([\s\S]*?)(?:<p>)?\[accordionstop(?:\s+[^\]]*?)?\](?:<\/p>)?/i'
        ];
        
        foreach ($patterns as $pattern) {
            $content = preg_replace_callback($pattern, function($matches) {
                $id = $matches[2];
                $title = $matches[3];
                $accordionContent = trim($matches[4]);
                return $this->generateAccordionHTML($id, $title, $accordionContent);
            }, $content);
        }
        
        return $content;
    }

    /**
     * Generiert Bootstrap 5 Accordion HTML
     */
    private function generateAccordionHTML(string $id, string $title, string $content): string
    {
        $accordionId = 'accordion-' . $id;
        $collapseId = 'collapse-' . $id;
        $headingId = 'heading-' . $id;
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $htmlContent = trim($content);
        
        return sprintf('
<div class="accordion mb-3" id="%s">
  <div class="accordion-item">
    <h3 class="accordion-header" id="%s">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#%s" aria-expanded="false" aria-controls="%s">
        %s
      </button>
    </h3>
    <div id="%s" class="accordion-collapse collapse" aria-labelledby="%s" data-bs-parent="#%s">
      <div class="accordion-body">
        %s
      </div>
    </div>
  </div>
</div>
',
            $accordionId, $headingId, $collapseId, $collapseId, $safeTitle,
            $collapseId, $headingId, $accordionId, $htmlContent
        );
    }

    /**
     * Sucht Content-Datei für Route
     */
    public function findContentFile(string $route): ?string
    {
        $contentDir = $this->config['paths']['content'];
        $extension = $this->config['markdown']['file_extension'];
        
        $decodedRoute = UnicodeNormalizer::decodeAndNormalize($route);
        
        $routeVariants = [
            trim($decodedRoute, '/'),
            trim($route, '/'),
            trim(urldecode($route), '/')
        ];
        
        foreach ($routeVariants as $routeVariant) {
            $possiblePaths = [
                $contentDir . '/' . $routeVariant . $extension,
                $contentDir . '/' . $routeVariant . '/index' . $extension,
                $contentDir . '/' . $routeVariant . '/page' . $extension
            ];
            
            foreach ($possiblePaths as $path) {
                if (file_exists($path) && is_readable($path)) {
                    return $path;
                }
            }
            
            // Extended search with normalization
            $foundPath = $this->searchNormalizedFile($routeVariant, $extension);
            if ($foundPath) {
                return $foundPath;
            }
        }
        
        // Homepage
        if ($route === 'index' || $route === '') {
            $indexPaths = [
                $contentDir . '/home' . $extension,
                $contentDir . '/index' . $extension
            ];
            
            foreach ($indexPaths as $path) {
                if (file_exists($path) && is_readable($path)) {
                    return $path;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Sucht Datei mit Unicode-Normalisierung
     */
    private function searchNormalizedFile(string $routeVariant, string $extension): ?string
    {
        $contentDir = $this->config['paths']['content'];
        $dir = dirname($contentDir . '/' . $routeVariant);
        $filename = basename($routeVariant) . $extension;
        
        if (!is_dir($dir)) {
            return null;
        }
        
        $files = scandir($dir);
        if (!$files) {
            return null;
        }
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            if (UnicodeNormalizer::normalizeForComparison($file) === 
                UnicodeNormalizer::normalizeForComparison($filename)) {
                $foundPath = $dir . '/' . $file;
                if (file_exists($foundPath) && is_readable($foundPath)) {
                    return $foundPath;
                }
            }
        }
        
        return null;
    }

    /**
     * Listet alle Content-Dateien auf
     */
    public function listAll(): array
    {
        $contentDir = $this->config['paths']['content'];
        $extension = $this->config['markdown']['file_extension'];
        
        $files = [];
        $this->scanDirectory($contentDir, $contentDir, $extension, $files);

        usort($files, function($a, $b) {
            return $b['modified'] <=> $a['modified'];
        });
        
        return $files;
    }

    /**
     * Rekursive Verzeichnis-Suche
     */
    private function scanDirectory(string $dir, string $baseDir, string $extension, array &$files): void
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $items = scandir($dir);
        if ($items === false) {
            return;
        }
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            
            $fullPath = $dir . '/' . $item;
            
            if (is_dir($fullPath)) {
                $this->scanDirectory($fullPath, $baseDir, $extension, $files);
            } elseif (is_file($fullPath) && str_ends_with($item, $extension)) {
                $relativePath = str_replace($baseDir . '/', '', $fullPath);
                $route = str_replace($extension, '', $relativePath);
                
                $files[] = [
                    'route' => $route,
                    'file' => $relativePath,
                    'path' => $fullPath,
                    'modified' => filemtime($fullPath),
                    'size' => filesize($fullPath)
                ];
            }
        }
    }
    
    /**
     * Hilfsmethoden für Ordner-Scanning
     */
    private function hasMarkdownFiles(string $folderPath): bool
    {
        if (!is_dir($folderPath)) {
            return false;
        }
        
        $extension = $this->config['markdown']['file_extension'];
        $files = scandir($folderPath);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $filePath = $folderPath . '/' . $file;
            if (is_file($filePath) && str_ends_with($file, $extension)) {
                return true;
            }
        }
        
        return false;
    }
    
    private function countMarkdownFiles(string $folderPath): int
    {
        if (!is_dir($folderPath)) {
            return 0;
        }
        
        $count = 0;
        $extension = $this->config['markdown']['file_extension'];
        $files = scandir($folderPath);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $filePath = $folderPath . '/' . $file;
            if (is_file($filePath) && str_ends_with($file, $extension)) {
                $count++;
            }
        }
        
        return $count;
    }
    
    private function getSubfolderTitle(string $folderPath, string $fallbackName): string
    {
        $extension = $this->config['markdown']['file_extension'];
        $indexFile = $folderPath . '/index' . $extension;
        
        if (file_exists($indexFile) && is_readable($indexFile)) {
            $content = file_get_contents($indexFile);
            $parsed = FrontMatterParser::parse($content);
            $title = $parsed['meta']['Title'] ?? $parsed['meta']['title'] ?? null;
            if (!empty($title)) {
                return $title;
            }
        }
        
        return TitleGenerator::fromFilename($fallbackName);
    }
    
    private function getSubfolderDescription(string $folderPath): string
    {
        $extension = $this->config['markdown']['file_extension'];
        $indexFile = $folderPath . '/index' . $extension;
        
        if (file_exists($indexFile) && is_readable($indexFile)) {
            $content = file_get_contents($indexFile);
            $parsed = FrontMatterParser::parse($content);
            $description = $parsed['meta']['description'] ?? '';
            
            if (!empty($description)) {
                return $description;
            }
            
            return $this->extractPreview($parsed['content'], 100);
        }
        
        return '';
    }
    
    private function shouldShowPrivateContent(): bool
    {
        $adminAuthFile = $this->config['paths']['system'] . '/admin/AdminAuth.php';
        if (file_exists($adminAuthFile)) {
            require_once $adminAuthFile;
            $adminAuth = new \StaticMD\Admin\AdminAuth($this->config);
            return $adminAuth->isLoggedIn();
        }
        return false;
    }
    
    /**
     * Public API für ShortcodeProcessor
     */
    public function getFolderFilesPublic(string $route, int $limit): array
    {
        return $this->getFolderFiles($route, $limit);
    }
    
    public function getFolderTagsPublic(string $route, int $limit): array
    {
        return $this->getFolderTags($route, $limit);
    }
    
    public function getDirectSubfoldersPublic(string $route, int $limit): array
    {
        return $this->getDirectSubfolders($route, $limit);
    }
    
    public function getBlogListPublic(string $route, int $perPage, int $currentPage): array
    {
        return $this->getBlogList($route, $perPage, $currentPage);
    }
    
    /**
     * Holt Dateien aus einem Ordner
     */
    private function getFolderFiles(string $route, int $limit): array
    {
        $contentDir = $this->config['paths']['content'];
        $folderPath = $contentDir . '/' . $route;
        
        if (!is_dir($folderPath)) {
            return [];
        }
        
        $files = [];
        $extension = $this->config['markdown']['file_extension'];
        $count = 0;
        
        $iterator = new \DirectoryIterator($folderPath);
        foreach ($iterator as $file) {
            if ($count >= $limit || $file->isDot() || !$file->isFile()) {
                continue;
            }
            
            $filename = $file->getFilename();
            if (!str_ends_with($filename, $extension) || $filename === 'index' . $extension) {
                continue;
            }
            
            $filePath = $file->getPathname();
            $baseName = substr($filename, 0, -strlen($extension));
            $fileRoute = empty($route) ? $baseName : $route . '/' . $baseName;
            
            $rawContent = file_get_contents($filePath);
            $parsed = FrontMatterParser::parse($rawContent);
            
            $title = $parsed['meta']['Title'] ?? $parsed['meta']['title'] 
                ?? TitleGenerator::fromRoute(basename($fileRoute));
            
            $files[] = [
                'title' => $title,
                'route' => $fileRoute,
                'tags' => $parsed['meta']['Tag'] ?? $parsed['meta']['tags'] ?? '',
                'author' => $parsed['meta']['Author'] ?? $parsed['meta']['author'] ?? '',
                'modified' => filemtime($filePath)
            ];
            
            $count++;
        }
        
        usort($files, function($a, $b) {
            return strcasecmp($a['title'], $b['title']);
        });
        
        return $files;
    }

    /**
     * Sammelt alle Tags aus einem Ordner
     */
    private function getFolderTags(string $route, int $limit): array
    {
        $files = $this->getFolderFiles($route, $limit);
        $tagCounts = [];
        
        foreach ($files as $file) {
            if (!empty($file['tags'])) {
                $fileTags = array_map('trim', explode(',', $file['tags']));
                foreach ($fileTags as $tag) {
                    $tag = trim($tag);
                    if (!empty($tag)) {
                        $tagCounts[$tag] = ($tagCounts[$tag] ?? 0) + 1;
                    }
                }
            }
        }
        
        uksort($tagCounts, function($a, $b) {
            return strcasecmp($a, $b);
        });
        
        return $tagCounts;
    }

    /**
     * Sammelt direkte Unterordner
     */
    private function getDirectSubfolders(string $folderPath, int $limit = 1000): array
    {
        $contentPath = $this->config['paths']['content'];
        $fullPath = $contentPath . '/' . $folderPath;
        
        if (!is_dir($fullPath)) {
            return [];
        }
        
        $subfolders = [];
        $iterator = new \DirectoryIterator($fullPath);
        
        foreach ($iterator as $item) {
            if ($item->isDot() || !$item->isDir()) {
                continue;
            }
            
            $folderName = $item->getFilename();
            
            if ($this->hasMarkdownFiles($item->getPathname())) {
                $title = $this->getFolderTitle($item->getPathname());
                $subfolders[] = [
                    'name' => $folderName,
                    'title' => $title,
                    'path' => $folderPath ? $folderPath . '/' . $folderName : $folderName
                ];
            }
        }
        
        usort($subfolders, function($a, $b) {
            return strcasecmp($a['title'], $b['title']);
        });
        
        return array_slice($subfolders, 0, $limit);
    }

    /**
     * Extrahiert Ordner-Titel
     */
    private function getFolderTitle(string $folderPath): string
    {
        $extension = $this->config['markdown']['file_extension'];
        $indexFile = $folderPath . '/index' . $extension;
        
        if (file_exists($indexFile) && is_readable($indexFile)) {
            $content = file_get_contents($indexFile);
            $parsed = FrontMatterParser::parse($content);
            
            if (!empty($parsed['meta']['Title'])) {
                return $parsed['meta']['Title'];
            }
            
            if (preg_match('/^#\s+(.+)$/m', $parsed['content'], $matches)) {
                return trim($matches[1]);
            }
        }
        
        return TitleGenerator::fromFolderPath($folderPath);
    }

    /**
     * Holt Blog-Einträge mit Pagination
     */
    private function getBlogList(string $route, int $perPage, int $currentPage): array
    {
        $contentDir = $this->config['paths']['content'];
        $folderPath = $contentDir . '/' . $route;
        
        if (!is_dir($folderPath)) {
            return ['items' => [], 'total' => 0, 'pages' => 0];
        }
        
        $files = [];
        $extension = $this->config['markdown']['file_extension'];
        
        $iterator = new \DirectoryIterator($folderPath);
        foreach ($iterator as $file) {
            if ($file->isDot() || !$file->isFile()) {
                continue;
            }
            
            $filename = $file->getFilename();
            if (!str_ends_with($filename, $extension) || $filename === 'index' . $extension) {
                continue;
            }
            
            $filePath = $file->getPathname();
            $baseName = substr($filename, 0, -strlen($extension));
            $fileRoute = empty($route) ? $baseName : $route . '/' . $baseName;
            
            $rawContent = file_get_contents($filePath);
            $parsed = FrontMatterParser::parse($rawContent);
            
            $layout = $parsed['meta']['Layout'] ?? $parsed['meta']['layout'] ?? '';
            $isInBlogFolder = strpos($route, 'blog') !== false;
            
            if ($layout !== 'blog' && !$isInBlogFolder) {
                continue;
            }
            
            $visibility = $parsed['meta']['Visibility'] ?? $parsed['meta']['visibility'] ?? 'public';
            if ($visibility === 'private' && !$this->shouldShowPrivateContent()) {
                continue;
            }
            
            $title = $parsed['meta']['Title'] ?? $parsed['meta']['title'] 
                ?? TitleGenerator::fromRoute(basename($fileRoute));
            $date = $parsed['meta']['Date'] ?? $parsed['meta']['date'] ?? '';
            $description = $parsed['meta']['Description'] ?? $parsed['meta']['description'] 
                ?? $this->extractPreview($parsed['content'], 200);
            
            $files[] = [
                'title' => $title,
                'route' => $fileRoute,
                'date' => $date,
                'sort_date' => $this->parseDate($date, filemtime($filePath)),
                'author' => $parsed['meta']['Author'] ?? $parsed['meta']['author'] ?? '',
                'description' => $description,
                'tags' => $parsed['meta']['Tag'] ?? $parsed['meta']['tags'] ?? '',
                'modified' => filemtime($filePath),
                'visibility' => $visibility
            ];
        }
        
        usort($files, function($a, $b) {
            return $b['sort_date'] <=> $a['sort_date'];
        });
        
        $total = count($files);
        $totalPages = ceil($total / $perPage);
        $offset = ($currentPage - 1) * $perPage;
        $items = array_slice($files, $offset, $perPage);
        
        return [
            'items' => $items,
            'total' => $total,
            'pages' => $totalPages,
            'current_page' => $currentPage,
            'per_page' => $perPage
        ];
    }
    
    /**
     * Parst Datum aus String
     */
    private function parseDate(string $dateString, int $fallbackTimestamp): int
    {
        if (empty($dateString)) {
            return $fallbackTimestamp;
        }
        
        $formats = ['Y-m-d', 'd.m.Y', 'Y/m/d', 'Y-m-d H:i:s'];
        
        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $dateString);
            if ($date !== false) {
                return $date->getTimestamp();
            }
        }
        
        return $fallbackTimestamp;
    }
    
    /**
     * Delegation an NavigationBuilder
     */
    public function getNavigationOrder(): array
    {
        return $this->navigationBuilder->getNavigationOrder();
    }
    
    public function getBreadcrumbs(string $route): array
    {
        return $this->navigationBuilder->getBreadcrumbs($route, [$this, 'findContentFile']);
    }
}
