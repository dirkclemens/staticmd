<?php

namespace StaticMD\Themes;

use StaticMD\Core\ContentLoader;

/**
 * Theme Helper Klasse
 * Gemeinsame Funktionen für alle Themes
 */
class ThemeHelper
{
    private ContentLoader $contentLoader;
    
    public function __construct(ContentLoader $contentLoader)
    {
        $this->contentLoader = $contentLoader;
    }
    
    /**
     * Erstellt Navigation mit Titeln und Sortierung
     */
    public function buildNavigation(): array
    {
        $pages = $this->contentLoader->listAll();
        
        // Hauptnavigation erstellen
        $navItems = [];
        foreach ($pages as $page) {
            $parts = explode('/', $page['route']);
            $section = $parts[0];
            
            if (!isset($navItems[$section])) {
                $navItems[$section] = [
                    'title' => ucwords(str_replace(['-', '_'], ' ', $section)),
                    'route' => $section,
                    'pages' => []
                ];
            }
            
            if (count($parts) > 1) {
                // Titel aus Front Matter laden
                if (isset($page['path']) && file_exists($page['path'])) {
                    $content = file_get_contents($page['path']);
                    $page['title'] = $this->parseTitle($content, $page['route']);
                } else {
                    $page['title'] = $this->generateTitle($page['route']);
                }
                
                $navItems[$section]['pages'][] = $page;
            }
        }
        
        // Dropdown-Seiten alphabetisch sortieren (case-insensitive)
        foreach ($navItems as $section => $nav) {
            if (!empty($nav['pages'])) {
                usort($navItems[$section]['pages'], function($a, $b) {
                    $titleA = $a['title'] ?? basename($a['route']);
                    $titleB = $b['title'] ?? basename($b['route']);
                    return strcasecmp($titleA, $titleB);
                });
            }
        }
        
        // Navigation sortieren - aus Einstellungen laden
        $navigationOrder = $this->contentLoader->getNavigationOrder();
        
        // Sortierung anwenden
        uksort($navItems, function($a, $b) use ($navigationOrder) {
            $orderA = $navigationOrder[$a] ?? 999;
            $orderB = $navigationOrder[$b] ?? 999;
            
            if ($orderA === $orderB) {
                // Bei gleicher Gewichtung alphabetisch sortieren
                return strcmp($a, $b);
            }
            
            return $orderA <=> $orderB;
        });
        
        return $navItems;
    }
    
    /**
     * Parst Front Matter aus Markdown-Content
     */
    public function parseTitle(string $content, string $route): string
    {
        // Front Matter erkennen (--- am Anfang)
        if (strpos($content, '---') === 0) {
            $parts = explode('---', $content, 3);
            
            if (count($parts) >= 3) {
                $frontMatter = trim($parts[1]);
                
                // Einfaches Key-Value Parsing
                $lines = explode("\n", $frontMatter);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line) || strpos($line, ':') === false) {
                        continue;
                    }
                    
                    [$key, $value] = explode(':', $line, 2);
                    $cleanKey = trim($key);
                    $cleanValue = trim($value, ' "\'');
                    
                    if (strtolower($cleanKey) === 'title') {
                        return $cleanValue;
                    }
                }
            }
        }
        
        // Fallback: Titel aus Route generieren
        return $this->generateTitle($route);
    }
    
    /**
     * Generiert einen Titel aus der Route
     */
    public function generateTitle(string $route): string
    {
        if ($route === 'index') {
            return 'StaticMD';
        }
        
        // Route zu lesbarem Titel konvertieren
        $title = str_replace(['/', '-', '_'], ' ', $route);
        return ucwords($title);
    }
    
    /**
     * Hilfsfunktion für encodeUrlPath (falls in Themes benötigt)
     */
    public static function encodeUrlPath(string $path): string
    {
        $parts = explode('/', $path);
        $encodedParts = array_map('rawurlencode', $parts);
        return implode('/', $encodedParts);
    }
}