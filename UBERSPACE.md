# StaticMD auf Uberspace deployen 🚀
## 📥 Download-Tag und Download-Verzeichnis
PDF- und ZIP-Dateien werden per Drag&Drop nach `/public/downloads/` hochgeladen und mit `[download datei.pdf "Alt-Text"]` verlinkt. Der Parser zeigt das passende Bootstrap-Icon.

## 📋 Schritt-für-Schritt Anleitung

### 1. Vorbereitung (lokal)

#### Admin-Passwort ändern
```bash
# Passwort-Hash generieren:
php generate_password_hash.php
```

#### config.php anpassen
Kopieren Sie den generierten Hash in `config.php`:
```php
'admin' => [
    'username' => 'admin',
    'password' => 'IHR_GENERIERTER_HASH',
    'session_timeout' => 3600
],
```

### 2. Upload per SFTP

#### Verbindung aufbauen
```bash
# Terminal/SSH:
ssh     @space.uberspace.de

# SFTP (z.B. FileZilla):
# Host: space.uberspace.de
# Port: 22
# Protocol: SFTP
# Username:     
# Password: IHR_PASSWORT
```

#### Dateien hochladen
```bash
# Zielordner auf Uberspace (wird automatisch bei Subdomain-Erstellung angelegt):
/var/www/virtual/USER/staticmd.adcore.de/

# WICHTIG: Korrekt mit rsync (beachte die Trailing-Slashes!):
rsync -avh --progress /source/ /target/staticmd.adcore.de/

# Alle Projektdateien aus lokalem staticMD/ Ordner hochladen:
- index.php
- config.php 
- .htaccess
- system/ (kompletter Ordner)
- content/ (kompletter Ordner)
- public/ (kompletter Ordner mit Bildern)
```

### 3. Domain einrichten

```bash
# SSH auf Uberspace:
ssh USER@space.uberspace.de

# Domain hinzufügen:
uberspace web domain add staticmd.adcore.de

# Optional: DocumentRoot setzen (falls Unterordner):
# uberspace web documentroot set /var/www/virtual/USER/html/staticmd/
```

### 4. Berechtigungen setzen

```bash
# Auf dem Uberspace-Server:
cd /var/www/virtual/USER/staticmd.adcore.de/

# Berechtigungen:
chmod 755 content/ system/ public/
chmod 755 system/core/ system/admin/ system/themes/
chmod 755 system/admin/templates/
chmod 755 public/images/ public/images/migration/ public/images/uploads/ public/downloads/
chmod 644 system/admin/*.php system/core/*.php system/themes/*/*.php
chmod 644 content/*.md content/*/*.md
chmod 644 public/images/*/* 2>/dev/null || true
chmod 600 config.php
chmod 644 .htaccess index.php

# Optional: Logs-Ordner erstellen:
mkdir logs
chmod 755 logs
```

### 5. Testen

#### Website aufrufen
- **Frontend**: `https://staticmd.adcore.de/`
- **Admin**: `https://staticmd.adcore.de/admin`

#### Login testen
- Benutzername: `admin` (oder Ihr gewählter Name)
- Passwort: Ihr neues Passwort

## ✅ Das wars! 🎉

### Warum ist das so einfach?

**Uberspace ist bereits perfekt konfiguriert:**
- ✅ **Apache + mod_rewrite** aktiviert
- ✅ **PHP 8.x** läuft out-of-the-box  
- ✅ **`.htaccess`** wird automatisch verarbeitet
- ✅ **HTTPS** wird automatisch bereitgestellt (Let's Encrypt)
- ✅ **URL-Rewriting** funktioniert ohne Konfiguration

## 🔧 Troubleshooting

### Problem: "500 Internal Server Error"
```bash
# Error-Log prüfen:
tail -f ~/logs/error_log
```

**Lösung für Subdomains:** Fügen Sie `RewriteBase /` in die `.htaccess` ein:
```apache
RewriteEngine On
RewriteBase /
# ... rest der .htaccess
```

### Problem: "Falscher Upload-Pfad" (häufiger Fehler)
**Symptom:** Admin funktioniert nicht, Dateien liegen in `/staticmd.adcore.de/staticMD/`

**Ursache:** Rsync ohne Trailing-Slash kopiert Ordner statt Inhalt
```bash
# FALSCH (erstellt Unterordner):
rsync /pfad/staticMD /ziel/

# RICHTIG (kopiert Inhalt):
rsync /pfad/staticMD/ /ziel/
```

**Lösung:** Dateien eine Ebene nach oben verschieben:
```bash
cd /var/www/virtual/USER/staticmd.adcore.de/
mv staticMD/* .
mv staticMD/.* . 2>/dev/null
rm -rf staticMD/
```

### Problem: "You don't have permission to access this resource"
**Symptom:** Admin-Link zeigt Berechtigungsfehler

**Ursache:** Standard .htaccess blockiert system/ zu stark

**Lösung:** Korrigierte .htaccess verwenden:
```apache
# Nur direkten Zugriff auf system/ blockieren, interne Weiterleitungen erlauben
RewriteCond %{THE_REQUEST} \s/+system/
RewriteRule ^system/ - [F,L]
```

### Problem: Admin-Login funktioniert nicht  
```bash
# System-Check ausführen:
cd /var/www/virtual/USER/staticmd.adcore.de/
php system_check.php
```

### Problem: CSS/JS lädt nicht
- CDN-Verbindung prüfen
- Browser-Cache leeren
- Developer Tools öffnen (F12)

## 🎯 Nächste Schritte

1. **Erste Seite erstellen** über Admin-Interface
2. **Inhalte** über den Editor bearbeiten  
3. **Navigation** testen
4. **Backup** einrichten (regelmäßig `content/` sichern)

## 📞 Support

- **Uberspace Doku**: https://manual.uberspace.de/
- **StaticMD Issues**: Bei Problemen mit dem CMS
- **Error-Logs**: `~/logs/error_log` auf dem Server

---
**Happy Publishing mit StaticMD auf Uberspace! 🚀**