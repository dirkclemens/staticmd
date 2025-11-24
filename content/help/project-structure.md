---
Title: 2b. Project Structure
Author: System
Layout: Standard
Tag: features, documentation
---

# StaticMD - Overview

## 📦 Project Structure
```
staticMD/
├── index.php                 # Frontend entry point
├── config.php               # Main configuration
├── assets.php              # Asset delivery system
├── robots.php              # Dynamic robots.txt generator
├── .htaccess               # Apache URL rewriting
├── content/                # Markdown content
│   ├── index.md
│   ├── about/
│   ├── blog/
│   └── help/
├── system/                 # Core system
│   ├── core/              # Application logic
│   │   ├── Application.php        # Main orchestrator
│   │   ├── Router.php             # Unicode-aware routing
│   │   ├── ContentLoader.php      # Content management
│   │   ├── MarkdownParser.php     # Enhanced parser
│   │   ├── TemplateEngine.php     # Multi-theme engine
│   │   ├── SearchEngine.php       # Full-text search
│   │   ├── NavigationBuilder.php  # Navigation system
│   │   ├── I18n.php              # Internationalization
│   │   └── SecurityHeaders.php    # Security headers
│   ├── admin/             # Admin interface
│   │   ├── index.php             # Admin entry point
│   │   ├── AdminAuth.php         # Authentication
│   │   ├── AdminController.php   # CRUD operations
│   │   └── templates/            # Admin templates
│   ├── utilities/         # Helper functions
│   │   ├── FrontMatterParser.php
│   │   ├── UnicodeNormalizer.php
│   │   ├── TitleGenerator.php
│   │   └── UrlHelper.php
│   ├── processors/        # Content processors
│   │   └── ShortcodeProcessor.php
│   ├── renderers/         # Content renderers
│   │   ├── FolderOverviewRenderer.php
│   │   └── BlogListRenderer.php
│   ├── themes/            # 9 frontend themes
│   │   ├── bootstrap/
│   │   ├── solarized-light/
│   │   ├── solarized-dark/
│   │   ├── monokai-light/
│   │   ├── monokai-dark/
│   │   ├── github-light/
│   │   ├── github-dark/
│   │   ├── static-md/
│   │   └── adcore/
│   ├── lang/              # Internationalization
│   │   ├── de.php                # German translations
│   │   └── en.php                # English translations
│   └── settings.json      # Site configuration
└── public/                # Public assets
    ├── assets/            # General assets
    │   └── galleries/     # Gallery images
    ├── images/            # Uploaded images
    └── downloads/         # PDF/ZIP files
```
