<?php
/**
 * Sidebar Section, used by StaticMD Static Theme
 */

// Rekursive Funktion zum Abrufen aller Unterverzeichnisse
function getSubfolders($path, $baseRoute, $config) {
    $subfolders = [];
    
    if (!is_dir($path)) {
        return $subfolders;
    }
    
    $iterator = new DirectoryIterator($path);
    foreach ($iterator as $item) {
        if ($item->isDot() || !$item->isDir()) {
            continue;
        }
        
        $folderName = $item->getFilename();
        $folderRoute = $baseRoute . '/' . $folderName;
        
        // Prüfe ob Ordner Markdown-Dateien enthält
        $hasMdFiles = false;
        $subIterator = new DirectoryIterator($item->getPathname());
        foreach ($subIterator as $subItem) {
            if ($subItem->isFile() && pathinfo($subItem->getFilename(), PATHINFO_EXTENSION) === 'md') {
                $hasMdFiles = true;
                break;
            }
        }
        
        if ($hasMdFiles) {
            // Titel aus index.md extrahieren falls vorhanden
            $indexFile = $item->getPathname() . '/index.md';
            $title = str_replace(['-', '_'], ' ', $folderName);
            
            if (file_exists($indexFile)) {
                $content = file_get_contents($indexFile);
                if (preg_match('/^---\s*\n.*?^Title:\s*(.+?)\s*\n.*?^---\s*\n/ms', $content, $matches)) {
                    $title = trim($matches[1]);
                }
            }
            
            // Rekursiv: Hole auch Unterverzeichnisse dieses Ordners
            $children = getSubfolders($item->getPathname(), $folderRoute, $config);
            
            $subfolders[$folderName] = [
                'route' => $folderRoute,
                'title' => $title,
                'children' => $children
            ];
        }
    }
    
    // Alphabetisch sortieren
    uasort($subfolders, function($a, $b) {
        return strcasecmp($a['title'], $b['title']);
    });
    
    return $subfolders;
}

// Rekursive Funktion zum Rendern der Navigation
function renderNavTree($subfolders, $currentRoute, $level = 0) {
    if (empty($subfolders)) {
        return '';
    }
    
    $html = '';
    $indent = str_repeat('    ', $level + 1);
    
    foreach ($subfolders as $key => $folder) {
        $isActive = strpos($currentRoute, $folder['route']) === 0;
        $hasChildren = !empty($folder['children']);
        $collapseId = 'collapse-' . md5($folder['route']);
        
        if ($hasChildren) {
            // Ordner mit Unterverzeichnissen
            $html .= $indent . '<a href="/' . \StaticMD\Themes\ThemeHelper::encodeUrlPath($folder['route']) . '" ' . "\n";
            $html .= $indent . '   class="list-group-item list-group-item-action ' . ($isActive ? 'active' : '') . '" ' . "\n";
            $html .= $indent . '   data-bs-toggle="collapse" ' . "\n";
            $html .= $indent . '   data-bs-target="#' . $collapseId . '" ' . "\n";
            $html .= $indent . '   aria-expanded="' . ($isActive ? 'true' : 'false') . '">' . "\n";
            $html .= $indent . '    <i class="bi bi-folder' . ($isActive ? '-open' : '') . ' me-2"></i> ' . "\n";
            $html .= $indent . '    ' . htmlspecialchars($folder['title']) . "\n";
            $html .= $indent . '    <i class="bi bi-chevron-down float-end"></i>' . "\n";
            $html .= $indent . '</a>' . "\n";
            
            // Unterverzeichnisse rekursiv
            $html .= $indent . '<div class="collapse ' . ($isActive ? 'show' : '') . '" id="' . $collapseId . '">' . "\n";
            $html .= $indent . '    <div class="list-group list-group-flush ms-3">' . "\n";
            $html .= renderNavTree($folder['children'], $currentRoute, $level + 1);
            $html .= $indent . '    </div>' . "\n";
            $html .= $indent . '</div>' . "\n";
        } else {
            // Ordner ohne Unterverzeichnisse
            $html .= $indent . '<a href="/' . \StaticMD\Themes\ThemeHelper::encodeUrlPath($folder['route']) . '" ' . "\n";
            $html .= $indent . '   class="list-group-item list-group-item-action ' . ($isActive ? 'active' : '') . '">' . "\n";
            $html .= $indent . '    <i class="bi bi-folder me-2"></i> ' . "\n";
            $html .= $indent . '    ' . htmlspecialchars($folder['title']) . "\n";
            $html .= $indent . '</a>' . "\n";
        }
    }
    
    return $html;
}
?>
                <!-- Sidebar -->                
                <div class="sidebar">
                    <h5><i class="bi bi-list-ul me-2"></i>Navigation</h5>
                    
                    <?php if (!empty($navItems)): ?>
                    <div class="list-group list-group-flush">
                        <a href="/" class="list-group-item list-group-item-action <?= $currentRoute === 'index' ? 'active' : '' ?>">
                            <i class="bi bi-house me-2"></i> <!-- Startseite -->
                        </a>
                        
                        <?php foreach ($navItems as $section => $nav): ?>
                            <?php if ($section !== 'index'): ?>
                                <?php 
                                $isActive = strpos($currentRoute, $section) === 0;
                                
                                // Scanne Dateisystem rekursiv für alle Unterverzeichnisse
                                $contentPath = $config['paths']['content'] . '/' . $nav['route'];
                                $subfolders = getSubfolders($contentPath, $nav['route'], $config);
                                $hasSubfolders = !empty($subfolders);
                                ?>
                                
                                <?php if ($hasSubfolders): ?>
                                    <!-- Ordner mit Unterverzeichnissen - Kollabierbar -->
                                    <a href="/<?= \StaticMD\Themes\ThemeHelper::encodeUrlPath($nav['route']) ?>" 
                                        class="list-group-item list-group-item-action <?= $isActive ? 'active' : '' ?>"
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#collapse-<?= htmlspecialchars($section) ?>"
                                        aria-expanded="<?= $isActive ? 'true' : 'false' ?>">
                                        <i class="bi bi-folder<?= $isActive ? '-open' : '' ?> me-2"></i> 
                                        <?= htmlspecialchars($nav['title']) ?>
                                        <i class="bi bi-chevron-down float-end"></i>
                                    </a>
                                    
                                    <!-- Unterverzeichnisse (rekursiv) -->
                                    <div class="collapse <?= $isActive ? 'show' : '' ?>" id="collapse-<?= htmlspecialchars($section) ?>">
                                        <div class="list-group list-group-flush ms-3">
                                            <?= renderNavTree($subfolders, $currentRoute, 0) ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- Einzelner Ordner ohne Unterverzeichnisse -->
                                    <a href="/<?= \StaticMD\Themes\ThemeHelper::encodeUrlPath($nav['route']) ?>" 
                                        class="list-group-item list-group-item-action <?= $isActive ? 'active' : '' ?>">
                                        <i class="bi bi-folder me-2"></i> 
                                        <?= htmlspecialchars($nav['title']) ?>
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                
                    <hr style="margin-top: 1.5rem; margin-bottom: 1.5rem;">

                    <!-- Zusätzliche Sidebar-Inhalte -->
                    <h5><i class="bi bi-tags me-2"></i>Tags</h5>                        
                    <div class="tag-cloud">
                        <?php if (!isset($meta['tags']) || empty($meta['tags'])): ?>
                            <p class="text-muted">Keine Tags verfügbar.</p>
                        <?php else: ?>
                            <?php foreach (explode(',', $meta['tags']) as $tag): ?>
                                <?php $cleanTag = trim($tag); ?>
                                <?php if (!empty($cleanTag)): ?>
                                <a href="/tag/<?= \StaticMD\Themes\ThemeHelper::encodeUrlPath($cleanTag) ?>" class="badge bg-primary text-white text-decoration-none me-1 mb-1">
                                    <?= htmlspecialchars($cleanTag) ?>
                                </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <hr style="margin-top: 1.5rem; margin-bottom: 1.5rem;">

                    <div class="mt-2">
                        <a href="/tag" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-tags me-1"></i>Alle Tags anzeigen
                        </a>
                    </div>
                </div>                    
                <!-- End Sidebar -->
                                            