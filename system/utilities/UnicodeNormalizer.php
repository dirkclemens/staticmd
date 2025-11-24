<?php

namespace StaticMD\Utilities;

use Normalizer;

/**
 * UnicodeNormalizer
 * Normalisiert Unicode-Zeichen für Dateinamen und URLs
 */
class UnicodeNormalizer
{
    /**
     * Normalisiert Unicode-Text (NFD zu NFC)
     * Kombinierte Zeichen werden zu einzelnen Zeichen
     * 
     * @param string $text Der zu normalisierende Text
     * @return string Normalisierter Text
     */
    public static function normalize(string $text): string
    {
        // Unicode-Normalisierung falls verfügbar
        if (class_exists('Normalizer') && function_exists('normalizer_normalize')) {
            return normalizer_normalize($text, Normalizer::FORM_C);
        }
        
        // Fallback: Einfache Normalisierung für häufige Fälle
        return self::simpleNormalize($text);
    }
    
    /**
     * Einfache Unicode-Normalisierung als Fallback
     * Konvertiert kombinierte Unicode-Zeichen zu einfachen
     * 
     * @param string $text Der zu normalisierende Text
     * @return string Normalisierter Text
     */
    private static function simpleNormalize(string $text): string
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
     * Normalisiert String für Dateinamen-Vergleiche
     * Case-insensitive Normalisierung
     * 
     * @param string $input Der zu normalisierende String
     * @return string Normalisierter lowercase String
     */
    public static function normalizeForComparison(string $input): string
    {
        $normalized = self::normalize($input);
        return mb_strtolower($normalized, 'UTF-8');
    }
    
    /**
     * URL-decodiert mit doppelter Dekodierung für kombinierte Unicode-Zeichen
     * 
     * @param string $text URL-encodierter Text
     * @return string Dekodierter und normalisierter Text
     */
    public static function decodeAndNormalize(string $text): string
    {
        // Double decoding for %CC%88 etc.
        $decoded = urldecode($text);
        $decoded = urldecode($decoded);
        
        return self::normalize($decoded);
    }
}
