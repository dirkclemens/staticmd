<?php
/**
 * Solarized Light Theme - Haupt-Template
 * Verwendet Bootstrap 5 mit Solarized Light Farbschema
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
    
    <!-- Solarized Light Theme CSS -->
    <style>
        :root {
            /* Solarized Light Colors */
            --sol-base03: #002b36;
            --sol-base02: #073642;
            --sol-base01: #586e75;
            --sol-base00: #657b83;
            --sol-base0: #839496;
            --sol-base1: #93a1a1;
            --sol-base2: #eee8d5;
            --sol-base3: #fdf6e3;
            --sol-yellow: #b58900;
            --sol-orange: #cb4b16;
            --sol-red: #dc322f;
            --sol-magenta: #d33682;
            --sol-violet: #6c71c4;
            --sol-blue: #268bd2;
            --sol-cyan: #2aa198;
            --sol-green: #859900;
        }
        
        body {
            background-color: var(--sol-base3);
            color: var(--sol-base00);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background-color: var(--sol-base2) !important;
            border-bottom: 2px solid var(--sol-base1);
        }
        
        .navbar-brand, .navbar-nav .nav-link {
            color: var(--sol-base01) !important;
        }
        
        .navbar-nav .nav-link:hover {
            color: var(--sol-blue) !important;
        }
        
        .btn-primary {
            background-color: var(--sol-blue);
            border-color: var(--sol-blue);
        }
        
        .btn-primary:hover {
            background-color: var(--sol-cyan);
            border-color: var(--sol-cyan);
        }
        
        .btn-warning {
            background-color: var(--sol-yellow);
            border-color: var(--sol-yellow);
            color: var(--sol-base3);
        }
        
        .btn-success {
            background-color: var(--sol-green);
            border-color: var(--sol-green);
        }
        
        .btn-danger {
            background-color: var(--sol-red);
            border-color: var(--sol-red);
        }
        
        .card {
            background-color: var(--sol-base2);
            border: 1px solid var(--sol-base1);
        }
        
        .card-header {
            background-color: var(--sol-base1);
            color: var(--sol-base3);
        }
        
        .breadcrumb {
            background-color: var(--sol-base2);
        }
        
        .breadcrumb-item a {
            color: var(--sol-blue);
        }
        
        .badge {
            background-color: var(--sol-violet) !important;
        }
        
        .badge.bg-primary {
            background-color: var(--sol-blue) !important;
        }
        
        .badge.bg-success {
            background-color: var(--sol-green) !important;
        }
        
        .badge.bg-warning {
            background-color: var(--sol-yellow) !important;
            color: var(--sol-base3) !important;
        }
        
        .alert-info {
            background-color: var(--sol-base2);
            border-color: var(--sol-cyan);
            color: var(--sol-base01);
        }
        
        .alert-success {
            background-color: rgba(133, 153, 0, 0.1);
            border-color: var(--sol-green);
            color: var(--sol-green);
        }
        
        pre {
            background-color: var(--sol-base2);
            border: 1px solid var(--sol-base1);
        }
        
        code {
            background-color: var(--sol-base2);
            color: var(--sol-base01);
        }
        
        blockquote {
            border-left: 4px solid var(--sol-blue);
            background-color: var(--sol-base2);
        }
        
        .admin-toolbar {
            position: fixed;
            top: 50%;
            right: 20px;
            transform: translateY(-50%);
            z-index: 1000;
        }
        
        footer {
            background-color: var(--sol-base01);
            color: var(--sol-base2);
        }
        
        a {
            color: var(--sol-blue);
        }
        
        a:hover {
            color: var(--sol-cyan);
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
                        <?php if ($section !== 'index'): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle <?= strpos($currentRoute, $section) === 0 ? 'active' : '' ?>" 
                               href="/<?= encodeUrlPath($nav['route']) ?>" 
                               id="navbarDropdown<?= $section ?>" 
                               role="button" 
                               data-bs-toggle="dropdown">
                                <?= htmlspecialchars($nav['title']) ?>
                            </a>
                            <?php if (!empty($nav['pages'])): ?>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="/<?= encodeUrlPath($nav['route']) ?>">Übersicht</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <?php foreach ($nav['pages'] as $page): ?>
                                <li><a class="dropdown-item" href="/<?= encodeUrlPath($page['route']) ?>"><?= htmlspecialchars(basename($page['route'])) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </li>
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