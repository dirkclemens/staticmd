# 🔧 Shortcodes Dokumentation

StaticMD bietet ein mächtiges Shortcode-System für dynamische Inhalte.

## Übersicht

| Shortcode | Funktion | Beispiel |
|-----------|----------|----------|
| `[pages]` | Seitenliste generieren | `[pages /tech/ 1000 rows]` |
| `[tags]` | Tag-Cloud erstellen | `[tags /tech/ 1000]` |
| `[folder]` | Unterordner-Navigation | `[folder /tech/ 10]` |
| `[accordion]` | Bootstrap-Accordion | `[accordionstart id "titel"]...[accordionstop]` |

## Pages-Shortcode

### Syntax

```markdown
[pages /ordnerpfad/ limit layout]
```

### Parameter

- **`/ordnerpfad/`** (optional): Relativer Pfad zum Ordner. Ohne Angabe = aktueller Ordner
- **`limit`** (optional): Maximale Anzahl Seiten. Standard: 1000
- **`layout`** (optional): Sortierung/Layout. Optionen: `rows`, `columns`. Standard: `columns`

### Layout-Optionen

#### Spaltenweise Sortierung (Standard)
```markdown
[pages /tech/ 1000 columns]
[pages /tech/ 1000]  <!-- gleicher Effekt -->
```
- Sortiert spaltenweise: A, D, G | B, E, H | C, F, I
- Items werden vertikal auf 4 Bootstrap-Spalten verteilt
- Platzsparend für viele Einträge

#### Zeilenweise Sortierung
```markdown
[pages /tech/ 1000 rows]
```
- Sortiert zeilenweise: A, B, C | D, E, F | G, H, I  
- Items werden horizontal auf 4 Bootstrap-Spalten verteilt
- **Visuelle Darstellung bleibt in 4 Spalten**
- Bessere Lesbarkeit bei wenigen Einträgen

### Beispiele

```markdown
# Alle Seiten aus dem tech-Ordner
[pages /tech/]

# Maximal 20 Seiten, zeilenweise sortiert
[pages /blog/ 20 rows]

# Seiten aus aktuellem Ordner, spaltenweise
[pages 50 columns]
```

### Ausgabe

Das Pages-Shortcode generiert:
- Responsive 4-Spalten Bootstrap-Grid
- Links zu allen gefundenen Seiten
- Dateisymbol (Bootstrap Icon) vor jedem Link
- Case-insensitive alphabetische Sortierung
- Automatische URL-Kodierung für Umlaute

## Tags-Shortcode

### Syntax

```markdown
[tags /ordnerpfad/ limit]
```

### Parameter

- **`/ordnerpfad/`** (optional): Relativer Pfad zum Ordner. Ohne Angabe = aktueller Ordner
- **`limit`** (optional): Maximale Anzahl Tags. Standard: 1000

### Beispiele

```markdown
# Alle Tags aus dem tech-Ordner
[tags /tech/]

# Maximal 50 Tags aus dem blog-Ordner
[tags /blog/ 50]

# Tags aus aktuellem Ordner
[tags]
```

### Ausgabe

Das Tags-Shortcode generiert:
- Tag-Cloud mit Bootstrap-Badges
- Größenbasierte Darstellung (häufige Tags = größer)
- Case-insensitive alphabetische Sortierung
- Klickbare Links zu Tag-Seiten (`/tag/tagname`)
- Anzahl-Anzeige bei mehrfachen Tags

### Tag-Häufigkeit

Tags werden nach Häufigkeit visualisiert:
- **1-2 Vorkommen**: Kleine graue Badges
- **3-4 Vorkommen**: Mittlere blaue Badges  
- **5+ Vorkommen**: Große grüne Badges

## Folder-Shortcode

### Syntax

```markdown
[folder /ordnerpfad/ limit]
```

### Parameter

- **`/ordnerpfad/`** (optional): Relativer Pfad zum Ordner. Ohne Angabe = aktueller Ordner
- **`limit`** (optional): Maximale Anzahl Unterordner. Standard: 1000

### Beispiele

```markdown
# Alle Unterordner aus dem tech-Ordner
[folder /tech/]

# Maximal 5 Unterordner aus dem blog-Ordner
[folder /blog/ 5]

# Unterordner aus aktuellem Ordner
[folder]

# Unterordner der Root-Ebene
[folder /]
```

### Ausgabe

Das Folder-Shortcode generiert:
- Horizontale Button-Navigation mit Bootstrap-Design
- Nur **direkte** Unterordner (nicht rekursiv)
- Prüfung auf Markdown-Inhalte (leere Ordner werden ignoriert)
- Automatische Titel-Extraktion aus `index.md`
- Fallback auf formatierten Ordnernamen
- Responsive Flexbox-Layout mit Zeilenumbruch
- Bootstrap Icons (Ordner-Symbol)

### Titel-Extraktion

Die Ordner-Titel werden in folgender Reihenfolge ermittelt:
1. **Front Matter**: `Title:` aus `index.md`
2. **H1-Überschrift**: Erste `# Überschrift` aus `index.md`
3. **Ordnername**: Formatiert mit `ucwords()` und `-` → Leerzeichen

### HTML-Ausgabe

```html
<nav class="folder-navigation mb-3">
  <div class="d-flex flex-wrap gap-2">
    <a href="/tech/hackintosh" class="btn btn-outline-primary btn-sm">
      <i class="bi bi-folder"></i> Hackintosh Projekte
    </a>
    <a href="/tech/hardware" class="btn btn-outline-primary btn-sm">
      <i class="bi bi-folder"></i> Hardware Tests
    </a>
  </div>
</nav>
```

## Accordion-Shortcode

### Syntax

```markdown
[accordionstart id "titel"]
Inhalt des Accordions...
[accordionstop]
```

### Parameter

- **`id`** (erforderlich): Eindeutige ID für das Accordion
- **`titel`** (erforderlich): Titel des Accordion-Abschnitts (in Anführungszeichen)

### Beispiele

```markdown
[accordionstart faq1 "Wie installiere ich StaticMD?"]
1. Repository klonen
2. Konfiguration anpassen
3. Webserver einrichten
[accordionstop]

[accordionstart tech "Technische Details"]
StaticMD basiert auf PHP 8.3+ und Bootstrap 5.
Es unterstützt Markdown, Shortcodes und Themes.
[accordionstop]
```

### Ausgabe

Das Accordion-Shortcode generiert:
- Bootstrap 5 Accordion-Komponenten
- Klappbare Bereiche mit Animations-Effekten
- Responsive Design
- Mehrere Accordions pro Seite möglich

## Shortcode-Verarbeitung

### Reihenfolge

1. **Markdown-Parsing** (Überschriften, Links, etc.)
2. **Shortcode-Verarbeitung** (Pages, Tags, Accordion)
3. **HTML-Ausgabe**

### Caching

- Shortcode-Ergebnisse werden dynamisch generiert
- Bei großen Ordnern kann die Verarbeitung Zeit dauern
- Caching erfolgt über PHP-interne Mechanismen

### Sicherheit

- Alle Ausgaben werden HTML-escaped
- Pfad-Traversal-Schutz verhindert `../`-Zugriffe
- URL-Encoding für sichere Links

## Fehlerbehandlung

### Leere Ergebnisse

```markdown
# Wenn keine Seiten gefunden werden:
[pages /nicht-existierend/]
```
**Ausgabe**: `<div class="alert alert-info">Keine Seiten in "nicht-existierend" gefunden.</div>`

### Ungültige Parameter

- Ungültige Ordnerpfade werden ignoriert
- Negative Limits werden auf 1000 gesetzt
- Fehlende Accordion-IDs generieren Warnungen

## Best Practices

### Performance

```markdown
# Gut: Begrenzung bei großen Ordnern
[pages /blog/ 50]
[folder /tech/ 8]

# Schlecht: Unbegrenzt bei vielen Dateien
[pages /blog/]
[folder /]
```

### SEO-Optimierung

```markdown
# Gut: Aussagekräftige Accordion-Titel
[accordionstart install "Installation & Setup"]

# Schlecht: Generische Titel
[accordionstart a1 "Info"]
```

### Responsive Design

- Pages-Shortcode nutzt automatisch Bootstrap-Grid
- Bei mobilen Geräten werden Spalten gestapelt
- Tags-Cloud passt sich der Bildschirmgröße an

## Erweiterte Verwendung

### Kombination mit Front Matter

```markdown
---
Title: Übersichtsseite
Tag: navigation, übersicht
---

# Hauptbereiche

[folder / 6]

# Alle Artikel

[pages /articles/ 100 rows]

## Beliebte Tags

[tags /articles/ 20]
```

### Verschachtelte Ordner

```markdown
# Unterordner einschließen
[pages /tech/hardware/]
[pages /tech/software/]

# Hauptordner mit allen Unterseiten
[pages /tech/]
```

### Layout-Kombinationen

```markdown
# Neueste Artikel (zeilenweise für Chronologie)
[pages /blog/ 10 rows]

# Alle Projekte (spaltenweise für Übersicht)
[pages /projects/ columns]

# Hauptkategorien-Navigation
[folder / 8]

# Unterkategorien eines Bereichs
[folder /tech/ 5]
```