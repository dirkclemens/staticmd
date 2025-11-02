<?php

namespace StaticMD\Core;

/**
 * Simple i18n helper for translating UI strings.
 * Loads base (English) and overlays selected language.
 */
class I18n
{
    private static string $lang = 'en';
    private static array $translations = [];
    private static string $basePath;

    /**
     * Initialize i18n with a language code and language files path
     */
    public static function init(string $lang, string $langBasePath): void
    {
        self::$basePath = rtrim($langBasePath, '/');
        self::setLanguage($lang);
    }

    /**
     * Set current language and (re)load translations
     */
    public static function setLanguage(string $lang): void
    {
        $lang = strtolower(substr($lang, 0, 5)); // e.g., en, de, en_US
        // normalize to two-letter code for files
        $short = substr($lang, 0, 2);
        self::$lang = $short;

        $enFile = self::$basePath . '/en.php';
        $langFile = self::$basePath . '/' . $short . '.php';

        $base = file_exists($enFile) ? (include $enFile) : [];
        $overlay = file_exists($langFile) ? (include $langFile) : [];

        // Merge with language overlay taking precedence
        self::$translations = self::arrayMergeRecursiveDistinct($base, $overlay);
    }

    /**
     * Get current language code (two-letter)
     */
    public static function getLanguage(): string
    {
        return self::$lang;
    }

    /**
     * Translate a key with optional placeholders
     * Usage: I18n::t('admin.login.title')
     */
    public static function t(string $key, array $placeholders = []): string
    {
        $value = self::getValueByDotKey(self::$translations, $key);
        if (!is_string($value)) {
            // fallback to key when not found
            $value = $key;
        }

        if (!empty($placeholders)) {
            foreach ($placeholders as $k => $v) {
                $value = str_replace('{' . $k . '}', (string)$v, $value);
            }
        }
        return $value;
    }

    private static function getValueByDotKey(array $array, string $key)
    {
        $segments = explode('.', $key);
        foreach ($segments as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return null;
            }
        }
        return $array;
    }

    private static function arrayMergeRecursiveDistinct(array $a, array $b): array
    {
        $merged = $a;
        foreach ($b as $key => $value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = self::arrayMergeRecursiveDistinct($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }
        return $merged;
    }
}
