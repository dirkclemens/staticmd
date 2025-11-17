---
Title: Beispiel Galerie
Layout: gallery
Author: StaticMD
Date: 2024-11-16
Tag: bilder, galerie, beispiel
Description: Eine Beispiel-Galerie zum Testen des Gallery-Layouts
---

# Meine Beispiel-Galerie

Diese Galerie zeigt das neue Gallery-Layout in Aktion. Bilder werden automatisch in einem responsiven Grid angeordnet und können mit einem Lightbox-Effekt vergrößert werden.

## Bilder hinzufügen

### Lokale Bilder (empfohlen)
Lade deine Bilder in den `/public/images/` Ordner hoch und verweise dann darauf:

```markdown
![Mein Bild](../public/images/mein-bild.jpg "Beschreibung")
```

### Externe Bilder (nur für Tests)
Du kannst auch externe Bilder verwenden:

![Beispielbild 1](https://picsum.photos/400/300?random=1 "Natur")
![Beispielbild 2](https://picsum.photos/400/400?random=2 "Architektur") 
![Beispielbild 3](https://picsum.photos/400/250?random=3 "Kunst")
![Beispielbild 4](https://picsum.photos/400/350?random=4 "Landschaft")
![Beispielbild 5](https://picsum.photos/400/280?random=5 "Portrait")
![Beispielbild 6](https://picsum.photos/400/320?random=6 "Abstrakt")

> **Hinweis:** Externe Bilder benötigen eine Internetverbindung und können langsamer laden.

## Funktionen des Gallery-Layouts

- **Responsives Grid**: Automatische Anpassung an verschiedene Bildschirmgrößen
- **Lightbox**: Klicke auf ein Bild, um es in voller Größe anzuzeigen
- **Hover-Effekte**: Elegante Animationen beim Überfahren mit der Maus
- **Filter**: Wenn Tags in den Front Matter definiert sind, können Bilder gefiltert werden
- **Bildstatistiken**: Automatische Anzeige der Anzahl der Bilder
- **Masonry-Layout**: Verschiedene Bildhöhen für interessante Darstellung

## Verwendung

Um eine Seite mit dem Gallery-Layout zu verwenden, setze einfach `Layout: gallery` in die Front Matter deiner Markdown-Datei.

Das Layout funktioniert am besten mit:
- Mehreren Bildern pro Seite
- Bildern mit aussagekräftigen Alt-Texten
- Optional: Tags für Filterung