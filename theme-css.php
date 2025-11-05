<?php
// theme-css.php
// Delivers the CSS of a theme from /system/themes/{theme}/template.css

$theme = $_GET['theme'] ?? 'bootstrap';
$theme = preg_replace('/[^a-zA-Z0-9_-]/', '', $theme); // Security
$cssFile = __DIR__ . "/system/themes/$theme/template.css";

if (!file_exists($cssFile)) {
    header('HTTP/1.0 404 Not Found');
    echo "/* Theme CSS not found */";
    exit;
}

header('Content-Type: text/css; charset=UTF-8');
readfile($cssFile);
