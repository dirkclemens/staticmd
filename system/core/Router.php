<?php

namespace StaticMD\Core;

use Normalizer;

/**
 * Router-Klasse
 * Verarbeitet URLs und bestimmt die Route
 */
class Router
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Ermittelt die aktuelle Route aus der URL
     */
    public function getRoute(): string
    {
        // Determine route from GET parameter or REQUEST_URI
        $route = $_GET['route'] ?? '';
        
        if (empty($route)) {
            // Fallback to REQUEST_URI
            $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
            
            // Remove script name (if present)
            $scriptName = dirname($_SERVER['SCRIPT_NAME']);
            if ($scriptName !== '/' && strpos($requestUri, $scriptName) === 0) {
                $requestUri = substr($requestUri, strlen($scriptName));
            }
            
            $route = trim($requestUri, '/');
            
            // Remove query string
            if (($pos = strpos($route, '?')) !== false) {
                $route = substr($route, 0, $pos);
            }
        }
        
        // Special search route (check before cleanup)
        if ($route === 'search') {
            return 'search';
        }
        
        // Special tag route (check before cleanup)
        if ($route === 'tag') {
            return 'tag'; // Tag overview
        }
        if (str_starts_with($route, 'tag/')) {
            // Clean up tag route
            return $this->sanitizeRoute($route);
        }

        // Empty route = homepage
        if (empty($route)) {
            return 'index';
        }

        // URL decoding and cleanup for normal routes
        return $this->sanitizeRoute($route);
    }

    /**
     * Bereinigt eine Route für Sicherheit
     */
    private function sanitizeRoute(string $route): string
    {
        // URL decode - multiple times for combined Unicode characters
        $route = urldecode($route);
        $route = urldecode($route); // Double decoding for %CC%88 etc.
        
        // Normalize Unicode (NFD to NFC) - combined characters to single ones
        if (class_exists('Normalizer') && function_exists('normalizer_normalize')) {
            $route = normalizer_normalize($route, Normalizer::FORM_C);
        } else {
            // Simple fallback normalization
            $route = $this->simpleUnicodeNormalize($route);
        }
        
        // Allowed characters: Unicode letters, numbers, -, _, /, . (for file extensions)
        // Umlauts and other Unicode characters are allowed
        $route = preg_replace('/[^\p{L}\p{N}\-_\/\.]/u', '', $route);
        
        // Remove multiple slashes
        $route = preg_replace('/\/+/', '/', $route);
        
        // Remove leading and trailing slashes
        $route = trim($route, '/');
        
        // Prevent path traversal - extended check for deep nesting
        $route = str_replace(['..', './'], '', $route);
        
        // Check maximum nesting depth (security measure)
        $maxDepth = 10; // Maximal 10 Ebenen tief
        $parts = explode('/', $route);
        if (count($parts) > $maxDepth) {
            // Too deep nesting: use only the first levels
            $route = implode('/', array_slice($parts, 0, $maxDepth));
        }
        
        // Remove empty path parts (created by multiple slashes)
        $parts = array_filter($parts, function($part) {
            return !empty(trim($part));
        });
        $route = implode('/', $parts);

        return $route;
    }
    
    /**
     * Validiert eine Route für tiefe Verschachtelung
     */
    public function validateRoute(string $route): array
    {
        $issues = [];
        $parts = explode('/', trim($route, '/'));
        
        // Check nesting depth
        if (count($parts) > 10) {
            $issues[] = 'Route zu tief verschachtelt (max. 10 Ebenen)';
        }
        
        // Check individual path parts
        foreach ($parts as $index => $part) {
            if (empty($part)) {
                $issues[] = "Leerer Pfad-Teil an Position " . ($index + 1);
                continue;
            }
            
            if (strlen($part) > 100) {
                $issues[] = "Pfad-Teil zu lang an Position " . ($index + 1) . " (max. 100 Zeichen)";
            }
            
            // Check for problematic characters
            if (preg_match('/[<>:"\\|?*]/', $part)) {
                $issues[] = "Ungültige Zeichen in Pfad-Teil an Position " . ($index + 1);
            }
        }
        
        return [
            'valid' => empty($issues),
            'issues' => $issues,
            'sanitized_route' => $this->sanitizeRoute($route)
        ];
    }

    /**
     * Erstellt eine URL für eine Route
     */
    public function url(string $route = ''): string
    {
        $baseUrl = $this->getBaseUrl();
        
        if ($this->config['url']['clean_urls']) {
            return $baseUrl . '/' . ltrim($route, '/');
        } else {
            return $baseUrl . '/index.php?route=' . urlencode($route);
        }
    }
    
    /**
     * Einfache Unicode-Normalisierung als Fallback
     */
    private function simpleUnicodeNormalize(string $text): string
    {
        // Convert common combined Unicode characters to simple ones
        // Use hex codes for combined characters
        $replacements = [
            "a\xCC\x88" => 'ä',  // ä (a + combining diaeresis)
            "o\xCC\x88" => 'ö',  // ö (o + combining diaeresis)
            "u\xCC\x88" => 'ü',  // ü (u + combining diaeresis)
            "A\xCC\x88" => 'Ä',  // Ä (A + combining diaeresis)
            "O\xCC\x88" => 'Ö',  // Ö (O + combining diaeresis)
            "U\xCC\x88" => 'Ü',  // Ü (U + combining diaeresis)
        ];
        
        foreach ($replacements as $combined => $simple) {
            $text = str_replace($combined, $simple, $text);
        }
        
        return $text;
    }

    /**
     * Ermittelt die Basis-URL
     */
    private function getBaseUrl(): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        
        return $protocol . '://' . $host . rtrim($scriptName, '/');
    }
}