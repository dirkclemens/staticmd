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
    private const MAX_FAILED_ATTEMPTS = 5;
    private const ATTEMPT_WINDOW_SECONDS = 900; // 15 Minuten
    private const LOCKOUT_SECONDS = 900; // 15 Minuten
    
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
        $clientIp = $this->getClientIp();
        if ($this->isLoginLockedOut($username, $clientIp)) {
            usleep(self::BRUTE_FORCE_DELAY_MICROSECONDS);
            return false;
        }

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
            } else {
                $this->deleteRememberMeCookie();
            }

            $this->clearLoginAttempts($username, $clientIp);
            
            return true;
        }

        // Failed login - add delay for brute-force protection
        $this->recordFailedLogin($username, $clientIp);
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
        $selector = bin2hex(random_bytes(9));
        $validator = bin2hex(random_bytes(32));
        $expires = time() + self::REMEMBER_ME_COOKIE_LIFETIME;

        $tokens = $this->loadRememberMeTokens();
        $tokens[$selector] = [
            'username' => $username,
            'validator_hash' => hash('sha256', $validator),
            'created' => time(),
            'last_used' => time(),
            'expires' => $expires
        ];
        $this->saveRememberMeTokens($tokens);

        $this->setRememberMeCookieValue($selector, $validator, $expires);
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
            $cookieData = $this->decodeRememberMeCookie($_COOKIE[self::REMEMBER_ME_COOKIE_NAME]);

            if (!$cookieData || empty($cookieData['selector']) || empty($cookieData['validator'])) {
                $this->deleteRememberMeCookie();
                return false;
            }

            if (!empty($cookieData['expires']) && (int) $cookieData['expires'] < time()) {
                $this->removeRememberMeToken($cookieData['selector']);
                $this->deleteRememberMeCookie();
                return false;
            }

            $tokens = $this->loadRememberMeTokens();
            $tokenRecord = $tokens[$cookieData['selector']] ?? null;
            if (!$tokenRecord) {
                $this->deleteRememberMeCookie();
                return false;
            }

            if (($tokenRecord['expires'] ?? 0) < time()) {
                $this->removeRememberMeToken($cookieData['selector']);
                $this->deleteRememberMeCookie();
                return false;
            }

            $validUsername = $this->config['admin']['username'];
            if (($tokenRecord['username'] ?? '') !== $validUsername) {
                $this->removeRememberMeToken($cookieData['selector']);
                $this->deleteRememberMeCookie();
                return false;
            }

            $validatorHash = hash('sha256', $cookieData['validator']);
            if (!hash_equals($tokenRecord['validator_hash'] ?? '', $validatorHash)) {
                $this->removeRememberMeToken($cookieData['selector']);
                $this->deleteRememberMeCookie();
                return false;
            }

            // Cookie ist gültig - stelle Session wieder her
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $tokenRecord['username'];
            $_SESSION['admin_login_time'] = time();
            $_SESSION['admin_last_activity'] = time();
            $_SESSION['admin_remember_me'] = true;

            // Regenerate session ID
            session_regenerate_id(true);

            // Erneuere Token + Cookie (Rotation)
            $this->rotateRememberMeToken($cookieData['selector'], $tokenRecord['username']);

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
            $cookieData = $this->decodeRememberMeCookie($_COOKIE[self::REMEMBER_ME_COOKIE_NAME]);
            if (!empty($cookieData['selector'])) {
                $this->removeRememberMeToken($cookieData['selector']);
            }
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

    private function setRememberMeCookieValue(string $selector, string $validator, int $expires): void
    {
        $cookieValue = base64_encode(json_encode([
            'selector' => $selector,
            'validator' => $validator,
            'expires' => $expires
        ]));

        setcookie(
            self::REMEMBER_ME_COOKIE_NAME,
            $cookieValue,
            [
                'expires' => $expires,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'httponly' => true,
                'samesite' => 'Lax'
            ]
        );
    }

    private function decodeRememberMeCookie(string $cookieValue): ?array
    {
        $decoded = base64_decode($cookieValue, true);
        if ($decoded === false) {
            return null;
        }

        $data = json_decode($decoded, true);
        if (!is_array($data)) {
            return null;
        }

        return $data;
    }

    private function rotateRememberMeToken(string $oldSelector, string $username): void
    {
        $tokens = $this->loadRememberMeTokens();
        unset($tokens[$oldSelector]);

        $selector = bin2hex(random_bytes(9));
        $validator = bin2hex(random_bytes(32));
        $expires = time() + self::REMEMBER_ME_COOKIE_LIFETIME;

        $tokens[$selector] = [
            'username' => $username,
            'validator_hash' => hash('sha256', $validator),
            'created' => time(),
            'last_used' => time(),
            'expires' => $expires
        ];

        $this->saveRememberMeTokens($tokens);
        $this->setRememberMeCookieValue($selector, $validator, $expires);
    }

    private function removeRememberMeToken(string $selector): void
    {
        $tokens = $this->loadRememberMeTokens();
        if (isset($tokens[$selector])) {
            unset($tokens[$selector]);
            $this->saveRememberMeTokens($tokens);
        }
    }

    private function loadRememberMeTokens(): array
    {
        $path = $this->getRememberMeTokenStorePath();
        if (!file_exists($path)) {
            return [];
        }

        $data = json_decode(file_get_contents($path), true);
        if (!is_array($data)) {
            return [];
        }

        $now = time();
        foreach ($data as $selector => $record) {
            if (!is_array($record) || ($record['expires'] ?? 0) < $now) {
                unset($data[$selector]);
            }
        }

        return $data;
    }

    private function saveRememberMeTokens(array $tokens): void
    {
        $path = $this->getRememberMeTokenStorePath();
        file_put_contents($path, json_encode($tokens, JSON_PRETTY_PRINT), LOCK_EX);
        @chmod($path, 0600);
    }

    private function getRememberMeTokenStorePath(): string
    {
        $adminPath = rtrim($this->config['paths']['admin'], '/');
        return $adminPath . '/remember_tokens.json';
    }

    private function getClientIp(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    private function getLoginAttemptsPath(): string
    {
        $adminPath = rtrim($this->config['paths']['admin'], '/');
        return $adminPath . '/login_attempts.json';
    }

    private function loadLoginAttempts(): array
    {
        $path = $this->getLoginAttemptsPath();
        if (!file_exists($path)) {
            return [];
        }

        $data = json_decode(file_get_contents($path), true);
        if (!is_array($data)) {
            return [];
        }

        $now = time();
        foreach ($data as $key => $record) {
            $lastAttempt = $record['last_attempt'] ?? 0;
            $lockedUntil = $record['locked_until'] ?? 0;
            if ($lockedUntil > 0 && $lockedUntil >= $now) {
                continue;
            }

            if ($lastAttempt < ($now - (self::ATTEMPT_WINDOW_SECONDS + self::LOCKOUT_SECONDS))) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    private function saveLoginAttempts(array $attempts): void
    {
        $path = $this->getLoginAttemptsPath();
        file_put_contents($path, json_encode($attempts, JSON_PRETTY_PRINT), LOCK_EX);
        @chmod($path, 0600);
    }

    private function getAttemptKey(string $username, string $clientIp): string
    {
        return hash('sha256', $username . '|' . $clientIp);
    }

    private function isLoginLockedOut(string $username, string $clientIp): bool
    {
        $attempts = $this->loadLoginAttempts();
        $key = $this->getAttemptKey($username, $clientIp);
        $record = $attempts[$key] ?? null;
        if (!$record) {
            return false;
        }

        $lockedUntil = $record['locked_until'] ?? 0;
        if ($lockedUntil > time()) {
            return true;
        }

        return false;
    }

    private function recordFailedLogin(string $username, string $clientIp): void
    {
        $attempts = $this->loadLoginAttempts();
        $key = $this->getAttemptKey($username, $clientIp);
        $now = time();

        $record = $attempts[$key] ?? [
            'count' => 0,
            'first_attempt' => $now,
            'last_attempt' => $now,
            'locked_until' => 0
        ];

        if (($now - ($record['first_attempt'] ?? $now)) > self::ATTEMPT_WINDOW_SECONDS) {
            $record['count'] = 0;
            $record['first_attempt'] = $now;
        }

        $record['count'] = ($record['count'] ?? 0) + 1;
        $record['last_attempt'] = $now;

        if ($record['count'] >= self::MAX_FAILED_ATTEMPTS) {
            $record['locked_until'] = $now + self::LOCKOUT_SECONDS;
        }

        $attempts[$key] = $record;
        $this->saveLoginAttempts($attempts);
    }

    private function clearLoginAttempts(string $username, string $clientIp): void
    {
        $attempts = $this->loadLoginAttempts();
        $key = $this->getAttemptKey($username, $clientIp);
        if (isset($attempts[$key])) {
            unset($attempts[$key]);
            $this->saveLoginAttempts($attempts);
        }
    }
}