<?php
/**
 * Green Theme - Bootstrap-based
 * Modern light theme using Bootstrap 5 components
 */

// Theme configuration
$siteName = $config['system']['name'] ?? 'StaticMD';
$currentRoute = $_GET['route'] ?? 'index';
$currentTheme = 'green';
$themeMode = 'light';

// Include shared head section
include __DIR__ . '/../shared/head.php';
?>
    <!-- insert custom css here -->

    <!-- KaTeX CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">

</head>
<body>
    <!-- Navigation -->
    <?php
    // Navigation mit geteilter Komponente
    include __DIR__ . '/../shared/navigation.php'; 
    ?>

    <!-- Main Content -->
    <div class="container-fluid">
        <div class="row">

            <!-- Sidebar Column -->
            <div class="sidebar-left col-lg-3">
                <div class="sidebar sticky-top">
                    <!-- Search Box -->
                    <div class="mb-4">
                        <form action="/search" method="GET">
                            <div class="input-group">
                                <input type="search" name="q" class="form-control text-light border-secondary" 
                                       placeholder="Suchen..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                                <button class="btn border-secondary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Navigation -->
                    <?php include __DIR__ . '/../shared/sidebar.php'; ?>
                </div>
            </div>

            <!-- Content Column -->
            <div class="col-lg-9">
                <!-- Breadcrumb Navigation -->
                <?= $themeHelper->renderBreadcrumbs($breadcrumbs ?? []) ?>

                <!-- Meta Information -->
                <?php if (!empty($meta) && ($meta['author'] ?? $meta['date'] ?? null)): ?>
                <div class="card border-secondary mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <?php if (isset($meta['author'])): ?>
                                <i class="bi bi-person text-warning me-1"></i>
                                <span class="me-3"><?= htmlspecialchars($meta['author']) ?></span>
                                <?php endif; ?>
                                
                                <?php if (isset($meta['date'])): ?>
                                <i class="bi bi-calendar text-warning me-1"></i>
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
                </div>
                <?php endif; ?>
                
                <!-- Success Alert -->
                <?php if (isset($_GET['saved'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    Die Seite wurde erfolgreich gespeichert.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <!-- Private Page Warning -->
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
                <article class="content">
                    <?= $body ?>
                </article>
                
                <!-- Tags -->
                <?php if (isset($meta['tags']) && !empty(trim($meta['tags']))): ?>
                <div class="mt-5 pt-4 border-top border-secondary">
                    <h4 class="mb-3">
                        <i class="bi bi-tags me-2"></i>Tags
                    </h4>
                    <div class="tag-cloud">
                        <?php foreach (explode(',', $meta['tags']) as $tag): ?>
                            <?php $cleanTag = trim($tag); ?>
                            <?php if (!empty($cleanTag)): ?>
                            <a href="/tag/<?= \StaticMD\Themes\ThemeHelper::encodeUrlPath($cleanTag) ?>" 
                               class="badge bg-primary me-1 mb-2 text-decoration-none">
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

    <?php
        // Footer mit geteilter Komponente
        include __DIR__ . '/../shared/footer.php'; 
    
        // Admin Toolbar mit geteilter Komponente
        include __DIR__ . '/../shared/admin-toolbar.php'; 

        // Scripts mit geteilter Komponente
        include __DIR__ . '/../shared/scripts.php'; 
    ?>    

    <!-- KaTeX JS -->
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/contrib/auto-render.min.js"></script>
        
    <script>
        // Code syntax highlighting (simple)
        document.querySelectorAll('pre code').forEach(block => {
            block.classList.add('language-' + (block.className.match(/language-(\w+)/) || ['', 'text'])[1]);
        });
        
        // KaTeX Math Rendering
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof renderMathInElement !== 'undefined') {
                renderMathInElement(document.body, {
                    delimiters: [
                        {left: '$$', right: '$$', display: true},
                        {left: '$', right: '$', display: false},
                        {left: '\\(', right: '\\)', display: false},
                        {left: '\\[', right: '\\]', display: true}
                    ],
                    throwOnError : false
                });
            }
        });

    document.querySelectorAll('.alert-dismissible .btn-close').forEach(btn=>{btn.addEventListener('click',function(){this.parentElement.style.display='none'})});
    </script>
</body>
</html>
