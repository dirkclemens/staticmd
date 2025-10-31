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
        // Häufige kombinierte Unicode-Zeichen zu einfachen konvertieren
        // Verwende hex-Codes für kombinierte Zeichen
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
        $contentPath = $this->findContentFile($route);
        
        if ($contentPath === null || !is_readable($contentPath)) {
            // Prüfen ob es ein Ordner ist
            $folderOverview = $this->generateFolderOverview($route);
            if ($folderOverview !== null) {
                return $folderOverview;
            }
            return null;
        }

        $rawContent = file_get_contents($contentPath);
        
        // Front Matter und Content trennen
        $parsed = $this->parseFrontMatter($rawContent);
        
        // Markdown zu HTML konvertieren
        $htmlContent = $this->parser->parse($parsed['content']);
        
        // Accordion-Shortcodes nach Markdown-Parsing verarbeiten
        $htmlContent = $this->processAccordionShortcodes($htmlContent);
        
        // Pages/Tags-Shortcodes nach Markdown-Parsing verarbeiten
        $htmlContent = $this->processShortcodes($htmlContent, $route);
        
        return [
            'title' => $parsed['meta']['title'] ?? 'Unbenannte Seite',
            'content' => $htmlContent,
            'meta' => $parsed['meta'],
            'route' => $route,
            'file_path' => $contentPath,
            'visibility' => $parsed['meta']['Visibility'] ?? $parsed['meta']['visibility'] ?? 'public'
        ];
    }

    /**
     * Generiert eine automatische Ordner-Übersichtsseite
     */
    private function generateFolderOverview(string $route): ?array
    {
        $contentDir = $this->config['paths']['content'];
        $folderPath = $contentDir . '/' . $route;
        
        // Prüfen ob Ordner existiert
        if (!is_dir($folderPath)) {
            return null;
        }
        
        // Alle Markdown-Dateien im Ordner finden
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
            
            // index.md überspringen (wäre die Ordner-Hauptseite)
            if ($filename === 'index' . $extension) {
                continue;
            }
            
            $filePath = $file->getPathname();
            $fileRoute = $route . '/' . substr($filename, 0, -strlen($extension));
            
            // Metadaten aus Datei lesen
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
        
        // Alphabetisch sortieren (case-insensitive)
        usort($files, function($a, $b) {
            return strcasecmp($a['title'], $b['title']);
        });
        
        // HTML für Übersichtsseite generieren
        $folderTitle = ucwords(str_replace(['/', '-', '_'], ' ', $route));
        $html = $this->generateFolderOverviewHTML($folderTitle, $files, $route);
        
        return [
            'title' => $folderTitle . ' - Übersicht',
            'content' => $html,
            'meta' => [
                'title' => $folderTitle . ' - Übersicht',
                'description' => 'Übersicht aller Seiten im Bereich ' . $folderTitle,
                'folder_overview' => true,
                'folder_route' => $route,
                'file_count' => count($files)
            ],
            'route' => $route,
            'file_path' => $folderPath,
            'modified' => filemtime($folderPath)
        ];
    }
    
    /**
     * Generiert HTML für Ordner-Übersichtsseite
     */
    private function generateFolderOverviewHTML(string $folderTitle, array $files, string $route): string
    {
        $html = '<div class="folder-overview">';
        
        // Header
        $html .= '<div class="overview-header mb-4">';
        $html .= '<h1><i class="bi bi-folder2-open me-2"></i>' . htmlspecialchars($folderTitle) . '</h1>';
        $html .= '<p class="lead">Übersicht aller Seiten in diesem Bereich (' . count($files) . ' Seiten)</p>';
        $html .= '</div>';
        
        if (empty($files)) {
            $html .= '<div class="alert alert-info">';
            $html .= '<i class="bi bi-info-circle me-2"></i>';
            $html .= 'In diesem Bereich sind noch keine Seiten vorhanden.';
            $html .= '</div>';
        } else {
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
        }
        
        // Navigation zurück
        if ($route !== '') {
            $parentRoute = dirname($route);
            $parentRoute = ($parentRoute === '.' || $parentRoute === '/') ? '' : $parentRoute;
            
            $html .= '<div class="overview-navigation mt-4">';
            $html .= '<a href="/' . $this->encodeUrlPath($parentRoute) . '" class="btn btn-outline-primary">';
            $html .= '<i class="bi bi-arrow-left me-1"></i>Zurück';
            $html .= '</a>';
            $html .= '</div>';
        }
        
        $html .= '</div>'; // folder-overview
        
        return $html;
    }

    /**
     * Verarbeitet Shortcodes in HTML-Content
     */
    private function processShortcodes(string $content, string $currentRoute): string
    {
        // Pattern für Shortcodes in HTML (auch in <p> Tags)
        $pattern = '/(?:<p>)?\[([a-zA-Z]+)\s+([^\]]+)\](?:<\/p>)?/';
        
        return preg_replace_callback($pattern, function($matches) use ($currentRoute) {
            $shortcode = strtolower(trim($matches[1]));
            $params = array_filter(array_map('trim', explode(' ', $matches[2])));
            
            switch ($shortcode) {
                case 'pages':
                    return $this->processPagesShortcode($params, $currentRoute);
                case 'tags':
                    return $this->processTagsShortcode($params, $currentRoute);
                default:
                    return $matches[0]; // Unbekannte Shortcodes unverändert lassen
            }
        }, $content);
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
        
        // Folder Overview für den angegebenen Pfad generieren
        $folderOverview = $this->generateFolderOverview($targetPath);
        
        if ($folderOverview === null) {
            return '<div class="alert alert-warning">Ordner "' . htmlspecialchars($targetPath) . '" nicht gefunden.</div>';
        }
        
        // Dateien limitieren
        $files = $this->getFolderFiles($targetPath, $limit);
        
        if (empty($files)) {
            return '<div class="alert alert-info">Keine Seiten in "' . htmlspecialchars($targetPath) . '" gefunden.</div>';
        }
        
        // Kompakte Darstellung für Einbettung
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
     * Verarbeitet Accordion-Shortcodes (mehrzeilig)
     * Unterstützt sowohl [spoilerstart]/[spoilerstop] als auch [accordionstart]/[accordionstop]
     */
    private function processAccordionShortcodes(string $content): string
    {
        // Pattern für beide Accordion-Typen in HTML-Code
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
        // Unique ID für mehrere Accordions pro Seite
        $accordionId = 'accordion-' . $id;
        $collapseId = 'collapse-' . $id;
        $headingId = 'heading-' . $id;
        
        // HTML-Entities in Titel escapen
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        
        // Content ist bereits HTML (wurde durch Markdown-Parser verarbeitet)
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
            
            // index.md überspringen
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
        
        // Alphabetisch sortieren (case-insensitive)
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
        
        // Case-insensitive alphabetisch sortieren
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
        
        return $html;
    }

    /**
     * Generiert Tag-Liste
     */
    private function generateTagsList(array $tagCounts, string $route): string
    {
        $html = '<div class="tag-cloud mb-3">';
        
        foreach ($tagCounts as $tag => $count) {
            // Tag-Größe basierend auf Häufigkeit
            $size = min(3, max(1, (int)floor($count / 2) + 1));
            $badgeClass = $size === 1 ? 'bg-secondary' : ($size === 2 ? 'bg-primary' : 'bg-success');
            
            $fontSize = number_format(0.7 + $size * 0.1, 2);
            $html .= '<a href="/tag/' . urlencode($tag) . '" class="badge ' . $badgeClass . ' me-2 mb-1 text-decoration-none" style="font-size: ' . $fontSize . 'rem;">';
            $html .= htmlspecialchars($tag);
            if ($count > 1) {
                $html .= ' <span class="badge bg-light text-dark ms-1">' . $count . '</span>';
            }
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
        
        // URL-dekodierte Route für Dateisystem-Zugriff
        $decodedRoute = urldecode($route);
        
        // Unicode-Normalisierung falls verfügbar
        if (class_exists('Normalizer') && function_exists('normalizer_normalize')) {
            $decodedRoute = normalizer_normalize($decodedRoute, Normalizer::FORM_C);
        } else {
            // Einfache Fallback-Normalisierung für häufige Fälle
            $decodedRoute = $this->simpleUnicodeNormalize($decodedRoute);
        }
        
        // Verschiedene Route-Varianten testen
        $routeVariants = [
            trim($decodedRoute, '/'),
            trim($route, '/'),
            trim(urldecode($route), '/')
        ];
        
        // Für jede Route-Variante verschiedene Pfade ausprobieren
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
            
            // Erweiterte Suche: Im Verzeichnis nach normalisierter Datei suchen
            $dir = dirname($contentDir . '/' . $routeVariant);
            $filename = basename($routeVariant) . $extension;
            
            if (is_dir($dir)) {
                $files = scandir($dir);
                if ($files) {
                    foreach ($files as $file) {
                        if ($file === '.' || $file === '..') continue;
                        
                        // Normalisiere beide Dateinamen zum Vergleich
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
        
        // Für Startseite
        if ($route === 'index' || $route === '') {
            $indexPaths = [
                $contentDir . '/home' . $extension,    // home.md hat Priorität
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
                    
                    // Behalte auch Original Yellow Keys für Kompatibilität
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
        
        // Route zu lesbarem Titel konvertieren
        $title = str_replace(['/', '-', '_'], ' ', $route);
        return ucwords($title);
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

        // Nach Änderungsdatum (modified) absteigend sortieren
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
                    'modified' => filemtime($fullPath)
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
        // AdminAuth-Klasse laden für Session-Prüfung
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
            'tech' => 3,
            'diy' => 4
        ];
        
        if (file_exists($settingsFile)) {
            $settings = json_decode(file_get_contents($settingsFile), true);
            if (isset($settings['navigation_order']) && is_array($settings['navigation_order'])) {
                return $settings['navigation_order'];
            }
        }
        
        return $defaultOrder;
    }
}