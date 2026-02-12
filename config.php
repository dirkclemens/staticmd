<?php
/**
 * StaticMD - Konfigurationsdatei
 */

return [
    // System settings
    'system' => [
        'name' => 'StaticMD',
        'version' => '1.0.0',
        'timezone' => 'Europe/Berlin',
        'charset' => 'UTF-8',
        'debug' => false
    ],
    
    // Directory structure
    'paths' => [
        'content' => __DIR__ . '/content',
        'system' => __DIR__ . '/system',
        'themes' => __DIR__ . '/system/themes',
        'admin' => __DIR__ . '/system/admin',
        'public' => __DIR__ . '/public',
        // Non-web storage for auth tokens/attempts
        'storage' => __DIR__ . '/storage'
    ],
    
    // Admin configuration
    'admin' => [
        'username' => 'admin',        
        'password' => '$2y$10$RLXuEkGUSVScmHoRGnLrIO7fgKcHU4/9CkBrFdNT46infcMfX.qOm',
        'session_timeout' => 86400 // 24 hours
    ],
    
    // Theme settings
    'theme' => [
        'default' => 'bootstrap',
        'template_extension' => '.php'
    ],
    
    // Markdown settings
    'markdown' => [
        'file_extension' => '.md',
        'auto_line_breaks' => true,
        'markup_escaped' => false
    ],
    
    // URL settings
    'url' => [
        'clean_urls' => true,
        'admin_path' => '/admin'
    ]
];
