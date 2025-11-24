<?php

namespace StaticMD\Utilities;

/**
 * FrontMatterParser
 * Parst YAML Front Matter aus Markdown-Dateien
 */
class FrontMatterParser
{
    /**
     * Parst Front Matter (YAML-Header) aus Markdown-Datei
     * 
     * @param string $content Der komplette Markdown-Content
     * @return array ['meta' => array, 'content' => string]
     */
    public static function parse(string $content): array
    {
        $meta = [];
        $bodyContent = $content;
        
        // Front Matter erkennen (--- am Anfang)
        if (strpos($content, '---') === 0) {
            $parts = explode('---', $content, 3);
            
            if (count($parts) >= 3) {
                $frontMatter = trim($parts[1]);
                $bodyContent = trim($parts[2]);
                
                // Einfaches Key-Value Parsing (ohne YAML-Dependency)
                $lines = explode("\n", $frontMatter);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line) || strpos($line, ':') === false) {
                        continue;
                    }
                    
                    [$key, $value] = explode(':', $line, 2);
                    $cleanKey = trim($key);
                    $cleanValue = trim($value, ' "\'');
                    
                    // Yellow CMS Compatibility: Key-Mapping
                    $yellowMapping = [
                        'Title' => 'title',
                        'TitleSlug' => 'titleslug', 
                        'Layout' => 'layout',
                        'Tag' => 'tags',
                        'Author' => 'author'
                    ];
                    
                    // Verwende gemappten Key falls vorhanden, sonst Original (lowercase)
                    $mappedKey = $yellowMapping[$cleanKey] ?? strtolower($cleanKey);
                    $meta[$mappedKey] = $cleanValue;
                    
                    // Keep original Yellow keys for compatibility
                    if (array_key_exists($cleanKey, $yellowMapping)) {
                        $meta[$cleanKey] = $cleanValue;
                    }
                }
            }
        }
        
        return [
            'meta' => $meta,
            'content' => $bodyContent
        ];
    }
    
    /**
     * Extrahiert nur Meta-Daten ohne Body-Content
     * 
     * @param string $content Der komplette Markdown-Content
     * @return array Meta-Daten Array
     */
    public static function extractMeta(string $content): array
    {
        $result = self::parse($content);
        return $result['meta'];
    }
    
    /**
     * Extrahiert nur Body-Content ohne Meta-Daten
     * 
     * @param string $content Der komplette Markdown-Content
     * @return string Body-Content ohne Front Matter
     */
    public static function extractContent(string $content): string
    {
        $result = self::parse($content);
        return $result['content'];
    }
}
