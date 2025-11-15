# StaticMD - Installation Guide

Complete guide for installing and configuring StaticMD on your server.

---

## 🚀 Quick Installation

### System Requirements
- **PHP 8.3+** with extensions: `mbstring`, `intl`
- **Apache web server** with `mod_rewrite` enabled
- **HTTPS certificate** (recommended for security headers)
- **SSH access** (for optimal deployment)

### 1. Install Files
```bash
git clone https://github.com/dirkclemens/staticmd.git staticMD
cd staticMD
# Or download ZIP and extract
unzip staticMD.zip
cd staticMD
```

### 2. Server Upload
```bash
nano upload.sh # Adjust SERVER and REMOTE_PATH
chmod +x upload.sh
./upload.sh
```

### 3. Initial Configuration
```bash
# Change admin password (recommended)
# Adjust bcrypt hash in config.php
```

---

## 📋 Detailed Installation

### Apache VirtualHost Configuration
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/html/staticMD
    <Directory /var/www/html/staticMD>
        AllowOverride All
        Require all granted
    </Directory>
    # Serve download directory statically
    # RewriteCond %{REQUEST_FILENAME} -f
    # RewriteRule ^downloads/(.*)$ public/downloads/$1 [L]
</VirtualHost>
```

### File Permissions
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

## 🐞 Troubleshooting

### 500 Internal Server Error
```bash
# Check causes
# .htaccess syntax error
# mod_rewrite not enabled
# PHP version < 8.3
# Wrong file permissions
# Solutions
tail -f /var/log/apache2/error.log
a2enmod rewrite
systemctl reload apache2
```

### Admin Login Not Working
```bash
ls -la /tmp/
chmod 1777 /tmp
<?php
session_start();
echo session_save_path();
?>
```

### Markdown Not Rendering
```bash
php -m | grep -E '(mbstring|intl)'
chmod -R 755 content/
```

### Umlauts in URLs Not Working
```bash
sudo apt-get install php8.3-intl
sudo yum install php83-intl
```

---

## 📈 Performance Optimization

### Apache Configuration
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

### PHP Optimization
```php
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=4000
memory_limit=256M
```

---

## 🔄 Maintenance & Updates

### Create Backup
```bash
tar -czf staticmd_backup_$(date +%Y%m%d).tar.gz content/ config.php public/media/
rsync -av content/ backup/content_$(date +%Y%m%d)/
```

### Deploy Updates
```bash
rsync -av --exclude=content/ --exclude=config.php new-version/ production-installation/
```

### Log Rotation
```bash
logrotate -f /etc/logrotate.d/apache2
tail -f /var/log/php_errors.log
```

---

### Go-Live Checklist

### Before Launch
- [ ] Admin password changed
- [ ] Site name adjusted in config.php
- [ ] SSL certificate installed
- [ ] Security headers activated (automatic)
- [ ] CSP test performed (`/csp-test.php`)
- [ ] Session timeout configured
- [ ] SEO settings configured
- [ ] robots.txt tested (`/robots.txt`)
- [ ] Backup strategy implemented
- [ ] Performance optimizations activated
- [ ] 404 page customized

### After Launch
- [ ] Set up Google Search Console
- [ ] Implement analytics
- [ ] Set up monitoring
- [ ] Plan maintenance intervals

---

## 📞 Support & Help

### Documentation
- **help/README.md**: Feature overview
- **CHANGELOG.md**: Development history
- **scope_final.md**: Project analysis

### Live Example
- **Demo**: https://staticMD.adcore.de
- **Admin Demo**: Full functionality
