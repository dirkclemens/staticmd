<?php

namespace StaticMD\Admin;

/**
 * Admin Controller
 * 
 * Handles all administrative operations including content management,
 * file operations, settings, and backup functionality.
 */
class AdminController {
    private array $config;
    private AdminAuth $auth;

    /**
     * Öffentlicher Getter für Auth-Objekt
     */
    public function getAuth(): AdminAuth
    {
        return $this->auth;
    }

    /**
     * Constructor
     * 
     * @param array $config Application configuration
     * @param AdminAuth $auth Authentication handler instance
     */
    public function __construct(array $config, AdminAuth $auth)
    {
        $this->config = $config;
        $this->auth = $auth;
    }

    /**
     * Handle incoming HTTP request and route to appropriate action
     * 
     * Routes requests based on 'action' parameter to corresponding handler methods.
     */
    public function handleRequest(): void
    {
        $action = $_GET['action'] ?? 'dashboard';
        
        switch ($action) {
            case 'rename':
                $this->renameFile();
                break;
            case 'upload_file':
                // Only allow POST requests
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    echo json_encode(['success' => false, 'error' => \StaticMD\Core\I18n::t('admin.errors.invalid_request')]);
                    exit;
                }

                // Check if file is present
                if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                    echo json_encode(['success' => false, 'error' => \StaticMD\Core\I18n::t('admin.upload.no_file')]);
                    exit;
                }

                $file = $_FILES['file'];
                $allowedTypes = ['application/pdf', 'application/zip', 'application/x-zip-compressed'];
                $allowedExts = ['pdf', 'zip'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (!in_array($file['type'], $allowedTypes) || !in_array($ext, $allowedExts)) {
                    echo json_encode(['success' => false, 'error' => \StaticMD\Core\I18n::t('admin.upload.invalid_type')]);
                    exit;
                }

                // Target directory for downloads
                $uploadDir = $this->config['paths']['public'] . '/downloads';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                // Generate secure filename with timestamp and random component
                $baseName = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($file['name'], PATHINFO_FILENAME));
                $filename = $baseName . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(2)) . '.' . $ext;
                $targetPath = $uploadDir . '/' . $filename;

                // Move uploaded file to target location
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    // Return filename for editor tag insertion
                    echo json_encode(['success' => true, 'filename' => $filename]);
                } else {
                    echo json_encode(['success' => false, 'error' => \StaticMD\Core\I18n::t('admin.upload.failed')]);
                }
                exit;
            case 'upload_image':
                $this->handleImageUpload();
                break;
            case 'login':
                $this->handleLogin();
                break;
                
            case 'logout':
                $this->handleLogout();
                break;
                
            case 'dashboard':
                $this->showDashboard();
                break;
                
            case 'edit':
                $this->showEditor();
                break;
                
            case 'save':
                $this->saveContent();
                break;
                
            case 'new':
                $this->showNewContentForm();
                break;
                
            case 'delete':
                $this->deleteContent();
                break;
                
            case 'files':
                $this->showFileManager();
                break;
                
            case 'settings':
                $this->showSettings();
                break;
                
            case 'save_settings':
                $this->saveSettings();
                break;
                
            case 'create_backup':
                $this->createBackup();
                break;
                
            case 'validate_path':
                $this->validatePath();
                break;
                
            default:
                $this->showDashboard();
        }
    }

    /**
     * Validate path for new file
     * 
     * AJAX endpoint that checks if a path already exists.
     */
    private function validatePath(): void
    {
        // Set headers first
        header('Content-Type: application/json');
        
        try {
            // Check authentication
            if (!$this->auth->isLoggedIn()) {
                echo json_encode(['error' => 'Not authenticated', 'valid' => false]);
                exit;
            }
            
            $path = $_GET['path'] ?? '';
            if (empty($path)) {
                echo json_encode(['valid' => true, 'exists' => false]);
                exit;
            }
            
            $path = $this->sanitizeFilename($path);
            $filePath = $this->config['paths']['content'] . '/' . $path . '.md';
            
            // Check if file already exists
            $exists = file_exists($filePath);
            
            echo json_encode([
                'valid' => !$exists,
                'exists' => $exists,
                'pathWillBeCreated' => !$exists && !empty($path)
            ]);
        } catch (\Throwable $e) {
            error_log('Path validation error: ' . $e->getMessage());
            echo json_encode([
                'valid' => true,
                'exists' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }
    


    /**
     * Handle user login process
     * 
     * Processes login form submission, validates credentials via AdminAuth,
     * and redirects to dashboard on success or shows login form with error.
     */
    private function handleLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $csrfToken = $_POST['csrf_token'] ?? '';
            
            if ($this->auth->verifyCSRFToken($csrfToken) && $this->auth->login($username, $password)) {
                header('Location: /admin');
                exit;
            } else {
                $error = \StaticMD\Core\I18n::t('admin.login.invalid');
            }
        }
        
        include __DIR__ . '/templates/login.php';
    }

    /**
     * Handle user logout
     * 
     * Destroys session and redirects to login page with confirmation message.
     */
    private function handleLogout(): void
    {
        // Get last frontend URL before clearing session
        $returnUrl = $_SESSION['last_frontend_url'] ?? '/';
        
        $this->auth->logout();
        
        // Redirect to last visited frontend page or homepage
        header('Location: ' . $returnUrl);
        exit;
    }

    /**
     * Get all available themes from themes directory
     * 
     * Scans the themes directory and returns array of theme folder names.
     * 
     * @return array List of available theme names
     */
    public function getAvailableThemes(): array
    {
        $themesDir = $this->config['paths']['system'] . '/themes';
        $themes = [];
        if (is_dir($themesDir)) {
            foreach (scandir($themesDir) as $entry) {
                if ($entry[0] !== '.' && is_dir($themesDir . '/' . $entry)) {
                    $themes[] = $entry;
                }
            }
        }
        return $themes;
    }

    /**
     * Display admin dashboard with statistics and recent files
     * 
     * Loads content statistics, calculates disk usage, and shows
     * list of recently modified files.
     */
    private function showDashboard(): void
    {
        $this->auth->requireLogin();
        
        // Load content statistics
        require_once __DIR__ . '/../core/MarkdownParser.php';
        require_once __DIR__ . '/../core/ContentLoader.php';
        $contentLoader = new \StaticMD\Core\ContentLoader($this->config);
        $allFiles = $contentLoader->listAll();
        
        // Load settings
        $settings = $this->getSettings();
        $recentFilesCount = $settings['recent_files_count'] ?? 15;
        
        // Calculate statistics
        $stats = [
            'total_files' => count($allFiles),
            // First N files are the most recent
            'recent_files' => array_slice($allFiles, 0, $recentFilesCount),
            'disk_usage' => $this->calculateDiskUsage(),
            'public_size' => $this->calculatePublicSize(),
            'system_info' => [
                'php_version' => PHP_VERSION,
                'upload_max_filesize' => ini_get('upload_max_filesize')
            ]
        ];
        
        include __DIR__ . '/templates/dashboard.php';
    }

    /**
     * Display content editor with markdown editing interface
     * 
     * Loads existing content file or prepares new file template.
     * Supports CodeMirror editor with preview functionality.
     */
    private function showEditor(): void
    {
        $this->auth->requireLogin();
        
        $file = $_GET['file'] ?? '';
        $content = '';
        $meta = [];
        $isNewFile = false;

        if (!empty($file)) {
            require_once __DIR__ . '/../core/MarkdownParser.php';
            require_once __DIR__ . '/../core/ContentLoader.php';
            $contentLoader = new \StaticMD\Core\ContentLoader($this->config);

            // Debug: Zeige alle getesteten Pfade
            $extension = $this->config['markdown']['file_extension'];
            $contentDir = $this->config['paths']['content'];
            $possiblePaths = [
                $contentDir . '/' . $file . $extension,
                $contentDir . '/' . $file . '/index' . $extension,
                $contentDir . '/' . $file . '/page' . $extension
            ];
            if ($file === 'index') {
                array_unshift($possiblePaths, $contentDir . '/index' . $extension);
                array_push($possiblePaths, $contentDir . '/home' . $extension);
            }
            //error_log('DEBUG: Datei-Suche für "' . $file . '":');
            // foreach ($possiblePaths as $p) {
            //     error_log('DEBUG: Teste Pfad: ' . $p . ' => ' . (file_exists($p) ? 'EXISTIERT' : 'FEHLT'));
            // }

            // Direkt den Dateipfad finden
            $contentPath = $this->findContentFile($file);
            //error_log('DEBUG: Gefundener Pfad: ' . ($contentPath ?: 'KEIN TREFFER'));

            if ($contentPath && is_readable($contentPath)) {
                $rawContent = file_get_contents($contentPath);

                // Front Matter und Content trennen
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
                        $content = trim($parts[2]);
                    }
                } else {
                    $content = $rawContent;
                }
            } else {
                //error_log('DEBUG: Datei konnte nicht gelesen werden oder existiert nicht!');
                $isNewFile = true;
                // $title = ucwords(str_replace(['/', '-', '_'], ' ', $file));
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
        
        include __DIR__ . '/templates/editor.php';
    }

    /**
     * Save content to markdown file
     * 
     * Processes form submission, validates CSRF token, constructs front matter,
     * and saves content to file. Supports both new and existing files.
     */
    private function saveContent(): void
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
        
        // Enforce secure filename
        $file = $this->sanitizeFilename($file);
        $filePath = $this->config['paths']['content'] . '/' . $file . '.md';
        
        // Create directory if needed
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        // Create front matter (Yellow CMS format preferred)
        $frontMatter = "---\n";
        
        // Define order for Yellow CMS compatibility
        $yellowOrder = ['Title', 'TitleSlug', 'Layout', 'Tag', 'Author'];
        $standardOrder = ['date', 'description'];
        
        // First Yellow CMS fields in defined order
        foreach ($yellowOrder as $key) {
            if (isset($meta[$key]) && !empty(trim($meta[$key]))) {
                $frontMatter .= $key . ': ' . $meta[$key] . "\n";
            }
        }
        
        // Then standard fields
        foreach ($standardOrder as $key) {
            if (isset($meta[$key]) && !empty(trim($meta[$key]))) {
                $frontMatter .= $key . ': ' . $meta[$key] . "\n";
            }
        }
        
        // All other fields
        foreach ($meta as $key => $value) {
            if (!in_array($key, array_merge($yellowOrder, $standardOrder)) && !empty(trim($value))) {
                $frontMatter .= $key . ': ' . $value . "\n";
            }
        }
        
        $frontMatter .= "---\n\n";
        
        // Save file
        $fullContent = $frontMatter . $content;
        
        if (file_put_contents($filePath, $fullContent) !== false) {
            // Check if return URL was provided
            $returnUrl = $_POST['return_url'] ?? '';
            if (!empty($returnUrl)) {
                // Return to original page with success parameter
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

    /**
     * Display form for creating new content file
     * 
     * Prepares empty editor with default meta fields.
     * Supports prefill_path parameter to prefill the file route.
     */
    private function showNewContentForm(): void
    {
        $this->auth->requireLogin();
        
        // Check if prefill_path is provided (from admin toolbar)
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
        
        include __DIR__ . '/templates/editor.php';
    }

    /**
     * Delete content file
     * 
     * Handles file deletion with security checks and return URL support.
     * Validates CSRF token and prevents path traversal attacks.
     */
    private function deleteContent(): void
    {
        $this->auth->requireLogin();
        
        // Allow GET or POST for file manager compatibility
        $file = $_REQUEST['file'] ?? '';
        $csrfToken = $_REQUEST['token'] ?? $_REQUEST['csrf_token'] ?? '';
        
        // Handle return URL with fallback
        $returnUrl = '/admin'; // Default
        if (isset($_REQUEST['return_url'])) {
            $returnUrl = urldecode($_REQUEST['return_url']);
        } elseif (isset($_REQUEST['return'])) {
            $returnUrl = urldecode($_REQUEST['return']);
        }
        
        // Security check: only allow local URLs (admin or frontend)
        if (!str_starts_with($returnUrl, '/admin') && !str_starts_with($returnUrl, '/')) {
            $returnUrl = '/admin';
        }
        // Additional security: ensure it's a relative URL
        if (preg_match('#^https?://#i', $returnUrl)) {
            $returnUrl = '/admin';
        }
        
        // Helper function to append query parameters correctly
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
        
        // Security: prevent path traversal
        $file = $this->sanitizeFilename($file);
        if (strpos($file, '..') !== false) {
            header('Location: ' . $appendParam($returnUrl, 'error=invalid_file'));
            exit;
        }
        
        $filePath = $this->config['paths']['content'] . '/' . $file . '.md';
        
        // Check if file exists and is deletable
        if (!file_exists($filePath)) {
            header('Location: ' . $appendParam($returnUrl, 'error=file_not_found'));
            exit;
        }
        
        if (!is_writable(dirname($filePath))) {
            header('Location: ' . $appendParam($returnUrl, 'error=no_permission'));
            exit;
        }
        
        // Delete file
        if (unlink($filePath)) {
            header('Location: ' . $appendParam($returnUrl, 'message=deleted'));
        } else {
            header('Location: ' . $appendParam($returnUrl, 'error=delete_failed'));
        }
        
        exit;
    }

    /**
     * Display file manager with hierarchical file tree
     * 
     * Shows all content files in a tree structure with edit/delete actions.
     */
    private function showFileManager(): void
    {
        $this->auth->requireLogin();
        
        require_once __DIR__ . '/../core/MarkdownParser.php';
        require_once __DIR__ . '/../core/ContentLoader.php';
        $contentLoader = new \StaticMD\Core\ContentLoader($this->config);
        $allFiles = $contentLoader->listAll();
        
        // Generate hierarchical structure
        $fileTree = $this->generateHierarchicalFileList($allFiles);
        
        include __DIR__ . '/templates/files.php';
    }
    
    /**
     * Generate hierarchical file list as tree structure
     * 
     * Converts flat file list into nested tree structure for display.
     * 
     * @param array $files Flat list of files from ContentLoader
     * @return array Nested tree structure
     */
    private function generateHierarchicalFileList(array $files): array
    {
        $tree = [];
        
        foreach ($files as $file) {
            $parts = explode('/', $file['route']);
            $current = &$tree;
            
            // Build path incrementally
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
                
                // At last part: attach file data
                if ($i === count($parts) - 1) {
                    // Only set as 'file' if it's not already a folder with children
                    if (empty($current[$part]['children'])) {
                        $current[$part]['type'] = 'file';
                    } else {
                        // If children exist, it's a folder with index.md
                        // Store file as special property of folder
                        $current[$part]['index_file'] = $file;
                        $current[$part]['type'] = 'folder';
                    }
                    $current[$part]['file_data'] = $file;
                } else {
                    // Only set as folder if not already a file
                    if ($current[$part]['type'] !== 'file' || !empty($current[$part]['children'])) {
                        $current[$part]['type'] = 'folder';
                    }
                    $current = &$current[$part]['children'];
                }
            }
        }
        
        // Sort folders and files separately
        $this->sortFileTree($tree);
        
        return $tree;
    }
    
    /**
     * Sort file tree: folders first, then files (both alphabetically)
     * 
     * Recursively sorts tree structure with folders prioritized.
     * 
     * @param array $tree Tree structure to sort (passed by reference)
     */
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
            
            // Folders first
            if ($typeA !== $typeB) {
                return $typeA === 'folder' ? -1 : 1;
            }
            
            // Same type: alphabetical
            return strcasecmp($a, $b);
        });
    }

    /**
     * Calculate disk usage of content directory
     * 
     * @return string Formatted size string (e.g., "5.2 MB")
     */
    private function calculateDiskUsage(): string
    {
        $contentDir = $this->config['paths']['content'];
        $size = $this->getDirSize($contentDir);
        
        return $this->formatBytes($size);
    }
    
    /**
     * Calculate size of public directory
     * 
     * @return string Formatted size string (e.g., "12.8 MB")
     */
    private function calculatePublicSize(): string
    {
        $publicDir = $this->config['paths']['public'];
        $size = $this->getDirSize($publicDir);
        
        return $this->formatBytes($size);
    }

    /**
     * Calculate directory size recursively
     * 
     * @param string $dir Directory path
     * @return int Size in bytes
     */
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

    /**
     * Format bytes as human-readable size
     * 
     * @param int $bytes Size in bytes
     * @param int $precision Decimal precision (default: 2)
     * @return string Formatted size string
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Find content file path matching route (similar to ContentLoader logic)
     * 
     * Tries multiple possible file paths for a given route.
     * 
     * @param string $route Content route
     * @return string|null File path if found, null otherwise
     */
    private function findContentFile(string $route): ?string
    {
        $contentDir = $this->config['paths']['content'];
        $extension = $this->config['markdown']['file_extension'];
        
        // URL decode and normalize Unicode for route
        $route = urldecode($route);
        $route = urldecode($route);
        if (class_exists('Normalizer') && function_exists('normalizer_normalize')) {
            $route = \Normalizer::normalize($route, \Normalizer::FORM_C);
        }
        
        // Try possible paths
        $possiblePaths = [
            $contentDir . '/' . $route . $extension,
            $contentDir . '/' . $route . '/index' . $extension,
            $contentDir . '/' . $route . '/page' . $extension
        ];
        
        // For root "/" also check index.md and home.md
        if ($route === 'index') {
            array_unshift($possiblePaths, $contentDir . '/index' . $extension);
            array_push($possiblePaths, $contentDir . '/home' . $extension);
        }
        
        foreach ($possiblePaths as $path) {
            // Also try normalized filesystem lookup for Unicode filenames
            $normalizedPath = $path;
            if (class_exists('Normalizer') && function_exists('normalizer_normalize')) {
                $normalizedPath = \Normalizer::normalize($path, \Normalizer::FORM_C);
            }
            
            if (file_exists($path) && is_readable($path)) {
                return $path;
            }
            // Try normalized path if different
            if ($normalizedPath !== $path && file_exists($normalizedPath) && is_readable($normalizedPath)) {
                return $normalizedPath;
            }
        }
        
        return null;
    }

    /**
     * Sanitize filename for security
     * 
     * Removes dangerous characters and prevents path traversal attacks.
     * Unicode letters (including German umlauts) are allowed.
     * 
     * @param string $filename Filename to sanitize
     * @return string Sanitized filename
     */
    private function sanitizeFilename(string $filename): string
    {
        // URL decode and normalize Unicode (double decode for combined characters)
        $filename = urldecode($filename);
        $filename = urldecode($filename);
        
        // Unicode normalization (NFD to NFC)
        if (class_exists('Normalizer') && function_exists('normalizer_normalize')) {
            $filename = \Normalizer::normalize($filename, \Normalizer::FORM_C);
        }
        
        // Allow Unicode letters (\p{L}), numbers (\p{N}), -, _, /
        // This includes German umlauts (ä, ö, ü, ß) and other Unicode letters
        $filename = preg_replace('/[^\p{L}\p{N}\-_\/]/u', '', $filename);
        
        // Remove multiple slashes
        $filename = preg_replace('/\/+/', '/', $filename);
        
        // Remove leading and trailing slashes
        $filename = trim($filename, '/');
        
        // Prevent path traversal
        $filename = str_replace(['..', './'], '', $filename);
        
        return $filename;
    }

    /**
     * Display settings page
     * 
     * Shows system settings form with current values and backup options.
     */
    private function showSettings(): void
    {
        $this->auth->requireLogin();
        
    $settings = $this->getSettings();
    $availableThemes = $this->getAvailableThemes();
    $backupStats = $this->calculateBackupStats();
    include __DIR__ . '/templates/settings.php';
    }

    /**
     * Save system settings
     * 
     * Validates and saves settings to JSON file. Includes validation
     * for themes, numeric limits, and security policies.
     */
    private function saveSettings(): void
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
            'editor_theme' => $_POST['editor_theme'] ?? 'github',
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
            header('Location: /admin?action=settings&message=settings_saved');
        } else {
            header('Location: /admin?action=settings&error=save_failed');
        }
        exit;
    }

    /**
     * Load settings from JSON file
     * 
     * Merges saved settings with defaults to ensure all keys exist.
     * 
     * @return array Settings array with defaults
     */
    private function getSettings(): array
    {
        $settingsFile = $this->config['paths']['system'] . '/settings.json';
        
        $defaultSettings = [
            'site_name' => $this->config['system']['name'] ?? 'StaticMD',
            'site_logo' => '',
            'recent_files_count' => 15,
            'items_per_page' => 25,
            'editor_theme' => 'github',
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

    /**
     * Save settings to JSON file
     * 
     * @param array $settings Settings to save
     * @return bool True on success, false on failure
     */
    private function saveSettingsToFile(array $settings): bool
    {
        $settingsFile = $this->config['paths']['system'] . '/settings.json';
        
        $json = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        return file_put_contents($settingsFile, $json) !== false;
    }

    /**
     * Parse navigation order from input
     * 
     * Supports both JSON format (from SortableJS drag & drop)
     * and text format (line-based with optional priorities).
     * 
     * @param string $input Navigation order input string
     * @return array Parsed navigation order array
     */
    private function parseNavigationOrder(string $input): array
    {
        $order = [];
        
        // Check if input is JSON (from SortableJS Drag & Drop)
        if (!empty($input) && $input[0] === '{') {
            $decoded = json_decode($input, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        
        // Fallback: parse old text-based input
        $lines = explode("\n", trim($input));
        $priority = 1;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Format: "section" or "section:priority"
            if (strpos($line, ':') !== false) {
                [$section, $prio] = explode(':', $line, 2);
                $order[trim($section)] = (int)trim($prio);
            } else {
                $order[trim($line)] = $priority++;
            }
        }
        
        return $order;
    }

    /**
     * Handle image upload for editor (AJAX)
     * 
     * Processes image uploads, validates file types, and returns
     * filename for markdown insertion.
     */
    private function handleImageUpload(): void
    {
        $this->auth->requireLogin();
        header('Content-Type: application/json; charset=utf-8');

        // Only allow POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => \StaticMD\Core\I18n::t('admin.errors.invalid_request')]);
            exit;
        }

        // Check if file is present
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'error' => \StaticMD\Core\I18n::t('admin.upload.no_file')]);
            exit;
        }

        $file = $_FILES['image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            echo json_encode(['success' => false, 'error' => \StaticMD\Core\I18n::t('admin.upload.invalid_type')]);
            exit;
        }

        // Target directory for images
        $uploadDir = $this->config['paths']['public'] . '/images';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate secure filename with timestamp
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $baseName = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($file['name'], PATHINFO_FILENAME));
        $filename = $baseName . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(2)) . '.' . $ext;
        $targetPath = $uploadDir . '/' . $filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Return filename for editor tag insertion
            echo json_encode(['success' => true, 'filename' => $filename]);
        } else {
            echo json_encode(['success' => false, 'error' => \StaticMD\Core\I18n::t('admin.upload.failed')]);
        }
        exit;
    }

    /**
     * Validate robots policy value
     * 
     * @param string $policy Policy to validate
     * @return string Valid policy or default 'index,follow'
     */
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
    
    /**
     * Calculate backup statistics (file count and total size)
     * 
     * Analyzes all directories included in backup to provide size estimate.
     * 
     * @return array Statistics array with 'files', 'size', and 'size_formatted'
     */
    private function calculateBackupStats(): array
    {
        $totalFiles = 0;
        $totalSize = 0;
        
        // Analyze content folder
        $contentPath = $this->config['paths']['content'];
        if (is_dir($contentPath)) {
            $stats = $this->analyzeDirectory($contentPath);
            $totalFiles += $stats['files'];
            $totalSize += $stats['size'];
        }
        
        // System settings file
        $systemPath = $this->config['paths']['system'];
        if (file_exists($systemPath . '/settings.json')) {
            $totalFiles++;
            $totalSize += filesize($systemPath . '/settings.json');
        }
        
        // Config file
        $configPath = __DIR__ . '/../../config.php';
        if (file_exists($configPath)) {
            $totalFiles++;
            $totalSize += filesize($configPath);
        }
        
        // Analyze themes
        $themesPath = $this->config['paths']['themes'];
        if (is_dir($themesPath)) {
            $stats = $this->analyzeDirectory($themesPath);
            $totalFiles += $stats['files'];
            $totalSize += $stats['size'];
        }
        
        // Analyze public assets
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
    
    /**
     * Analyze directory recursively and return file count and size
     * 
     * @param string $path Directory path to analyze
     * @return array Array with 'files' count and 'size' in bytes
     */
    private function analyzeDirectory(string $path): array
    {
        $files = 0;
        $size = 0;
        
        if (!is_dir($path)) {
            return ['files' => 0, 'size' => 0];
        }
        
        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    // Exclude certain system files
                    $filename = $file->getFilename();
                    if (in_array($filename, ['.DS_Store', 'Thumbs.db', '.gitignore'])) {
                        continue;
                    }
                    
                    $files++;
                    $size += $file->getSize();
                }
            }
        } catch (\Exception $e) {
            // Return zeros on error
            return ['files' => 0, 'size' => 0];
        }
        
        return ['files' => $files, 'size' => $size];
    }
    
    /**
     * Create complete backup of all important files
     * 
     * Generates ZIP archive containing content, settings, themes, and assets.
     * Excludes sensitive data like password hashes.
     */
    private function createBackup(): void
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
            // Check if ZIP extension is available
            if (!class_exists('\ZipArchive')) {
                throw new \Exception(\StaticMD\Core\I18n::t('admin.backup.zip_not_available'));
            }
            
            // Backup filename with timestamp
            $timestamp = date('Y-m-d_H-i-s');
            $backupFilename = "staticmd_backup_{$timestamp}.zip";
            $backupPath = sys_get_temp_dir() . '/' . $backupFilename;
            
            // Create ZIP archive
            $zip = new \ZipArchive();
            if ($zip->open($backupPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
                throw new \Exception(\StaticMD\Core\I18n::t('admin.backup.create_failed'));
            }
            
            // Add content folder
            $this->addDirectoryToZip($zip, $this->config['paths']['content'], 'content/');
            
            // Add system settings
            $systemPath = $this->config['paths']['system'];
            if (file_exists($systemPath . '/settings.json')) {
                $zip->addFile($systemPath . '/settings.json', 'system/settings.json');
            }
            
            // Add config file (without password hash for security)
            $this->addConfigToZip($zip);
            
            // Add themes
            $this->addDirectoryToZip($zip, $this->config['paths']['themes'], 'system/themes/');
            
            // Add public assets
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
            
            // Add README for backup
            $readme = $this->generateBackupReadme($timestamp);
            $zip->addFromString('README.md', $readme);
            
            $zip->close();
            
            // Offer backup for download
            if (file_exists($backupPath)) {
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . $backupFilename . '"');
                header('Content-Length: ' . filesize($backupPath));
                header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
                header('Pragma: no-cache');
                
                readfile($backupPath);
                unlink($backupPath); // Delete temporary file
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
    
    /**
     * Add directory recursively to ZIP archive
     * 
     * @param \ZipArchive $zip ZIP archive instance
     * @param string $sourcePath Source directory path
     * @param string $zipPath Path within ZIP archive
     */
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
                // Exclude certain system files
                $filename = $file->getFilename();
                if (in_array($filename, ['.DS_Store', 'Thumbs.db', '.gitignore'])) {
                    continue;
                }
                
                $zip->addFile($filePath, $relativePath);
            }
        }
    }
    
    /**
     * Add sanitized config file to ZIP archive
     * 
     * Removes password hash from config for security.
     * 
     * @param \ZipArchive $zip ZIP archive instance
     */
    private function addConfigToZip(\ZipArchive $zip): void
    {
        $configPath = __DIR__ . '/../../config.php';
        if (!file_exists($configPath)) {
            return;
        }
        
        $configContent = file_get_contents($configPath);
        
        // Replace password hash with placeholder
        $configContent = preg_replace(
            "/('password'\s*=>\s*')[^']*(')/",
            "$1*** REMOVED FOR SECURITY ***$2",
            $configContent
        );
        
        $zip->addFromString('config.php', $configContent);
    }
    
    /**
     * Generate README file for backup
     * 
     * @param string $timestamp Backup creation timestamp
     * @return string README content
     */
    private function generateBackupReadme(string $timestamp): string
    {
        $settings = $this->getSettings();
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

    /**
     * Rename or move a content file
     *
     * Handles file renaming/moving with security checks, CSRF validation, authentication,
     * path validation, and error handling. Returns status and error message as JSON.
     */
    public function renameFile(): void
    {
        try {
            // Authentifizierung prüfen
            if (!$this->auth->isLoggedIn()) {
                header('Location: /admin?action=files&error=not_authenticated');
                exit;
            }

            // CSRF-Token prüfen
            $csrfToken = $_POST['csrf_token'] ?? $_REQUEST['csrf_token'] ?? '';
            if (!$this->auth->verifyCSRFToken($csrfToken)) {
                header('Location: /admin?action=files&error=csrf_invalid');
                exit;
            }

            // Ursprungs- und Zielpfad prüfen
            $oldPath = $_POST['old_path'] ?? $_REQUEST['old_path'] ?? '';
            $newPath = $_POST['new_path'] ?? $_REQUEST['new_path'] ?? '';
            if (empty($oldPath) || empty($newPath)) {
                header('Location: /admin?action=files&error=missing_path');
                exit;
            }

            // Pfade normalisieren und validieren
            $oldPath = $this->sanitizeFilename($oldPath);
            $newPath = $this->sanitizeFilename($newPath);
            if (strpos($oldPath, '..') !== false || strpos($newPath, '..') !== false) {
                header('Location: /admin?action=files&error=invalid_path');
                exit;
            }

            $contentDir = $this->config['paths']['content'];
            $extension = '.md';
            $oldFile = $contentDir . '/' . $oldPath . $extension;

            // Prüfe, ob die neue Datei-Endung schon vorhanden ist
            if (strtolower(substr($newPath, -3)) === '.md') {
                $newFile = $contentDir . '/' . $newPath;
            } else {
                $newFile = $contentDir . '/' . $newPath . $extension;
            }

            // Existenz prüfen
            if (!file_exists($oldFile)) {
                header('Location: /admin?action=files&error=source_not_found');
                exit;
            }
            if (file_exists($newFile)) {
                header('Location: /admin?action=files&error=target_exists');
                exit;
            }

            // Schreibrechte prüfen
            if (!is_writable(dirname($oldFile)) || !is_writable($contentDir)) {
                header('Location: /admin?action=files&error=no_permission');
                exit;
            }

            // Zielverzeichnis ggf. anlegen
            $newDir = dirname($newFile);
            if (!is_dir($newDir)) {
                if (!mkdir($newDir, 0755, true)) {
                    header('Location: /admin?action=files&error=mkdir_failed');
                    exit;
                }
            }

            // Datei verschieben/umbenennen
            if (rename($oldFile, $newFile)) {
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

}
