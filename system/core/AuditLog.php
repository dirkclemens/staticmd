<?php

namespace StaticMD\Core;

class AuditLog
{
    private string $logFile;

    public function __construct(string $storageDir)
    {
        $this->logFile = rtrim($storageDir, '/') . '/audit.log';
    }

    public function log(string $action, string $username, array $details = []): void
    {
        $entry = json_encode([
            'ts'      => date('c'),
            'user'    => $username,
            'ip'      => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'action'  => $action,
            'details' => $details,
        ], JSON_UNESCAPED_UNICODE);

        $dir = dirname($this->logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($this->logFile, $entry . "\n", FILE_APPEND | LOCK_EX);
    }

    /**
     * Returns the last $limit entries in reverse-chronological order.
     */
    public function getEntries(int $limit = 500): array
    {
        if (!is_file($this->logFile)) {
            return [];
        }

        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines) {
            return [];
        }

        $entries = [];
        foreach (array_reverse(array_slice($lines, -$limit)) as $line) {
            $decoded = json_decode($line, true);
            if (is_array($decoded)) {
                $entries[] = $decoded;
            }
        }

        return $entries;
    }
}
