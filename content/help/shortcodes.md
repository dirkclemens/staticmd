---
Title: Shortcodes Reference
Author: StaticMD Team
Tag: shortcodes, markdown, content
Layout: wiki
---

# Shortcodes Reference

Umfassende Dokumentation aller verfügbaren Shortcodes in StaticMD für erweiterte Content-Funktionalität.

---

## Übersicht

Shortcodes erweitern die Standard-Markdown-Syntax um dynamische Inhalte und erweiterte Layoutoptionen. StaticMD unterstützt sowohl eigene Shortcodes als auch Yellow CMS-kompatible Syntax.

## Content-Shortcodes

### [pages] - Seitenlisten generieren

Generiert automatische Listen von Markdown-Dateien aus einem bestimmten Verzeichnis.

#### Grundsyntax
```markdown
[pages /pfad/ limit]
[pages /pfad/ limit layout]
```

#### Parameter
- **`/pfad/`**: Pfad zum Verzeichnis (relativ zu `/content/`)
- **`limit`**: Maximale Anzahl von Dateien (Standard: 20)
- **`layout`**: `rows` (Standard) oder `columns`

#### Beispiele

**Einfache Liste**:
```markdown
[pages /blog/ 10]
```

**Spalten-Layout**:
```markdown
[pages /tech/ 15 columns]
```

**Root-Verzeichnis**:
```markdown
[pages / 5]
```

#### Ausgabe-Format (Rows)
- Chronologische Liste mit Titeln
- Klickbare Links zu den Seiten
- Automatische Titel-Extraktion aus Front Matter
- Datum der letzten Änderung

#### Ausgabe-Format (Columns)
- Bootstrap-Grid mit Cards
- Responsive 2-3 Spalten je nach Bildschirmgröße
- Kompakte Darstellung für Übersichten

#### Sortieroptionen
- **Alphabetisch**: Standard-Sortierung nach Dateiname
- **Datum**: Nach letzter Änderung (neueste zuerst)
- **Titel**: Nach extrahiertem Titel aus Front Matter

### [tags] - Tag-Clouds erstellen

Erstellt Tag-Clouds mit allen verfügbaren Tags aus einem Verzeichnis.

#### Grundsyntax
```markdown
[tags /pfad/ limit]
[tags /pfad/ limit layout]
```

#### Parameter
- **`/pfad/`**: Pfad zum Verzeichnis für Tag-Extraktion
- **`limit`**: Maximale Anzahl von Tags (Standard: 30)
- **`layout`**: `cloud` (Standard) oder `list`

#### Beispiele

**Standard Tag-Cloud**:
```markdown
[tags /blog/ 20]
```

**Tag-Liste**:
```markdown
[tags /tech/ 15 list]
```

**Alle Tags**:
```markdown
[tags / 50]
```

#### Ausgabe-Format (Cloud)
- Größen-gewichtete Tags basierend auf Häufigkeit
- Klickbare Links zu Tag-Filterseiten
- Bootstrap-Badges mit unterschiedlichen Größen
- Alphabetische oder Häufigkeits-Sortierung

#### Ausgabe-Format (List)
- Kompakte Liste mit Tag-Namen
- Verwendungsanzahl in Klammern
- Zeilen-basierte Darstellung

---

## Layout-Shortcodes

### [accordionstart] / [accordionstop] - Bootstrap Accordions

Erstellt kollabierbare Bootstrap 5 Accordion-Bereiche.

#### Syntax
```markdown
[accordionstart id "Titel"]
Inhalt des Accordion-Bereichs...
[accordionstop]
```

#### Parameter
- **`id`**: Eindeutige ID für das Accordion (nur Buchstaben/Zahlen)
- **`"Titel"`**: Sichtbarer Titel des Accordion-Headers

#### Beispiel
```markdown
[accordionstart install "Installation"]
## Schritt 1: Download
Lade die neueste Version herunter...

## Schritt 2: Konfiguration
Bearbeite die config.php...
[accordionstop]

[accordionstart config "Konfiguration"]
### Database Setup
Verbindung zur Datenbank...
[accordionstop]
```

#### Features
- **Bootstrap 5 kompatibel**: Moderne Accordion-Styles
- **Vollständiges Markdown**: Markdown-Syntax wird innerhalb verarbeitet
- **Eindeutige IDs**: Verhindert Konflikte bei mehreren Accordions
- **Responsive Design**: Mobile-optimierte Darstellung

#### Verschachtelte Accordions
```markdown
[accordionstart main "Hauptbereich"]
Einführungstext...

[accordionstart sub "Unterbereich"]
Detaillierte Informationen...
[accordionstop]

Weiterer Text...
[accordionstop]
```

---

## Yellow CMS Kompatibilität

### [image] - Responsive Bilder

Yellow CMS-kompatible Bild-Syntax für responsive Bilder.

#### Syntax
```markdown
[image dateiname.jpg Beschreibung Klasse Größe]
```

#### Parameter
- **`dateiname.jpg`**: Bild-Datei in `/public/images/`
- **`Beschreibung`**: Alt-Text (verwende `-` für leer)
- **`Klasse`**: CSS-Klasse (verwende `-` für keine)
- **`Größe`**: Prozentuale Breite (z.B. `50%`)

#### Beispiele

**Einfaches Bild**:
```markdown
[image screenshot.png - - 100%]
```

**Mit Beschreibung und Größe**:
```markdown
[image logo.svg "StaticMD Logo" center 30%]
```

**Responsive Bild**:
```markdown
[image banner.jpg "Banner Image" img-fluid 80%]
```

#### Ausgabe
```html
<img src="/public/images/screenshot.png" 
     alt="StaticMD Logo" 
     class="center img-responsive" 
     style="width: 30%;">
```

---

## Konfiguration

### Standard-Limits

In `system/settings.json` können Standard-Werte konfiguriert werden:

```json
{
    "default_pages_limit": 20,
    "default_tags_limit": 30,
    "pages_sort_order": "alphabetical",
    "tags_sort_order": "alphabetical"
}
```

### Sortier-Optionen

#### Pages Sortierung
- **`alphabetical`**: Nach Dateiname (Standard)
- **`modified`**: Nach Änderungsdatum
- **`created`**: Nach Erstellungsdatum
- **`title`**: Nach extrahiertem Titel

#### Tags Sortierung
- **`alphabetical`**: Alphabetisch (Standard)
- **`frequency`**: Nach Häufigkeit

### Performance-Einstellungen

```php
// In config.php
'shortcodes' => [
    'cache_enabled' => true,
    'cache_duration' => 3600,  // 1 Stunde
    'max_files_scan' => 1000   // Limit für große Verzeichnisse
]
```

---

## Erweiterte Verwendung

### Kombinierte Shortcodes

```markdown
# Projekt-Übersicht

## Alle Projekte
[pages /projekte/ 10 columns]

## Beliebte Tags
[tags /projekte/ 15 cloud]

## Detaillierte Anleitungen
[accordionstart details "Installation & Setup"]
### Schritt-für-Schritt Anleitung
[pages /help/installation/ 5]
[accordionstop]
```

### Conditional Content

```markdown
<!-- Nur auf der Startseite -->
[pages / 5]

<!-- Blog-Bereich -->
[pages /blog/ 10]
[tags /blog/ 20]

<!-- Technische Dokumentation -->
[accordionstart api "API Dokumentation"]
[pages /api/ 15 list]
[accordionstop]
```

### Responsive Layouts

```markdown
<!-- Mobile-optimiert -->
[pages /portfolio/ 6 columns]

<!-- Desktop-Listen -->
[pages /articles/ 20 rows]

<!-- Tag-Navigation -->
[tags / 25 cloud]
```

---

## Custom Shortcodes

### Eigene Shortcodes entwickeln

Entwickler können eigene Shortcodes in `MarkdownParser.php` hinzufügen:

```php
private function processCustomShortcodes(string $content): string
{
    // [quote autor "text"] Shortcode
    $content = preg_replace_callback(
        '/\[quote\s+([^\s]+)\s+"([^"]+)"\]/',
        function($matches) {
            $author = $matches[1];
            $text = $matches[2];
            return $this->renderQuote($author, $text);
        },
        $content
    );
    
    return $content;
}
```

### Plugin-System

```php
// Custom Plugin
class GalleryShortcode 
{
    public function process(string $content): string 
    {
        return preg_replace_callback(
            '/\[gallery\s+([^\]]+)\]/',
            [$this, 'renderGallery'],
            $content
        );
    }
    
    private function renderGallery(array $matches): string
    {
        // Gallery-HTML generieren
        return '<div class="gallery">...</div>';
    }
}
```

---

## Debugging und Troubleshooting

### Debug-Modus aktivieren

```php
// In config.php
'system' => [
    'debug' => true
]
```

### Häufige Probleme

#### Shortcode wird nicht verarbeitet
- **Ursache**: Syntax-Fehler oder fehlende Parameter
- **Lösung**: Parameter-Reihenfolge und Schreibweise prüfen

#### Pfad nicht gefunden
- **Ursache**: Falscher Pfad zu Content-Verzeichnis
- **Lösung**: Pfad relativ zu `/content/` angeben

#### Bilder werden nicht angezeigt
- **Ursache**: Bild-Datei nicht in `/public/images/`
- **Lösung**: Datei-Pfad und Berechtigungen prüfen

### Shortcode-Logs

```php
// Debug-Output für Shortcodes
error_log("Processing shortcode: " . $shortcode);
error_log("Parameters: " . print_r($params, true));
```

---

## Performance-Optimierung

### Caching-Strategien

- **Content-Cache**: Verarbeitete Shortcodes werden gecacht
- **File-System-Cache**: Verzeichnis-Scans werden zwischengespeichert
- **Template-Cache**: Generierte HTML-Snippets werden gecacht

### Best Practices

1. **Limits setzen**: Große Verzeichnisse mit Limits abfragen
2. **Layout wählen**: `columns` für viele Einträge, `rows` für Details
3. **Caching nutzen**: Cache-Einstellungen aktivieren
4. **Pfade optimieren**: Spezifische Pfade statt Root-Verzeichnis

### Monitoring

```php
// Performance-Messung
$start = microtime(true);
$result = $this->processShortcode($content);
$duration = microtime(true) - $start;

if ($duration > 0.1) {
    error_log("Slow shortcode processing: {$duration}s");
}
```

---

## Migration von anderen CMS

### Von Yellow CMS

StaticMD ist vollständig kompatibel mit Yellow CMS Shortcodes:

```markdown
<!-- Yellow CMS -->
[image photo.jpg "Description" left 50%]

<!-- StaticMD (identisch) -->
[image photo.jpg "Description" left 50%]
```

### Von Kirby CMS

```markdown
<!-- Kirby -->
(image: photo.jpg alt: Description class: left width: 50%)

<!-- StaticMD Äquivalent -->
[image photo.jpg "Description" left 50%]
```

### Von Jekyll

```markdown
<!-- Jekyll -->
{% for post in site.posts limit:10 %}
  {{ post.title }}
{% endfor %}

<!-- StaticMD Äquivalent -->
[pages /blog/ 10]
```

---

*StaticMD Shortcodes - Erweiterte Content-Funktionalität für Markdown*