<?php

namespace StaticMD\Core;

use StaticMD\Utilities\FrontMatterParser;
use StaticMD\Utilities\TitleGenerator;
use StaticMD\Utilities\UrlHelper;

/**
 * SearchEngine-Klasse
 * Durchsucht Markdown-Inhalte nach Begriffen
 */
class SearchEngine
{
    private array $config;
    private ContentLoader $contentLoader;

    public function __construct(array $config)
    {
        $this->config = $config;
    }
    


    /**
     * Durchsucht alle Markdown-Dateien nach einem Begriff
     */
    public function search(string $query, ?int $limit = null): array
    {
        if (empty(trim($query))) {
            return [];
        }

        $query = trim($query);
        $results = [];
        $contentDir = $this->config['paths']['content'];
        $extension = $this->config['markdown']['file_extension'];

        // Limit aus Settings holen, falls nicht explizit übergeben
        if ($limit === null) {
            $settingsFile = $this->config['paths']['system'] . '/settings.json';
            if (file_exists($settingsFile)) {
                $settings = json_decode(file_get_contents($settingsFile), true);
                if (isset($settings['search_result_limit'])) {
                    $limit = (int)$settings['search_result_limit'];
                } else {
                    $limit = 50;
                }
            } else {
                $limit = 50;
            }
        }

        // Alle Dateien rekursiv durchsuchen
        $this->searchInDirectory($contentDir, $contentDir, $extension, $query, $results, $limit);

        // Nach Relevanz sortieren
        usort($results, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return array_slice($results, 0, $limit);
    }

    /**
     * Rekursive Verzeichnis-Durchsuchung
     */
    private function searchInDirectory(string $dir, string $baseDir, string $extension, string $query, array &$results, int $limit): void
    {
        if (count($results) >= $limit) {
            return;
        }

        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..' || count($results) >= $limit) {
                continue;
            }

            $fullPath = $dir . '/' . $item;

            if (is_dir($fullPath)) {
                $this->searchInDirectory($fullPath, $baseDir, $extension, $query, $results, $limit);
            } elseif (is_file($fullPath) && str_ends_with($item, $extension)) {
                $result = $this->searchInFile($fullPath, $baseDir, $extension, $query);
                if ($result !== null) {
                    $results[] = $result;
                }
            }
        }
    }

    /**
     * Durchsucht eine einzelne Datei
     */
    private function searchInFile(string $filePath, string $baseDir, string $extension, string $query): ?array
    {
        if (!is_readable($filePath)) {
            return null;
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            return null;
        }

        // Front Matter parsen
        $parsed = FrontMatterParser::parse($content);
        $meta = $parsed['meta'];
        $bodyContent = $parsed['content'];

        // Suche in verschiedenen Bereichen
        $queryLower = strtolower($query);
        $score = 0;
        $matches = [];

        // Titel (höchste Gewichtung)
        $title = $meta['Title'] ?? $meta['title'] ?? '';
        if (!empty($title) && stripos($title, $query) !== false) {
            $score += 10;
            $matches[] = 'title';
        }

        // Tags (hohe Gewichtung)
        $tags = $meta['Tag'] ?? $meta['tags'] ?? '';
        if (!empty($tags) && stripos($tags, $query) !== false) {
            $score += 8;
            $matches[] = 'tags';
        }

        // Author
        $author = $meta['Author'] ?? $meta['author'] ?? '';
        if (!empty($author) && stripos($author, $query) !== false) {
            $score += 5;
            $matches[] = 'author';
        }

        // Beschreibung
        $description = $meta['description'] ?? '';
        if (!empty($description) && stripos($description, $query) !== false) {
            $score += 6;
            $matches[] = 'description';
        }

        // Inhalt (niedrigere Gewichtung, aber mehrfache Treffer möglich)
        $contentMatches = substr_count(strtolower($bodyContent), $queryLower);
        if ($contentMatches > 0) {
            $score += $contentMatches * 2;
            $matches[] = 'content';
        }

        // Keine Treffer gefunden
        if ($score === 0) {
            return null;
        }

        // Route aus Dateipfad generieren
        $relativePath = str_replace($baseDir . '/', '', $filePath);
        $route = str_replace($extension, '', $relativePath);

        // Titel generieren falls nicht vorhanden
        if (empty($title)) {
            $title = TitleGenerator::fromRoute($route);
        }

        // Textvorschau generieren
        $preview = $this->generatePreview($bodyContent, $query, 200);

        return [
            'title' => $title,
            'route' => $route,
            'file_path' => $filePath,
            'description' => $description,
            'preview' => $preview,
            'tags' => $tags,
            'author' => $author,
            'modified' => filemtime($filePath),
            'score' => $score,
            'matches' => $matches,
            'query' => $query
        ];
    }

    /**
     * Generiert eine Textvorschau mit Hervorhebung
     */
    private function generatePreview(string $content, string $query, int $maxLength = 200): string
    {
        // Markdown-Syntax entfernen
        $cleanContent = $this->stripMarkdown($content);
        
        // Position des ersten Treffers finden
        $queryPos = stripos($cleanContent, $query);
        
        if ($queryPos === false) {
            // Kein Treffer gefunden, nehme Anfang
            $preview = substr($cleanContent, 0, $maxLength);
        } else {
            // Kontext um den Treffer herum
            $start = max(0, $queryPos - ($maxLength / 2));
            $preview = substr($cleanContent, $start, $maxLength);
            
            // Am Anfang abschneiden falls nötig
            if ($start > 0) {
                $preview = '...' . $preview;
            }
        }
        
        // Am Ende abschneiden falls nötig
        if (strlen($preview) >= $maxLength) {
            $preview .= '...';
        }

        // Query hervorheben (case-insensitive)
        $preview = preg_replace(
            '/(' . preg_quote($query, '/') . ')/i',
            '<mark>$1</mark>',
            $preview
        );

        return trim($preview);
    }

    /**
     * Entfernt Markdown-Syntax für saubere Textvorschau
     */
    private function stripMarkdown(string $content): string
    {
        // Überschriften
        $content = preg_replace('/^#+\s+/m', '', $content);
        
        // Code-Blöcke
        $content = preg_replace('/```[\s\S]*?```/', '', $content);
        $content = preg_replace('/`([^`]+)`/', '$1', $content);
        
        // Links und Bilder
        $content = preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $content);
        $content = preg_replace('/!\[([^\]]*)\]\([^)]+\)/', '$1', $content);
        
        // Formatierungen
        $content = preg_replace('/\*\*(.*?)\*\*/', '$1', $content);
        $content = preg_replace('/\*(.*?)\*/', '$1', $content);
        $content = preg_replace('/__(.*?)__/', '$1', $content);
        $content = preg_replace('/_(.*?)_/', '$1', $content);
        
        // Listen
        $content = preg_replace('/^\s*[\*\-\+]\s+/m', '', $content);
        $content = preg_replace('/^\s*\d+\.\s+/m', '', $content);
        
        // Blockquotes
        $content = preg_replace('/^\s*>\s*/m', '', $content);
        
        // Horizontale Linien
        $content = preg_replace('/^[\-\*_]{3,}$/m', '', $content);
        
        // Mehrfache Leerzeichen und Zeilenumbrüche normalisieren
        $content = preg_replace('/\s+/', ' ', $content);
        
        return trim($content);
    }

    /**
     * Erstellt HTML für Suchergebnisseite
     */
    public function generateSearchResultsHTML(array $results, string $query, float $totalTime = 0.0): string
    {
        $html = '<div class="search-results">';
        
        // Header
        $html .= '<div class="search-header mb-4">';
        $html .= '<h2><i class="bi bi-search me-2"></i>' . \StaticMD\Core\I18n::t('core.search_results_title', ['query' => htmlspecialchars($query)]) . '</h2>';
        
        if (!empty($results)) {
            $html .= '<p class="lead">';
            if ($totalTime > 0) {
                $html .= \StaticMD\Core\I18n::t('core.search_results_count_with_time', [
                    'count' => count($results),
                    'query' => '<strong>' . htmlspecialchars($query) . '</strong>',
                    'time' => number_format($totalTime, 3)
                ]);
            } else {
                $html .= \StaticMD\Core\I18n::t('core.search_results_count', [
                    'count' => count($results),
                    'query' => '<strong>' . htmlspecialchars($query) . '</strong>'
                ]);
            }
            $html .= '</p>';
        } else {
            $html .= '<p class="lead text-muted">' . \StaticMD\Core\I18n::t('core.search_no_results', [
                'query' => '<strong>' . htmlspecialchars($query) . '</strong>'
            ]) . '</p>';
        }
        $html .= '</div>';
        
        if (!empty($results)) {
            foreach ($results as $result) {
                $route = $result['route'];
                $title = $result['title'];
                $html .= '<div class="search-result mb-4 p-3 border rounded">';
                
                // Titel
                $html .= '<h3 class="mb-2">';
                $html .= '<a href="/' . UrlHelper::encodePath($route) . '" class="text-decoration-none">';
                $html .= '<i class="bi bi-file-earmark-text me-2"></i>';
                $html .= htmlspecialchars($title);
                $html .= '</a>';
                $html .= '</h3>';
                
                // URL
                $html .= '<div class="search-url mb-2">';
                $html .= '<small class="text-success">/' . htmlspecialchars($route) . '</small>';
                $html .= '</div>';
                
                // Vorschau
                if (!empty($result['preview'])) {
                    $html .= '<p class="search-preview mb-2">' . $result['preview'] . '</p>';
                }
                
                // Metadaten
                $html .= '<div class="search-meta">';
                
                if (!empty($result['tags'])) {
                    $tags = array_map('trim', explode(',', $result['tags']));
                    $html .= '<div class="mb-1">';
                    foreach ($tags as $tag) {
                        if (!empty($tag)) {
                            $html .= '<a href="/tag/' . urlencode($tag) . '" class="badge bg-secondary me-1 text-decoration-none">' . htmlspecialchars($tag) . '</a>';
                        }
                    }
                    $html .= '</div>';
                }
                
                $html .= '<small class="text-muted">';
                if (!empty($result['author'])) {
                    $html .= '<i class="bi bi-person me-1"></i>' . htmlspecialchars($result['author']) . ' • ';
                }
                $html .= '<i class="bi bi-calendar me-1"></i>' . date('d.m.Y', $result['modified']);
                $html .= ' • Score: ' . $result['score'];
                $html .= '</small>';
                
                $html .= '</div>'; // search-meta
                $html .= '</div>'; // search-result
            }
        } else {
            $html .= '<div class="alert alert-info">';
            $html .= '<h5><i class="bi bi-info-circle me-2"></i>' . I18n::t('core.no_results_found') . '</h5>';
            $html .= '<p class="mb-0">' . I18n::t('core.search_suggestions') . '</p>';
            $html .= '</div>';
        }
        
        $html .= '</div>'; // search-results
        
        return $html;
    }
    
    /**
     * Sucht alle Seiten mit einem bestimmten Tag
     */
    public function searchByTag(string $tagName): array
    {
        $results = [];
        $contentDir = $this->config['paths']['content'];
        $extension = $this->config['markdown']['file_extension'];
        
        // Alle Dateien rekursiv durchsuchen
        $this->searchTagInDirectory($contentDir, $contentDir, $extension, $tagName, $results);
        
        // Nach Titel sortieren
        usort($results, function($a, $b) {
            return strcmp($a['title'], $b['title']);
        });
        
        return $results;
    }
    
    /**
     * Rekursive Tag-Suche in Verzeichnis
     */
    private function searchTagInDirectory(string $dir, string $baseDir, string $extension, string $tagName, array &$results): void
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
                $this->searchTagInDirectory($fullPath, $baseDir, $extension, $tagName, $results);
            } elseif (is_file($fullPath) && str_ends_with($item, $extension)) {
                $result = $this->searchTagInFile($fullPath, $baseDir, $extension, $tagName);
                if ($result !== null) {
                    $results[] = $result;
                }
            }
        }
    }
    
    /**
     * Sucht Tag in einer einzelnen Datei
     */
    private function searchTagInFile(string $filePath, string $baseDir, string $extension, string $tagName): ?array
    {
        if (!is_readable($filePath)) {
            return null;
        }
        
        $content = file_get_contents($filePath);
        if ($content === false) {
            return null;
        }
        
        // Front Matter parsen
        $parsed = FrontMatterParser::parse($content);
        $meta = $parsed['meta'];
        $bodyContent = $parsed['content'];
        
        // Tags prüfen
        $tags = $meta['Tag'] ?? $meta['tags'] ?? '';
        if (empty($tags)) {
            return null;
        }
        
        // Tag in Tags-Liste suchen (case-insensitive)
        $fileTags = array_map('trim', explode(',', $tags));
        $tagFound = false;
        foreach ($fileTags as $fileTag) {
            if (strcasecmp(trim($fileTag), $tagName) === 0) {
                $tagFound = true;
                break;
            }
        }
        
        if (!$tagFound) {
            return null;
        }
        
        // Route aus Dateipfad generieren
        $relativePath = str_replace($baseDir . '/', '', $filePath);
        $route = str_replace($extension, '', $relativePath);
        
        // Titel und andere Metadaten
        $title = $meta['Title'] ?? $meta['title'] ?? TitleGenerator::fromRoute($route);
        $description = $meta['description'] ?? '';
        $author = $meta['Author'] ?? $meta['author'] ?? '';
        
        // Beschreibung aus Inhalt generieren falls leer
        if (empty($description)) {
            $cleanContent = $this->stripMarkdown($bodyContent);
            $description = substr($cleanContent, 0, 200);
            if (strlen($cleanContent) > 200) {
                $description .= '...';
            }
        }
        
        return [
            'title' => $title,
            'route' => $route,
            'file_path' => $filePath,
            'description' => $description,
            'tags' => $tags,
            'author' => $author,
            'modified' => filemtime($filePath)
        ];
    }
    
    /**
     * Generiert HTML für Tag-Seite
     */
    public function generateTagPageHTML(array $results, string $tagName, float $searchTime = 0.0): string
    {
        $html = '<div class="tag-page">';
        
        // Header
        $html .= '<div class="tag-header mb-4">';
        $html .= '<h2><i class="bi bi-tag me-2"></i>Tag: ' . htmlspecialchars($tagName) . '</h2>';
        
        if (!empty($results)) {
            $html .= '<p class="lead">';
            $html .= I18n::t('core.pages_with_tag_count', ['count' => count($results), 'tag' => htmlspecialchars($tagName)]);
            if ($searchTime > 0) {
                $html .= ' (' . number_format($searchTime, 3) . ' Sekunden)';
            }
            $html .= '</p>';
        } else {
            $html .= '<p class="lead text-muted">' . I18n::t('core.no_pages_with_tag', ['tag' => htmlspecialchars($tagName)]) . '</p>';
        }
        $html .= '</div>';
        
        if (!empty($results)) {
            foreach ($results as $result) {
                $route = $result['route'];
                $title = $result['title'];
                $html .= '<div class="tag-result mb-4 p-3 border rounded">';
                
                // Titel
                $html .= '<h3 class="mb-2">';
                $html .= '<a href="/' . UrlHelper::encodePath($route) . '" class="text-decoration-none">';
                $html .= '<i class="bi bi-file-earmark-text me-2"></i>';
                $html .= htmlspecialchars($title);
                $html .= '</a>';
                $html .= '</h3>';
                
                // URL
                $html .= '<div class="tag-url mb-2">';
                $html .= '<small class="text-success">/' . htmlspecialchars($route) . '</small>';
                $html .= '</div>';
                
                // Beschreibung
                if (!empty($result['description'])) {
                    $html .= '<p class="tag-description mb-2">' . htmlspecialchars($result['description']) . '</p>';
                }
                
                // Metadaten
                $html .= '<div class="tag-meta">';
                
                // Alle Tags anzeigen
                if (!empty($result['tags'])) {
                    $tags = array_map('trim', explode(',', $result['tags']));
                    $html .= '<div class="mb-1">';
                    foreach ($tags as $tag) {
                        if (!empty($tag)) {
                            $isCurrentTag = strcasecmp($tag, $tagName) === 0;
                            $badgeClass = $isCurrentTag ? 'bg-primary' : 'bg-secondary';
                            $html .= '<a href="/tag/' . urlencode($tag) . '" class="badge ' . $badgeClass . ' me-1 text-decoration-none">' . htmlspecialchars($tag) . '</a>';
                        }
                    }
                    $html .= '</div>';
                }
                
                $html .= '<small class="text-muted">';
                if (!empty($result['author'])) {
                    $html .= '<i class="bi bi-person me-1"></i>' . htmlspecialchars($result['author']) . ' • ';
                }
                $html .= '<i class="bi bi-calendar me-1"></i>' . date('d.m.Y', $result['modified']);
                $html .= '</small>';
                
                $html .= '</div>'; // tag-meta
                $html .= '</div>'; // tag-result
            }
        } else {
            $html .= '<div class="alert alert-info">';
            $html .= '<h5><i class="bi bi-info-circle me-2"></i>' . I18n::t('core.no_pages_found_short') . '</h5>';
            $html .= '<p class="mb-0">' . I18n::t('core.no_pages_with_tag_short', ['tag' => htmlspecialchars($tagName)]) . '</p>';
            $html .= '</div>';
        }
        
        $html .= '</div>'; // tag-page
        
        return $html;
    }
    
    /**
     * Sammelt alle Tags aus allen Dateien
     */
    public function getAllTags(): array
    {
        $allTags = [];
        $contentDir = $this->config['paths']['content'];
        $extension = $this->config['markdown']['file_extension'];
        
        // Alle Dateien rekursiv durchsuchen
        $this->collectAllTagsFromDirectory($contentDir, $contentDir, $extension, $allTags);
        
        // Alphabetisch sortieren (nach Tag-Namen)
        ksort($allTags, SORT_STRING | SORT_FLAG_CASE);
        
        return $allTags;
    }
    
    /**
     * Rekursive Tag-Sammlung aus Verzeichnis
     */
    private function collectAllTagsFromDirectory(string $dir, string $baseDir, string $extension, array &$allTags): void
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
                $this->collectAllTagsFromDirectory($fullPath, $baseDir, $extension, $allTags);
            } elseif (is_file($fullPath) && str_ends_with($item, $extension)) {
                $this->collectTagsFromFile($fullPath, $allTags);
            }
        }
    }
    
    /**
     * Sammelt Tags aus einer einzelnen Datei
     */
    private function collectTagsFromFile(string $filePath, array &$allTags): void
    {
        if (!is_readable($filePath)) {
            return;
        }
        
        $content = file_get_contents($filePath);
        if ($content === false) {
            return;
        }
        
        // Front Matter parsen
        $parsed = FrontMatterParser::parse($content);
        $meta = $parsed['meta'];
        
        // Tags extrahieren
        $tags = $meta['Tag'] ?? $meta['tags'] ?? '';
        if (empty($tags)) {
            return;
        }
        
        $fileTags = array_map('trim', explode(',', $tags));
        foreach ($fileTags as $tag) {
            $tag = trim($tag);
            if (!empty($tag)) {
                $allTags[$tag] = ($allTags[$tag] ?? 0) + 1;
            }
        }
    }
    
    /**
     * Generiert HTML für Alle-Tags-Seite
     */
    public function generateAllTagsHTML(array $allTags, float $searchTime = 0.0): string
    {
        $html = '<div class="all-tags-page">';
        
        // Header
        $html .= '<div class="all-tags-header mb-4">';
        $html .= '<h2><i class="bi bi-tags me-2"></i>Alle Tags</h2>';
        
        if (!empty($allTags)) {
            $html .= '<p class="lead">';
            $html .= I18n::t('core.tags_found_count', ['count' => count($allTags)]);
            if ($searchTime > 0) {
                $html .= ' (' . number_format($searchTime, 3) . ' Sekunden)';
            }
            $html .= '</p>';
        } else {
            $html .= '<p class="lead text-muted">' . I18n::t('core.no_tags_found_short') . '</p>';
        }
        $html .= '</div>';
        
        if (!empty($allTags)) {
            // Tag-Cloud mit allen Tags
            $html .= '<div class="all-tags-cloud mb-4">';
            
            foreach ($allTags as $tag => $count) {
                // Tag-Größe basierend auf Häufigkeit (wie in ContentLoader)
                $size = min(3, max(1, (int)floor($count / 2) + 1));
                $badgeClass = $size === 1 ? 'bg-secondary' : ($size === 2 ? 'bg-primary' : 'bg-success');
                
                //$fontSize = number_format(0.8 + $size * 0.15, 2);
                //$html .= '<a href="/tag/' . urlencode($tag) . '" class="badge ' . $badgeClass . ' me-2 mb-2 text-decoration-none" style="font-size: ' . $fontSize . 'rem;">';
                $html .= '<a href="/tag/' . urlencode($tag) . '" class="badge ' . $badgeClass . ' me-2 mb-2 text-decoration-none">';
                $html .= htmlspecialchars($tag);
                $html .= ' <span class="badge bg-light text-dark ms-1">' . $count . '</span>';
                $html .= '</a>';
            }
            
            $html .= '</div>';
            
        } else {
            $html .= '<div class="alert alert-info">';
            $html .= '<h5><i class="bi bi-info-circle me-2"></i>' . I18n::t('core.no_tags_found_short') . '</h5>';
            $html .= '<p class="mb-0">' . I18n::t('core.no_tags_used') . '</p>';
            $html .= '</div>';
        }
        
        $html .= '</div>'; // all-tags-page
        
        return $html;
    }
}