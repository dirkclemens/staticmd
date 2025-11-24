<?php

namespace StaticMD\Processors;

use StaticMD\Utilities\FrontMatterParser;
use StaticMD\Utilities\TitleGenerator;
use StaticMD\Renderers\FolderOverviewRenderer;
use StaticMD\Renderers\BlogListRenderer;
use StaticMD\Core\I18n;

/**
 * ShortcodeProcessor
 * Verarbeitet alle Shortcodes: [pages], [tags], [gallery], [bloglist], [folder]
 */
class ShortcodeProcessor
{
    private array $config;
    private $contentLoader; // ContentLoader instance for file access
    
    public function __construct(array $config, $contentLoader = null)
    {
        $this->config = $config;
        $this->contentLoader = $contentLoader;
    }
    
    /**
     * Verarbeitet alle Shortcodes im Content
     * Schützt Code-Blocks vor Verarbeitung
     */
    public function process(string $content, string $currentRoute): string
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
        
        // 2. Shortcodes verarbeiten
        $pattern = '/\[([a-zA-Z]+)(?:\s+([^\]]+))?\]/';
        
        $content = preg_replace_callback($pattern, function($matches) use ($currentRoute) {
            $shortcode = strtolower(trim($matches[1]));
            $params = isset($matches[2]) ? array_filter(array_map('trim', explode(' ', $matches[2]))) : [];
            $fullMatch = $matches[0]; // Keep original shortcode for unknown ones
            
            return $this->processShortcode($shortcode, $params, $currentRoute, $fullMatch);
        }, $content);
        
        // 3. Code-Blocks wieder einsetzen
        $content = str_replace(array_keys($codeBlocks), array_values($codeBlocks), $content);
        
        return $content;
    }
    
    /**
     * Verarbeitet einen einzelnen Shortcode
     */
    private function processShortcode(string $shortcode, array $params, string $currentRoute, string $fullMatch): string
    {
        switch ($shortcode) {
            case 'pages':
                return $this->processPages($params, $currentRoute);
            case 'tags':
                return $this->processTags($params, $currentRoute);
            case 'folder':
                return $this->processFolder($params, $currentRoute);
            case 'gallery':
                return $this->processGallery($params, $currentRoute);
            case 'bloglist':
                return $this->processBloglist($params, $currentRoute);
            default:
                // Unknown shortcode - return unchanged for later processing (e.g. by MarkdownParser)
                return $fullMatch;
        }
    }
    
    /**
     * [pages /pfad/ limit layout]
     */
    private function processPages(array $params, string $currentRoute): string
    {
        if (!$this->contentLoader) {
            return '<div class="alert alert-danger">ContentLoader not available</div>';
        }
        
        $targetPath = isset($params[0]) ? trim($params[0], ' /') : trim($currentRoute, '/');
        $limit = isset($params[1]) ? (int)$params[1] : 1000;
        $layout = isset($params[2]) ? strtolower(trim($params[2])) : 'columns';
        
        $files = $this->contentLoader->getFolderFilesPublic($targetPath, $limit);
        
        if (empty($files)) {
            return '<div class="alert alert-info">' . I18n::t('core.no_pages_found', ['path' => htmlspecialchars($targetPath ?: '/')]) . '</div>';
        }
        
        return FolderOverviewRenderer::renderEmbedded($files, $layout);
    }
    
    /**
     * [tags /pfad/ limit]
     */
    private function processTags(array $params, string $currentRoute): string
    {
        if (!$this->contentLoader) {
            return '<div class="alert alert-danger">ContentLoader not available</div>';
        }
        
        $targetPath = isset($params[0]) ? trim($params[0], ' /') : trim($currentRoute, '/');
        $limit = isset($params[1]) ? (int)$params[1] : 1000;
        
        $tags = $this->contentLoader->getFolderTagsPublic($targetPath, $limit);
        
        if (empty($tags)) {
            return '<div class="alert alert-info">' . I18n::t('core.no_tags_found', ['path' => htmlspecialchars($targetPath ?: '/')]) . '</div>';
        }
        
        return BlogListRenderer::renderTagsList($tags, $targetPath);
    }
    
    /**
     * [folder /pfad/ limit]
     */
    private function processFolder(array $params, string $currentRoute): string
    {
        if (!$this->contentLoader) {
            return '<div class="alert alert-danger">ContentLoader not available</div>';
        }
        
        $targetPath = isset($params[0]) ? trim($params[0], ' /') : trim($currentRoute, '/');
        $limit = isset($params[1]) ? (int)$params[1] : 1000;
        
        $subfolders = $this->contentLoader->getDirectSubfoldersPublic($targetPath, $limit);
        
        if (empty($subfolders)) {
            return '<div class="alert alert-info">' . I18n::t('core.no_subfolders_found', ['path' => htmlspecialchars($targetPath ?: '/')]) . '</div>';
        }
        
        return FolderOverviewRenderer::renderFolderNavigation($subfolders);
    }
    
    /**
     * [gallery /pfad/ limit]
     */
    private function processGallery(array $params, string $currentRoute): string
    {
        $targetPath = isset($params[0]) ? trim($params[0], ' ') : '';
        $limit = isset($params[1]) ? (int)$params[1] : 100;
        
        if (empty($targetPath)) {
            return '<div class="alert alert-warning">Gallery-Shortcode: Pfad-Parameter erforderlich. Beispiel: [gallery paris]</div>';
        }
        
        // Determine path
        if (strpos($targetPath, '/') === 0) {
            $imagePath = $this->config['paths']['public'] . $targetPath;
            $urlPath = $targetPath;
        } else {
            $imagePath = $this->config['paths']['public'] . '/assets/galleries/' . $targetPath;
            $urlPath = '/assets/galleries/' . $targetPath;
        }
        
        if (!is_dir($imagePath)) {
            return '<div class="alert alert-warning">' . I18n::t('core.gallery_directory_not_found', ['path' => htmlspecialchars($imagePath)]) . '</div>';
        }
        
        $images = $this->getImageFiles($imagePath, $urlPath, $limit);
        
        if (empty($images)) {
            return '<div class="alert alert-info">' . I18n::t('core.no_images_found', ['path' => htmlspecialchars($targetPath)]) . '</div>';
        }
        
        return $this->generateGalleryHTML($images, $targetPath);
    }
    
    /**
     * [bloglist /pfad/ per_page page]
     */
    private function processBloglist(array $params, string $currentRoute): string
    {
        if (!$this->contentLoader) {
            return '<div class="alert alert-danger">ContentLoader not available</div>';
        }
        
        $targetPath = isset($params[0]) ? trim($params[0], ' /') : trim($currentRoute, '/');
        $perPage = isset($params[1]) ? (int)$params[1] : $this->getItemsPerPage();
        $currentPage = isset($params[2]) ? max(1, (int)$params[2]) : $this->getCurrentPage();
        
        $blogData = $this->contentLoader->getBlogListPublic($targetPath, $perPage, $currentPage);
        
        if (empty($blogData['items'])) {
            return '<div class="alert alert-info">' . I18n::t('core.no_blog_entries_found', ['path' => htmlspecialchars($targetPath ?: '/')]) . '</div>';
        }
        
        return BlogListRenderer::render($blogData, $targetPath, $currentPage, $perPage);
    }
    
    /**
     * Hilfsmethoden für Gallery
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
        
        usort($images, function($a, $b) {
            return strcmp($a['filename'], $b['filename']);
        });
        
        return $images;
    }
    
    private function generateImageAltText(string $filename): string
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $name = preg_replace('/^\d{4}_\d{4}_\d{6}/', '', $name);
        $name = preg_replace('/^\d+_/', '', $name);
        $name = str_replace(['_', '-'], ' ', $name);
        return trim($name) ?: 'Bild';
    }
    
    private function generateImageTitle(string $filename): string
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $name = str_replace('_', ' ', $name);
        return $name;
    }
    
    private function generateGalleryHTML(array $images, string $path): string
    {
        $html = '<div class="auto-gallery-info mb-3">';
        $html .= '<small class="text-muted"><i class="bi bi-images"></i> ' . count($images) . ' Bilder aus ' . htmlspecialchars($path) . '</small>';
        $html .= '</div>';
        
        foreach ($images as $image) {
            $html .= '<img src="' . htmlspecialchars($image['url']) . '" ';
            $html .= 'alt="' . htmlspecialchars($image['alt']) . '" ';
            $html .= 'title="' . htmlspecialchars($image['title']) . '" ';
            $html .= 'loading="lazy" />' . "\n";
        }
        
        return $html;
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
}
