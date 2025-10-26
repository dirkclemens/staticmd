# Pages Shortcode - Layout-Dokumentation

## Neue Syntax:
```markdown
[pages /ordnerpfad/ limit layout]
```

## Parameter:

1. **Ordnerpfad** (erforderlich): Der Pfad zum Content-Ordner
2. **Limit** (optional, Standard: 1000): Maximale Anzahl der Seiten
3. **Layout** (optional, Standard: 'columns'): Anordnung der Seiten

## Layout-Optionen:

### Spaltenweise Sortierung (Standard):
```markdown
[pages /tech/ 1000 columns]
[pages /tech/ 1000]  <!-- gleicher Effekt -->
```
- Sortiert spaltenweise: A, D, G | B, E, H | C, F, I
- Items werden vertikal auf Spalten verteilt

### Zeilenweise Sortierung:
```markdown
[pages /tech/ 1000 rows]
```
- Sortiert zeilenweise: A, B, C | D, E, F | G, H, I  
- Items werden horizontal auf Spalten verteilt
- **Visuelle Darstellung bleibt in 4 Spalten**

## Sortierung:
- **Case-insensitive**: "apple" kommt vor "Banana"
- **Alphabetisch**: Nach Seitentitel sortiert
- **Konsistent**: Gleiche Sortierung in allen Übersichten

## Beispiele:

```markdown
<!-- Alle Tech-Seiten in Spalten -->
[pages /tech/ 1000 columns]

<!-- Nur 10 Blog-Artikel zeilenweise -->
[pages /blog/ 10 rows]

<!-- Alle Seiten im aktuellen Ordner -->
[pages / 1000 rows]
```