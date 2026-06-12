<?php

namespace StaticMD\Admin\Controllers;

use StaticMD\Admin\AdminAuth;
use StaticMD\Core\AuditLog;

class BackupController
{
    private array $config;
    private AdminAuth $auth;
    private SettingsController $settingsCtrl;
    private AuditLog $audit;

    public function __construct(array $config, AdminAuth $auth, SettingsController $settingsCtrl, AuditLog $audit)
    {
        $this->config      = $config;
        $this->auth        = $auth;
        $this->settingsCtrl = $settingsCtrl;
        $this->audit       = $audit;
    }

    public function createBackup(): void
    {
        $this->auth->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin?action=settings');
            exit;
        }

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!$this->auth->verifyCSRFToken($csrfToken)) {
            header('Location: /admin?action=settings&error=csrf_invalid');
            exit;
        }

        try {
            if (!class_exists('\ZipArchive')) {
                throw new \Exception(\StaticMD\Core\I18n::t('admin.backup.zip_not_available'));
            }

            $timestamp = date('Y-m-d_H-i-s');
            $backupFilename = "staticmd_backup_{$timestamp}.zip";
            $backupPath = sys_get_temp_dir() . '/' . $backupFilename;

            $zip = new \ZipArchive();
            if ($zip->open($backupPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
                throw new \Exception(\StaticMD\Core\I18n::t('admin.backup.create_failed'));
            }

            $this->addDirectoryToZip($zip, $this->config['paths']['content'], 'content/');

            $systemPath = $this->config['paths']['system'];
            if (file_exists($systemPath . '/settings.json')) {
                $zip->addFile($systemPath . '/settings.json', 'system/settings.json');
            }

            $this->addConfigToZip($zip);
            $this->addDirectoryToZip($zip, $this->config['paths']['themes'], 'system/themes/');

            $publicPath = $this->config['paths']['public'];
            if (is_dir($publicPath . '/images')) {
                $this->addDirectoryToZip($zip, $publicPath . '/images', 'public/images/');
            }
            if (is_dir($publicPath . '/downloads')) {
                $this->addDirectoryToZip($zip, $publicPath . '/downloads', 'public/downloads/');
            }
            if (is_dir($publicPath . '/assets')) {
                $this->addDirectoryToZip($zip, $publicPath . '/assets', 'public/assets/');
            }

            $zip->addFromString('README.md', $this->generateBackupReadme($timestamp));
            $zip->close();

            if (file_exists($backupPath)) {
                $this->audit->log('create_backup', $this->auth->getUsername(), ['filename' => $backupFilename]);
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . $backupFilename . '"');
                header('Content-Length: ' . filesize($backupPath));
                header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
                header('Pragma: no-cache');
                readfile($backupPath);
                unlink($backupPath);
                exit;
            } else {
                throw new \Exception(\StaticMD\Core\I18n::t('admin.backup.file_not_created'));
            }
        } catch (\Exception $e) {
            error_log('Backup creation failed: ' . $e->getMessage());
            header('Location: /admin?action=settings&error=backup_failed&message=' . urlencode($e->getMessage()));
            exit;
        }
    }

    private function addDirectoryToZip(\ZipArchive $zip, string $sourcePath, string $zipPath): void
    {
        if (!is_dir($sourcePath)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourcePath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            $filePath = $file->getRealPath();
            $relativePath = $zipPath . substr($filePath, strlen($sourcePath) + 1);

            if ($file->isDir()) {
                $zip->addEmptyDir($relativePath);
            } else {
                $filename = $file->getFilename();
                if (in_array($filename, ['.DS_Store', 'Thumbs.db', '.gitignore'])) {
                    continue;
                }
                $zip->addFile($filePath, $relativePath);
            }
        }
    }

    private function addConfigToZip(\ZipArchive $zip): void
    {
        // config.php is 3 levels up: controllers/ → admin/ → system/ → project root
        $configPath = dirname(__DIR__, 3) . '/config.php';
        if (!file_exists($configPath)) {
            return;
        }

        $configContent = file_get_contents($configPath);
        $configContent = preg_replace(
            "/('password'\s*=>\s*')[^']*(')/",
            "$1*** REMOVED FOR SECURITY ***$2",
            $configContent
        );
        $zip->addFromString('config.php', $configContent);
    }

    private function generateBackupReadme(string $timestamp): string
    {
        $settings = $this->settingsCtrl->getSettings();
        $siteName = $settings['site_name'] ?? 'StaticMD';

        return "# StaticMD Backup\n\n" .
               "**Site:** {$siteName}\n" .
               "**Erstellt:** {$timestamp}\n" .
               "**Version:** " . ($this->config['system']['version'] ?? '1.0.0') . "\n\n" .
               "## Inhalt\n\n" .
               "Dieses Backup enthält:\n\n" .
               "- `content/` - Alle Markdown-Inhalte\n" .
               "- `system/settings.json` - Website-Einstellungen\n" .
               "- `system/themes/` - Alle Themes\n" .
               "- `public/images/` - Hochgeladene Bilder\n" .
               "- `public/assets/` - Öffentliche Assets\n" .
               "- `public/downloads/` - Hochgeladene Dateien\n" .
               "- `config.php` - Konfiguration (Passwort entfernt)\n\n" .
               "## Wiederherstellung\n\n" .
               "1. Entpacken Sie das Archiv in Ihr StaticMD-Verzeichnis\n" .
               "2. Passen Sie `config.php` an (Passwort setzen)\n" .
               "3. Stellen Sie sicher, dass die Verzeichnisrechte korrekt sind\n" .
               "4. Testen Sie Ihre Installation\n\n" .
               "**Wichtig:** Das Admin-Passwort wurde aus Sicherheitsgründen entfernt und muss neu gesetzt werden.\n";
    }
}
