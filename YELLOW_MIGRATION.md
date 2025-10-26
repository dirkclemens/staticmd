# Yellow CMS zu StaticMD Migration 🔄

## Bilder migrieren

### 1. Bilder kopieren

Kopieren Sie alle Bilder aus Ihrem Yellow CMS in den Ordner:
```
public/images/migration/
```

### 2. Yellow Syntax wird automatisch konvertiert

**Yellow CMS Format:**
```
[image e74782e6a7067.jpg - - 50%]
```

**Wird automatisch zu:**
```html
<img src="/public/images/migration/e74782e6a7067.jpg" alt="E74782e6a7067" style="width: 50%;" class="img-fluid">
```

### 3. Unterstützte Formate

#### Mit Größenangabe:
- `[image bild.jpg - - 50%]` → 50% Breite
- `[image bild.jpg - - 300]` → 300px Breite

#### Ohne Größenangabe:
- `[image bild.jpg]` → Originalgröße (responsive)

## Yellow CMS Header-Unterstützung

Das System unterstützt automatisch Yellow CMS Header:

**Yellow Format:**
```yaml
---
Title: Außentreppe
TitleSlug: Außentreppe  
Layout: wiki
Tag: Treppe, Außenbereich
Author: Max Mustermann, Anna Schmidt
---
```

**Wird automatisch erkannt und im Editor angezeigt!**

- `Title` → Seitentitel
- `TitleSlug` → Titel für URL/Dateiname  
- `Layout` → Seiten-Layout (wiki, blog, page)
- `Tag` → Tags/Kategorien
- `Author` → Autor(en), kommagetrennt

## Vollständige Migration

### Schritt 1: Bilder vorbereiten
```bash
# Alle Yellow-Bilder in migration-Ordner kopieren:
cp /pfad/zu/yellow/media/* /pfad/zu/staticMD/public/images/migration/
```

### Schritt 2: Markdown-Dateien migrieren
1. Kopieren Sie Yellow `.md` Dateien nach `content/`
2. Yellow Header-Format wird automatisch erkannt
3. Yellow Bild-Syntax funktioniert automatisch
4. Testen Sie die Seiten im Admin-Interface

### Schritt 3: Berechtigungen setzen
```bash
# Lokale Entwicklung:
chmod -R 755 public/images/

# Auf dem Server:
chmod -R 755 public/images/
```

### Schritt 4: .htaccess erweitern (falls nötig)

Die aktuelle `.htaccess` sollte bereits korrekt konfiguriert sein:
```apache
# Statische Dateien direkt ausliefern
RewriteCond %{REQUEST_FILENAME} -f
RewriteRule ^public/(.*)$ public/$1 [L]
```

## Erweiterte Optionen

### Neue Markdown-Syntax nutzen (optional)

Für neue Inhalte können Sie auch Standard-Markdown verwenden:
```markdown
![Beschreibung](/public/images/uploads/neues-bild.jpg)
```

### Responsive Bilder

Alle migrierten Bilder erhalten automatisch die Klasse `img-fluid` (Bootstrap) für responsive Darstellung.

### Bild-Upload im Admin

Zukünftig können Sie Bilder direkt über das Admin-Interface hochladen (wenn implementiert).

## Troubleshooting

### Bilder werden nicht angezeigt?
1. Prüfen Sie den Pfad: `/public/images/migration/dateiname.jpg`
2. Prüfen Sie die Berechtigungen: `chmod 644 dateiname.jpg`
3. Prüfen Sie die .htaccess-Regel für `/public/`

### Yellow-Syntax wird nicht erkannt?
- Syntax: `[image dateiname.jpg - - 50%]` (Leerzeichen beachten!)
- Unterstützte Größen: `50%`, `300`, `100px`

---
**Migration erfolgreich abgeschlossen! 🎉**