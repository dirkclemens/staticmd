<?php
/**
 * robots.txt Generator für StaticMD
 * Generiert dynamische robots.txt basierend auf den SEO-Settings
 */

// Config und Settings laden
$config = require_once __DIR__ . '/config.php';
$settingsFile = $config['paths']['system'] . '/settings.json';
$settings = [];
if (file_exists($settingsFile)) {
    $settings = json_decode(file_get_contents($settingsFile), true) ?: [];
}

// Content-Type für robots.txt setzen
header('Content-Type: text/plain; charset=utf-8');

// Cache-Headers (robots.txt sollte cachebar sein)
header('Cache-Control: public, max-age=86400'); // 24 Stunden
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');

// robots.txt Inhalt generieren
function generateRobotsTxt($settings): string {
    $robotsTxt = [];
    
    // Header-Kommentar
    $robotsTxt[] = "# robots.txt für StaticMD";
    $robotsTxt[] = "# Generiert am: " . date('Y-m-d H:i:s T');
    $robotsTxt[] = "";
    
    // SEO-Settings auswerten
    $blockCrawlers = $settings['seo_block_crawlers'] ?? false;
    $generateRobotsTxt = $settings['seo_generate_robots_txt'] ?? true;
    
    if (!$generateRobotsTxt) {
        // Einfache Allow-All robots.txt
        $robotsTxt[] = "User-agent: *";
        $robotsTxt[] = "Allow: /";
        $robotsTxt[] = "";
        return implode("\n", $robotsTxt);
    }
    
    if ($blockCrawlers) {
        // Kompletter Crawling-Block
        $robotsTxt[] = "User-agent: *";
        $robotsTxt[] = "Disallow: /";
        $robotsTxt[] = "";
        $robotsTxt[] = "# Zusätzlich spezifische Bots blockieren";
        
        $blockedBots = [
            'Googlebot', 'Bingbot', 'Slurp', 'DuckDuckBot', 
            'Baiduspider', 'YandexBot', 'facebookexternalhit',
            'Twitterbot', 'LinkedInBot', 'WhatsApp', 'Applebot'
        ];
        
        foreach ($blockedBots as $bot) {
            $robotsTxt[] = "User-agent: $bot";
            $robotsTxt[] = "Disallow: /";
            $robotsTxt[] = "";
        }
    } else {
        // Standard-SEO-freundliche robots.txt
        $robotsTxt[] = "User-agent: *";
        $robotsTxt[] = "Allow: /";
        $robotsTxt[] = "";
        
        // Standardmäßig blockierte Verzeichnisse
        $robotsTxt[] = "# System-Verzeichnisse blockieren";
        $robotsTxt[] = "Disallow: /system/";
        $robotsTxt[] = "Disallow: /admin/";
        $robotsTxt[] = "Disallow: /config.php";
        $robotsTxt[] = "Disallow: /*.json$";
        $robotsTxt[] = "Disallow: /csp-test.php";
        $robotsTxt[] = "Disallow: /assets-security-test.php";
        $robotsTxt[] = "";
        
        // Spezifische Bot-Regelungen
        $robotsTxt[] = "# Spezielle Bot-Regelungen";
        $robotsTxt[] = "User-agent: Googlebot";
        $robotsTxt[] = "Allow: /public/";
        $robotsTxt[] = "Allow: /assets.php";
        $robotsTxt[] = "Disallow: /system/";
        $robotsTxt[] = "";
        
        $robotsTxt[] = "User-agent: Bingbot";
        $robotsTxt[] = "Allow: /public/";
        $robotsTxt[] = "Crawl-delay: 2";
        $robotsTxt[] = "";
        
        // Aggressive Bots beschränken
        $robotsTxt[] = "# Aggressive Bots beschränken";
        $aggressiveBots = [
            'AhrefsBot', 'MJ12bot', 'DotBot', 'BLEXBot', 
            'SemrushBot', 'LinkpadBot', 'DataForSeoBot'
        ];
        
        foreach ($aggressiveBots as $bot) {
            $robotsTxt[] = "User-agent: $bot";
            $robotsTxt[] = "Crawl-delay: 10";
            $robotsTxt[] = "Disallow: /";
            $robotsTxt[] = "";
        }
    }
    
    // Sitemap-Hinweis (falls vorhanden)
    $sitemapPath = __DIR__ . '/sitemap.xml';
    if (file_exists($sitemapPath)) {
        $baseUrl = getBaseUrl();
        $robotsTxt[] = "Sitemap: $baseUrl/sitemap.xml";
        $robotsTxt[] = "";
    }
    
    // Zusätzliche Hinweise
    if (!$blockCrawlers) {
        $robotsTxt[] = "# Empfohlene Crawl-Delays";
        $robotsTxt[] = "User-agent: *";
        $robotsTxt[] = "Crawl-delay: 1";
        $robotsTxt[] = "";
    }
    
    $robotsTxt[] = "# Ende der robots.txt";
    
    return implode("\n", $robotsTxt);
}

// Hilfsfunktion für Base-URL
function getBaseUrl(): string {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptName = dirname($_SERVER['SCRIPT_NAME']);
    return $protocol . '://' . $host . rtrim($scriptName, '/');
}

// robots.txt ausgeben
echo generateRobotsTxt($settings);

// Optional: Logging für Monitoring
error_log('robots.txt accessed - Block Crawlers: ' . ($settings['seo_block_crawlers'] ?? false ? 'true' : 'false'));
?>