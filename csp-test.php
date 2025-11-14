<?php
/**
 * CSP Test-Seite
 * Testet die Content-Security-Policy Implementierung
 */

// SecurityHeaders einbinden
require_once __DIR__ . '/system/core/SecurityHeaders.php';
use StaticMD\Core\SecurityHeaders;

// Test verschiedener CSP-Kontexte
$context = $_GET['context'] ?? 'frontend';
SecurityHeaders::setAllSecurityHeaders($context);
$nonce = SecurityHeaders::getNonce();

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSP Test - StaticMD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .test-section { 
            margin: 20px 0; 
            padding: 15px; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
        }
        .pass { border-color: #28a745; background-color: #d4edda; }
        .fail { border-color: #dc3545; background-color: #f8d7da; }
        .warning { border-color: #ffc107; background-color: #fff3cd; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>🔒 CSP Test für StaticMD</h1>
        <p class="lead">Kontext: <strong><?= htmlspecialchars($context) ?></strong></p>
        
        <div class="row">
            <div class="col-md-6">
                <div class="test-section pass">
                    <h3>✅ Erlaubte Ressourcen</h3>
                    <p><strong>Bootstrap CSS (jsdelivr):</strong> Sollte laden</p>
                    <p><strong>Inline-Style mit Nonce:</strong> Sollte funktionieren</p>
                    <p><strong>Self-hosted Assets:</strong> Sollten laden</p>
                </div>
                
                <div class="test-section" id="script-test">
                    <h3>📜 Script-Test</h3>
                    <p id="script-result">Inline-Script wird getestet...</p>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="test-section warning">
                    <h3>⚠️ Blockierte Ressourcen</h3>
                    <p><strong>Inline-Scripts ohne Nonce:</strong> Sollten blockiert werden</p>
                    <p><strong>Externe Domains (außer CDN):</strong> Sollten blockiert werden</p>
                    <p><strong>eval() Ausführung:</strong> Nur im Admin-Kontext erlaubt</p>
                </div>
                
                <div class="test-section" id="eval-test">
                    <h3>🧮 Eval-Test (nur Admin)</h3>
                    <p id="eval-result">Eval wird getestet...</p>
                </div>
            </div>
        </div>
        
        <div class="test-section">
            <h3>🔍 CSP-Header Analyse</h3>
            <pre id="csp-headers" class="bg-light p-3"></pre>
        </div>
        
        <div class="mt-4">
            <a href="?context=frontend" class="btn btn-outline-primary">Frontend-Kontext testen</a>
            <a href="?context=admin" class="btn btn-outline-secondary">Admin-Kontext testen</a>
        </div>
    </div>
    
    <!-- Erlaubtes Script mit Nonce -->
    <script nonce="<?= $nonce ?>">
        console.log('✅ Nonce-Script funktioniert:', '<?= $nonce ?>');
        
        // Script-Test
        document.getElementById('script-result').innerHTML = 
            '<span class="text-success">✅ Inline-Script mit Nonce funktioniert!</span>';
        document.getElementById('script-test').className = 'test-section pass';
        
        // Eval-Test (sollte nur im Admin-Kontext funktionieren)
        try {
            eval('console.log("Eval funktioniert")');
            document.getElementById('eval-result').innerHTML = 
                '<span class="text-success">✅ Eval funktioniert (Admin-Kontext)</span>';
            document.getElementById('eval-test').className = 'test-section pass';
        } catch(e) {
            document.getElementById('eval-result').innerHTML = 
                '<span class="text-warning">⚠️ Eval blockiert (Frontend-Kontext) - ' + e.message + '</span>';
            document.getElementById('eval-test').className = 'test-section warning';
        }
        
        // CSP-Header anzeigen (falls verfügbar)
        const headers = {};
        // Simulierte Header-Anzeige
        headers['Content-Security-Policy'] = `Kontext: <?= $context ?>`;
        headers['X-Frame-Options'] = 'DENY';
        headers['X-Content-Type-Options'] = 'nosniff';
        headers['Referrer-Policy'] = 'strict-origin-when-cross-origin';
        
        document.getElementById('csp-headers').textContent = 
            Object.entries(headers).map(([k,v]) => `${k}: ${v}`).join('\n');
    </script>
    
    <!-- Blockiertes Script ohne Nonce - sollte CSP-Violation auslösen -->
    <script>
        console.error('❌ Dieses Script sollte durch CSP blockiert werden!');
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>