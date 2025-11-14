# StaticMD - Feature-Übersicht

## Hauptfeatures
- 9 Frontend-Themes (Bootstrap, Solarized, Monokai, GitHub, Static-MD, AdCore)
- 5 Editor-Themes (CodeMirror)
- Live Theme-Wechsel
- Responsive Design
- Vollständiges Admin-Dashboard
- Professioneller Editor mit Toolbar und Drag&Drop
- Delete-Funktion mit Bestätigung
- Return-URL Navigation
- Settings-System
- Auto-Save
- Privacy Controls
- Unicode/Umlaut-Support
- Yellow CMS Kompatibilität
- Volltext-Suche
- Shortcodes: `[pages]`, `[tags]`, `[folder]`, `[accordion]`, `[ download datei.pdf]`, `[ image name.png]`
- Tag-System
- Download-Tag mit Bootstrap-Icon
- Drag&Drop-Upload für PDF/ZIP
- Clean URLs
- CSRF-Schutz
- Content-Security-Policy (CSP)
- Umfassende Security Headers
- Path-Traversal-Schutz
- XSS-Schutz
- Session-Security
- Open-Redirect-Schutz
- SEO & Suchmaschinen-Kontrolle
- Dynamische robots.txt Generierung
- Per-Page Robots Meta-Tags
- Breadcrumb-Navigation
- Unterverzeichnis-Support

## Projektstruktur
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

## Markdown-Features
```
- [ download datei.pdf "Alt-Text"] erzeugt einen Download-Link mit passendem Icon   
- [ image bild.jpg "Alt-Text" - 50%] für Bilder   
- [ pages /pfad/ limit] für Übersichten   
- [ tags /pfad/ limit] für Tag-Clouds   
- [ folder /pfad/ limit] für horizontale Unterordner-Navigation
- [ accordionstart id "Titel"]...[accordionstop] für Accordions   
```

## SEO Front Matter
```markdown
---
Title: Seitentitel
Author: Autor
Tag: seo, robots
Description: Meta-Description für Suchmaschinen
Robots: noindex,nofollow
Canonical: https://your-domain.com/canonical-url
---
```

## Hinweise
- PDF/ZIP werden nach `/public/downloads/` hochgeladen
- Download-Tag wird automatisch eingefügt
- Parser zeigt je Dateityp das passende Bootstrap-Icon
