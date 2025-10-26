<?php
/**
 * StaticMD - Konfigurationsdatei
 */

return [
    // System Einstellungen
    'system' => [
        'name' => 'StaticMD',
        'version' => '1.0.0',
        'timezone' => 'Europe/Berlin',
        'charset' => 'UTF-8'
    ],
    
    // Verzeichnis-Struktur
    'paths' => [
        'content' => __DIR__ . '/content',
        'system' => __DIR__ . '/system',
        'themes' => __DIR__ . '/system/themes',
        'admin' => __DIR__ . '/system/admin',
        'public' => __DIR__ . '/public'
    ],
    
    // Admin Konfiguration
    'admin' => [
        'username' => 'dirk',
        'password' => '$2y$10$/lqmtxQzJatB6r5/lEQGf.6McMaRpwNCCiF0QTep3jQlewqW1JO9G',
        'session_timeout' => 28800
    ],
    
    // Theme Einstellungen
    'theme' => [
        'default' => 'bootstrap',
        'template_extension' => '.php'
    ],
    
    // Markdown Einstellungen
    'markdown' => [
        'file_extension' => '.md',
        'auto_line_breaks' => true,
        'markup_escaped' => false
    ],
    
    // URL Einstellungen
    'url' => [
        'clean_urls' => true,
        'admin_path' => '/admin'
    ]
];