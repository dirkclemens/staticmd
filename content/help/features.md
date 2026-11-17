---
Title: 2. Feature Overview
Tag: features, documentation
Author: System
Visibility: public
---

# Feature Overview

> **Status Legend**: ✅ Fully Implemented | 🚧 In Development | ❌ Not Implemented

**Total Features**: 45+ implemented features across 8 major categories

## Main Features

### 🎨 Frontend & Design
- ✅ **9 Frontend Themes**: Bootstrap, Solarized Light/Dark, Monokai Light/Dark, GitHub Light/Dark, Static-MD, AdCore
- ✅ **5 Editor Themes**: GitHub, Monokai, Solarized Light/Dark, Material
- ✅ **Gallery Layout**: Special gallery layout with lightbox, grid display, and tag filtering
- ✅ **Live Theme Switching**: Real-time theme preview in admin
- ✅ **Responsive Design**: Bootstrap 5 based, mobile-friendly
- ✅ **Theme Helper**: Centralized theme management and navigation building

### 🛠️ Admin Interface
- ✅ **Complete Admin Dashboard**: File statistics, recent files, system info
- ✅ **Professional Editor**: CodeMirror with enhanced toolbar
- ✅ **Container & Alert Menu**: Insert alerts, column layouts, boxes and collapsible sections (wraps the selection)
- ✅ **Text Tools**: UPPER/lower/Sentence/Title case, camelCase, snake_case, kebab-case, sort/dedupe lines, clean whitespace
- ✅ **Line Operations**: Move (`Alt+↑/↓`) and duplicate (`Shift+Alt+↑/↓`) lines, multi-line selections and multiple cursors
- ✅ **Autocomplete**: Word suggestions from all pages + current text from the 4th letter, ranked by frequency
- ✅ **Drag & Drop Upload**: PDF/ZIP files with automatic download tag insertion
- ✅ **Image Upload**: Drag & drop image upload with automatic insertion
- ✅ **Delete Function**: File deletion with confirmation dialog
- ✅ **Return-URL Navigation**: Smart navigation after edit operations
- ✅ **Settings System**: Comprehensive JSON-based configuration
- ✅ **Auto-Save**: Configurable intervals (30-300 seconds)
- ✅ **Backup System**: Complete site backup with download

### 📝 Content & Markdown
- ✅ **Unicode/Umlaut Support**: Full German umlaut support with normalization
- ✅ **Yellow CMS Compatibility**: Import and syntax compatibility
- ✅ **Emoji Support**: ~150 GitHub-style emojis (`:smile:` → 😄)
- ✅ **LaTeX Support**: Inline (`$formula$`) and block (`$$formula$$`) math
- ✅ **Enhanced Markdown**: Headers with IDs, tables, code blocks, autolinks
- ✅ **Alerts**: GitHub-style `> [!NOTE]`, `[!TIP]`, `[!IMPORTANT]`, `[!WARNING]`, `[!CAUTION]` with custom titles and folding
- ✅ **Container Blocks**: Pandoc/Quarto-style `:::` fenced divs – columns, boxes, details, callouts, custom classes and attributes

### 🔍 Search & Navigation
- ✅ **Full-text Search**: Weighted relevance search with tag filtering
- ✅ **Tag System**: Content tagging with `/tag/tagname` routes
- ✅ **Navigation Ordering**: Configurable navigation priority system
- ✅ **Breadcrumb Navigation**: Automatic breadcrumb generation
- ✅ **Clean URLs**: SEO-friendly URL structure
- ✅ **Subdirectory Support**: Nested content organization

### 🎯 Shortcodes
- ✅ **`[pages /path/ limit]`**: Automatic page listings with pagination
- ✅ **`[tags /path/ limit]`**: Tag clouds with frequency-based sizing
- ✅ **`[folder /path/ limit]`**: Horizontal subfolder navigation
- ✅ **`[gallery folder]`**: Automatic image galleries with lightbox
- ✅ **`[accordion*]`**: Bootstrap 5 collapsible accordions
- ✅ **`[download file.pdf "Alt"]`**: Download links with Bootstrap icons
- ✅ **`[image name.png "Alt" - 50%]`**: Yellow CMS image syntax
- ✅ **Code-Block Protection**: Shortcodes in code blocks remain as text

### 🔐 Security
- ✅ **CSRF Protection**: All forms protected with CSRF tokens
- ✅ **Content Security Policy (CSP)**: Context-aware CSP headers
- ✅ **Comprehensive Security Headers**: X-Frame-Options, HSTS, X-XSS-Protection
- ✅ **Path Traversal Protection**: Safe file path handling
- ✅ **XSS Protection**: Input sanitization and output encoding
- ✅ **Session Security**: Secure session management with timeouts
- ✅ **Open Redirect Protection**: URL validation for redirects

### 🌐 SEO & Search Engines
- ✅ **Dynamic robots.txt Generation**: Context-aware robots.txt at `/robots.txt`
- ✅ **Per-Page Robots Meta Tags**: `Robots: noindex,nofollow` in front matter
- ✅ **SEO Settings**: Global crawler blocking and robots policy
- ✅ **Meta Tag Generation**: Automatic meta descriptions and author tags
- ✅ **Canonical URLs**: Support for canonical URL specification

### 🎛️ Privacy & Control
- ✅ **Privacy Controls**: Private pages visible only to admins
- ✅ **Visibility Settings**: `Visibility: private` in front matter
- ✅ **Admin-only Content**: Conditional content display
- ✅ **Session-based Authentication**: Secure admin access

## Shortcodes / Markdown Features

### ✅ Implemented Shortcodes
```markdown
# Content Listing
[pages /path/ limit]              # Page overviews with pagination
[tags /path/ limit]               # Tag clouds with frequency sizing  
[folder /path/ limit]             # Horizontal subfolder navigation

# Media & Downloads  
[download file.pdf "Alt-Text"]    # Download links with Bootstrap icons
[image image.jpg "Alt-Text" - 50%] # Images with Yellow CMS syntax
[gallery folder-name]             # Automatic image galleries

# Interactive Elements
[accordionstart id "Title"]
Content here...
[accordionstop]
```

### ✅ Enhanced Markdown
```markdown
# Headers with custom IDs {#custom-id}
**Bold** and *italic* text
~~Strikethrough~~ text
`inline code` and ```code blocks```

# Emoji support
:smile: :heart: :rocket: :thumbsup:

# LaTeX Math
Inline: $E = mc^2$
Block: $$\sum_{i=1}^n x_i$$

# Auto-linking
https://example.com becomes clickable

# Alerts (GitHub style)
> [!WARNING] Optional custom title
> Multi-line content with **Markdown**

# Container blocks (Pandoc/Quarto style)
:::: {.columns gap=2rem}
::: {.column width="40%" border align=center}
Left column
:::
::: column
Right column
:::
::::

::: details Show more
Hidden content
:::
```

See [Container Blocks & Alerts](/help/container-alerts) for all options.

## Front Matter Support

### ✅ Supported Fields
```markdown
---
Title: Page Title                    # Page title (required)
Author: Author Name                  # Content author  
Tag: seo, robots, documentation      # Comma-separated tags
Description: Meta description        # SEO meta description
Layout: gallery                      # Special layouts (wiki, gallery, etc.)
Visibility: private                  # Privacy control (private/public)
Robots: noindex,nofollow            # Per-page robots directive
Date: 2024-01-15                    # Content date
---
```

### 🎨 Special Layouts
- **`Layout: gallery`**: Image gallery with lightbox and filtering
- **`Layout: wiki`**: Standard wiki-style layout (default)
- **`Layout: blog`**: Blog post layout with meta information
- **`Layout: page`**: Simple page layout

## Implementation Details

### 📁 File Organization
- **PDF/ZIP uploads**: Stored in `/public/downloads/`
- **Image uploads**: Stored in `/public/images/`
- **Gallery images**: Organized in `/public/assets/galleries/folder-name/`
- **Content files**: Stored in `/content/` as Markdown files

### 🔧 Technical Features
- **Automatic tag insertion**: Upload creates appropriate shortcode
- **Bootstrap icons**: PDF (📄), ZIP (📦), generic files (📁)
- **Security validation**: File type and extension checking
- **Unicode normalization**: NFC/NFD handling for German umlauts
- **Clean URL routing**: `/content/about.md` → `/about`
- **Asset routing**: `/assets/images/file.jpg` routes to `/public/images/file.jpg`

### 🎯 Gallery System
- **Automatic loading**: `[gallery folder]` loads all images from folder
- **Lightbox integration**: GLightbox for full-screen viewing
- **Tag filtering**: Images can be tagged and filtered
- **Grid layout**: Responsive 4-column Bootstrap grid
- **Hover effects**: Image scaling and info overlays

### 🚀 Performance
- **Lazy loading**: Images loaded on demand
- **CSS/JS CDN**: Bootstrap and libraries from CDN
- **Efficient routing**: Single entry point with clean URL rewriting
- **Caching headers**: Appropriate cache control for static assets

## Recent Additions

### 🖼️ Gallery System (NEW)
- **Gallery Layout**: Dedicated layout for image collections
- **Automatic Gallery Loading**: `[gallery folder-name]` shortcode
- **Lightbox Functionality**: GLightbox integration for full-screen viewing
- **Tag-based Filtering**: Filter images by tags in gallery view
- **Responsive Grid**: Bootstrap 5 responsive image grid
- **Hover Effects**: Image scaling and information overlays

### 🔧 Enhanced Shortcode System (IMPROVED)
- **Pre-processing**: Shortcodes processed before Markdown parsing
- **Code Block Protection**: Shortcodes in code blocks remain as text
- **Parameter Flexibility**: Optional parameters and improved parsing
- **Error Handling**: Graceful fallbacks for invalid shortcodes

### 🎨 Theme System Expansion
- **9 Complete Themes**: All themes fully functional
- **Gallery Template**: Special gallery.php template for all themes
- **Theme Helper**: Centralized navigation and breadcrumb generation
- **Asset Integration**: Proper asset routing for theme resources

### 🔐 Security Enhancements
- **Enhanced CSP**: Context-aware Content Security Policy
- **External Image Support**: CSP updated for gallery external images
- **Asset Security**: Secure asset delivery with validation
- **Path Normalization**: Unicode-safe path handling