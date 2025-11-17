<?php

namespace StaticMD\Core;

use Normalizer;

/**
 * ContentLoader-Klasse
 * Lädt und verarbeitet Markdown-Inhalte
 */
class ContentLoader
{
    private array $config;
    private MarkdownParser $parser;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->parser = new MarkdownParser();
    }
    
    /**
     * Encodiert URL-Pfade ohne die Slashes zu kodieren
     */
    private function encodeUrlPath(string $path): string
    {
        $parts = explode('/', $path);
        $encodedParts = array_map('rawurlencode', $parts);
        return implode('/', $encodedParts);
    }
    
    /**
     * Einfache Unicode-Normalisierung als Fallback
     */
    private function simpleUnicodeNormalize(string $text): string
    {
        // Convert common combined Unicode characters to simple ones
        // Use hex codes for combined characters
        $replacements = [
            "a\xCC\x88" => 'ä',  // ä (a + combining diaeresis)
            "o\xCC\x88" => 'ö',  // ö (o + combining diaeresis)
            "u\xCC\x88" => 'ü',  // ü (u + combining diaeresis)
            "A\xCC\x88" => 'Ä',  // Ä (A + combining diaeresis)
            "O\xCC\x88" => 'Ö',  // Ö (O + combining diaeresis)
            "U\xCC\x88" => 'Ü',  // Ü (U + combining diaeresis)
        ];
        
        foreach ($replacements as $combined => $simple) {
            $text = str_replace($combined, $simple, $text);
        }
        
        return $text;
    }

    /**
     * Lädt Content für eine bestimmte Route
     */
    public function load(string $route): ?array
    {
        // Route validation for deep nesting
        if (!$this->validateRouteDepth($route)) {
            return null;
        }
        
        $contentPath = $this->findContentFile($route);
        
        if ($contentPath === null || !is_readable($contentPath)) {
            // Check if it is a folder
            $folderOverview = $this->generateFolderOverview($route);
            if ($folderOverview !== null) {
                return $folderOverview;
            }
            return null;
        }

        $rawContent = file_get_contents($contentPath);
        
        // Separate front matter and content
        $parsed = $this->parseFrontMatter($rawContent);
        
        // Process shortcodes BEFORE Markdown parsing to prevent code-block conversion
        $contentWithShortcodes = $this->processShortcodes($parsed['content'], $route);
        
        // Convert Markdown to HTML
        $htmlContent = $this->parser->parse($contentWithShortcodes);
        
        // Process accordion shortcodes after Markdown parsing (they need HTML structure)
        $htmlContent = $this->processAccordionShortcodes($htmlContent);
        
        return [
            'title' => $parsed['meta']['title'] ?? '', //'Unbenannte Seite',
            'content' => $htmlContent,
            'meta' => $parsed['meta'],
            'route' => $route,
            'file_path' => $contentPath,
            'visibility' => $parsed['meta']['Visibility'] ?? $parsed['meta']['visibility'] ?? 'public'
        ];
    }
    
    /**
     * Validiert Route-Tiefe und verhindert zu tiefe Verschachtelung
     */
    private function validateRouteDepth(string $route): bool
    {
        $parts = explode('/', trim($route, '/'));
        $maxDepth = 10; // Maximal 10 Ebenen
        
        if (count($parts) > $maxDepth) {
            return false;
        }
        
        // Check for empty or problematic path parts
        foreach ($parts as $part) {
            if (empty(trim($part)) || strlen($part) > 100) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Generiert eine automatische Ordner-Übersichtsseite
     */
    private function generateFolderOverview(string $route): ?array
    {
        $contentDir = $this->config['paths']['content'];
        $folderPath = $contentDir . '/' . $route;
        
        // Check if folder exists
        if (!is_dir($folderPath)) {
            return null;
        }
        
        // Find all Markdown files and subfolders in the folder
        $files = [];
        $subfolders = [];
        $extension = $this->config['markdown']['file_extension'];
        
        $iterator = new \DirectoryIterator($folderPath);
        foreach ($iterator as $item) {
            if ($item->isDot()) {
                continue;
            }
            
            if ($item->isDir()) {
                // Handle subfolders
                $subfolderName = $item->getFilename();
                $subfolderRoute = empty($route) ? $subfolderName : $route . '/' . $subfolderName;
                
                // Check if subfolder contains Markdown files
                $subfolderPath = $item->getPathname();
                $hasFiles = $this->hasMarkdownFiles($subfolderPath);
                
                if ($hasFiles) {
                    $subfolders[] = [
                        'name' => $subfolderName,
                        'route' => $subfolderRoute,
                        'title' => $this->getSubfolderTitle($subfolderPath, $subfolderName),
                        'description' => $this->getSubfolderDescription($subfolderPath),
                        'file_count' => $this->countMarkdownFiles($subfolderPath)
                    ];
                }
                continue;
            }
            
            if (!$item->isFile()) {
                continue;
            }
            
            $filename = $item->getFilename();
            if (!str_ends_with($filename, $extension)) {
                continue;
            }
            
            // Skip index.md (would be the folder main page)
            if ($filename === 'index' . $extension) {
                continue;
            }
            
            $filePath = $item->getPathname();
            $fileRoute = empty($route) ? substr($filename, 0, -strlen($extension)) : $route . '/' . substr($filename, 0, -strlen($extension));
            
            // Read metadata from file
            $rawContent = file_get_contents($filePath);
            $parsed = $this->parseFrontMatter($rawContent);
            
            $title = $parsed['meta']['Title'] ?? $parsed['meta']['title'] ?? $this->generateTitle(basename($fileRoute));
            $description = $parsed['meta']['description'] ?? '';
            $visibility = $parsed['meta']['Visibility'] ?? $parsed['meta']['visibility'] ?? 'public';
            
            // Private Seiten ausblenden wenn nicht angemeldet
            if ($visibility === 'private' && !$this->shouldShowPrivateContent()) {
                continue;
            }
            
            // Erste Zeilen des Inhalts als Vorschau
            if (empty($description)) {
                $contentLines = explode("\n", trim($parsed['content']));
                $preview = '';
                foreach ($contentLines as $line) {
                    $line = trim($line);
                    if (!empty($line) && !str_starts_with($line, '#')) {
                        $preview = substr($line, 0, 150);
                        if (strlen($line) > 150) {
                            $preview .= '...';
                        }
                        break;
                    }
                }
                $description = $preview;
            }
            
            $files[] = [
                'title' => $title,
                'route' => $fileRoute,
                'description' => $description,
                'modified' => filemtime($filePath),
                'tags' => $parsed['meta']['Tag'] ?? $parsed['meta']['tags'] ?? '',
                'author' => $parsed['meta']['Author'] ?? $parsed['meta']['author'] ?? '',
                'visibility' => $visibility
            ];
        }
        
        // Sort alphabetically (case-insensitive)
        usort($files, function($a, $b) {
            return strcasecmp($a['title'], $b['title']);
        });
        
        // Generate HTML for overview page
        $folderTitle = str_replace(['/', '-', '_'], ' ', $route);
        $html = $this->generateFolderOverviewHTML($folderTitle, $files, $subfolders, $route);
        
        return [
            'title' => $folderTitle . ' - Übersicht',
            'content' => $html,
            'meta' => [
                'title' => $folderTitle . ' - Übersicht',
                'description' => 'Übersicht aller Seiten im Bereich ' . $folderTitle,
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
     * Generiert HTML für Ordner-Übersichtsseite
     */
    private function generateFolderOverviewHTML(string $folderTitle, array $files, array $subfolders, string $route): string
    {
        $html = '<div class="folder-overview">';
        
        // Header
        $html .= '<div class="overview-header mb-4">';
        $html .= '<h1><i class="bi bi-folder2-open me-2"></i>' . htmlspecialchars($folderTitle) . '</h1>';
        $totalItems = count($files) + count($subfolders);
        $html .= '<p class="lead">' . \StaticMD\Core\I18n::t('core.content_overview', ['pages' => count($files)]);
        if (count($subfolders) > 0) {
            $html .= ', ' . count($subfolders) . ' Unterordner';
        }
        $html .= ')</p>';
        $html .= '</div>';
        
        // Unterordner anzeigen (falls vorhanden)
        if (!empty($subfolders)) {
            $html .= '<div class="subfolders-section mb-5">';
            $html .= '<h2><i class="bi bi-folder me-2"></i>Unterordner</h2>';
            $html .= '<div class="row">';
            
            foreach ($subfolders as $subfolder) {
                $html .= '<div class="col-md-6 col-lg-4 mb-3">';
                $html .= '<div class="card h-100 border-primary border-opacity-25">';
                $html .= '<div class="card-body">';
                $html .= '<h5 class="card-title">';
                $html .= '<a href="/' . $this->encodeUrlPath($subfolder['route']) . '" class="text-decoration-none">';
                $html .= '<i class="bi bi-folder-fill me-2 text-primary"></i>';
                $html .= htmlspecialchars($subfolder['title']);
                $html .= '</a>';
                $html .= '</h5>';
                if (!empty($subfolder['description'])) {
                    $html .= '<p class="card-text">' . htmlspecialchars($subfolder['description']) . '</p>';
                }
                $html .= '<small class="text-muted">';
                $html .= '<i class="bi bi-files me-1"></i>';
                $html .= $subfolder['file_count'] . ' Seiten';
                $html .= '</small>';
                $html .= '</div>';
                $html .= '</div>';
                $html .= '</div>';
            }
            
            $html .= '</div>';
            $html .= '</div>';
        }
        
        // Dateien anzeigen
        if (empty($files)) {
            if (empty($subfolders)) {
                $html .= '<div class="alert alert-info">';
                $html .= '<i class="bi bi-info-circle me-2"></i>';
                $html .= 'In diesem Bereich sind noch keine Seiten vorhanden.';
                $html .= '</div>';
            }
        } else {
            $html .= '<div class="files-section">';
            $html .= '<h2><i class="bi bi-file-earmark-text me-2"></i>Seiten</h2>';
            
            // Dateien spaltenweise in 3 Spalten aufteilen
            $columns = $this->distributeItemsInColumns($files, 3);
            
            $html .= '<div class="row">';
            foreach ($columns as $column) {
                $html .= '<div class="col-md-4 mb-4">';
                
                foreach ($column as $file) {
                    $fileRoute = $file['route'];
                    $title = $file['title'];
                    $html .= '<div class="mb-2">';
                    $html .= '<a href="/' . $this->encodeUrlPath($fileRoute) . '" class="text-decoration-none">';
                    $html .= '<i class="bi bi-file-earmark-text me-2"></i>';
                    $html .= htmlspecialchars($title);
                    $html .= '</a>';
                    $html .= '</div>';
                }
                
                $html .= '</div>'; // col
            }
            $html .= '</div>'; // row
            $html .= '</div>'; // files-section
        }
        
        // Navigation back
        if ($route !== '') {
            $parentRoute = dirname($route);
            $parentRoute = ($parentRoute === '.' || $parentRoute === '/') ? '' : $parentRoute;
            
            $html .= '<div class="overview-navigation mt-4">';
            $html .= '<a href="/' . $this->encodeUrlPath($parentRoute) . '" class="btn btn-outline-primary">';
            $html .= '<i class="bi bi-arrow-left me-1"></i>' . \StaticMD\Core\I18n::t('core.back_link');
            $html .= '</a>';
            $html .= '</div>';
        }
        
        $html .= '</div>'; // folder-overview
        
        return $html;
    }

    /**
     * Verarbeitet Shortcodes in Raw-Markdown-Content (vor HTML-Konvertierung)
     * Schützt Shortcodes in Code-Blocks vor Verarbeitung
     */
    private function processShortcodes(string $content, string $currentRoute): string
    {
        // 1. Code-Blocks temporär durch Platzhalter ersetzen
        $codeBlocks = [];
        $codeIndex = 0;
        
        // Schütze Fenced Code Blocks (```)
        $content = preg_replace_callback('/```[\s\S]*?```/', function($matches) use (&$codeBlocks, &$codeIndex) {
            $placeholder = '___CODE_BLOCK_' . $codeIndex . '___';
            $codeBlocks[$placeholder] = $matches[0];
            $codeIndex++;
            return $placeholder;
        }, $content);
        
        // Schütze Inline Code (`)
        $content = preg_replace_callback('/`[^`]+`/', function($matches) use (&$codeBlocks, &$codeIndex) {
            $placeholder = '___INLINE_CODE_' . $codeIndex . '___';
            $codeBlocks[$placeholder] = $matches[0];
            $codeIndex++;
            return $placeholder;
        }, $content);
        
        // 2. Shortcodes verarbeiten (jetzt sicher außerhalb von Code-Blocks)
        $pattern = '/\[([a-zA-Z]+)(?:\s+([^\]]+))?\]/';
        
        $content = preg_replace_callback($pattern, function($matches) use ($currentRoute) {
            $shortcode = strtolower(trim($matches[1]));
            $params = isset($matches[2]) ? array_filter(array_map('trim', explode(' ', $matches[2]))) : [];
            
            switch ($shortcode) {
                case 'pages':
                    return $this->processPagesShortcode($params, $currentRoute);
                case 'tags':
                    return $this->processTagsShortcode($params, $currentRoute);
                case 'folder':
                    return $this->processFolderShortcode($params, $currentRoute);
                case 'gallery':
                    return $this->processGalleryShortcode($params, $currentRoute);
                case 'bloglist':
                    return $this->processBloglistShortcode($params, $currentRoute);
                default:
                    return $matches[0]; // Leave unknown shortcodes unchanged
            }
        }, $content);
        
        // 3. Code-Blocks wieder einsetzen
        $content = str_replace(array_keys($codeBlocks), array_values($codeBlocks), $content);
        
        return $content;
    }

    /**
     * Verarbeitet [pages /pfad/ limit layout] Shortcode
     * Layout: 'columns' (spaltenweise) oder 'rows' (zeilenweise)
     */
    private function processPagesShortcode(array $params, string $currentRoute): string
    {
        // Parameter parsen: [pages /treppen/ 1000 rows] oder [pages / 6]
        $targetPath = isset($params[0]) ? trim($params[0], ' ') : $currentRoute;
        
        // Root-Pfad erkennen: "/", "", oder leer
        if ($targetPath === '/' || empty($targetPath)) {
            $targetPath = '';
        } else {
            // Normale Pfade von Slashes befreien
            $targetPath = trim($targetPath, '/');
        }
        
        $limit = isset($params[1]) ? (int)$params[1] : 1000;
        $layout = isset($params[2]) ? strtolower(trim($params[2])) : 'columns';
        
        // Generate folder overview for the specified path
        $folderOverview = $this->generateFolderOverview($targetPath);
        
        if ($folderOverview === null) {
            return '<div class="alert alert-warning">Ordner "' . htmlspecialchars($targetPath) . '" nicht gefunden.</div>';
        }
        
        // Dateien limitieren
        $files = $this->getFolderFiles($targetPath, $limit);
        
        if (empty($files)) {
            return '<div class="alert alert-info">Keine Seiten in "' . htmlspecialchars($targetPath) . '" gefunden.</div>';
        }
        
        // Compact display for embedding
        return $this->generateEmbeddedFolderOverview($files, $targetPath, $layout);
    }

    /**
     * Verarbeitet [tags /pfad/ limit] Shortcode
     */
    private function processTagsShortcode(array $params, string $currentRoute): string
    {
        // Parameter parsen: [tags /treppen/ 1000] oder [tags / 10]
        $targetPath = isset($params[0]) ? trim($params[0], ' ') : $currentRoute;
        
        // Root-Pfad erkennen: "/", "", oder leer
        if ($targetPath === '/' || empty($targetPath)) {
            $targetPath = '';
        } else {
            // Normale Pfade von Slashes befreien
            $targetPath = trim($targetPath, '/');
        }
        
        $limit = isset($params[1]) ? (int)$params[1] : 1000;
        
        // Alle Tags aus dem Ordner sammeln
        $tags = $this->getFolderTags($targetPath, $limit);
        
        if (empty($tags)) {
            return '<div class="alert alert-info">Keine Tags in "' . htmlspecialchars($targetPath) . '" gefunden.</div>';
        }
        
        // Tags als Badge-Liste darstellen
        return $this->generateTagsList($tags, $targetPath);
    }

    /**
     * Verarbeitet [folder /pfad/ limit] Shortcode
     * Zeigt horizontale Liste der direkten Unterverzeichnisse
     */
    private function processFolderShortcode(array $params, string $currentRoute): string
    {
        // Parameter parsen: [folder /tech/ 10] oder [folder]
        $targetPath = isset($params[0]) ? trim($params[0], ' ') : $currentRoute;
        
        // Root-Pfad erkennen: "/", "", oder leer
        if ($targetPath === '/' || empty($targetPath)) {
            $targetPath = '';
        } else {
            // Normale Pfade von Slashes befreien
            $targetPath = trim($targetPath, '/');
        }
        
        $limit = isset($params[1]) ? (int)$params[1] : 1000;
        
        // Direkte Unterordner sammeln
        $subfolders = $this->getDirectSubfolders($targetPath, $limit);
        
        if (empty($subfolders)) {
            return '<div class="alert alert-info">Keine Unterordner in "' . htmlspecialchars($targetPath ?: '/') . '" gefunden.</div>';
        }
        
        // Generate horizontal folder navigation
        return $this->generateFolderNavigation($subfolders, $targetPath);
    }

    /**
     * Verarbeitet [gallery /pfad/ limit] Shortcode
     * Lädt automatisch alle Bilder aus einem Verzeichnis
     */
    private function processGalleryShortcode(array $params, string $currentRoute): string
    {
        // Parameter parsen: [gallery /images/galleries/paris/] oder [gallery paris 20]
        $targetPath = isset($params[0]) ? trim($params[0], ' ') : '';
        $limit = isset($params[1]) ? (int)$params[1] : 100;
        
        // Pfad-Handling
        if (empty($targetPath)) {
            return '<div class="alert alert-warning">Gallery-Shortcode: Pfad-Parameter erforderlich. Beispiel: [gallery paris] oder [gallery /assets/galleries/paris/]</div>';
        }
        
        // Determine if it's a relative path from /public/assets/ or absolute path
        if (strpos($targetPath, '/') === 0) {
            // Absolute path: /assets/galleries/paris/ -> /public/assets/galleries/paris/
            $imagePath = $this->config['paths']['public'] . $targetPath;
            $urlPath = $targetPath;
        } else {
            // Relative path: paris -> /public/assets/galleries/paris/
            $imagePath = $this->config['paths']['public'] . '/assets/galleries/' . $targetPath;
            $urlPath = '/assets/galleries/' . $targetPath;
        }
        
        // Check if directory exists
        if (!is_dir($imagePath)) {
            return '<div class="alert alert-warning">Gallery-Verzeichnis nicht gefunden: ' . htmlspecialchars($imagePath) . '</div>';
        }
        
        // Get all image files
        $images = $this->getImageFiles($imagePath, $urlPath, $limit);
        
        if (empty($images)) {
            return '<div class="alert alert-info">Keine Bilder in "' . htmlspecialchars($targetPath) . '" gefunden.</div>';
        }
        
        // Generate gallery HTML
        return $this->generateAutoGalleryHTML($images, $targetPath);
    }

    /**
     * Verarbeitet [bloglist /pfad/ per_page page] Shortcode
     * Zeigt nach Datum sortierte Blog-Einträge mit Pagination
     */
    private function processBloglistShortcode(array $params, string $currentRoute): string
    {
        // Parameter parsen: [bloglist /blog/ 5 1] - Pfad, Einträge pro Seite, aktuelle Seite
        $targetPath = isset($params[0]) ? trim($params[0], ' /') : trim($currentRoute, '/');
        $perPage = isset($params[1]) ? (int)$params[1] : $this->getItemsPerPage();
        $currentPage = isset($params[2]) ? max(1, (int)$params[2]) : $this->getCurrentPage();
        
        // Hole Blog-Einträge mit Pagination
        $blogData = $this->getBlogList($targetPath, $perPage, $currentPage);
        
        if (empty($blogData['items'])) {
            return '<div class="alert alert-info">Keine Blog-Einträge in "' . htmlspecialchars($targetPath ?: '/') . '" gefunden.</div>';
        }
        
        // Generate blog list HTML with pagination
        return $this->generateBlogListHTML($blogData, $targetPath, $currentPage, $perPage);
    }

    /**
     * Sammelt alle Bilddateien aus einem Verzeichnis
     */
    private function getImageFiles(string $imagePath, string $urlPath, int $limit): array
    {
        $images = [];
        $supportedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        try {
            $iterator = new \DirectoryIterator($imagePath);
            $count = 0;
            
            foreach ($iterator as $item) {
                if ($item->isDot() || $count >= $limit) {
                    continue;
                }
                
                if ($item->isFile()) {
                    $extension = strtolower($item->getExtension());
                    if (in_array($extension, $supportedExtensions)) {
                        $filename = $item->getFilename();
                        $images[] = [
                            'filename' => $filename,
                            'url' => rtrim($urlPath, '/') . '/' . $filename,
                            'alt' => $this->generateImageAltText($filename),
                            'title' => $this->generateImageTitle($filename)
                        ];
                        $count++;
                    }
                }
            }
        } catch (\Exception $e) {
            error_log('Gallery Shortcode Error: ' . $e->getMessage());
            return [];
        }
        
        // Sort images by filename
        usort($images, function($a, $b) {
            return strcmp($a['filename'], $b['filename']);
        });
        
        return $images;
    }
    
    /**
     * Generiert Alt-Text aus Dateiname
     */
    private function generateImageAltText(string $filename): string
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        
        // Remove common date/time patterns
        $name = preg_replace('/^\d{4}_\d{4}_\d{6}/', '', $name);
        $name = preg_replace('/^\d+_/', '', $name);
        
        // Replace underscores and dashes with spaces
        $name = str_replace(['_', '-'], ' ', $name);
        
        // Capitalize words
        // return ucwords(trim($name)) ?: 'Bild';
        return trim($name) ?: 'Bild';
    }
    
    /**
     * Generiert Titel aus Dateiname (für Lightbox)
     */
    private function generateImageTitle(string $filename): string
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        
        // Keep original formatting but clean up
        $name = str_replace('_', ' ', $name);
        return $name;
    }
    
    /**
     * Generiert HTML für automatische Galerie
     */
    private function generateAutoGalleryHTML(array $images, string $path): string
    {
        $html = '<div class="auto-gallery-info mb-3">';
        $html .= '<small class="text-muted"><i class="bi bi-images"></i> ' . count($images) . ' Bilder aus ' . htmlspecialchars($path) . '</small>';
        $html .= '</div>';
        
        // Generate images in the format expected by gallery.js
        foreach ($images as $image) {
            $html .= '<img src="' . htmlspecialchars($image['url']) . '" ';
            $html .= 'alt="' . htmlspecialchars($image['alt']) . '" ';
            $html .= 'title="' . htmlspecialchars($image['title']) . '" ';
            $html .= 'loading="lazy" />' . "\n";
        }
        
        return $html;
    }

    /**
     * Verarbeitet Accordion-Shortcodes (mehrzeilig)
     * Unterstützt sowohl [spoilerstart]/[spoilerstop] als auch [accordionstart]/[accordionstop]
     */
    private function processAccordionShortcodes(string $content): string
    {
        // Pattern for both accordion types in HTML code
        $patterns = [
            // [spoilerstart id "title"] ... [spoilerstop] oder [spoilerstop id] (auch in <p> Tags)
            '/(?:<p>)?\[(spoilerstart)\s+([a-zA-Z0-9_-]+)\s+"([^"]+)"\](?:<\/p>)?([\s\S]*?)(?:<p>)?\[spoilerstop(?:\s+[^\]]*?)?\](?:<\/p>)?/i',
            // [accordionstart id "title"] ... [accordionstop] oder [accordionstop id] (auch in <p> Tags)  
            '/(?:<p>)?\[(accordionstart)\s+([a-zA-Z0-9_-]+)\s+"([^"]+)"\](?:<\/p>)?([\s\S]*?)(?:<p>)?\[accordionstop(?:\s+[^\]]*?)?\](?:<\/p>)?/i'
        ];
        
        foreach ($patterns as $pattern) {
            $content = preg_replace_callback($pattern, function($matches) {
                $type = $matches[1]; // spoilerstart oder accordionstart
                $id = $matches[2];   // eindeutige ID
                $title = $matches[3]; // Titel
                $accordionContent = trim($matches[4]); // Inhalt (bereits als HTML)
                
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
        // Unique ID for multiple accordions per page
        $accordionId = 'accordion-' . $id;
        $collapseId = 'collapse-' . $id;
        $headingId = 'heading-' . $id;
        
        // HTML-Entities in Titel escapen
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        
        // Content is already HTML (was processed by Markdown parser)
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
            $accordionId,
            $headingId,
            $collapseId,
            $collapseId,
            $safeTitle,
            $collapseId,
            $headingId,
            $accordionId,
            $htmlContent
        );
    }

    /**
     * Holt Dateien aus einem Ordner (für Shortcodes)
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
            if ($count >= $limit) {
                break;
            }
            
            if ($file->isDot() || !$file->isFile()) {
                continue;
            }
            
            $filename = $file->getFilename();
            if (!str_ends_with($filename, $extension)) {
                continue;
            }
            
            // Skip index.md
            if ($filename === 'index' . $extension) {
                continue;
            }
            
            $filePath = $file->getPathname();
            $baseName = substr($filename, 0, -strlen($extension));
            $fileRoute = empty($route) ? $baseName : $route . '/' . $baseName;
            
            // Metadaten lesen
            $rawContent = file_get_contents($filePath);
            $parsed = $this->parseFrontMatter($rawContent);
            
            $title = $parsed['meta']['Title'] ?? $parsed['meta']['title'] ?? $this->generateTitle(basename($fileRoute));
            
            $files[] = [
                'title' => $title,
                'route' => $fileRoute,
                'tags' => $parsed['meta']['Tag'] ?? $parsed['meta']['tags'] ?? '',
                'author' => $parsed['meta']['Author'] ?? $parsed['meta']['author'] ?? '',
                'modified' => filemtime($filePath)
            ];
            
            $count++;
        }
        
        // Sort alphabetically (case-insensitive)
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
        
        // Sort alphabetically case-insensitive
        uksort($tagCounts, function($a, $b) {
            return strcasecmp($a, $b);
        });
        
        return $tagCounts;
    }

    /**
     * Generiert eingebettete Ordner-Übersicht für Shortcodes
     */
    private function generateEmbeddedFolderOverview(array $files, string $route, string $layout = 'columns'): string
    {
        $html = '<div class="embedded-page-list">';
        
        if ($layout === 'rows') {
            // Zeilenweise Sortierung, aber in Spalten angezeigt
            $columns = $this->distributeItemsInRows($files, 4);
        } else {
            // Spaltenweise Sortierung (Standard)
            $columns = $this->distributeItemsInColumns($files, 4);
        }
        
        $html .= '<div class="row">';
        foreach ($columns as $column) {
            $html .= '<div class="col-6 col-sm-6 col-md-3 col-lg-3 mb-3">';
            
            foreach ($column as $file) {
                $fileRoute = $file['route'];
                $title = $file['title'];
                $html .= '<div class="mb-2">';
                $html .= '<a href="/' . $this->encodeUrlPath($fileRoute) . '" class="text-decoration-none d-block">';
                $html .= '<i class="bi bi-file-earmark-text me-2 text-muted"></i>';
                $html .= '<small>' . htmlspecialchars($title) . '</small>';
                $html .= '</a>';
                $html .= '</div>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    /**
     * Generiert Tag-Liste
     */
    private function generateTagsList(array $tagCounts, string $route): string
    {
        $html = '<div class="tag-cloud mb-3">';
        
        foreach ($tagCounts as $tag => $count) {
            // Tag size based on frequency
            $size = min(3, max(1, (int)floor($count / 2) + 1));
            $badgeClass = $size === 1 ? 'bg-secondary' : ($size === 2 ? 'bg-primary' : 'bg-success');
            
            $fontSize = number_format(0.7 + $size * 0.1, 2);
            //$html .= '<a href="/tag/' . urlencode($tag) . '" class="badge ' . $badgeClass . ' me-2 mb-1 text-decoration-none" style="font-size: ' . $fontSize . 'rem;">';
            $html .= '<a href="/tag/' . urlencode($tag) . '" class="badge ' . $badgeClass . ' me-2 mb-2 text-decoration-none">';
            $html .= htmlspecialchars($tag);
            //if ($count > 1) {
            $html .= ' <span class="badge bg-light text-dark ms-1">' . $count . '</span>';
            //}
            $html .= '</a>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Sucht nach der entsprechenden Content-Datei
     */
    private function findContentFile(string $route): ?string
    {
        $contentDir = $this->config['paths']['content'];
        $extension = $this->config['markdown']['file_extension'];
        
        // URL-decoded route for filesystem access
        $decodedRoute = urldecode($route);
        
        // Unicode-Normalisierung falls verfügbar
        if (class_exists('Normalizer') && function_exists('normalizer_normalize')) {
            $decodedRoute = normalizer_normalize($decodedRoute, Normalizer::FORM_C);
        } else {
            // Simple fallback normalization for common cases
            $decodedRoute = $this->simpleUnicodeNormalize($decodedRoute);
        }
        
        // Verschiedene Route-Varianten testen
        $routeVariants = [
            trim($decodedRoute, '/'),
            trim($route, '/'),
            trim(urldecode($route), '/')
        ];
        
        // Try different paths for each route variant
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
            
            // Extended search: Look for normalized file in directory
            $dir = dirname($contentDir . '/' . $routeVariant);
            $filename = basename($routeVariant) . $extension;
            
            if (is_dir($dir)) {
                $files = scandir($dir);
                if ($files) {
                    foreach ($files as $file) {
                        if ($file === '.' || $file === '..') continue;
                        
                        // Normalize both filenames for comparison
                        $normalizedFile = $this->normalizeForComparison($file);
                        $normalizedTarget = $this->normalizeForComparison($filename);
                        
                        if ($normalizedFile === $normalizedTarget) {
                            $foundPath = $dir . '/' . $file;
                            if (file_exists($foundPath) && is_readable($foundPath)) {
                                return $foundPath;
                            }
                        }
                    }
                }
            }
        }
        
        // For homepage
        if ($route === 'index' || $route === '') {
            $indexPaths = [
                $contentDir . '/home' . $extension,    // home.md has priority
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
     * Parst Front Matter (YAML-Header) aus Markdown-Datei
     */
    private function parseFrontMatter(string $content): array
    {
        $meta = [];
        $bodyContent = $content;
        
        // Front Matter erkennen (--- am Anfang)
        if (strpos($content, '---') === 0) {
            $parts = explode('---', $content, 3);
            
            if (count($parts) >= 3) {
                $frontMatter = trim($parts[1]);
                $bodyContent = trim($parts[2]);
                
                // Einfaches Key-Value Parsing (ohne YAML-Dependency)
                $lines = explode("\n", $frontMatter);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line) || strpos($line, ':') === false) {
                        continue;
                    }
                    
                    [$key, $value] = explode(':', $line, 2);
                    $cleanKey = trim($key);
                    $cleanValue = trim($value, ' "\'');
                    
                    // Yellow CMS Compatibility: Key-Mapping
                    $yellowMapping = [
                        'Title' => 'title',
                        'TitleSlug' => 'titleslug', 
                        'Layout' => 'layout',
                        'Tag' => 'tags',
                        'Author' => 'author'
                    ];
                    
                    // Verwende gemappten Key falls vorhanden, sonst Original (lowercase)
                    $mappedKey = $yellowMapping[$cleanKey] ?? strtolower($cleanKey);
                    $meta[$mappedKey] = $cleanValue;
                    
                    // Keep original Yellow keys for compatibility
                    if (array_key_exists($cleanKey, $yellowMapping)) {
                        $meta[$cleanKey] = $cleanValue;
                    }
                }
            }
        }
        
        return [
            'meta' => $meta,
            'content' => $bodyContent
        ];
    }

    /**
     * Generiert einen Titel aus der Route
     */
    private function generateTitle(string $route): string
    {
        if ($route === 'index') {
            return $this->config['system']['name'];
        }
        
        // Convert route to readable title
        $title = str_replace(['/', '-', '_'], ' ', $route);
        // return ucwords($title);
        return $title;
    }

    /**
     * Listet alle verfügbaren Content-Dateien auf
     */
    public function listAll(): array
    {
        $contentDir = $this->config['paths']['content'];
        $extension = $this->config['markdown']['file_extension'];
        
        $files = [];
        $this->scanDirectory($contentDir, $contentDir, $extension, $files);

        // Sort by modification date (modified) descending
        usort($files, function($a, $b) {
            return $b['modified'] <=> $a['modified'];
            //return $a['modified'] <=> $b['modified'];
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
     * Normalisiert Strings für Dateinamen-Vergleiche
     */
    private function normalizeForComparison(string $input): string
    {
        // Unicode-Normalisierung falls verfügbar
        if (class_exists('Normalizer') && function_exists('normalizer_normalize')) {
            $normalized = normalizer_normalize($input, Normalizer::FORM_C);
        } else {
            // Fallback-Normalisierung
            $normalized = $this->simpleUnicodeNormalize($input);
        }
        
        // Zu lowercase für case-insensitive Vergleich
        return mb_strtolower($normalized, 'UTF-8');
    }
    
    /**
     * Verteilt Items spaltenweise auf Spalten (statt zeilenweise)
     */
    private function distributeItemsInColumns(array $items, int $columnCount): array
    {
        $columns = array_fill(0, $columnCount, []);
        $itemsPerColumn = ceil(count($items) / $columnCount);
        
        for ($i = 0; $i < count($items); $i++) {
            $columnIndex = intval($i / $itemsPerColumn);
            $columnIndex = min($columnIndex, $columnCount - 1); // Sicherheit: nicht über letzte Spalte hinaus
            $columns[$columnIndex][] = $items[$i];
        }
        
        return $columns;
    }
    
    /**
     * Verteilt Items zeilenweise auf Spalten (A,B,C | D,E,F | G,H,I)
     */
    private function distributeItemsInRows(array $items, int $columnCount): array
    {
        $columns = array_fill(0, $columnCount, []);
        $totalItems = count($items);
        
        for ($i = 0; $i < $totalItems; $i++) {
            $columnIndex = $i % $columnCount;
            $columns[$columnIndex][] = $items[$i];
        }
        
        return $columns;
    }
    
    /**
     * Prüft ob private Seiten angezeigt werden sollen (Admin angemeldet)
     */
    private function shouldShowPrivateContent(): bool
    {
        // Load AdminAuth class for session check
        $adminAuthFile = $this->config['paths']['system'] . '/admin/AdminAuth.php';
        if (file_exists($adminAuthFile)) {
            require_once $adminAuthFile;
            $adminAuth = new \StaticMD\Admin\AdminAuth($this->config);
            return $adminAuth->isLoggedIn();
        }
        return false;
    }
    
    /**
     * Lädt Navigation-Sortierung aus Settings
     */
    public function getNavigationOrder(): array
    {
        $settingsFile = $this->config['paths']['system'] . '/settings.json';
        
        // Standard-Navigation-Reihenfolge als Fallback
        $defaultOrder = [
            'about' => 1,
            'blog' => 2,
            'help' => 3
        ];
        
        if (file_exists($settingsFile)) {
            $settings = json_decode(file_get_contents($settingsFile), true);
            if (isset($settings['navigation_order']) && is_array($settings['navigation_order'])) {
                return $settings['navigation_order'];
            }
        }
        
        return $defaultOrder;
    }
    
    /**
     * Generiert Breadcrumb-Navigation für eine Route
     */
    public function getBreadcrumbs(string $route): array
    {
        $breadcrumbs = [['title' => 'Home', 'route' => '', 'url' => '/', 'is_last' => false]];
        
        if (empty($route) || $route === 'index') {
            // Für Homepage: Home als letztes Element markieren
            $breadcrumbs[0]['is_last'] = true;
            return $breadcrumbs;
        }
        
        $parts = explode('/', trim($route, '/'));
        $currentPath = '';
        
        foreach ($parts as $index => $part) {
            $currentPath .= ($currentPath ? '/' : '') . $part;
            
            // Generate title - try to get real title from file
            $title = $this->getBreadcrumbTitle($currentPath, $part);
            $url = '/' . $this->encodeUrlPath($currentPath);
            
            $breadcrumbs[] = [
                'title' => $title,
                'route' => $currentPath,
                'url' => $url,
                'is_last' => $index === count($parts) - 1
            ];
        }
        
        return $breadcrumbs;
    }
    
    /**
     * Ermittelt den besten Titel für einen Breadcrumb-Teil
     */
    private function getBreadcrumbTitle(string $route, string $fallback): string
    {
        // First try to get the real title from a file
        $contentFile = $this->findContentFile($route);
        if ($contentFile && is_readable($contentFile)) {
            $rawContent = file_get_contents($contentFile);
            $parsed = $this->parseFrontMatter($rawContent);
            $title = $parsed['meta']['Title'] ?? $parsed['meta']['title'] ?? null;
            if (!empty($title)) {
                return $title;
            }
        }
        
        // Try index.md in the folder
        $indexFile = $this->findContentFile($route . '/index');
        if ($indexFile && is_readable($indexFile)) {
            $rawContent = file_get_contents($indexFile);
            $parsed = $this->parseFrontMatter($rawContent);
            $title = $parsed['meta']['Title'] ?? $parsed['meta']['title'] ?? null;
            if (!empty($title)) {
                return $title;
            }
        }
        
        // Fallback: Convert route part to readable title
        return $this->generateTitle($fallback);
    }
    
    /**
     * Prüft ob ein Ordner Markdown-Dateien enthält
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
    
    /**
     * Zählt Markdown-Dateien in einem Ordner
     */
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
    
    /**
     * Ermittelt Titel eines Unterordners (aus index.md oder Ordnername)
     */
    private function getSubfolderTitle(string $folderPath, string $fallbackName): string
    {
        $extension = $this->config['markdown']['file_extension'];
        $indexFile = $folderPath . '/index' . $extension;
        
        if (file_exists($indexFile) && is_readable($indexFile)) {
            $content = file_get_contents($indexFile);
            $parsed = $this->parseFrontMatter($content);
            $title = $parsed['meta']['Title'] ?? $parsed['meta']['title'] ?? null;
            if (!empty($title)) {
                return $title;
            }
        }
        
        // return ucwords(str_replace(['-', '_'], ' ', $fallbackName));
        return trim(str_replace(['-', '_'], ' ', $fallbackName));
    }
    
    /**
     * Ermittelt Beschreibung eines Unterordners (aus index.md)
     */
    private function getSubfolderDescription(string $folderPath): string
    {
        $extension = $this->config['markdown']['file_extension'];
        $indexFile = $folderPath . '/index' . $extension;
        
        if (file_exists($indexFile) && is_readable($indexFile)) {
            $content = file_get_contents($indexFile);
            $parsed = $this->parseFrontMatter($content);
            $description = $parsed['meta']['description'] ?? '';
            
            if (!empty($description)) {
                return $description;
            }
            
            // Fallback: Erste Zeilen des Inhalts
            $contentLines = explode("\n", trim($parsed['content']));
            foreach ($contentLines as $line) {
                $line = trim($line);
                if (!empty($line) && !str_starts_with($line, '#')) {
                    $preview = substr($line, 0, 100);
                    if (strlen($line) > 100) {
                        $preview .= '...';
                    }
                    return $preview;
                }
            }
        }
        
        return '';
    }

    /**
     * Sammelt direkte Unterordner eines Pfades
     */
    private function getDirectSubfolders(string $folderPath, int $limit = 1000): array
    {
        $contentPath = $this->config['paths']['content'];
        $fullPath = $contentPath . '/' . $folderPath;
        
        if (!is_dir($fullPath)) {
            return [];
        }
        
        $subfolders = [];
        
        // Nur direkte Unterordner, nicht rekursiv
        $iterator = new \DirectoryIterator($fullPath);
        
        foreach ($iterator as $item) {
            if ($item->isDot() || !$item->isDir()) {
                continue;
            }
            
            $folderName = $item->getFilename();
            
            // Check if the folder contains Markdown files
            $hasContent = false;
            
            // Check for index.md or other .md files
            $folderIterator = new \DirectoryIterator($item->getPathname());
            foreach ($folderIterator as $subItem) {
                if ($subItem->isFile() && pathinfo($subItem->getFilename(), PATHINFO_EXTENSION) === 'md') {
                    $hasContent = true;
                    break;
                }
            }
            
            if ($hasContent) {
                // Titel aus index.md extrahieren oder Ordnername verwenden
                $title = $this->getFolderTitle($item->getPathname());
                $subfolders[] = [
                    'name' => $folderName,
                    'title' => $title,
                    'path' => $folderPath ? $folderPath . '/' . $folderName : $folderName
                ];
            }
        }
        
        // Sort by title and limit
        usort($subfolders, function($a, $b) {
            return strcasecmp($a['title'], $b['title']);
        });
        
        return array_slice($subfolders, 0, $limit);
    }

    /**
     * Extrahiert den Titel eines Ordners aus seiner index.md
     */
    private function getFolderTitle(string $folderPath): string
    {
        $extension = $this->config['markdown']['file_extension'];
        $indexFile = $folderPath . '/index' . $extension;
        
        if (file_exists($indexFile) && is_readable($indexFile)) {
            $content = file_get_contents($indexFile);
            $parsed = $this->parseFrontMatter($content);
            
            // Titel aus Front Matter
            if (!empty($parsed['meta']['Title'])) {
                return $parsed['meta']['Title'];
            }
            
            // Fallback: Erste H1 im Inhalt
            if (preg_match('/^#\s+(.+)$/m', $parsed['content'], $matches)) {
                return trim($matches[1]);
            }
        }
        
        // Fallback: Ordnername mit Ersetzungen
        $title = basename($folderPath);
        $title = str_replace(['-', '_'], ' ', $title);
        // return ucwords($title);
        return trim($title);
    }

    /**
     * Generiert horizontale Navigation für Unterordner
     */
    private function generateFolderNavigation(array $subfolders, string $basePath): string
    {
        $html = '<nav class="folder-navigation mb-3">';
        $html .= '<div class="d-flex flex-wrap gap-2">';
        
        foreach ($subfolders as $folder) {
            $url = '/' . $folder['path'];
            $html .= '<a href="' . htmlspecialchars($url) . '" class="btn btn-outline-primary btn-sm">';
            $html .= '<i class="bi bi-folder"></i> ' . htmlspecialchars($folder['title']);
            $html .= '</a>';
        }
        
        $html .= '</div>';
        $html .= '</nav>';
        
        return $html;
    }
    
    /**
     * Holt Blog-Einträge mit Datumsortierung und Pagination
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
            if (!str_ends_with($filename, $extension)) {
                continue;
            }
            
            // Skip index.md
            if ($filename === 'index' . $extension) {
                continue;
            }
            
            $filePath = $file->getPathname();
            $baseName = substr($filename, 0, -strlen($extension));
            $fileRoute = empty($route) ? $baseName : $route . '/' . $baseName;
            
            // Metadaten lesen
            $rawContent = file_get_contents($filePath);
            $parsed = $this->parseFrontMatter($rawContent);
            
            // Nur Dateien mit blog layout oder ohne Layout im blog-Ordner
            $layout = $parsed['meta']['Layout'] ?? $parsed['meta']['layout'] ?? '';
            $isInBlogFolder = strpos($route, 'blog') !== false;
            
            if ($layout !== 'blog' && !$isInBlogFolder) {
                continue;
            }
            
            $title = $parsed['meta']['Title'] ?? $parsed['meta']['title'] ?? $this->generateTitle(basename($fileRoute));
            $date = $parsed['meta']['Date'] ?? $parsed['meta']['date'] ?? '';
            $author = $parsed['meta']['Author'] ?? $parsed['meta']['author'] ?? '';
            $description = $parsed['meta']['Description'] ?? $parsed['meta']['description'] ?? '';
            $tags = $parsed['meta']['Tag'] ?? $parsed['meta']['tags'] ?? '';
            $visibility = $parsed['meta']['Visibility'] ?? $parsed['meta']['visibility'] ?? 'public';
            
            // Private Seiten ausblenden wenn nicht angemeldet
            if ($visibility === 'private' && !$this->shouldShowPrivateContent()) {
                continue;
            }
            
            // Fallback-Beschreibung aus Inhalt
            if (empty($description)) {
                $contentLines = explode("\n", trim($parsed['content']));
                $preview = '';
                foreach ($contentLines as $line) {
                    $line = trim($line);
                    if (!empty($line) && !str_starts_with($line, '#')) {
                        $preview = substr($line, 0, 200);
                        if (strlen($line) > 200) {
                            $preview .= '...';
                        }
                        break;
                    }
                }
                $description = $preview;
            }
            
            // Datum für Sortierung vorbereiten
            $sortDate = $this->parseDate($date, filemtime($filePath));
            
            $files[] = [
                'title' => $title,
                'route' => $fileRoute,
                'date' => $date,
                'sort_date' => $sortDate,
                'author' => $author,
                'description' => $description,
                'tags' => $tags,
                'modified' => filemtime($filePath),
                'visibility' => $visibility
            ];
        }
        
        // Nach Datum sortieren (neueste zuerst)
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
     * Generiert HTML für Blog-Liste mit Pagination
     */
    private function generateBlogListHTML(array $blogData, string $route, int $currentPage, int $perPage): string
    {
        $html = '<div class="blog-list">';
        
        // Blog-Einträge
        foreach ($blogData['items'] as $item) {
            $html .= '<article class="blog-entry card mb-4">';
            $html .= '<div class="card-body">';
            
            // Titel
            $html .= '<h3 class="card-title">';
            $html .= '<a href="/' . $this->encodeUrlPath($item['route']) . '" class="text-decoration-none">';
            $html .= htmlspecialchars($item['title']);
            $html .= '</a>';
            $html .= '</h3>';
            
            // Meta-Informationen
            $html .= '<div class="blog-meta mb-3">';
            if (!empty($item['date'])) {
                $html .= '<span class="text-muted me-3">';
                $html .= '<i class="bi bi-calendar me-1"></i>';
                $html .= htmlspecialchars($item['date']);
                $html .= '</span>';
            }
            if (!empty($item['author'])) {
                $html .= '<span class="text-muted me-3">';
                $html .= '<i class="bi bi-person me-1"></i>';
                $html .= htmlspecialchars($item['author']);
                $html .= '</span>';
            }
            $html .= '</div>';
            
            // Beschreibung
            if (!empty($item['description'])) {
                $html .= '<p class="card-text">' . htmlspecialchars($item['description']) . '</p>';
            }
            
            // Tags
            if (!empty($item['tags'])) {
                $html .= '<div class="blog-tags mb-2">';
                $tags = array_map('trim', explode(',', $item['tags']));
                foreach ($tags as $tag) {
                    if (!empty($tag)) {
                        $html .= '<a href="/tag/' . urlencode($tag) . '" class="badge bg-secondary me-1 text-decoration-none">';
                        $html .= htmlspecialchars($tag);
                        $html .= '</a>';
                    }
                }
                $html .= '</div>';
            }
            
            // Weiterlesen-Link
            $html .= '<a href="/' . $this->encodeUrlPath($item['route']) . '" class="btn btn-primary btn-sm">';
            $html .= 'Weiterlesen <i class="bi bi-arrow-right"></i>';
            $html .= '</a>';
            
            $html .= '</div>';
            $html .= '</article>';
        }
        
        // Pagination
        if ($blogData['pages'] > 1) {
            $html .= $this->generatePagination($blogData, $route);
        }
        
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Generiert Pagination-HTML
     */
    private function generatePagination(array $blogData, string $route): string
    {
        $currentPage = $blogData['current_page'];
        $totalPages = $blogData['pages'];
        $perPage = $blogData['per_page'];
        
        $html = '<nav aria-label="Blog pagination" class="mt-4">';
        $html .= '<ul class="pagination justify-content-center">';
        
        // Vorherige Seite
        if ($currentPage > 1) {
            $prevUrl = $this->getPaginationUrl($route, $currentPage - 1, $perPage);
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . htmlspecialchars($prevUrl) . '">';
            $html .= '<i class="bi bi-chevron-left"></i> Vorherige';
            $html .= '</a>';
            $html .= '</li>';
        } else {
            $html .= '<li class="page-item disabled">';
            $html .= '<span class="page-link"><i class="bi bi-chevron-left"></i> Vorherige</span>';
            $html .= '</li>';
        }
        
        // Seitennummern
        $startPage = max(1, $currentPage - 2);
        $endPage = min($totalPages, $currentPage + 2);
        
        if ($startPage > 1) {
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . htmlspecialchars($this->getPaginationUrl($route, 1, $perPage)) . '">1</a>';
            $html .= '</li>';
            if ($startPage > 2) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }
        
        for ($i = $startPage; $i <= $endPage; $i++) {
            if ($i == $currentPage) {
                $html .= '<li class="page-item active">';
                $html .= '<span class="page-link">' . $i . '</span>';
                $html .= '</li>';
            } else {
                $html .= '<li class="page-item">';
                $html .= '<a class="page-link" href="' . htmlspecialchars($this->getPaginationUrl($route, $i, $perPage)) . '">' . $i . '</a>';
                $html .= '</li>';
            }
        }
        
        if ($endPage < $totalPages) {
            if ($endPage < $totalPages - 1) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . htmlspecialchars($this->getPaginationUrl($route, $totalPages, $perPage)) . '">' . $totalPages . '</a>';
            $html .= '</li>';
        }
        
        // Nächste Seite
        if ($currentPage < $totalPages) {
            $nextUrl = $this->getPaginationUrl($route, $currentPage + 1, $perPage);
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . htmlspecialchars($nextUrl) . '">';
            $html .= 'Nächste <i class="bi bi-chevron-right"></i>';
            $html .= '</a>';
            $html .= '</li>';
        } else {
            $html .= '<li class="page-item disabled">';
            $html .= '<span class="page-link">Nächste <i class="bi bi-chevron-right"></i></span>';
            $html .= '</li>';
        }
        
        $html .= '</ul>';
        $html .= '</nav>';
        
        return $html;
    }
    
    /**
     * Hilfsfunktionen für Blog-Liste
     */
    private function parseDate(string $dateString, int $fallbackTimestamp): int
    {
        if (empty($dateString)) {
            return $fallbackTimestamp;
        }
        
        // Verschiedene Datumsformate unterstützen
        $formats = [
            'Y-m-d',     // 2025-01-15
            'd.m.Y',     // 15.01.2025
            'Y/m/d',     // 2025/01/15
            'Y-m-d H:i:s' // 2025-01-15 14:30:00
        ];
        
        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $dateString);
            if ($date !== false) {
                return $date->getTimestamp();
            }
        }
        
        // Fallback: Dateimodifikationszeit
        return $fallbackTimestamp;
    }
    
    private function getItemsPerPage(): int
    {
        $settingsFile = $this->config['paths']['system'] . '/settings.json';
        if (file_exists($settingsFile)) {
            $settings = json_decode(file_get_contents($settingsFile), true);
            return $settings['items_per_page'] ?? 10;
        }
        return 10;
    }
    
    private function getCurrentPage(): int
    {
        return isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    }
    
    private function getPaginationUrl(string $route, int $page, int $perPage): string
    {
        $baseUrl = '/' . ($route ? $this->encodeUrlPath($route) : '');
        $params = [];
        
        if ($page > 1) {
            $params['page'] = $page;
        }
        
        // Weitere URL-Parameter beibehalten (außer page)
        foreach ($_GET as $key => $value) {
            if ($key !== 'page' && $key !== 'route') {
                $params[$key] = $value;
            }
        }
        
        return $baseUrl . (!empty($params) ? '?' . http_build_query($params) : '');
    }
}