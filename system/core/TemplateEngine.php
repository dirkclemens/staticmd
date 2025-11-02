<?php

namespace StaticMD\Core;

require_once __DIR__ . '/Logger.php';
/**
 * Template-Engine
 * Rendert HTML-Templates mit Bootstrap
 */
class TemplateEngine
{
    private array $config;
    private ContentLoader $contentLoader;

    public function __construct(array $config, ContentLoader $contentLoader)
    {
        $this->config = $config;
        $this->contentLoader = $contentLoader;
    }

    /**
     * Rendert Content mit Template
     */
    public function render(string $template, array $templateData): void
    {
        $meta = $templateData['content']['meta'] ?? [];
        $templatePath = $this->getTemplatePath($meta);
        
        if (!file_exists($templatePath)) {
            // Fallback: Einfaches HTML generieren
            echo $this->renderSimple($templateData['content']);
            return;
        }
        
        // Template-Variablen extrahieren
        extract($templateData);
        
        // Spezielle Template-Variablen setzen
        $title = $templateData['content']['title'] ?? 'Unbenannte Seite';
        $body = $templateData['content']['content'] ?? '';
        $meta = $templateData['content']['meta'] ?? [];
        $currentRoute = $templateData['current_route'] ?? '';
        $siteName = $this->config['system']['name'] ?? 'StaticMD';
        
        // Navigation generieren
        $navItems = $this->generateNavigation();
        
        // Template einbinden und ausgeben
        include $templatePath;
    }

    /**
     * Generiert Navigation aus Content-Struktur
     */
    private function generateNavigation(): array
    {
        // Vollständige Navigation aus ContentLoader laden
        $pages = $this->contentLoader->listAll();
        
        // Titel aus Markdown-Headern laden
        foreach ($pages as &$page) {
            if (isset($page['path']) && file_exists($page['path'])) {
                $content = file_get_contents($page['path']);
                $frontMatter = $this->parseFrontMatter($content);
                $page['title'] = $frontMatter['meta']['Title'] ?? $frontMatter['meta']['title'] ?? $this->generateTitle($page['route']);
            } else {
                $page['title'] = $this->generateTitle($page['route']);
            }
        }
        
        return $pages;
    }
    
    /**
     * Parst Front Matter aus Markdown-Content
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
                    
                    $meta[$cleanKey] = $cleanValue;
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
            return $this->config['system']['name'] ?? 'StaticMD';
        }
        
        // Route zu lesbarem Titel konvertieren
        $title = str_replace(['/', '-', '_'], ' ', $route);
        return ucwords($title);
    }

    /**
     * Ermittelt den Template-Pfad
     */
    private function getTemplatePath(array $meta = []): string
    {
        // Frontend-Theme aus Settings laden
        $settingsFile = $this->config['paths']['system'] . '/settings.json';
        \StaticMD\Core\Logger::info("########### Using settingsFile: $settingsFile");
        $settings = [];
        if (file_exists($settingsFile)) {
            $settings = json_decode(file_get_contents($settingsFile), true) ?: [];
        }

        // Theme aus Settings oder Fallback
        $themeName = $settings['frontend_theme'] ?? $this->config['theme']['default'];
        $extension = $this->config['theme']['template_extension'];

        // Layout aus Metadaten ermitteln
        $layout = '';
        if (!empty($meta['Layout'])) {
            $layout = strtolower($meta['Layout']);
        } elseif (!empty($meta['layout'])) {
            $layout = strtolower($meta['layout']);
        }
        \StaticMD\Core\Logger::info("1) Using layout: $layout");

        // Erlaubte Layouts
        $allowedLayouts = ['wiki', 'blog', 'page', 'gallery'];
        if ($layout && in_array($layout, $allowedLayouts, true)) {
            $layoutFile = $this->config['paths']['themes'] . '/' . $themeName . '/' . $layout . $extension;
            if (file_exists($layoutFile)) {
                \StaticMD\Core\Logger::info("2) Using layout template: $layoutFile");
                return $layoutFile;
            }
        }

        // Fallback: Standard-Template
        return $this->config['paths']['themes'] . '/' . $themeName . '/template' . $extension;
    }

    /**
     * Einfaches HTML-Rendering (Fallback)
     */
    private function renderSimple(array $content): string
    {
        $title = htmlspecialchars($content['title']);
        $body = $content['content'];
        
        return <<<HTML
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>$title</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding-top: 2rem; }
        .content { margin-bottom: 3rem; }
        pre { background-color: #f8f9fa; padding: 1rem; border-radius: 0.5rem; }
        code { background-color: #f8f9fa; padding: 0.2rem 0.4rem; border-radius: 0.25rem; }
        blockquote { border-left: 4px solid #007bff; padding-left: 1rem; margin-left: 0; color: #6c757d; }
    </style>
</head>
<body>
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
            <div class="container-fluid">
                <a class="navbar-brand" href="/">StaticMD</a>
            </div>
        </nav>
        
        <main class="content">
            <h1 class="mb-4">$title</h1>
            $body
        </main>
        
        <footer class="border-top pt-3 text-muted">
            <p>&copy; 2024 StaticMD - Powered by PHP & Bootstrap</p>
        </footer>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
HTML;
    }
}