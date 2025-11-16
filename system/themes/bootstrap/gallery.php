<?php
/**
 * Bootstrap Theme - Gallery Layout
 * Spezielles Layout für Bildergalerien mit responsivem Grid
 */

// Theme-Konfiguration
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

// Theme Helper for common functions
require_once __DIR__ . '/../ThemeHelper.php';
$themeHelper = new \StaticMD\Themes\ThemeHelper($this->contentLoader);

// Navigation erstellen
$navItems = $themeHelper->buildNavigation();

// Sort navigation - load from settings
$navigationOrder = $this->contentLoader->getNavigationOrder();

// Apply sorting
uksort($navItems, function($a, $b) use ($navigationOrder) {
    $orderA = $navigationOrder[$a] ?? 999;
    $orderB = $navigationOrder[$b] ?? 999;
    
    if ($orderA === $orderB) {
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
    <meta name="description" content="<?= htmlspecialchars($meta['description'] ?? 'Galerie - Powered by StaticMD') ?>">
    <meta name="author" content="<?= htmlspecialchars($meta['author'] ?? '') ?>">
    
    <!-- SEO/Robots Meta-Tags -->
    <?= $robotsMeta ?>
    
    <title><?= htmlspecialchars($title) ?> - <?= htmlspecialchars($siteName) ?></title>
    
    <!-- Favicon -->  
    <link rel="icon" type="image/png" href="/public/images/favicon.png">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- GLightbox CSS für Lightbox-Funktionalität -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">
    
    <!-- Custom Gallery CSS -->
    <style>
        <?php include __DIR__ . '/template.css'; ?>
        
        /* Gallery-spezifische Styles */
        .gallery-header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 2rem 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 0.5rem;
        }
        
        .gallery-header h1 {
            color: var(--bs-primary);
            margin-bottom: 1rem;
            font-size: 2.5rem;
            font-weight: 300;
        }
        
        .gallery-grid {
            margin: 2rem 0;
        }
        
        .gallery-item {
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .gallery-item img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: opacity 0.3s ease;
        }
        
        .gallery-item img:hover {
            opacity: 0.9;
        }
        
        .gallery-item-info {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            color: white;
            padding: 1rem;
            border-radius: 0 0 0.5rem 0.5rem;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .gallery-item:hover .gallery-item-info {
            opacity: 1;
        }
        
        .gallery-item-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .gallery-item-description {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .gallery-stats {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .gallery-stats .stat-item {
            margin: 0 1rem;
        }
        
        .gallery-stats .stat-number {
            font-size: 2rem;
            font-weight: 600;
            color: var(--bs-primary);
            display: block;
        }
        
        .gallery-filter {
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .gallery-filter .btn {
            margin: 0.25rem;
        }
        
        /* Masonry-ähnliches Layout für verschiedene Bildgrößen */
        @media (min-width: 768px) {
            .gallery-masonry .gallery-item:nth-child(5n+1) img {
                height: 300px;
            }
            .gallery-masonry .gallery-item:nth-child(5n+3) img {
                height: 200px;
            }
        }
        
        /* Auto-Gallery Styles (für [gallery] Shortcode) */
        .auto-gallery-info {
            text-align: center;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
        }
        
        /* Direkte Bilder im Gallery-Container (vor JavaScript-Verarbeitung) */
        #gallery-container > img {
            width: 100%;
            max-width: 300px;
            height: 250px;
            object-fit: cover;
            border-radius: 0.5rem;
            margin: 0.75rem;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: inline-block;
            vertical-align: top;
        }
        
        #gallery-container > img:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        /* Responsive Anpassungen */
        @media (max-width: 767px) {
            .gallery-item img {
                height: 200px;
            }
            
            #gallery-container > img {
                height: 200px;
                max-width: 100%;
                margin: 0.5rem 0;
            }
            
            .gallery-header h1 {
                font-size: 2rem;
            }
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
                    <i class="bi bi-images me-2"></i>
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
                            <i class="bi bi-house me-1"></i> <!--Startseite-->
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
                                    <li><a class="dropdown-item" href="/<?= \StaticMD\Themes\ThemeHelper::encodeUrlPath($nav['route']) ?>"><?= \StaticMD\Core\I18n::t('core.overview') ?></a></li>
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
        <div class="container-fluid px-4">
            <!-- Breadcrumb Navigation -->
            <?= $themeHelper->renderBreadcrumbs($breadcrumbs ?? []) ?>
            
            <!-- Gallery Header -->
            <div class="gallery-header">
                <h1><i class="bi bi-images me-3"></i><?= htmlspecialchars($title) ?></h1>
                <?php if (!empty($meta['description'])): ?>
                <p class="lead"><?= htmlspecialchars($meta['description']) ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Meta-Informationen -->
            <?php if (!empty($meta) && ($meta['author'] ?? $meta['date'] ?? null)): ?>
            <div class="gallery-stats">
                <div class="row justify-content-center">
                    <?php if (isset($meta['author'])): ?>
                    <div class="col-auto stat-item">
                        <span class="stat-number"><i class="bi bi-person"></i></span>
                        <small class="text-muted"><?= htmlspecialchars($meta['author']) ?></small>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($meta['date'])): ?>
                    <div class="col-auto stat-item">
                        <span class="stat-number"><i class="bi bi-calendar"></i></span>
                        <small class="text-muted"><?= htmlspecialchars($meta['date']) ?></small>
                    </div>
                    <?php endif; ?>
                    
                    <div class="col-auto stat-item">
                        <span class="stat-number" id="image-count">0</span>
                        <small class="text-muted">Bilder</small>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Gallery Filter (falls Tags vorhanden) -->
            <?php if (isset($meta['tags'])): ?>
            <div class="gallery-filter">
                <button class="btn btn-outline-primary active" data-filter="all">
                    <i class="bi bi-grid me-1"></i> Alle anzeigen
                </button>
                <?php foreach (explode(',', $meta['tags']) as $tag): ?>
                    <?php $cleanTag = trim($tag); ?>
                    <?php if (!empty($cleanTag)): ?>
                    <button class="btn btn-outline-secondary" data-filter="<?= htmlspecialchars(strtolower($cleanTag)) ?>">
                        <?= htmlspecialchars($cleanTag) ?>
                    </button>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <!-- Erfolgsmeldung nach Speichern -->
            <?php if (isset($_GET['saved'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                Die Galerie wurde erfolgreich gespeichert.
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
                <strong>Private Galerie:</strong> Diese Galerie ist nur für angemeldete Admins sichtbar.
            </div>
            <?php endif; ?>
            
            <!-- Gallery Content -->
            <article class="content">
                <div class="gallery-grid">
                    <div class="row gallery-masonry" id="gallery-container">
                        <!-- Der Markdown-Content wird hier eingefügt -->
                        <?= $body ?>
                    </div>
                </div>
            </article>
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
            <a href="/admin?action=edit&file=<?= urlencode($content['route']) ?>&return_url=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-warning btn-sm" title="Galerie bearbeiten">
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
                        <small class="text-light">Galerie-Layout - Powered by StaticMD</small>
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
    
    <!-- GLightbox JS für Lightbox-Funktionalität -->
    <script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
       
    <!-- Gallery Custom JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gallery setup first
            setupGallery();
            
            // Initialize Lightbox after gallery setup
            const lightbox = GLightbox({
                touchNavigation: true,
                loop: true,
                autoplayVideos: false,
                selector: '#gallery-container img' // All images in gallery container
            });
            
            // Filter functionality
            setupGalleryFilter();
        });
        
        function setupGallery() {
            const galleryContainer = document.getElementById('gallery-container');
            const allImages = galleryContainer.querySelectorAll('img');
            
            // Update image count (nur wenn Element existiert)
            const imageCountElement = document.getElementById('image-count');
            if (imageCountElement) {
                imageCountElement.textContent = allImages.length;
            }
            
            // Add lightbox attributes to all images first
            allImages.forEach(function(img, index) {
                // Add lightbox attributes
                img.setAttribute('data-gallery', 'gallery');
                img.setAttribute('data-glightbox', 'title: ' + (img.alt || 'Bild ' + (index + 1)));
            });
            
            // Wrap images in gallery items
            allImages.forEach(function(img, index) {
                if (!img.closest('.gallery-item')) {
                    const galleryItem = document.createElement('div');
                    galleryItem.className = 'col-md-4 col-lg-3 gallery-item';
                    
                    const imgContainer = document.createElement('div');
                    imgContainer.className = 'position-relative';
                    
                    // Add tags from image alt or title for filtering
                    const imgTags = img.getAttribute('title') || img.getAttribute('alt') || '';
                    if (imgTags) {
                        galleryItem.setAttribute('data-tags', imgTags.toLowerCase());
                    }
                    
                    // Create info overlay if image has alt text
                    if (img.alt) {
                        const infoDiv = document.createElement('div');
                        infoDiv.className = 'gallery-item-info';
                        
                        const titleDiv = document.createElement('div');
                        titleDiv.className = 'gallery-item-title';
                        titleDiv.textContent = img.alt;
                        
                        infoDiv.appendChild(titleDiv);
                        imgContainer.appendChild(infoDiv);
                    }
                    
                    // Wrap image
                    img.parentNode.insertBefore(galleryItem, img);
                    imgContainer.appendChild(img);
                    galleryItem.appendChild(imgContainer);
                }
            });
        }
        
        function setupGalleryFilter() {
            const filterButtons = document.querySelectorAll('[data-filter]');
            const galleryItems = document.querySelectorAll('.gallery-item');
            
            filterButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    const filter = this.getAttribute('data-filter');
                    
                    // Update active button
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Filter items
                    galleryItems.forEach(function(item) {
                        const tags = item.getAttribute('data-tags') || '';
                        
                        if (filter === 'all' || tags.includes(filter)) {
                            item.style.display = 'block';
                            item.style.opacity = '1';
                        } else {
                            item.style.opacity = '0';
                            setTimeout(() => {
                                if (item.style.opacity === '0') {
                                    item.style.display = 'none';
                                }
                            }, 300);
                        }
                    });
                    
                    // Update image count
                    setTimeout(() => {
                        const visibleItems = document.querySelectorAll('.gallery-item:not([style*="display: none"])');
                        const imageCountElement = document.getElementById('image-count');
                        if (imageCountElement) {
                            imageCountElement.textContent = visibleItems.length;
                        }
                    }, 350);
                });
            });
        }
        
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
    </script>
    
    <?php if (isset($meta['js'])): ?>
    <script><?= $meta['js'] ?></script>
    <?php endif; ?>
</body>
</html>