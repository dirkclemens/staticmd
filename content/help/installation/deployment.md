# StaticMD Deployment Guide

## 🛠 Pre-Deployment Setup

### 1. Admin-Passwort ändern
Bearbeiten Sie `config.php` und ändern Sie:
```php
'admin' => [
    'username' => 'admin',
    'password' => password_hash('IHR_SICHERES_PASSWORT', PASSWORD_DEFAULT),
    'session_timeout' => 3600
],
```

### 2. Server-Anforderungen prüfen
- PHP 8.0+ (idealerweise 8.4+)
- Apache mit mod_rewrite ODER Nginx
- Schreibrechte für `content/` Verzeichnis

### 3. Datei-Berechtigungen setzen
```bash
chmod 755 content/ system/ public/downloads/
chmod 644 content/*.md content/*/*.md
chmod 600 config.php
```

### 4. Debug-Modus deaktivieren
In `config.php`:
```php
'system' => [
    'name' => 'StaticMD',
    'debug' => false
],
```

## 🌐 Uberspace Server-Konfiguration
### Download-Verzeichnis
Statische Downloads werden unter `/public/downloads/` gespeichert und über `/downloads/datei.pdf` ausgeliefert.

### Uberspace Setup
- Dateien hochladen nach `/var/www/virtual/USER/html/`
- Domain einrichten: `uberspace web domain add staticMD.your-domain.com`
- Berechtigungen setzen: `chmod 755 content/ system/`
- Testen: `https://staticMD.your-domain.com/` und `https://staticMD.your-domain.com/admin`

### Nginx Beispiel-Konfiguration
```nginx
server {
    listen 80;
    server_name staticMD.your-domain.com;
    root /var/www/virtual/USER/staticMD.your-domain.com;
    index index.php;
    location ~ ^/(system|content)/ { deny all; return 403; }
    location ~ ^/admin(/.*)?$ { try_files $uri $uri/ /system/admin/index.php?route=$1; }
    location / { try_files $uri $uri/ /index.php?route=$uri&$args; }
    location ~ \.php$ { fastcgi_pass unix:/var/run/php/php8.4-fpm.sock; fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; include fastcgi_params; }
    location ~ /\. { deny all; }
    location ~* \.(md|json)$ { deny all; }
}
```

## 📦 Upload-Methoden
- FTP/SFTP Upload
- Git Deployment
- ZIP Upload

## ✅ Nach dem Deployment testen
- Frontend: `http://your-domain.com/`
- Admin: `http://your-domain.com/admin`
- Editor: Neue Seite erstellen
- Navigation: Alle Links prüfen

## 🔒 Sicherheits-Features (automatisch aktiviert)

### Content-Security-Policy (CSP)
- ✅ **Automatisch aktiviert** - Schutz vor XSS-Angriffen
- ✅ **Kontextbasiert** - Frontend/Admin-spezifische Policies
- ✅ **CDN-Whitelist** - Bootstrap, CodeMirror erlaubt
- ✅ **Nonce-System** - Sichere Inline-Scripts

### HTTP Security Headers
- ✅ **X-Frame-Options**: DENY (Clickjacking-Schutz)
- ✅ **X-Content-Type-Options**: nosniff
- ✅ **X-XSS-Protection**: 1; mode=block
- ✅ **Referrer-Policy**: strict-origin-when-cross-origin
- ✅ **HSTS**: Bei HTTPS automatisch aktiviert
- ✅ **Permissions-Policy**: Unnötige Browser-APIs deaktiviert

### Session-Security
- ✅ **Sichere Cookies**: HttpOnly, Secure, SameSite=Strict
- ✅ **CSRF-Schutz**: Alle Admin-Aktionen geschützt
- ✅ **Session-Timeout**: Konfigurierbar bis 48h
- ✅ **Path-Traversal-Schutz**: URL-Validierung

### CSP-Test durchführen
Nach dem Deployment testen:

**Security Tests:**
```
https://your-domain.com/csp-test.php?context=frontend
https://your-domain.com/csp-test.php?context=admin
```

**SEO Tests:**
```
https://your-domain.com/robots.txt
https://your-domain.com/admin (SEO-Settings konfigurieren)
```

## 🛡️ Zusätzliche Sicherheits-Tipps
- SSL/HTTPS aktivieren (für HSTS)
- Firewall konfigurieren
- Regelmäßige Backups
- PHP Error-Logs überwachen
- Updates von PHP und Server-Software
- CSP-Violations im Browser-Log überwachen

## 🚨 Häufige Probleme
- 500 Internal Server Error: PHP Error-Log prüfen, mod_rewrite aktiviert?
- Admin-Login funktioniert nicht: Passwort-Hash korrekt, Session-Ordner beschreibbar?
- CSS/JS lädt nicht: CDN-Links erreichbar?

---
