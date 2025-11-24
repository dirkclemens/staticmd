<?php

namespace StaticMD\Core;

use StaticMD\Utilities\FrontMatterParser;
use StaticMD\Utilities\TitleGenerator;
use StaticMD\Utilities\UrlHelper;

/**
 * NavigationBuilder
 * Generiert Navigation und Breadcrumbs
 */
class NavigationBuilder
{
    private array $config;
    
    public function __construct(array $config)
    {
        $this->config = $config;
    }
    
    /**
     * Generiert Breadcrumb-Navigation für eine Route
     * 
     * @param string $route Aktuelle Route
     * @param callable $fileFinderCallback Callback zum Finden von Content-Dateien
     * @return array Array von Breadcrumb-Elementen
     */
    public function getBreadcrumbs(string $route, callable $fileFinderCallback = null): array
    {
        $breadcrumbs = [['title' => 'Home', 'route' => '', 'url' => '/', 'is_last' => false]];
        
        if (empty($route) || $route === 'index') {
            $breadcrumbs[0]['is_last'] = true;
            return $breadcrumbs;
        }
        
        $parts = explode('/', trim($route, '/'));
        $currentPath = '';
        
        foreach ($parts as $index => $part) {
            $currentPath .= ($currentPath ? '/' : '') . $part;
            
            $title = $this->getBreadcrumbTitle($currentPath, $part, $fileFinderCallback);
            $url = '/' . UrlHelper::encodePath($currentPath);
            
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
    private function getBreadcrumbTitle(string $route, string $fallback, callable $fileFinderCallback = null): string
    {
        if (!$fileFinderCallback) {
            return TitleGenerator::fromRoute($fallback);
        }
        
        // Versuche echten Titel aus Datei zu holen
        $contentFile = call_user_func($fileFinderCallback, $route);
        if ($contentFile && is_readable($contentFile)) {
            $rawContent = file_get_contents($contentFile);
            $parsed = FrontMatterParser::parse($rawContent);
            $title = $parsed['meta']['Title'] ?? $parsed['meta']['title'] ?? null;
            if (!empty($title)) {
                return $title;
            }
        }
        
        // Versuche index.md im Ordner
        $indexFile = call_user_func($fileFinderCallback, $route . '/index');
        if ($indexFile && is_readable($indexFile)) {
            $rawContent = file_get_contents($indexFile);
            $parsed = FrontMatterParser::parse($rawContent);
            $title = $parsed['meta']['Title'] ?? $parsed['meta']['title'] ?? null;
            if (!empty($title)) {
                return $title;
            }
        }
        
        return TitleGenerator::fromRoute($fallback);
    }
    
    /**
     * Lädt Navigation-Sortierung aus Settings
     * 
     * @return array Navigation-Order Array
     */
    public function getNavigationOrder(): array
    {
        $settingsFile = $this->config['paths']['system'] . '/settings.json';
        
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
}
