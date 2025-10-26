# StaticMD - Installationsanleitung

Komplette Anleitung für die Installation und Konfiguration von StaticMD auf Ihrem Server.

---

## 🚀 Schnellinstallation

### Systemanforderungen
- **PHP 8.3+** mit Extensions: `mbstring`, `intl`
- **Apache Webserver** mit `mod_rewrite` aktiviert
- **SSH-Zugang** (für optimales Deployment)

### 1. Dateien installieren
```bash
# Via Git
git clone [repository-url] staticMD
cd staticMD

# Oder ZIP herunterladen und entpacken
unzip staticMD.zip
cd staticMD
```

### 2. Server-Upload
```bash
# Upload-Script anpassen
nano upload.sh
# SERVER="user@your-server.com"
# REMOTE_PATH="/var/www/html/"

# Upload ausführen
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
    
    # Optional: HTTPS Redirect
    # RewriteEngine On
    # RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</VirtualHost>
```

### Dateiberechtigungen
```bash
# Grundberechtigungen
chmod 755 /var/www/html/staticMD
chmod -R 644 /var/www/html/staticMD/*
chmod -R 755 /var/www/html/staticMD/content/

# Spezielle Berechtigungen
chmod 644 /var/www/html/staticMD/.htaccess
chmod 644 /var/www/html/staticMD/config.php
```

---

## 🔧 Konfiguration

### Admin-Zugangsdaten ändern
```php
// config.php bearbeiten
'admin' => [
    'username' => 'ihr-username',
    'password' => '$2y$10$...',  // Neuen bcrypt-Hash generieren
    'session_timeout' => 3600
]
```

### Neuen Passwort-Hash generieren
```php
// Temporäres PHP-Script ausführen
<?php
echo password_hash('ihr-neues-passwort', PASSWORD_BCRYPT);
?>
```

### Site-Konfiguration anpassen
```php
// config.php - System-Einstellungen
'system' => [
    'name' => 'Ihr Site-Name',
    'timezone' => 'Europe/Berlin',  // Ihre Zeitzone
    'charset' => 'UTF-8'
],

// Theme-Anpassungen
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
# SSH-Verbindung zu Uberspace
ssh user@server.uberspace.de

# Domain konfigurieren  
uberspace web domain add ihre-domain.de

# Upload der Dateien
cd /var/www/virtual/user/ihre-domain.de/
# Dateien hierhin kopieren

# Berechtigungen setzen
chmod -R 755 content/
```

### Shared Hosting Installation
```bash
# Via FTP/SFTP
1. Alle Dateien in DocumentRoot hochladen
2. .htaccess überprüfen (mod_rewrite verfügbar?)
3. PHP-Version auf 8.3+ setzen  
4. content/ Ordner beschreibbar machen
```

---

## 🧪 Installation testen

### Basis-Funktionalität
1. **Website aufrufen**: `https://ihre-domain.de/`
   - ✅ Sollte Startseite mit Navigation zeigen
   
2. **Admin-Bereich**: `https://ihre-domain.de/admin`
   - ✅ Sollte Login-Form anzeigen
   
3. **Login testen**: Username/Passwort eingeben
   - ✅ Sollte zum Dashboard weiterleiten

### Erweiterte Features testen
1. **Suche testen**: `https://ihre-domain.de/search?q=test`
   - ✅ Sollte Suchergebnisse anzeigen
   
2. **Tag-Übersicht**: `https://ihre-domain.de/tag`
   - ✅ Sollte alle Tags alphabetisch auflisten
   
3. **Unicode-URLs**: `https://ihre-domain.de/tech/zb2l3-kapazitätstester`
   - ✅ Sollte deutsche Umlauts korrekt verarbeiten

---

## 🐛 Fehlerbehebung

### Häufige Probleme

#### 1. "500 Internal Server Error"
```bash
# Ursachen prüfen
- .htaccess Syntax-Fehler
- mod_rewrite nicht aktiviert  
- PHP-Version < 8.3
- Dateiberechtigungen falsch

# Lösungen
tail -f /var/log/apache2/error.log
a2enmod rewrite
systemctl reload apache2
```

#### 2. "Admin-Login funktioniert nicht"
```bash
# Session-Ordner prüfen
ls -la /tmp/
chmod 1777 /tmp

# PHP-Sessions testen
<?php
session_start();
echo session_save_path();
?>
```

#### 3. "Markdown wird nicht gerendert"
```bash
# PHP-Extensions prüfen
php -m | grep -E '(mbstring|intl)'

# Content-Berechtigungen
chmod -R 755 content/
```

#### 4. "Umlauts in URLs funktionieren nicht"
```bash
# PHP intl Extension installieren
# Ubuntu/Debian:
sudo apt-get install php8.3-intl

# CentOS/RHEL:
sudo yum install php83-intl
```

---

## 📊 Performance-Optimierung

### Apache-Konfiguration
```apache
# .htaccess Ergänzungen
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
// php.ini Anpassungen
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=4000
memory_limit=256M
```

---

## 🔄 Wartung & Updates

### Backup erstellen
```bash
# Vollständiges Backup
tar -czf staticmd_backup_$(date +%Y%m%d).tar.gz \
    content/ config.php public/media/

# Nur Content sichern  
rsync -av content/ backup/content_$(date +%Y%m%d)/
```

### Updates einspielen
```bash
# System-Dateien aktualisieren (ohne Content)
rsync -av --exclude=content/ \
    --exclude=config.php \
    neue-version/ produktiv-installation/
```

### Log-Rotation
```bash
# Apache-Logs rotieren
logrotate -f /etc/logrotate.d/apache2

# PHP-Error-Logs prüfen  
tail -f /var/log/php_errors.log
```

---

## 🚀 Go-Live Checkliste

### Vor dem Launch
- [ ] Admin-Passwort geändert
- [ ] Site-Name in config.php angepasst
- [ ] SSL-Zertifikat installiert
- [ ] Backup-Strategie implementiert
- [ ] Performance-Optimierungen aktiviert
- [ ] 404-Seite angepasst

### Nach dem Launch  
- [ ] Google Search Console einrichten
- [ ] Analytics implementieren (falls gewünscht)
- [ ] Monitoring einrichten
- [ ] Wartungsintervalle planen

---

## 📞 Support & Hilfe

### Dokumentation
- **README.md**: Vollständige Feature-Übersicht
- **CHANGELOG.md**: Entwicklungshistorie
- **scope_final.md**: Projekt-Analyse

### Live-Beispiel
- **Demo-Installation**: https://flat.adcore.de/
- **Admin-Demo**: Funktionalität vollständig sichtbar

### Weitere Ressourcen
- **Bootstrap 5 Docs**: https://getbootstrap.com/docs/5.3/
- **CodeMirror Docs**: https://codemirror.net/
- **PHP Manual**: https://www.php.net/manual/

---

*StaticMD Installation Guide - Version 1.0.0*