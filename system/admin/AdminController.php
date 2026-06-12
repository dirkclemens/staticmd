<?php

namespace StaticMD\Admin;

use StaticMD\Core\AuditLog;
use StaticMD\Admin\Controllers\FileController;
use StaticMD\Admin\Controllers\UploadController;
use StaticMD\Admin\Controllers\SettingsController;
use StaticMD\Admin\Controllers\BackupController;
use StaticMD\Admin\Controllers\DashboardController;

/**
 * Admin Controller (thin router)
 *
 * Routes incoming requests to the appropriate sub-controller.
 * Sub-controllers live in system/admin/controllers/.
 */
class AdminController
{
    private array $config;
    private AdminAuth $auth;
    private AuditLog $audit;
    private FileController $fileCtrl;
    private UploadController $uploadCtrl;
    private SettingsController $settingsCtrl;
    private BackupController $backupCtrl;
    private DashboardController $dashCtrl;

    public function getAuth(): AdminAuth
    {
        return $this->auth;
    }

    public function __construct(array $config, AdminAuth $auth)
    {
        $this->config = $config;
        $this->auth   = $auth;

        $storagePath = $config['paths']['storage'] ?? dirname(__DIR__, 2) . '/storage';
        $this->audit = new AuditLog($storagePath);

        $this->settingsCtrl = new SettingsController($config, $auth, $this->audit);
        $this->backupCtrl   = new BackupController($config, $auth, $this->settingsCtrl, $this->audit);
        $this->fileCtrl     = new FileController($config, $auth, $this->audit);
        $this->uploadCtrl   = new UploadController($config, $auth, $this->audit);
        $this->dashCtrl     = new DashboardController($config, $auth, $this->settingsCtrl, $this->audit);
    }

    public function handleRequest(): void
    {
        $action = $_GET['action'] ?? 'dashboard';

        switch ($action) {
            case 'rename':
                $this->fileCtrl->renameFile();
                break;
            case 'upload_file':
                $this->uploadCtrl->handleFileUpload();
                break;
            case 'upload_image':
                $this->uploadCtrl->handleImageUpload();
                break;
            case 'login':
                $this->handleLogin();
                break;
            case 'logout':
                $this->handleLogout();
                break;
            case 'dashboard':
                $this->dashCtrl->showDashboard();
                break;
            case 'edit':
                $this->fileCtrl->showEditor();
                break;
            case 'save':
                $this->fileCtrl->saveContent();
                break;
            case 'new':
                $this->fileCtrl->showNewContentForm();
                break;
            case 'delete':
                $this->fileCtrl->deleteContent();
                break;
            case 'files':
                $this->dashCtrl->showFileManager();
                break;
            case 'settings':
                $this->settingsCtrl->showSettings();
                break;
            case 'save_settings':
                $this->settingsCtrl->saveSettings();
                break;
            case 'create_backup':
                $this->backupCtrl->createBackup();
                break;
            case 'validate_path':
                $this->fileCtrl->validatePath();
                break;
            case 'audit_log':
                $this->dashCtrl->showAuditLog();
                break;
            default:
                http_response_code(400);
                header('Location: /admin?error=invalid_action');
                exit;
        }
    }

    private function handleLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username   = $_POST['username'] ?? '';
            $password   = $_POST['password'] ?? '';
            $csrfToken  = $_POST['csrf_token'] ?? '';
            $rememberMe = isset($_POST['remember_me']) && $_POST['remember_me'] === '1';

            if ($this->auth->verifyCSRFToken($csrfToken) && $this->auth->login($username, $password, $rememberMe)) {
                $this->audit->log('login', $username);
                header('Location: /admin');
                exit;
            } else {
                $this->audit->log('login_failed', $username ?: 'unknown');
                $error = \StaticMD\Core\I18n::t('admin.login.invalid');
            }
        }

        include __DIR__ . '/templates/login.php';
    }

    private function handleLogout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin?error=invalid_request');
            exit;
        }

        $this->auth->requireLogin();

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!$this->auth->verifyCSRFToken($csrfToken)) {
            header('Location: /admin?error=csrf_invalid');
            exit;
        }

        $this->audit->log('logout', $this->auth->getUsername());
        $returnUrl = $_SESSION['last_frontend_url'] ?? '/';
        $this->auth->logout();
        header('Location: ' . $returnUrl);
        exit;
    }
}
