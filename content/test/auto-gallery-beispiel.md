---
Title: Auto-Gallery Beispiel
Layout: gallery
Author: StaticMD
Date: 2024-11-16
Tag: gallery, auto, paris, beispiel
Description: Demonstration der automatischen Gallery-Funktion
---

# Automatische Galerie - Paris

Diese Seite demonstriert die **automatische Gallery-Erstellung** mit dem `[gallery]` Shortcode.

## Alle Paris-Bilder automatisch laden

Der folgende Shortcode lädt automatisch alle Bilder aus dem Paris-Verzeichnis:

[gallery paris]

## Weitere Beispiele

### Begrenzung auf 10 Bilder
[gallery paris 10]

### Absoluter Pfad
[gallery /assets/galleries/blackwhite/ 15]

## So funktioniert's

### **Syntax:**
```markdown
[gallery PFAD LIMIT]
```

### **Parameter:**
- **PFAD**: Pfad zum Bildverzeichnis (relativ oder absolut)
- **LIMIT**: Maximale Anzahl Bilder (optional, Standard: 100)

### **Pfad-Optionen:**
1. **Relativ:** `[gallery paris]` → lädt aus `/public/assets/galleries/paris/`
2. **Absolut:** `[gallery /assets/galleries/paris/]` → lädt aus `/public/assets/galleries/paris/`

### **Features:**
- ✅ **Automatisches Laden** aller Bilder (.jpg, .jpeg, .png, .gif, .webp)
- ✅ **Sortierung** nach Dateiname
- ✅ **Alt-Text-Generierung** aus Dateiname
- ✅ **Lazy Loading** für Performance
- ✅ **Lightbox-kompatibel** mit Gallery-Layout
- ✅ **Fehlerbehandlung** bei fehlenden Verzeichnissen

### **Unterstützte Formate:**
- JPEG (.jpg, .jpeg)
- PNG (.png)
- GIF (.gif)
- WebP (.webp)

## Weitere Gallery-Verzeichnisse

Du kannst jedes Verzeichnis unter `/public/images/galleries/` verwenden:

- `[gallery allgemein]` - Allgemeine Bilder
- `[gallery hdr]` - HDR-Fotografie
- `[gallery industrie]` - Industriefotografie
- `[gallery reisen]` - Reisebilder
- `[gallery usa]` - USA-Bilder

Die automatische Gallery-Funktion macht es super einfach, große Bildsammlungen ohne manuelles Einbinden zu präsentieren! 🖼️