---
Title: Security & CSP
Author: System
Tag: security, csp, documentation
---

# Security Features in StaticMD

StaticMD implements comprehensive security measures according to modern web security standards.

## Content Security Policy (CSP)

### What is CSP?
Content Security Policy protects against **Cross-Site Scripting (XSS)** and **code injection attacks** through strict control over allowed resources.

### Implementation in StaticMD

#### Automatic Activation
- ✅ **Frontend**: Automatically activated in all themes
- ✅ **Admin**: Specially configured for CodeMirror editor
- ✅ **Context-based**: Different policies depending on area

#### CSP-Direktiven

**Frontend-Policy:**
```
default-src 'self';
script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com;
style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com;
font-src 'self' https://cdn.jsdelivr.net data:;
img-src 'self' data: blob:;
object-src 'none';
frame-ancestors 'none';
```

**Admin-Policy (additional):**
```
script-src [...] 'unsafe-eval';  // für CodeMirror Syntax-Highlighting
```

### Nonce System
StaticMD uses **nonces** for secure inline scripts:

```php
<!-- Secure inline script -->
<script nonce="<?= $nonce ?>">
    console.log('Secure through nonce');
</script>
```

## Additional Security Features

### 1. HTTP Security Headers

**X-Frame-Options:** `DENY`
- Protection against clickjacking attacks

**X-Content-Type-Options:** `nosniff`  
- Prevents MIME type sniffing

**X-XSS-Protection:** `1; mode=block`
- XSS filter for legacy browsers

**Referrer-Policy:** `strict-origin-when-cross-origin`
- Controlled referrer transmission

**Strict-Transport-Security** (with HTTPS)
- Enforces HTTPS connections

### 2. Input Validation

**Path Traversal Protection:**
- Multiple `../` removal
- Maximum nesting depth (10 levels)
- Allowed character whitelist

**Unicode Normalization:**
- NFD→NFC conversion for German umlauts
- Safe URL decoding

### 3. Session Security

**Secure Cookie Parameters:**
```php
'httponly' => true,
'secure' => true,     // with HTTPS
'samesite' => 'Strict'
```

**Configurable Timeouts:**
- Up to 48 hours session lifetime
- Automatic garbage collection

### 4. CSRF Protection

**Token Generation:**
```php
$token = bin2hex(random_bytes(32));
```

**Timing-Attack Safe Verification:**
```php
hash_equals($_SESSION['csrf_token'], $userToken);
```

### 5. Open Redirect Protection

**Return URL Validation:**
```php
if (!str_starts_with($returnUrl, '/admin')) {
    $returnUrl = '/admin';  // Safe fallback
}
```

## Testing & Debugging

### Browser Tools
1. **Developer Tools** → **Console**: Show CSP violations
2. **Network Tab**: Identify blocked resources
3. **Security Tab**: HTTPS and certificate status

### Debug Information
Session debug available via AdminAuth:
```php
$sessionInfo = $auth->getSessionInfo();
print_r($sessionInfo);
```

## Best Practices

### 1. Development
- **Avoid inline scripts** or use with nonce
- **CDN resources** only from whitelisted domains
- **File uploads** restricted to allowed types

### 2. Deployment
- **Use HTTPS** in production
- **Add security headers** in web server configuration
- **Set up CSP reports** for monitoring (optional)

### 3. Monitoring
- **Monitor browser console** for CSP violations
- **Check server logs** for unusual requests
- **Adjust session timeouts** to usage behavior

## Configuration

### Customize SecurityHeaders Class
```php
// Add custom CSP directives
$basePolicy[] = "worker-src 'self'";
$basePolicy[] = "manifest-src 'self'";
```

### Extend CDN Domains
```php
"script-src 'self' 'unsafe-inline' https://your-cdn.com";
```

### Context-Specific Adjustments
```php
if ($context === 'special') {
    $basePolicy[1] = "script-src 'self' 'unsafe-inline'";
}
```

## Compatibility

**Supported Browsers:**
- ✅ Chrome 25+
- ✅ Firefox 23+  
- ✅ Safari 7+
- ✅ Edge (all versions)

**Fallback Behavior:**
- Older browsers ignore CSP headers
- X-XSS-Protection active as fallback
- Basic security through input validation

---

*StaticMD achieves **CSP Level 2** compliance and meets modern web security standards.*