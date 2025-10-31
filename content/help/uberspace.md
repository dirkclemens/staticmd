# StaticMD auf Uberspace deployen 🚀

## 📥 Download-Tag und Download-Verzeichnis
PDF- und ZIP-Dateien werden per Drag&Drop nach `/public/downloads/` hochgeladen und mit `[download datei.pdf "Alt-Text"]` verlinkt. Der Parser zeigt das passende Bootstrap-Icon.

## Schritt-für-Schritt Anleitung

### 1. Vorbereitung (lokal)
- Passwort-Hash generieren: `php generate_password_hash.php`
- config.php anpassen: Hash eintragen

### 2. Upload per SFTP
- Verbindung aufbauen: `ssh USER@SPACE.uberspace.de`
- Dateien hochladen nach `/var/www/virtual/USER/staticmd.adcore.de/`
- Rsync-Tipp: Trailing-Slash beachten!

### 3. Domain einrichten
- `uberspace web domain add staticmd.adcore.de`
- DocumentRoot setzen (optional)

### 4. Berechtigungen setzen
```bash
cd /var/www/virtual/USER/staticmd.adcore.de/
chmod 755 content/ system/ public/
chmod 755 system/core/ system/admin/ system/themes/
chmod 755 system/admin/templates/
chmod 755 public/images/ public/downloads/
chmod 644 system/admin/*.php system/core/*.php system/themes/*/*.php
chmod 644 content/*.md content/*/*.md
chmod 644 public/images/*/* 2>/dev/null || true
chmod 600 config.php
chmod 644 .htaccess index.php
```

### 5. Testen
- Frontend: `https://staticmd.adcore.de/`
- Admin: `https://staticmd.adcore.de/admin`
- Login: Zugangsdaten testen

## Troubleshooting
- 500 Internal Server Error: Error-Log prüfen
- Falscher Upload-Pfad: Rsync mit Trailing-Slash verwenden
- Berechtigungsfehler: .htaccess prüfen
- Admin-Login funktioniert nicht: system_check.php ausführen
- CSS/JS lädt nicht: CDN-Verbindung prüfen

## Nächste Schritte
- Erste Seite erstellen
- Inhalte über den Editor bearbeiten
- Navigation testen
- Backup einrichten

## Support
- Uberspace Doku: https://manual.uberspace.de/
- StaticMD Issues
- Error-Logs: `~/logs/error_log`
