---
Title: Uberspace Hosting
Author: StaticMD Team
Tag: hosting, uberspace, deployment
Layout: Standard
---

# Uberspace Hosting Guide

Komplette Anleitung für das Hosting von StaticMD auf Uberspace - einem deutschen Shared Hosting Service für Entwickler.

---

## Übersicht

Uberspace ist ein developer-freundlicher Hosting-Service aus Deutschland, der sich perfekt für StaticMD eignet. Diese Anleitung führt durch die komplette Installation und Konfiguration.

## Voraussetzungen

### Uberspace Account
- **Account**: Registrierung auf [uberspace.de](https://uberspace.de)
- **SSH-Zugang**: Standardmäßig aktiviert
- **PHP Version**: PHP 8.3+ (verfügbar)
- **Domains**: Kostenlose \*.uber.space Subdomain inklusive

### Lokale Vorbereitung
- StaticMD-Installation auf lokalem System
- SSH-Client (Terminal, PuTTY, etc.)
- SCP/SFTP-Client für Dateiübertragung

---

## Installation

### 1. SSH-Verbindung herstellen

```bash
# Mit SSH verbinden
ssh username@username.uber.space

# Arbeitsverzeichnis wechseln
cd ~/html
```

### 2. PHP-Version konfigurieren

```bash
# Verfügbare PHP-Versionen anzeigen
uberspace tools version list php

# PHP 8.3 aktivieren
uberspace tools version use php 8.3

# Version überprüfen
php --version
```

### 3. StaticMD hochladen

#### Option A: Git Clone (empfohlen)
```bash
# Repository klonen
git clone https://github.com/dirkclemens/staticmd.git
mv staticmd/* .
mv staticmd/.* . 2>/dev/null
rmdir staticmd
```

#### Option B: Upload via SCP
```bash
# Lokal: Dateien komprimieren
tar -czf staticmd.tar.gz *

# Hochladen
scp staticmd.tar.gz username@your-space.uber.space:~/html/

# Auf Uberspace: Entpacken
ssh username@your-space.uber.space
cd ~/html
tar -xzf staticmd.tar.gz
rm staticmd.tar.gz
```

### 4. Berechtigungen setzen

```bash
# Verzeichnisse beschreibbar machen
chmod 755 content/
chmod 755 system/
chmod 755 public/

# Config-Datei sichern
chmod 600 config.php

# Session-Verzeichnis erstellen
mkdir -p ~/tmp/sessions
chmod 700 ~/tmp/sessions
```

---

## Konfiguration

### 1. Apache-Konfiguration (.htaccess)

Uberspace-optimierte `.htaccess` erstellen:

```apache
# StaticMD - Uberspace Configuration
RewriteEngine On

# Security Headers
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-Content-Type-Options "nosniff"
Header always set Referrer-Policy "strict-origin-when-cross-origin"

# Block access to system files
RedirectMatch 403 ^/system/
RedirectMatch 403 ^/content/
RedirectMatch 403 ^/(config|composer)\.(php|json|lock)$
RedirectMatch 403 ^/\.

# Assets routing
RewriteRule ^assets/(.+)$ assets.php?file=$1 [QSA,L]

# Robots.txt routing  
RewriteRule ^robots\.txt$ robots.php [L]

# Admin interface
RewriteRule ^admin/?$ system/admin/index.php [L]
RewriteRule ^admin/(.+)$ system/admin/index.php?route=$1 [QSA,L]

# Main routing
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?route=$1 [QSA,L]

# Performance
<IfModule mod_expires.c>
    ExpiresActive on
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
</IfModule>

# Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>
```

---

## Troubleshooting

### Häufige Probleme

#### 1. 500 Internal Server Error
```bash
# Error Log prüfen
tail ~/logs/error_log

# Häufige Ursachen:
# - Falsche Berechtigungen
# - PHP-Syntax-Fehler
# - Fehlende Abhängigkeiten
```

#### 2. Session-Probleme
```bash
# Session-Verzeichnis prüfen
ls -la ~/tmp/sessions/

# Berechtigungen korrigieren
chmod 700 ~/tmp/sessions
```

## Support

### Uberspace-spezifische Hilfe
- **Manual**: [manual.uberspace.de](https://manual.uberspace.de)
- **Support**: Ticket-System im Uberspace-Dashboard
- **Community**: [Twitter @ubernauten](https://twitter.com/ubernauten)

### StaticMD-Support  
- **GitHub**: Issues und Discussions
- **Documentation**: Vollständige Docs im `/content/help/` Verzeichnis

---

*StaticMD auf Uberspace - Professional German Hosting für Entwickler*
