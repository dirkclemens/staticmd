---
Title: 12. Uberspace Hosting
Author: System
Tag: hosting, uberspace, deployment
Layout: Standard
---

# Uberspace Hosting Guide

Complete guide for hosting StaticMD on Uberspace - a German shared hosting service for developers.

---

## Overview

Uberspace is a developer-friendly hosting service from Germany that's perfect for StaticMD. This guide walks through the complete installation and configuration.

## Prerequisites

### Uberspace Account
- **Account**: Registration at [uberspace.de](https://uberspace.de)
- **SSH Access**: Enabled by default
- **PHP Version**: PHP 8.4+ (available)
- **Domains**: Free \*.uber.space subdomain included

### Local Preparation
- StaticMD installation on local system
- SSH client (Terminal, PuTTY, etc.)
- SCP/SFTP client for file transfer

---

## Installation

### 1. Establish SSH Connection

```bash
# Connect via SSH
ssh username@your-space.uberspace.de

# Change to working directory
cd ~/html
```

### 2. Configure PHP Version

```bash
# Show available PHP versions
uberspace tools version list php

# Activate PHP 8.4
uberspace tools version use php 8.4

# Check version
php --version
```

### 3. Upload StaticMD

#### Option A: Git Clone (recommended)
```bash
# Clone repository
git clone https://github.com/dirkclemens/staticmd.git
mv staticmd/* .
mv staticmd/.* . 2>/dev/null
rmdir staticmd
```

#### Option B: Upload via SCP
```bash
# Local: Compress files
tar -czf staticmd.tar.gz *

# Upload
scp staticmd.tar.gz username@your-space.uberspace.de:~/html/

# On Uberspace: Extract
ssh username@your-space.uberspace.de
cd ~/html
tar -xzf staticmd.tar.gz
rm staticmd.tar.gz
```

### 4. Set Permissions

```bash
# Make directories writable
chmod 755 content/
chmod 755 system/
chmod 755 public/

# Secure config file
chmod 600 config.php

# Create session directory
mkdir -p ~/tmp/sessions
chmod 700 ~/tmp/sessions
```

---

## Configuration

### 1. Apache Configuration (.htaccess)

Create Uberspace-optimized `.htaccess`:

```apache
# StaticMD - Uberspace Configuration
RewriteEngine On
RewriteBase /

# Verzeichnis-Zugriff verhindern für System-Ordner
RewriteCond %{THE_REQUEST} \s/+system/
RewriteRule ^system/ - [F,L]

RewriteCond %{THE_REQUEST} \s/+content/
RewriteRule ^content/ - [F,L]

# Debug-Dateien temporär erlauben
RewriteCond %{REQUEST_FILENAME} -f
RewriteCond %{REQUEST_URI} debug
RewriteRule ^(.*)$ - [L]

# Assets über PHP-Handler ausliefern (funktioniert zuverlässig)
RewriteRule ^assets/(.+)$ assets.php?asset=$1 [L,QSA]

# robots.txt dynamisch generieren
RewriteRule ^robots\.txt$ robots.php [L]

# Admin-Bereich weiterleiten
RewriteRule ^admin/?$ system/admin/index.php [L]
RewriteRule ^admin/(.+)$ system/admin/index.php?route=$1 [L,QSA]

# Logo und Favicon direkt ausliefern (falls im Root-Verzeichnis)
RewriteCond %{REQUEST_FILENAME} -f
RewriteRule ^(logo\.png|favicon\.ico)$ $1 [L]

# Alle anderen Anfragen an index.php weiterleiten
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?route=$1 [L,QSA]

# Security Headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>

# Disable server signature
ServerSignature Off

# Prevent access to sensitive files
<Files "*.md">
    Order allow,deny
    Deny from all
</Files>

<Files "config.php">
    Order allow,deny
    Deny from all
</Files>

<Files "composer.json">
    Order allow,deny
    Deny from all
</Files>
```

---

## Troubleshooting

### Common Problems

#### 1. 500 Internal Server Error
```bash
# Check error log
tail ~/logs/error_log

# Common causes:
# - Wrong permissions
# - PHP syntax errors
# - Missing dependencies
```

#### 2. Session Problems
```bash
# Check session directory
ls -la ~/tmp/sessions/

# Fix permissions
chmod 700 ~/tmp/sessions
```
