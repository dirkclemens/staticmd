<?php
// Security Headers setzen
require_once __DIR__ . '/../../core/SecurityHeaders.php';
use StaticMD\Core\SecurityHeaders;
SecurityHeaders::setAllSecurityHeaders('admin');
$nonce = SecurityHeaders::getNonce();
$pageTitle = 'Admin Log';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(\StaticMD\Core\I18n::getLanguage()) ?>" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('admin.brand') ?> - <?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/system/admin/admin.css">
</head>
<body>
    <!-- Admin Header -->
    <nav class="navbar admin-header navbar-expand-lg">
        <div class="container-fluid">
            <a href="/admin" class="navbar-brand mb-0 h1">
                <i class="bi bi-shield-lock me-2"></i>
                <?= __('admin.brand') ?>
            </a>
            <div class="d-flex align-items-center">
                <small class="session-timer">
                    <?= __('admin.common.session') ?>: <span id="timer"><?= gmdate('H:i:s', $this->auth->getTimeRemaining()) ?></span>
                </small>
                <!-- Theme Toggle Button -->
                <button id="theme-toggle" class="btn btn-link ms-3" title="<?= \StaticMD\Core\I18n::t('core.theme_toggle') ?>">
                    <i class="bi bi-moon-fill" id="theme-icon"></i>
                </button>
                <div class="ms-3 dropdown">
                    <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($this->auth->getUsername() ?? 'Admin') ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/admin?action=settings">
                            <i class="bi bi-gear me-2"></i><?= __('admin.common.settings') ?>
                        </a></li>
                        <li><a class="dropdown-item" href="<?= htmlspecialchars($_SESSION['last_frontend_url'] ?? '/') ?>">
                            <i class="bi bi-house me-2"></i><?= __('admin.common.view_site') ?>
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
                        <li class="nav-item">
                            <a class="nav-link <?= ($_GET['action'] ?? '') === 'audit' ? 'active' : '' ?>" 
                               href="/admin?action=audit">
                                <i class="bi bi-journal-text me-2"></i>
                                Admin Log
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">
                        <i class="bi bi-journal-text me-2 text-primary"></i>
                        Admin Log
                    </h1>
                    <div class="small text-muted">
                        Showing latest <?= (int)$limit ?> entries
                    </div>
                </div>

                <?php if (!$logExists): ?>
                    <div class="alert alert-info">
                        Log file not found. It will be created on the next admin action.
                    </div>
                <?php elseif (empty($entries)): ?>
                    <div class="alert alert-info">
                        No log entries yet.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Event</th>
                                    <th>User</th>
                                    <th>IP</th>
                                    <th>Context</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($entries as $entry): ?>
                                    <?php
                                        $ts = $entry['ts'] ?? '';
                                        $time = $ts ? date('Y-m-d H:i:s', strtotime($ts)) : '';
                                        $context = $entry['context'] ?? [];
                                        $contextText = $context ? json_encode($context, JSON_UNESCAPED_SLASHES) : '';
                                    ?>
                                    <tr>
                                        <td class="text-nowrap"><?= htmlspecialchars($time) ?></td>
                                        <td><code><?= htmlspecialchars($entry['event'] ?? '') ?></code></td>
                                        <td><?= htmlspecialchars($entry['user'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($entry['ip'] ?? '') ?></td>
                                        <td class="text-muted small"><?= htmlspecialchars($contextText) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script nonce="<?= $nonce ?>">
        let timeRemaining = <?= (int)$this->auth->getTimeRemaining() ?>;
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

        // Theme toggle functionality (shared across all admin pages)
        const themeToggle = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');
        const htmlElement = document.documentElement;
        const savedTheme = localStorage.getItem('adminTheme') || 'light';
        htmlElement.setAttribute('data-bs-theme', savedTheme);
        if (themeToggle && themeIcon) {
            updateThemeIcon(savedTheme);
            themeToggle.addEventListener('click', function() {
                const currentTheme = htmlElement.getAttribute('data-bs-theme');
                const newTheme = currentTheme === 'light' ? 'dark' : 'light';
                htmlElement.setAttribute('data-bs-theme', newTheme);
                localStorage.setItem('adminTheme', newTheme);
                updateThemeIcon(newTheme);
            });
        }
        function updateThemeIcon(theme) {
            if (theme === 'dark') {
                themeIcon.classList.remove('bi-moon-fill');
                themeIcon.classList.add('bi-sun-fill');
            } else {
                themeIcon.classList.remove('bi-sun-fill');
                themeIcon.classList.add('bi-moon-fill');
            }
        }
    </script>
</body>
</html>
