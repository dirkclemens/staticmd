<?php

namespace StaticMD\Core;

/**
 * Security Headers Klasse
 * Verwaltet Security-Header wie CSP, HSTS, etc.
 */
class SecurityHeaders
{
    /**
     * Setzt Content-Security-Policy Header
     */
    public static function setCSP(string $context = 'frontend'): void
    {
        $csp = self::getCSP($context);
        header('Content-Security-Policy: ' . $csp);
    }

    /**
     * Generiert CSP-Direktiven basierend auf Kontext
     * um nur Bilder der eigenen Domain zu erlauben, 
     * muss "img-src 'self' data: blob:" sein 
     * und darf nicht "img-src 'self' data: blob: https: http:" enthalten.
     */
    private static function getCSP(string $context): string
    {
        $basePolicy = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
            "font-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com data:",
            "img-src 'self' data: blob: https: http:",
            "connect-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
            "media-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
            "upgrade-insecure-requests"
        ];

        // Admin-specific adjustments
        if ($context === 'admin') {
            // CodeMirror requires eval() for syntax highlighting
            $basePolicy[1] = "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com";
            
            // Admin may need forms for file upload
            $basePolicy[] = "child-src 'self'";
        }

        return implode('; ', $basePolicy);
    }

    /**
     * Setzt alle Security-Header
     */
    public static function setAllSecurityHeaders(string $context = 'frontend'): void
    {
        // Content-Security-Policy
        self::setCSP($context);

        // X-Frame-Options (Defense in Depth to CSP frame-ancestors)
        header('X-Frame-Options: DENY');

        // X-Content-Type-Options
        header('X-Content-Type-Options: nosniff');

        // X-XSS-Protection (Legacy, but still useful for older browsers)
        header('X-XSS-Protection: 1; mode=block');

        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // HTTPS connection: Strict-Transport-Security (only set with HTTPS)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }

        // Permissions Policy (ersetzt Feature-Policy)
        header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
    }

    /**
     * Hilfsfunktion zum Generieren von Nonces für Inline-Scripts
     */
    public static function generateNonce(): string
    {
        return base64_encode(random_bytes(16));
    }

    /**
     * CSP-Nonce für Templates verfügbar machen
     */
    public static function getNonce(): string
    {
        static $nonce = null;
        if ($nonce === null) {
            $nonce = self::generateNonce();
        }
        return $nonce;
    }
}