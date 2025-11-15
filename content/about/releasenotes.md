---
Title: Release Notes v2.0.0
Author: StaticMD Team
Tag: release, version, changelog
Layout: default
---

# 🚀 StaticMD v2.0.0 - Complete Rewrite & Major Release

We're excited to announce StaticMD 2.0.0, a complete rewrite of our PHP-based Markdown CMS with significant improvements in architecture, functionality, and user experience.

## 🌟 Major New Features

### 📝 **Professional Admin Interface**
- **CodeMirror Editor** with syntax highlighting and 5 editor themes (GitHub, Monokai, Solarized Light/Dark, Material)
- **Enhanced Toolbar** with buttons for headings, formatting, lists, code blocks, tables, and media insertion
- **Live Preview** with real-time markdown rendering
- **Auto-save** functionality with configurable intervals (30-300 seconds)
- **File Management** with create, edit, delete, and directory operations
- **Statistics Dashboard** showing content overview and system information

### 🎨 **Multi-Theme System**
- **7 Beautiful Themes**: Bootstrap (default), Solarized Light/Dark, Monokai Light/Dark, GitHub Light/Dark, and custom themes
- **Dynamic Theme Switching** through admin settings
- **Responsive Design** with Bootstrap 5 integration
- **Admin Toolbar** integration across all themes

### 🌍 **Unicode & German Content Support**
- **Full Unicode Support** with proper umlaut handling (ä, ö, ü, ß)
- **NFD/NFC Normalization** for consistent file operations
- **German-optimized** routing and content processing
- **Yellow CMS Compatibility** for seamless migration

### 🔍 **Advanced Search & Navigation**
- **Full-text Search Engine** with weighted relevance (title > content > meta)
- **Tag-based Filtering** with `/tag/tagname` routes
- **Smart Navigation** with configurable ordering through admin interface
- **Folder Overview** generation for directory-based content organization

### 📋 **Rich Content Features**
- **Shortcode System**: `[pages]`, `[tags]`, `[accordionstart/stop]` with Bootstrap 5 components
- **150+ Emoji Support** with GitHub-style syntax (`:smile:`, `:heart:`, `:rocket:`)
- **Emoji Toolbar** with categorized selection (Gesichter, Reaktionen, Herzen, Aktivitäten, Technik)
- **Yellow CMS Image Syntax** with responsive image generation
- **Custom Header IDs** for better document structure

## 🏗️ **Technical Improvements**

### 💡 **Modern PHP Architecture**
- **PHP 8.3+ Support** with modern coding standards
- **PSR-4 Autoloading** for clean dependency management
- **Modular Design** with separated concerns (Router, ContentLoader, TemplateEngine)
- **Exception Handling** with comprehensive error management

### 🔒 **Enhanced Security**
- **CSRF Protection** on all admin operations
- **Session-based Authentication** with configurable timeouts
- **Path Traversal Prevention** in routing and file operations
- **Secure Password Hashing** with PHP's password_hash()
- **`.htaccess` Security Rules** blocking direct access to system files

### 🚀 **Performance Optimizations**
- **Content Caching** through efficient file operations
- **Unicode Normalization** with performance-optimized fallbacks
- **Columnar Layout Distribution** for large content lists
- **CDN Integration** for Bootstrap and external resources

## 🛠️ **Developer Experience**

### 📦 **Easy Setup & Deployment**
- **One-command Installation** with automatic configuration
- **Upload Script** for easy server deployment
- **Debug Mode** with comprehensive error reporting
- **System Requirements Check** with clear feedback

### 🔧 **Flexible Configuration**
- **JSON-based Settings** with admin UI management
- **Multi-environment Config** (development/production)
- **Configurable Paths** and system parameters
- **Theme and Editor Customization**

## 🎯 **Content Management**

### 📄 **Smart Content Processing**
- **Front Matter Support** with Yellow CMS compatibility
- **Automatic Navigation** generation from folder structure
- **Tag Cloud Generation** with frequency-based sizing
- **Content Statistics** and overview dashboards

### 🔗 **SEO & Web Standards**
- **Clean URL Structure** with Apache/Nginx support
- **Meta Tag Generation** from front matter
- **Responsive Images** with proper alt text handling
- **Semantic HTML5** output across all themes

## 🚦 **Migration & Compatibility**

- **Yellow CMS Migration Path** with compatible front matter format
- **Backward Compatible** URL structure where possible
- **Content Preservation** during upgrades
- **Theme Migration** support for custom themes

## 📋 **System Requirements**

- PHP 8.4+ with `intl` and `mbstring` extensions
- Apache with `mod_rewrite` OR Nginx with custom config
- Write permissions for `content/` directory

---

**Download**: [Latest Release](https://github.com/dirkclemens/staticmd/releases/tag/v2.0.0)
**Documentation**: [Installation Guide](https://github.com/dirkclemens/staticmd/blob/main/README.md)
**Support**: [Issues & Discussions](https://github.com/dirkclemens/staticmd/issues)

