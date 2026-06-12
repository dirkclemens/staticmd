<?php
/**
 * PSR-4 Autoloader für den StaticMD\-Namespace.
 * Mappt StaticMD\<Segment>\<Class> → system/<segment>/<Class>.php
 */
$_staticmd_base = __DIR__;

spl_autoload_register(function (string $class) use ($_staticmd_base): void {
    if (!str_starts_with($class, 'StaticMD\\')) {
        return;
    }
    $parts     = explode('\\', substr($class, strlen('StaticMD\\')));
    $className = array_pop($parts);                    // last part = class filename (keep case)
    $dirParts  = array_map('strtolower', $parts);      // directory segments → lowercase
    $file      = $_staticmd_base . '/' . implode('/', $dirParts) . '/' . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});
