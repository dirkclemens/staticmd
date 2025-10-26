<?php

namespace StaticMD\Core;

/**
 * Einfacher Markdown-Parser
 * Implementiert die wichtigsten Markdown-Features
 */
class MarkdownParser
{
    /**
     * Konvertiert Markdown zu HTML
     */
    public function parse(string $markdown): string
    {
        // Zeilen in Array aufteilen
        $lines = explode("\n", $markdown);
        $html = '';
        $inCodeBlock = false;
        $inList = false;
        $listType = '';
        
        $inParagraph = false;
        $paragraphContent = '';
        
        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];
            $trimmedLine = trim($line);
            
            // Code-Blöcke erkennen
            if (str_starts_with($trimmedLine, '```')) {
                // Paragraph beenden falls aktiv
                if ($inParagraph) {
                    $html .= '<p>' . $this->parseInline($paragraphContent) . "</p>\n";
                    $inParagraph = false;
                    $paragraphContent = '';
                }
                
                if ($inCodeBlock) {
                    $html .= "</code></pre>\n";
                    $inCodeBlock = false;
                } else {
                    $language = trim(substr($trimmedLine, 3));
                    $html .= '<pre><code class="language-' . htmlspecialchars($language) . '">';
                    $inCodeBlock = true;
                }
                continue;
            }
            
            // In Code-Block: Zeile unverändert ausgeben
            if ($inCodeBlock) {
                $html .= htmlspecialchars($line) . "\n";
                continue;
            }
            
            // Listen beenden wenn nötig
            if ($inList && !$this->isListItem($trimmedLine)) {
                $html .= "</$listType>\n";
                $inList = false;
                $listType = '';
            }
            
            // Leere Zeilen - beenden Paragraphen
            if (empty($trimmedLine)) {
                if ($inParagraph) {
                    $html .= '<p>' . $this->parseInline($paragraphContent) . "</p>\n";
                    $inParagraph = false;
                    $paragraphContent = '';
                }
                continue;
            }
            
            // Überschriften
            if (str_starts_with($trimmedLine, '#')) {
                // Paragraph beenden falls aktiv
                if ($inParagraph) {
                    $html .= '<p>' . $this->parseInline($paragraphContent) . "</p>\n";
                    $inParagraph = false;
                    $paragraphContent = '';
                }
                
                $level = 0;
                while ($level < strlen($trimmedLine) && $trimmedLine[$level] === '#') {
                    $level++;
                }
                if ($level <= 6) {
                    $text = trim(substr($trimmedLine, $level));
                    
                    // Header-ID Syntax parsen: ## Text {#id}
                    $headerId = '';
                    if (preg_match('/^(.+?)\s*\{\s*#([a-zA-Z0-9_-]+)\s*\}$/', $text, $matches)) {
                        $text = trim($matches[1]);
                        $headerId = ' id="' . htmlspecialchars($matches[2]) . '"';
                    }
                    
                    $html .= "<h$level$headerId>" . $this->parseInline($text) . "</h$level>\n";
                    continue;
                }
            }
            
            // Horizontale Linie
            if (preg_match('/^[-*_]{3,}$/', $trimmedLine)) {
                // Paragraph beenden falls aktiv
                if ($inParagraph) {
                    $html .= '<p>' . $this->parseInline($paragraphContent) . "</p>\n";
                    $inParagraph = false;
                    $paragraphContent = '';
                }
                $html .= "<hr>\n";
                continue;
            }
            
            // Listen
            if ($this->isListItem($trimmedLine)) {
                // Paragraph beenden falls aktiv
                if ($inParagraph) {
                    $html .= '<p>' . $this->parseInline($paragraphContent) . "</p>\n";
                    $inParagraph = false;
                    $paragraphContent = '';
                }
                
                $isOrdered = preg_match('/^\d+\./', $trimmedLine);
                $newListType = $isOrdered ? 'ol' : 'ul';
                
                if (!$inList) {
                    $html .= "<$newListType>\n";
                    $inList = true;
                    $listType = $newListType;
                } elseif ($listType !== $newListType) {
                    $html .= "</$listType>\n<$newListType>\n";
                    $listType = $newListType;
                }
                
                $text = preg_replace('/^(\*|\+|-|\d+\.)\s+/', '', $trimmedLine);
                $html .= '<li>' . $this->parseInline($text) . "</li>\n";
                continue;
            }
            
            // Tabellen erkennen (Zeilen mit | Zeichen)
            // Aber nur wenn es eine echte Tabelle ist (mit Header und Separator)
            if (strpos($trimmedLine, '|') !== false && !empty($trimmedLine)) {
                // Prüfen ob nächste Zeile ein Tabellen-Separator ist
                $isTable = false;
                if (($i + 1) < count($lines)) {
                    $nextLine = trim($lines[$i + 1]);
                    // Tabellen-Separator: Zeile mit |, -, : und Leerzeichen
                    if (preg_match('/^[\s\|:\-]+$/', $nextLine) && strpos($nextLine, '|') !== false) {
                        $isTable = true;
                    }
                }
                
                if ($isTable) {
                    // Paragraph beenden falls aktiv
                    if ($inParagraph) {
                        $html .= '<p>' . $this->parseInline($paragraphContent) . "</p>\n";
                        $inParagraph = false;
                        $paragraphContent = '';
                    }
                    
                    // Tabelle parsen
                    $tableResult = $this->parseTable($lines, $i);
                    $html .= $tableResult['html'];
                    $i = $tableResult['lastIndex'];
                    continue;
                }
                // Sonst als normalen Text behandeln
            }
            
            // Blockquotes
            if (str_starts_with($trimmedLine, '>')) {
                // Paragraph beenden falls aktiv
                if ($inParagraph) {
                    $html .= '<p>' . $this->parseInline($paragraphContent) . "</p>\n";
                    $inParagraph = false;
                    $paragraphContent = '';
                }
                $text = trim(substr($trimmedLine, 1));
                $html .= '<blockquote>' . $this->parseInline($text) . "</blockquote>\n";
                continue;
            }
            
            // Normaler Text - sammeln für Paragraph
            if ($inParagraph) {
                // Zeile mit 3+ Leerzeichen = Hard Break
                if (str_ends_with($line, '   ')) {
                    // Prüfen ob Leerzeichen am Ende sind (mindestens 3)
                    $spaceCount = strlen($line) - strlen(rtrim($line));
                    if ($spaceCount >= 3) {
                        $paragraphContent .= ' ' . $trimmedLine . '<br>';
                    } else {
                        $paragraphContent .= ' ' . $trimmedLine;
                    }
                } else {
                    $paragraphContent .= ' ' . $trimmedLine;
                }
            } else {
                $inParagraph = true;
                if (str_ends_with($line, '   ')) {
                    // Prüfen ob Leerzeichen am Ende sind (mindestens 3)
                    $spaceCount = strlen($line) - strlen(rtrim($line));
                    if ($spaceCount >= 3) {
                        $paragraphContent = $trimmedLine . '<br>';
                    } else {
                        $paragraphContent = $trimmedLine;
                    }
                } else {
                    $paragraphContent = $trimmedLine;
                }
            }
        }
        
        // Finalen Paragraph beenden falls aktiv
        if ($inParagraph) {
            $html .= '<p>' . $this->parseInline($paragraphContent) . "</p>\n";
        }
        
        // Offene Listen schließen
        if ($inList) {
            $html .= "</$listType>\n";
        }
        
        // Offene Code-Blöcke schließen
        if ($inCodeBlock) {
            $html .= "</code></pre>\n";
        }
        
        return trim($html);
    }
    
    /**
     * Parst Inline-Markdown (Fett, Kursiv, Links, etc.)
     */
    private function parseInline(string $text): string
    {
        // Yellow CMS Bilder: [image dateiname.jpg - - 50%]
        $text = preg_replace_callback(
            '/\[image\s+([^\s\]]+)(?:\s+-\s+-\s+(\d+%?))?\]/',
            [$this, 'parseYellowImage'],
            $text
        );
        
        // Links: [Text](URL)
        $text = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $text);
        
        // Bilder: ![Alt](URL)
        $text = preg_replace('/!\[([^\]]*)\]\(([^)]+)\)/', '<img src="$2" alt="$1">', $text);
        
        // Fett: **Text** oder __Text__
        $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
        $text = preg_replace('/__(.*?)__/', '<strong>$1</strong>', $text);
        
        // Kursiv: *Text* oder _Text_
        $text = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $text);
        $text = preg_replace('/_(.*?)_/', '<em>$1</em>', $text);
        
        // Code: `Code`
        $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
        
        // Durchgestrichen: ~~Text~~
        $text = preg_replace('/~~(.*?)~~/', '<del>$1</del>', $text);
        
        return $text;
    }
    
    /**
     * Konvertiert Yellow CMS Bild-Syntax zu HTML
     */
    private function parseYellowImage(array $matches): string
    {
        $filename = $matches[1];
        $size = $matches[2] ?? '';
        
        // Bildpfad konstruieren
        $imagePath = '/public/images/migration/' . $filename;
        
        // Alt-Text aus Dateiname generieren
        $altText = pathinfo($filename, PATHINFO_FILENAME);
        $altText = ucfirst(str_replace(['-', '_'], ' ', $altText));
        
        // HTML generieren
        $html = '<img src="' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($altText) . '"';
        
        // Größe hinzufügen falls vorhanden
        if (!empty($size)) {
            if (str_ends_with($size, '%')) {
                $html .= ' style="width: ' . htmlspecialchars($size) . ';"';
            } else {
                $html .= ' style="width: ' . htmlspecialchars($size) . 'px;"';
            }
        }
        
        $html .= ' class="img-fluid">';
        
        return $html;
    }
    
    /**
     * Prüft ob eine Zeile ein Listenelement ist
     */
    private function isListItem(string $line): bool
    {
        return preg_match('/^(\*|\+|-|\d+\.)\s+/', trim($line));
    }
    
    /**
     * Parst Markdown-Tabellen
     */
    private function parseTable(array $lines, int $startIndex): array
    {
        $tableLines = [];
        $currentIndex = $startIndex;
        
        // Alle Tabellenzeilen sammeln
        while ($currentIndex < count($lines)) {
            $line = trim($lines[$currentIndex]);
            
            // Leer oder keine Tabellensyntax = Ende der Tabelle
            if (empty($line) || strpos($line, '|') === false) {
                break;
            }
            
            $tableLines[] = $line;
            $currentIndex++;
        }
        
        if (count($tableLines) < 2) {
            // Keine gültige Tabelle
            return [
                'html' => '<p>' . htmlspecialchars($lines[$startIndex]) . "</p>\n",
                'lastIndex' => $startIndex
            ];
        }
        
        $html = '<table class="table table-striped">' . "\n";
        
        // Header-Zeile (erste Zeile)
        $headerCells = $this->parseTableRow($tableLines[0]);
        $html .= "<thead>\n<tr>\n";
        foreach ($headerCells as $cell) {
            $html .= '<th>' . $this->parseInline(trim($cell)) . "</th>\n";
        }
        $html .= "</tr>\n</thead>\n";
        
        // Separator-Zeile überspringen (zweite Zeile mit --- )
        $separatorIndex = 1;
        if (isset($tableLines[1]) && preg_match('/^[\s\|:\-]+$/', $tableLines[1])) {
            $separatorIndex = 2;
        }
        
        // Body-Zeilen
        if (count($tableLines) > $separatorIndex) {
            $html .= "<tbody>\n";
            for ($i = $separatorIndex; $i < count($tableLines); $i++) {
                $bodyCells = $this->parseTableRow($tableLines[$i]);
                $html .= "<tr>\n";
                foreach ($bodyCells as $cell) {
                    $html .= '<td>' . $this->parseInline(trim($cell)) . "</td>\n";
                }
                $html .= "</tr>\n";
            }
            $html .= "</tbody>\n";
        }
        
        $html .= "</table>\n";
        
        return [
            'html' => $html,
            'lastIndex' => $currentIndex - 1
        ];
    }
    
    /**
     * Parst eine Tabellenzeile in Zellen auf
     */
    private function parseTableRow(string $line): array
    {
        // Führende und nachfolgende Pipes entfernen
        $line = trim($line, ' |');
        
        // In Zellen aufteilen
        $cells = explode('|', $line);
        
        // Zellen trimmen
        return array_map('trim', $cells);
    }
}