<?php
/**
 * Solarized Theme - Blog Template (Modular)
 * Vereinfachte Darstellung für Blog-Listen
 */

// Theme configuration
$siteName = $config['system']['name'] ?? 'StaticMD';
$currentRoute = $_GET['route'] ?? 'index';
$currentTheme = 'orange';
$themeMode = 'light'; // 'light' or 'dark'

// Include shared head section
include __DIR__ . '/../shared/head.php';
?>
</head>
<body>
    <?php 
    // Include shared navigation
    include __DIR__ . '/../shared/navigation.php';
    ?>
    
    <div class="container-fluid px-4 pt-4">
        <!-- Breadcrumb Navigation -->
        <?= $themeHelper->renderBreadcrumbs($breadcrumbs ?? []) ?>
        
        <div class="row">
            <div class="col-lg-12"> <!-- Full-width for blog layout, no sidebar -->
                
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
                    &nbsp;Die Seite wurde erfolgreich gespeichert.
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
                
                <!-- Main Content -->
                <main class="container mt-4">
                    <?= $body ?>
                </main>
            </div>
                        
        </div>
    </div>    
    
    <?php 
    // Admin-Toolbar mit geteilter Komponente
    include __DIR__ . '/../shared/admin-toolbar.php';
    
    // Footer mit geteilter Komponente
    include __DIR__ . '/../shared/footer.php'; 
    
    // Scripts mit geteilter Komponente (vereinfacht für Blog)
    include __DIR__ . '/../shared/scripts.php'; 
    ?>
</body>
</html>