<?php
// Admin-Layout Header
$pageTitle = $pageTitle ?? 'Dashboard';
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
    <title>StaticMD Admin - <?= htmlspecialchars($pageTitle) ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --admin-primary: #0d6efd;
            --admin-secondary: #6c757d;
            --admin-success: #198754;
            --admin-danger: #dc3545;
            --admin-warning: #ffc107;
        }
        
        body {
            background-color: #f8f9fa;
        }
        
        .admin-header {
            background: linear-gradient(90deg, var(--admin-primary), #0a58ca);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .admin-sidebar {
            background: white;
            box-shadow: 2px 0 4px rgba(0,0,0,0.1);
            height: calc(100vh - 76px);
            overflow-y: auto;
        }
        
        .admin-content {
            padding: 2rem;
        }
        
        .stat-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
            transition: transform 0.2s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .nav-pills .nav-link {
            border-radius: 10px;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .nav-pills .nav-link.active {
            background-color: var(--admin-primary);
        }
        
        .session-timer {
            font-size: 0.85rem;
            color: #ffc107;
        }
        
        .table-actions {
            white-space: nowrap;
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <nav class="navbar admin-header navbar-dark">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="bi bi-shield-lock me-2"></i>
                StaticMD Admin
            </span>
            
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
                                Dashboard
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link <?= ($_GET['action'] ?? '') === 'files' ? 'active' : '' ?>" 
                               href="/admin?action=files">
                                <i class="bi bi-folder me-2"></i>
                                Dateien
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link <?= ($_GET['action'] ?? '') === 'new' ? 'active' : '' ?>" 
                               href="/admin?action=new">
                                <i class="bi bi-file-earmark-plus me-2"></i>
                                Neue Seite
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link <?= ($_GET['action'] ?? '') === 'edit' ? 'active' : '' ?>" 
                               href="/admin?action=edit">
                                <i class="bi bi-pencil me-2"></i>
                                Editor
                            </a>
                        </li>
                        
                        <hr class="my-3">
                        
                        <li class="nav-item">
                            <a class="nav-link <?= ($_GET['action'] ?? '') === 'settings' ? 'active' : '' ?>" 
                               href="/admin?action=settings">
                                <i class="bi bi-gear me-2"></i>
                                Einstellungen
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="/">
                                <i class="bi bi-eye me-2"></i>
                                Website ansehen
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="/admin?action=logout">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                Abmelden
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Hauptinhalt -->
            <main class="col-md-9 col-lg-10 admin-content">
                <?php if (isset($_GET['message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <?php
                    switch ($_GET['message']) {
                        case 'saved': echo 'Datei wurde erfolgreich gespeichert.'; break;
                        case 'deleted': echo 'Datei wurde erfolgreich gelöscht.'; break;
                        default: echo 'Aktion wurde erfolgreich ausgeführt.';
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
                        case 'save_failed': echo 'Fehler beim Speichern der Datei.'; break;
                        case 'delete_failed': echo 'Fehler beim Löschen der Datei.'; break;
                        case 'no_file': echo 'Keine Datei angegeben.'; break;
                        case 'file_not_found': echo 'Datei wurde nicht gefunden.'; break;
                        case 'no_permission': echo 'Keine Berechtigung zum Löschen.'; break;
                        case 'invalid_file': echo 'Ungültiger Dateiname.'; break;
                        case 'csrf_invalid': echo 'Sicherheitstoken ungültig.'; break;
                        case 'invalid_request': echo 'Ungültige Anfrage.'; break;
                        default: echo 'Ein Fehler ist aufgetreten.';
                    }
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Dashboard Inhalt -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">
                        <i class="bi bi-speedometer2 me-2 text-primary"></i>
                        Dashboard
                    </h1>
                    <div class="btn-group" role="group">
                        <a href="/admin?action=new" class="btn btn-primary">
                            <i class="bi bi-plus me-1"></i> Neue Seite
                        </a>
                        <a href="/admin?action=files" class="btn btn-outline-primary">
                            <i class="bi bi-folder me-1"></i> Dateien
                        </a>
                    </div>
                </div>

                <!-- Statistik-Karten -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                                        <i class="bi bi-file-earmark-text"></i>
                                    </div>
                                    <div>
                                        <h5 class="card-title mb-1"><?= $stats['total_files'] ?></h5>
                                        <p class="card-text text-muted small mb-0">Gesamt Seiten</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                                        <i class="bi bi-hdd"></i>
                                    </div>
                                    <div>
                                        <h5 class="card-title mb-1"><?= $stats['disk_usage'] ?></h5>
                                        <p class="card-text text-muted small mb-0">Speicherplatz</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                                        <i class="bi bi-code-slash"></i>
                                    </div>
                                    <div>
                                        <h5 class="card-title mb-1"><?= $stats['system_info']['php_version'] ?></h5>
                                        <p class="card-text text-muted small mb-0">PHP Version</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                                        <i class="bi bi-memory"></i>
                                    </div>
                                    <div>
                                        <h5 class="card-title mb-1"><?= $stats['system_info']['memory_limit'] ?></h5>
                                        <p class="card-text text-muted small mb-0">Memory Limit</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Neueste Dateien -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-clock-history me-2"></i>
                                    Zuletzt bearbeitet
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($stats['recent_files'])): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Datei</th>
                                                <th>Route</th>
                                                <th>Geändert</th>
                                                <th width="120">Aktionen</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($stats['recent_files'] as $file): ?>
                                            <tr>
                                                <td>
                                                    <i class="bi bi-file-earmark-text me-2 text-primary"></i>
                                                    <?= htmlspecialchars(basename($file['file'])) ?>
                                                </td>
                                                <td>
                                                    <a href="/<?= encodeUrlPath($file['route']) ?>" 
                                                       class="text-decoration-none" target="_blank">
                                                        /<?= htmlspecialchars($file['route']) ?>
                                                        <i class="bi bi-box-arrow-up-right ms-1"></i>
                                                    </a>
                                                </td>
                                                <td class="text-muted">
                                                    <?= date('d.m.Y H:i', $file['modified']) ?>
                                                </td>
                                                <td class="table-actions">
                                                    <a href="/admin?action=edit&file=<?= urlencode($file['route']) ?>" 
                                                       class="btn btn-sm btn-outline-primary" title="Bearbeiten">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="/<?= encodeUrlPath($file['route']) ?>" 
                                                       class="btn btn-sm btn-outline-info" target="_blank" title="Ansehen">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="confirmDelete('<?= htmlspecialchars($file['route']) ?>', '<?= htmlspecialchars(basename($file['file'])) ?>')" 
                                                            title="Löschen">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <p class="text-muted mb-0">Noch keine Dateien vorhanden.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bestätigungs-Modal für Löschung -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle text-danger me-2"></i>
                        Datei löschen
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Sind Sie sicher, dass Sie diese Datei löschen möchten?</p>
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong id="deleteFileName"></strong><br>
                        <small>Diese Aktion kann nicht rückgängig gemacht werden!</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Abbrechen
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="bi bi-trash me-1"></i> Ja, löschen
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Verstecktes Form für Löschung -->
    <form id="deleteForm" method="POST" action="/admin?action=delete" style="display: none;">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($this->auth->generateCSRFToken()) ?>">
        <input type="hidden" name="file" id="deleteFileInput">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Session-Timer
        let timeRemaining = <?= $timeRemaining ?>;
        
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
        
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(alert => {
                const closeBtn = alert.querySelector('.btn-close');
                if (closeBtn) {
                    closeBtn.click();
                }
            });
        }, 5000);
        
        // Löschbestätigung
        let deleteModal;
        let currentDeleteFile = '';
        
        function confirmDelete(fileRoute, fileName) {
            currentDeleteFile = fileRoute;
            document.getElementById('deleteFileName').textContent = fileName;
            document.getElementById('deleteFileInput').value = fileRoute;
            
            if (!deleteModal) {
                deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            }
            deleteModal.show();
        }
        
        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            if (currentDeleteFile) {
                document.getElementById('deleteForm').submit();
            }
        });
    </script>
</body>
</html>