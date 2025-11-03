<?php

namespace StaticMD\Core;

/**
 * Hauptanwendungsklasse
 * Koordiniert alle Komponenten des Systems
 */
class Application
{
    private array $config;
    private Router $router;
    private ContentLoader $contentLoader;
    private TemplateEngine $templateEngine;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->router = new Router($config);
        $this->contentLoader = new ContentLoader($config);
        $this->templateEngine = new TemplateEngine($config, $this->contentLoader);
    }

    /**
     * Führt die Anwendung aus
     */
    public function run(): void
    {
        // Route ermitteln
        $route = $this->router->getRoute();
        
        // Such-Route behandeln
        if ($route === 'search') {
            $this->handleSearch();
            return;
        }
        
        // Tag-Übersicht behandeln
        if ($route === 'tag') {
            $this->handleTagOverview();
            return;
        }
        
        // Tag-Route behandeln
        if (str_starts_with($route, 'tag/')) {
            $this->handleTagPage($route);
            return;
        }
        
        // Content laden
        $content = $this->contentLoader->load($route);
        
        if ($content === null) {
            http_response_code(404);
            $content = [
                'title' => I18n::t('core.404_title'),
                'content' => '<h1>404 - ' . I18n::t('core.404_title') . '</h1><p>' . I18n::t('core.404_message') . '</p>'
            ];
        } else {
            // Prüfen ob Seite privat ist und Benutzer nicht angemeldet
            $visibility = $content['meta']['Visibility'] ?? $content['meta']['visibility'] ?? 'public';
            if ($visibility === 'private' && !$this->isAdminLoggedIn()) {
                http_response_code(404);
                $content = [
                    'title' => I18n::t('core.404_title'),
                    'content' => '<h1>404 - ' . I18n::t('core.404_title') . '</h1><p>' . I18n::t('core.404_message') . '</p>'
                ];
            }
        }
        
        // Template-Daten vorbereiten
        $template = $this->config['theme']['template'] ?? 'default';
        $templateData = [
            'config' => $this->config,
            'content' => $content,
            'current_route' => $route
        ];
        
        // Template rendern und ausgeben
        $this->templateEngine->render($template, $templateData);
    }
    
    /**
     * Behandelt Such-Anfragen
     */
    private function handleSearch(): void
    {
        require_once __DIR__ . '/SearchEngine.php';
        
        $query = $_GET['q'] ?? '';
        $searchEngine = new \StaticMD\Core\SearchEngine($this->config, $this->contentLoader);
        
        $results = [];
        $searchTime = 0;

        // Limit aus Settings laden
        $settingsFile = $this->config['paths']['system'] . '/settings.json';
        $limit = 50;
        if (file_exists($settingsFile)) {
            $settings = json_decode(file_get_contents($settingsFile), true);
            if (isset($settings['search_result_limit'])) {
                $limit = (int)$settings['search_result_limit'];
            }
        }

        if (!empty(trim($query))) {
            $startTime = microtime(true);
            $results = $searchEngine->search($query, $limit);
            $searchTime = microtime(true) - $startTime;
        }
        
        // Suchergebnisse als Content darstellen
        $searchHTML = $searchEngine->generateSearchResultsHTML($results, $query, $searchTime);
        
        $content = [
            'title' => empty($query) ? I18n::t('core.search_title') : sprintf(I18n::t('core.search_results_title'), $query),
            'content' => $searchHTML,
            'meta' => [
                'title' => empty($query) ? I18n::t('core.search_title') : sprintf(I18n::t('core.search_results_title'), $query),
                'description' => I18n::t('core.search_description'),
                'search_results' => true,
                'search_query' => $query,
                'search_count' => count($results)
            ]
        ];
        
        // Template-Daten vorbereiten
        $template = $this->config['theme']['template'] ?? 'default';
        $templateData = [
            'config' => $this->config,
            'content' => $content,
            'current_route' => 'search'
        ];
        
        $this->templateEngine->render($template, $templateData);
    }
    
    /**
     * Behandelt Tag-Übersichtsseite
     */
    private function handleTagOverview(): void
    {
        require_once __DIR__ . '/SearchEngine.php';
        
        $searchEngine = new \StaticMD\Core\SearchEngine($this->config, $this->contentLoader);
        
        $startTime = microtime(true);
        $allTags = $searchEngine->getAllTags();
        $searchTime = microtime(true) - $startTime;
        
        // Tag-Übersicht als Content darstellen
        $tagHTML = $searchEngine->generateAllTagsHTML($allTags, $searchTime);
        
        $content = [
            'title' => I18n::t('core.tags_title'),
            'content' => $tagHTML,
            'meta' => [
                'title' => I18n::t('core.tags_overview_title'),
                'description' => I18n::t('core.tags_overview_description'),
                'tag_overview' => true,
                'tag_count' => count($allTags)
            ]
        ];
        
        // Template-Daten vorbereiten
        $template = $this->config['theme']['template'] ?? 'default';
        $templateData = [
            'config' => $this->config,
            'content' => $content,
            'current_route' => 'tag'
        ];
        
        $this->templateEngine->render($template, $templateData);
    }
    
    /**
     * Behandelt Tag-Seiten
     */
    private function handleTagPage(string $route): void
    {
        require_once __DIR__ . '/SearchEngine.php';
        
        // Tag aus Route extrahieren: tag/tagname
        $tagName = substr($route, 4); // Entferne "tag/"
        $tagName = urldecode($tagName);
        
        if (empty($tagName)) {
            http_response_code(404);
            return;
        }
        
        $searchEngine = new \StaticMD\Core\SearchEngine($this->config, $this->contentLoader);
        
        $startTime = microtime(true);
        $results = $searchEngine->searchByTag($tagName);
        $searchTime = microtime(true) - $startTime;
        
        // Tag-Ergebnisse als Content darstellen
        $tagHTML = $searchEngine->generateTagPageHTML($results, $tagName, $searchTime);
        
        $content = [
            'title' => sprintf(I18n::t('core.tag_title'), $tagName),
            'content' => $tagHTML,
            'meta' => [
                'title' => sprintf(I18n::t('core.tag_title'), $tagName),
                'description' => sprintf(I18n::t('core.tag_description'), $tagName),
                'tag_page' => true,
                'tag_name' => $tagName,
                'result_count' => count($results)
            ]
        ];
        
        // Template-Daten vorbereiten
        $template = $this->config['theme']['template'] ?? 'default';
        $templateData = [
            'config' => $this->config,
            'content' => $content,
            'current_route' => $route
        ];
        
        $this->templateEngine->render($template, $templateData);
    }

    /**
     * Gibt die aktuelle Konfiguration zurück
     */
    public function getConfig(): array
    {
        return $this->config;
    }
    
    /**
     * Prüft ob ein Admin angemeldet ist
     */
    private function isAdminLoggedIn(): bool
    {
        // AdminAuth-Klasse laden für Session-Prüfung
        require_once $this->config['paths']['system'] . '/admin/AdminAuth.php';
        $adminAuth = new \StaticMD\Admin\AdminAuth($this->config);
        
        return $adminAuth->isLoggedIn();
    }
}