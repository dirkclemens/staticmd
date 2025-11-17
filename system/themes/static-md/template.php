<?php
/**
 * StaticMD Theme - Haupt-Template
 * Verwendet Bootstrap 5 für modernes, responsives Design
 */

// Theme configuration
$siteName = $config['system']['name'] ?? 'StaticMD';
$currentRoute = $_GET['route'] ?? 'index';
$currentTheme = 'static-md';

// Include shared head section
include __DIR__ . '/../shared/head.php';
?>

<!-- insert custom css here -->

</head>
<body>
    <?php 
    // Include shared navigation
    include __DIR__ . '/navigation.php';
    ?>

    <!-- Hauptinhalt - Layout-spezifisch -->
    <div class="content-wrapper">
        <div class="container">
            <!-- Breadcrumb Navigation -->
            <?= $themeHelper->renderBreadcrumbs($breadcrumbs ?? []) ?>
            
            <div class="row">
                <div class="col-lg-10">
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
                <div class="col-lg-2">
                    <div class="sidebar">
                        <h5><i class="bi bi-list-ul me-2"></i>Navigation</h5>
                        
                        <?php if (!empty($navItems)): ?>
                        <div class="list-group list-group-flush">
                            <a href="/" class="list-group-item list-group-item-action <?= $currentRoute === 'index' ? 'active' : '' ?>">
                                <i class="bi bi-house me-2"></i> <!-- Startseite -->
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
                        <div class="mt-2">
                            <a href="/tag" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-tags me-1"></i>Alle Tags anzeigen
                            </a>
                        </div>
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
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php 
    // Admin-Toolbar mit geteilter Komponente
    include __DIR__ . '/../shared/admin-toolbar.php';
    
    // Footer mit geteilter Komponente
    include __DIR__ . '/../shared/footer.php'; 
    
    // Scripts mit geteilter Komponente
    include __DIR__ . '/../shared/scripts.php'; 
    ?>    

    <!-- insert custom javascript here -->
    
</body>
</html>