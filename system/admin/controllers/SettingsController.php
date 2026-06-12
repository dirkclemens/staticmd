<?php

namespace StaticMD\Admin\Controllers;

use StaticMD\Admin\AdminAuth;
use StaticMD\Core\AuditLog;

class SettingsController
{
    private array $config;
    private AdminAuth $auth;
    private AuditLog $audit;

    public function __construct(array $config, AdminAuth $auth, AuditLog $audit)
    {
        $this->config = $config;
        $this->auth   = $auth;
        $this->audit  = $audit;
    }

    public function showSettings(): void
    {
        $this->auth->requireLogin();
        $settings = $this->getSettings();
        $availableThemes = $this->getAvailableThemes();
        $backupStats = $this->calculateBackupStats();
        include __DIR__ . '/../templates/settings.php';
    }

    public function saveSettings(): void
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

        $lang = $_POST['language'] ?? 'en';
        $lang = in_array($lang, ['en', 'de'], true) ? $lang : 'en';

        $availableThemes = $this->getAvailableThemes();
        $selectedTheme = $_POST['frontend_theme'] ?? 'bootstrap';
        if (!in_array($selectedTheme, $availableThemes, true)) {
            $selectedTheme = 'bootstrap';
        }

        $settings = [
            'site_name' => trim($_POST['site_name'] ?? 'StaticMD'),
            'site_logo' => trim($_POST['site_logo'] ?? ''),
            'frontend_theme' => $selectedTheme,
            'recent_files_count' => max(5, min(50, (int)($_POST['recent_files_count'] ?? 15))),
            'items_per_page' => max(10, min(100, (int)($_POST['items_per_page'] ?? 25))),
            'editor_theme' => $_POST['editor_theme'] ?? 'elegant',
            'show_file_stats' => isset($_POST['show_file_stats']),
            'auto_save_interval' => max(30, min(300, (int)($_POST['auto_save_interval'] ?? 60))),
            'navigation_show_dropdowns' => isset($_POST['navigation_show_dropdowns']),
            'navigation_order' => $this->parseNavigationOrder($_POST['navigation_order'] ?? ''),
            'language' => $lang,
            'search_result_limit' => max(10, min(200, (int)($_POST['search_result_limit'] ?? 50))),
            'seo_robots_policy' => $this->validateRobotsPolicy($_POST['seo_robots_policy'] ?? 'index,follow'),
            'seo_block_crawlers' => isset($_POST['seo_block_crawlers']),
            'seo_generate_robots_txt' => isset($_POST['seo_generate_robots_txt'])
        ];

        if ($this->saveSettingsToFile($settings)) {
            $this->audit->log('save_settings', $this->auth->getUsername(), ['theme' => $selectedTheme, 'lang' => $lang]);
            header('Location: /admin?action=settings&message=settings_saved');
        } else {
            header('Location: /admin?action=settings&error=save_failed');
        }
        exit;
    }

    public function getSettings(): array
    {
        $settingsFile = $this->config['paths']['system'] . '/settings.json';

        $defaultSettings = [
            'site_name' => $this->config['system']['name'] ?? 'StaticMD',
            'site_logo' => '',
            'recent_files_count' => 15,
            'items_per_page' => 25,
            'editor_theme' => 'elegant',
            'show_file_stats' => true,
            'auto_save_interval' => 60,
            'navigation_show_dropdowns' => true,
            'navigation_order' => [
                'about' => 1,
                'blog' => 2,
                'tech' => 3,
                'diy' => 4
            ],
            'language' => 'en',
            'search_result_limit' => 50
        ];

        if (file_exists($settingsFile)) {
            $savedSettings = json_decode(file_get_contents($settingsFile), true);
            if (is_array($savedSettings)) {
                return array_merge($defaultSettings, $savedSettings);
            }
        }

        return $defaultSettings;
    }

    public function getAvailableThemes(): array
    {
        $themesDir = $this->config['paths']['system'] . '/themes';
        $themes = [];
        if (is_dir($themesDir)) {
            foreach (scandir($themesDir) as $entry) {
                if ($entry[0] !== '.' && $entry !== 'shared' && is_dir($themesDir . '/' . $entry)) {
                    $themes[] = $entry;
                }
            }
        }
        return $themes;
    }

    public function calculateBackupStats(): array
    {
        $totalFiles = 0;
        $totalSize = 0;

        $contentPath = $this->config['paths']['content'];
        if (is_dir($contentPath)) {
            $stats = $this->analyzeDirectory($contentPath);
            $totalFiles += $stats['files'];
            $totalSize += $stats['size'];
        }

        $systemPath = $this->config['paths']['system'];
        if (file_exists($systemPath . '/settings.json')) {
            $totalFiles++;
            $totalSize += filesize($systemPath . '/settings.json');
        }

        // config.php is 3 levels up: controllers/ → admin/ → system/ → project root
        $configPath = dirname(__DIR__, 3) . '/config.php';
        if (file_exists($configPath)) {
            $totalFiles++;
            $totalSize += filesize($configPath);
        }

        $themesPath = $this->config['paths']['themes'];
        if (is_dir($themesPath)) {
            $stats = $this->analyzeDirectory($themesPath);
            $totalFiles += $stats['files'];
            $totalSize += $stats['size'];
        }

        $publicPath = $this->config['paths']['public'];
        foreach (['images', 'downloads', 'assets'] as $subdir) {
            $path = $publicPath . '/' . $subdir;
            if (is_dir($path)) {
                $stats = $this->analyzeDirectory($path);
                $totalFiles += $stats['files'];
                $totalSize += $stats['size'];
            }
        }

        return [
            'files' => $totalFiles,
            'size' => $totalSize,
            'size_formatted' => $this->formatBytes($totalSize, 1)
        ];
    }

    private function analyzeDirectory(string $path): array
    {
        if (!is_dir($path)) {
            return ['files' => 0, 'size' => 0];
        }

        $files = 0;
        $size = 0;

        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $filename = $file->getFilename();
                    if (in_array($filename, ['.DS_Store', 'Thumbs.db', '.gitignore'])) {
                        continue;
                    }
                    $files++;
                    $size += $file->getSize();
                }
            }
        } catch (\Exception $e) {
            return ['files' => 0, 'size' => 0];
        }

        return ['files' => $files, 'size' => $size];
    }

    private function saveSettingsToFile(array $settings): bool
    {
        $settingsFile = $this->config['paths']['system'] . '/settings.json';
        $json = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return file_put_contents($settingsFile, $json) !== false;
    }

    private function parseNavigationOrder(string $input): array
    {
        $order = [];

        if (!empty($input) && $input[0] === '{') {
            $decoded = json_decode($input, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        $lines = explode("\n", trim($input));
        $priority = 1;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (strpos($line, ':') !== false) {
                [$section, $prio] = explode(':', $line, 2);
                $order[trim($section)] = (int)trim($prio);
            } else {
                $order[trim($line)] = $priority++;
            }
        }

        return $order;
    }

    private function validateRobotsPolicy(string $policy): string
    {
        $validPolicies = [
            'index,follow',
            'index,nofollow',
            'noindex,follow',
            'noindex,nofollow'
        ];

        return in_array($policy, $validPolicies) ? $policy : 'index,follow';
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
