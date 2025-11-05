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
        
    // Create main navigation
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
                // Load title from front matter
                if (isset($page['path']) && file_exists($page['path'])) {
                    $content = file_get_contents($page['path']);
                    $page['title'] = $this->parseTitle($content, $page['route']);
                } else {
                    $page['title'] = $this->generateTitle($page['route']);
                }
                
                $navItems[$section]['pages'][] = $page;
            }
        }
        
    // Sort dropdown pages alphabetically (case-insensitive)
        foreach ($navItems as $section => $nav) {
            if (!empty($nav['pages'])) {
                usort($navItems[$section]['pages'], function($a, $b) {
                    $titleA = $a['title'] ?? basename($a['route']);
                    $titleB = $b['title'] ?? basename($b['route']);
                    return strcasecmp($titleA, $titleB);
                });
            }
        }
        
    // Sort navigation - load from settings
        $navigationOrder = $this->contentLoader->getNavigationOrder();
        
    // Apply sorting
        uksort($navItems, function($a, $b) use ($navigationOrder) {
            $orderA = $navigationOrder[$a] ?? 999;
            $orderB = $navigationOrder[$b] ?? 999;
            
            if ($orderA === $orderB) {
                // If same weight, sort alphabetically
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
    // Detect front matter (--- at the beginning)
        if (strpos($content, '---') === 0) {
            $parts = explode('---', $content, 3);
            
            if (count($parts) >= 3) {
                $frontMatter = trim($parts[1]);
                
                // Simple key-value parsing
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
        
    // Fallback: generate title from route
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
        
    // Convert route to readable title
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