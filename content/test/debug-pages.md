---
Title: Debug Pages Shortcode
Layout: page
---

# Debug Test für Pages Shortcode

## Testfall 1: Direkter Pfad
[pages tech 1000]

## Testfall 2: Mit Slash
[pages /tech/ 1000]

## Testfall 3: Ohne Slash
[pages tech 1000]

## Debug Info

- Parameter sollten sein: `tech` und `1000`
- Ziel-Pfad sollte sein: `content/tech/`
- Erwartete Dateien: hackintosh-dell-5400.md, hackintosh-dell-mini.md, etc.

Debug Ende.