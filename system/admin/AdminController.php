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
                $error = 'Ungültige Anmeldedaten oder CSRF-Token.';
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
        
        // Statistiken berechnen
        $stats = [
            'total_files' => count($allFiles),
            'recent_files' => array_slice($allFiles, -5),
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
            
            // Direkt den Dateipfad finden
            $contentPath = $this->findContentFile($file);
            
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
            header('Location: /admin?message=saved');
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
        
        // Nur POST-Requests erlauben für Sicherheit
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin?error=invalid_request');
            exit;
        }
        
        $file = $_POST['file'] ?? '';
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        if (!$this->auth->verifyCSRFToken($csrfToken)) {
            header('Location: /admin?error=csrf_invalid');
            exit;
        }
        
        if (empty($file)) {
            header('Location: /admin?error=no_file');
            exit;
        }
        
        // Sicherheit: Pfad-Traversal verhindern
        $file = basename($file);
        if (strpos($file, '..') !== false || strpos($file, '/') !== false) {
            header('Location: /admin?error=invalid_file');
            exit;
        }
        
        $filePath = $this->config['paths']['content'] . '/' . $file . '.md';
        
        // Prüfen ob Datei existiert und löschbar ist
        if (!file_exists($filePath)) {
            header('Location: /admin?error=file_not_found');
            exit;
        }
        
        if (!is_writable(dirname($filePath))) {
            header('Location: /admin?error=no_permission');
            exit;
        }
        
        // Datei löschen
        if (unlink($filePath)) {
            header('Location: /admin?message=deleted');
        } else {
            header('Location: /admin?error=delete_failed');
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
}