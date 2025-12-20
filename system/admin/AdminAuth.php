<?php

namespace StaticMD\Admin;

/**
 * Admin Authentication Handler
 * 
 * Manages login/logout operations and session management with CSRF protection.
 * Implements security features like session timeout and brute-force prevention.
 */
class AdminAuth
{
    private const BRUTE_FORCE_DELAY_MICROSECONDS = 500000; // 0.5 seconds
    private const REMEMBER_ME_COOKIE_NAME = 'staticmd_remember_me';
    private const REMEMBER_ME_COOKIE_LIFETIME = 365 * 24 * 60 * 60; // 1 Jahr
    
    private array $config;

    /**
     * Constructor
     * 
     * @param array $config Application configuration array
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Check if user is currently logged in and session is valid
     * 
     * Validates both session existence and timeout constraints.
     * Implements sliding session - extends timeout on each activity.
     * 
     * @return bool True if user is authenticated and session is valid
     */
    public function isLoggedIn(): bool
    {
        // Prüfe zuerst Session
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            // Wenn keine Session, prüfe Remember-Me Cookie
            if ($this->checkRememberMeCookie()) {
                return true; // Cookie hat Session wiederhergestellt
            }
            return false;
        }
        
        if (!isset($_SESSION['admin_login_time'])) {
            return false;
        }
        
        // Prüfe Timeout nur wenn "Remember Me" nicht aktiv ist
        if (!isset($_SESSION['admin_remember_me']) || $_SESSION['admin_remember_me'] !== true) {
            // Prüfe Timeout basierend auf letzter Aktivität (nicht Login-Zeit)
            $lastActivity = $_SESSION['admin_last_activity'] ?? $_SESSION['admin_login_time'];
            $timeout = $this->config['admin']['session_timeout'] ?? 3600;
            
            if ((time() - $lastActivity) >= $timeout) {
                $this->logout();
                return false;
            }
        }
        
        // Session bei Aktivität verlängern (sliding session)
        $_SESSION['admin_last_activity'] = time();
        
        return true;
    }

    /**
     * Attempt user login with provided credentials
     * 
     * Validates credentials against configured username/password hash.
     * Implements brute-force protection via delay on failed attempts.
     * Regenerates session ID on successful login for security.
     * 
     * @param string $username Username to authenticate
     * @param string $password Plain-text password to verify
     * @return bool True if login successful, false otherwise
     */
    public function login(string $username, string $password, bool $rememberMe = false): bool
    {
        $validUsername = $this->config['admin']['username'];
        $validPasswordHash = $this->config['admin']['password'];

        if ($username === $validUsername && password_verify($password, $validPasswordHash)) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            $_SESSION['admin_login_time'] = time();
            $_SESSION['admin_last_activity'] = time();
            $_SESSION['admin_remember_me'] = $rememberMe;
            
            // Regenerate session ID to prevent session fixation attacks
            session_regenerate_id(true);
            
            // Setze Remember-Me Cookie wenn gewünscht
            if ($rememberMe) {
                $this->setRememberMeCookie($username);
            }
            
            return true;
        }

        // Failed login - add delay for brute-force protection
        usleep(self::BRUTE_FORCE_DELAY_MICROSECONDS);
        
        return false;
    }

    /**
     * Log out current user and destroy session
     * 
     * Clears session data and removes session cookie.
     */
    public function logout(): void
    {
        $_SESSION = [];
        
        // Delete session cookie if cookie-based sessions are enabled
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Lösche Remember-Me Cookie
        $this->deleteRememberMeCookie();
        
        session_destroy();
    }

    /**
     * Require valid login or redirect to login page
     * 
     * Checks authentication status and redirects to login if not authenticated.
     * Activity tracking is handled by isLoggedIn() to avoid duplication.
     */
    public function requireLogin(): void
    {
        if (!$this->isLoggedIn()) {
            header('Location: /admin?action=login');
            exit;
        }
    }

    /**
     * Generate or retrieve existing CSRF token
     * 
     * Creates a new token if none exists in session.
     * 
     * @return string CSRF token string
     */
    public function generateCSRFToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify provided CSRF token matches session token
     * 
     * Uses timing-safe comparison to prevent timing attacks.
     * 
     * @param string $token Token to verify
     * @return bool True if token is valid, false otherwise
     */
    public function verifyCSRFToken(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Get current admin username from session
     * 
     * @return string|null Username if logged in, null otherwise
     */
    public function getUsername(): ?string
    {
        return $_SESSION['admin_username'] ?? null;
    }

    /**
     * Calculate remaining session time before timeout
     * 
     * @return int Remaining seconds, 0 if not logged in
     */
    public function getTimeRemaining(): int
    {
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            return 0;
        }
        
        $lastActivity = $_SESSION['admin_last_activity'] ?? $_SESSION['admin_login_time'] ?? time();
        $timeout = $this->config['admin']['session_timeout'] ?? 3600;
        $elapsed = time() - $lastActivity;
        
        return max(0, $timeout - $elapsed);
    }

    /**
     * Get detailed session information for debugging
     * 
     * Returns comprehensive session state including timestamps and timeout info.
     * 
     * @return array Session information array, empty if not logged in
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
            'elapsed_since_activity' => $currentTime - $lastActivity,
            'remember_me' => $_SESSION['admin_remember_me'] ?? false
        ];
    }

    /**
     * Setze Remember-Me Cookie mit sicherem Token
     * 
     * @param string $username Username für Cookie
     */
    private function setRememberMeCookie(string $username): void
    {
        // Generiere sicheren Token
        $token = bin2hex(random_bytes(32));
        $cookieValue = base64_encode(json_encode([
            'username' => $username,
            'token' => $token,
            'created' => time()
        ]));
        
        // Speichere Token-Hash in Session für spätere Validierung
        $_SESSION['admin_remember_token'] = hash('sha256', $token);
        
        // Setze Cookie (1 Jahr Gültigkeit)
        setcookie(
            self::REMEMBER_ME_COOKIE_NAME,
            $cookieValue,
            [
                'expires' => time() + self::REMEMBER_ME_COOKIE_LIFETIME,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'httponly' => true,
                'samesite' => 'Lax'
            ]
        );
    }

    /**
     * Prüfe und validiere Remember-Me Cookie
     * 
     * @return bool True wenn Cookie gültig und Session wiederhergestellt
     */
    private function checkRememberMeCookie(): bool
    {
        if (!isset($_COOKIE[self::REMEMBER_ME_COOKIE_NAME])) {
            return false;
        }
        
        try {
            $cookieData = json_decode(base64_decode($_COOKIE[self::REMEMBER_ME_COOKIE_NAME]), true);
            
            if (!$cookieData || !isset($cookieData['username'], $cookieData['token'], $cookieData['created'])) {
                $this->deleteRememberMeCookie();
                return false;
            }
            
            // Validiere Username
            $validUsername = $this->config['admin']['username'];
            if ($cookieData['username'] !== $validUsername) {
                $this->deleteRememberMeCookie();
                return false;
            }
            
            // Cookie ist gültig - stelle Session wieder her
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $cookieData['username'];
            $_SESSION['admin_login_time'] = time();
            $_SESSION['admin_last_activity'] = time();
            $_SESSION['admin_remember_me'] = true;
            $_SESSION['admin_remember_token'] = hash('sha256', $cookieData['token']);
            
            // Regenerate session ID
            session_regenerate_id(true);
            
            // Erneuere Cookie
            $this->setRememberMeCookie($cookieData['username']);
            
            return true;
        } catch (\Exception $e) {
            $this->deleteRememberMeCookie();
            return false;
        }
    }

    /**
     * Lösche Remember-Me Cookie
     */
    private function deleteRememberMeCookie(): void
    {
        if (isset($_COOKIE[self::REMEMBER_ME_COOKIE_NAME])) {
            setcookie(
                self::REMEMBER_ME_COOKIE_NAME,
                '',
                [
                    'expires' => time() - 3600,
                    'path' => '/',
                    'domain' => '',
                    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]
            );
            unset($_COOKIE[self::REMEMBER_ME_COOKIE_NAME]);
        }
    }
}