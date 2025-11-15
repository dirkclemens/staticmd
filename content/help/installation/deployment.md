# StaticMD Deployment Guide

## 🛠 Pre-Deployment Setup

### 1. Change Admin Password
Edit `config.php` and change:
```php
'admin' => [
    'username' => 'admin',
    'password' => password_hash('YOUR_SECURE_PASSWORD', PASSWORD_DEFAULT),
    'session_timeout' => 3600
],
```

### 2. Check Server Requirements
- PHP 8.0+ (ideally 8.4+)
- Apache with mod_rewrite OR Nginx
- Write permissions for `content/` and `public/` directory

### 3. Set File Permissions
```bash
chmod 755 content/ system/ public/
chmod 644 content/*.md content/*/*.md
chmod 600 config.php
```

### 4. Disable Debug Mode
In `config.php`:
```php
'system' => [
    'name' => 'StaticMD',
    'debug' => false
],
```

## 🌐 Uberspace Server Configuration
### Download Directory
Static downloads are stored under `/public/downloads/` and served via `/downloads/file.pdf`.

### Uberspace Setup
- Upload files to `/var/www/virtual/USER/html/`
- Setup domain: `uberspace web domain add staticMD.your-domain.com`
- Set permissions: `chmod 755 content/ system/ public/`
- Test: `https://staticMD.your-domain.com/` and `https://staticMD.your-domain.com/admin`

### Nginx Example Configuration
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

## 📦 Upload Methods
- FTP/SFTP Upload
- Git Deployment
- ZIP Upload

## ✅ Test After Deployment
- Frontend: `http://your-domain.com/`
- Admin: `http://your-domain.com/admin`
- Editor: Create new page
- Navigation: Check all links

## 🔒 Security Features (automatically enabled)

### Content Security Policy (CSP)
- ✅ **Automatically enabled** - Protection against XSS attacks
- ✅ **Context-based** - Frontend/Admin-specific policies
- ✅ **CDN Whitelist** - Bootstrap, CodeMirror allowed
- ✅ **Nonce System** - Secure inline scripts

### HTTP Security Headers
- ✅ **X-Frame-Options**: DENY (Clickjacking protection)
- ✅ **X-Content-Type-Options**: nosniff
- ✅ **X-XSS-Protection**: 1; mode=block
- ✅ **Referrer-Policy**: strict-origin-when-cross-origin
- ✅ **HSTS**: Automatically enabled with HTTPS
- ✅ **Permissions-Policy**: Unnecessary browser APIs disabled

### Session Security
- ✅ **Secure Cookies**: HttpOnly, Secure, SameSite=Strict
- ✅ **CSRF Protection**: All admin actions protected
- ✅ **Session Timeout**: Configurable up to 48h
- ✅ **Path Traversal Protection**: URL validation
