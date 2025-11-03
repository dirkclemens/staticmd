<?php

namespace StaticMD\Admin;

/**
 * Admin-Controller
 * Verarbeitet alle Admin-Anfragen
 */
class AdminController
{
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
            'system_info' => [
                'php_version' => PHP_VERSION,
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'memory_limit' => ini_get('memory_limit')
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
                $meta = [
                    'title' => ucwords(str_replace(['/', '-', '_'], ' ', $file)),
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
            die('CSRF-Token ungültig.');
        }
        
        $file = $_POST['file'] ?? '';
        $content = $_POST['content'] ?? '';
        $meta = $_POST['meta'] ?? [];
        
        if (empty($file)) {
            die('Dateiname ist erforderlich.');
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
     * Zeigt Datei-Manager
     */
    private function showFileManager(): void
    {
        $this->auth->requireLogin();
        
        require_once __DIR__ . '/../core/MarkdownParser.php';
        require_once __DIR__ . '/../core/ContentLoader.php';
        $contentLoader = new \StaticMD\Core\ContentLoader($this->config);
        $allFiles = $contentLoader->listAll();
        
        // Nach Verzeichnis gruppieren
        $filesByDir = [];
        foreach ($allFiles as $file) {
            $dir = dirname($file['route']);
            if ($dir === '.') $dir = 'Root';
            
            if (!isset($filesByDir[$dir])) {
                $filesByDir[$dir] = [];
            }
            $filesByDir[$dir][] = $file;
        }
        
        include __DIR__ . '/templates/files.php';
    }

    /**
     * Berechnet Festplattenverbrauch
     */
    private function calculateDiskUsage(): string
    {
        $contentDir = $this->config['paths']['content'];
        $size = $this->getDirSize($contentDir);
        
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
        
        // Für Startseite
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

        $settings = [
            'site_name' => trim($_POST['site_name'] ?? 'StaticMD'),
            'site_logo' => trim($_POST['site_logo'] ?? ''),
            'frontend_theme' => $_POST['frontend_theme'] ?? 'bootstrap',
            'recent_files_count' => max(5, min(50, (int)($_POST['recent_files_count'] ?? 15))),
            'items_per_page' => max(10, min(100, (int)($_POST['items_per_page'] ?? 25))),
            'editor_theme' => $_POST['editor_theme'] ?? 'github',
            'show_file_stats' => isset($_POST['show_file_stats']),
            'auto_save_interval' => max(30, min(300, (int)($_POST['auto_save_interval'] ?? 60))),
            'navigation_order' => $this->parseNavigationOrder($_POST['navigation_order'] ?? ''),
            'language' => $lang,
            'search_result_limit' => max(10, min(200, (int)($_POST['search_result_limit'] ?? 50)))
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
}
