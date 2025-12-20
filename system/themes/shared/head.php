<?php
/**
 * Shared HTML Head Section
 * Verwendet von allen Themes und Layouts
 */

// Load settings
$settingsFile = $config['paths']['system'] . '/settings.json';
$settings = [];
if (file_exists($settingsFile)) {
    $settings = json_decode(file_get_contents($settingsFile), true) ?: [];
}
$siteName = $settings['site_name'] ?? $siteName;
$siteLogo = $settings['site_logo'] ?? '';

// Theme helper for navigation
require_once __DIR__ . '/../ThemeHelper.php';
$themeHelper = new \StaticMD\Themes\ThemeHelper($this->contentLoader);
// Navigation erstellen
$navItems = $themeHelper->buildNavigation();

// Navigation ordering from settings
$navigationOrder = $this->contentLoader->getNavigationOrder();
uksort($navItems, function($a, $b) use ($navigationOrder) {
    $orderA = $navigationOrder[$a] ?? 999;
    $orderB = $navigationOrder[$b] ?? 999;
    
    if ($orderA === $orderB) {
        return strcasecmp($a, $b);
    }
    
    return $orderA <=> $orderB;
});

?>
<!DOCTYPE html>
<html lang="de" data-bs-theme="<?= htmlspecialchars($themeMode ?? 'light') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($meta['description'] ?? 'Powered by StaticMD') ?>">
    <meta name="author" content="<?= htmlspecialchars($meta['author'] ?? '') ?>">
    <meta name="route" content="<?= htmlspecialchars($currentRoute ?? '') ?>">
    
    <!-- SEO/Robots Meta-Tags -->
    <?= $robotsMeta ?? '' ?>
    
    <title><?= htmlspecialchars($title) ?> - <?= htmlspecialchars($siteName) ?></title>
    
    <!-- Favicon -->  
    <link rel="icon" type="image/x-icon" href="/assets/favicon.ico">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Highlight.js CSS for Syntax Highlighting -->
    <?php
    // Mapping von Theme zu Highlight.js Styles
    // https://highlightjs.org/demo
    $highlightThemes = [
        'github' => [
            'light' => 'github',
            'dark' => 'github-dark'
        ],
        'solarized' => [
            'light' => 'base16/solarized-light',
            'dark' => 'base16/solarized-dark'
        ],
        'monokai' => [
            'light' => 'base16/monokai',
            'dark' => 'monokai'
        ],
        'black' => [
            'light' => 'base16/monokai',
            'dark' => 'monokai'
        ],
        'one-atom' => [
            'light' => 'atom-one-light',
            'dark' => 'atom-one-dark'
        ],
        'material' => [
            'light' => 'atom-one-light',
            'dark' => 'atom-one-dark'
        ]
    ];
    
    // Standard-Theme auswählen
    $currentThemeKey = $currentTheme ?? 'bootstrap';
    $mode = $themeMode ?? 'light';
    
    // Highlight.js Theme bestimmen
    if (isset($highlightThemes[$currentThemeKey][$mode])) {
        $highlightStyle = $highlightThemes[$currentThemeKey][$mode];
    } else {
        // Fallback: stackoverflow
        $highlightStyle = 'stackoverflow-' . $mode . '.min';
    }
    
    $highlightUrl = "https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.11.1/styles/{$highlightStyle}.css";
    
    // Theme-Mapping als JavaScript-Variable für clientseitiges Switching
    echo "<script>window.HIGHLIGHT_THEME_MAP = " . json_encode($highlightThemes) . ";</script>\n";
    echo "<script>window.CURRENT_THEME = " . json_encode($currentThemeKey) . ";</script>\n";
    ?>
    <link rel="stylesheet" href="<?= $highlightUrl ?>" id="highlight-theme">

    <!-- Theme CSS -->
    <style>
        <?php 

        // Include shared CSS
        include __DIR__ . '/shared.css'; 
        ?>

        <?php 
        // Include theme-specific CSS
        $themeCssPath = __DIR__ . '/../' . ($currentTheme ?? 'bootstrap') . '/template.css';
        if (file_exists($themeCssPath)) {
            include $themeCssPath;
        }
        ?>
    </style>        

    <!-- Custom CSS from page meta -->
    <?php if (isset($meta['css'])): ?>
    <style><?= $meta['css'] ?></style>
    <?php endif; ?>
