<?php

namespace StaticMD\Admin\Controllers;

use StaticMD\Admin\AdminAuth;
use StaticMD\Core\AuditLog;

class UploadController
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

    public function handleFileUpload(): void
    {
        $this->auth->requireLogin();
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => \StaticMD\Core\I18n::t('admin.errors.invalid_request')]);
            exit;
        }

        if (!$this->auth->verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => \StaticMD\Core\I18n::t('admin.errors.csrf_invalid')]);
            exit;
        }

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'error' => \StaticMD\Core\I18n::t('admin.upload.no_file')]);
            exit;
        }

        $file = $_FILES['file'];
        $allowedTypes = ['application/pdf', 'application/zip', 'application/x-zip-compressed'];
        $allowedExts = ['pdf', 'zip'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $detectedMime = $finfo->file($file['tmp_name']);
        if (!in_array($detectedMime, $allowedTypes) || !in_array($ext, $allowedExts)) {
            echo json_encode(['success' => false, 'error' => \StaticMD\Core\I18n::t('admin.upload.invalid_type')]);
            exit;
        }

        $uploadDir = $this->config['paths']['public'] . '/downloads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $baseName = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($file['name'], PATHINFO_FILENAME));
        $filename = $baseName . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(2)) . '.' . $ext;
        $targetPath = $uploadDir . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $this->audit->log('upload_file', $this->auth->getUsername(), ['filename' => $filename]);
            echo json_encode(['success' => true, 'filename' => $filename]);
        } else {
            echo json_encode(['success' => false, 'error' => \StaticMD\Core\I18n::t('admin.upload.failed')]);
        }
        exit;
    }

    public function handleImageUpload(): void
    {
        $this->auth->requireLogin();
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => \StaticMD\Core\I18n::t('admin.errors.invalid_request')]);
            exit;
        }

        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'error' => \StaticMD\Core\I18n::t('admin.upload.no_file')]);
            exit;
        }

        if (!$this->auth->verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => \StaticMD\Core\I18n::t('admin.errors.csrf_invalid')]);
            exit;
        }

        $file = $_FILES['image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $allowedExts  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $detectedMime = $finfo->file($file['tmp_name']);
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($detectedMime, $allowedTypes) || !in_array($ext, $allowedExts)) {
            echo json_encode(['success' => false, 'error' => \StaticMD\Core\I18n::t('admin.upload.invalid_type')]);
            exit;
        }

        $uploadDir = $this->config['paths']['public'] . '/images';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $baseName = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($file['name'], PATHINFO_FILENAME));
        $filename = $baseName . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(2)) . '.' . $ext;
        $targetPath = $uploadDir . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $this->audit->log('upload_image', $this->auth->getUsername(), ['filename' => $filename]);
            echo json_encode(['success' => true, 'filename' => $filename]);
        } else {
            echo json_encode(['success' => false, 'error' => \StaticMD\Core\I18n::t('admin.upload.failed')]);
        }
        exit;
    }
}
