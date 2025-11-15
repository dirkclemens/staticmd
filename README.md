
# Welcome to StaticMD

StaticMD is a professional PHP-based CMS for Markdown content with Bootstrap frontend and comprehensive admin interface. The software is ideal for technical documentation, wikis, blogs, and knowledge bases.

---

## 🚀 Key Features
- **Markdown Parser** with shortcodes, emoji support, accordions, tag system
- **9 Themes** (Bootstrap, Solarized, Monokai, GitHub, Static-MD, AdCore)
- **CodeMirror Editor** with toolbar, drag&drop upload for images, PDF, ZIP
- **Download Tag** with automatic Bootstrap icon per file type
- **Full-text search** and tag filter
- **Unicode/Umlaut support** for German content
- **Yellow CMS compatibility**
- **Admin Dashboard** with file manager, live preview, auto-save
- **CSRF protection** and secure authentication
- **Content Security Policy** (CSP) and comprehensive security headers
- **SEO control** with robots.txt generator and meta tags
- **Search engine blocking** globally or per page
- **Breadcrumb navigation** for subdirectories
- **Folder shortcode** for horizontal folder navigation

---

## 📦 Project Structure
```
staticMD/
├── index.php
├── config.php
├── .htaccess
├── content/
│   ├── index.md
│   └── ...
├── system/
│   ├── core/
│   ├── admin/
│   └── themes/
└── public/
	├── assets/
	├── images/
	└── downloads/
```

---

## 📝 Markdown Features
- **Shortcodes**: `[pages]`, `[tags]`, `[folder]`, `[accordion]`, `[download ...]`, `[image ...]`
- **Download Tag**: `[download file.pdf "Alt-Text"]` creates a link with matching icon
- **Accordion**: `[accordionstart id "Title"] ... [accordionstop]`
- **Tag Cloud**: `[tags /path/ limit]`
- **Folder Navigation**: `[folder /path/ limit]` for horizontal subfolder links
- **Images**: `[image image.jpg "Alt-Text" - 50%]`
- **Emoji**: `:smile:`, `:rocket:`, `:heart:` and many more
- **SEO Front Matter**: `Robots:`, `Description:`, `Canonical:` for search engine control

---

## 📚 Help & Documentation
- **Installation Guide**: [content/help/installation/installation.md](content/help/installation/installation.md)
- **Deployment Guide**: [content/help/installation/deployment.md](content/help/installation/deployment.md)
- **Security & CSP**: [content/help/security.md](content/help/security.md)
- **SEO & Search Engines**: [content/help/seo.md](content/help/seo.md)
- **Uberspace Setup**: [content/help/installation/uberspace.md](content/help/installation/uberspace.md)
- **Feature Overview**: [content/help/features.md](content/help/features.md)

---

## 💡 Tips
- Upload PDF/ZIP via drag&drop, download tag is automatically inserted
- Customize navigation and theme in admin dashboard
- Create backups regularly
- Issues? See [content/help/installation/installation.md](content/help/installation/installation.md) and [content/help/installation/deployment.md](content/help/installation/deployment.md)

---

## 🔗 Links
- **Demo**: https://staticMD.adcore.de/ (login: admin/admin123)
- **Project Page**: https://github.com/dirkclemens/staticMD
- **Uberspace Docs**: https://manual.uberspace.de/
