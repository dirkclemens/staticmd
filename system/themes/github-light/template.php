<?php
/**
 * GitHub Light Theme - Haupt-Template
 * Verwendet Bootstrap 5 mit GitHub Light Farbschema
 */

// Theme configuration
// $themeName wird von TemplateEngine übergeben
$siteName = $config['system']['name'] ?? 'StaticMD';
$siteUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
$currentRoute = $_GET['route'] ?? 'index';

// Load settings
$settingsFile = $config['paths']['system'] . '/settings.json';
$settings = [];
if (file_exists($settingsFile)) {
    $settings = json_decode(file_get_contents($settingsFile), true) ?: [];
}
$siteName = $settings['site_name'] ?? $siteName;
$siteLogo = $settings['site_logo'] ?? '';

// Generate navigation from content directory
$contentLoader = new \StaticMD\Core\ContentLoader($config);

// Theme helper for shared functions
require_once __DIR__ . '/../ThemeHelper.php';
$themeHelper = new \StaticMD\Themes\ThemeHelper($contentLoader);

// Create navigation
$navItems = $themeHelper->buildNavigation();

// Generate title from route
function generateTitle($route) {
    if ($route === 'index') {
        return 'StaticMD';
    }
    
    // Convert route to readable title
    $title = str_replace(['/', '-', '_'], ' ', $route);
    return ucwords($title);
}

// Sort navigation - load from settings
$navigationOrder = $this->contentLoader->getNavigationOrder();

// Apply sorting
uksort($navItems, function($a, $b) use ($navigationOrder) {
    $orderA = $navigationOrder[$a] ?? 999;
    $orderB = $navigationOrder[$b] ?? 999;
    
    if ($orderA === $orderB) {
    // If same weight, sort alphabetically
        return strcasecmp($a, $b);
    }
    
    return $orderA <=> $orderB;
});
?>
<!DOCTYPE html>
<html lang="de" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($meta['description'] ?? 'Powered by StaticMD') ?>">
    <meta name="author" content="<?= htmlspecialchars($meta['author'] ?? '') ?>">
    
    <title><?= htmlspecialchars($title) ?> - <?= htmlspecialchars($siteName) ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- KaTeX CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
    
    <!-- Favicon -->  
    <link rel="icon" type="image/x-icon" href="/assets/favicon.ico">

    <!-- Custom Theme CSS -->
    <style>
        <?php include __DIR__ . '/template.css'; ?>
    </style>

    <?php if (isset($meta['css'])): ?>
    <style><?= $meta['css'] ?></style>
    <?php endif; ?>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="/">
                <?php if (!empty($siteLogo)): ?>
                    <img src="<?= htmlspecialchars($siteLogo) ?>" alt="Logo" style="height: 30px;" class="me-2">
                <?php else: ?>
                    <i class="bi bi-file-earmark-text me-2"></i>
                <?php endif; ?>
                <?= htmlspecialchars($siteName) ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= $currentRoute === 'index' ? 'active' : '' ?>" href="/">
                            <i class="bi bi-house me-1"></i> Startseite
                        </a>
                    </li>
                    
                    <?php foreach ($navItems as $section => $nav): ?>
                        <?php if ($section !== 'index' && $section !== 'home'): ?>
                            <?php if (!empty($nav['pages']) && count($nav['pages']) > 0): ?>
                            <!-- Dropdown für Ordner mit Unterseiten -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle <?= strpos($currentRoute, $section) === 0 ? 'active' : '' ?>" 
                                   href="#" role="button" data-bs-toggle="dropdown">
                                    <?= htmlspecialchars($nav['title']) ?>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="/<?= \StaticMD\Themes\ThemeHelper::encodeUrlPath($nav['route']) ?>">Übersicht</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <?php foreach ($nav['pages'] as $page): ?>
                                    <li><a class="dropdown-item" href="/<?= \StaticMD\Themes\ThemeHelper::encodeUrlPath($page['route']) ?>">
                                        <?= htmlspecialchars($page['title'] ?? basename($page['route'])) ?>
                                    </a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                            <?php else: ?>
                            <!-- Normaler Link für einzelne Dateien -->
                            <li class="nav-item">
                                <a class="nav-link <?= $currentRoute === $section ? 'active' : '' ?>" 
                                   href="/<?= \StaticMD\Themes\ThemeHelper::encodeUrlPath($nav['route']) ?>">
                                    <?= htmlspecialchars($nav['title']) ?>
                                </a>
                            </li>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
                
                <!-- Suchformular -->
                <form class="d-flex me-3" action="/search" method="GET">
                    <input class="form-control me-2" type="search" name="q" placeholder="Suchen..." 
                           value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" style="width: 250px;">
                    <button class="btn btn-outline-primary" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
                
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="/admin">
                            <i class="bi bi-gear me-1"></i> Admin
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hauptinhalt -->
    <div class="content-wrapper">
        <div class="container">
            <!-- Breadcrumb Navigation -->
            <?= $themeHelper->renderBreadcrumbs($breadcrumbs ?? []) ?>
            
            <div class="row">
                <div class="col-lg-8">
                    <!-- Meta-Informationen -->
                    <?php if (!empty($meta) && ($meta['author'] ?? $meta['date'] ?? null)): ?>
                    <div class="meta-info">
                        <div class="row align-items-center">
                            <div class="col">
                                <?php if (isset($meta['author'])): ?>
                                <i class="bi bi-person me-1"></i>
                                <span class="me-3"><?= htmlspecialchars($meta['author']) ?></span>
                                <?php endif; ?>
                                
                                <?php if (isset($meta['date'])): ?>
                                <i class="bi bi-calendar me-1"></i>
                                <span><?= htmlspecialchars($meta['date']) ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (isset($content['modified'])): ?>
                            <div class="col-auto text-muted">
                                <small>
                                    <i class="bi bi-clock me-1"></i>
                                    Aktualisiert: <?= date('d.m.Y H:i', $content['modified']) ?>
                                </small>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Erfolgsmeldung nach Speichern -->
                    <?php if (isset($_GET['saved'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        Die Seite wurde erfolgreich gespeichert.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Private Seiten-Hinweis für Admins -->
                    <?php 
                    $visibility = $meta['Visibility'] ?? $meta['visibility'] ?? 'public';
                    if ($visibility === 'private'): 
                    ?>
                    <div class="alert alert-warning" role="alert">
                        <i class="bi bi-lock me-2"></i>
                        <strong>Private Seite:</strong> Diese Seite ist nur für angemeldete Admins sichtbar.
                    </div>
                    <?php endif; ?>
                    
                    <!-- Hauptcontent -->
                    <article class="content">
                        <?= $body ?>
                    </article>
                </div>
                
                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="sidebar">
                        <h5><i class="bi bi-list-ul me-2"></i>Navigation</h5>
                        
                        <?php if (!empty($navItems)): ?>
                        <div class="list-group list-group-flush">
                            <a href="/" class="list-group-item list-group-item-action <?= $currentRoute === 'index' ? 'active' : '' ?>">
                                <i class="bi bi-house me-2"></i> Startseite
                            </a>
                            
                            <?php foreach ($navItems as $section => $nav): ?>
                                <?php if ($section !== 'index'): ?>
                                <a href="/<?= \StaticMD\Themes\ThemeHelper::encodeUrlPath($nav['route']) ?>" class="list-group-item list-group-item-action <?= strpos($currentRoute, $section) === 0 ? 'active' : '' ?>">
                                    <i class="bi bi-folder me-2"></i> <?= htmlspecialchars($nav['title']) ?>
                                    <?php if (!empty($nav['pages'])): ?>
                                    <span class="badge bg-secondary float-end"><?= count($nav['pages']) ?></span>
                                    <?php endif; ?>
                                </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Zusätzliche Sidebar-Inhalte -->
                    <?php if (isset($meta['tags'])): ?>
                    <div class="sidebar mt-4">
                        <h5><i class="bi bi-tags me-2"></i>Tags</h5>
                        <div class="tag-cloud">
                            <?php foreach (explode(',', $meta['tags']) as $tag): ?>
                                <?php $cleanTag = trim($tag); ?>
                                <?php if (!empty($cleanTag)): ?>
                                <a href="/tag/<?= \StaticMD\Themes\ThemeHelper::encodeUrlPath($cleanTag) ?>" class="badge bg-primary text-white text-decoration-none me-1 mb-1">
                                    <?= htmlspecialchars($cleanTag) ?>
                                </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-2">
                            <a href="/tag" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-tags me-1"></i>Alle Tags anzeigen
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin-Toolbar (nur wenn eingeloggt) -->
    <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']): ?>
    <div class="admin-toolbar">
        <div class="btn-group-vertical" role="group">
            <a href="/admin" class="btn btn-primary btn-sm" title="Admin Dashboard">
                <i class="bi bi-gear"></i>
            </a>
            <?php if (isset($content['file_path'])): ?>
            <a href="/admin?action=edit&file=<?= urlencode($content['route']) ?>&return_url=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-warning btn-sm" title="Seite bearbeiten">
                <i class="bi bi-pencil"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <p class="mb-1">&copy; <?= date('Y') ?> <?= htmlspecialchars($siteName) ?></p>
                    <p class="mb-0">
                        <small class="text-light">                        
                        </small>
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="/admin" class="text-light text-decoration-none">
                        <i class="bi bi-shield-lock me-1"></i> Admin-Bereich
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- KaTeX JS -->
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/contrib/auto-render.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Code syntax highlighting (simple)
        document.querySelectorAll('pre code').forEach(block => {
            block.classList.add('language-' + (block.className.match(/language-(\w+)/) || ['', 'text'])[1]);
        });
        
        // KaTeX Auto-Render
        if (typeof renderMathInElement !== 'undefined') {
            renderMathInElement(document.body, {
                delimiters: [
                    {left: '<span class="katex-inline">', right: '</span>', display: false},
                    {left: '<div class="katex-display">', right: '</div>', display: true}
                ]
            });
        }
    </script>
    
    <?php if (isset($meta['js'])): ?>
    <script><?= $meta['js'] ?></script>
    <?php endif; ?>
</body>
</html>