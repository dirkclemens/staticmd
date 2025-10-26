<?php
/**
 * Passwort-Hash Generator für StaticMD
 * Führen Sie dieses Script aus, um einen sicheren Hash für config.php zu generieren
 */

echo "=== StaticMD Passwort-Hash Generator ===\n\n";

// Passwort eingeben
echo "Geben Sie Ihr neues Admin-Passwort ein: ";
$handle = fopen("php://stdin", "r");
$password = trim(fgets($handle));
fclose($handle);

if (empty($password)) {
    echo "Fehler: Passwort darf nicht leer sein.\n";
    exit(1);
}

if (strlen($password) < 8) {
    echo "Warnung: Passwort sollte mindestens 8 Zeichen haben.\n";
}

// Hash generieren
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "\n=== Ergebnis ===\n";
echo "Ihr Passwort: " . $password . "\n";
echo "Generated Hash: " . $hash . "\n\n";

echo "Kopieren Sie diesen Hash in Ihre config.php:\n\n";
echo "'admin' => [\n";
echo "    'username' => 'admin',\n";
echo "    'password' => '" . $hash . "',\n";
echo "    'session_timeout' => 3600\n";
echo "],\n\n";

echo "WICHTIG: Löschen Sie diese Datei nach der Verwendung!\n";
?>