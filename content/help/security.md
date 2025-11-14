---
Title: Security & CSP
Author: StaticMD Team
Tag: security, csp, documentation
---

# Security Features in StaticMD

StaticMD implementiert umfassende Sicherheitsmaßnahmen nach modernen Web-Security-Standards.

## Content-Security-Policy (CSP)

### Was ist CSP?
Content-Security-Policy schützt vor **Cross-Site-Scripting (XSS)** und **Code-Injection-Angriffen** durch strenge Kontrolle über erlaubte Ressourcen.

### Implementierung in StaticMD

#### Automatische Aktivierung
- ✅ **Frontend**: Automatisch in allen Themes aktiviert
- ✅ **Admin**: Speziell konfiguriert für CodeMirror-Editor
- ✅ **Kontextbasiert**: Verschiedene Policies je nach Bereich

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

**Admin-Policy (zusätzlich):**
```
script-src [...] 'unsafe-eval';  // für CodeMirror Syntax-Highlighting
```

### Nonce-System
StaticMD verwendet **Nonces** für sichere Inline-Scripts:

```php
<!-- Sicheres Inline-Script -->
<script nonce="<?= $nonce ?>">
    console.log('Sicher durch Nonce');
</script>
```

## Weitere Security Features

### 1. HTTP Security Headers

**X-Frame-Options:** `DENY`
- Schutz vor Clickjacking-Angriffen

**X-Content-Type-Options:** `nosniff`  
- Verhindert MIME-Type-Sniffing

**X-XSS-Protection:** `1; mode=block`
- XSS-Filter für Legacy-Browser

**Referrer-Policy:** `strict-origin-when-cross-origin`
- Kontrollierte Referrer-Übertragung

**Strict-Transport-Security** (bei HTTPS)
- Erzwingt HTTPS-Verbindungen

### 2. Input-Validierung

**Path-Traversal-Schutz:**
- Mehrfache `../` Entfernung
- Maximale Verschachtelungstiefe (10 Ebenen)
- Erlaubte Zeichen-Whitelist

**Unicode-Normalisierung:**
- NFD→NFC Konvertierung für deutsche Umlaute
- Sichere URL-Dekodierung

### 3. Session-Security

**Sichere Cookie-Parameter:**
```php
'httponly' => true,
'secure' => true,     // bei HTTPS
'samesite' => 'Strict'
```

**Konfigurierbare Timeouts:**
- Bis zu 48 Stunden Session-Laufzeit
- Automatische Garbage Collection

### 4. CSRF-Schutz

**Token-Generierung:**
```php
$token = bin2hex(random_bytes(32));
```

**Timing-Attack-sichere Verifikation:**
```php
hash_equals($_SESSION['csrf_token'], $userToken);
```

### 5. Open-Redirect-Schutz

**Return-URL-Validierung:**
```php
if (!str_starts_with($returnUrl, '/admin')) {
    $returnUrl = '/admin';  // Sicherer Fallback
}
```

## Testing & Debugging

### CSP-Test-Seite
StaticMD enthält eine Test-Seite unter `/csp-test.php`:

```
https://your-domain.com/csp-test.php?context=frontend
https://your-domain.com/csp-test.php?context=admin
```

### Browser-Tools
1. **Developer Tools** → **Console**: CSP-Violations anzeigen
2. **Network Tab**: Blockierte Ressourcen identifizieren
3. **Security Tab**: HTTPS und Certificate-Status

### Debug-Informationen
Session-Debug über AdminAuth verfügbar:
```php
$sessionInfo = $auth->getSessionInfo();
print_r($sessionInfo);
```

## Best Practices

### 1. Entwicklung
- **Inline-Scripts vermeiden** oder mit Nonce versehen
- **CDN-Ressourcen** nur von whitelisteten Domains
- **File-Uploads** auf erlaubte Typen beschränken

### 2. Deployment
- **HTTPS** in Produktion verwenden
- **Security Headers** in Webserver-Konfiguration ergänzen
- **CSP-Reports** für Monitoring einrichten (optional)

### 3. Monitoring
- **Browser-Console** auf CSP-Violations überwachen
- **Server-Logs** auf ungewöhnliche Requests prüfen
- **Session-Timeouts** an Nutzungsverhalten anpassen

## Konfiguration

### SecurityHeaders-Klasse anpassen
```php
// Eigene CSP-Direktiven hinzufügen
$basePolicy[] = "worker-src 'self'";
$basePolicy[] = "manifest-src 'self'";
```

### CDN-Domains erweitern
```php
"script-src 'self' 'unsafe-inline' https://ihr-cdn.com";
```

### Kontextspezifische Anpassungen
```php
if ($context === 'special') {
    $basePolicy[1] = "script-src 'self' 'unsafe-inline'";
}
```

## Kompatibilität

**Unterstützte Browser:**
- ✅ Chrome 25+
- ✅ Firefox 23+  
- ✅ Safari 7+
- ✅ Edge (alle Versionen)

**Fallback-Verhalten:**
- Alte Browser ignorieren CSP-Header
- X-XSS-Protection als Fallback aktiv
- Grundsätzliche Sicherheit durch Input-Validierung

---

*StaticMD erreicht **CSP Level 2** Compliance und erfüllt moderne Web-Security-Standards.*