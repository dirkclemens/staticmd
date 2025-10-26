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
        // Route aus GET-Parameter oder REQUEST_URI ermitteln
        $route = $_GET['route'] ?? '';
        
        if (empty($route)) {
            // Fallback auf REQUEST_URI
            $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
            
            // Script-Name entfernen (falls vorhanden)
            $scriptName = dirname($_SERVER['SCRIPT_NAME']);
            if ($scriptName !== '/' && strpos($requestUri, $scriptName) === 0) {
                $requestUri = substr($requestUri, strlen($scriptName));
            }
            
            $route = trim($requestUri, '/');
            
            // Query-String entfernen
            if (($pos = strpos($route, '?')) !== false) {
                $route = substr($route, 0, $pos);
            }
        }
        
        // Spezielle Such-Route (vor Bereinigung prüfen)
        if ($route === 'search') {
            return 'search';
        }
        
        // Spezielle Tag-Route (vor Bereinigung prüfen)  
        if ($route === 'tag') {
            return 'tag'; // Tag-Übersicht
        }
        if (str_starts_with($route, 'tag/')) {
            // Tag-Route bereinigen
            return $this->sanitizeRoute($route);
        }

        // Leere Route = Startseite
        if (empty($route)) {
            return 'index';
        }

        // URL-Dekodierung und Bereinigung für normale Routen
        return $this->sanitizeRoute($route);
    }

    /**
     * Bereinigt eine Route für Sicherheit
     */
    private function sanitizeRoute(string $route): string
    {
        // URL-dekodieren - mehrfach für kombinierte Unicode-Zeichen
        $route = urldecode($route);
        $route = urldecode($route); // Doppelte Dekodierung für %CC%88 usw.
        
        // Unicode normalisieren (NFD zu NFC) - kombinierte Zeichen zu einzelnen
        if (class_exists('Normalizer') && function_exists('normalizer_normalize')) {
            $route = normalizer_normalize($route, Normalizer::FORM_C);
        } else {
            // Einfache Fallback-Normalisierung
            $route = $this->simpleUnicodeNormalize($route);
        }
        
        // Erlaubte Zeichen: Unicode-Buchstaben, Zahlen, -, _, /, . (für Dateierweiterungen)
        // Umlaute und andere Unicode-Zeichen sind erlaubt
        $route = preg_replace('/[^\p{L}\p{N}\-_\/\.]/u', '', $route);
        
        // Mehrfache Slashes entfernen
        $route = preg_replace('/\/+/', '/', $route);
        
        // Führende und abschließende Slashes entfernen
        $route = trim($route, '/');
        
        // Path-Traversal verhindern
        $route = str_replace(['..', './'], '', $route);

        return $route;
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
        // Häufige kombinierte Unicode-Zeichen zu einfachen konvertieren
        // Verwende hex-Codes für kombinierte Zeichen
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