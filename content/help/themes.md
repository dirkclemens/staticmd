---
Title: Theme System
Author: StaticMD Team
Tag: themes, design, frontend
Layout: wiki
---

# Theme System

Umfassende Dokumentation des StaticMD Theme-Systems mit 7 professionellen Themes und Anleitung zur Entwicklung eigener Themes.

---

## Übersicht

StaticMD bietet ein flexibles Theme-System mit 7 vorinstallierten professionellen Themes. Das System unterstützt sowohl Frontend-Themes für die Besucher-Ansicht als auch Editor-Themes für das Admin-Interface.

## Verfügbare Themes

### Frontend-Themes

#### 1. Bootstrap (Standard)
- **Pfad**: `/system/themes/bootstrap/`
- **Beschreibung**: Standard Bootstrap 5 Theme mit modernem Design
- **Features**: Responsive Grid, Dark/Light Mode Toggle, Professional Layout
- **Zielgruppe**: Universell einsetzbar, business-tauglich

#### 2. Solarized Light
- **Pfad**: `/system/themes/solarized-light/`
- **Beschreibung**: Augenschonendes helles Theme für Entwickler
- **Features**: Warme Farbtöne, hoher Kontrast, Code-optimiert
- **Zielgruppe**: Entwickler, technische Dokumentation

#### 3. Solarized Dark
- **Pfad**: `/system/themes/solarized-dark/`  
- **Beschreibung**: Dunkles Solarized Theme für nächtliche Arbeit
- **Features**: Dunkler Hintergrund, gedämpfte Farben, augenschonend
- **Zielgruppe**: Entwickler, Dark Mode Präferenz

#### 4. Monokai Light
- **Pfad**: `/system/themes/monokai-light/`
- **Beschreibung**: Helle Variante des beliebten Monokai Themes
- **Features**: Kontraststarke Farben, moderne Typografie
- **Zielgruppe**: Designer, kreative Projekte

#### 5. Monokai Dark
- **Pfad**: `/system/themes/monokai-dark/`
- **Beschreibung**: Klassisches dunkles Monokai Theme
- **Features**: Dunkler Hintergrund, leuchtende Akzentfarben
- **Zielgruppe**: Entwickler, Programmierer

#### 6. GitHub Light
- **Pfad**: `/system/themes/github-light/`
- **Beschreibung**: Authentische GitHub-Optik für Repositories
- **Features**: GitHub-ähnliches Design, Markdown-optimiert
- **Zielgruppe**: Open Source Projekte, Dokumentation

#### 7. GitHub Dark
- **Pfad**: `/system/themes/github-dark/`
- **Beschreibung**: Dunkle GitHub-Variante
- **Features**: GitHub Dark Mode, professionell
- **Zielgruppe**: Entwickler, moderne Projekte

### Editor-Themes

#### CodeMirror Themes
- **GitHub**: Standard heller Editor
- **Monokai**: Dunkler Editor mit Syntaxhervorhebung
- **Solarized Light**: Heller Solarized Editor
- **Solarized Dark**: Dunkler Solarized Editor  
- **Material**: Material Design Editor

---

## Theme-Struktur

### Verzeichnisaufbau
```
system/themes/
├── bootstrap/
│   ├── template.php      # PHP Template
│   └── template.css      # Theme-spezifisches CSS
├── solarized-light/
│   ├── template.php
│   └── template.css
└── [weitere themes...]
```

### Template-Datei (template.php)
```php
<?php
// Theme: Bootstrap
// Version: 1.0.0
// Author: StaticMD Team

$siteName = $this->getSetting('site_name', 'StaticMD');
$currentTheme = $this->getSetting('frontend_theme', 'bootstrap');
?>
<!DOCTYPE html>
<html lang="de">
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

### CSS-Datei (template.css)
```css
/* Theme: Bootstrap */
/* Custom theme styles */

:root {
    --primary-color: #0d6efd;
    --secondary-color: #6c757d;
    --success-color: #198754;
    --info-color: #0dcaf0;
    --warning-color: #ffc107;
    --danger-color: #dc3545;
    --light-color: #f8f9fa;
    --dark-color: #212529;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.6;
    color: var(--dark-color);
}

.navbar-brand {
    font-weight: bold;
    font-size: 1.5rem;
}

.content-wrapper {
    min-height: calc(100vh - 200px);
}

/* Responsive design */
@media (max-width: 768px) {
    .container {
        padding: 0 15px;
    }
}
```

---

## Theme-Auswahl und -Verwaltung

### Admin-Interface

Themes können über **Admin → Settings → Theme Settings** verwaltet werden:

1. **Frontend Theme Auswahl**: Dropdown mit allen verfügbaren Themes
2. **Editor Theme Auswahl**: CodeMirror Theme für den Admin-Editor
3. **Live-Vorschau**: Sofortige Ansicht der Änderungen
4. **Theme-Informationen**: Details zu jedem Theme

### Programmatische Auswahl

```php
// Theme setzen
$app->setSetting('frontend_theme', 'solarized-dark');
$app->setSetting('editor_theme', 'monokai');

// Aktuelles Theme abfragen
$currentTheme = $app->getSetting('frontend_theme', 'bootstrap');
```

### URL-Parameter (Debug)

```
# Temporäres Theme testen
https://your-domain.com/?theme=github-dark

# Editor-Theme testen  
https://your-domain.com/admin/?editor_theme=solarized-light
```

---

## Eigene Themes entwickeln

### 1. Theme-Verzeichnis erstellen

```bash
mkdir system/themes/mein-theme
cd system/themes/mein-theme
```

### 2. Template-Datei erstellen

```php
<?php
// Theme: Mein Custom Theme
// Version: 1.0.0
// Author: Dein Name

$siteName = $this->getSetting('site_name', 'StaticMD');
$siteDescription = $this->getSetting('site_description', '');
$enableSeoMeta = $this->getSetting('enable_seo_meta', true);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? $siteName) ?></title>
    
    <?php if ($enableSeoMeta): ?>
    <meta name="description" content="<?= htmlspecialchars($description ?? $siteDescription) ?>">
    <meta name="author" content="<?= htmlspecialchars($author ?? '') ?>">
    <?php if (!empty($tags)): ?>
    <meta name="keywords" content="<?= htmlspecialchars($tags) ?>">
    <?php endif; ?>
    <?= $robotsMeta ?? '' ?>
    <?php endif; ?>
    
    <!-- Theme CSS -->
    <link href="/system/themes/mein-theme/template.css" rel="stylesheet">
    
    <!-- Optional: External Dependencies -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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

### 3. CSS-Datei erstellen

```css
/* Theme: Mein Custom Theme */

:root {
    --primary-color: #2c3e50;
    --accent-color: #3498db;
    --text-color: #333;
    --bg-color: #fff;
    --border-color: #e1e8ed;
}

* {
    box-sizing: border-box;
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

/* Typography */
h1, h2, h3, h4, h5, h6 {
    color: var(--primary-color);
    line-height: 1.3;
}

h1 { font-size: 2.5rem; }
h2 { font-size: 2rem; }
h3 { font-size: 1.5rem; }

/* Links */
a {
    color: var(--accent-color);
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}

/* Code */
code {
    background: #f8f9fa;
    padding: 0.2rem 0.4rem;
    border-radius: 3px;
    font-size: 0.9em;
}

pre {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 5px;
    overflow-x: auto;
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
    
    .container {
        padding: 0 15px;
    }
}
```

### 4. Theme registrieren

Das Theme wird automatisch erkannt, wenn es im `/system/themes/` Verzeichnis liegt.

---

## Theme-Variablen

### Verfügbare Variablen in Templates

```php
// Content-Variablen
$content         // Verarbeiteter Markdown-Content
$title          // Seiten-Titel
$author         // Autor der Seite
$tags           // Komma-getrennte Tags
$description    // Meta-Beschreibung

// Navigation
$navigation     // Array mit Navigation-Items
$breadcrumb     // Breadcrumb-Navigation

// Settings
$siteName       // Site-Name aus Settings
$siteDescription // Site-Beschreibung
$currentTheme   // Aktuelles Theme

// SEO
$robotsMeta     // Robots Meta-Tags
$enableSeoMeta  // SEO aktiviert/deaktiviert

// System
$isAdmin        // Ist Admin eingeloggt
$adminToolbar   // Admin-Toolbar HTML
```

### Helper-Funktionen

```php
// Settings abrufen
$this->getSetting('key', 'default')

// URL generieren
$this->url('/pfad/')

// Asset-URL generieren
$this->asset('css/style.css')

// Navigation generieren
$this->getNavigationOrder()
```

---

## Theme-Vererbung

### Base-Theme erstellen

```php
// system/themes/base/template.php
<?php
abstract class BaseTheme 
{
    protected function renderHeader(): string 
    {
        return '<header class="site-header">...</header>';
    }
    
    protected function renderNavigation(): string 
    {
        return '<nav class="main-nav">...</nav>';
    }
    
    protected function renderFooter(): string 
    {
        return '<footer class="site-footer">...</footer>';
    }
}
```

### Theme erweitern

```php
// system/themes/mein-theme/template.php
<?php
require_once __DIR__ . '/../base/template.php';

class MeinTheme extends BaseTheme 
{
    public function render(): string 
    {
        return '<!DOCTYPE html>
        <html>
        <head>...</head>
        <body>
            ' . $this->renderHeader() . '
            <main>' . $content . '</main>
            ' . $this->renderFooter() . '
        </body>
        </html>';
    }
}
```

---

## Advanced Theme Features

### Conditional Loading

```php
<?php
// Verschiedene Layouts je nach Content-Typ
$layout = $this->getSetting('default_layout', 'wiki');

if (strpos($content, '[gallery') !== false) {
    $layout = 'gallery';
} elseif (strpos($content, '[blog') !== false) {
    $layout = 'blog';
}

// Layout-spezifische Includes
include __DIR__ . "/layouts/{$layout}.php";
?>
```

### Dynamic CSS

```php
<?php
// CSS-Variablen aus Settings
$primaryColor = $this->getSetting('primary_color', '#0d6efd');
$accentColor = $this->getSetting('accent_color', '#6c757d');
?>
<style>
:root {
    --primary-color: <?= $primaryColor ?>;
    --accent-color: <?= $accentColor ?>;
}
</style>
```

### JavaScript Integration

```php
<!-- Theme-spezifische JavaScript -->
<script>
window.StaticMD = {
    theme: '<?= $currentTheme ?>',
    settings: <?= json_encode($this->getThemeSettings()) ?>,
    isAdmin: <?= $isAdmin ? 'true' : 'false' ?>
};
</script>
<script src="/system/themes/<?= $currentTheme ?>/theme.js"></script>
```

---

## Performance-Optimierung

### CSS-Minification

```php
<?php
// Automatische CSS-Minification
$cssFile = "/system/themes/{$currentTheme}/template.css";
$minifiedCss = $this->minifyCSS(file_get_contents($cssFile));

// Cache-Busting
$cssVersion = filemtime($cssFile);
echo "<link href='{$cssFile}?v={$cssVersion}' rel='stylesheet'>";
?>
```

### Lazy Loading

```css
/* Progressive Enhancement */
.theme-content {
    opacity: 0;
    transition: opacity 0.3s ease;
}

.theme-loaded .theme-content {
    opacity: 1;
}
```

### Critical CSS

```php
<?php
// Inline Critical CSS für Above-the-Fold Content
$criticalCSS = $this->getCriticalCSS($currentTheme);
echo "<style>{$criticalCSS}</style>";

// Nicht-kritisches CSS async laden
echo "<link rel='preload' href='/system/themes/{$currentTheme}/template.css' as='style' onload=\"this.onload=null;this.rel='stylesheet'\">";
?>
```

---

## Testing und Debugging

### Theme-Testing

```bash
# Alle Themes testen
for theme in system/themes/*/; do
    echo "Testing theme: $(basename $theme)"
    curl -s "http://localhost:8000/?theme=$(basename $theme)"
done
```

### CSS-Validierung

```bash
# CSS-Syntax prüfen
npm install -g css-validator
css-validator system/themes/*/template.css
```

### Performance-Testing

```javascript
// Theme-Performance messen
console.time('Theme Load');
document.addEventListener('DOMContentLoaded', function() {
    console.timeEnd('Theme Load');
});
```

---

## Migration bestehender Themes

### Von anderen CMS

```php
// WordPress Theme → StaticMD
// header.php → template.php (Header-Teil)
// footer.php → template.php (Footer-Teil)
// style.css → template.css

// Jekyll Theme → StaticMD
// _layouts/default.html → template.php
// _sass/main.scss → template.css (kompiliert)
```

### Theme-Converter

```php
class ThemeConverter 
{
    public function convertWordPressTheme($themePath): void 
    {
        $header = file_get_contents($themePath . '/header.php');
        $footer = file_get_contents($themePath . '/footer.php');
        
        $template = $this->mergeTemplate($header, $footer);
        file_put_contents('template.php', $template);
    }
}
```

---

## Troubleshooting

### Häufige Probleme

#### Theme wird nicht geladen
- **Ursache**: Theme-Name falsch geschrieben oder Theme-Dateien fehlen
- **Lösung**: Theme-Verzeichnis und Dateien prüfen

#### CSS wird nicht angewendet
- **Ursache**: CSS-Pfad falsch oder Schreibrechte fehlen
- **Lösung**: Pfade und Berechtigungen überprüfen

#### JavaScript-Errors
- **Ursache**: Externe Dependencies nicht verfügbar
- **Lösung**: CDN-Links prüfen, lokale Fallbacks implementieren

### Debug-Modus

```php
// In config.php
'system' => [
    'debug' => true  // Zeigt Theme-Loading-Details
]
```

### Theme-Validierung

```php
class ThemeValidator 
{
    public function validate($themePath): array 
    {
        $errors = [];
        
        if (!file_exists($themePath . '/template.php')) {
            $errors[] = 'template.php fehlt';
        }
        
        if (!file_exists($themePath . '/template.css')) {
            $errors[] = 'template.css fehlt';
        }
        
        return $errors;
    }
}
```

---

*StaticMD Theme System v2.0 - Professional Theme Architecture*