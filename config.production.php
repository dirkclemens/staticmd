<?php
/**
 * StaticMD - Produktions-Konfiguration
 * WICHTIG: Passen Sie diese Datei für Ihren Server an!
 */

return [
    // System settings
    'system' => [
    'name' => 'StaticMD', // Change the site name
        'version' => '1.0.0',
        'timezone' => 'Europe/Berlin',
        'charset' => 'UTF-8',
    'debug' => false // IMPORTANT: Set to false for production!
    ],
    
    // Directory structure
    'paths' => [
        'content' => __DIR__ . '/content',
        'system' => __DIR__ . '/system',
        'themes' => __DIR__ . '/system/themes',
        'admin' => __DIR__ . '/system/admin',
        'public' => __DIR__ . '/public'
    ],
    
    // Admin configuration - CHANGE THESE VALUES!
    'admin' => [
    'username' => 'admin', // TODO: Choose your own username
    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // TODO: Generate your own hash!
    'session_timeout' => 3600 // 1 hour (adjust as needed)
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
    ],
    
    // Security settings (new for production)
    'security' => [
        'max_login_attempts' => 5,
    'lockout_duration' => 900, // 15 minutes
    'session_cookie_secure' => true, // Only via HTTPS (with SSL/TLS)
        'session_cookie_httponly' => true,
        'csrf_token_lifetime' => 3600
    ],
    
    // Logging (optional)
    'logging' => [
        'enabled' => true,
        'log_file' => __DIR__ . '/logs/staticmd.log',
    'log_level' => 'ERROR' // ERROR, WARNING, INFO, DEBUG
    ]
];