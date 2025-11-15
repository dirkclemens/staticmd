# StaticMD - Feature Overview

## Main Features
- 9 Frontend Themes (Bootstrap, Solarized, Monokai, GitHub, Static-MD, AdCore)
- 5 Editor Themes (CodeMirror)
- Live Theme Switching
- Responsive Design
- Complete Admin Dashboard
- Professional Editor with Toolbar and Drag&Drop
- Delete Function with Confirmation
- Return-URL Navigation
- Settings System
- Auto-Save
- Privacy Controls
- Unicode/Umlaut Support
- Yellow CMS Compatibility
- Full-text Search
- Shortcodes: `[pages]`, `[tags]`, `[folder]`, `[accordion]`, `[ download file.pdf]`, `[ image name.png]`
- Tag System
- Download Tag with Bootstrap Icon
- Drag&Drop Upload for PDF/ZIP
- Clean URLs
- CSRF Protection
- Content Security Policy (CSP)
- Comprehensive Security Headers
- Path Traversal Protection
- XSS Protection
- Session Security
- Open Redirect Protection
- SEO & Search Engine Control
- Dynamic robots.txt Generation
- Per-Page Robots Meta Tags
- Breadcrumb Navigation
- Subdirectory Support

## Project Structure
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

## Markdown Features
```
- [ download file.pdf "Alt-Text"] creates a download link with matching icon   
- [ image image.jpg "Alt-Text" - 50%] for images   
- [ pages /path/ limit] for overviews   
- [ tags /path/ limit] for tag clouds   
- [ folder /path/ limit] for horizontal subfolder navigation
- [ accordionstart id "Title"]...[accordionstop] for accordions   
```

## SEO Front Matter
```markdown
---
Title: Page Title
Author: Author
Tag: seo, robots
Description: Meta description for search engines
Robots: noindex,nofollow
Canonical: https://your-domain.com/canonical-url
---
```

## Notes
- PDF/ZIP files are uploaded to `/public/downloads/`
- Download tag is automatically inserted
- Parser shows the appropriate Bootstrap icon for each file type
