
# Willkommen bei StaticMD

StaticMD ist ein professionelles, PHP-basiertes CMS für Markdown-Inhalte mit Bootstrap-Frontend und umfangreichem Admin-Interface. Die Software eignet sich ideal für technische Dokus, Wikis, Blogs und Knowledge Bases.

---

## 🚀 Hauptfunktionen
- **Markdown-Parser** mit Shortcodes, Emoji-Support, Accordions, Tag-System
- **9 Themes** (Bootstrap, Solarized, Monokai, GitHub, Static-MD, AdCore)
- **CodeMirror-Editor** mit Toolbar, Drag&Drop-Upload für Bilder, PDF, ZIP
- **Download-Tag** mit automatischem Bootstrap-Icon je Dateityp
- **Volltextsuche** und Tag-Filter
- **Unicode/Umlaut-Support** für deutsche Inhalte
- **Yellow CMS Kompatibilität**
- **Admin-Dashboard** mit Datei-Manager, Live-Preview, Auto-Save
- **CSRF-Schutz** und sichere Authentifizierung
- **Content-Security-Policy** (CSP) und umfassende Security Headers
- **SEO-Kontrolle** mit robots.txt Generator und Meta-Tags
- **Suchmaschinen-Blockierung** global oder pro Seite
- **Breadcrumb-Navigation** für Unterverzeichnisse
- **Folder-Shortcode** für horizontale Ordner-Navigation

---

## 📦 Projektstruktur
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

## 📝 Markdown-Features
- **Shortcodes**: `[pages]`, `[tags]`, `[folder]`, `[accordion]`, `[download ...]`, `[image ...]`
- **Download-Tag**: `[download datei.pdf "Alt-Text"]` erzeugt einen Link mit passendem Icon
- **Accordion**: `[accordionstart id "Titel"] ... [accordionstop]`
- **Tag-Cloud**: `[tags /pfad/ limit]`
- **Folder-Navigation**: `[folder /pfad/ limit]` für horizontale Unterordner-Links
- **Bilder**: `[image bild.jpg "Alt-Text" - 50%]`
- **Emoji**: `:smile:`, `:rocket:`, `:heart:` u.v.m.
- **SEO Front Matter**: `Robots:`, `Description:`, `Canonical:` für Suchmaschinen-Kontrolle

---

## 📚 Hilfe & Dokumentation
- **Installationsanleitung**: [content/help/installation/installation.md](content/help/installation/installation.md)
- **Deployment-Guide**: [content/help/installation/deployment.md](content/help/installation/deployment.md)
- **Security & CSP**: [content/help/security.md](content/help/security.md)
- **SEO & Suchmaschinen**: [content/help/seo.md](content/help/seo.md)
- **Uberspace-Setup**: [content/help/installation/uberspace.md](content/help/installation/uberspace.md)
- **Feature-Übersicht**: [content/help/features.md](content/help/features.md)

---

## 💡 Tipps
- PDF/ZIP per Drag&Drop hochladen, Download-Tag wird automatisch eingefügt
- Navigation und Theme im Admin-Dashboard anpassen
- Backup regelmäßig erstellen
- Fehler? Siehe [content/help/installation/installation.md](content/help/installation/installation.md) und [content/help/installation/deployment.md](content/help/installation/deployment.md)

---

## 🔗 Links
- **Demo**: https://staticMD.adcore.de/ (login: admin/admin123)
- **Projektseite**: https://github.com/dirkclemens/staticMD
- **Uberspace Doku**: https://manual.uberspace.de/
