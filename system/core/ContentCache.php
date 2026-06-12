<?php

namespace StaticMD\Core;

/**
 * Datei-basierter Content-Cache.
 * Cache-Key: md5(Dateipfad), Invalidierung per filemtime.
 * Seiten mit [authstart]-Blöcken werden nicht gecacht,
 * da deren Ausgabe vom Login-Status abhängt.
 */
class ContentCache
{
    private string $cacheDir;

    public function __construct(string $storageDir)
    {
        $this->cacheDir = rtrim($storageDir, '/') . '/cache';
    }

    public function get(string $filePath): ?array
    {
        $cacheFile = $this->cacheFilePath($filePath);
        if (!is_file($cacheFile)) {
            return null;
        }

        $payload = @unserialize(file_get_contents($cacheFile));
        if (!is_array($payload) || !isset($payload['mtime'], $payload['data'])) {
            return null;
        }

        if ($payload['mtime'] !== filemtime($filePath)) {
            @unlink($cacheFile);
            return null;
        }

        return $payload['data'];
    }

    public function set(string $filePath, array $data): void
    {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }

        $payload = ['mtime' => filemtime($filePath), 'data' => $data];
        file_put_contents($this->cacheFilePath($filePath), serialize($payload), LOCK_EX);
    }

    private function cacheFilePath(string $filePath): string
    {
        return $this->cacheDir . '/' . md5($filePath) . '.cache';
    }
}
