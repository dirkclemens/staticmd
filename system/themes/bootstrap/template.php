<?php
/**
 * Bootstrap Theme - Haupt-Template
 * Verwendet Bootstrap 5 für modernes, responsives Design
 */

// Theme-Konfiguration
$siteName = $config['system']['name'] ?? 'StaticMD';
$siteUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
$currentRoute = $_GET['route'] ?? 'index';

// Einstellungen laden
$settingsFile = $config['paths']['system'] . '/settings.json';
$settings = [];
if (file_exists($settingsFile)) {
    $settings = json_decode(file_get_contents($settingsFile), true) ?: [];
}
$siteName = $settings['site_name'] ?? $siteName;
$siteLogo = $settings['site_logo'] ?? '';



// Navigation aus Content-Verzeichnis generieren
$contentLoader = new \StaticMD\Core\ContentLoader($config);

// Theme Helper für gemeinsame Funktionen
require_once __DIR__ . '/../ThemeHelper.php';
$themeHelper = new \StaticMD\Themes\ThemeHelper($contentLoader);

// Navigation erstellen
$navItems = $themeHelper->buildNavigation();



// Titel aus Route generieren
function generateTitle($route) {
    if ($route === 'index') {
        return 'StaticMD';
    }
    
    // Route zu lesbarem Titel konvertieren
    $title = str_replace(['/', '-', '_'], ' ', $route);
    return ucwords($title);
}

// Navigation sortieren - aus Einstellungen laden
$navigationOrder = $this->contentLoader->getNavigationOrder();

// Sortierung anwenden
uksort($navItems, function($a, $b) use ($navigationOrder) {
    $orderA = $navigationOrder[$a] ?? 999;
    $orderB = $navigationOrder[$b] ?? 999;
    
    if ($orderA === $orderB) {
        // Bei gleicher Gewichtung alphabetisch sortieren
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
    
    <!-- Custom Theme CSS -->
    <style>
        :root {
            --bs-primary: #0d6efd;
            --bs-secondary: #6c757d;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
        }
        
        .navbar-brand {
            font-weight: 600;
            font-size: 1.5rem;
        }
        
        .content-wrapper {
            min-height: calc(100vh - 200px);
            padding: 2rem 0;
        }
        
        .content h1 {
            color: var(--bs-primary);
            border-bottom: 3px solid var(--bs-primary);
            padding-bottom: 0.5rem;
            margin-bottom: 2rem;
        }
        
        .content h2 {
            color: var(--bs-secondary);
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        
        .content blockquote {
            border-left: 4px solid var(--bs-primary);
            margin: 1.5rem 0;
            padding-left: 1.5rem;
            color: var(--bs-secondary);
            font-style: italic;
        }
        
        .content pre {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 1rem;
            overflow-x: auto;
        }
        
        .content code:not(pre code) {
            background-color: #f8f9fa;
            color: #d63384;
            padding: 0.125rem 0.375rem;
            border-radius: 0.25rem;
            font-size: 0.875em;
        }
        
        .content table {
            margin: 1.5rem 0;
        }
        
        .sidebar {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1.5rem;
        }
        
        .sidebar h5 {
            color: var(--bs-primary);
            border-bottom: 2px solid var(--bs-primary);
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .meta-info {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 2rem;
            font-size: 0.9rem;
        }
        
        /* Ordner-Übersicht Styles */
        .folder-overview .overview-header {
            text-align: center;
            border-bottom: 2px solid var(--bs-primary);
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        
        .folder-overview .overview-header h1 {
            color: var(--bs-primary);
        }
        
        .folder-item {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .folder-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .folder-item .card-title a {
            color: var(--bs-primary);
        }
        
        .folder-item .card-title a:hover {
            color: var(--bs-dark);
        }
        
        .folder-meta {
            border-top: 1px solid #dee2e6;
            padding-top: 0.75rem;
            margin-top: 0.75rem;
        }
        
        .folder-meta .badge {
            font-size: 0.75em;
        }
        
        .overview-navigation {
            text-align: center;
            border-top: 1px solid #dee2e6;
            padding-top: 1rem;
        }
        
        .admin-toolbar {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        /* Suchergebnisse Styles */
        .search-results .search-result {
            transition: box-shadow 0.2s ease;
        }
        
        .search-results .search-result:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .search-results .search-result h3 a {
            color: var(--bs-primary);
        }
        
        .search-results .search-result h3 a:hover {
            color: var(--bs-dark);
        }
        
        .search-results .search-url {
            font-family: monospace;
        }
        
        .search-results .search-preview mark {
            background-color: #fff3cd;
            padding: 0.125rem 0.25rem;
            border-radius: 0.25rem;
        }
        
        .embedded-page-list {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1rem;
            margin: 1.5rem 0;
        }
        
        .tag-cloud .badge {
            margin: 0.125rem;
            font-size: 0.8rem;
        }
        
        /* Tag-Seiten Styles */
        .tag-page .tag-result {
            transition: box-shadow 0.2s ease;
        }
        
        .tag-page .tag-result:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .tag-page .tag-result h3 a {
            color: var(--bs-primary);
        }
        
        .tag-page .tag-result h3 a:hover {
            color: var(--bs-dark);
        }
        
        .tag-page .tag-url {
            font-family: monospace;
        }

        footer {
            background-color: var(--bs-secondary);
            color: white;
            margin-top: 3rem;
        }
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
            <?php if ($currentRoute !== 'index' && $currentRoute !== ''): ?>
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/"><i class="bi bi-house"></i> Startseite</a>
                    </li>
                    <?php 
                    $routeParts = explode('/', trim($currentRoute, '/'));
                    $currentPath = '';
                    foreach ($routeParts as $i => $part):
                        $currentPath .= ($currentPath ? '/' : '') . $part;
                        $isLast = ($i === count($routeParts) - 1);
                    ?>
                    <li class="breadcrumb-item <?= $isLast ? 'active' : '' ?>">
                        <?php if ($isLast): ?>
                            <?= htmlspecialchars(ucwords(str_replace(['-', '_'], ' ', $part))) ?>
                        <?php else: ?>
                            <a href="/<?= $currentPath ?>"><?= htmlspecialchars(ucwords(str_replace(['-', '_'], ' ', $part))) ?></a>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ol>
            </nav>
            <?php endif; ?>
            
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
                            Powered by <strong>StaticMD</strong> - Ein PHP Markdown CMS mit Bootstrap
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
    
    <!-- Custom JS -->
    <script>
        // Smooth scrolling für Anker-Links
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
        
        // Code-Syntax-Highlighting (einfach)
        document.querySelectorAll('pre code').forEach(block => {
            block.classList.add('language-' + (block.className.match(/language-(\w+)/) || ['', 'text'])[1]);
        });
    </script>
    
    <?php if (isset($meta['js'])): ?>
    <script><?= $meta['js'] ?></script>
    <?php endif; ?>
</body>
</html>