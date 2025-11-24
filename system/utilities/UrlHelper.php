<?php

namespace StaticMD\Utilities;

/**
 * UrlHelper
 * Hilfs-Funktionen für URL-Encoding und -Handling
 */
class UrlHelper
{
    /**
     * Encodiert URL-Pfade ohne die Slashes zu kodieren
     * 
     * @param string $path Der zu encodierende Pfad
     * @return string Encodierter Pfad mit erhaltenen Slashes
     */
    public static function encodePath(string $path): string
    {
        $parts = explode('/', $path);
        $encodedParts = array_map('rawurlencode', $parts);
        return implode('/', $encodedParts);
    }
    
    /**
     * Bereinigt einen Pfad von gefährlichen Zeichen
     * 
     * @param string $path Der zu bereinigende Pfad
     * @return string Bereinigter Pfad
     */
    public static function sanitizePath(string $path): string
    {
        // Entferne Path-Traversal-Versuche
        $path = str_replace(['..', './'], '', $path);
        
        // Entferne mehrfache Slashes
        $path = preg_replace('/\/+/', '/', $path);
        
        // Entferne führende und nachfolgende Slashes
        $path = trim($path, '/');
        
        return $path;
    }
}
