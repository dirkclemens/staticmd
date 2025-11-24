# Welcome to StaticMD

StaticMD is a professional PHP-based CMS for Markdown content with Bootstrap frontend and comprehensive admin interface. The software features a complete **gallery system**, **9 beautiful themes**, **advanced security**, and **45+ implemented features**.

**Perfect for**: Technical documentation, wikis, blogs, knowledge bases, image galleries, and content-driven websites.

**New**: Gallery layouts, enhanced shortcode system, improved security, and comprehensive backup functionality.

---

## 🚀 Key Features
- ✅ **Enhanced Markdown Parser** with shortcodes, ~150 emojis, LaTeX math, accordions
- ✅ **9 Frontend Themes** (Bootstrap, Solarized Light/Dark, Monokai Light/Dark, GitHub Light/Dark, Static-MD, AdCore)
- ✅ **Gallery System** with automatic image loading, lightbox, and tag filtering
- ✅ **CodeMirror Editor** with 5 themes, enhanced toolbar, drag&drop upload
- ✅ **Smart Upload System** for PDF/ZIP with automatic download tag insertion
- ✅ **Image Upload** with drag&drop and automatic markdown insertion
- ✅ **Full-text Search** with weighted relevance and tag filtering
- ✅ **Unicode/Umlaut Support** with NFC/NFD normalization for German content
- ✅ **Yellow CMS Compatibility** for seamless migration
- ✅ **Professional Admin Dashboard** with statistics, file manager, backup system
- ✅ **Advanced Security** with CSRF protection, CSP headers, session management
- ✅ **SEO & Search Engine Control** with dynamic robots.txt and per-page settings
- ✅ **Navigation System** with configurable ordering and breadcrumb support
- ✅ **Shortcode System** with code-block protection and pre-processing

---

## 📝 Markdown Features

### ✅ Shortcodes (All Implemented)
- **Content Listing**: `[pages /path/ limit]`, `[tags /path/ limit]`, `[folder /path/ limit]`
- **Gallery System**: `[gallery folder-name]` - NEW! Automatic image galleries with lightbox
- **Downloads**: `[download file.pdf "Alt-Text"]` with Bootstrap icons (PDF 📄, ZIP 📦)
- **Images**: `[image image.jpg "Alt-Text" - 50%]` Yellow CMS syntax
- **Interactive**: `[accordionstart id "Title"] ... [accordionstop]` Bootstrap 5 accordions

### ✅ Enhanced Markdown
- **Headers with IDs**: `# Title {#custom-id}`
- **150+ Emojis**: `:smile:` → 😄, `:rocket:` → 🚀, `:heart:` → ❤️
- **LaTeX Math**: `$E=mc^2$` (inline) and `$$formula$$` (block)
- **Auto-links**: URLs become clickable automatically
- **Code Protection**: Shortcodes in `` `code blocks` `` remain as text

### ✅ Front Matter Support
- **SEO Control**: `Robots:`, `Description:`, `Canonical:`
- **Layout Override**: `Layout: gallery` for special layouts
- **Privacy**: `Visibility: private` for admin-only content
- **Organization**: `Tag:`, `Author:`, `Date:` for content management

---

## 📚 Help & Documentation
- **📋 Feature Overview**: [features.md](features.md) - Complete feature list with status
- **⚙️ Settings System**: [settings.md](settings.md) - All configuration options
- **🖼️ Gallery Layout**: [gallery-layout.md](gallery-layout.md) - NEW! Gallery system guide
- **🔧 Installation Guide**: [installation/installation.md](installation/installation.md)
- **🚀 Deployment Guide**: [installation/deployment.md](installation/deployment.md)
- **🛡️ Security & CSP**: [security.md](security.md)
- **🔍 SEO & Search Engines**: [seo.md](seo.md)
- **🌐 Uberspace Setup**: [installation/uberspace.md](installation/uberspace.md)
- **🎯 Shortcodes Guide**: [shortcodes.md](shortcodes.md)
- **🎨 Themes Guide**: [themes.md](themes.md)

---

## 💡 Tips & Best Practices

### ✅ File Management
- **Upload Files**: Drag&drop PDF/ZIP in editor → automatic `[download]` tag insertion
- **Upload Images**: Drag&drop images → automatic markdown insertion with correct paths
- **Gallery Creation**: Use `[gallery folder-name]` for automatic image galleries
- **Backup System**: Use Admin → Settings → Create Backup for complete site backup

### ✅ Content Organization
- **Navigation Ordering**: Configure priority in Admin → Settings → Navigation
- **Theme Selection**: Choose from 9 themes in Admin → Settings → Frontend Theme
- **Private Content**: Use `Visibility: private` in front matter for admin-only pages
- **SEO Control**: Use `Robots: noindex,nofollow` to hide pages from search engines

### ✅ Advanced Features
- **Gallery Tags**: Add tags to images for filtering: `![Description tags](/path/image.jpg)`
- **LaTeX Math**: Use `$formula$` for inline or `$$formula$$` for block equations
- **Custom Layouts**: Use `Layout: gallery` for image-focused pages
- **Unicode Support**: Full German umlaut support with automatic normalization

---

## 🔗 Links & Resources
- **Live Demo**: https://flat.adcore.de/ - Experience all features live
- **Gallery Demo**: https://flat.adcore.de/galerie-beispiel - NEW! Gallery system showcase
- **Admin Interface**: https://flat.adcore.de/admin - Complete admin dashboard
- **Project Repository**: https://github.com/dirkclemens/staticMD
- **robots.txt**: https://flat.adcore.de/robots.txt - Dynamic SEO control

## 🆕 Recent Updates (November 2024)
- **Gallery System**: Complete image gallery solution with lightbox
- **Enhanced Shortcodes**: Code-block protection and improved processing
- **Security Improvements**: Enhanced CSP and asset security
- **Theme Expansion**: All 9 themes now support gallery layouts
- **Backup System**: One-click complete site backup functionality
