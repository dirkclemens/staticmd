<?php
/**
 * GitHub Light Theme - Haupt-Template
 * Verwendet Bootstrap 5 mit GitHub Light Farbschema
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

// Helper-Funktion für pfad-sichere URL-Encoding
function encodeUrlPath($path) {
    $parts = explode('/', $path);
    $encodedParts = array_map('rawurlencode', $parts);
    return implode('/', $encodedParts);
}

// Navigation aus Content-Verzeichnis generieren
$contentLoader = new \StaticMD\Core\ContentLoader($config);
$pages = $contentLoader->listAll();

// Hauptnavigation erstellen
$navItems = [];
foreach ($pages as $page) {
    $parts = explode('/', $page['route']);
    $section = $parts[0];
    
    if (!isset($navItems[$section])) {
        $navItems[$section] = [
            'title' => ucwords(str_replace(['-', '_'], ' ', $section)),
            'route' => $section,
            'pages' => []
        ];
    }
    
    if (count($parts) > 1) {
        $navItems[$section]['pages'][] = $page;
    }
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
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($meta['title']) ? htmlspecialchars($meta['title']) . ' - ' : '' ?><?= htmlspecialchars($siteName) ?></title>
    
    <?php if (isset($meta['description'])): ?>
    <meta name="description" content="<?= htmlspecialchars($meta['description']) ?>">
    <?php endif; ?>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- GitHub Light Theme CSS -->
    <style>
        :root {
            /* GitHub Light Colors */
            --gh-canvas-default: #ffffff;
            --gh-canvas-subtle: #f6f8fa;
            --gh-border-default: #d0d7de;
            --gh-border-muted: #d8dee4;
            --gh-fg-default: #1f2328;
            --gh-fg-muted: #656d76;
            --gh-fg-subtle: #6e7781;
            --gh-accent-fg: #0969da;
            --gh-accent-emphasis: #0550ae;
            --gh-success-fg: #1a7f37;
            --gh-attention-fg: #9a6700;
            --gh-danger-fg: #d1242f;
            --gh-done-fg: #8250df;
            --gh-btn-primary-bg: #1f883d;
            --gh-btn-primary-hover-bg: #1a7f37;
        }
        
        body {
            background-color: var(--gh-canvas-default);
            color: var(--gh-fg-default);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Noto Sans', Helvetica, Arial, sans-serif;
        }
        
        .navbar {
            background-color: var(--gh-canvas-subtle) !important;
            border-bottom: 1px solid var(--gh-border-default);
        }
        
        .navbar-brand, .navbar-nav .nav-link {
            color: var(--gh-fg-default) !important;
        }
        
        .navbar-nav .nav-link:hover {
            color: var(--gh-accent-fg) !important;
        }
        
        .btn-primary {
            background-color: var(--gh-btn-primary-bg);
            border-color: var(--gh-btn-primary-bg);
            color: #ffffff;
        }
        
        .btn-primary:hover {
            background-color: var(--gh-btn-primary-hover-bg);
            border-color: var(--gh-btn-primary-hover-bg);
        }
        
        .btn-warning {
            background-color: var(--gh-attention-fg);
            border-color: var(--gh-attention-fg);
            color: #ffffff;
        }
        
        .btn-success {
            background-color: var(--gh-success-fg);
            border-color: var(--gh-success-fg);
        }
        
        .btn-danger {
            background-color: var(--gh-danger-fg);
            border-color: var(--gh-danger-fg);
        }
        
        .btn-outline-primary {
            color: var(--gh-accent-fg);
            border-color: var(--gh-border-default);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--gh-accent-fg);
            border-color: var(--gh-accent-fg);
            color: #ffffff;
        }
        
        .card {
            background-color: var(--gh-canvas-default);
            border: 1px solid var(--gh-border-default);
            border-radius: 6px;
        }
        
        .card-header {
            background-color: var(--gh-canvas-subtle);
            color: var(--gh-fg-default);
            border-bottom: 1px solid var(--gh-border-default);
            font-weight: 600;
        }
        
        .breadcrumb {
            background-color: var(--gh-canvas-subtle);
            border: 1px solid var(--gh-border-default);
            border-radius: 6px;
        }
        
        .breadcrumb-item a {
            color: var(--gh-accent-fg);
        }
        
        .breadcrumb-item.active {
            color: var(--gh-fg-muted);
        }
        
        .badge {
            background-color: var(--gh-done-fg) !important;
            border-radius: 12px;
        }
        
        .badge.bg-primary {
            background-color: var(--gh-accent-fg) !important;
        }
        
        .badge.bg-success {
            background-color: var(--gh-success-fg) !important;
        }
        
        .badge.bg-warning {
            background-color: var(--gh-attention-fg) !important;
        }
        
        .alert-info {
            background-color: rgba(9, 105, 218, 0.1);
            border-color: var(--gh-accent-fg);
            color: var(--gh-accent-emphasis);
        }
        
        .alert-success {
            background-color: rgba(26, 127, 55, 0.1);
            border-color: var(--gh-success-fg);
            color: var(--gh-success-fg);
        }
        
        .dropdown-menu {
            background-color: var(--gh-canvas-default);
            border: 1px solid var(--gh-border-default);
            border-radius: 6px;
            box-shadow: 0 8px 24px rgba(149, 157, 165, 0.2);
        }
        
        .dropdown-item {
            color: var(--gh-fg-default);
        }
        
        .dropdown-item:hover {
            background-color: var(--gh-canvas-subtle);
            color: var(--gh-fg-default);
        }
        
        .form-control {
            background-color: var(--gh-canvas-default);
            border: 1px solid var(--gh-border-default);
            color: var(--gh-fg-default);
            border-radius: 6px;
        }
        
        .form-control:focus {
            background-color: var(--gh-canvas-default);
            border-color: var(--gh-accent-fg);
            color: var(--gh-fg-default);
            box-shadow: 0 0 0 3px rgba(9, 105, 218, 0.12);
        }
        
        pre {
            background-color: var(--gh-canvas-subtle);
            border: 1px solid var(--gh-border-default);
            border-radius: 6px;
            color: var(--gh-fg-default);
        }
        
        code {
            background-color: rgba(175, 184, 193, 0.2);
            color: var(--gh-fg-default);
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 85%;
        }
        
        blockquote {
            border-left: 4px solid var(--gh-border-default);
            background-color: var(--gh-canvas-subtle);
            color: var(--gh-fg-muted);
            padding: 16px;
            margin: 16px 0;
            border-radius: 0 6px 6px 0;
        }
        
        .admin-toolbar {
            position: fixed;
            top: 50%;
            right: 20px;
            transform: translateY(-50%);
            z-index: 1000;
        }
        
        footer {
            background-color: var(--gh-canvas-subtle);
            color: var(--gh-fg-muted);
            border-top: 1px solid var(--gh-border-default);
        }
        
        a {
            color: var(--gh-accent-fg);
        }
        
        a:hover {
            color: var(--gh-accent-emphasis);
        }
        
        h1, h2, h3, h4, h5, h6 {
            color: var(--gh-fg-default);
            font-weight: 600;
            line-height: 1.25;
        }
        
        .text-muted {
            color: var(--gh-fg-muted) !important;
        }
        
        table {
            border-collapse: collapse;
        }
        
        table th, table td {
            border: 1px solid var(--gh-border-default);
            padding: 6px 13px;
        }
        
        table th {
            background-color: var(--gh-canvas-subtle);
            font-weight: 600;
        }
    </style>
    
    <?php if (isset($meta['css'])): ?>
    <style><?= $meta['css'] ?></style>
    <?php endif; ?>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <?php if (!empty($siteLogo)): ?>
                <a class="navbar-brand d-flex align-items-center" href="/">
                    <img src="<?= htmlspecialchars($siteLogo) ?>" alt="<?= htmlspecialchars($siteName) ?>" height="32" class="me-2">
                    <?= htmlspecialchars($siteName) ?>
                </a>
            <?php else: ?>
                <a class="navbar-brand" href="/">
                    <i class="bi bi-file-earmark-text me-2"></i><?= htmlspecialchars($siteName) ?>
                </a>
            <?php endif; ?>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= $currentRoute === 'index' ? 'active' : '' ?>" href="/">Home</a>
                    </li>
                    
                    <?php foreach ($navItems as $section => $nav): ?>
                        <?php if ($section !== 'index' && $section !== 'home'): ?>
                            <?php if (!empty($nav['pages']) && count($nav['pages']) > 0): ?>
                            <!-- Dropdown für Ordner mit Unterseiten -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle <?= strpos($currentRoute, $section) === 0 ? 'active' : '' ?>" 
                                   href="#" 
                                   id="navbarDropdown<?= $section ?>" 
                                   role="button" 
                                   data-bs-toggle="dropdown">
                                    <?= htmlspecialchars($nav['title']) ?>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="/<?= encodeUrlPath($nav['route']) ?>">Übersicht</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <?php foreach ($nav['pages'] as $page): ?>
                                    <li><a class="dropdown-item" href="/<?= encodeUrlPath($page['route']) ?>"><?= htmlspecialchars(basename($page['route'])) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                            <?php else: ?>
                            <!-- Normaler Link für einzelne Dateien -->
                            <li class="nav-item">
                                <a class="nav-link <?= $currentRoute === $section ? 'active' : '' ?>" 
                                   href="/<?= encodeUrlPath($nav['route']) ?>">
                                    <?= htmlspecialchars($nav['title']) ?>
                                </a>
                            </li>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
                
                <!-- Suchfeld -->
                <form class="d-flex" action="/search" method="get">
                    <div class="input-group">
                        <input class="form-control" type="search" name="q" placeholder="Suchen..." 
                               value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
                        <button class="btn btn-outline-primary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </nav>

    <!-- Success Message -->
    <?php if (isset($_GET['success'])): ?>
    <div class="container mt-3">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <?php 
            switch ($_GET['success']) {
                case 'saved':
                    echo 'Datei wurde erfolgreich gespeichert!';
                    break;
                case 'deleted':
                    echo 'Datei wurde erfolgreich gelöscht!';
                    break;
                default:
                    echo 'Aktion erfolgreich ausgeführt!';
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Breadcrumb -->
    <?php if ($currentRoute !== 'index'): ?>
    <div class="container mt-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <?php 
                $parts = explode('/', $currentRoute);
                $path = '';
                for ($i = 0; $i < count($parts); $i++): 
                    $path .= ($path ? '/' : '') . $parts[$i];
                    $isLast = ($i === count($parts) - 1);
                ?>
                    <?php if ($isLast): ?>
                        <li class="breadcrumb-item active"><?= ucwords(str_replace(['-', '_'], ' ', $parts[$i])) ?></li>
                    <?php else: ?>
                        <li class="breadcrumb-item"><a href="/<?= encodeUrlPath($path) ?>"><?= ucwords(str_replace(['-', '_'], ' ', $parts[$i])) ?></a></li>
                    <?php endif; ?>
                <?php endfor; ?>
            </ol>
        </nav>
    </div>
    <?php endif; ?>

    <!-- Hauptinhalt -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-lg-8">
                <div class="content">
                    <?= $body ?>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="sidebar-content">
                    <!-- Content Info -->
                    <?php if (isset($meta['author']) || isset($meta['date'])): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="bi bi-info-circle me-2"></i>Informationen
                        </div>
                        <div class="card-body">
                            <?php if (isset($meta['author'])): ?>
                            <p class="mb-1"><strong>Autor:</strong> <?= htmlspecialchars($meta['author']) ?></p>
                            <?php endif; ?>
                            <?php if (isset($meta['date'])): ?>
                            <p class="mb-0"><strong>Datum:</strong> <?= htmlspecialchars($meta['date']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Zusätzliche Sidebar-Inhalte -->
                    <?php if (isset($meta['tags'])): ?>
                    <div class="sidebar mt-4">
                        <h5><i class="bi bi-tags me-2"></i>Tags</h5>
                        <div class="tag-cloud">
                            <?php foreach (explode(',', $meta['tags']) as $tag): ?>
                                <?php $cleanTag = trim($tag); ?>
                                <?php if (!empty($cleanTag)): ?>
                                <a href="/tag/<?= encodeUrlPath($cleanTag) ?>" class="badge bg-primary text-white text-decoration-none me-1 mb-1">
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
                        <small class="text-muted">
                            Powered by <strong>StaticMD</strong> - Ein PHP Markdown CMS mit Bootstrap
                        </small>
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="/admin" class="text-muted text-decoration-none">
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