<?php

namespace StaticMD\Core;

/**
 * Main application class
 * Coordinates all system components
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
     * Runs the application
     */
    public function run(): void
    {
    // Determine route
        $route = $this->router->getRoute();
        
    // Handle search route
        if ($route === 'search') {
            $this->handleSearch();
            return;
        }
        
    // Handle tag overview
        if ($route === 'tag') {
            $this->handleTagOverview();
            return;
        }
        
    // Handle tag route
        if (str_starts_with($route, 'tag/')) {
            $this->handleTagPage($route);
            return;
        }
        
    // Load content
        $content = $this->contentLoader->load($route);
        
        if ($content === null) {
            http_response_code(404);
            $content = [
                'title' => I18n::t('core.404_title'),
                'content' => '<h1>404 - ' . I18n::t('core.404_title') . '</h1><p>' . I18n::t('core.404_message') . '</p>'
            ];
        } else {
            // Check if page is private and user is not logged in
            $visibility = $content['meta']['Visibility'] ?? $content['meta']['visibility'] ?? 'public';
            if ($visibility === 'private' && !$this->isAdminLoggedIn()) {
                http_response_code(404);
                $content = [
                    'title' => I18n::t('core.404_title'),
                    'content' => '<h1>404 - ' . I18n::t('core.404_title') . '</h1><p>' . I18n::t('core.404_message') . '</p>'
                ];
            }
        }
        
    // Prepare template data
        $template = $this->config['theme']['template'] ?? 'default';
        $templateData = [
            'config' => $this->config,
            'content' => $content,
            'current_route' => $route
        ];
        
    // Render and output template
        $this->templateEngine->render($template, $templateData);
    }
    
    /**
     * Handles search requests
     */
    private function handleSearch(): void
    {
        require_once __DIR__ . '/SearchEngine.php';
        
        $query = $_GET['q'] ?? '';
        $searchEngine = new \StaticMD\Core\SearchEngine($this->config, $this->contentLoader);
        
        $results = [];
        $searchTime = 0;

    // Load limit from settings
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
        
    // Display search results as content
        $searchHTML = $searchEngine->generateSearchResultsHTML($results, $query, $searchTime);
        
        $content = [
            'title' => empty($query) ? I18n::t('core.search_title') : I18n::t('core.search_results_title', ['query' => $query]),
            'content' => $searchHTML,
            'meta' => [
                'title' => empty($query) ? I18n::t('core.search_title') : I18n::t('core.search_results_title', ['query' => $query]),
                'description' => I18n::t('core.search_description'),
                'search_results' => true,
                'search_query' => $query,
                'search_count' => count($results)
            ]
        ];
        
    // Prepare template data
        $template = $this->config['theme']['template'] ?? 'default';
        $templateData = [
            'config' => $this->config,
            'content' => $content,
            'current_route' => 'search'
        ];
        
        $this->templateEngine->render($template, $templateData);
    }
    
    /**
     * Handles tag overview page
     */
    private function handleTagOverview(): void
    {
        require_once __DIR__ . '/SearchEngine.php';
        
        $searchEngine = new \StaticMD\Core\SearchEngine($this->config, $this->contentLoader);
        
        $startTime = microtime(true);
        $allTags = $searchEngine->getAllTags();
        $searchTime = microtime(true) - $startTime;
        
    // Display tag overview as content
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
        
    // Prepare template data
        $template = $this->config['theme']['template'] ?? 'default';
        $templateData = [
            'config' => $this->config,
            'content' => $content,
            'current_route' => 'tag'
        ];
        
        $this->templateEngine->render($template, $templateData);
    }
    
    /**
     * Handles tag pages
     */
    private function handleTagPage(string $route): void
    {
        require_once __DIR__ . '/SearchEngine.php';
        
    // Extract tag from route: tag/tagname
        $tagName = substr($route, 4); // Remove "tag/"
        $tagName = urldecode($tagName);
        
        if (empty($tagName)) {
            http_response_code(404);
            return;
        }
        
        $searchEngine = new \StaticMD\Core\SearchEngine($this->config, $this->contentLoader);
        
        $startTime = microtime(true);
        $results = $searchEngine->searchByTag($tagName);
        $searchTime = microtime(true) - $startTime;
        
    // Display tag results as content
        $tagHTML = $searchEngine->generateTagPageHTML($results, $tagName, $searchTime);
        
        $content = [
            'title' => I18n::t('core.tag_title', ['tag' => $tagName]),
            'content' => $tagHTML,
            'meta' => [
                'title' => I18n::t('core.tag_title', ['tag' => $tagName]),
                'description' => I18n::t('core.tag_description', ['tag' => $tagName]),
                'tag_page' => true,
                'tag_name' => $tagName,
                'result_count' => count($results)
            ]
        ];
        
    // Prepare template data
        $template = $this->config['theme']['template'] ?? 'default';
        $templateData = [
            'config' => $this->config,
            'content' => $content,
            'current_route' => $route
        ];
        
        $this->templateEngine->render($template, $templateData);
    }

    /**
     * Returns the current configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }
    
    /**
     * Checks if an admin is logged in
     */
    private function isAdminLoggedIn(): bool
    {
    // Load AdminAuth class for session check
        require_once $this->config['paths']['system'] . '/admin/AdminAuth.php';
        $adminAuth = new \StaticMD\Admin\AdminAuth($this->config);
        
        return $adminAuth->isLoggedIn();
    }
}