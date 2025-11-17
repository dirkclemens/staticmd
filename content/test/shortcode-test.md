---
Title: Shortcode Test
Layout: default
---

# Shortcode Test-Seite

## Normale Shortcodes (sollten funktionieren):

Hier ist eine Galerie:
[gallery demo]

Hier sind Seiten:
[pages /help/ 5]

## Shortcodes in Code-Blocks (sollten NICHT verarbeitet werden):

Inline Code mit Shortcode: `[gallery demo]` sollte als Text bleiben.

```markdown
# Beispiel-Markdown mit Shortcodes
[gallery meine-bilder]
[pages /blog/ 10]
```

```bash
# Shell-Befehle
echo "[gallery test]"
```

## Gemischter Inhalt:

Normal: [pages /tech/ 3]

In Code: `[pages /tech/ 3]`

```
Code-Block: [gallery test]
```

Wieder normal: [gallery demo]

## Ende

Das war der Test!