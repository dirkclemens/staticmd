# 🎨 Theme-System Dokumentation

StaticMD bietet ein umfangreiches Theme-System mit 7 Frontend-Themes und 5 Editor-Themes.

## Frontend-Themes

### Verfügbare Themes

| Theme | Beschreibung | Charakteristika |
|-------|-------------|-----------------|
| **Bootstrap** | Standard-Theme | Hell, klassisch, Bootstrap 5 Standard-Farben |
| **Solarized Light** | Heller Solarized-Look | Warme Farben, augenschonend, cremiger Hintergrund |
| **Solarized Dark** | Dunkler Solarized-Look | Dunkle Basis, warme Akzente, professionell |
| **Monokai Light** | Heller Monokai-Stil | Modern, kontrastreich, entwicklerfreundlich |
| **Monokai Dark** | Dunkler Monokai-Stil | Dunkel, klassisches Entwickler-Theme |
| **GitHub Light** | GitHub's helles Design | Clean, vertraut, wie github.com |
| **GitHub Dark** | GitHub's dunkles Design | Dunkel, modern, GitHub-authentisch |

### Theme-Konfiguration

1. **Admin → Settings** aufrufen
2. **Frontend-Theme** aus der Dropdown-Liste auswählen
3. **Speichern** klicken
4. **Frontend-Seite** besuchen → Theme ist sofort aktiv

### Theme-Struktur

Jedes Frontend-Theme befindet sich in:
```
system/themes/[theme-name]/
└── template.php    # Haupt-Template-Datei
```

Alle Themes enthalten:
- ✅ Vollständige Bootstrap 5 Integration
- ✅ Responsive Navigation mit Dropdown-Menüs
- ✅ Theme-spezifische CSS-Variablen
- ✅ Optimierte Lesbarkeit und Kontraste
- ✅ Admin-Toolbar Integration
- ✅ Settings-System Kompatibilität

## Editor-Themes

### Verfügbare Editor-Themes

| Theme | Stil | Beschreibung |
|-------|------|-------------|
| **GitHub** | Hell | Standard GitHub-Look, vertraut |
| **Monokai** | Dunkel | Klassisches dunkles Entwickler-Theme |
| **Solarized Light** | Hell | Augenschonende helle Variante |
| **Solarized Dark** | Dunkel | Professionelle dunkle Variante |
| **Material** | Dunkel | Modernes Material Design |

### Editor-Theme Konfiguration

1. **Admin → Settings** aufrufen
2. **Editor-Theme** auswählen
3. **Live-Vorschau** betrachten
4. **Speichern** → Theme wird sofort im Editor aktiv

### Theme-Vorschau

Im Settings-Bereich wird eine Live-Vorschau des Editor-Themes angezeigt mit:
- Markdown-Syntax-Highlighting
- JavaScript-Code-Beispiel
- Verschiedene Textarten (Keywords, Strings, Comments)

## Eigene Themes erstellen

### Frontend-Theme erstellen

1. **Neuen Ordner** erstellen: `system/themes/mein-theme/`
2. **template.php** erstellen basierend auf einem bestehenden Theme
3. **CSS-Variablen** anpassen für eigene Farbpalette
4. **Theme registrieren** in Settings-Template

### Beispiel Custom Theme

```php
<?php
// system/themes/mein-theme/template.php

$siteName = $config['system']['name'] ?? 'StaticMD';
// ... Standard Theme-Setup ...
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <!-- Standard HTML Head -->
    <style>
        :root {
            /* Eigene Farbvariablen */
            --my-primary: #your-color;
            --my-secondary: #your-color;
            /* ... weitere Variablen ... */
        }
        
        body {
            background-color: var(--my-primary);
            color: var(--my-secondary);
        }
        
        /* Weitere CSS-Anpassungen */
    </style>
</head>
<body>
    <!-- Standard Template-Struktur -->
    <?= $body ?>
</body>
</html>
```

### Theme in Settings registrieren

In `system/admin/templates/settings.php` das neue Theme hinzufügen:

```php
<option value="mein-theme" <?= ($settings['frontend_theme'] ?? '') === 'mein-theme' ? 'selected' : '' ?>>
    Mein Custom Theme
</option>
```

## Theme-Kompatibilität

### Benötigte Template-Variablen

Alle Frontend-Themes müssen diese Variablen unterstützen:

- `$body` - Der Haupt-Content (HTML)
- `$meta` - Meta-Informationen (Array)
- `$config` - System-Konfiguration
- `$settings` - Benutzer-Settings
- `$currentRoute` - Aktuelle Route

### Bootstrap-Integration

Alle Themes basieren auf Bootstrap 5 und enthalten:
- Responsive Grid-System
- Navigation-Komponenten
- Card- und Alert-Styling
- Form-Komponenten
- Button-Styling

### Admin-Integration

Themes müssen Admin-Features unterstützen:
- Admin-Toolbar (wenn eingeloggt)
- Edit-Buttons für Seiten
- Success-Messages
- Privacy-Hinweise

## Troubleshooting

### Theme wird nicht geladen

1. **Datei existiert?** `system/themes/[theme]/template.php`
2. **Berechtigungen prüfen:** `chmod 644 template.php`
3. **PHP-Syntax prüfen:** `php -l template.php`
4. **Settings zurücksetzen:** Fallback auf Bootstrap-Theme

### CSS nicht korrekt

1. **Browser-Cache leeren**
2. **CSS-Variablen prüfen** in Developer Tools
3. **Bootstrap-Konflikte** überprüfen
4. **Responsive Breakpoints** testen

### Performance-Optimierung

- **CSS minimieren** in Produktion
- **CDN verwenden** für Bootstrap
- **Caching aktivieren** für statische Assets
- **Bilder optimieren** für verschiedene Themes