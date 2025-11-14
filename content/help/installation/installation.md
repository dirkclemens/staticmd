# StaticMD - Installationsanleitung

Komplette Anleitung für die Installation und Konfiguration von StaticMD auf Ihrem Server.

---

## 🚀 Schnellinstallation

### Systemanforderungen
- **PHP 8.3+** mit Extensions: `mbstring`, `intl`
- **Apache Webserver** mit `mod_rewrite` aktiviert
- **HTTPS-Zertifikat** (empfohlen für Security Headers)
- **SSH-Zugang** (für optimales Deployment)

### 1. Dateien installieren
```bash
git clone https://github.com/dirkclemens/staticmd.git staticMD
cd staticMD
# Oder ZIP herunterladen und entpacken
unzip staticMD.zip
cd staticMD
```

### 2. Server-Upload
```bash
nano upload.sh # SERVER und REMOTE_PATH anpassen
chmod +x upload.sh
./upload.sh
```

### 3. Erste Konfiguration
```bash
# Admin-Passwort ändern (empfohlen)
# In config.php den bcrypt-Hash anpassen
```

---

## 📋 Detaillierte Installation

### Apache VirtualHost Konfiguration
```apache
<VirtualHost *:80>
    ServerName ihre-domain.de
    DocumentRoot /var/www/html/staticMD
    <Directory /var/www/html/staticMD>
        AllowOverride All
        Require all granted
    </Directory>
    # Download-Verzeichnis als statisch ausliefern
    # RewriteCond %{REQUEST_FILENAME} -f
    # RewriteRule ^downloads/(.*)$ public/downloads/$1 [L]
</VirtualHost>
```

### Dateiberechtigungen
```bash
chmod 755 /var/www/html/staticMD
chmod -R 644 /var/www/html/staticMD/*
chmod -R 755 /var/www/html/staticMD/content/
chmod -R 755 /var/www/html/staticMD/public/downloads/
chmod 644 /var/www/html/staticMD/.htaccess
chmod 644 /var/www/html/staticMD/config.php
```

---

## 🛠 Konfiguration
### Download-Tag und Download-Verzeichnis
PDF- und ZIP-Dateien werden per Drag&Drop nach `/public/downloads/` hochgeladen und mit `[download datei.pdf "Alt-Text"]` verlinkt. Der Parser zeigt das passende Bootstrap-Icon.

### Admin-Zugangsdaten ändern
```php
'admin' => [
    'username' => 'ihr-username',
    'password' => '$2y$10$...',
    'session_timeout' => 3600
]
```

### Passwort-Hash generieren
```php
<?php
echo password_hash('ihr-neues-passwort', PASSWORD_BCRYPT);
?>
```

### Site-Konfiguration
```php
'system' => [
    'name' => 'Ihr Site-Name',
    'timezone' => 'Europe/Berlin',
    'charset' => 'UTF-8'
],
'theme' => [
    'name' => 'Bootstrap',
    'navigation' => true,
    'sidebar' => true
]
```

---

## 🌍 Produktionsumgebung

### Uberspace.de Installation
```bash
ssh user@server.uberspace.de
uberspace web domain add ihre-domain.de
cd /var/www/virtual/user/ihre-domain.de/
chmod -R 755 content/
```

### Shared Hosting Installation
```bash
# Via FTP/SFTP
# Alle Dateien in DocumentRoot hochladen
# .htaccess prüfen (mod_rewrite verfügbar?)
# PHP-Version auf 8.3+ setzen
# content/ Ordner beschreibbar machen
```

---

## 🐞 Fehlerbehebung

### 500 Internal Server Error
```bash
# Ursachen prüfen
# .htaccess Syntax-Fehler
# mod_rewrite nicht aktiviert
# PHP-Version < 8.3
# Dateiberechtigungen falsch
# Lösungen
tail -f /var/log/apache2/error.log
a2enmod rewrite
systemctl reload apache2
```

### Admin-Login funktioniert nicht
```bash
ls -la /tmp/
chmod 1777 /tmp
<?php
session_start();
echo session_save_path();
?>
```

### Markdown wird nicht gerendert
```bash
php -m | grep -E '(mbstring|intl)'
chmod -R 755 content/
```

### Umlauts in URLs funktionieren nicht
```bash
sudo apt-get install php8.3-intl
sudo yum install php83-intl
```

---

## 📈 Performance-Optimierung

### Apache-Konfiguration
```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 year"
</IfModule>
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/javascript
</IfModule>
```

### PHP-Optimierung
```php
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=4000
memory_limit=256M
```

---

## 🔄 Wartung & Updates

### Backup erstellen
```bash
tar -czf staticmd_backup_$(date +%Y%m%d).tar.gz content/ config.php public/media/
rsync -av content/ backup/content_$(date +%Y%m%d)/
```

### Updates einspielen
```bash
rsync -av --exclude=content/ --exclude=config.php neue-version/ produktiv-installation/
```

### Log-Rotation
```bash
logrotate -f /etc/logrotate.d/apache2
tail -f /var/log/php_errors.log
```

---

### Go-Live Checkliste

### Vor dem Launch
- [ ] Admin-Passwort geändert
- [ ] Site-Name in config.php angepasst
- [ ] SSL-Zertifikat installiert
- [ ] Security Headers aktiviert (automatisch)
- [ ] CSP-Test durchgeführt (`/csp-test.php`)
- [ ] Session-Timeout konfiguriert
- [ ] SEO-Einstellungen konfiguriert
- [ ] robots.txt getestet (`/robots.txt`)
- [ ] Backup-Strategie implementiert
- [ ] Performance-Optimierungen aktiviert
- [ ] 404-Seite angepasst

### Nach dem Launch
- [ ] Google Search Console einrichten
- [ ] Analytics implementieren
- [ ] Monitoring einrichten
- [ ] Wartungsintervalle planen

---

## 📞 Support & Hilfe

### Dokumentation
- **help/README.md**: Feature-Übersicht
- **CHANGELOG.md**: Entwicklungshistorie
- **scope_final.md**: Projekt-Analyse

### Live-Beispiel
- **Demo**: https://staticMD.adcore.de
- **Admin-Demo**: Vollständige Funktion
