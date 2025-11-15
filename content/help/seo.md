---
Title: SEO & Search Engine Control
Author: System
Tag: seo, robots, documentation
---

# SEO & Search Engine Control in StaticMD

StaticMD provides comprehensive control over search engine indexing and SEO settings.

## 🎛️ Admin Interface

### SEO Settings

In the **Admin Settings** under "SEO & Search Engine Settings":

#### **Default Robots Policy**
- `index,follow` - Search engine friendly (default)
- `index,nofollow` - Index but don't follow links
- `noindex,follow` - Don't index but follow links  
- `noindex,nofollow` - Block completely

#### **Block All Search Engines**
- ✅ Enabled: Complete website will not be indexed
- ⚠️ Overrides all other settings

#### **Generate robots.txt**
- Automatic robots.txt at `/robots.txt`
- Based on SEO settings

## 🔧 Per-Page Control

### Front Matter Robots
Each Markdown page can have its own robots directives:

```markdown
---
Title: Private Page
Author: Admin
Robots: noindex,nofollow
---

# This page will not be indexed
```

### Supported Robots Directives
- `index` / `noindex`
- `follow` / `nofollow`
- `archive` / `noarchive`
- `snippet` / `nosnippet`
- `imageindex` / `noimageindex`

## 🤖 robots.txt Features

### Automatic Generation
- **URL**: `/robots.txt` (redirected to `/robots.php`)
- **Dynamic**: Based on current SEO settings
- **Cache**: 24-hour HTTP cache headers

### When "Block Search Engines" = OFF
```
User-agent: *
Allow: /

# Block system directories
Disallow: /system/
Disallow: /admin/
Disallow: /config.php

# Special bot rules
User-agent: Googlebot
Allow: /public/
Crawl-delay: 1

# Restrict aggressive bots
User-agent: AhrefsBot
Crawl-delay: 10
Disallow: /
```

### When "Block Search Engines" = ON
```
User-agent: *
Disallow: /

# Additionally block specific bots
User-agent: Googlebot
Disallow: /

User-agent: Bingbot  
Disallow: /
```

## 🏷️ Meta-Tags Implementation

### Automatic Meta-Tags
StaticMD automatically inserts the following meta-tags:

```html
<!-- Standard Robots -->
<meta name="robots" content="index,follow">

<!-- Additionally for noindex -->
<meta name="googlebot" content="noindex,nofollow,noarchive,nosnippet">
<meta name="bingbot" content="noindex,nofollow,noarchive,nosnippet">
<meta name="yahoobot" content="noindex,nofollow">
```

### HTTP Headers
For `noindex` pages, additionally set:
```
X-Robots-Tag: noindex,nofollow,noarchive,nosnippet
```

## 📊 Crawler Control

### Allowed/Restricted Bots

**Standard Crawlers (normal)**:
- Googlebot, Bingbot, DuckDuckBot
- Crawl-Delay: 1-2 seconds

**Aggressive SEO Bots (restricted)**:
- AhrefsBot, MJ12bot, SemrushBot
- Crawl-Delay: 10 seconds
- Disallow: / (in block mode)

### Bot-Detection
robots.txt-Zugriffe werden geloggt:
```
robots.txt accessed - Block Crawlers: false
```

## 🎯 Use Cases

### 1. Complete Website Private
```
Admin > Settings > "Block All Search Engines" ✅
```

### 2. Specific Pages Private
```markdown
---
Title: Internal Documentation
Robots: noindex,nofollow
---
```

### 3. SEO Optimized (Default)
```
Admin > Settings > "Default Robots Policy" = "index,follow"
```

### 4. Staging/Development
```
Admin > Settings > "Block All Search Engines" ✅
```

## 🔍 Testing & Debugging

### Test robots.txt
- **URL**: `https://your-domain.com/robots.txt`
- **Google Search Console**: robots.txt Tester
- **Bing Webmaster Tools**: robots.txt Tester

### Check Meta Tags
```html
<!-- View page source -->
<meta name="robots" content="noindex,nofollow">
```

### Check HTTP Headers
```bash
curl -I https://your-domain.com/page
# X-Robots-Tag: noindex,nofollow,noarchive,nosnippet
```

## ⚙️ Technical Details

### Template Integration
All StaticMD themes automatically support:
```php
<!-- SEO/Robots Meta-Tags -->
<?= $robotsMeta ?>
```

### Settings Schema
```json
{
    "seo_robots_policy": "index,follow",
    "seo_block_crawlers": false,
    "seo_generate_robots_txt": true
}
```

### Front Matter Priority
1. **Front Matter `Robots:`** (highest priority)
2. **"Block All Search Engines"** 
3. **Default Robots Policy** (lowest)

## 🚀 Best Practices

### Production Website
- Default: `index,follow`
- robots.txt: Enabled
- Per-page: As needed

### Staging/Development
- Block Crawlers: ✅ Enabled
- Prevents accidental indexing

### Sensitive Content
- Front Matter: `Robots: noindex,nofollow`
- Additionally: HTTP Auth or IP restrictions

---

*SEO control in StaticMD is flexible and multi-layered - configurable from global to per-page.*