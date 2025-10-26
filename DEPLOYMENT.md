# StaticMD Deployment Guide

## 🔧 Pre-Deployment Setup

### 1. Admin-Passwort ändern (WICHTIG!)
Bearbeiten Sie `config.php` und ändern Sie:

```php
'admin' => [
    'username' => 'admin',                    // Ggf. anderen Username wählen
    'password' => password_hash('IHR_SICHERES_PASSWORT', PASSWORD_DEFAULT),
    'session_timeout' => 3600
],
```

Nutzen Sie dieses PHP-Script zum Generieren des Hashes:
```php
<?php echo password_hash('IHR_NEUES_PASSWORT', PASSWORD_DEFAULT); ?>
```

### 2. Server-Anforderungen prüfen
- PHP 8.0+ (idealerweise 8.4+)
- Apache mit mod_rewrite ODER Nginx
- Schreibrechte für `content/` Verzeichnis

### 3. Datei-Berechtigungen setzen
```bash
# Auf dem Server ausführen:
chmod 755 content/ system/
chmod 644 content/*.md content/*/*.md
chmod 600 config.php  # Config-Datei schützen
```

### 4. Debug-Modus deaktivieren
In `config.php` hinzufügen:
```php
'system' => [
    'name' => 'StaticMD',
    'version' => '1.0.0',
    'timezone' => 'Europe/Berlin',
    'charset' => 'UTF-8',
    'debug' => false  // WICHTIG: Auf false setzen!
],
```

## 🌐 Uberspace Server-Konfiguration

### 🚀 Uberspace Setup (Einfach & Empfohlen!)
Bei Uberspace läuft StaticMD **out-of-the-box** mit der vorhandenen Apache-Konfiguration!

**❌ Keine manuelle Nginx-Konfiguration nötig!** 
**✅ Die `.htaccess` funktioniert automatisch!**

#### 1. Dateien hochladen
```bash
# Per SFTP in Ihr DocumentRoot:
# Standard: /var/www/virtual/BENUTZERNAME/html/
# Oder Unterverzeichnis: /var/www/virtual/BENUTZERNAME/html/staticmd/
```

#### 2. Domain einrichten
```bash
# SSH auf Ihren Uberspace:
uberspace web domain add flat.adcore.de
```

#### 3. Berechtigungen setzen
```bash
# Auf dem Uberspace-Server:
cd /var/www/virtual/BENUTZERNAME/html/
chmod 755 content/ system/
chmod 600 config.php
```

#### 4. Testen
- Frontend: `https://flat.adcore.de/`
- Admin: `https://flat.adcore.de/admin`

### 🛡️ Warum funktioniert das so einfach?
Uberspace hat Apache bereits so konfiguriert, dass:
- ✅ PHP läuft (mod_php + php-fpm)
- ✅ `.htaccess` wird verarbeitet (AllowOverride All)
- ✅ URL-Rewriting ist aktiviert (mod_rewrite)  
- ✅ HTTPS ist automatisch (Let's Encrypt)

### Traditionelle Server (nicht Uberspace)
<details>
<summary>Nginx Konfiguration für andere Server</summary>

```nginx
server {
    listen 80;
    server_name flat.adcore.de;
    root /var/www/virtual/mbx/flat.adcore.de;
    index index.php;

    # Verzeichnis-Zugriff verhindern
    location ~ ^/(system|content)/ {
        deny all;
        return 403;
    }
    
    # Admin-Bereich
    location ~ ^/admin(/.*)?$ {
        try_files $uri $uri/ /system/admin/index.php?route=$1;
        location ~ \.php$ {
            fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
        }
    }
    
    # Hauptseite
    location / {
        try_files $uri $uri/ /index.php?route=$uri&$args;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Sicherheit
    location ~ /\. {
        deny all;
    }
    
    location ~* \.(md|json)$ {
        deny all;
    }
}
```
</details>

## 📁 Upload-Methoden

### Option 1: FTP/SFTP Upload
```bash
# Alle Dateien hochladen außer:
# - .git/ (falls vorhanden)
# - README.md (optional)
# - scope.md (optional)
```

### Option 2: Git Deployment
```bash
# Auf dem Server:
git clone https://github.com/ihr-repo/staticmd.git
cd staticmd
chmod 755 content/ system/
chmod 600 config.php
```

### Option 3: ZIP Upload
1. Projekt als ZIP packen
2. Auf Server entpacken  
3. Berechtigungen setzen

## ✅ Nach dem Deployment testen

1. **Frontend testen**: `http://ihre-domain.com/`
2. **Admin testen**: `http://ihre-domain.com/admin`
3. **Login testen**: Mit neuen Zugangsdaten
4. **Editor testen**: Neue Seite erstellen
5. **Navigation testen**: Alle Links überprüfen

## 🔒 Sicherheits-Tipps

- **SSL/HTTPS aktivieren** (Let's Encrypt)
- **Firewall konfigurieren**
- **Regelmäßige Backups** des `content/` Verzeichnisses
- **PHP Error-Logs überwachen**
- **Updates** von PHP und Server-Software

## 🚨 Häufige Probleme

### "500 Internal Server Error"
- PHP Error-Log prüfen
- mod_rewrite aktiviert?
- Dateiberechtigungen korrekt?

### Admin-Login funktioniert nicht
- Passwort-Hash korrekt generiert?
- Session-Ordner beschreibbar?
- HTTPS für Cookies?

### CSS/JS lädt nicht
- CDN-Links erreichbar?
- Content Security Policy?
- Firewall-Einstellungen?

---

**Viel Erfolg beim Deployment! 🚀**