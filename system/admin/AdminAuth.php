<?php

namespace StaticMD\Admin;

/**
 * Admin-Authentifizierung
 * Verwaltet Login/Logout und Session-Management
 */
class AdminAuth
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Überprüft ob User eingeloggt ist
     */
    public function isLoggedIn(): bool
    {
        return isset($_SESSION['admin_logged_in']) 
               && $_SESSION['admin_logged_in'] === true
               && isset($_SESSION['admin_login_time'])
               && (time() - $_SESSION['admin_login_time']) < $this->config['admin']['session_timeout'];
    }

    /**
     * Versucht Login mit Benutzerdaten
     */
    public function login(string $username, string $password): bool
    {
        $validUsername = $this->config['admin']['username'];
        $validPasswordHash = $this->config['admin']['password'];

        if ($username === $validUsername && password_verify($password, $validPasswordHash)) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            $_SESSION['admin_login_time'] = time();
            $_SESSION['admin_last_activity'] = time();
            
            // Regenerate session for security
            session_regenerate_id(true);
            
            return true;
        }

        // Fehlgeschlagener Login - kurz warten (Brute-Force-Schutz)
        usleep(500000); // 0.5 Sekunden
        
        return false;
    }

    /**
     * Loggt User aus
     */
    public function logout(): void
    {
        $_SESSION = [];
        
        // Delete session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
    }

    /**
     * Erzwingt Login-Überprüfung
     */
    public function requireLogin(): void
    {
        if (!$this->isLoggedIn()) {
            header('Location: /admin?action=login');
            exit;
        }
        // Track activity without changing login time
        $_SESSION['admin_last_activity'] = time();
    }

    /**
     * Generiert CSRF-Token
     */
    public function generateCSRFToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }

    /**
     * Verifiziert CSRF-Token
     */
    public function verifyCSRFToken(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Gibt aktuellen Admin-Username zurück
     */
    public function getUsername(): ?string
    {
        return $_SESSION['admin_username'] ?? null;
    }

    /**
     * Gibt verbleibende Session-Zeit zurück (in Sekunden)
     */
    public function getTimeRemaining(): int
    {
        if (!$this->isLoggedIn()) {
            return 0;
        }
        
        $elapsed = time() - $_SESSION['admin_login_time'];
        return max(0, $this->config['admin']['session_timeout'] - $elapsed);
    }

    /**
     * Gibt Session-Informationen für Debug-Zwecke zurück
     */
    public function getSessionInfo(): array
    {
        if (!$this->isLoggedIn()) {
            return [];
        }

        $loginTime = $_SESSION['admin_login_time'] ?? 0;
        $lastActivity = $_SESSION['admin_last_activity'] ?? 0;
        $currentTime = time();
        
        return [
            'login_time' => $loginTime,
            'login_time_formatted' => date('Y-m-d H:i:s', $loginTime),
            'last_activity' => $lastActivity,
            'last_activity_formatted' => date('Y-m-d H:i:s', $lastActivity),
            'current_time' => $currentTime,
            'session_timeout' => $this->config['admin']['session_timeout'],
            'time_remaining' => $this->getTimeRemaining(),
            'elapsed_since_login' => $currentTime - $loginTime,
            'elapsed_since_activity' => $currentTime - $lastActivity
        ];
    }
}