---
Title: Accordion Test
Layout: page
Tag: test, accordion, bootstrap
Visibility: public
---

# Accordion Funktionalität Test

Diese Seite testet die neue **Accordion-Funktionalität** in unserem StaticMD System.

## Standard Accordion

[accordionstart demo "Demo Accordion"]

Das ist der **Inhalt** eines Standard-Accordions.

### Unterpunkte funktionieren auch

- Listenpunkt 1
- Listenpunkt 2
- Listenpunkt 3

```php
// Auch Code-Blöcke funktionieren
echo "Hallo Welt!";
```

[accordionstop]

## Legacy Spoiler Support

[accordionstart legacy "Legacy Spoiler Support"]

Unser System unterstützt **beide Syntaxen**:   
```
1. [spoilerstart id "titel"] ... [spoilerstop]
2. [ accordionstart id "titel"] ... [ accordionstop]
```   

### Warum Accordion?

- **Bessere UX**: Klarer Interface-Standard
- **Accessibility**: ARIA-Labels für Screen Reader
- **Bootstrap 5**: Native Integration
- **SEO**: Content bleibt indexierbar

[accordionstop]

## Mehre Accordions

[accordionstart tech "Technische Details"]

### Bootstrap 5 Accordion Features

- **Collapsible Content**: Ein-/Ausklappbar
- **Unique IDs**: Mehrere pro Seite möglich
- **Responsive**: Mobile-optimiert
- **Accessible**: WCAG-konform

### HTML Struktur

```html
<div class="accordion" id="accordion-tech">
  <div class="accordion-item">
    <h3 class="accordion-header">
      <button class="accordion-button collapsed" ...>
        Technische Details
      </button>
    </h3>
    <div class="accordion-collapse collapse" ...>
      <div class="accordion-body">
        <!-- Content hier -->
      </div>
    </div>
  </div>
</div>
```

[accordionstop]

Das war ein kompletter Test der Accordion-Funktionalität! 🎉