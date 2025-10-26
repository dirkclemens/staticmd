# Bilder-Verzeichnis

Dieses Verzeichnis enthält alle Bilder für die Website.

## Struktur
- `uploads/` - Neue Uploads über das Admin-Interface
- `migration/` - Migrierte Bilder aus Yellow CMS
- `thumbs/` - Automatisch generierte Thumbnails (falls implementiert)

## Yellow CMS Migration

Yellow CMS Bilder können hier abgelegt und dann in Markdown-Dateien referenziert werden.

### Yellow Format:
```
[image dateiname.jpg - - 50%]
```

### Neues Format:
```markdown
![Alt-Text](/public/images/migration/dateiname.jpg)
```

Oder mit Größenangabe:
```html
<img src="/public/images/migration/dateiname.jpg" alt="Alt-Text" style="width: 50%;">
```