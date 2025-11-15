---
Title: Shortcodes Reference
Author: StaticMD Team
Tag: shortcodes, markdown, content
Layout: Standard
---

# Shortcodes Reference

Comprehensive documentation of all available shortcodes in StaticMD for advanced content functionality.

---

## Overview

Shortcodes extend the standard Markdown syntax with dynamic content and advanced layout options. StaticMD supports both custom shortcodes and Yellow CMS-compatible syntax.

## Content Shortcodes

### [pages] - Generate Page Lists

Automatically generates lists of Markdown files from a specific directory.

#### Basic Syntax
```markdown
[pages /path/ limit]
[pages /path/ limit layout]
```

#### Parameters
- **`/path/`**: Path to directory (relative to `/content/`)
- **`limit`**: Maximum number of files (default: 20)
- **`layout`**: `rows` (default) or `columns`

#### Examples

**Simple List**:
```markdown
[pages /blog/ 10]
```

**Column Layout**:
```markdown
[pages /tech/ 15 columns]
```

**Root Directory**:
```markdown
[pages / 5]
```

#### Output Format (Rows)
- Chronological list with titles
- Clickable links to pages
- Automatic title extraction from Front Matter
- Last modification date

#### Output Format (Columns)
- Bootstrap grid with cards
- Responsive 2-3 columns depending on screen size
- Compact display for overviews

#### Sorting Options
- **Alphabetical**: Default sorting by filename
- **Date**: By last modification (newest first)
- **Title**: By extracted title from Front Matter

### [tags] - Create Tag Clouds

Creates tag clouds with all available tags from a directory.

#### Basic Syntax
```markdown
[tags /path/ limit]
[tags /path/ limit layout]
```

#### Parameters
- **`/path/`**: Path to directory for tag extraction
- **`limit`**: Maximum number of tags (default: 30)
- **`layout`**: `cloud` (default) or `list`

#### Examples

**Standard Tag Cloud**:
```markdown
[tags /blog/ 20]
```

**Tag List**:
```markdown
[tags /tech/ 15 list]
```

**All Tags**:
```markdown
[tags / 50]
```

#### Output Format (Cloud)
- Size-weighted tags based on frequency
- Clickable links to tag filter pages
- Bootstrap badges with different sizes
- Alphabetical or frequency sorting

#### Output Format (List)
- Compact list with tag names
- Usage count in parentheses
- Row-based display

---

## Layout Shortcodes

### [accordionstart] / [accordionstop] - Bootstrap Accordions

Creates collapsible Bootstrap 5 accordion areas.

#### Syntax
```markdown
[accordionstart id "Title"]
Content of the accordion area...
[accordionstop]
```

#### Parameters
- **`id`**: Unique ID for the accordion (letters/numbers only)
- **`"Title"`**: Visible title of the accordion header

#### Example
```markdown
[accordionstart install "Installation"]
## Step 1: Download
Download the latest version...

## Step 2: Configuration
Edit the config.php...
[accordionstop]

[accordionstart config "Configuration"]
### Database Setup
Connection to database...
[accordionstop]
```

#### Features
- **Bootstrap 5 compatible**: Modern accordion styles
- **Full Markdown**: Markdown syntax is processed within
- **Unique IDs**: Prevents conflicts with multiple accordions
- **Responsive Design**: Mobile-optimized display

#### Nested Accordions
```markdown
[accordionstart main "Main Area"]
Introduction text...

[accordionstart sub "Sub Area"]
Detailed information...
[accordionstop]

Additional text...
[accordionstop]
```

---

## Yellow CMS Compatibility

### [image] - Responsive Images

Yellow CMS-compatible image syntax for responsive images.

#### Syntax
```markdown
[image filename.jpg Description Class Size]
```

#### Parameters
- **`filename.jpg`**: Image file in `/public/images/`
- **`Description`**: Alt text (use `-` for empty)
- **`Class`**: CSS class (use `-` for none)
- **`Size`**: Percentage width (e.g. `50%`)

#### Examples

**Simple Image**:
```markdown
[image screenshot.png - - 100%]
```

**With Description and Size**:
```markdown
[image logo.svg "StaticMD Logo" center 30%]
```

**Responsive Image**:
```markdown
[image banner.jpg "Banner Image" img-fluid 80%]
```

#### Output
```html
<img src="/public/images/screenshot.png" 
     alt="StaticMD Logo" 
     class="center img-responsive" 
     style="width: 30%;">
```

---

## Configuration

### Default Limits

Default values can be configured in `system/settings.json`:

```json
{
    "default_pages_limit": 20,
    "default_tags_limit": 30,
    "pages_sort_order": "alphabetical",
    "tags_sort_order": "alphabetical"
}
```

### Sorting Options

#### Pages Sorting
- **`alphabetical`**: By filename (default)
- **`modified`**: By modification date
- **`created`**: By creation date
- **`title`**: By extracted title

#### Tags Sorting
- **`alphabetical`**: Alphabetical (default)
- **`frequency`**: By frequency

### Performance Settings

```php
// In config.php
'shortcodes' => [
    'cache_enabled' => true,
    'cache_duration' => 3600,  // 1 hour
    'max_files_scan' => 1000   // Limit for large directories
]
```

---

## Advanced Usage

### Combined Shortcodes

```markdown
# Project Overview

## All Projects
[pages /projects/ 10 columns]

## Popular Tags
[tags /projects/ 15 cloud]

## Detailed Guides
[accordionstart details "Installation & Setup"]
### Step-by-Step Guide
[pages /help/installation/ 5]
[accordionstop]
```

### Conditional Content

```markdown
<!-- Only on homepage -->
[pages / 5]

<!-- Blog section -->
[pages /blog/ 10]
[tags /blog/ 20]

<!-- Technical documentation -->
[accordionstart api "API Documentation"]
[pages /api/ 15 list]
[accordionstop]
```

### Responsive Layouts

```markdown
<!-- Mobile-optimized -->
[pages /portfolio/ 6 columns]

<!-- Desktop lists -->
[pages /articles/ 20 rows]

<!-- Tag navigation -->
[tags / 25 cloud]
```

---

## Debugging and Troubleshooting

### Common Issues

#### Shortcode not processed
- **Cause**: Syntax error or missing parameters
- **Solution**: Check parameter order and spelling

#### Path not found
- **Cause**: Wrong path to content directory
- **Solution**: Specify path relative to `/content/`

#### Images not displayed
- **Cause**: Image file not in `/public/images/`
- **Solution**: Check file path and permissions

### Performance Optimization

#### Best Practices

1. **Set Limits**: Query large directories with limits
2. **Choose Layout**: `columns` for many entries, `rows` for details
3. **Use Caching**: Enable cache settings when available
4. **Optimize Paths**: Use specific paths instead of root directory

#### Monitoring Performance

Large directories or many shortcodes can impact page loading times. Consider:
- Using reasonable limits (10-20 items)
- Organizing content in focused subdirectories
- Implementing caching strategies for frequently accessed content

---

## Migration from Other CMS

### From Yellow CMS

StaticMD is fully compatible with Yellow CMS shortcodes:

```markdown
<!-- Yellow CMS -->
[image photo.jpg "Description" left 50%]

<!-- StaticMD (identical) -->
[image photo.jpg "Description" left 50%]
```

### From Jekyll

```markdown
<!-- Jekyll -->
{% for post in site.posts limit:10 %}
  {{ post.title }}
{% endfor %}

<!-- StaticMD Equivalent -->
[pages /blog/ 10]
```

---

*StaticMD Shortcodes - Advanced Content Functionality for Markdown*