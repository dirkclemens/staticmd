<?php
$pageTitle = 'Datei-Manager';
$currentUser = $this->auth->getUsername();
$timeRemaining = $this->auth->getTimeRemaining();

// Helper-Funktion für pfad-sichere URL-Encoding
function encodeUrlPath($path) {
    $parts = explode('/', $path);
    $encodedParts = array_map('rawurlencode', $parts);
    return implode('/', $encodedParts);
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StaticMD Admin - Dateien</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body { background-color: #f8f9fa; }
        .admin-header {
            background: linear-gradient(90deg, #0d6efd, #0a58ca);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .files-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
        }
        
        .file-item {
            transition: all 0.2s ease;
            border: none;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .file-item:hover {
            background-color: #f8f9fa;
        }
        
        .file-icon {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            font-size: 1.2rem;
        }
        
        .file-actions {
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        
        .file-item:hover .file-actions {
            opacity: 1;
        }
        
        .session-timer {
            font-size: 0.85rem;
            color: #ffc107;
        }
        
        .folder-header {
            background: linear-gradient(45deg, #e9ecef, #dee2e6);
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }
        
        .search-box {
            border-radius: 25px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .search-box:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        
        .bulk-actions {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            display: none;
        }
        
        .bulk-actions.show {
            display: block;
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <nav class="navbar admin-header navbar-dark">
        <div class="container-fluid">
            <a href="/admin" class="navbar-brand mb-0 h1 text-decoration-none">
                <i class="bi bi-shield-lock me-2"></i>
                StaticMD Admin
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
                    <ul class="dropdown-menu">
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

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="files-container">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">
                                <i class="bi bi-folder me-2"></i>
                                Datei-Manager
                            </h5>
                            <small class="text-muted">Verwalten Sie alle Ihre Markdown-Dateien</small>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <input type="text" class="form-control search-box" id="searchFiles" 
                                   placeholder="Dateien durchsuchen..." style="width: 250px;">
                            <a href="/admin?action=new" class="btn btn-primary">
                                <i class="bi bi-plus me-1"></i> Neue Seite
                            </a>
                        </div>
                    </div>
                    
                    <!-- Bulk Actions -->
                    <div class="bulk-actions" id="bulkActions">
                        <div class="d-flex justify-content-between align-items-center">
                            <span id="selectedCount">0 Dateien ausgewählt</span>
                            <div>
                                <button class="btn btn-outline-danger btn-sm" id="bulkDelete">
                                    <i class="bi bi-trash me-1"></i> Ausgewählte löschen
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" id="deselectAll">
                                    <i class="bi bi-x-circle me-1"></i> Auswahl aufheben
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body p-0">
                        <?php if (!empty($filesByDir)): ?>
                            <?php foreach ($filesByDir as $dirName => $files): ?>
                            <div class="folder-section mb-4" data-folder="<?= htmlspecialchars($dirName) ?>">
                                <div class="folder-header p-3">
                                    <h6 class="mb-0">
                                        <i class="bi bi-folder-fill me-2 text-primary"></i>
                                        <?= htmlspecialchars($dirName) ?>
                                        <span class="badge bg-secondary ms-2"><?= count($files) ?></span>
                                    </h6>
                                </div>
                                
                                <div class="files-list">
                                    <?php foreach ($files as $file): ?>
                                    <div class="file-item p-3 d-flex align-items-center" data-filename="<?= htmlspecialchars($file['route']) ?>">
                                        <div class="form-check me-3">
                                            <input class="form-check-input file-checkbox" type="checkbox" 
                                                   value="<?= htmlspecialchars($file['route']) ?>" 
                                                   id="file_<?= md5($file['route']) ?>">
                                        </div>
                                        
                                        <div class="file-icon bg-primary bg-opacity-10 text-primary me-3">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </div>
                                        
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1">
                                                        <a href="/admin?action=edit&file=<?= urlencode($file['route']) ?>" 
                                                           class="text-decoration-none">
                                                            <?= htmlspecialchars(basename($file['file'])) ?>
                                                        </a>
                                                    </h6>
                                                    <small class="text-muted">
                                                        Route: <code>/<?= htmlspecialchars($file['route']) ?></code>
                                                    </small>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="bi bi-clock me-1"></i>
                                                        Geändert: <?= date('d.m.Y H:i', $file['modified']) ?>
                                                    </small>
                                                </div>
                                                
                                                <div class="file-actions">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="/<?= encodeUrlPath($file['route']) ?>" 
                                                           class="btn btn-outline-info" target="_blank" title="Ansehen">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <a href="/admin?action=edit&file=<?= urlencode($file['route']) ?>" 
                                                           class="btn btn-outline-primary" title="Bearbeiten">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <button class="btn btn-outline-danger" 
                                                                onclick="confirmDelete('<?= htmlspecialchars($file['route']) ?>')" 
                                                                title="Löschen">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-folder-x display-1 text-muted mb-3"></i>
                            <h5>Keine Dateien vorhanden</h5>
                            <p class="text-muted">Erstellen Sie Ihre erste Seite, um zu beginnen.</p>
                            <a href="/admin?action=new" class="btn btn-primary">
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
                        Datei löschen
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Sind Sie sicher, dass Sie die Datei <strong id="deleteFileName"></strong> löschen möchten?</p>
                    <p class="text-muted">Diese Aktion kann nicht rückgängig gemacht werden.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <a href="#" class="btn btn-danger" id="deleteConfirmBtn">
                        <i class="bi bi-trash me-1"></i> Löschen
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
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
                selectedCount.textContent = `${count} Datei${count > 1 ? 'en' : ''} ausgewählt`;
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
            
            if (confirm(`Sind Sie sicher, dass Sie ${selectedFiles.size} Datei(en) löschen möchten?\n\n${fileNames}`)) {
                // Hier würde die Bulk-Delete-Funktionalität implementiert
                alert('Bulk-Delete-Funktionalität würde hier implementiert werden.');
            }
        });
        
        // Einzelne Datei löschen
        function confirmDelete(fileName) {
            document.getElementById('deleteFileName').textContent = fileName;
            document.getElementById('deleteConfirmBtn').href = 
                `/admin?action=delete&file=${encodeURIComponent(fileName)}&token=<?= htmlspecialchars($this->auth->generateCSRFToken()) ?>`;
            
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