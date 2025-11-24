<?php

namespace StaticMD\Renderers;

use StaticMD\Utilities\UrlHelper;

/**
 * BlogListRenderer
 * Generiert HTML für Blog-Listen mit Pagination
 */
class BlogListRenderer
{
    /**
     * Generiert vollständige Blog-Liste mit Pagination
     * 
     * @param array $blogData Blog-Daten mit items, total, pages, current_page, per_page
     * @param string $route Basis-Route für Pagination
     * @param int $currentPage Aktuelle Seite
     * @param int $perPage Einträge pro Seite
     * @return string HTML-Code
     */
    public static function render(array $blogData, string $route, int $currentPage, int $perPage): string
    {
        $html = '<div class="blog-list">';
        
        // Blog-Einträge
        foreach ($blogData['items'] as $item) {
            $html .= self::renderBlogEntry($item);
        }
        
        // Pagination
        if ($blogData['pages'] > 1) {
            $html .= self::renderPagination($blogData, $route);
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Rendert einen einzelnen Blog-Eintrag
     */
    private static function renderBlogEntry(array $item): string
    {
        $html = '<article class="blog-entry card mb-4">';
        $html .= '<div class="card-body">';
        
        // Titel
        $html .= '<h3 class="card-title">';
        $html .= '<a href="/' . UrlHelper::encodePath($item['route']) . '" class="text-decoration-none">';
        $html .= htmlspecialchars($item['title']);
        $html .= '</a>';
        $html .= '</h3>';
        
        // Meta-Informationen
        $html .= '<div class="blog-meta mb-3">';
        if (!empty($item['date'])) {
            $html .= '<span class="text-muted me-3">';
            $html .= '<i class="bi bi-calendar me-1"></i>';
            $html .= htmlspecialchars($item['date']);
            $html .= '</span>';
        }
        if (!empty($item['author'])) {
            $html .= '<span class="text-muted me-3">';
            $html .= '<i class="bi bi-person me-1"></i>';
            $html .= htmlspecialchars($item['author']);
            $html .= '</span>';
        }
        $html .= '</div>';
        
        // Beschreibung
        if (!empty($item['description'])) {
            $html .= '<p class="card-text">' . htmlspecialchars($item['description']) . '</p>';
        }
        
        // Tags
        if (!empty($item['tags'])) {
            $html .= '<div class="blog-tags mb-2">';
            $tags = array_map('trim', explode(',', $item['tags']));
            foreach ($tags as $tag) {
                if (!empty($tag)) {
                    $html .= '<a href="/tag/' . urlencode($tag) . '" class="badge bg-secondary me-1 text-decoration-none">';
                    $html .= htmlspecialchars($tag);
                    $html .= '</a>';
                }
            }
            $html .= '</div>';
        }
        
        // Weiterlesen-Link
        $html .= '<a href="/' . UrlHelper::encodePath($item['route']) . '" class="btn btn-primary btn-sm">';
        $html .= 'Weiterlesen <i class="bi bi-arrow-right"></i>';
        $html .= '</a>';
        
        $html .= '</div>';
        $html .= '</article>';
        
        return $html;
    }
    
    /**
     * Generiert Pagination-HTML
     */
    private static function renderPagination(array $blogData, string $route): string
    {
        $currentPage = $blogData['current_page'];
        $totalPages = $blogData['pages'];
        $perPage = $blogData['per_page'];
        
        $html = '<nav aria-label="Blog pagination" class="mt-4">';
        $html .= '<ul class="pagination justify-content-center">';
        
        // Vorherige Seite
        if ($currentPage > 1) {
            $prevUrl = self::getPaginationUrl($route, $currentPage - 1, $perPage);
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . htmlspecialchars($prevUrl) . '">';
            $html .= '<i class="bi bi-chevron-left"></i> Vorherige';
            $html .= '</a>';
            $html .= '</li>';
        } else {
            $html .= '<li class="page-item disabled">';
            $html .= '<span class="page-link"><i class="bi bi-chevron-left"></i> Vorherige</span>';
            $html .= '</li>';
        }
        
        // Seitennummern
        $startPage = max(1, $currentPage - 2);
        $endPage = min($totalPages, $currentPage + 2);
        
        if ($startPage > 1) {
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . htmlspecialchars(self::getPaginationUrl($route, 1, $perPage)) . '">1</a>';
            $html .= '</li>';
            if ($startPage > 2) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }
        
        for ($i = $startPage; $i <= $endPage; $i++) {
            if ($i == $currentPage) {
                $html .= '<li class="page-item active">';
                $html .= '<span class="page-link">' . $i . '</span>';
                $html .= '</li>';
            } else {
                $html .= '<li class="page-item">';
                $html .= '<a class="page-link" href="' . htmlspecialchars(self::getPaginationUrl($route, $i, $perPage)) . '">' . $i . '</a>';
                $html .= '</li>';
            }
        }
        
        if ($endPage < $totalPages) {
            if ($endPage < $totalPages - 1) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . htmlspecialchars(self::getPaginationUrl($route, $totalPages, $perPage)) . '">' . $totalPages . '</a>';
            $html .= '</li>';
        }
        
        // Nächste Seite
        if ($currentPage < $totalPages) {
            $nextUrl = self::getPaginationUrl($route, $currentPage + 1, $perPage);
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . htmlspecialchars($nextUrl) . '">';
            $html .= 'Nächste <i class="bi bi-chevron-right"></i>';
            $html .= '</a>';
            $html .= '</li>';
        } else {
            $html .= '<li class="page-item disabled">';
            $html .= '<span class="page-link">Nächste <i class="bi bi-chevron-right"></i></span>';
            $html .= '</li>';
        }
        
        $html .= '</ul>';
        $html .= '</nav>';
        
        return $html;
    }
    
    /**
     * Generiert Pagination-URL
     */
    private static function getPaginationUrl(string $route, int $page, int $perPage): string
    {
        $baseUrl = '/' . ($route ? UrlHelper::encodePath($route) : '');
        $params = [];
        
        if ($page > 1) {
            $params['page'] = $page;
        }
        
        // Weitere URL-Parameter beibehalten (außer page)
        foreach ($_GET as $key => $value) {
            if ($key !== 'page' && $key !== 'route') {
                $params[$key] = $value;
            }
        }
        
        return $baseUrl . (!empty($params) ? '?' . http_build_query($params) : '');
    }
    
    /**
     * Rendert Tag-Liste für Blog-Einträge
     */
    public static function renderTagsList(array $tagCounts, string $route = ''): string
    {
        $html = '<div class="tag-cloud mb-3">';
        
        foreach ($tagCounts as $tag => $count) {
            // Tag size based on frequency
            $size = min(3, max(1, (int)floor($count / 2) + 1));
            $badgeClass = $size === 1 ? 'bg-secondary' : ($size === 2 ? 'bg-primary' : 'bg-success');
            
            $html .= '<a href="/tag/' . urlencode($tag) . '" class="badge ' . $badgeClass . ' me-2 mb-2 text-decoration-none">';
            $html .= htmlspecialchars($tag);
            $html .= ' <span class="badge bg-light text-dark ms-1">' . $count . '</span>';
            $html .= '</a>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
}
