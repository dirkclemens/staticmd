<?php
$pageTitle = __('admin.files.title');
$currentUser = $this->auth->getUsername();
$timeRemaining = $this->auth->getTimeRemaining();

// Helper-Funktion für pfad-sichere URL-Encoding
function encodeUrlPath($path) {
    $parts = explode('/', $path);
    $encodedParts = array_map('rawurlencode', $parts);
    return implode('/', $encodedParts);
}
?>
<?php
// Security Headers setzen
require_once __DIR__ . '/../../core/SecurityHeaders.php';
use StaticMD\Core\SecurityHeaders;
SecurityHeaders::setAllSecurityHeaders('admin');
$nonce = SecurityHeaders::getNonce();
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(\StaticMD\Core\I18n::getLanguage()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('admin.brand') ?> - <?= __('admin.files.title') ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <link rel="stylesheet" href="/public/admin.css">
</head>
<body>
    <!-- Admin Header -->
    <nav class="navbar admin-header navbar-dark">
        <div class="container-fluid">
            <a href="/admin" class="navbar-brand mb-0 h1 text-decoration-none">
                <i class="bi bi-shield-lock me-2"></i>
                <?= __('admin.brand') ?>
            </a>
            
            <div class="d-flex align-items-center text-white">
                <div class="me-3">
                    <small class="session-timer">
                        <i class="bi bi-clock me-1"></i>
                        Session: <span id="timer"><?= gmdate('H:i:s', $timeRemaining) ?></span>
                    </small>
                </div>
                
                <div class="dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>
                        <?= htmlspecialchars($currentUser) ?>
                    </a>
                    <ul class="dropdown-menu" style="right: 0; left: auto;">
                        <li><a class="dropdown-item" href="/admin">
                            <i class="bi bi-speedometer2 me-2"></i>Dashboard
                        </a></li>
                        <li><a class="dropdown-item" href="/">
                            <i class="bi bi-house me-2"></i>Zur Website
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/admin?action=logout">
                            <i class="bi bi-box-arrow-right me-2"></i>Abmelden
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 admin-sidebar">
                <div class="p-3">
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?= ($_GET['action'] ?? 'dashboard') === 'dashboard' ? 'active' : '' ?>" 
                               href="/admin">
                                <i class="bi bi-speedometer2 me-2"></i>
                                <?= __('admin.common.dashboard') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($_GET['action'] ?? '') === 'files' ? 'active' : '' ?>" 
                               href="/admin?action=files">
                                <i class="bi bi-folder me-2"></i>
                                <?= __('admin.common.files') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($_GET['action'] ?? '') === 'new' ? 'active' : '' ?>" 
                               href="/admin?action=new">
                                <i class="bi bi-file-earmark-plus me-2"></i>
                                <?= __('admin.common.new_page') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($_GET['action'] ?? '') === 'edit' ? 'active' : '' ?>" 
                               href="/admin?action=edit">
                                <i class="bi bi-pencil me-2"></i>
                                <?= __('admin.common.editor') ?>
                            </a>
                        </li>
                        <hr class="my-3">
                        <li class="nav-item">
                            <a class="nav-link <?= ($_GET['action'] ?? '') === 'settings' ? 'active' : '' ?>" 
                               href="/admin?action=settings">
                                <i class="bi bi-gear me-2"></i>
                                <?= __('admin.common.settings') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/">
                                <i class="bi bi-eye me-2"></i>
                                <?= __('admin.common.view_site') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin?action=logout">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                <?= __('admin.common.logout') ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            <!-- Hauptinhalt -->
            <main class="col-md-9 col-lg-10 admin-content">
                <div class="card files-container">
                    <div class="card-header">
                            <h4 class="card-title mb-0">
                                <i class="bi bi-folder me-2"></i>
                                <?= __('admin.files.title') ?>
                            </h5>
                                                
                        <div class="d-flex justify-content-end align-items-center mt-2 mb-1">
                            <input type="text" class="form-control search-box" id="searchFiles" 
                                   placeholder="<?= __('admin.files.search_placeholder') ?>" style="width: 250px;">
                            <a href="/admin?action=new&return_url=<?= urlencode('/admin?action=files') ?>" class="btn btn-primary">
                                <i class="bi bi-plus me-1"></i> <?= __('admin.files.new_page') ?>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Bulk Actions -->
                    <div class="bulk-actions" id="bulkActions">
                        <div class="d-flex justify-content-between align-items-center">
                            <span id="selectedCount">0 <?= __('admin.files.selected_count') ?></span>
                            <div>
                                <button class="btn btn-outline-danger btn-sm" id="bulkDelete">
                                    <i class="bi bi-trash me-1"></i> <?= __('admin.files.delete_selected') ?>
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" id="deselectAll">
                                    <i class="bi bi-x-circle me-1"></i> Auswahl aufheben
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status Messages -->
                    <?php if (isset($_GET['message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i>
                            <?php
                            switch ($_GET['message']) {
                                case 'deleted':
                                    echo \StaticMD\Core\I18n::t('admin.files.file_deleted_success');
                                    break;
                                case 'saved':
                                    echo 'Datei wurde erfolgreich gespeichert.';
                                    break;
                                default:
                                    echo htmlspecialchars($_GET['message']);
                            }
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <?php
                            switch ($_GET['error']) {
                                case 'csrf_invalid':
                                    echo __('admin.errors.csrf_invalid');
                                    break;
                                case 'no_file':
                                    echo __('admin.files.no_file_specified');
                                    break;
                                case 'invalid_file':
                                    echo __('admin.files.invalid_filename');
                                    break;
                                case 'file_not_found':
                                    echo __('admin.errors.file_not_found');
                                    break;
                                case 'no_permission':
                                    echo __('admin.files.no_delete_permission');
                                    break;
                                case 'delete_failed':
                                    echo __('admin.errors.delete_failed');
                                    break;
                                default:
                                    echo htmlspecialchars($_GET['error']);
                            }
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['saved'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i>
                            <?= __('admin.files.changes_saved') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <?php if (!empty($fileTree)): ?>
                            <div class="file-tree">
                                <?php 
                                function renderFileTreeNode($name, $item, $level = 0) {
                                    $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
                                    
                                    if ($item['type'] === 'folder') {
                                        echo '<div class="folder-node mb-2" style="margin-left: ' . ($level * 20) . 'px;">';
                                        echo '<div class="folder-header p-2 bg-light rounded d-flex align-items-center" data-bs-toggle="collapse" data-bs-target="#folder_' . md5($item['path']) . '" style="cursor: pointer;">';
                                        echo '<i class="bi bi-folder-fill me-2 text-primary"></i>';
                                        echo '<strong>' . htmlspecialchars($name) . '</strong>';
                                        
                                        // Zeige Anzahl der Kinder und optional Index-Datei Info
                                        $childCount = count($item['children']);
                                        $hasIndexFile = isset($item['index_file']);
                                        if ($hasIndexFile) {
                                            echo '<span class="badge bg-success ms-2" title="Ordner mit Index-Datei">' . $childCount . ' + index</span>';
                                        } else {
                                            echo '<span class="badge bg-secondary ms-2">' . $childCount . '</span>';
                                        }
                                        echo '<i class="bi bi-chevron-down ms-auto"></i>';
                                        echo '</div>';
                                        
                                        echo '<div class="collapse" id="folder_' . md5($item['path']) . '">';
                                        
                                        // Zeige Index-Datei zuerst, falls vorhanden
                                        if ($hasIndexFile) {
                                            $indexFile = $item['index_file'];
                                            echo '<div class="file-item p-2 d-flex align-items-center border-bottom bg-light bg-opacity-50" style="margin-left: ' . (($level + 1) * 20) . 'px;" data-filename="' . htmlspecialchars($indexFile['route']) . '">';
                                            echo '<div class="form-check me-3">';
                                            echo '<input class="form-check-input file-checkbox" type="checkbox" value="' . htmlspecialchars($indexFile['route']) . '" id="file_' . md5($indexFile['route']) . '">';
                                            echo '</div>';
                                            echo '<div class="file-icon bg-success bg-opacity-10 text-success me-3 rounded p-1">';
                                            echo '<i class="bi bi-house-fill" title="Index-Datei"></i>';
                                            echo '</div>';
                                            echo '<div class="flex-grow-1">';
                                            echo '<div class="d-flex justify-content-between align-items-center">';
                                            echo '<div>';
                                            echo '<h6 class="mb-1">';
                                            echo '<a href="/admin?action=edit&file=' . urlencode($indexFile['route']) . '&return_url=' . urlencode('/admin?action=files') . '" class="text-decoration-none">';
                                            echo htmlspecialchars(basename($indexFile['route']) . '.md');
                                            echo '</a>';
                                            echo ' <small class="text-muted">(Index)</small>';
                                            echo '</h6>';
                                            echo '<small class="text-muted">Route: <code>/' . htmlspecialchars($indexFile['route']) . '</code></small>';
                                            echo '<br><small class="text-muted"><i class="bi bi-clock me-1"></i>' . \StaticMD\Core\I18n::t('admin.files.modified') . ': ' . date('d.m.Y H:i', $indexFile['modified']) . '</small>';
                                            echo '</div>';
                                            echo '<div class="file-actions">';
                                            echo '<div class="btn-group btn-group-sm" role="group">';
                                            echo '<a href="/' . encodeUrlPath($indexFile['route']) . '" class="btn btn-outline-info" target="_blank" title="' . \StaticMD\Core\I18n::t('admin.files.view_tooltip') . '"><i class="bi bi-eye"></i></a>';
                                            echo '<a href="/admin?action=edit&file=' . urlencode($indexFile['route']) . '&return_url=' . urlencode('/admin?action=files') . '" class="btn btn-outline-primary" title="' . \StaticMD\Core\I18n::t('admin.files.edit_tooltip') . '"><i class="bi bi-pencil"></i></a>';
                                            echo '<button class="btn btn-outline-danger" onclick="confirmDelete(\'' . htmlspecialchars($indexFile['route']) . '\')" title="' . \StaticMD\Core\I18n::t('admin.files.delete_tooltip') . '"><i class="bi bi-trash"></i></button>';
                                            echo '</div>';
                                            echo '</div>';
                                            echo '</div>';
                                            echo '</div>';
                                            echo '</div>';
                                        }
                                        
                                        // Dann alle anderen Kinder
                                        foreach ($item['children'] as $childName => $child) {
                                            renderFileTreeNode($childName, $child, $level + 1);
                                        }
                                        echo '</div>';
                                        echo '</div>';
                                    } else {
                                        // File
                                        $file = $item['file_data'];
                                        echo '<div class="file-item p-2 d-flex align-items-center border-bottom" style="margin-left: ' . ($level * 20) . 'px;" data-filename="' . htmlspecialchars($file['route']) . '">';
                                        echo '<div class="form-check me-3">';
                                        echo '<input class="form-check-input file-checkbox" type="checkbox" value="' . htmlspecialchars($file['route']) . '" id="file_' . md5($file['route']) . '">';
                                        echo '</div>';
                                        echo '<div class="file-icon bg-primary bg-opacity-10 text-primary me-3 rounded p-1">';
                                        echo '<i class="bi bi-file-earmark-text"></i>';
                                        echo '</div>';
                                        echo '<div class="flex-grow-1">';
                                        echo '<div class="d-flex justify-content-between align-items-center">';
                                        echo '<div>';
                                        echo '<h6 class="mb-1">';
                                        echo '<a href="/admin?action=edit&file=' . urlencode($file['route']) . '&return_url=' . urlencode('/admin?action=files') . '" class="text-decoration-none">';
                                        echo htmlspecialchars($name . '.md');
                                        echo '</a>';
                                        echo '</h6>';
                                        echo '<small class="text-muted">Route: <code>/' . htmlspecialchars($file['route']) . '</code></small>';
                                        echo '<br><small class="text-muted"><i class="bi bi-clock me-1"></i>' . \StaticMD\Core\I18n::t('admin.files.modified') . ': ' . date('d.m.Y H:i', $file['modified']) . '</small>';
                                        echo '</div>';
                                        echo '<div class="file-actions">';
                                        echo '<div class="btn-group btn-group-sm" role="group">';
                                        echo '<a href="/' . encodeUrlPath($file['route']) . '" class="btn btn-outline-info" target="_blank" title="' . \StaticMD\Core\I18n::t('admin.files.view_tooltip') . '"><i class="bi bi-eye"></i></a>';
                                        echo '<a href="/admin?action=edit&file=' . urlencode($file['route']) . '&return_url=' . urlencode('/admin?action=files') . '" class="btn btn-outline-primary" title="' . \StaticMD\Core\I18n::t('admin.files.edit_tooltip') . '"><i class="bi bi-pencil"></i></a>';
                                        echo '<button class="btn btn-outline-danger" onclick="confirmDelete(\'' . htmlspecialchars($file['route']) . '\')" title="' . \StaticMD\Core\I18n::t('admin.files.delete_tooltip') . '"><i class="bi bi-trash"></i></button>';
                                        echo '</div>';
                                        echo '</div>';
                                        echo '</div>';
                                        echo '</div>';
                                        echo '</div>';
                                    }
                                }
                                
                                foreach ($fileTree as $name => $item) {
                                    renderFileTreeNode($name, $item);
                                }
                                ?>
                            </div>
                        <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-folder-x display-1 text-muted mb-3"></i>
                            <h5>Keine Dateien vorhanden</h5>
                            <p class="text-muted">Erstellen Sie Ihre erste Seite, um zu beginnen.</p>
                            <a href="/admin?action=new&return_url=<?= urlencode('/admin?action=files') ?>" class="btn btn-primary">
                                <i class="bi bi-plus me-1"></i> Erste Seite erstellen
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lösch-Bestätigung Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                        <?= __('admin.files.delete_modal_title') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="deleteModalQuestion"><?= __('admin.files.delete_modal_question') ?></p>
                    <p class="text-muted"><?= __('admin.files.delete_modal_warning') ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('admin.common.cancel') ?></button>
                    <a href="#" class="btn btn-danger" id="deleteConfirmBtn">
                        <i class="bi bi-trash me-1"></i> <?= __('admin.files.delete_modal_confirm') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script nonce="<?= $nonce ?>">
        let timeRemaining = <?= $timeRemaining ?>;
        let selectedFiles = new Set();
        
        // Session-Timer
        function updateTimer() {
            if (timeRemaining <= 0) {
                alert('Ihre Session ist abgelaufen. Sie werden zur Login-Seite weitergeleitet.');
                window.location.href = '/admin?action=login';
                return;
            }
            
            const hours = Math.floor(timeRemaining / 3600);
            const minutes = Math.floor((timeRemaining % 3600) / 60);
            const seconds = timeRemaining % 60;
            
            const timerElement = document.getElementById('timer');
            if (timerElement) {
                timerElement.textContent = 
                    String(hours).padStart(2, '0') + ':' +
                    String(minutes).padStart(2, '0') + ':' +
                    String(seconds).padStart(2, '0');
            }
            
            timeRemaining--;
        }
        setInterval(updateTimer, 1000);
        
        // Datei-Suche
        document.getElementById('searchFiles').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const fileItems = document.querySelectorAll('.file-item');
            const folderSections = document.querySelectorAll('.folder-section');
            
            fileItems.forEach(item => {
                const fileName = item.getAttribute('data-filename').toLowerCase();
                const visible = fileName.includes(searchTerm);
                item.style.display = visible ? 'flex' : 'none';
            });
            
            // Ordner ausblenden wenn alle Dateien ausgeblendet sind
            folderSections.forEach(section => {
                const visibleFiles = section.querySelectorAll('.file-item[style*="flex"]');
                const allFiles = section.querySelectorAll('.file-item');
                const hasVisibleFiles = searchTerm === '' || visibleFiles.length > 0;
                section.style.display = hasVisibleFiles ? 'block' : 'none';
            });
        });
        
        // Datei-Auswahl
        document.querySelectorAll('.file-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const fileName = this.value;
                
                if (this.checked) {
                    selectedFiles.add(fileName);
                } else {
                    selectedFiles.delete(fileName);
                }
                
                updateBulkActions();
            });
        });
        
        function updateBulkActions() {
            const count = selectedFiles.size;
            const bulkActions = document.getElementById('bulkActions');
            const selectedCount = document.getElementById('selectedCount');
            
            if (count > 0) {
                bulkActions.classList.add('show');
                selectedCount.textContent = `${count} <?= __('admin.files.selected_count') ?>`;
            } else {
                bulkActions.classList.remove('show');
            }
        }
        
        // Auswahl aufheben
        document.getElementById('deselectAll').addEventListener('click', function() {
            document.querySelectorAll('.file-checkbox:checked').forEach(checkbox => {
                checkbox.checked = false;
            });
            selectedFiles.clear();
            updateBulkActions();
        });
        
        // Bulk-Delete
        document.getElementById('bulkDelete').addEventListener('click', function() {
            if (selectedFiles.size === 0) return;
            
            const fileNames = Array.from(selectedFiles).join(', ');
            
            if (confirm(`<?= __('admin.files.bulk_delete_confirm') ?>`.replace('{count}', selectedFiles.size) + `\n\n${fileNames}`)) {
                // Hier würde die Bulk-Delete-Funktionalität implementiert
                alert('<?php echo \StaticMD\Core\I18n::t('common.bulk_delete_placeholder'); ?>');
            }
        });
        
        // JavaScript-Übersetzungen
        const deleteModalQuestionTemplate = '<?= __('admin.files.delete_modal_question') ?>';
        
        // Einzelne Datei löschen
        function confirmDelete(fileName) {
            // Update modal question with filename
            const questionText = deleteModalQuestionTemplate.replace('{filename}', `<strong>${fileName}</strong>`);
            document.getElementById('deleteModalQuestion').innerHTML = questionText;
            
            // Einfache, direkte URL-Konstruktion ohne komplexe Kodierung
            const deleteUrl = `/admin?action=delete&file=${encodeURIComponent(fileName)}&token=<?= htmlspecialchars($this->auth->generateCSRFToken()) ?>&return_url=/admin%3Faction%3Dfiles`;
            
            console.log('Delete URL:', deleteUrl); // Debug output
            console.log('Expected return to: /admin?action=files');
            
            document.getElementById('deleteConfirmBtn').href = deleteUrl;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
        
        // Keyboard Shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl+A: Alle auswählen
            if (e.ctrlKey && e.key === 'a' && e.target.tagName !== 'INPUT') {
                e.preventDefault();
                document.querySelectorAll('.file-checkbox').forEach(checkbox => {
                    checkbox.checked = true;
                    selectedFiles.add(checkbox.value);
                });
                updateBulkActions();
            }
            
            // Escape: Auswahl aufheben
            if (e.key === 'Escape') {
                document.getElementById('deselectAll').click();
            }
            
            // Ctrl+N: Neue Datei
            if (e.ctrlKey && e.key === 'n') {
                e.preventDefault();
                window.location.href = '/admin?action=new';
            }
        });
        
        // Fokus auf Suchfeld bei Seitenladen
        document.getElementById('searchFiles').focus();
    </script>
</body>
</html>