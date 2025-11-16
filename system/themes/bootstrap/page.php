<?php
/**
 * basic page template
 */

// Theme configuration
$siteName = $config['system']['name'] ?? 'StaticMD';
$currentRoute = $_GET['route'] ?? 'index';

// Load settings
$settingsFile = $config['paths']['system'] . '/settings.json';
$settings = [];
if (file_exists($settingsFile)) {
    $settings = json_decode(file_get_contents($settingsFile), true) ?: [];
}
$siteName = $settings['site_name'] ?? $siteName;
$siteLogo = $settings['site_logo'] ?? '';

// Theme Helper for navigation
require_once __DIR__ . '/../ThemeHelper.php';
$themeHelper = new \StaticMD\Themes\ThemeHelper($this->contentLoader);
$navItems = $themeHelper->buildNavigation();

// Navigation ordering from settings
$navigationOrder = $this->contentLoader->getNavigationOrder();
uksort($navItems, function($a, $b) use ($navigationOrder) {
    $orderA = $navigationOrder[$a] ?? 999;
    $orderB = $navigationOrder[$b] ?? 999;
    return $orderA <=> $orderB;
});
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> - <?= htmlspecialchars($siteName) ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Theme CSS -->
    <style><?php include __DIR__ . '/template.css'; ?></style>
</head>
<body>
    <!-- Navigation with dropdown support -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <!-- Logo and site name -->
            <a class="navbar-brand" href="/">
                <?php if (!empty($siteLogo)): ?>
                    <img src="<?= htmlspecialchars($siteLogo) ?>" alt="Logo" style="height: 30px;">
                <?php endif; ?>
                <?= htmlspecialchars($siteName) ?>
            </a>
            
            <!-- Navigation items with dropdown -->
            <div class="navbar-nav">
                <?php foreach ($navItems as $section => $nav): ?>
                    <?php if (!empty($nav['pages'])): ?>
                    <!-- Dropdown for folders -->
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <?= htmlspecialchars($nav['title']) ?>
                        </a>
                        <ul class="dropdown-menu">
                            <?php foreach ($nav['pages'] as $page): ?>
                            <li><a class="dropdown-item" href="/<?= $page['route'] ?>">
                                <?= htmlspecialchars($page['title']) ?>
                            </a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php else: ?>
                    <!-- Direct link -->
                    <a class="nav-link" href="/<?= $nav['route'] ?>">
                        <?= htmlspecialchars($nav['title']) ?>
                    </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="container mt-4">
        <?= $body ?>
    </main>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>