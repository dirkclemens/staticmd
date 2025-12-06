<?php
/**
 * Shared Admin Toolbar
 * Nur sichtbar wenn Admin eingeloggt ist
 */
?>
<?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']): ?>
    <!-- Admin-Toolbar (nur wenn eingeloggt) -->
    <div class="admin-toolbar">
        <div class="btn-group-vertical" role="group">
            <a href="/admin?return_to_frontend=1" class="btn btn-primary btn-sm" title="Admin Dashboard">
                <i class="bi bi-gear"></i>
            </a>
            <?php if (isset($content['file_path'])): ?>
            <a href="/admin?action=edit&file=<?= urlencode($content['route']) ?>&return_url=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-warning btn-sm" title="Seite bearbeiten">
                <i class="bi bi-pencil"></i>
            </a>
            <?php 
            // Verzeichnis ermitteln: Bei Folder-Overview nutze die Route direkt,
            // bei normalen Dateien nutze das übergeordnete Verzeichnis
            
            if (isset($content['folder_route'])) {
                // Es ist eine Verzeichnis-Übersicht (kein index.md vorhanden)
                $currentDir = $content['folder_route'];
            } else {
                // Es ist eine normale Datei (oder index.md in einem Verzeichnis)
                // Nutze das übergeordnete Verzeichnis
                $route = $content['route'];
                $currentDir = dirname($route);
                if ($currentDir === '.' || $currentDir === '') {
                    $currentDir = '';
                }
            }
            
            // Stelle sicher, dass der Pfad mit / endet (außer bei Root)
            if (!empty($currentDir) && !str_ends_with($currentDir, '/')) {
                $currentDir .= '/';
            }
            
            // error_log("DEBUG admin-toolbar: route = " . ($content['route'] ?? 'N/A'));
            // error_log("DEBUG admin-toolbar: folder_route = " . ($content['folder_route'] ?? 'N/A'));
            // error_log("DEBUG admin-toolbar: currentDir = " . $currentDir);
            ?>
            <a href="/admin?action=new&prefill_path=<?= urlencode($_SERVER['REQUEST_URI']) ?>/&return_url=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-success btn-sm" title="Neue Seite in diesem Verzeichnis">
                <i class="bi bi-file-earmark-plus"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>