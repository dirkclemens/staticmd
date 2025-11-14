<?php
/**
 * Assets Security Test
 * Testet die Sicherheitsmaßnahmen der assets.php
 */

echo "<!DOCTYPE html>
<html>
<head>
    <title>Assets Security Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test { margin: 10px 0; padding: 10px; border-radius: 5px; }
        .pass { background-color: #d4edda; border: 1px solid #c3e6cb; }
        .fail { background-color: #f8d7da; border: 1px solid #f5c6cb; }
        .warning { background-color: #fff3cd; border: 1px solid #ffeaa7; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔒 Assets Security Test</h1>
    <p>Testet die Sicherheitsmaßnahmen der erweiterten assets.php</p>
";

// Test-Cases definieren
$testCases = [
    // Legitime Requests (sollten funktionieren)
    'legitimate' => [
        'test.css' => 'Legitime CSS-Datei',
        'fonts/test.woff2' => 'Font-Datei in Unterordner',
        'icons/favicon.ico' => 'Icon-Datei',
        'test.js' => 'JavaScript-Datei'
    ],
    
    // Path-Traversal-Versuche (sollten blockiert werden)
    'path_traversal' => [
        '../config.php' => 'Einfacher Path-Traversal',
        '../../system/admin/AdminAuth.php' => 'Mehrfacher Path-Traversal',
        'test/../../../config.php' => 'Versteckter Path-Traversal',
        '%2e%2e%2fconfig.php' => 'URL-kodierter Path-Traversal',
        '%2e%2e/%2e%2e/config.php' => 'Gemischter URL-kodierter Path-Traversal'
    ],
    
    // Unerlaubte Dateitypen (sollten blockiert werden)
    'forbidden_types' => [
        'test.php' => 'PHP-Datei',
        'config.ini' => 'Konfigurationsdatei',
        'test.exe' => 'Ausführbare Datei',
        'test.bat' => 'Batch-Datei',
        'test.sh' => 'Shell-Script'
    ],
    
    // Gefährliche Patterns (sollten blockiert werden)
    'dangerous_patterns' => [
        'php://filter/convert.base64-encode/resource=config.php' => 'PHP-Stream',
        'file://C:/Windows/System32/drivers/etc/hosts' => 'File-URL',
        'http://evil.com/malware.js' => 'HTTP-URL',
        'data:text/html,<script>alert(1)</script>' => 'Data-URL'
    ]
];

// Hilfsfunktion für HTTP-Requests
function testAssetRequest($assetPath, $description) {
    $url = '/assets.php?asset=' . urlencode($assetPath);
    
    // HTTP-Context für Request erstellen
    $context = stream_context_create([
        'http' => [
            'method' => 'HEAD', // Nur Header abrufen
            'timeout' => 5,
            'ignore_errors' => true
        ]
    ]);
    
    // Request senden und Response-Header auswerten
    $response = @file_get_contents($url, false, $context);
    $headers = $http_response_header ?? [];
    
    // Status-Code extrahieren
    $statusCode = 0;
    if (!empty($headers[0])) {
        preg_match('/HTTP\/\d\.\d\s+(\d+)/', $headers[0], $matches);
        $statusCode = intval($matches[1] ?? 0);
    }
    
    return [
        'status_code' => $statusCode,
        'headers' => $headers,
        'success' => in_array($statusCode, [200, 206, 304])
    ];
}

// Tests durchführen
foreach ($testCases as $category => $tests) {
    echo "<h2>" . ucfirst(str_replace('_', ' ', $category)) . "</h2>";
    
    foreach ($tests as $assetPath => $description) {
        $result = testAssetRequest($assetPath, $description);
        $statusCode = $result['status_code'];
        
        // Erwartetes Verhalten bestimmen
        $shouldSucceed = ($category === 'legitimate');
        $actuallySucceeded = $result['success'];
        
        // Test-Ergebnis bewerten
        if ($shouldSucceed && $actuallySucceeded) {
            $class = 'pass';
            $status = '✅ PASS';
        } elseif (!$shouldSucceed && !$actuallySucceeded) {
            $class = 'pass';
            $status = '✅ BLOCKED';
        } elseif ($shouldSucceed && !$actuallySucceeded) {
            $class = 'fail';
            $status = '❌ FALSE NEGATIVE';
        } else {
            $class = 'fail';
            $status = '❌ FALSE POSITIVE';
        }
        
        echo "<div class='test $class'>";
        echo "<strong>$status</strong> - $description<br>";
        echo "<code>$assetPath</code> → HTTP $statusCode<br>";
        
        if (!empty($result['headers'])) {
            echo "<details><summary>Response Headers</summary>";
            echo "<pre>" . htmlspecialchars(implode("\n", $result['headers'])) . "</pre>";
            echo "</details>";
        }
        echo "</div>";
    }
}

echo "
    <h2>🔍 Security Features</h2>
    <div class='test warning'>
        <h3>Implementierte Sicherheitsmaßnahmen:</h3>
        <ul>
            <li>✅ <strong>Path-Traversal-Schutz</strong>: Mehrfache URL-Dekodierung und Pattern-Erkennung</li>
            <li>✅ <strong>Realpath-Validierung</strong>: Finale Überprüfung des tatsächlichen Pfads</li>
            <li>✅ <strong>Dateitype-Whitelist</strong>: Nur erlaubte Extensions und MIME-Types</li>
            <li>✅ <strong>MIME-Type-Validierung</strong>: Detected vs. Expected MIME-Type</li>
            <li>✅ <strong>SVG-Sanitization</strong>: Gefährliche SVG-Inhalte werden blockiert</li>
            <li>✅ <strong>DoS-Schutz</strong>: Dateigröße-Limits und Timeout-Schutz</li>
            <li>✅ <strong>Security Headers</strong>: X-Content-Type-Options, CSP für SVG</li>
            <li>✅ <strong>Performance</strong>: ETag-Caching und Range-Requests</li>
            <li>✅ <strong>Logging</strong>: Sicherheitsverletzungen werden geloggt</li>
        </ul>
    </div>
    
    <h2>📊 Test-Zusammenfassung</h2>
    <div class='test warning'>
        <p><strong>Hinweis:</strong> Diese Tests verwenden HEAD-Requests und können je nach Server-Konfiguration unterschiedliche Ergebnisse liefern.</p>
        <p>Für vollständige Tests sollten die Server-Logs auf Security-Violations überprüft werden:</p>
        <pre>tail -f /var/log/php_errors.log | grep \"Assets Security\"</pre>
    </div>
    
</body>
</html>";
?>