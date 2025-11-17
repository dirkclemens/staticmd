---
Title: Accordion Test mit IDs
Layout: page
Tag: test, accordion, ids
Visibility: public
---

# Test für Accordion-Schließungs-Syntaxen

## Standard Syntax (ohne Parameter)

[accordionstart test1 "Standard Accordion"]

Das ist ein **Standard Accordion** mit normaler Schließung.

### Listen funktionieren
- Punkt 1
- Punkt 2  
- Punkt 3

[accordionstop]

## Legacy Syntax (mit ID-Parameter)

[accordionstart specs1 "Specs mit ID-Parameter"]

| Hardware | Details |
|----------|---------|
| CPU | Intel Core i5 |
| RAM | 16 GB DDR4 |
| SSD | 512 GB NVMe |

Das ist der **problematische Fall** mit ID im spoilerstop.

[accordionstop specs1]

## Gemischte Syntax

[accordionstart mixed "Gemischter Test"]

```php
// Code-Block Test
echo "Hallo Welt!";
```

[accordionstop mixed]

Das sollte **alles funktionieren** nach der Korrektur! 🎯