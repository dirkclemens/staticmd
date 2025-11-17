<?php

namespace StaticMD\Admin;

/**
 * Admin-Controller
 * Verarbeitet alle Admin-Anfragen
 */
class AdminController {
    private array $config;
    private AdminAuth $auth;

    public function __construct(array $config, AdminAuth $auth)
    {
        $this->config = $config;
        $this->auth = $auth;
    }

    /**
     * Verarbeitet die aktuelle Anfrage
     */
    public function handleRequest(): void
    {
        $action = $_GET['action'] ?? 'dashboard';
        
        switch ($action) {
            case 'upload_file':
                // Nur POST erlauben
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
                    exit;
                }

                // Prüfe ob Datei vorhanden ist
                if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                    echo json_encode(['success' => false, 'error' => 'No file uploaded']);
                    exit;
                }

                $file = $_FILES['file'];
                $allowedTypes = ['application/pdf', 'application/zip', 'application/x-zip-compressed'];
                $allowedExts = ['pdf', 'zip'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (!in_array($file['type'], $allowedTypes) || !in_array($ext, $allowedExts)) {
                    echo json_encode(['success' => false, 'error' => 'Invalid file type']);
                    exit;
                }

                // Zielverzeichnis
                $uploadDir = $this->config['paths']['public'] . '/downloads';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                // Sicheren Dateinamen generieren
                $baseName = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($file['name'], PATHINFO_FILENAME));
                $filename = $baseName . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(2)) . '.' . $ext;
                $targetPath = $uploadDir . '/' . $filename;

                // Datei verschieben
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    // Rückgabe: nur Dateiname für Editor-Tag
                    echo json_encode(['success' => true, 'filename' => $filename]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Upload failed']);
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
                
            default:
                $this->showDashboard();
        }
    }

    /**
     * Behandelt Login-Prozess
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
     * Behandelt Logout
     */
    private function handleLogout(): void
    {
        $this->auth->logout();
        header('Location: /admin?action=login&message=logged_out');
        exit;
    }

    /**
     * Gibt alle verfügbaren Themes im Theme-Verzeichnis zurück
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
     * Zeigt Admin-Dashboard
     */
    private function showDashboard(): void
    {
        $this->auth->requireLogin();
        
        // Content-Statistiken laden
        require_once __DIR__ . '/../core/MarkdownParser.php';
        require_once __DIR__ . '/../core/ContentLoader.php';
        $contentLoader = new \StaticMD\Core\ContentLoader($this->config);
        $allFiles = $contentLoader->listAll();
        
        // Einstellungen laden
        $settings = $this->getSettings();
        $recentFilesCount = $settings['recent_files_count'] ?? 15;
        
        // Statistiken berechnen
        $stats = [
            'total_files' => count($allFiles),
            // Die ersten N Dateien sind die neuesten
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
     * Zeigt Content-Editor
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
     * Speichert Content
     */
    private function saveContent(): void
    {
        //DEBUG: Zeige die ersten und letzten Zeichen des Inhalts
        if (isset($_POST['content'])) {
            $debugContent = $_POST['content'];
            // error_log('DEBUG: Content-Start: ' . substr($debugContent, 0, 40));
            // error_log('DEBUG: Content-Ende: ' . substr($debugContent, -40));
            // error_log('DEBUG: Content-RAW: ' . bin2hex(substr($debugContent, -10)));
        }
    
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
        
        // Sichere Dateinamen erzwingen
        $file = $this->sanitizeFilename($file);
        $filePath = $this->config['paths']['content'] . '/' . $file . '.md';
        
        // Verzeichnis erstellen falls nötig
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        // Front Matter erstellen (Yellow CMS Format bevorzugt)
        $frontMatter = "---\n";
        
        // Definiere Reihenfolge für Yellow CMS Kompatibilität
        $yellowOrder = ['Title', 'TitleSlug', 'Layout', 'Tag', 'Author'];
        $standardOrder = ['date', 'description'];
        
        // Zuerst Yellow-Felder in definierter Reihenfolge
        foreach ($yellowOrder as $key) {
            if (isset($meta[$key]) && !empty(trim($meta[$key]))) {
                $frontMatter .= $key . ': ' . $meta[$key] . "\n";
            }
        }
        
        // Dann Standard-Felder
        foreach ($standardOrder as $key) {
            if (isset($meta[$key]) && !empty(trim($meta[$key]))) {
                $frontMatter .= $key . ': ' . $meta[$key] . "\n";
            }
        }
        
        // Alle anderen Felder
        foreach ($meta as $key => $value) {
            if (!in_array($key, array_merge($yellowOrder, $standardOrder)) && !empty(trim($value))) {
                $frontMatter .= $key . ': ' . $value . "\n";
            }
        }
        
        $frontMatter .= "---\n\n";
        
        // Datei speichern
        $fullContent = $frontMatter . $content;
        
        if (file_put_contents($filePath, $fullContent) !== false) {
            // Prüfen ob eine Return-URL angegeben wurde
            $returnUrl = $_POST['return_url'] ?? '';
            if (!empty($returnUrl)) {
                // Zurück zur ursprünglichen Seite mit Erfolgsparameter
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
     * Zeigt Formular für neue Datei
     */
    private function showNewContentForm(): void
    {
        $this->auth->requireLogin();
        
        $file = '';
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
     * Löscht Content-Datei
     */
    private function deleteContent(): void
    {
        $this->auth->requireLogin();
        
        // GET oder POST erlauben für Dateimanager-Kompatibilität
        $file = $_REQUEST['file'] ?? '';
        $csrfToken = $_REQUEST['token'] ?? $_REQUEST['csrf_token'] ?? '';
        
        // Einfache Return-URL-Behandlung
        $returnUrl = '/admin'; // Default
        if (isset($_REQUEST['return_url'])) {
            $returnUrl = urldecode($_REQUEST['return_url']);
        } elseif (isset($_REQUEST['return'])) {
            $returnUrl = urldecode($_REQUEST['return']);
        }
        
        // Sicherheitscheck: Nur lokale URLs erlauben
        if (!str_starts_with($returnUrl, '/admin')) {
            $returnUrl = '/admin';
        }
        
        if (!$this->auth->verifyCSRFToken($csrfToken)) {
            header('Location: ' . $returnUrl . '?error=csrf_invalid');
            exit;
        }
        
        if (empty($file)) {
            header('Location: ' . $returnUrl . '?error=no_file');
            exit;
        }
        
        // Sicherheit: Pfad-Traversal verhindern
        $file = $this->sanitizeFilename($file);
        if (strpos($file, '..') !== false) {
            header('Location: ' . $returnUrl . '?error=invalid_file');
            exit;
        }
        
        $filePath = $this->config['paths']['content'] . '/' . $file . '.md';
        
        // Prüfen ob Datei existiert und löschbar ist
        if (!file_exists($filePath)) {
            header('Location: ' . $returnUrl . '?error=file_not_found');
            exit;
        }
        
        if (!is_writable(dirname($filePath))) {
            header('Location: ' . $returnUrl . '?error=no_permission');
            exit;
        }
        
        // Datei löschen
        if (unlink($filePath)) {
            header('Location: ' . $returnUrl . '?message=deleted');
        } else {
            header('Location: ' . $returnUrl . '?error=delete_failed');
        }
        
        exit;
    }

    /**
     * Zeigt Dateimanager
     */
    private function showFileManager(): void
    {
        $this->auth->requireLogin();
        
        require_once __DIR__ . '/../core/MarkdownParser.php';
        require_once __DIR__ . '/../core/ContentLoader.php';
        $contentLoader = new \StaticMD\Core\ContentLoader($this->config);
        $allFiles = $contentLoader->listAll();
        
        // Hierarchische Struktur erstellen
        $fileTree = $this->generateHierarchicalFileList($allFiles);
        
        include __DIR__ . '/templates/files.php';
    }
    
    /**
     * Generiert hierarchische Dateiliste als Baum
     */
    private function generateHierarchicalFileList(array $files): array
    {
        $tree = [];
        
        foreach ($files as $file) {
            $parts = explode('/', $file['route']);
            $current = &$tree;
            
            // Pfad aufbauen
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
                
                // Bei letztem Teil: Dateidaten anhängen
                if ($i === count($parts) - 1) {
                    // WICHTIG: Nur als 'file' setzen wenn es noch kein Ordner mit Kindern ist
                    if (empty($current[$part]['children'])) {
                        $current[$part]['type'] = 'file';
                    } else {
                        // Wenn bereits Kinder vorhanden sind, ist es ein Ordner mit index.md
                        // In diesem Fall die Datei als spezielle Eigenschaft des Ordners speichern
                        $current[$part]['index_file'] = $file;
                        $current[$part]['type'] = 'folder'; // Bleibt ein Ordner
                    }
                    $current[$part]['file_data'] = $file;
                } else {
                    // Nur als Ordner setzen wenn es noch keine Datei ist
                    if ($current[$part]['type'] !== 'file' || !empty($current[$part]['children'])) {
                        $current[$part]['type'] = 'folder';
                    }
                    $current = &$current[$part]['children'];
                }
            }
        }
        
        // Ordner und Dateien getrennt sortieren
        $this->sortFileTree($tree);
        
        return $tree;
    }
    
    /**
     * Sortiert Dateibaum: Ordner zuerst, dann Dateien (beide alphabetisch)
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
            
            // Ordner zuerst
            if ($typeA !== $typeB) {
                return $typeA === 'folder' ? -1 : 1;
            }
            
            // Gleicher Typ: alphabetisch
            return strcasecmp($a, $b);
        });
    }    /**
     * Berechnet Festplattenverbrauch
     */
    private function calculateDiskUsage(): string
    {
        $contentDir = $this->config['paths']['content'];
        $size = $this->getDirSize($contentDir);
        
        return $this->formatBytes($size);
    }
    
    /**
     * Berechnet die Größe des /public/ Verzeichnisses
     */
    private function calculatePublicSize(): string
    {
        $publicDir = $this->config['paths']['public'];
        $size = $this->getDirSize($publicDir);
        
        return $this->formatBytes($size);
    }

    /**
     * Rekursive Verzeichnisgröße berechnen
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
     * Formatiert Bytes als lesbare Größe
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
     * Sucht nach der entsprechenden Content-Datei (wie in ContentLoader)
     */
    private function findContentFile(string $route): ?string
    {
        $contentDir = $this->config['paths']['content'];
        $extension = $this->config['markdown']['file_extension'];
        
        // Mögliche Pfade ausprobieren
        $possiblePaths = [
            $contentDir . '/' . $route . $extension,
            $contentDir . '/' . $route . '/index' . $extension,
            $contentDir . '/' . $route . '/page' . $extension
        ];
        
        // Für root "/" auch index.md und home.md prüfen
        if ($route === 'index') {
            array_unshift($possiblePaths, $contentDir . '/index' . $extension);
            array_push($possiblePaths, $contentDir . '/home' . $extension);
        }
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path) && is_readable($path)) {
                return $path;
            }
        }
        
        return null;
    }

    /**
     * Bereinigt Dateinamen für Sicherheit
     */
    private function sanitizeFilename(string $filename): string
    {
        // Nur erlaubte Zeichen: a-z, A-Z, 0-9, -, _, /
        $filename = preg_replace('/[^a-zA-Z0-9\-_\/]/', '', $filename);
        
        // Mehrfache Slashes entfernen
        $filename = preg_replace('/\/+/', '/', $filename);
        
        // Führende und abschließende Slashes entfernen
        $filename = trim($filename, '/');
        
        // Path-Traversal verhindern
        $filename = str_replace(['..', './'], '', $filename);
        
        return $filename;
    }

    /**
     * Zeigt Einstellungsseite
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
     * Speichert Einstellungen
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
     * Lädt Einstellungen aus Datei
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
     * Speichert Einstellungen in Datei
     */
    private function saveSettingsToFile(array $settings): bool
    {
        $settingsFile = $this->config['paths']['system'] . '/settings.json';
        
        $json = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        return file_put_contents($settingsFile, $json) !== false;
    }

    /**
     * Parst Navigation-Reihenfolge aus Eingabe
     */
    private function parseNavigationOrder(string $input): array
    {
        $order = [];
        $lines = explode("\n", trim($input));
        $priority = 1;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Format: "section" oder "section:priority"
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
     * Behandelt Bild-Upload für Editor (AJAX)
     */
    private function handleImageUpload(): void
    {
        $this->auth->requireLogin();
        header('Content-Type: application/json; charset=utf-8');

        // Nur POST erlauben
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            exit;
        }

        // Prüfe ob Datei vorhanden ist
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'error' => 'No file uploaded']);
            exit;
        }

        $file = $_FILES['image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            echo json_encode(['success' => false, 'error' => 'Invalid file type']);
            exit;
        }

        // Zielverzeichnis
        $uploadDir = $this->config['paths']['public'] . '/images'; // Physisch
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Sicheren Dateinamen generieren
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $baseName = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($file['name'], PATHINFO_FILENAME));
        $filename = $baseName . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(2)) . '.' . $ext;
        $targetPath = $uploadDir . '/' . $filename;

        // Datei verschieben
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Rückgabe: nur Dateiname für Editor-Tag
            echo json_encode(['success' => true, 'filename' => $filename]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Upload failed']);
        }
        exit;
    }

    /**
     * Validiert Robots-Policy-Wert
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
     * Berechnet Statistiken für das Backup (Anzahl Dateien und Größe)
     */
    private function calculateBackupStats(): array
    {
        $totalFiles = 0;
        $totalSize = 0;
        
        // Content-Ordner analysieren
        $contentPath = $this->config['paths']['content'];
        if (is_dir($contentPath)) {
            $stats = $this->analyzeDirectory($contentPath);
            $totalFiles += $stats['files'];
            $totalSize += $stats['size'];
        }
        
        // System-Einstellungen
        $systemPath = $this->config['paths']['system'];
        if (file_exists($systemPath . '/settings.json')) {
            $totalFiles++;
            $totalSize += filesize($systemPath . '/settings.json');
        }
        
        // Config-Datei
        $configPath = __DIR__ . '/../../config.php';
        if (file_exists($configPath)) {
            $totalFiles++;
            $totalSize += filesize($configPath);
        }
        
        // Themes analysieren
        $themesPath = $this->config['paths']['themes'];
        if (is_dir($themesPath)) {
            $stats = $this->analyzeDirectory($themesPath);
            $totalFiles += $stats['files'];
            $totalSize += $stats['size'];
        }
        
        // Public Assets analysieren
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
     * Analysiert ein Verzeichnis rekursiv und gibt Datei-Anzahl und Größe zurück
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
                    // Bestimmte Dateien ausschließen
                    $filename = $file->getFilename();
                    if (in_array($filename, ['.DS_Store', 'Thumbs.db', '.gitignore'])) {
                        continue;
                    }
                    
                    $files++;
                    $size += $file->getSize();
                }
            }
        } catch (\Exception $e) {
            // Bei Fehlern einfach 0 zurückgeben
            return ['files' => 0, 'size' => 0];
        }
        
        return ['files' => $files, 'size' => $size];
    }
    
    /**
     * Erstellt ein Backup aller wichtigen Dateien
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
            // Prüfe ob ZIP-Extension verfügbar ist
            if (!class_exists('\ZipArchive')) {
                throw new \Exception('ZIP-Extension ist nicht verfügbar. Bitte installieren Sie php-zip.');
            }
            
            // Backup-Dateiname mit Zeitstempel
            $timestamp = date('Y-m-d_H-i-s');
            $backupFilename = "staticmd_backup_{$timestamp}.zip";
            $backupPath = sys_get_temp_dir() . '/' . $backupFilename;
            
            // ZIP-Archiv erstellen
            $zip = new \ZipArchive();
            if ($zip->open($backupPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
                throw new \Exception('Konnte ZIP-Archiv nicht erstellen');
            }
            
            // Content-Ordner hinzufügen
            $this->addDirectoryToZip($zip, $this->config['paths']['content'], 'content/');
            
            // System-Einstellungen hinzufügen
            $systemPath = $this->config['paths']['system'];
            if (file_exists($systemPath . '/settings.json')) {
                $zip->addFile($systemPath . '/settings.json', 'system/settings.json');
            }
            
            // Config-Datei hinzufügen (ohne Passwort-Hash aus Sicherheitsgründen)
            $this->addConfigToZip($zip);
            
            // Themes hinzufügen
            $this->addDirectoryToZip($zip, $this->config['paths']['themes'], 'system/themes/');
            
            // Public Assets hinzufügen
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
            
            // README für Backup hinzufügen
            $readme = $this->generateBackupReadme($timestamp);
            $zip->addFromString('README.md', $readme);
            
            $zip->close();
            
            // Backup zum Download anbieten
            if (file_exists($backupPath)) {
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . $backupFilename . '"');
                header('Content-Length: ' . filesize($backupPath));
                header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
                header('Pragma: no-cache');
                
                readfile($backupPath);
                unlink($backupPath); // Temporäre Datei löschen
                exit;
            } else {
                throw new \Exception('Backup-Datei konnte nicht erstellt werden');
            }
            
        } catch (\Exception $e) {
            error_log('Backup creation failed: ' . $e->getMessage());
            header('Location: /admin?action=settings&error=backup_failed&message=' . urlencode($e->getMessage()));
            exit;
        }
    }
    
    /**
     * Fügt ein Verzeichnis rekursiv zum ZIP-Archiv hinzu
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
                // Bestimmte Dateien ausschließen
                $filename = $file->getFilename();
                if (in_array($filename, ['.DS_Store', 'Thumbs.db', '.gitignore'])) {
                    continue;
                }
                
                $zip->addFile($filePath, $relativePath);
            }
        }
    }
    
    /**
     * Fügt eine bereinigte Config-Datei zum ZIP hinzu
     */
    private function addConfigToZip(\ZipArchive $zip): void
    {
        $configPath = __DIR__ . '/../../config.php';
        if (!file_exists($configPath)) {
            return;
        }
        
        $configContent = file_get_contents($configPath);
        
        // Passwort-Hash durch Platzhalter ersetzen
        $configContent = preg_replace(
            "/('password'\s*=>\s*')[^']*(')/",
            "$1*** REMOVED FOR SECURITY ***$2",
            $configContent
        );
        
        $zip->addFromString('config.php', $configContent);
    }
    
    /**
     * Generiert eine README-Datei für das Backup
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
}
