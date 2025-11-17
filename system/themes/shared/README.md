# Geteilte Template-Komponenten

Dieses Verzeichnis enthält modularisierte HTML-Komponenten, die von allen Themes verwendet werden können, um Code-Duplikation zu vermeiden.

## Verfügbare Komponenten

### head.php
- HTML HEAD-Sektion mit Meta-Tags, CSS-Includes und Theme-CSS
- **Variablen:** `$siteName`, `$title`, `$meta`, `$currentTheme`
- **Verwendet:** Bootstrap CSS, KaTeX CSS, Bootstrap Icons, Favicon

### navigation.php  
- Vollständige Bootstrap 5 Navigation mit Dropdowns
- **Variablen:** `$navItems`, `$currentRoute`, `$siteName`, `$siteLogo`
- **Features:** Responsive Navigation, Suchformular, Admin-Link

### admin-toolbar.php
- Floating Admin-Toolbar (nur wenn eingeloggt)
- **Variablen:** `$_SESSION['admin_logged_in']`, `$currentRoute`
- **Features:** Dashboard-Link, Bearbeiten-Button

### footer.php
- Site-Footer mit Copyright und Admin-Link
- **Variablen:** `$siteName`
- **Features:** Jahr automatisch, Admin-Link wenn eingeloggt

### scripts.php
- JavaScript-Includes und Initialisierung
- **Enthält:** Bootstrap JS, KaTeX Auto-Render, Custom Scripts
- **Features:** Math-Rendering, Smooth Scrolling, Auto-Hide Alerts

## Verwendung in Themes

```php
<?php
// Theme-spezifische Variablen vorbereiten
$currentTheme = 'mein-theme';
$siteName = $settings['site_name'] ?? 'StaticMD';
// ... weitere Variablen

// Shared Head-Sektion einbinden
include __DIR__ . '/../shared/head.php';
?>
<body>
    <?php 
    // Shared Navigation einbinden
    include __DIR__ . '/../shared/navigation.php';
    ?>
    
    <!-- Theme-spezifisches Layout hier -->
    <main>
        <?= $content ?>
    </main>
    
    <?php 
    // Shared Komponenten einbinden
    include __DIR__ . '/../shared/admin-toolbar.php';
    include __DIR__ . '/../shared/footer.php'; 
    include __DIR__ . '/../shared/scripts.php'; 
    ?>
</body>
</html>
```

## Vorteile

1. **Wartbarkeit:** Ein Update betrifft alle Themes
2. **Konsistenz:** Einheitliche Navigation und Features
3. **Performance:** Weniger Code-Duplikation
4. **Updates:** Bootstrap/Library-Updates zentral möglich

## Migration bestehender Themes

1. Theme-spezifische Variablen vorbereiten
2. HTML HEAD durch `include head.php` ersetzen  
3. Navigation durch `include navigation.php` ersetzen
4. Footer durch `include footer.php` ersetzen
5. Scripts durch `include scripts.php` ersetzen
6. Admin-Toolbar durch `include admin-toolbar.php` ersetzen

## Anpassungen

Theme-spezifische Anpassungen können weiterhin in der `template.css` und in den Layout-Bereichen zwischen den includes vorgenommen werden.