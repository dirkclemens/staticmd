<?php

namespace StaticMD\Core;

/**
 * Wortindex über alle Markdown-Dateien für die Editor-Autovervollständigung.
 *
 * Liefert Wörter (mind. MIN_LENGTH Zeichen) mit ihrer Häufigkeit, absteigend sortiert.
 * Das Ergebnis wird in storage/cache/word_index.json zwischengespeichert und neu
 * aufgebaut, sobald sich Anzahl oder Änderungszeitpunkt der Inhaltsdateien ändern.
 */
class WordIndex
{
    private const MIN_LENGTH = 5;
    private const MAX_WORDS  = 5000;
    private const MAX_LENGTH = 60;

    private string $contentDir;
    private string $cacheFile;

    public function __construct(string $contentDir, string $storageDir)
    {
        $this->contentDir = rtrim($contentDir, '/');
        $this->cacheFile  = rtrim($storageDir, '/') . '/cache/word_index.json';
    }

    /**
     * @return array<string,int> Wort => Häufigkeit, nach Häufigkeit absteigend
     */
    public function get(): array
    {
        $files = $this->collectFiles();
        $signature = $this->signature($files);

        if (is_file($this->cacheFile)) {
            $cached = json_decode((string) file_get_contents($this->cacheFile), true);
            if (is_array($cached) && ($cached['signature'] ?? null) === $signature && is_array($cached['words'] ?? null)) {
                return $cached['words'];
            }
        }

        $words = $this->build($files);

        $dir = dirname($this->cacheFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        @file_put_contents(
            $this->cacheFile,
            json_encode(['signature' => $signature, 'words' => $words], JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );

        return $words;
    }

    /**
     * @return array<string,int> Pfad => mtime
     */
    private function collectFiles(): array
    {
        $files = [];
        if (!is_dir($this->contentDir)) {
            return $files;
        }
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->contentDir, \FilesystemIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            if ($file->isFile() && strtolower($file->getExtension()) === 'md') {
                $files[$file->getPathname()] = $file->getMTime();
            }
        }
        return $files;
    }

    private function signature(array $files): string
    {
        return count($files) . ':' . ($files ? max($files) : 0) . ':' . md5(implode('|', array_keys($files)));
    }

    /**
     * @param array<string,int> $files
     * @return array<string,int>
     */
    private function build(array $files): array
    {
        $counts = [];

        foreach (array_keys($files) as $path) {
            $text = @file_get_contents($path);
            if ($text === false || $text === '') {
                continue;
            }
            if (class_exists('Normalizer')) {
                $text = \Normalizer::normalize($text, \Normalizer::FORM_C) ?: $text;
            }

            // Rauschen entfernen: URLs, HTML-Tags, Shortcode-/Link-Ziele
            $text = preg_replace('~https?://\S+~u', ' ', $text);
            $text = preg_replace('~<[^>]+>~u', ' ', $text);
            $text = preg_replace('~\]\([^)]*\)~u', '] ', $text);

            if (!preg_match_all('/\p{L}[\p{L}\p{N}_]*/u', $text, $m)) {
                continue;
            }
            foreach ($m[0] as $word) {
                $len = mb_strlen($word);
                if ($len < self::MIN_LENGTH || $len > self::MAX_LENGTH) {
                    continue;
                }
                $counts[$word] = ($counts[$word] ?? 0) + 1;
            }
        }

        arsort($counts, SORT_NUMERIC);
        return array_slice($counts, 0, self::MAX_WORDS, true);
    }
}
