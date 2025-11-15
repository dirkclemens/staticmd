<?php
// Security Headers setzen
require_once __DIR__ . '/../../core/SecurityHeaders.php';
use StaticMD\Core\SecurityHeaders;
SecurityHeaders::setAllSecurityHeaders('admin');
$nonce = SecurityHeaders::getNonce();

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
<html lang="<?= htmlspecialchars(\StaticMD\Core\I18n::getLanguage()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('admin.brand') ?> - <?= htmlspecialchars($pageTitle) ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <link rel="stylesheet" href="/public/admin.css">
</head>
<body>
    <!-- Admin Header -->
    <nav class="navbar admin-header navbar-dark">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="bi bi-shield-lock me-2"></i>
                <?= __('admin.brand') ?>
            </span>
            
            <div class="d-flex align-items-center text-white">
                <div class="me-3">
                    <small class="session-timer">
                        <i class="bi bi-clock me-1"></i>
                        <?= __('admin.common.session') ?>: <span id="timer"><?= gmdate('H:i:s', $timeRemaining) ?></span>
                    </small>
                </div>
                
                <div class="dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>
                        <?= htmlspecialchars($currentUser) ?>
                    </a>
                    <ul class="dropdown-menu" style="right: 0; left: auto;">
                        <li><a class="dropdown-item" href="/">
                            <i class="bi bi-house me-2"></i><?= __('admin.common.view_site') ?>
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/admin?action=logout">
                            <i class="bi bi-box-arrow-right me-2"></i><?= __('admin.common.logout') ?>
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
                <?php if (isset($_GET['message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <?php
                    switch ($_GET['message']) {
                        case 'saved': echo __('admin.alerts.saved'); break;
                        case 'deleted': echo __('admin.alerts.deleted'); break;
                        default: echo __('admin.alerts.success');
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
                        case 'save_failed': echo __('admin.errors.save_failed'); break;
                        case 'delete_failed': echo __('admin.errors.delete_failed'); break;
                        case 'no_file': echo __('admin.errors.no_file'); break;
                        case 'file_not_found': echo __('admin.errors.file_not_found'); break;
                        case 'no_permission': echo __('admin.errors.no_permission'); break;
                        case 'invalid_file': echo __('admin.errors.invalid_file'); break;
                        case 'csrf_invalid': echo __('admin.errors.csrf_invalid'); break;
                        case 'invalid_request': echo __('admin.errors.invalid_request'); break;
                        default: echo __('admin.errors.generic');
                    }
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Dashboard Inhalt -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">
                        <i class="bi bi-speedometer2 me-2 text-primary"></i>
                        <?= __('admin.common.dashboard') ?>
                    </h1>
                    <div class="btn-group" role="group">
                        <a href="/admin?action=new" class="btn btn-primary">
                            <i class="bi bi-plus me-1"></i> <?= __('admin.dashboard.buttons.new_page') ?>
                        </a>
                        <a href="/admin?action=files" class="btn btn-outline-primary">
                            <i class="bi bi-folder me-1"></i> <?= __('admin.dashboard.buttons.files') ?>
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
                                        <p class="card-text text-muted small mb-0"><?= __('admin.dashboard.stats.total_pages') ?></p>
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
                                        <p class="card-text text-muted small mb-0"><?= __('admin.dashboard.stats.disk_usage') ?></p>
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
                                        <i class="bi bi-folder"></i>
                                    </div>
                                    <div>
                                        <h5 class="card-title mb-1"><?= $stats['public_size'] ?></h5>
                                        <p class="card-text text-muted small mb-0"><?= __('admin.dashboard.stats.public_size') ?></p>
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
                                        <p class="card-text text-muted small mb-0"><?= __('admin.dashboard.stats.php_version') ?></p>
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
                                    <?= __('admin.dashboard.recent') ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($stats['recent_files'])): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th><?= __('admin.dashboard.columns.file') ?></th>
                                                <th><?= __('admin.dashboard.columns.route') ?></th>
                                                <th><?= __('admin.dashboard.columns.modified') ?></th>
                                                <th width="120"><?= __('admin.dashboard.columns.actions') ?></th>
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
                                                       class="btn btn-sm btn-outline-primary" title="<?= __('admin.dashboard.buttons.edit') ?>">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="/<?= encodeUrlPath($file['route']) ?>" 
                                                       class="btn btn-sm btn-outline-info" target="_blank" title="<?= __('admin.dashboard.buttons.view') ?>">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="confirmDelete('<?= htmlspecialchars($file['route']) ?>', '<?= htmlspecialchars(basename($file['file'])) ?>')" 
                                                            title="<?= __('admin.dashboard.buttons.delete') ?>">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <p class="text-muted mb-0"><?= __('admin.dashboard.empty') ?></p>
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
                        <?= __('admin.delete_modal.title') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3"><?= __('admin.delete_modal.question') ?></p>
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong id="deleteFileName"></strong><br>
                        <small><?= __('admin.delete_modal.warning') ?></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> <?= __('admin.delete_modal.cancel') ?>
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="bi bi-trash me-1"></i> <?= __('admin.delete_modal.confirm') ?>
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
    
    <script nonce="<?= $nonce ?>">
        // Session-Timer
        let timeRemaining = <?= $timeRemaining ?>;
        
        function updateTimer() {
            if (timeRemaining <= 0) {
                alert('<?= __('admin.session.expired_alert') ?>');
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