---
Title: 3. Settings System
Author: System
Tag: admin, configuration, settings
Layout: Standard
---

# Settings System

Comprehensive documentation of the StaticMD settings system with all available configuration options.

---

## Overview

The StaticMD settings system uses JSON-based configuration with a user-friendly admin interface. All settings are stored in `system/settings.json` and can be managed through the admin interface.

## Available Settings

### Website Basic Settings

#### Site Name
- **Key**: `site_name`
- **Type**: String
- **Default**: `"StaticMD"`
- **Description**: Website name, displayed in browser tab and header
- **Status**: ✅ **Implemented**

#### Site Logo
- **Key**: `site_logo`
- **Type**: String (URL)
- **Default**: `""`
- **Description**: URL to logo image (full URL or relative path)
- **Status**: ✅ **Implemented**

#### Language
- **Key**: `language`
- **Type**: String
- **Default**: `"en"`
- **Options**: `en` (English), `de` (Deutsch)
- **Description**: Interface language for admin and frontend
- **Status**: ✅ **Implemented**

### Frontend Themes

#### Frontend Theme
- **Key**: `frontend_theme`
- **Type**: String
- **Default**: `"bootstrap"`
- **Status**: ✅ **Implemented**
- **Options**: 
  - `bootstrap` - Standard Bootstrap Theme
  - `solarized-light` - Solarized Light Theme
  - `solarized-dark` - Solarized Dark Theme
  - `monokai-light` - Monokai Light Theme
  - `monokai-dark` - Monokai Dark Theme
  - `github-light` - GitHub Light Theme
  - `github-dark` - GitHub Dark Theme
  - `adcore` - Adcore Custom Theme
  - `static-md` - StaticMD Default Theme

#### Editor Theme
- **Key**: `editor_theme`
- **Type**: String
- **Default**: `"github"`
- **Status**: ✅ **Implemented**
- **Options**: 
  - `github` - GitHub Light Editor
  - `monokai` - Monokai Dark Editor
  - `solarized-light` - Solarized Light Editor
  - `solarized-dark` - Solarized Dark Editor
  - `material` - Material Design Editor

### SEO Settings

#### SEO Robots Policy
- **Key**: `seo_robots_policy`
- **Type**: String
- **Default**: `"index,follow"`
- **Status**: ✅ **Implemented**
- **Options**: 
  - `index,follow` - Allow indexing and following links
  - `noindex,nofollow` - Block indexing and following links
  - `index,nofollow` - Allow indexing but block following links
  - `noindex,follow` - Block indexing but allow following links

#### Block Search Engine Crawlers
- **Key**: `seo_block_crawlers`
- **Type**: Boolean
- **Default**: `false`
- **Status**: ✅ **Implemented**
- **Description**: Globally block search engines from indexing the site

#### Generate robots.txt
- **Key**: `seo_generate_robots_txt`
- **Type**: Boolean
- **Default**: `true`
- **Status**: ✅ **Implemented**
- **Description**: Automatically generate robots.txt at `/robots.txt`

### Navigation Settings

#### Navigation Show Dropdowns
- **Key**: `navigation_show_dropdowns`
- **Type**: Boolean
- **Default**: `true`
- **Status**: ✅ **Implemented** but not available in every theme
- **Description**: Controls whether top-level navigation displays dropdown menus for folders with subpages
- **Options**: 
  - `true` - Show dropdown menus for folders with subpages (default)
  - `false` - Display all navigation links directly (flat navigation)

#### Navigation Order
- **Key**: `navigation_order`
- **Type**: Object
- **Status**: ✅ **Implemented** 
- **Description**: Custom ordering (UI: drag & drop) for main navigation items
- **Example**:
```json
{
  "about": 1,
  "blog": 2,
  "help": 3
}
```

### Editor Settings

#### Auto-Save Interval
- **Key**: `auto_save_interval`
- **Type**: Integer (seconds)
- **Default**: `30`
- **Status**: ✅ **Implemented**
- **Options**: `30` to `300` seconds (slider in admin)
- **Description**: Automatic saving interval for editor content

### Dashboard Settings

#### Recent Files Count
- **Key**: `recent_files_count`
- **Type**: Integer
- **Default**: `20`
- **Status**: ✅ **Implemented**
- **Range**: 5-50 (slider in admin)
- **Description**: Number of recent files shown in dashboard

#### Show File Statistics
- **Key**: `show_file_stats`
- **Type**: Boolean
- **Default**: `true`
- **Status**: ✅ **Implemented**
- **Description**: Display file statistics in dashboard

#### Search Result Limit
- **Key**: `search_result_limit`
- **Type**: Integer
- **Default**: `200`
- **Status**: ✅ **Implemented**
- **Range**: 10-200 (slider in admin)
- **Description**: Maximum search results returned

---

## Configuration File Structure

### system/settings.json

```json
{
    "site_name": "StaticMD",
    "site_logo": "https://staticmd.adcore.de/logo.png",
    "frontend_theme": "bootstrap",
    "recent_files_count": 20,
    "editor_theme": "github",
    "show_file_stats": true,
    "auto_save_interval": 30,
    "navigation_show_dropdowns": true,
    "navigation_order": {
        "blog": 1,
        "help": 2,
        "about": 3
    },
    "language": "en",
    "search_result_limit": 200,
    "seo_robots_policy": "noindex,nofollow",
    "seo_block_crawlers": true,
    "seo_generate_robots_txt": true
}
```
---

## Admin Interface

### Settings Management

Access via **Admin → Settings**:

**Single-Page Interface** with the following sections:
1. **Website Settings**: Site name, logo, language
2. **Dashboard Settings**: Recent files, statistics, search limits
3. **Frontend Theme**: Theme selection with 9 available themes
4. **Editor Settings**: Editor theme with live preview, auto-save interval
5. **Navigation Settings**: Dropdown behavior control, custom ordering with live preview
6. **SEO Settings**: Robots policy, crawler blocking, robots.txt generation
7. **Backup System**: Create downloadable backups with statistics

---

## Programmatic Access

### Reading Settings

```php
// Using TemplateEngine
$siteName = $this->getSetting('site_name', 'StaticMD');
$theme = $this->getSetting('frontend_theme', 'bootstrap');

// Direct JSON access
$settings = json_decode(file_get_contents('system/settings.json'), true);
$siteName = $settings['site_name'] ?? 'StaticMD';
```

### Writing Settings

```php
// Load current settings
$settings = json_decode(file_get_contents('system/settings.json'), true);

// Update settings
$settings['site_name'] = 'My New Site';
$settings['frontend_theme'] = 'solarized-dark';

// Save settings
file_put_contents('system/settings.json', json_encode($settings, JSON_PRETTY_PRINT));
```

### Settings Validation

```php
class SettingsValidator 
{
    public function validate(array $settings): array 
    {
        $errors = [];
        
        // Required fields
        if (empty($settings['site_name'])) {
            $errors[] = 'Site name is required';
        }
        
        // Valid theme
        $validThemes = ['bootstrap', 'solarized-light', 'solarized-dark'];
        if (!in_array($settings['frontend_theme'], $validThemes)) {
            $errors[] = 'Invalid frontend theme';
        }
        
        // Auto-save interval
        if ($settings['auto_save_interval'] < 0 || $settings['auto_save_interval'] > 600) {
            $errors[] = 'Auto-save interval must be between 0 and 600 seconds';
        }
        
        return $errors;
    }
}
```

---

## Environment-Specific Settings

### Development Settings

```json
{
    "site_name": "StaticMD (Dev)",
    "enable_seo_meta": false,
    "block_search_engines": true,
    "auto_save_interval": 30,
    "debug_mode": true
}
```

### Production Settings

```json
{
    "site_name": "My Production Site",
    "enable_seo_meta": true,
    "block_search_engines": false,
    "auto_save_interval": 120,
    "debug_mode": false
}
```

### Settings Override

```php
// config.php - Override specific settings
$config['settings_override'] = [
    'development' => [
        'block_search_engines' => true,
        'debug_mode' => true
    ],
    'production' => [
        'block_search_engines' => false,
        'debug_mode' => false
    ]
];
```

---

## Custom Settings

### Adding New Settings

1. **Update settings.json**:
```json
{
    "my_custom_setting": "default_value"
}
```

2. **Add to Admin Interface**:
```php
// In admin/templates/settings.php
<div class="mb-3">
    <label for="my_custom_setting" class="form-label">My Custom Setting</label>
    <input type="text" class="form-control" id="my_custom_setting" 
           name="my_custom_setting" value="<?= htmlspecialchars($settings['my_custom_setting'] ?? '') ?>">
</div>
```

3. **Use in Templates**:
```php
$customValue = $this->getSetting('my_custom_setting', 'default');
```

### Setting Categories

```php
class SettingsManager 
{
    private $categories = [
        'general' => ['site_name', 'site_logo', 'site_description'],
        'themes' => ['frontend_theme', 'editor_theme'],
        'seo' => ['enable_seo_meta', 'default_robots', 'block_search_engines'],
        'navigation' => ['navigation_show_dropdowns', 'navigation_order', 'show_homepage_in_nav'],
        'editor' => ['auto_save_interval', 'editor_line_numbers', 'enable_live_preview'],
        'content' => ['default_layout', 'enable_tag_clouds', 'pages_per_folder'],
        'search' => ['enable_search', 'search_results_per_page', 'search_in_content']
    ];
    
    public function getSettingsByCategory(string $category): array 
    {
        $settings = $this->loadSettings();
        $categorySettings = [];
        
        if (isset($this->categories[$category])) {
            foreach ($this->categories[$category] as $key) {
                $categorySettings[$key] = $settings[$key] ?? null;
            }
        }
        
        return $categorySettings;
    }
}
```

---

## Migration and Backup

### Settings Backup

```php
// Create backup
$settings = file_get_contents('system/settings.json');
$backup = [
    'timestamp' => date('Y-m-d H:i:s'),
    'settings' => json_decode($settings, true)
];
file_put_contents('backup/settings_' . date('Y-m-d_H-i-s') . '.json', 
                  json_encode($backup, JSON_PRETTY_PRINT));
```

### Settings Restore

```php
// Restore from backup
$backupFile = 'backup/settings_2024-01-15_10-30-00.json';
$backup = json_decode(file_get_contents($backupFile), true);
file_put_contents('system/settings.json', 
                  json_encode($backup['settings'], JSON_PRETTY_PRINT));
```

### Migration Script

```php
class SettingsMigration 
{
    public function migrateFrom_v1_to_v2(): void 
    {
        $settings = json_decode(file_get_contents('system/settings.json'), true);
        
        // Add new settings with defaults
        $settings['enable_live_preview'] = true;
        $settings['search_in_content'] = true;
        
        // Remove deprecated settings
        unset($settings['old_deprecated_setting']);
        
        // Rename settings
        if (isset($settings['old_name'])) {
            $settings['new_name'] = $settings['old_name'];
            unset($settings['old_name']);
        }
        
        file_put_contents('system/settings.json', json_encode($settings, JSON_PRETTY_PRINT));
    }
}
```

---

## Performance Considerations

### Settings Caching

```php
class SettingsCache 
{
    private static $cache = null;
    
    public static function get(string $key, $default = null) 
    {
        if (self::$cache === null) {
            self::$cache = json_decode(file_get_contents('system/settings.json'), true);
        }
        
        return self::$cache[$key] ?? $default;
    }
    
    public static function invalidate(): void 
    {
        self::$cache = null;
    }
}
```

### Lazy Loading

```php
// Only load settings when needed
class LazySettings 
{
    private $settings = null;
    
    public function get(string $key, $default = null) 
    {
        if ($this->settings === null) {
            $this->settings = json_decode(file_get_contents('system/settings.json'), true);
        }
        
        return $this->settings[$key] ?? $default;
    }
}
```

---

## Troubleshooting

### Common Issues

#### Settings not saving
- **Cause**: File permissions or JSON syntax error
- **Solution**: Check file permissions and validate JSON

#### Default values not working
- **Cause**: Missing fallback values in code
- **Solution**: Always provide default values when accessing settings

#### Settings UI not updating
- **Cause**: Browser cache or incorrect form handling
- **Solution**: Clear cache and check form submission

### Debug Settings

```php
// Enable settings debugging
$config['debug_settings'] = true;

// Log all settings access
if ($config['debug_settings']) {
    error_log("Settings access: {$key} = " . json_encode($value));
}
```

### Settings Validation

```php
// Validate settings file
$json = file_get_contents('system/settings.json');
$settings = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    throw new Exception('Invalid JSON in settings.json: ' . json_last_error_msg());
}
```
