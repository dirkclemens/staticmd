---
Title: Uberspace Hosting
Author: StaticMD Team
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

# Activate PHP 8.3
uberspace tools version use php 8.3

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

## Support

### Uberspace-specific Help
- **Manual**: [manual.uberspace.de](https://manual.uberspace.de)
- **Support**: Ticket system in Uberspace dashboard
- **Community**: [Twitter @ubernauten](https://twitter.com/ubernauten)

### StaticMD Support  
- **GitHub**: Issues and Discussions
- **Documentation**: Complete docs in `/content/help/` directory

---

*StaticMD on Uberspace - Professional German Hosting for Developers*
