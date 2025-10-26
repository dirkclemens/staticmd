<?php

namespace StaticMD\Core;

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
        $templatePath = $this->getTemplatePath();
        
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
        return $pages;
    }

    /**
     * Ermittelt den Template-Pfad
     */
    private function getTemplatePath(): string
    {
        // Frontend-Theme aus Settings laden
        $settingsFile = $this->config['paths']['system'] . '/settings.json';
        $settings = [];
        if (file_exists($settingsFile)) {
            $settings = json_decode(file_get_contents($settingsFile), true) ?: [];
        }
        
        // Theme aus Settings oder Fallback
        $themeName = $settings['frontend_theme'] ?? $this->config['theme']['default'];
        $extension = $this->config['theme']['template_extension'];
        
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