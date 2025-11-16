---
Title: 7. Theme System
Author: System
Tag: themes, design, frontend
Layout: Standard
---

# Theme System

Comprehensive documentation of the StaticMD theme system with **8 professional themes** and complete guide for developing custom themes.

> **Available**: 8 public themes + 5 editor themes + gallery support + ThemeHelper system

---

## Overview

StaticMD offers a flexible theme system with **8 pre-installed professional themes** (excluding internal themes). The system supports both frontend themes for visitor view and editor themes for the admin interface.

**Available for Public Use**: 8 themes
**Total Installed**: 9 themes (including 1 internal theme)

## Available Themes

### Frontend Themes

#### 1. Bootstrap (Default) ✅
- **Path**: `/system/themes/bootstrap/`
- **Description**: Standard Bootstrap 5 theme with modern design
- **Features**: Responsive grid, navigation dropdown, professional layout, gallery support
- **Target Audience**: Universal use, business-ready
- **Special Layouts**: `template.php`, `gallery.php`

#### 2. Solarized Light ✅
- **Path**: `/system/themes/solarized-light/`
- **Description**: Eye-friendly light theme for developers
- **Features**: Warm colors, high contrast, code-optimized
- **Target Audience**: Developers, technical documentation

#### 3. Solarized Dark ✅
- **Path**: `/system/themes/solarized-dark/`
- **Description**: Dark Solarized theme for nighttime work
- **Features**: Dark background, muted colors, eye-friendly
- **Target Audience**: Developers, dark mode preference

#### 4. Monokai Light ✅
- **Path**: `/system/themes/monokai-light/`
- **Description**: Light variant of the popular Monokai theme
- **Features**: High-contrast colors, modern typography
- **Target Audience**: Designers, creative projects

#### 5. Monokai Dark ✅
- **Path**: `/system/themes/monokai-dark/`
- **Description**: Classic dark Monokai theme
- **Features**: Dark background, bright accent colors
- **Target Audience**: Developers, programmers

#### 6. GitHub Light ✅
- **Path**: `/system/themes/github-light/`
- **Description**: Authentic GitHub look for repositories
- **Features**: GitHub-like design, Markdown-optimized
- **Target Audience**: Open source projects, documentation

#### 7. GitHub Dark ✅
- **Path**: `/system/themes/github-dark/`
- **Description**: Dark GitHub variant
- **Features**: GitHub dark mode, professional
- **Target Audience**: Developers, modern projects

#### 8. Static-MD ✅
- **Path**: `/system/themes/static-md/`
- **Description**: Original StaticMD theme with clean design
- **Features**: Minimal layout, fast loading, content-focused
- **Target Audience**: Documentation sites, content-heavy projects

### Editor Themes

#### CodeMirror Themes (5 Available) ✅
- **GitHub**: Standard light editor with clean syntax highlighting
- **Monokai**: Dark editor with vibrant colors and excellent contrast
- **Solarized Light**: Light Solarized editor with warm, eye-friendly colors
- **Solarized Dark**: Dark Solarized editor with muted, comfortable colors
- **Material**: Material Design editor with modern flat design

**Features**: Live preview, theme switching in settings, syntax highlighting for Markdown, real-time theme preview

---

## Theme Structure

### Directory Structure ✅
```
system/themes/
├── ThemeHelper.php       # Shared theme functionality
├── bootstrap/
│   ├── template.php      # Main template
│   ├── gallery.php       # Gallery layout (NEW)
│   └── template.css      # Theme CSS
├── solarized-light/
├── solarized-dark/
├── monokai-light/
├── monokai-dark/
├── github-light/
├── github-dark/
└── static-md/
    ├── template.php
    └── template.css
```

**Note**: All themes include gallery layout support for image collections.

### Template File (template.php) ✅
```php
<?php
/**
 * Bootstrap Theme - Main Template
 * Uses Bootstrap 5 for modern, responsive design
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
```

---

## Theme Selection and Management

### Admin Interface ✅

Themes can be managed via **Admin → Settings**:

#### Frontend Theme Section
1. **Theme Dropdown**: Shows all 8 public themes (excludes internal themes)
2. **Theme Preview**: Visual theme selector with description
3. **Live Application**: Changes apply immediately without restart
4. **Theme Validation**: Automatic validation of theme files

#### Editor Theme Section
1. **CodeMirror Theme Selection**: 5 available editor themes
2. **Live Preview**: Real-time preview of editor appearance
3. **Syntax Highlighting**: Preview shows actual markdown syntax
4. **Theme Switching**: Instant theme changes in editor

#### Settings Storage
- **Frontend Theme**: Stored in `system/settings.json` as `frontend_theme`
- **Editor Theme**: Stored as `editor_theme`
- **Auto-Save**: Settings saved automatically on change

### Programmatic Selection

```php
// Set theme
$app->setSetting('frontend_theme', 'solarized-dark');
$app->setSetting('editor_theme', 'monokai');

// Get current theme
$currentTheme = $app->getSetting('frontend_theme', 'bootstrap');
```

---

## Developing Custom Themes

### 1. Create Theme Directory

```bash
mkdir system/themes/my-theme
cd system/themes/my-theme
```

### 2. Create Template File

```php
<?php
// Theme: My Custom Theme
// Version: 1.0.0
// Author: Your Name

$siteName = $this->getSetting('site_name', 'StaticMD');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? $siteName) ?></title>
    
    <!-- Theme CSS -->
    <link href="/system/themes/my-theme/template.css" rel="stylesheet">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <h1 class="site-title"><?= htmlspecialchars($siteName) ?></h1>
            
            <!-- Navigation -->
            <?php if (!empty($navigation)): ?>
            <nav class="main-navigation">
                <ul>
                <?php foreach ($navigation as $item): ?>
                    <li>
                        <a href="<?= htmlspecialchars($item['url']) ?>" 
                           <?= $item['active'] ? 'class="active"' : '' ?>>
                            <?= htmlspecialchars($item['title']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </header>
    
    <main class="main-content">
        <div class="container">
            <?= $content ?>
        </div>
    </main>
    
    <footer class="site-footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($siteName) ?>. Powered by StaticMD.</p>
        </div>
    </footer>
</body>
</html>
```

### 3. Create CSS File

```css
/* Theme: My Custom Theme */

:root {
    --primary-color: #2c3e50;
    --accent-color: #3498db;
    --text-color: #333;
    --bg-color: #fff;
    --border-color: #e1e8ed;
}

body {
    font-family: 'Georgia', serif;
    line-height: 1.8;
    color: var(--text-color);
    background-color: var(--bg-color);
    margin: 0;
    padding: 0;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Header */
.site-header {
    background: var(--primary-color);
    color: white;
    padding: 2rem 0;
}

.site-title {
    font-size: 2.5rem;
    margin: 0 0 1rem 0;
    font-weight: normal;
}

/* Navigation */
.main-navigation ul {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    gap: 2rem;
}

.main-navigation a {
    color: white;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s ease;
}

.main-navigation a:hover,
.main-navigation a.active {
    color: var(--accent-color);
}

/* Content */
.main-content {
    min-height: calc(100vh - 300px);
    padding: 3rem 0;
}

/* Footer */
.site-footer {
    background: var(--primary-color);
    color: white;
    text-align: center;
    padding: 2rem 0;
    margin-top: 3rem;
}

/* Responsive */
@media (max-width: 768px) {
    .site-title {
        font-size: 2rem;
    }
    
    .main-navigation ul {
        flex-direction: column;
        gap: 1rem;
    }
}
```

---

## Theme Variables

### Available Variables in Templates ✅

```php
// Content variables (from ContentLoader)
$body           // Processed Markdown content (main variable)
$title          // Page title from front matter
$meta           // Array with all front matter data
$content        // Content array with route, file_path, etc.

// Navigation (from ThemeHelper)
$navItems       // Navigation array built by ThemeHelper
$breadcrumbs    // Breadcrumb array (optional)

// Settings (from settings.json)
$settings       // Complete settings array
$siteName       // Site name: $settings['site_name']
$siteLogo       // Site logo: $settings['site_logo']
$navigationOrder // Navigation priority: $settings['navigation_order']

// System variables
$config         // Complete configuration array
$currentRoute   // Current route from $_GET['route']
$_SESSION       // Admin session data

// SEO variables (from SecurityHeaders)
$robotsMeta     // Generated robots meta tags

// Theme variables
$this->contentLoader  // Access to ContentLoader instance

// Example usage:
$author = $meta['author'] ?? '';
$tags = $meta['tag'] ?? '';
$isPrivate = ($meta['visibility'] ?? 'public') === 'private';
```

---

## Performance Optimization

### CSS Minification

```php
<?php
// Automatic CSS minification
$cssFile = "/system/themes/{$currentTheme}/template.css";
$minifiedCss = $this->minifyCSS(file_get_contents($cssFile));

// Cache busting
$cssVersion = filemtime($cssFile);
echo "<link href='{$cssFile}?v={$cssVersion}' rel='stylesheet'>";
?>
```

### Critical CSS

```php
<?php
// Inline critical CSS for above-the-fold content
$criticalCSS = $this->getCriticalCSS($currentTheme);
echo "<style>{$criticalCSS}</style>";

// Load non-critical CSS async
echo "<link rel='preload' href='/system/themes/{$currentTheme}/template.css' as='style' onload=\"this.onload=null;this.rel='stylesheet'\">";
?>
```

---

## Troubleshooting

### Common Issues

#### Theme not loading
- **Cause**: Theme name misspelled or theme files missing
- **Solution**: Check theme directory and files

#### CSS not applied
- **Cause**: Wrong CSS path or missing write permissions
- **Solution**: Check paths and permissions

#### JavaScript errors
- **Cause**: External dependencies not available
- **Solution**: Check CDN links, implement local fallbacks

### Debug Mode

```php
// In config.php
'system' => [
    'debug' => true  // Shows theme loading details
]
```

## New Features & Gallery Support

### 🖼️ Gallery Layout System (NEW)
All themes now support gallery layouts:

```php
// Special gallery template: gallery.php
// Activated with: Layout: gallery in front matter
// Features:
- Responsive image grid
- GLightbox integration
- Tag-based filtering
- Hover effects and overlays
- Automatic image loading via [gallery] shortcode
```

### 🔧 Theme Helper System ✅
```php
// Centralized theme functionality
class ThemeHelper {
    public function buildNavigation()     // Auto-generate navigation
    public function renderBreadcrumbs()   // Breadcrumb generation
    public function encodeUrlPath()       // Unicode-safe URL encoding
    public function getNavigationOrder()  // Configurable navigation sorting
}
```

### 🚀 Performance Features
- **Inline CSS**: CSS embedded directly in templates
- **CDN Integration**: Bootstrap and icons from CDN
- **Asset Routing**: Efficient asset delivery via assets.php
- **Theme Caching**: Automatic theme file caching

### 🔐 Security Integration
- **CSP Compatibility**: All themes work with Content Security Policy
- **XSS Protection**: Proper output escaping in all templates
- **Admin Integration**: Conditional admin toolbar display
- **Session Security**: Admin-only content support

---

*StaticMD Theme System v2.1 - 8 Professional Themes with Gallery Support*