<?php
/**
 * Monokai Dark Theme - Haupt-Template
 * Verwendet Bootstrap 5 mit Monokai Dark Farbschema
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
        // Titel aus Front Matter laden
        if (isset($page['path']) && file_exists($page['path'])) {
            $content = file_get_contents($page['path']);
            $page['title'] = parseTitle($content, $page['route']);
        } else {
            $page['title'] = generateTitle($page['route']);
        }
        
        $navItems[$section]['pages'][] = $page;
    }
}

// Dropdown-Seiten alphabetisch sortieren (case-insensitive)
foreach ($navItems as $section => $nav) {
    if (!empty($nav['pages'])) {
        usort($navItems[$section]['pages'], function($a, $b) {
            $titleA = $a['title'] ?? basename($a['route']);
            $titleB = $b['title'] ?? basename($b['route']);
            return strcasecmp($titleA, $titleB);
        });
    }
}

// Hilfsfunktion für Titel-Parsing
function parseTitle($content, $route) {
    // Front Matter erkennen (--- am Anfang)
    if (strpos($content, '---') === 0) {
        $parts = explode('---', $content, 3);
        
        if (count($parts) >= 3) {
            $frontMatter = trim($parts[1]);
            
            // Einfaches Key-Value Parsing
            $lines = explode("\n", $frontMatter);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || strpos($line, ':') === false) {
                    continue;
                }
                
                [$key, $value] = explode(':', $line, 2);
                $cleanKey = trim($key);
                $cleanValue = trim($value, ' "\"');
                
                if (strtolower($cleanKey) === 'title') {
                    return $cleanValue;
                }
            }
        }
    }
    
    // Fallback: Titel aus Route generieren
    $title = str_replace(['/', '-', '_'], ' ', $route);
    return ucwords($title);
}

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
    
    <!-- Monokai Dark Theme CSS -->
    <style>
        :root {
            /* Monokai Dark Colors */
            --monokai-bg: #272822;
            --monokai-paper: #3e3d32;
            --monokai-text: #f8f8f2;
            --monokai-comment: #75715e;
            --monokai-red: #f92672;
            --monokai-orange: #fd971f;
            --monokai-yellow: #f4bf75;
            --monokai-green: #a6e22e;
            --monokai-blue: #66d9ef;
            --monokai-purple: #ae81ff;
            --monokai-pink: #f92672;
            --monokai-border: #49483e;
            --monokai-sidebar: #49483e;
        }
        
        body {
            background-color: var(--monokai-bg);
            color: var(--monokai-text);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background-color: var(--monokai-paper) !important;
            border-bottom: 2px solid var(--monokai-border);
        }
        
        .navbar-brand, .navbar-nav .nav-link {
            color: var(--monokai-text) !important;
        }
        
        .navbar-nav .nav-link:hover {
            color: var(--monokai-blue) !important;
        }
        
        .btn-primary {
            background-color: var(--monokai-blue);
            border-color: var(--monokai-blue);
            color: var(--monokai-bg);
        }
        
        .btn-primary:hover {
            background-color: var(--monokai-purple);
            border-color: var(--monokai-purple);
        }
        
        .btn-warning {
            background-color: var(--monokai-orange);
            border-color: var(--monokai-orange);
            color: var(--monokai-bg);
        }
        
        .btn-success {
            background-color: var(--monokai-green);
            border-color: var(--monokai-green);
            color: var(--monokai-bg);
        }
        
        .btn-danger {
            background-color: var(--monokai-red);
            border-color: var(--monokai-red);
        }
        
        .btn-outline-primary {
            color: var(--monokai-blue);
            border-color: var(--monokai-blue);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--monokai-blue);
            border-color: var(--monokai-blue);
            color: var(--monokai-bg);
        }
        
        .card {
            background-color: var(--monokai-paper);
            border: 1px solid var(--monokai-border);
            color: var(--monokai-text);
        }
        
        .card-header {
            background-color: var(--monokai-sidebar);
            color: var(--monokai-text);
            border-bottom: 1px solid var(--monokai-border);
        }
        
        .breadcrumb {
            background-color: var(--monokai-paper);
            border: 1px solid var(--monokai-border);
        }
        
        .breadcrumb-item a {
            color: var(--monokai-blue);
        }
        
        .breadcrumb-item.active {
            color: var(--monokai-text);
        }
        
        .badge {
            background-color: var(--monokai-purple) !important;
        }
        
        .badge.bg-primary {
            background-color: var(--monokai-blue) !important;
            color: var(--monokai-bg) !important;
        }
        
        .badge.bg-success {
            background-color: var(--monokai-green) !important;
            color: var(--monokai-bg) !important;
        }
        
        .badge.bg-warning {
            background-color: var(--monokai-orange) !important;
            color: var(--monokai-bg) !important;
        }
        
        .alert-info {
            background-color: rgba(102, 217, 239, 0.2);
            border-color: var(--monokai-blue);
            color: var(--monokai-text);
        }
        
        .alert-success {
            background-color: rgba(166, 226, 46, 0.2);
            border-color: var(--monokai-green);
            color: var(--monokai-green);
        }
        
        .dropdown-menu {
            background-color: var(--monokai-paper);
            border: 1px solid var(--monokai-border);
        }
        
        .dropdown-item {
            color: var(--monokai-text);
        }
        
        .dropdown-item:hover {
            background-color: var(--monokai-sidebar);
            color: var(--monokai-text);
        }
        
        .form-control {
            background-color: var(--monokai-paper);
            border-color: var(--monokai-border);
            color: var(--monokai-text);
        }
        
        .form-control:focus {
            background-color: var(--monokai-paper);
            border-color: var(--monokai-blue);
            color: var(--monokai-text);
            box-shadow: 0 0 0 0.2rem rgba(102, 217, 239, 0.25);
        }
        
        .form-control::placeholder {
            color: var(--monokai-comment);
        }
        
        pre {
            background-color: var(--monokai-sidebar);
            border: 1px solid var(--monokai-border);
            color: var(--monokai-text);
        }
        
        code {
            background-color: var(--monokai-sidebar);
            color: var(--monokai-red);
            padding: 2px 4px;
            border-radius: 3px;
        }
        
        blockquote {
            border-left: 4px solid var(--monokai-blue);
            background-color: var(--monokai-sidebar);
            color: var(--monokai-text);
            padding: 15px;
            margin: 15px 0;
        }
        
        .admin-toolbar {
            position: fixed;
            top: 50%;
            right: 20px;
            transform: translateY(-50%);
            z-index: 1000;
        }
        
        footer {
            background-color: var(--monokai-paper);
            color: var(--monokai-text);
            border-top: 1px solid var(--monokai-border);
        }
        
        a {
            color: var(--monokai-blue);
        }
        
        a:hover {
            color: var(--monokai-purple);
        }
        
        h1, h2, h3, h4, h5, h6 {
            color: var(--monokai-text);
            font-weight: 600;
        }
        
        .text-muted {
            color: var(--monokai-comment) !important;
        }
        
        .text-light {
            color: var(--monokai-text) !important;
        }
    </style>
    
    <?php if (isset($meta['css'])): ?>
    <style><?= $meta['css'] ?></style>
    <?php endif; ?>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
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
                                    <li><a class="dropdown-item" href="/<?= encodeUrlPath($page['route']) ?>"><?= htmlspecialchars($page['title'] ?? basename($page['route'])) ?></a></li>
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