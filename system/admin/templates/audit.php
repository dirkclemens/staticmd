<?php
require_once __DIR__ . '/../../core/SecurityHeaders.php';
use StaticMD\Core\SecurityHeaders;
SecurityHeaders::setAllSecurityHeaders('admin');
$nonce = SecurityHeaders::getNonce();

$currentUser   = $this->auth->getUsername();
$timeRemaining = $this->auth->getTimeRemaining();

$actionMeta = [
    'login'         => ['badge' => 'success'],
    'login_failed'  => ['badge' => 'danger'],
    'logout'        => ['badge' => 'secondary'],
    'save_file'     => ['badge' => 'primary'],
    'delete_file'   => ['badge' => 'danger'],
    'rename_file'   => ['badge' => 'warning'],
    'upload_file'   => ['badge' => 'info'],
    'upload_image'  => ['badge' => 'info'],
    'create_backup' => ['badge' => 'success'],
    'save_settings' => ['badge' => 'primary'],
];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(\StaticMD\Core\I18n::getLanguage()) ?>" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('admin.brand') ?> - <?= __('admin.common.audit_log') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/system/admin/admin.css">
</head>
<body>
    <!-- Admin Header -->
    <nav class="navbar admin-header navbar-expand-lg">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="bi bi-shield-lock me-2"></i>
                <?= __('admin.brand') ?>
            </span>
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <small class="session-timer">
                        <i class="bi bi-clock me-1"></i>
                        <?= __('admin.common.session') ?>: <span id="timer"><?= gmdate('H:i:s', $timeRemaining) ?></span>
                    </small>
                </div>
                <button id="theme-toggle" class="btn btn-link me-3" title="<?= \StaticMD\Core\I18n::t('core.theme_toggle') ?>">
                    <i class="bi bi-moon-fill" id="theme-icon"></i>
                </button>
                <div class="dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>
                        <?= htmlspecialchars($currentUser) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/admin">
                            <i class="bi bi-speedometer2 me-2"></i><?= __('admin.common.dashboard') ?>
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="/admin?action=logout">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($this->auth->generateCSRFToken()) ?>">
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-box-arrow-right me-2"></i><?= __('admin.common.logout') ?>
                                </button>
                            </form>
                        </li>
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
                            <a class="nav-link" href="/admin">
                                <i class="bi bi-speedometer2 me-2"></i>
                                <?= __('admin.common.dashboard') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin?action=files">
                                <i class="bi bi-folder me-2"></i>
                                <?= __('admin.common.files') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin?action=new">
                                <i class="bi bi-file-earmark-plus me-2"></i>
                                <?= __('admin.common.new_page') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin?action=edit">
                                <i class="bi bi-pencil me-2"></i>
                                <?= __('admin.common.editor') ?>
                            </a>
                        </li>
                        <hr class="my-3">
                        <li class="nav-item">
                            <a class="nav-link" href="/admin?action=settings">
                                <i class="bi bi-gear me-2"></i>
                                <?= __('admin.common.settings') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="/admin?action=audit_log">
                                <i class="bi bi-journal-text me-2"></i>
                                <?= __('admin.common.audit_log') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($_SESSION['last_frontend_url'] ?? '/') ?>">
                                <i class="bi bi-eye me-2"></i>
                                <?= __('admin.common.view_site') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="/admin?action=logout">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($this->auth->generateCSRFToken()) ?>">
                                <button type="submit" class="nav-link btn btn-link w-100 text-start">
                                    <i class="bi bi-box-arrow-right me-2"></i>
                                    <?= __('admin.common.logout') ?>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 col-lg-10 admin-content">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h4 class="card-title mb-0">
                            <i class="bi bi-journal-text me-2"></i>
                            <?= __('admin.common.audit_log') ?>
                        </h4>
                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            <select id="filterAction" class="form-select form-select-sm" style="width: auto;">
                                <option value=""><?= __('admin.audit.filter_all') ?></option>
                                <?php foreach (array_keys($actionMeta) as $key): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($key) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" id="filterUser" class="form-control form-control-sm"
                                   placeholder="<?= __('admin.audit.filter_user') ?>" style="width: 120px;">
                            <small class="text-muted"><?= count($auditEntries) ?> <?= __('admin.audit.entries') ?></small>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($auditEntries)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-journal display-4 mb-3 d-block"></i>
                            <p><?= __('admin.audit.no_entries') ?></p>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0" id="auditTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:170px"><?= __('admin.audit.col_time') ?></th>
                                        <th style="width:140px"><?= __('admin.audit.col_action') ?></th>
                                        <th style="width:100px"><?= __('admin.audit.col_user') ?></th>
                                        <th style="width:120px"><?= __('admin.audit.col_ip') ?></th>
                                        <th><?= __('admin.audit.col_details') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($auditEntries as $entry):
                                    $action  = $entry['action'] ?? '';
                                    $badge   = $actionMeta[$action]['badge'] ?? 'secondary';
                                    $details = $entry['details'] ?? [];
                                    $detailParts = [];
                                    foreach ($details as $k => $v) {
                                        $detailParts[] = '<code>' . htmlspecialchars($k) . '</code>: ' . htmlspecialchars((string)$v);
                                    }
                                    $ts = $entry['ts'] ?? '';
                                    try {
                                        $dt = new \DateTime($ts);
                                        $tsDisplay = $dt->format('d.m.Y H:i:s');
                                    } catch (\Exception $e) {
                                        $tsDisplay = htmlspecialchars($ts);
                                    }
                                ?>
                                <tr data-action="<?= htmlspecialchars($action) ?>"
                                    data-user="<?= htmlspecialchars(strtolower($entry['user'] ?? '')) ?>">
                                    <td class="text-muted small font-monospace"><?= $tsDisplay ?></td>
                                    <td>
                                        <span class="badge bg-<?= htmlspecialchars($badge) ?>">
                                            <?= htmlspecialchars($action) ?>
                                        </span>
                                    </td>
                                    <td class="small"><?= htmlspecialchars($entry['user'] ?? '') ?></td>
                                    <td class="small font-monospace text-muted"><?= htmlspecialchars($entry['ip'] ?? '') ?></td>
                                    <td class="small"><?= implode(', ', $detailParts) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script nonce="<?= $nonce ?>">
        // Session countdown
        let timeRemaining = <?= (int)$timeRemaining ?>;
        function updateTimer() {
            if (timeRemaining <= 0) { window.location.href = '/admin?action=login'; return; }
            const h = Math.floor(timeRemaining / 3600);
            const m = Math.floor((timeRemaining % 3600) / 60);
            const s = timeRemaining % 60;
            const el = document.getElementById('timer');
            if (el) el.textContent = String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
            timeRemaining--;
        }
        setInterval(updateTimer, 1000);

        // Row filter
        function applyFilters() {
            const actionFilter = document.getElementById('filterAction').value;
            const userFilter   = document.getElementById('filterUser').value.toLowerCase();
            document.querySelectorAll('#auditTable tbody tr').forEach(row => {
                const ok = (!actionFilter || row.dataset.action === actionFilter)
                        && (!userFilter   || row.dataset.user.includes(userFilter));
                row.style.display = ok ? '' : 'none';
            });
        }
        document.getElementById('filterAction')?.addEventListener('change', applyFilters);
        document.getElementById('filterUser')?.addEventListener('input', applyFilters);

        // Theme toggle
        const html  = document.documentElement;
        const saved = localStorage.getItem('adminTheme') || 'light';
        html.setAttribute('data-bs-theme', saved);
        setIcon(saved);
        document.getElementById('theme-toggle')?.addEventListener('click', () => {
            const next = html.getAttribute('data-bs-theme') === 'light' ? 'dark' : 'light';
            html.setAttribute('data-bs-theme', next);
            localStorage.setItem('adminTheme', next);
            setIcon(next);
        });
        function setIcon(t) {
            const i = document.getElementById('theme-icon');
            if (i) i.className = t === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
        }
    </script>
</body>
</html>
