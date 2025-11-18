---
Title: 10. Installation Guide
Author: System
Tag: installation, hosting
Layout: Standard
---

# StaticMD - Installation Guide

Complete guide for installing and configuring StaticMD on your server.

---

## 🚀 Quick Installation

### System Requirements
- **PHP 8.4+** with extensions: `mbstring`, `intl`
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
use any ftp/sftp or similar tool to upload all files

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

### File Permissions examples
```bash 
chmod 755 /var/www/html/staticMD
chmod -R 644 /var/www/html/staticMD/*
chmod -R 755 /var/www/html/staticMD/content/
chmod -R 755 /var/www/html/staticMD/public/
chmod 644 /var/www/html/staticMD/.htaccess
chmod 644 /var/www/html/staticMD/config.php
```

---

## 🛠 Configuration
### Download Tag and Download Directory
PDF and ZIP files are uploaded via drag&drop to `/public/downloads/` and linked with `[download filename.pdf "Alt-Text"]`. The parser shows the appropriate Bootstrap icon.

### Change Admin Credentials
```php
'admin' => [
    'username' => 'your-username',
    'password' => '$2y$10$...',
    'session_timeout' => 3600
]
```

### Generate Password Hash
simply run `php -f ./generate_password_hash.php` and follow th einstructions


### Site Configuration
```php
'system' => [
    'name' => 'Your Site Name',
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

## 🌍 Produktion environment

### Uberspace.de Installation
```bash
ssh user@server.uberspace.de
uberspace web domain add ihre-domain.de
cd /var/www/virtual/user/ihre-domain.de/
chmod -R 755 content/ public/
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
sudo apt-get install php8.4-intl
sudo yum install php84-intl
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
tar -czf staticmd_backup_$(date +%Y%m%d).tar.gz config.php content/ public/
rsync -av content/ backup/content_$(date +%Y%m%d)/
```

### Deploy Updates
```bash
rsync -av --exclude=content/ --exclude=public/ --exclude=config.php new-version/ production-installation/
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
