---
Title: Settings System
Author: StaticMD Team
Tag: admin, konfiguration, einstellungen
Layout: wiki
---

# Settings System

Umfassende Dokumentation des StaticMD Settings-Systems mit allen verfügbaren Konfigurationsoptionen.

---

## Übersicht

Das Settings-System von StaticMD verwendet JSON-basierte Konfiguration mit einem benutzerfreundlichen Admin-Interface. Alle Einstellungen werden in `system/settings.json` gespeichert und können über das Admin-Interface verwaltet werden.

## Verfügbare Settings

### Website-Grundeinstellungen

#### Site Name
- **Schlüssel**: `site_name`
- **Typ**: String
- **Standard**: `"StaticMD"`
- **Beschreibung**: Name der Website, wird im Browser-Tab und Header angezeigt

#### Site Logo
- **Schlüssel**: `site_logo`
- **Typ**: String (Pfad)
- **Standard**: `""`
- **Beschreibung**: Pfad zum Logo-Bild (relativ zu `/public/images/`)

#### Site Description
- **Schlüssel**: `site_description`
- **Typ**: String
- **Standard**: `"Professional Markdown CMS"`
- **Beschreibung**: Kurze Beschreibung der Website für Meta-Tags

### Frontend-Themes

#### Frontend Theme
- **Schlüssel**: `frontend_theme`
- **Typ**: String
- **Standard**: `"bootstrap"`
- **Optionen**: 
  - `bootstrap` - Standard Bootstrap Theme
  - `solarized-light` - Solarized Light Theme
  - `solarized-dark` - Solarized Dark Theme
  - `monokai-light` - Monokai Light Theme
  - `monokai-dark` - Monokai Dark Theme
  - `github-light` - GitHub Light Theme
  - `github-dark` - GitHub Dark Theme

### Editor-Konfiguration

#### Editor Theme
- **Schlüssel**: `editor_theme`
- **Typ**: String
- **Standard**: `"github"`
- **Optionen**:
  - `github` - GitHub Theme
  - `monokai` - Monokai Theme
  - `solarized-light` - Solarized Light
  - `solarized-dark` - Solarized Dark
  - `material` - Material Theme

#### Auto-Save Interval
- **Schlüssel**: `auto_save_interval`
- **Typ**: Integer (Sekunden)
- **Standard**: `60`
- **Bereich**: 30-300 Sekunden
- **Beschreibung**: Intervall für automatisches Speichern im Editor

#### Editor Layout
- **Schlüssel**: `editor_layout`
- **Typ**: String
- **Standard**: `"vertical"`
- **Optionen**: `vertical`, `horizontal`

### Dashboard-Einstellungen

#### Recent Files Limit
- **Schlüssel**: `recent_files_limit`
- **Typ**: Integer
- **Standard**: `10`
- **Bereich**: 5-50
- **Beschreibung**: Anzahl der zuletzt bearbeiteten Dateien im Dashboard

#### Show File Stats
- **Schlüssel**: `show_file_stats`
- **Typ**: Boolean
- **Standard**: `true`
- **Beschreibung**: Zeigt Datei-Statistiken im Dashboard

### SEO-Einstellungen

#### Robots Policy
- **Schlüssel**: `robots_policy`
- **Typ**: String
- **Standard**: `"allow"`
- **Optionen**: `allow`, `block`
- **Beschreibung**: Grundsätzliche Suchmaschinen-Policy

#### Enable SEO Meta Tags
- **Schlüssel**: `enable_seo_meta`
- **Typ**: Boolean
- **Standard**: `true`
- **Beschreibung**: Aktiviert automatische SEO Meta-Tags

### Content-Einstellungen

#### Default Layout
- **Schlüssel**: `default_layout`
- **Typ**: String
- **Standard**: `"wiki"`
- **Beschreibung**: Standard-Layout für neue Seiten

#### Show Navigation Order
- **Schlüssel**: `show_navigation_order`
- **Typ**: Boolean
- **Standard**: `true`
- **Beschreibung**: Zeigt Navigation-Reihenfolge im Admin-Interface

#### Navigation Order
- **Schlüssel**: `navigation_order`
- **Typ**: Object
- **Standard**: `{}`
- **Beschreibung**: Reihenfolge der Navigation (Pfad → Priorität)

### Shortcode-Einstellungen

#### Default Pages Limit
- **Schlüssel**: `default_pages_limit`
- **Typ**: Integer
- **Standard**: `20`
- **Beschreibung**: Standard-Limit für `[pages]` Shortcode

#### Default Tags Limit
- **Schlüssel**: `default_tags_limit`
- **Typ**: Integer
- **Standard**: `30`
- **Beschreibung**: Standard-Limit für `[tags]` Shortcode

#### Pages Sort Order
- **Schlüssel**: `pages_sort_order`
- **Typ**: String
- **Standard**: `"alphabetical"`
- **Optionen**: `alphabetical`, `modified`, `created`

#### Tags Sort Order
- **Schlüssel**: `tags_sort_order`
- **Typ**: String
- **Standard**: `"alphabetical"`
- **Optionen**: `alphabetical`, `frequency`

---

## Konfigurationsdatei

Die Einstellungen werden in `system/settings.json` gespeichert:

```json
{
    "site_name": "StaticMD",
    "site_logo": "",
    "site_description": "Professional Markdown CMS",
    "frontend_theme": "bootstrap",
    "editor_theme": "github",
    "auto_save_interval": 60,
    "editor_layout": "vertical",
    "recent_files_limit": 10,
    "show_file_stats": true,
    "robots_policy": "allow",
    "enable_seo_meta": true,
    "default_layout": "wiki",
    "show_navigation_order": true,
    "navigation_order": {
        "about": 1,
        "blog": 2,
        "help": 3
    },
    "default_pages_limit": 20,
    "default_tags_limit": 30,
    "pages_sort_order": "alphabetical",
    "tags_sort_order": "alphabetical"
}
```

---

## Admin-Interface

### Settings-Verwaltung

Das Settings-Interface ist über **Admin → Settings** erreichbar und bietet:

1. **Website-Einstellungen**: Site Name, Logo, Beschreibung
2. **Theme-Auswahl**: Frontend und Editor Themes mit Live-Vorschau
3. **Editor-Konfiguration**: Auto-Save, Layout, Theme
4. **Dashboard-Optionen**: Recent Files, Statistiken
5. **SEO-Kontrollen**: Robots Policy, Meta Tags
6. **Content-Einstellungen**: Standard-Layout, Navigation
7. **Shortcode-Parameter**: Limits und Sortierung

### Navigation-Management

Die Navigation-Reihenfolge kann über das Settings-Interface verwaltet werden:

- **Automatische Erkennung**: Alle Hauptordner werden erkannt
- **Prioritäten setzen**: Numerische Werte für Reihenfolge
- **Live-Vorschau**: Änderungen werden sofort sichtbar
- **Alphabetische Fallback**: Ordner ohne Priorität werden alphabetisch sortiert

---

## Programmatische Verwendung

### Settings laden

```php
use StaticMD\Core\Application;

$app = new Application();
$settings = $app->getSettings();

$siteName = $settings['site_name'] ?? 'StaticMD';
$theme = $settings['frontend_theme'] ?? 'bootstrap';
```

### Settings speichern

```php
use StaticMD\Admin\AdminController;

$admin = new AdminController();
$admin->updateSettings([
    'site_name' => 'Meine Website',
    'frontend_theme' => 'solarized-dark'
]);
```

### Einzelne Settings abfragen

```php
$app = new Application();

// Mit Fallback-Wert
$autoSave = $app->getSetting('auto_save_interval', 60);

// Ohne Fallback
$theme = $app->getSetting('frontend_theme');
```

---

## Validierung und Fehlerbehandlung

### Automatische Validierung

- **Theme-Namen**: Werden gegen verfügbare Themes validiert
- **Numerische Werte**: Bereich-Validierung (z.B. 30-300 für Auto-Save)
- **Boolean-Werte**: Strenge Typ-Konvertierung
- **String-Werte**: HTML-Escaping für Sicherheit

### Fehlerbehandlung

```php
try {
    $admin->updateSettings($newSettings);
    $message = "Settings erfolgreich gespeichert!";
} catch (Exception $e) {
    $error = "Fehler beim Speichern: " . $e->getMessage();
}
```

### Fallback-Verhalten

- **Fehlende Settings**: Werden mit Standard-Werten ersetzt
- **Ungültige Werte**: Fallen auf Standard-Werte zurück
- **Korrupte JSON**: Wird mit leeren Settings überschrieben

---

## Migration und Upgrades

### Settings-Migration

Bei Updates werden neue Settings automatisch mit Standard-Werten hinzugefügt:

```php
private function migrateSettings(array $settings): array
{
    $defaults = $this->getDefaultSettings();
    return array_merge($defaults, $settings);
}
```

### Backup-Strategie

- **Automatische Backups**: Vor jeder Änderung wird eine Backup-Datei erstellt
- **Rollback-Funktion**: Wiederherstellung bei Fehlern möglich
- **Versioning**: Settings-Versionen werden getrackt

---

## Erweiterte Konfiguration

### Custom Settings

Entwickler können eigene Settings hinzufügen:

```php
// In config.php
'custom_settings' => [
    'my_feature_enabled' => true,
    'my_api_key' => 'secret'
]
```

### Theme-spezifische Settings

```php
'theme_settings' => [
    'bootstrap' => [
        'navbar_style' => 'dark',
        'container_fluid' => false
    ],
    'solarized-dark' => [
        'syntax_highlighting' => true
    ]
]
```

---

## Troubleshooting

### Häufige Probleme

#### Settings werden nicht gespeichert
- **Ursache**: Keine Schreibrechte auf `system/settings.json`
- **Lösung**: `chmod 664 system/settings.json`

#### Theme wird nicht geladen  
- **Ursache**: Theme-Name falsch geschrieben
- **Lösung**: Überprüfe verfügbare Themes in `system/themes/`

#### Auto-Save funktioniert nicht
- **Ursache**: JavaScript-Error oder ungültiges Intervall
- **Lösung**: Browser-Konsole prüfen, Intervall zwischen 30-300s setzen

### Debug-Modus

```php
// In config.php
'system' => [
    'debug' => true  // Zeigt detaillierte Settings-Logs
]
```

---

## Performance-Optimierung

### Settings-Caching

- **Memory-Cache**: Settings werden nach dem ersten Laden gecacht
- **File-Cache**: Vermeidet wiederholtes JSON-Parsing
- **Invalidierung**: Cache wird bei Änderungen automatisch geleert

### Best Practices

1. **Minimal Settings**: Nur notwendige Settings in JSON speichern
2. **Lazy Loading**: Settings erst bei Bedarf laden
3. **Batch Updates**: Mehrere Settings in einem Request ändern
4. **Validierung**: Client-seitige Validierung für bessere UX

---

*StaticMD Settings System - Professional Configuration Management*