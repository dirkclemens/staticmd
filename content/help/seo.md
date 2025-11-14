---
Title: SEO & Suchmaschinen-Kontrolle
Author: StaticMD Team
Tag: seo, robots, documentation
---

# SEO & Suchmaschinen-Kontrolle in StaticMD

StaticMD bietet umfassende Kontrolle über Suchmaschinen-Indexierung und SEO-Einstellungen.

## 🎛️ Admin-Interface

### SEO-Einstellungen

In den **Admin-Settings** finden Sie unter "SEO & Suchmaschinen-Einstellungen":

#### **Standard Robots-Policy**
- `index,follow` - Suchmaschinen-freundlich (Standard)
- `index,nofollow` - Indexieren aber Links nicht folgen
- `noindex,follow` - Nicht indexieren aber Links folgen  
- `noindex,nofollow` - Komplett blockieren

#### **Alle Suchmaschinen blockieren**
- ✅ Aktiviert: Komplette Website wird nicht indexiert
- ⚠️ Überschreibt alle anderen Einstellungen

#### **robots.txt generieren**
- Automatische robots.txt unter `/robots.txt`
- Basiert auf den SEO-Einstellungen

## 🔧 Pro-Seite Kontrolle

### Front Matter Robots
Jede Markdown-Seite kann eigene Robots-Direktiven haben:

```markdown
---
Title: Private Seite
Author: Admin
Robots: noindex,nofollow
---

# Diese Seite wird nicht indexiert
```

### Unterstützte Robots-Direktiven
- `index` / `noindex`
- `follow` / `nofollow`
- `archive` / `noarchive`
- `snippet` / `nosnippet`
- `imageindex` / `noimageindex`

## 🤖 robots.txt Features

### Automatische Generierung
- **URL**: `/robots.txt` (wird zu `/robots.php` weitergeleitet)
- **Dynamisch**: Basiert auf aktuellen SEO-Settings
- **Cache**: 24 Stunden HTTP-Cache-Header

### Bei "Suchmaschinen blockieren" = AUS
```
User-agent: *
Allow: /

# System-Verzeichnisse blockieren
Disallow: /system/
Disallow: /admin/
Disallow: /config.php

# Spezielle Bot-Regelungen
User-agent: Googlebot
Allow: /public/
Crawl-delay: 1

# Aggressive Bots beschränken
User-agent: AhrefsBot
Crawl-delay: 10
Disallow: /
```

### Bei "Suchmaschinen blockieren" = AN
```
User-agent: *
Disallow: /

# Zusätzlich spezifische Bots blockieren
User-agent: Googlebot
Disallow: /

User-agent: Bingbot  
Disallow: /
```

## 🏷️ Meta-Tags Implementation

### Automatische Meta-Tags
StaticMD fügt automatisch folgende Meta-Tags ein:

```html
<!-- Standard Robots -->
<meta name="robots" content="index,follow">

<!-- Bei noindex zusätzlich -->
<meta name="googlebot" content="noindex,nofollow,noarchive,nosnippet">
<meta name="bingbot" content="noindex,nofollow,noarchive,nosnippet">
<meta name="yahoobot" content="noindex,nofollow">
```

### HTTP-Headers
Bei `noindex` wird zusätzlich gesetzt:
```
X-Robots-Tag: noindex,nofollow,noarchive,nosnippet
```

## 📊 Crawler-Kontrolle

### Erlaubte/Beschränkte Bots

**Standard-Crawler (normal)**:
- Googlebot, Bingbot, DuckDuckBot
- Crawl-Delay: 1-2 Sekunden

**Aggressive SEO-Bots (beschränkt)**:
- AhrefsBot, MJ12bot, SemrushBot
- Crawl-Delay: 10 Sekunden
- Disallow: / (bei Block-Modus)

### Bot-Detection
robots.txt-Zugriffe werden geloggt:
```
robots.txt accessed - Block Crawlers: false
```

## 🎯 Anwendungsfälle

### 1. Komplette Website privat
```
Admin > Settings > "Alle Suchmaschinen blockieren" ✅
```

### 2. Bestimmte Seiten privat
```markdown
---
Title: Interne Dokumentation
Robots: noindex,nofollow
---
```

### 3. SEO-optimiert (Standard)
```
Admin > Settings > "Standard Robots-Policy" = "index,follow"
```

### 4. Staging/Development
```
Admin > Settings > "Alle Suchmaschinen blockieren" ✅
```

## 🔍 Testing & Debugging

### robots.txt testen
- **URL**: `https://your-domain.com/robots.txt`
- **Google Search Console**: robots.txt Tester
- **Bing Webmaster Tools**: robots.txt Tester

### Meta-Tags prüfen
```html
<!-- Seitenquelltext anzeigen -->
<meta name="robots" content="noindex,nofollow">
```

### HTTP-Headers prüfen
```bash
curl -I https://your-domain.com/seite
# X-Robots-Tag: noindex,nofollow,noarchive,nosnippet
```

## ⚙️ Technische Details

### Template-Integration
Alle StaticMD-Themes unterstützen automatisch:
```php
<!-- SEO/Robots Meta-Tags -->
<?= $robotsMeta ?>
```

### Settings-Schema
```json
{
    "seo_robots_policy": "index,follow",
    "seo_block_crawlers": false,
    "seo_generate_robots_txt": true
}
```

### Front Matter Priority
1. **Front Matter `Robots:`** (höchste Priorität)
2. **"Alle Suchmaschinen blockieren"** 
3. **Standard Robots-Policy** (niedrigste)

## 🚀 Best Practices

### Production-Website
- Standard: `index,follow`
- robots.txt: Aktiviert
- Pro-Seite: Nach Bedarf

### Staging/Development
- Block Crawlers: ✅ Aktiviert
- Verhindert versehentliche Indexierung

### Sensitive Content
- Front Matter: `Robots: noindex,nofollow`
- Zusätzlich: HTTP Auth oder IP-Beschränkung

---

*SEO-Kontrolle in StaticMD ist flexibel und mehrschichtig - von global bis pro-Seite konfigurierbar.*