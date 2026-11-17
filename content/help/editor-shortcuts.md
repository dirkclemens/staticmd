---
Title: Editor Tastenkombinationen
TitleSlug: Tastaturkürzel im Markdown-Editor
Author: StaticMD
Layout: wiki
Tag: hilfe, editor, shortcuts
---

# Editor Tastenkombinationen

Der Markdown-Editor unterstützt zahlreiche Tastenkombinationen für effizientes Arbeiten.

## 📝 Markdown-Formatierung

| Tastenkombination | Aktion | Beispiel |
|-------------------|--------|----------|
| `Ctrl-B` / `Cmd-B` | **Fett** | `**Text**` |
| `Ctrl-I` / `Cmd-I` | *Kursiv* | `*Text*` |
| `Ctrl-K` / `Cmd-K` | Inline Code | `` `Code` `` |
| `Ctrl-L` / `Cmd-L` | Link einfügen | `[Text](URL)` |

## 🔍 Suchen & Navigieren
  
| Tastenkombination | Aktion |
|-------------------|--------|
| `Ctrl-F` / `Cmd-F` | Suchen öffnen |
| `F3` / `Ctrl-G` | Weitersuchen (vorwärts) |
| `Shift-F3` / `Ctrl-Shift-G` | Weitersuchen (rückwärts) |
| `Ctrl-H` / `Cmd-Alt-F` | Suchen & Ersetzen |

## 💾 Speichern & Ansicht

| Tastenkombination | Aktion |
|-------------------|--------|
| `Ctrl-S` / `Cmd-S` | Seite speichern |
| `F11` | Vollbild umschalten |
| `Esc` | Vollbild verlassen |

## ✏️ Text bearbeiten

| Tastenkombination | Aktion |
|-------------------|--------|
| `Ctrl-D` | Aktuelle Zeile löschen |
| `Alt-↑` / `Alt-↓` | Zeile(n) nach oben / unten verschieben – auch mehrere markierte Zeilen |
| `Shift-Alt-↑` / `Shift-Alt-↓` | Zeile(n) nach oben / unten duplizieren |
| `Ctrl-Shift-D` | Zeile(n) duplizieren (nach unten) |
| `Ctrl-/` / `Cmd-/` | Kommentar umschalten |
| `Ctrl-Space` | Autovervollständigung manuell öffnen (schon ab 1 Buchstaben) |

Auf dem Mac entspricht `Alt` der `⌥ Option`-Taste.

## 💬 Autovervollständigung

Ab dem **4. Buchstaben** eines Wortes erscheint automatisch ein Popup mit Wortvorschlägen.

- Vorschläge stammen aus **allen Seiten** (`content/`) und dem **aktuellen Text** – auch aus noch nicht gespeicherten Änderungen.
- Sortierung nach **Häufigkeit** – häufig verwendete Wörter stehen oben.
- Schreibvarianten (`Wolken` / `wolken`) werden zusammengefasst, die häufigste Schreibweise wird vorgeschlagen.
- Beginnt das getippte Wort mit einem Großbuchstaben, werden die Vorschläge ebenfalls großgeschrieben.

| Taste | Aktion |
|-------|--------|
| `↑` / `↓` | Vorschlag auswählen |
| `Enter` / `Tab` | Vorschlag übernehmen |
| `Esc` | Popup schließen |

## 🎯 Editor-Features

### Toolbar-Buttons

Zusätzlich zu den Tastenkombinationen bietet die Toolbar Buttons für:

- Überschriften (H1-H4)
- Listen (ungeordnet, geordnet, Checklisten)
- Links & Bilder
- Code-Blöcke
- Tabellen
- Horizontale Linie
- Sprungmarken
- Accordions
- Emojis :smile:
- Text-Werkzeuge (Menü mit dem Schrift-Symbol)

### Text-Werkzeuge

Das Menü wirkt auf den **markierten Text**; mehrere Markierungen (Mehrfachcursor) werden einzeln bearbeitet. Alle Änderungen lassen sich mit `Ctrl-Z` / `Cmd-Z` rückgängig machen.

| Funktion | Beispiel | Hinweis |
|----------|----------|---------|
| GROSSBUCHSTABEN | `straße` → `STRASSE` | ohne Markierung: Wort am Cursor |
| kleinbuchstaben | `XML Datei` → `xml datei` | ohne Markierung: Wort am Cursor |
| Satzanfang groß | `DAS IST GUT. noch was` → `Das ist gut. Noch was` | setzt den Rest klein (auch Substantive) |
| Titel Case | `über den wolken` → `Über Den Wolken` | |
| camelCase | `Hallo schöne Welt` → `halloSchöneWelt` | zeilenweise, Einrückung bleibt |
| snake_case | `myVariableName` → `my_variable_name` | zeilenweise, Einrückung bleibt |
| kebab-case | `my_variable Name` → `my-variable-name` | zeilenweise, Einrückung bleibt |
| Zeilen sortieren A–Z | | deutsche Sortierung, `item2` vor `item10` |
| Doppelte Zeilen entfernen | | erstes Vorkommen bleibt, Leerzeilen bleiben |
| Leerzeilen entfernen | | auch Zeilen nur mit Leerzeichen/Tabs |
| Leerzeichen bereinigen | `a   b\t c  ` → `a b c` | Einrückung bleibt; entfernt auch zwei Leerzeichen am Zeilenende (Markdown-Zeilenumbruch) |

Zeilen-Funktionen wirken immer auf **ganze Zeilen** – eine teilweise markierte Zeile wird vollständig einbezogen.

### Drag & Drop

- **Bilder**: Ziehe Bilddateien direkt in den Editor → Yellow CMS Image-Syntax wird automatisch eingefügt
- **PDFs/ZIPs**: Ziehe Download-Dateien in den Editor → Download-Shortcode wird automatisch erstellt

## 🖥️ Ansichtsmodi

| Button | Beschreibung |
|--------|--------------|
| **Editor** | Nur Code-Editor anzeigen |
| **Preview** | Live-Vorschau mit vollständigem Rendering |
| **Split** | Editor und Vorschau nebeneinander |

Die Vorschau zeigt das Ergebnis 1:1 wie im Frontend, inklusive:
- Shortcodes (`[pages]`, `[tags]`, `[accordionstart]`)
- Emojis (`:smile:` → 😄)
- Yellow CMS Bildsyntax
- Alle Markdown-Features

## 💡 Tipps

- Die Preview aktualisiert sich automatisch während du tippst (500ms Verzögerung)
- `Ctrl-S` / `Cmd-S` funktioniert von überall im Editor
- `F3` ist der schnellste Weg zum nächsten Suchergebnis
- Nutze `Alt-Up/Down` um Code-Zeilen schnell umzusortieren

---

**Hinweis**: `Ctrl` bezieht sich auf Windows/Linux, `Cmd` auf macOS.
