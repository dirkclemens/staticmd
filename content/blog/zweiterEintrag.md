---
Title: Markdown Tipps & Tricks für StaticMD
Author: Content Team
Date: 2025-10-12
Layout: blog
Tag: blog, tutorials, markdown, tipps, shortcuts
Description: Professionelle Markdown-Techniken für bessere Inhalte in StaticMD - von Shortcodes bis zu erweiterten Formatierungen.
Visibility: public
---

# Markdown Tipps & Tricks für StaticMD 📝

**12. Oktober 2025** - Hol das Maximum aus Deinen Markdown-Inhalten heraus!

Markdown ist das Herzstück von StaticMD. Mit diesen Profi-Tipps erstellst Du noch bessere Inhalte.

## 🎯 Shortcodes effektiv nutzen

### Seiten-Listen
```markdown
[pages /tech/ 10 columns]  # Spaltenweise Anordnung
[pages /blog/ 5 rows]      # Zeilenweise Anordnung
```

### Tag-Clouds
```markdown
[tags /blog/ 20]  # Zeigt die 20 häufigsten Tags
```

### Accordions
```markdown
[accordionstart faq1 "Häufige Fragen"]
Hier steht die Antwort auf die häufig gestellte Frage.
[accordionstop]
```

## ✨ Front Matter Best Practices

```yaml
---
Title: Aussagekräftiger Titel
Author: Dein Name
Date: 2025-10-12
Layout: blog
Tag: tag1, tag2, tag3
Description: SEO-optimierte Beschreibung (max. 160 Zeichen)
Visibility: public  # oder private
---
```

## 🎨 Erweiterte Formatierungen

### Emojis verwenden
StaticMD unterstützt über 150 Emojis:
- `:smile:` → 😄
- `:rocket:` → 🚀
- `:heart:` → ❤️

### Tabellen
| Feature | StaticMD | Andere CMS |
|---------|----------|------------|
| Markdown | ✅ | ❌ |
| Themes | 7 | 2-3 |
| Performance | ⚡ | 🐌 |

### Code-Blöcke
```php
<?php
echo "StaticMD rocks!";
?>
```

## 📱 Responsive Bilder

```markdown
[image beispiel.jpg "Bildtitel" - 100%]
[image mobile.jpg "Mobil-optimiert" - 50%]
```

## 🔗 Interne Verlinkungen

```markdown
[Link zu Unterseite](tech/arduino-projekte)
[Zurück zur Hauptseite](/)
```

## 💡 Profi-Tipp: Inhalts-Struktur

1. **H1** nur einmal pro Seite (Titel)
2. **H2** für Hauptabschnitte
3. **H3** für Unterabschnitte
4. Kurze Absätze für bessere Lesbarkeit
