<?php
/**
 * StaticMD Theme - Haupt-Template
 * Verwendet Bootstrap 5 für modernes, responsives Design
 */

// Theme configuration
$siteName = $config['system']['name'] ?? 'StaticMD';
$currentRoute = $_GET['route'] ?? 'index';
$currentTheme = 'static-md';
$themeMode = 'light'; // 'light' or 'dark'

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

                <div class="col-lg-3 col-sm-4">
                <?php 
                // Include shared navigation
                include __DIR__ . '/sidebar.php';
                ?>
                </div>

                <div class="col-lg-9 col-sm-8">
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
                    
                    <!-- Hauptcontent -->
                    <article class="content">
                        <?= $body ?>
                    </article>
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

    <!-- KaTeX JS -->
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/contrib/auto-render.min.js"></script>
        
    <script>
        // Copy code to clipboard function
        function copyCode(button) {
            const codeBlock = button.previousElementSibling.querySelector('code');
            const code = codeBlock.textContent;
            
            navigator.clipboard.writeText(code).then(() => {
                // Visual feedback
                const originalHTML = button.innerHTML;
                button.innerHTML = '<i class="bi bi-check"></i>';
                button.classList.add('copied');
                
                setTimeout(() => {
                    button.innerHTML = originalHTML;
                    button.classList.remove('copied');
                }, 2000);
            }).catch(err => {
                console.error('Fehler beim Kopieren:', err);
            });
        }
        
        // Copy inline code to clipboard function
        function copyInlineCode(button) {
            const codeElement = button.previousElementSibling;
            const code = codeElement.textContent;
            
            navigator.clipboard.writeText(code).then(() => {
                // Visual feedback
                const originalHTML = button.innerHTML;
                button.innerHTML = '<i class="bi bi-check"></i>';
                button.classList.add('copied');
                
                setTimeout(() => {
                    button.innerHTML = originalHTML;
                    button.classList.remove('copied');
                }, 2000);
            }).catch(err => {
                console.error('Fehler beim Kopieren:', err);
            });
        }
        
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
    </script>
    
</body>
</html>