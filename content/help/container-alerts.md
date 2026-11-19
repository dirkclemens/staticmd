---
Title: Container-Blöcke & Alerts
Tag: markdown, container, alerts, layout, hilfe
Author: StaticMD
Layout: wiki
---

# Container-Blöcke & Alerts

StaticMD unterstützt **Container-Blöcke** mit `:::` (wie Pandoc, Quarto, Docusaurus, VitePress) und **Alerts** im GitHub-Stil (`> [!NOTE]`). Damit lassen sich Hinweisboxen, aufklappbare Abschnitte und mehrspaltige Layouts direkt in Markdown schreiben.

> [!TIP] Einfügen per Toolbar
> Im Editor fügt das Menü mit dem **Spalten-Symbol** alle Alerts, Spalten-Layouts, Boxen und aufklappbare Abschnitte als Vorlage ein. Ist Text markiert, wird er direkt in den Block übernommen. Siehe [Editor Tastenkombinationen](/help/editor-shortcuts).

## Alerts (GitHub-Stil)

```markdown
> [!NOTE]
> Nützliche Information, die man auch beim Überfliegen sehen sollte.

> [!TIP]
> Ein Tipp, wie es leichter geht.

> [!IMPORTANT]
> Wichtige Information.

> [!WARNING]
> Dringende Information, die sofortige Aufmerksamkeit erfordert.

> [!CAUTION]
> Warnung vor Risiken oder negativen Folgen.
```

> [!NOTE]
> Nützliche Information, die man auch beim Überfliegen sehen sollte.

> [!TIP]
> Ein Tipp, wie es leichter geht.

> [!IMPORTANT]
> Wichtige Information.

> [!WARNING]
> Dringende Information, die sofortige Aufmerksamkeit erfordert.

> [!CAUTION]
> Warnung vor Risiken oder negativen Folgen.

### Eigener Titel und aufklappbar (Obsidian-Stil)

```markdown
> [!WARNING] Vor dem Update lesen
> Backup erstellen!

> [!faq]- Wie funktioniert das? (zugeklappt)
> Mit `-` ist der Alert zugeklappt, mit `+` aufgeklappt.
```

> [!WARNING] Vor dem Update lesen
> Backup erstellen!

> [!faq]- Wie funktioniert das? (zugeklappt)
> Mit `-` ist der Alert zugeklappt, mit `+` aufgeklappt.

**Typen:** `NOTE`, `TIP`, `IMPORTANT`, `WARNING`, `CAUTION` – Groß-/Kleinschreibung egal.
Zusätzlich werden gängige Aliase verstanden, z. B. `info`, `hint`, `success`, `question`/`faq`, `danger`, `error`, `bug`, `example`.

Innerhalb eines Alerts funktioniert normales Markdown: Listen, Code, Links, Tabellen, sogar verschachtelte Container.

## Container mit `:::`

Ein Container beginnt mit mindestens drei Doppelpunkten **und einem Namen bzw. Attributen** und endet mit einer Zeile **nur aus Doppelpunkten**:

```markdown
::: warning
Inhalt
:::
```

### Alerts als Container

Alle Alert-Typen gehen auch als Container – mit optionalem Titel dahinter (Docusaurus/VitePress) oder im Quarto-Stil:

```markdown
::: tip Gut zu wissen
Inhalt
:::

::: {.callout-important title="Quarto-Callout" collapse="true"}
`collapse="true"` = zugeklappt, `collapse="false"` = aufgeklappt
:::
```

::: tip Gut zu wissen
Inhalt
:::

::: {.callout-important title="Quarto-Callout" collapse="true"}
`collapse="true"` = zugeklappt, `collapse="false"` = aufgeklappt
:::

### Aufklappbarer Abschnitt

```markdown
::: details Klick mich
Versteckter Inhalt
:::

::: details Schon offen {open}
Sichtbarer Inhalt
:::
```

::: details Klick mich
Versteckter Inhalt
:::

Der ältere Shortcode `[accordionstart id "Titel"]` … `[accordionstop]` (ebenso `[spoilerstart]`) ist ein Alias dafür und erzeugt exakt dasselbe – `::: details Titel {#accordion-id}`. Bestehende Seiten funktionieren also unverändert weiter, lassen sich jetzt auch verschachteln und erscheinen in der Editor-Vorschau.

### Spalten

Äußerer Container `columns`, darin je Spalte ein `column`. Der äußere Container braucht **mehr Doppelpunkte** als die inneren, damit klar ist, welches `:::` was schließt.

```markdown
:::: {.columns gap=2rem valign=center}
::: {.column width="40%" border align=center}
**Linke Spalte** – 40 % breit, mit Rahmen, zentriert
:::
::: {.column border}
**Rechte Spalte** – nimmt den restlichen Platz ein
:::
::::
```

:::: {.columns gap=2rem valign=center}
::: {.column width="40%" border align=center}
**Linke Spalte** – 40 % breit, mit Rahmen, zentriert
:::
::: {.column border}
**Rechte Spalte** – nimmt den restlichen Platz ein
:::
::::

Ohne `width` teilen sich die Spalten den Platz gleichmäßig. Auf schmalen Bildschirmen (< 768 px) werden die Spalten automatisch untereinander angezeigt.

### Generische Container

Jeder andere Name wird zur CSS-Klasse. Da alle Themes Bootstrap laden, funktionieren auch Bootstrap-Klassen direkt:

```markdown
::: {.text-center .border .rounded .p-3 .shadow-sm}
Zentrierte Box mit Rahmen, Innenabstand und Schatten
:::

::: {#kontakt border bg="#f6f8fa" padding=1.5rem}
Box mit Anker `#kontakt`, Rahmen, Hintergrund und Innenabstand
:::
```

::: {.text-center .border .rounded .p-3 .shadow-sm}
Zentrierte Box mit Rahmen, Innenabstand und Schatten
:::

## Attribute

Attribute stehen in geschweiften Klammern: `{#id .klasse schlüssel=wert schlüssel="Wert mit Leerzeichen"}`.

| Attribut | Wirkung | Beispiel |
|----------|---------|----------|
| `#id` | Anker-ID (Sprungmarke) | `{#abschnitt1}` |
| `.klasse` | CSS-Klasse (z. B. Bootstrap) | `{.text-center .p-3}` |
| `title` | Titel für Alerts, Details, Container | `title="Mein Titel"` |
| `align` | Textausrichtung: `left`, `center`, `right`, `justify` | `align=center` |
| `valign` | Vertikal: `top`, `center`, `bottom`, `stretch` (bei `columns` für alle Spalten, sonst nur dieser Block) | `valign=center` |
| `width` | Breite (bei `column`: Spaltenbreite) | `width="30%"` |
| `gap` | Abstand zwischen Spalten | `gap=2rem` |
| `border` / `frame` | Rahmen; ohne Wert Standard-Rahmen, mit Wert eigener | `border` / `border="2px dashed #c00"` |
| `padding` | Innenabstand | `padding=1rem` |
| `bg` / `background` | Hintergrundfarbe | `bg="#fff8e1"` |
| `color` | Textfarbe | `color=#333` |
| `radius` | Eckenradius | `radius=12px` |
| `style` | Freies CSS (nur einfache Werte, kein `url()`) | `style="font-size: 1.2rem"` |
| `open` | Details aufgeklappt anzeigen | `{open}` |
| `collapse` | Quarto: Alert aufklappbar (`true` = zu, `false` = offen) | `collapse="true"` |

Unbekannte Attribute werden als `data-*`-Attribute ausgegeben.

> [!TIP]
> Der **Syntax prüfen**-Button im Editor warnt, wenn ein `:::` nicht geschlossen wird.
