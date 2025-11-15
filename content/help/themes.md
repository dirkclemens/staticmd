---
Title: Theme System
Author: StaticMD Team
Tag: themes, design, frontend
Layout: Standard
---

# Theme System

Comprehensive documentation of the StaticMD theme system with 7 professional themes and guide for developing custom themes.

---

## Overview

StaticMD offers a flexible theme system with 7 pre-installed professional themes. The system supports both frontend themes for visitor view and editor themes for the admin interface.

## Available Themes

### Frontend Themes

#### 1. Bootstrap (Default)
- **Path**: `/system/themes/bootstrap/`
- **Description**: Standard Bootstrap 5 theme with modern design
- **Features**: Responsive grid, dark/light mode toggle, professional layout
- **Target Audience**: Universal use, business-ready

#### 2. Solarized Light
- **Path**: `/system/themes/solarized-light/`
- **Description**: Eye-friendly light theme for developers
- **Features**: Warm colors, high contrast, code-optimized
- **Target Audience**: Developers, technical documentation

#### 3. Solarized Dark
- **Path**: `/system/themes/solarized-dark/`
- **Description**: Dark Solarized theme for nighttime work
- **Features**: Dark background, muted colors, eye-friendly
- **Target Audience**: Developers, dark mode preference

#### 4. Monokai Light
- **Path**: `/system/themes/monokai-light/`
- **Description**: Light variant of the popular Monokai theme
- **Features**: High-contrast colors, modern typography
- **Target Audience**: Designers, creative projects

#### 5. Monokai Dark
- **Path**: `/system/themes/monokai-dark/`
- **Description**: Classic dark Monokai theme
- **Features**: Dark background, bright accent colors
- **Target Audience**: Developers, programmers

#### 6. GitHub Light
- **Path**: `/system/themes/github-light/`
- **Description**: Authentic GitHub look for repositories
- **Features**: GitHub-like design, Markdown-optimized
- **Target Audience**: Open source projects, documentation

#### 7. GitHub Dark
- **Path**: `/system/themes/github-dark/`
- **Description**: Dark GitHub variant
- **Features**: GitHub dark mode, professional
- **Target Audience**: Developers, modern projects

### Editor Themes

#### CodeMirror Themes
- **GitHub**: Standard light editor
- **Monokai**: Dark editor with syntax highlighting
- **Solarized Light**: Light Solarized editor
- **Solarized Dark**: Dark Solarized editor
- **Material**: Material Design editor

---

## Theme Structure

### Directory Structure
```
system/themes/
├── bootstrap/
│   ├── template.php      # PHP Template
│   └── template.css      # Theme-specific CSS
├── solarized-light/
│   ├── template.php
│   └── template.css
└── [other themes...]
```

### Template File (template.php)
```php
<?php
// Theme: Bootstrap
// Version: 1.0.0
// Author: StaticMD Team

$siteName = $this->getSetting('site_name', 'StaticMD');
$currentTheme = $this->getSetting('frontend_theme', 'bootstrap');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? $siteName) ?></title>
    
    <!-- Theme CSS -->
    <link href="/system/themes/<?= $currentTheme ?>/template.css" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <!-- Navigation content -->
    </nav>
    
    <!-- Main Content -->
    <main class="container mt-4">
        <?= $content ?>
    </main>
    
    <!-- Footer -->
    <footer class="bg-light mt-5 py-4">
        <!-- Footer content -->
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```

---

## Theme Selection and Management

### Admin Interface

Themes can be managed via **Admin → Settings → Theme Settings**:

1. **Frontend Theme Selection**: Dropdown with all available themes
2. **Editor Theme Selection**: CodeMirror theme for admin editor
3. **Live Preview**: Immediate view of changes
4. **Theme Information**: Details about each theme

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

### Available Variables in Templates

```php
// Content variables
$content         // Processed Markdown content
$title          // Page title
$author         // Page author
$tags           // Comma-separated tags
$description    // Meta description

// Navigation
$navigation     // Array with navigation items
$breadcrumb     // Breadcrumb navigation

// Settings
$siteName       // Site name from settings
$siteDescription // Site description
$currentTheme   // Current theme

// SEO
$robotsMeta     // Robots meta tags
$enableSeoMeta  // SEO enabled/disabled

// System
$isAdmin        // Is admin logged in
$adminToolbar   // Admin toolbar HTML
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

---

*StaticMD Theme System v2.0 - Professional Theme Architecture*