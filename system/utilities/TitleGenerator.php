<?php

namespace StaticMD\Utilities;

use StaticMD\Core\I18n;

/**
 * TitleGenerator
 * Generiert lesbare Titel aus Routes und Dateinamen
 */
class TitleGenerator
{
    /**
     * Generiert einen Titel aus einer Route
     * 
     * @param string $route Die Route (z.B. "blog/my-first-post")
     * @param string $fallback Fallback-Titel falls Route leer ist
     * @return string Generierter Titel
     */
    public static function fromRoute(string $route, string $fallback = ''): string
    {
        if (empty($route) || $route === 'index') {
            return $fallback ?: I18n::t('core.unnamed_page');
        }
        
        // Route zu lesbarem Titel konvertieren
        $title = str_replace(['/', '-', '_'], ' ', $route);
        return trim($title);
    }
    
    /**
     * Generiert einen Titel aus einem Dateinamen
     * 
     * @param string $filename Der Dateiname (mit oder ohne Extension)
     * @return string Generierter Titel
     */
    public static function fromFilename(string $filename): string
    {
        // Extension entfernen falls vorhanden
        $basename = pathinfo($filename, PATHINFO_FILENAME);
        
        // Konvertiere zu lesbarem Titel
        $title = str_replace(['-', '_'], ' ', $basename);
        return trim($title);
    }
    
    /**
     * Generiert einen Ordner-Titel aus einem Pfad
     * 
     * @param string $path Der Ordner-Pfad
     * @return string Generierter Titel
     */
    public static function fromFolderPath(string $path): string
    {
        $basename = basename($path);
        
        if (empty($basename) || $basename === '/' || $basename === '.') {
            return 'Home';
        }
        
        // Konvertiere zu lesbarem Titel
        $title = str_replace(['-', '_'], ' ', $basename);
        return trim($title);
    }
    
    /**
     * Bereinigt und formatiert einen Titel
     * 
     * @param string $title Der zu bereinigende Titel
     * @return string Bereinigter Titel
     */
    public static function cleanup(string $title): string
    {
        // Entferne mehrfache Leerzeichen
        $title = preg_replace('/\s+/', ' ', $title);
        
        // Trimme Leerzeichen
        $title = trim($title);
        
        return $title;
    }
}
