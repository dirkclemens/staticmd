<?php

namespace StaticMD\Admin\Controllers;

use StaticMD\Admin\AdminAuth;
use StaticMD\Core\AuditLog;

class DashboardController
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

    public function getAuth(): AdminAuth
    {
        return $this->auth;
    }

    public function showDashboard(): void
    {
        $this->auth->requireLogin();

        $contentLoader = new \StaticMD\Core\ContentLoader($this->config);
        $allFiles = $contentLoader->listAll();

        $settings = $this->settingsCtrl->getSettings();
        $recentFilesCount = $settings['recent_files_count'] ?? 15;

        $stats = [
            'total_files' => count($allFiles),
            'recent_files' => array_slice($allFiles, 0, $recentFilesCount),
            'disk_usage' => $this->calculateDiskUsage(),
            'public_size' => $this->calculatePublicSize(),
            'system_info' => [
                'php_version' => PHP_VERSION,
                'upload_max_filesize' => ini_get('upload_max_filesize')
            ]
        ];

        include __DIR__ . '/../templates/dashboard.php';
    }

    public function showFileManager(): void
    {
        $this->auth->requireLogin();

        $contentLoader = new \StaticMD\Core\ContentLoader($this->config);
        $allFiles = $contentLoader->listAll();

        $fileTree = $this->generateHierarchicalFileList($allFiles);

        include __DIR__ . '/../templates/files.php';
    }

    public function showAuditLog(): void
    {
        $this->auth->requireLogin();
        $auditEntries = $this->audit->getEntries(500);
        include __DIR__ . '/../templates/audit.php';
    }

    private function generateHierarchicalFileList(array $files): array
    {
        $tree = [];

        foreach ($files as $file) {
            $parts = explode('/', $file['route']);
            $current = &$tree;

            $path = '';
            for ($i = 0; $i < count($parts); $i++) {
                $part = $parts[$i];
                $path .= ($path ? '/' : '') . $part;

                if (!isset($current[$part])) {
                    $current[$part] = [
                        'type' => ($i === count($parts) - 1) ? 'file' : 'folder',
                        'name' => $part,
                        'path' => $path,
                        'children' => [],
                        'file_data' => null
                    ];
                }

                if ($i === count($parts) - 1) {
                    if (empty($current[$part]['children'])) {
                        $current[$part]['type'] = 'file';
                    } else {
                        $current[$part]['index_file'] = $file;
                        $current[$part]['type'] = 'folder';
                    }
                    $current[$part]['file_data'] = $file;
                } else {
                    if ($current[$part]['type'] !== 'file' || !empty($current[$part]['children'])) {
                        $current[$part]['type'] = 'folder';
                    }
                    $current = &$current[$part]['children'];
                }
            }
        }

        $this->sortFileTree($tree);

        return $tree;
    }

    private function sortFileTree(array &$tree): void
    {
        foreach ($tree as &$item) {
            if (!empty($item['children'])) {
                $this->sortFileTree($item['children']);
            }
        }

        uksort($tree, function($a, $b) use ($tree) {
            $typeA = $tree[$a]['type'];
            $typeB = $tree[$b]['type'];
            if ($typeA !== $typeB) {
                return $typeA === 'folder' ? -1 : 1;
            }
            return strcasecmp($a, $b);
        });
    }

    private function calculateDiskUsage(): string
    {
        return $this->formatBytes($this->getDirSize($this->config['paths']['content']));
    }

    private function calculatePublicSize(): string
    {
        return $this->formatBytes($this->getDirSize($this->config['paths']['public']));
    }

    private function getDirSize(string $dir): int
    {
        $size = 0;
        if (is_dir($dir)) {
            foreach (scandir($dir) as $file) {
                if ($file !== '.' && $file !== '..') {
                    $path = $dir . '/' . $file;
                    if (is_dir($path)) {
                        $size += $this->getDirSize($path);
                    } else {
                        $size += filesize($path);
                    }
                }
            }
        }
        return $size;
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
