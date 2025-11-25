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
<html lang="de" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($meta['description'] ?? 'Powered by StaticMD') ?>">
    <meta name="author" content="<?= htmlspecialchars($meta['author'] ?? '') ?>">
    
    <!-- SEO/Robots Meta-Tags -->
    <?= $robotsMeta ?? '' ?>
    
    <title><?= htmlspecialchars($title) ?> - <?= htmlspecialchars($siteName) ?></title>
    
    <!-- Favicon -->  
    <link rel="icon" type="image/x-icon" href="/assets/favicon.ico">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Theme CSS -->
    <style>
        <?php 
        // Include theme-specific CSS
        $themeCssPath = __DIR__ . '/../' . ($currentTheme ?? 'bootstrap') . '/template.css';
        if (file_exists($themeCssPath)) {
            include $themeCssPath;
        }
        ?>
    </style>        

    <?php if (isset($meta['css'])): ?>
    <style><?= $meta['css'] ?></style>
    <?php endif; ?>
