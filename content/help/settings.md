---
Title: Settings System
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

#### Site Logo
- **Key**: `site_logo`
- **Type**: String (path)
- **Default**: `""`
- **Description**: Path to logo image (relative to `/public/images/`)

#### Site Description
- **Key**: `site_description`
- **Type**: String
- **Default**: `"Professional Markdown CMS"`
- **Description**: Brief website description for meta tags

### Frontend Themes

#### Frontend Theme
- **Key**: `frontend_theme`
- **Type**: String
- **Default**: `"bootstrap"`
- **Options**: 
  - `bootstrap` - Standard Bootstrap Theme
  - `solarized-light` - Solarized Light Theme
  - `solarized-dark` - Solarized Dark Theme
  - `monokai-light` - Monokai Light Theme
  - `monokai-dark` - Monokai Dark Theme
  - `github-light` - GitHub Light Theme
  - `github-dark` - GitHub Dark Theme

#### Editor Theme
- **Key**: `editor_theme`
- **Type**: String
- **Default**: `"github"`
- **Options**: 
  - `github` - GitHub Light Editor
  - `monokai` - Monokai Dark Editor
  - `solarized-light` - Solarized Light Editor
  - `solarized-dark` - Solarized Dark Editor
  - `material` - Material Design Editor

### SEO Settings

#### Enable SEO Meta Tags
- **Key**: `enable_seo_meta`
- **Type**: Boolean
- **Default**: `true`
- **Description**: Enable automatic generation of SEO meta tags

#### Default Robots Setting
- **Key**: `default_robots`
- **Type**: String
- **Default**: `"index,follow"`
- **Options**: 
  - `index,follow` - Allow indexing and following links
  - `noindex,nofollow` - Block indexing and following links
  - `index,nofollow` - Allow indexing but block following links
  - `noindex,follow` - Block indexing but allow following links

#### Enable Search Engine Blocking
- **Key**: `block_search_engines`
- **Type**: Boolean
- **Default**: `false`
- **Description**: Globally block search engines from indexing the site

### Navigation Settings

#### Navigation Order
- **Key**: `navigation_order`
- **Type**: Object
- **Description**: Custom ordering for main navigation items
- **Example**:
```json
{
  "about": 1,
  "blog": 2,
  "help": 3
}
```

#### Show Homepage in Navigation
- **Key**: `show_homepage_in_nav`
- **Type**: Boolean
- **Default**: `true`
- **Description**: Display homepage link in main navigation

### Editor Settings

#### Auto-Save Interval
- **Key**: `auto_save_interval`
- **Type**: Integer (seconds)
- **Default**: `60`
- **Options**: `30`, `60`, `120`, `300` or `0` (disabled)
- **Description**: Automatic saving interval for editor content

#### Show Line Numbers
- **Key**: `editor_line_numbers`
- **Type**: Boolean
- **Default**: `true`
- **Description**: Display line numbers in code editor

#### Enable Live Preview
- **Key**: `enable_live_preview`
- **Type**: Boolean
- **Default**: `true`
- **Description**: Enable real-time preview in editor

### Content Settings

#### Default Layout
- **Key**: `default_layout`
- **Type**: String
- **Default**: `"wiki"`
- **Options**: `standard`, `wiki`, `blog`, `page`
- **Description**: Default layout for new pages

#### Enable Tag Clouds
- **Key**: `enable_tag_clouds`
- **Type**: Boolean
- **Default**: `true`
- **Description**: Enable tag cloud generation for folders

#### Pages Per Folder Overview
- **Key**: `pages_per_folder`
- **Type**: Integer
- **Default**: `20`
- **Description**: Maximum pages shown in folder overviews

### Search Settings

#### Enable Search
- **Key**: `enable_search`
- **Type**: Boolean
- **Default**: `true`
- **Description**: Enable site-wide search functionality

#### Search Results Per Page
- **Key**: `search_results_per_page`
- **Type**: Integer
- **Default**: `10`
- **Description**: Number of search results per page

#### Search in Content
- **Key**: `search_in_content`
- **Type**: Boolean
- **Default**: `true`
- **Description**: Include page content in search results

---

## Configuration File Structure

### system/settings.json

```json
{
    "site_name": "StaticMD",
    "site_logo": "",
    "frontend_theme": "bootstrap",
    "recent_files_count": 20,
    "items_per_page": 25,
    "editor_theme": "github",
    "show_file_stats": true,
    "auto_save_interval": 30,
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

1. **General Tab**: Basic website settings
2. **Themes Tab**: Frontend and editor theme selection
3. **SEO Tab**: Search engine optimization settings
4. **Navigation Tab**: Navigation structure and ordering
5. **Editor Tab**: Editor behavior and preferences
6. **Content Tab**: Content display and organization
7. **Search Tab**: Search functionality configuration

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
        'navigation' => ['navigation_order', 'show_homepage_in_nav'],
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

---

*StaticMD Settings System v2.0 - Flexible Configuration Management*