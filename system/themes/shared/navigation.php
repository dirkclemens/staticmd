<?php
/**
 * Shared Navigation Section
 * Verwendet von allen Themes und Layouts
 */
?>
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
                            <i class="bi bi-house me-1"></i>
                        </a>
                    </li>
                    
                    <?php foreach ($navItems as $section => $nav): ?>
                        <?php 
                        // Skip index pages in navigation (for blog layout compatibility)
                        if ($section === 'index' || $nav['route'] === 'index') continue;
                        ?>
                        <?php if ($section !== 'index' && $section !== 'home'): ?>
                            
                            <?php 
                            // Check if dropdowns are enabled in settings
                            $showDropdowns = $settings['navigation_show_dropdowns'] ?? true;
                            ?>
                            
                            <?php if ($showDropdowns && !empty($nav['pages']) && count($nav['pages']) > 0): ?>
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
                                        <?php 
                                        // Skip index pages in dropdown (for blog layout compatibility)
                                        if (basename($page['route']) === 'index') continue;
                                        ?>
                                    <li><a class="dropdown-item" href="/<?= \StaticMD\Themes\ThemeHelper::encodeUrlPath($page['route']) ?>">
                                        <?= htmlspecialchars($page['title'] ?? basename($page['route'])) ?>
                                    </a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                            <?php else: ?>
                            <!-- Normaler Link für einzelne Dateien oder wenn Dropdowns deaktiviert -->
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