# ⚙️ Settings-System Dokumentation

StaticMD verfügt über ein umfangreiches Settings-System zur Konfiguration aller Aspekte des CMS.

## Übersicht

Das Settings-System besteht aus zwei Ebenen:
- **`config.php`**: Basis-Systemkonfiguration (statisch)
- **`system/settings.json`**: Benutzer-Settings (dynamisch, über Admin-Interface)

## Admin-Settings (system/settings.json)

### Zugriff

**Admin → Settings** im Dashboard aufrufen

### Verfügbare Einstellungen

#### 🏢 Site-Konfiguration

| Setting | Beschreibung | Standard | Beispiel |
|---------|-------------|----------|----------|
| `site_name` | Name der Website | "StaticMD" | "Mein Blog" |
| `site_logo` | Pfad zum Logo | "" | "/public/images/logo.png" |

#### 🎨 Theme-System

| Setting | Beschreibung | Optionen | Standard |
|---------|-------------|----------|----------|
| `frontend_theme` | Website-Aussehen | bootstrap, solarized-light, solarized-dark, monokai-light, monokai-dark, github-light, github-dark | "bootstrap" |
| `editor_theme` | Editor-Aussehen | github, monokai, solarized-light, solarized-dark, material | "github" |

#### 📊 Dashboard-Konfiguration

| Setting | Beschreibung | Bereich | Standard |
|---------|-------------|---------|----------|
| `recent_files_count` | Anzahl "Zuletzt bearbeitet" | 5-50 | 15 |
| `items_per_page` | Einträge pro Seite | 10-100 | 25 |
| `show_file_stats` | Datei-Statistiken anzeigen | true/false | true |

#### ✏️ Editor-Konfiguration

| Setting | Beschreibung | Bereich | Standard |
|---------|-------------|---------|----------|
| `auto_save_interval` | Auto-Save Intervall (Sekunden) | 30-300 | 60 |

### Beispiel settings.json

```json
{
    "site_name": "Meine Website",
    "site_logo": "/public/images/logo.png",
    "frontend_theme": "github-dark",
    "editor_theme": "monokai",
    "recent_files_count": 20,
    "items_per_page": 30,
    "show_file_stats": true,
    "auto_save_interval": 90
}
```

## Basis-Konfiguration (config.php)

### System-Settings

```php
'system' => [
    'name' => 'StaticMD',           // Fallback Site-Name
    'debug' => false,               // Debug-Modus (NIEMALS true in Produktion!)
    'timezone' => 'Europe/Berlin'   // PHP-Zeitzone
]
```

### Admin-Konfiguration

```php
'admin' => [
    'username' => 'admin',                    // Admin-Benutzername
    'password' => '$2y$10$xyz...',           // Bcrypt-Hash des Passworts
    'session_timeout' => 3600                // Session-Timeout in Sekunden
]
```

### Pfad-Konfiguration

```php
'paths' => [
    'content' => __DIR__ . '/content',           // Content-Verzeichnis
    'themes' => __DIR__ . '/system/themes',     // Theme-Verzeichnis
    'system' => __DIR__ . '/system',            // System-Verzeichnis
    'public' => __DIR__ . '/public'             // Öffentliche Dateien
]
```

### Theme-Konfiguration

```php
'theme' => [
    'default' => 'bootstrap',        // Fallback-Theme
    'template_extension' => '.php'   // Template-Dateierweiterung
]
```

## Settings-Verwaltung

### Programmatisch laden

```php
// Settings laden
$settingsFile = $config['paths']['system'] . '/settings.json';
$settings = [];
if (file_exists($settingsFile)) {
    $settings = json_decode(file_get_contents($settingsFile), true) ?: [];
}

// Setting mit Fallback abrufen
$siteName = $settings['site_name'] ?? $config['system']['name'];
$theme = $settings['frontend_theme'] ?? 'bootstrap';
```

### Settings speichern (Admin-Controller)

```php
private function saveSettings(): void
{
    $settings = [
        'site_name' => trim($_POST['site_name'] ?? 'StaticMD'),
        'frontend_theme' => $_POST['frontend_theme'] ?? 'bootstrap',
        // ... weitere Settings
    ];
    
    $this->saveSettingsToFile($settings);
}
```

## Theme-Integration

### Frontend-Theme laden

```php
// In TemplateEngine.php
private function getTemplatePath(): string
{
    $settingsFile = $this->config['paths']['system'] . '/settings.json';
    $settings = [];
    if (file_exists($settingsFile)) {
        $settings = json_decode(file_get_contents($settingsFile), true) ?: [];
    }
    
    $themeName = $settings['frontend_theme'] ?? $this->config['theme']['default'];
    return $this->config['paths']['themes'] . '/' . $themeName . '/template.php';
}
```

### Template-Integration

```php
// In template.php
$settingsFile = $config['paths']['system'] . '/settings.json';
$settings = [];
if (file_exists($settingsFile)) {
    $settings = json_decode(file_get_contents($settingsFile), true) ?: [];
}

$siteName = $settings['site_name'] ?? $config['system']['name'];
$siteLogo = $settings['site_logo'] ?? '';
```

## Settings-Validierung

### Bereichsprüfung

```php
// Im AdminController
$settings = [
    'recent_files_count' => max(5, min(50, (int)($_POST['recent_files_count'] ?? 15))),
    'items_per_page' => max(10, min(100, (int)($_POST['items_per_page'] ?? 25))),
    'auto_save_interval' => max(30, min(300, (int)($_POST['auto_save_interval'] ?? 60)))
];
```

### Theme-Validierung

```php
$validThemes = ['bootstrap', 'solarized-light', 'solarized-dark', 'monokai-light', 'monokai-dark', 'github-light', 'github-dark'];
$frontendTheme = in_array($_POST['frontend_theme'] ?? '', $validThemes) 
    ? $_POST['frontend_theme'] 
    : 'bootstrap';
```

## Sicherheit

### CSRF-Schutz

```php
// Settings-Formular
<input type="hidden" name="csrf_token" value="<?= $this->auth->generateCSRFToken() ?>">

// Verarbeitung
$csrfToken = $_POST['csrf_token'] ?? '';
if (!$this->auth->verifyCSRFToken($csrfToken)) {
    header('Location: /admin?action=settings&error=csrf_invalid');
    exit;
}
```

### Eingabe-Sanitization

```php
$settings = [
    'site_name' => trim($_POST['site_name'] ?? 'StaticMD'),
    'site_logo' => trim($_POST['site_logo'] ?? ''),
    // Boolean-Werte
    'show_file_stats' => isset($_POST['show_file_stats']),
];
```

### Datei-Berechtigungen

```bash
chmod 600 system/settings.json
chmod 600 config.php
```

## Backup & Recovery

### Settings-Backup

```bash
# Automatisches Backup vor Änderungen
cp system/settings.json system/settings.json.backup.$(date +%Y%m%d_%H%M%S)
```

### Recovery

```bash
# Settings zurücksetzen
rm system/settings.json
# → System nutzt Fallback-Werte aus config.php
```

## Troubleshooting

### Settings werden nicht gespeichert

1. **Berechtigungen prüfen:**
   ```bash
   ls -la system/settings.json
   chmod 644 system/settings.json
   ```

2. **JSON-Syntax prüfen:**
   ```bash
   php -r "json_decode(file_get_contents('system/settings.json'));"
   ```

3. **Schreibrechte testen:**
   ```bash
   touch system/test.json && rm system/test.json
   ```

### Theme wird nicht geladen

1. **Theme-Datei existiert:**
   ```bash
   ls -la system/themes/[theme-name]/template.php
   ```

2. **Fallback auf Bootstrap:**
   - Settings.json löschen oder theme auf "bootstrap" setzen

3. **PHP-Syntax prüfen:**
   ```bash
   php -l system/themes/[theme-name]/template.php
   ```

### Performance-Probleme

1. **Settings-Caching** implementieren
2. **JSON-Datei-Größe** überwachen
3. **Nur notwendige Settings** laden

## Best Practices

### Settings-Design

- **Sinnvolle Defaults** in config.php definieren
- **Validierung** aller Benutzereingaben
- **Fallback-Mechanismen** für fehlende Werte
- **Sichere Bereiche** für numerische Werte

### Performance

- **Settings cachen** bei häufigem Zugriff
- **Lazy Loading** von Settings implementieren  
- **JSON-Datei klein halten** (nur User-Settings)

### Sicherheit

- **CSRF-Tokens** bei allen Änderungen
- **Input-Sanitization** für alle Felder
- **Sichere Defaults** verwenden
- **Berechtigungen** regelmäßig prüfen