<?php
/**
 * StaticMD - Produktions-Konfiguration
 * WICHTIG: Passen Sie diese Datei für Ihren Server an!
 */

return [
    // System Einstellungen
    'system' => [
        'name' => 'StaticMD', // Ändern Sie den Site-Namen
        'version' => '1.0.0',
        'timezone' => 'Europe/Berlin',
        'charset' => 'UTF-8',
        'debug' => false // WICHTIG: Auf false für Produktion!
    ],
    
    // Verzeichnis-Struktur
    'paths' => [
        'content' => __DIR__ . '/content',
        'system' => __DIR__ . '/system',
        'themes' => __DIR__ . '/system/themes',
        'admin' => __DIR__ . '/system/admin',
        'public' => __DIR__ . '/public'
    ],
    
    // Admin Konfiguration - ÄNDERN SIE DIESE WERTE!
    'admin' => [
        'username' => 'admin', // TODO: Eigenen Username wählen
        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // TODO: Eigenen Hash generieren!
        'session_timeout' => 3600 // 1 Stunde (anpassen nach Bedarf)
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
    ],
    
    // Sicherheits-Einstellungen (Neu für Produktion)
    'security' => [
        'max_login_attempts' => 5,
        'lockout_duration' => 900, // 15 Minuten
        'session_cookie_secure' => true, // Nur über HTTPS (bei SSL/TLS)
        'session_cookie_httponly' => true,
        'csrf_token_lifetime' => 3600
    ],
    
    // Logging (Optional)
    'logging' => [
        'enabled' => true,
        'log_file' => __DIR__ . '/logs/staticmd.log',
        'log_level' => 'ERROR' // ERROR, WARNING, INFO, DEBUG
    ]
];