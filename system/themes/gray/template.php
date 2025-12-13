<?php
/**
 * Gray Theme 
 * Layout: Header (top) → Sidebar (left: search + nav) + Content (right) → Footer (bottom)
 */

// Theme configuration
$siteName = $config['system']['name'] ?? 'StaticMD';
$currentRoute = $_GET['route'] ?? 'index';
$currentTheme = 'gray';
$themeMode = 'dark'; // 'light' or 'dark'

// Include shared head section
include __DIR__ . '/../shared/head.php';
?>
    <!-- KaTeX CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">

</head>
<body>
<header class="header">
    <div class="header-content">
        <a class="logo" href="/">
            <?php if (!empty($siteLogo)): ?>
                <img src="<?= htmlspecialchars($siteLogo) ?>" alt="Logo" class="site-logo me-2">
            <?php else: ?>
                <i class="bi bi-file-earmark-text me-2"></i>
            <?php endif; ?>
            <?= htmlspecialchars($siteName) ?>
        </a>

        <div class="sidebar-search">
            <form action="/search" method="get">
                <input type="search" name="q" placeholder="Suche..." value="<?= htmlspecialchars($_GET['q']??'') ?>">
            </form>
        </div>
        <a href="/admin?return_to_frontend=1" class="btn" title="Admin Dashboard"><i class="bi bi-gear"></i>&nbsp;Admin</a>
    </div>
</header>

<!-- Hauptcontainer -->
<div class="main-container">

<main class="content">
<?php if(!empty($meta)&&($meta['author']??$meta['date']??null)): ?>
<div class="meta-info">
<?php if(isset($meta['author'])): ?><i class="bi bi-person"></i><span><?= htmlspecialchars($meta['author'])?></span> &nbsp;&nbsp;<?php endif; ?>
<?php if(isset($meta['date'])): ?><i class="bi bi-calendar"></i><span><?= htmlspecialchars($meta['date'])?></span><?php endif; ?>
<?php if(isset($content['modified'])): ?> &nbsp;&nbsp;<small style="opacity:.7"><i class="bi bi-clock"></i>Aktualisiert: <?= date('d.m.Y',$content['modified'])?></small><?php endif; ?>
</div>
<?php endif; ?>
<?php if(isset($_GET['saved'])): ?>
<div class="alert alert-success alert-dismissible"><i class="bi bi-check-circle"></i>&nbsp;Die Seite wurde erfolgreich gespeichert.<button type="button" class="btn-close" onclick="this.parentElement.remove()">&times;</button></div>
<?php endif; ?>
<?php $visibility=$meta['Visibility']??$meta['visibility']??'public';if($visibility==='private'): ?>
<div class="alert alert-warning"><i class="bi bi-lock"></i><strong>Private Seite:</strong> Diese Seite ist nur für angemeldete Admins sichtbar.</div>
<?php endif; ?>

<!-- Hauptcontent -->
<article><?= $body ?></article>

<!-- Tag-Cloud anzeigen, wenn Tags vorhanden sind -->
<?php if(isset($meta['tags'])&&!empty(trim($meta['tags']))): ?>
<div style="margin-top:3rem;padding-top:2rem;border-top:1px solid var(--gp-border)">
<h4><i class="bi bi-tags"></i> Tags</h4>
<div class="tag-cloud">
<?php foreach(explode(',',$meta['tags']) as $tag):$cleanTag=trim($tag);if(!empty($cleanTag)): ?>
<a href="/tag/<?=\StaticMD\Themes\ThemeHelper::encodeUrlPath($cleanTag)?>"><?= htmlspecialchars($cleanTag)?></a>
<?php endif;endforeach; ?>
</div>
</div>
<?php endif; ?>
</main>

<aside class="sidebar-right">
<div class="sidebar-search">
<form action="/search" method="get">
<input type="search" name="q" placeholder="Suche..." value="<?= htmlspecialchars($_GET['q']??'') ?>">
</form>
</div>
<?php
include __DIR__ . '/../shared/sidebar.php';
?>
</aside>
</div>

<footer class="footer">
<div class="footer-content">
<div class="footer-logo"><i class="bi bi-leaf"></i> 
    <a class="logo" href="/">
        <?php if (!empty($siteLogo)): ?>
            <img src="<?= htmlspecialchars($siteLogo) ?>" alt="Logo" class="site-logo me-2">
        <?php else: ?>
            <i class="bi bi-file-earmark-text me-2"></i>
        <?php endif; ?>
        <?= htmlspecialchars($siteName) ?>
    </a>
</div>
<div class="footer-text">Powered by <a href="https://github.com/dirkclemens/staticmd" target="_blank">StaticMD</a> &copy; <?= date('Y') ?></div>
</div>
</footer>



<div class="admin-toolbar">
<?php if(isset($_SESSION['admin_logged_in'])&&$_SESSION['admin_logged_in']): ?>
    <?php if(isset($content['file_path'])): ?>
        <a href="/admin?action=edit&file=<?=urlencode($content['route'])?>&return_url=<?=urlencode($_SERVER['REQUEST_URI'])?>" class="btn" title="Seite bearbeiten" style="background-color:#FFC439"><i class="bi bi-pencil"></i></a>
        <a href="/admin?action=new&prefill_path=<?=urlencode($_SERVER['REQUEST_URI'])?>&return_url=<?=urlencode($_SERVER['REQUEST_URI'])?>" class="btn" title="Neue Seite" style="background-color:#61BA01"><i class="bi bi-file-earmark-plus"></i></a>
    <?php endif; ?>
    <a href="/admin?action=logout" class="btn" title="Logout" style="background-color:#343A41"><i class="bi bi-box-arrow-right"></i></a>
<?php endif; ?>
<a href="/admin?return_to_frontend=1" class="btn" title="Admin Dashboard" style="background-color:#0d6efd"><i class="bi bi-gear"></i></a>
</div>
<?php
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
