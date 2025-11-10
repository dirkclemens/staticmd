
# Willkommen bei StaticMD

StaticMD ist ein professionelles, PHP-basiertes CMS für Markdown-Inhalte mit Bootstrap-Frontend und umfangreichem Admin-Interface. Die Software eignet sich ideal für technische Dokus, Wikis, Blogs und Knowledge Bases.

---

## 🚀 Hauptfunktionen
- **Markdown-Parser** mit Shortcodes, Emoji-Support, Accordions, Tag-System
- **7 Themes** (Bootstrap, Solarized, Monokai, GitHub, jeweils Light/Dark)
- **CodeMirror-Editor** mit Toolbar, Drag&Drop-Upload für Bilder, PDF, ZIP
- **Download-Tag** mit automatischem Bootstrap-Icon je Dateityp
- **Volltextsuche** und Tag-Filter
- **Unicode/Umlaut-Support** für deutsche Inhalte
- **Yellow CMS Kompatibilität**
- **Admin-Dashboard** mit Datei-Manager, Live-Preview, Auto-Save
- **CSRF-Schutz** und sichere Authentifizierung

---

## 📦 Projektstruktur
```
staticMD/
├── index.php
├── config.php
├── .htaccess
├── upload.sh
├── content/
│   ├── index.md
│   ├── tech/
│   └── ...
├── system/
│   ├── core/
│   ├── admin/
│   └── themes/
└── public/
	├── images/
	└── downloads/
```

---

## 📝 Markdown-Features
- **Shortcodes**: `[pages]`, `[tags]`, `[accordion]`, `[download ...]`, `[image ...]`
- **Download-Tag**: `[download datei.pdf "Alt-Text"]` erzeugt einen Link mit passendem Icon
- **Accordion**: `[accordionstart id "Titel"] ... [accordionstop]`
- **Tag-Cloud**: `[tags /pfad/ limit]`
- **Bilder**: `[image bild.jpg "Alt-Text" - 50%]`
- **Emoji**: `:smile:`, `:rocket:`, `:heart:` u.v.m.

---

## 📚 Hilfe & Dokumentation
- **Installationsanleitung**: [installation.md](installation.md)
- **Deployment-Guide**: [deployment.md](deployment.md)
- **Uberspace-Setup**: [uberspace.md](uberspace.md)
- **Feature-Übersicht**: [features.md](features.md)

---

## 💡 Tipps
- PDF/ZIP per Drag&Drop hochladen, Download-Tag wird automatisch eingefügt
- Navigation und Theme im Admin-Dashboard anpassen
- Backup regelmäßig erstellen
- Fehler? Siehe [installation.md](installation.md) und [deployment.md](deployment.md)

---

## 🔗 Links
- **Demo**: https://staticMD.adcore.de/ (login: admin/admin123)
- **Projektseite**: https://github.com/dirkclemens/staticMD
- **Uberspace Doku**: https://manual.uberspace.de/
