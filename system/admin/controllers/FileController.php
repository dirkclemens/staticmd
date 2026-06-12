<?php

namespace StaticMD\Admin\Controllers;

use StaticMD\Admin\AdminAuth;
use StaticMD\Core\AuditLog;

class FileController
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

    public function validatePath(): void
    {
        header('Content-Type: application/json');
        try {
            $this->auth->requireLogin();
            $path = $_GET['path'] ?? '';
            if (empty($path)) {
                echo json_encode(['valid' => true, 'exists' => false]);
                exit;
            }
            $path = $this->sanitizeFilename($path);
            $filePath = $this->config['paths']['content'] . '/' . $path . '.md';
            $exists = file_exists($filePath);
            echo json_encode([
                'valid' => !$exists,
                'exists' => $exists,
                'pathWillBeCreated' => !$exists && !empty($path)
            ]);
        } catch (\Throwable $e) {
            error_log('Path validation error: ' . $e->getMessage());
            echo json_encode(['valid' => true, 'exists' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    public function showEditor(): void
    {
        $this->auth->requireLogin();

        $file = $_GET['file'] ?? '';
        $content = '';
        $meta = [];
        $isNewFile = false;

        if (!empty($file)) {
            $contentPath = $this->findContentFile($file);

            if ($contentPath && is_readable($contentPath)) {
                $rawContent = file_get_contents($contentPath);
                if (strpos($rawContent, '---') === 0) {
                    $parts = explode('---', $rawContent, 3);
                    if (count($parts) >= 3) {
                        $frontMatterLines = explode("\n", trim($parts[1]));
                        foreach ($frontMatterLines as $line) {
                            if (strpos($line, ':') !== false) {
                                [$key, $value] = explode(':', $line, 2);
                                $meta[trim($key)] = trim($value, ' "\'');
                            }
                        }
                        $content = ltrim($parts[2]);
                    }
                } else {
                    $content = $rawContent;
                }
            } else {
                $isNewFile = true;
                $title = str_replace(['/', '-', '_'], ' ', $file);
                $meta = [
                    'title' => $title,
                    'author' => $this->auth->getUsername(),
                    'date' => date('Y-m-d')
                ];
            }
        } else {
            $isNewFile = true;
        }

        include __DIR__ . '/../templates/editor.php';
    }

    public function saveContent(): void
    {
        $this->auth->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin');
            exit;
        }

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!$this->auth->verifyCSRFToken($csrfToken)) {
            die(\StaticMD\Core\I18n::t('admin.errors.csrf_token_invalid'));
        }

        $file = $_POST['file'] ?? '';
        $content = $_POST['content'] ?? '';
        $meta = $_POST['meta'] ?? [];

        if (empty($file)) {
            die(\StaticMD\Core\I18n::t('admin.errors.filename_required'));
        }

        $file = $this->sanitizeFilename($file);
        $filePath = $this->config['paths']['content'] . '/' . $file . '.md';

        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $frontMatter = "---\n";
        $yellowOrder = ['Title', 'TitleSlug', 'Layout', 'Tag', 'Author'];
        $standardOrder = ['date', 'description'];

        foreach ($yellowOrder as $key) {
            if (isset($meta[$key]) && !empty(trim($meta[$key]))) {
                $frontMatter .= $key . ': ' . $meta[$key] . "\n";
            }
        }
        foreach ($standardOrder as $key) {
            if (isset($meta[$key]) && !empty(trim($meta[$key]))) {
                $frontMatter .= $key . ': ' . $meta[$key] . "\n";
            }
        }
        foreach ($meta as $key => $value) {
            if (!in_array($key, array_merge($yellowOrder, $standardOrder)) && !empty(trim($value))) {
                $frontMatter .= $key . ': ' . $value . "\n";
            }
        }
        $frontMatter .= "---\n\n";

        $fullContent = $frontMatter . $content;

        if (file_put_contents($filePath, $fullContent) !== false) {
            $this->audit->log('save_file', $this->auth->getUsername(), ['file' => $file]);
            $returnUrl = $_POST['return_url'] ?? '';
            if (!empty($returnUrl) && str_starts_with($returnUrl, '/') && !preg_match('#^https?://#i', $returnUrl)) {
                $separator = strpos($returnUrl, '?') !== false ? '&' : '?';
                header('Location: ' . $returnUrl . $separator . 'saved=1');
            } else {
                header('Location: /admin?message=saved');
            }
        } else {
            header('Location: /admin?action=edit&file=' . urlencode($file) . '&error=save_failed');
        }
        exit;
    }

    public function showNewContentForm(): void
    {
        $this->auth->requireLogin();

        $prefillPath = $_GET['prefill_path'] ?? '';
        if (!empty($prefillPath)) {
            $prefillPath = $this->sanitizeFilename($prefillPath);
        }

        $file = $prefillPath;
        $content = '';
        $meta = [
            'title' => '',
            'author' => $this->auth->getUsername(),
            'date' => date('Y-m-d')
        ];
        $isNewFile = true;

        include __DIR__ . '/../templates/editor.php';
    }

    public function deleteContent(): void
    {
        $this->auth->requireLogin();

        $file = $_REQUEST['file'] ?? '';
        $csrfToken = $_REQUEST['token'] ?? $_REQUEST['csrf_token'] ?? '';

        $returnUrl = '/admin';
        if (isset($_REQUEST['return_url'])) {
            $returnUrl = urldecode($_REQUEST['return_url']);
        } elseif (isset($_REQUEST['return'])) {
            $returnUrl = urldecode($_REQUEST['return']);
        }

        if (!str_starts_with($returnUrl, '/admin') && !str_starts_with($returnUrl, '/')) {
            $returnUrl = '/admin';
        }
        if (preg_match('#^https?://#i', $returnUrl)) {
            $returnUrl = '/admin';
        }

        $appendParam = function(string $url, string $param): string {
            $separator = (strpos($url, '?') !== false) ? '&' : '?';
            return $url . $separator . $param;
        };

        if (!$this->auth->verifyCSRFToken($csrfToken)) {
            header('Location: ' . $appendParam($returnUrl, 'error=csrf_invalid'));
            exit;
        }

        if (empty($file)) {
            header('Location: ' . $appendParam($returnUrl, 'error=no_file'));
            exit;
        }

        $file = $this->sanitizeFilename($file);
        if (strpos($file, '..') !== false) {
            header('Location: ' . $appendParam($returnUrl, 'error=invalid_file'));
            exit;
        }

        $filePath = $this->config['paths']['content'] . '/' . $file . '.md';

        if (!file_exists($filePath)) {
            header('Location: ' . $appendParam($returnUrl, 'error=file_not_found'));
            exit;
        }

        if (!is_writable(dirname($filePath))) {
            header('Location: ' . $appendParam($returnUrl, 'error=no_permission'));
            exit;
        }

        if (unlink($filePath)) {
            $this->audit->log('delete_file', $this->auth->getUsername(), ['file' => $file]);
            header('Location: ' . $appendParam($returnUrl, 'message=deleted'));
        } else {
            header('Location: ' . $appendParam($returnUrl, 'error=delete_failed'));
        }
        exit;
    }

    public function renameFile(): void
    {
        try {
            if (!$this->auth->isLoggedIn()) {
                header('Location: /admin?action=files&error=not_authenticated');
                exit;
            }

            $csrfToken = $_POST['csrf_token'] ?? $_REQUEST['csrf_token'] ?? '';
            if (!$this->auth->verifyCSRFToken($csrfToken)) {
                header('Location: /admin?action=files&error=csrf_invalid');
                exit;
            }

            $oldPath = $_POST['old_path'] ?? $_REQUEST['old_path'] ?? '';
            $newPath = $_POST['new_path'] ?? $_REQUEST['new_path'] ?? '';
            if (empty($oldPath) || empty($newPath)) {
                header('Location: /admin?action=files&error=missing_path');
                exit;
            }

            $oldPath = $this->sanitizeFilename($oldPath);
            $newPath = $this->sanitizeFilename($newPath);
            if (strpos($oldPath, '..') !== false || strpos($newPath, '..') !== false) {
                header('Location: /admin?action=files&error=invalid_path');
                exit;
            }

            $contentDir = $this->config['paths']['content'];
            $extension = '.md';
            $oldFile = $contentDir . '/' . $oldPath . $extension;

            if (strtolower(substr($newPath, -3)) === '.md') {
                $newFile = $contentDir . '/' . $newPath;
            } else {
                $newFile = $contentDir . '/' . $newPath . $extension;
            }

            if (!file_exists($oldFile)) {
                header('Location: /admin?action=files&error=source_not_found');
                exit;
            }
            if (file_exists($newFile)) {
                header('Location: /admin?action=files&error=target_exists');
                exit;
            }

            if (!is_writable(dirname($oldFile)) || !is_writable($contentDir)) {
                header('Location: /admin?action=files&error=no_permission');
                exit;
            }

            $newDir = dirname($newFile);
            if (!is_dir($newDir)) {
                if (!mkdir($newDir, 0755, true)) {
                    header('Location: /admin?action=files&error=mkdir_failed');
                    exit;
                }
            }

            if (rename($oldFile, $newFile)) {
                $this->audit->log('rename_file', $this->auth->getUsername(), ['from' => $oldPath, 'to' => $newPath]);
                header('Location: /admin?action=files&message=rename_success');
            } else {
                header('Location: /admin?action=files&error=rename_failed');
            }
        } catch (\Throwable $e) {
            error_log('Rename error: ' . $e->getMessage());
            header('Location: /admin?action=files&error=exception&message=' . urlencode($e->getMessage()));
        }
        exit;
    }

    public function findContentFile(string $route): ?string
    {
        $contentDir = $this->config['paths']['content'];
        $extension = $this->config['markdown']['file_extension'];

        $route = urldecode($route);
        $route = urldecode($route);
        if (class_exists('Normalizer') && function_exists('normalizer_normalize')) {
            $route = \Normalizer::normalize($route, \Normalizer::FORM_C);
        }

        $possiblePaths = [
            $contentDir . '/' . $route . $extension,
            $contentDir . '/' . $route . '/index' . $extension,
            $contentDir . '/' . $route . '/page' . $extension
        ];

        if ($route === 'index') {
            array_unshift($possiblePaths, $contentDir . '/index' . $extension);
            array_push($possiblePaths, $contentDir . '/home' . $extension);
        }

        foreach ($possiblePaths as $path) {
            $normalizedPath = $path;
            if (class_exists('Normalizer') && function_exists('normalizer_normalize')) {
                $normalizedPath = \Normalizer::normalize($path, \Normalizer::FORM_C);
            }
            if (file_exists($path) && is_readable($path)) {
                return $path;
            }
            if ($normalizedPath !== $path && file_exists($normalizedPath) && is_readable($normalizedPath)) {
                return $normalizedPath;
            }
        }

        return null;
    }

    public function sanitizeFilename(string $filename): string
    {
        $filename = urldecode($filename);
        $filename = urldecode($filename);
        if (class_exists('Normalizer') && function_exists('normalizer_normalize')) {
            $filename = \Normalizer::normalize($filename, \Normalizer::FORM_C);
        }
        $filename = preg_replace('/[^\p{L}\p{N}\-_\/]/u', '', $filename);
        $filename = preg_replace('/\/+/', '/', $filename);
        $filename = trim($filename, '/');
        $filename = str_replace(['..', './'], '', $filename);
        return $filename;
    }
}
