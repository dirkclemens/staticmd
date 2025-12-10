<?php

namespace StaticMD\Renderers;

use StaticMD\Utilities\UrlHelper;
use StaticMD\Core\I18n;

/**
 * FolderOverviewRenderer
 * Generiert HTML für Ordner-Übersichtsseiten
 */
class FolderOverviewRenderer
{
    /**
     * Generiert vollständige HTML-Übersicht für einen Ordner
     * 
     * @param string $folderTitle Titel des Ordners
     * @param array $files Array von Dateien mit [title, route, description, etc.]
     * @param array $subfolders Array von Unterordnern
     * @param string $route Aktuelle Route
     * @return string HTML-Code
     */
    public static function renderFull(string $folderTitle, array $files, array $subfolders, string $route): string
    {
        $html = '<div class="folder-overview">';
        
        // Header
        $html .= self::renderHeader($folderTitle, count($files), count($subfolders));
        
        // Unterordner anzeigen (falls vorhanden)
        if (!empty($subfolders)) {
            $html .= self::renderSubfolders($subfolders);
        }
        
        // Dateien anzeigen
        if (empty($files) && empty($subfolders)) {
            $html .= self::renderEmptyState();
        } elseif (!empty($files)) {
            $html .= self::renderFiles($files);
        }
        
        // Navigation zurück
        if ($route !== '') {
            $html .= self::renderBackNavigation($route);
        }
        
        $html .= '</div>'; // folder-overview
        
        return $html;
    }
    
    /**
     * Generiert kompakte Übersicht für Shortcode-Einbettung
     * 
     * @param array $files Array von Dateien
     * @param string $layout 'columns' oder 'rows'
     * @return string HTML-Code
     */
    public static function renderEmbedded(array $files, string $layout = 'columns'): string
    {
        $html = '<div class="embedded-page-list">';
        
        if ($layout === 'rows') {
            $columns = self::distributeItemsInRows($files, 4);
        } else {
            $columns = self::distributeItemsInColumns($files, 4);
        }
        
        $html .= '<div class="row">';
        foreach ($columns as $column) {
            $html .= '<div class="col-6 col-sm-6 col-md-3 col-lg-3 mb-3">';
            
            foreach ($column as $file) {
                $fileRoute = $file['route'];
                $title = $file['title'];
                $html .= '<div class="mb-2">';
                $html .= '<a href="/' . UrlHelper::encodePath($fileRoute) . '" class="text-decoration-none d-block">';
                $html .= '<i class="bi bi-file-earmark-text me-2 text-muted"></i>';
                $html .= htmlspecialchars($title);
                $html .= '</a>';
                $html .= '</div>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Rendert Header-Bereich
     */
    private static function renderHeader(string $folderTitle, int $fileCount, int $subfolderCount): string
    {
        $html = '<div class="overview-header mb-4">';
        $html .= '<h1><i class="bi bi-folder2-open me-2"></i>' . htmlspecialchars($folderTitle) . '</h1>';
        $html .= '<p class="lead">' . I18n::t('core.content_overview', ['pages' => $fileCount]);
        if ($subfolderCount > 0) {
            $html .= ', ' . $subfolderCount . ' ' . I18n::t('core.subfolders');
        }
        $html .= '</p>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Rendert Unterordner-Bereich
     */
    private static function renderSubfolders(array $subfolders): string
    {
        $html = '<div class="subfolders-section mb-5">';
        $html .= '<h2><i class="bi bi-folder me-2"></i>' . I18n::t('core.subfolders') . '</h2>';
        $html .= '<div class="row">';
        
        foreach ($subfolders as $subfolder) {
            $html .= '<div class="col-md-6 col-lg-4 mb-3">';
            $html .= '<div class="card h-100 border-primary border-opacity-25">';
            $html .= '<div class="card-body">';
            $html .= '<h5 class="card-title">';
            $html .= '<a href="/' . UrlHelper::encodePath($subfolder['route']) . '" class="text-decoration-none">';
            $html .= '<i class="bi bi-folder-fill me-2 text-primary"></i>';
            $html .= htmlspecialchars($subfolder['title']);
            $html .= '</a>';
            $html .= '</h5>';
            if (!empty($subfolder['description'])) {
                $html .= '<p class="card-text">' . htmlspecialchars($subfolder['description']) . '</p>';
            }
            $html .= '<small class="text-muted">';
            $html .= '<i class="bi bi-files me-1"></i>';
            $html .= $subfolder['file_count'] . ' ' . I18n::t('core.pages_lowercase');
            $html .= '</small>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Rendert Dateien-Liste
     */
    private static function renderFiles(array $files): string
    {
        $html = '<div class="files-section">';
        // $html .= '<h2><i class="bi bi-file-earmark-text me-2"></i>' . I18n::t('core.pages') . '</h2>';
        
        // Dateien spaltenweise in 3 Spalten aufteilen
        $columns = self::distributeItemsInColumns($files, 3);
        
        $html .= '<div class="row">';
        foreach ($columns as $column) {
            $html .= '<div class="col-md-4 mb-4">';
            
            foreach ($column as $file) {
                $fileRoute = $file['route'];
                $title = $file['title'];
                $html .= '<div class="mb-2">';
                $html .= '<a href="/' . UrlHelper::encodePath($fileRoute) . '" class="text-decoration-none">';
                $html .= '<i class="bi bi-file-earmark-text me-2"></i>';
                $html .= htmlspecialchars($title);
                $html .= '</a>';
                $html .= '</div>';
            }
            
            $html .= '</div>'; // col
        }
        $html .= '</div>'; // row
        $html .= '</div>'; // files-section
        
        return $html;
    }
    
    /**
     * Rendert Leer-Zustand
     */
    private static function renderEmptyState(): string
    {
        $html = '<div class="alert alert-info">';
        $html .= '<i class="bi bi-info-circle me-2"></i>';
        $html .= I18n::t('core.no_pages_yet');
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Rendert Zurück-Navigation
     */
    private static function renderBackNavigation(string $route): string
    {
        $parentRoute = dirname($route);
        $parentRoute = ($parentRoute === '.' || $parentRoute === '/') ? '' : $parentRoute;
        
        $html = '<div class="overview-navigation mt-4">';
        $html .= '<a href="/' . UrlHelper::encodePath($parentRoute) . '" class="btn btn-outline-primary">';
        $html .= '<i class="bi bi-arrow-left me-1"></i>' . I18n::t('core.back_link');
        $html .= '</a>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Verteilt Items spaltenweise auf Spalten
     */
    private static function distributeItemsInColumns(array $items, int $columnCount): array
    {
        $columns = array_fill(0, $columnCount, []);
        $itemsPerColumn = ceil(count($items) / $columnCount);
        
        for ($i = 0; $i < count($items); $i++) {
            $columnIndex = intval($i / $itemsPerColumn);
            $columnIndex = min($columnIndex, $columnCount - 1);
            $columns[$columnIndex][] = $items[$i];
        }
        
        return $columns;
    }
    
    /**
     * Verteilt Items zeilenweise auf Spalten
     */
    private static function distributeItemsInRows(array $items, int $columnCount): array
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
     * Rendert horizontale Navigation für Unterordner (für [folder] Shortcode)
     */
    public static function renderFolderNavigation(array $subfolders): string
    {
        $html = '<nav class="folder-navigation mb-3">';
        $html .= '<div class="d-flex flex-wrap gap-2">';
        
        foreach ($subfolders as $folder) {
            $url = '/' . $folder['path'];
            $html .= '<a href="' . htmlspecialchars($url) . '" class="btn btn-outline-primary btn-sm">';
            $html .= '<i class="bi bi-folder"></i> ' . htmlspecialchars($folder['title']);
            $html .= '</a>';
        }
        
        $html .= '</div>';
        $html .= '</nav>';
        
        return $html;
    }
}
