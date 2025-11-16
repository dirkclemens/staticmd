<?php

namespace StaticMD\Core;

/**
 * Einfacher Markdown-Parser
 * Implementiert die wichtigsten Markdown-Features
 */
class MarkdownParser
{
    private array $latexBlocks = [];
    
    /**
     * Konvertiert Markdown zu HTML
     */
    public function parse(string $markdown): string
    {
        // Protect LaTeX formulas before Markdown processing
        $markdown = $this->preprocessLaTeX($markdown);
        
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
            $trimmedLine = $line;

            // Recognize code blocks
            if (str_starts_with($trimmedLine, '```')) {
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

            // In code block: output line unchanged
            if ($inCodeBlock) {
                $html .= htmlspecialchars($line) . "\n";
                continue;
            }

            // End lists if necessary
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
            
            // Headings
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
            
            // Hardbreaks: Zeile endet mit 2+ Leerzeichen
            if (preg_match('/[\s]{2,}$/u', $trimmedLine)) {
                // If line is really empty after whitespace, only insert <br>
                if (strlen(trim($trimmedLine)) === 0) {
                    $html .= "<br>\n";
                    continue;
                }
                if ($inParagraph) {
                    $paragraphContent .= rtrim($trimmedLine) . '<br>';
                } else {
                    $inParagraph = true;
                    $paragraphContent = rtrim($trimmedLine) . '<br>';
                }
                continue;
            }
            
            // Horizontale Linie: Zeile mit 3+ Bindestrichen
            if (preg_match('/^\s*-{3,}\s*$/', $trimmedLine)) {
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
                // Check if next line is a table separator
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
            
            // Normal text - collect for paragraph
            if ($inParagraph) {
                $paragraphContent .= ($paragraphContent === '' ? '' : ' ') . $trimmedLine;
            } else {
                $inParagraph = true;
                $paragraphContent = $trimmedLine;
            }
        }
        
        // Finalen Paragraph beenden falls aktiv
        if ($inParagraph) {
            $html .= '<p>' . $this->parseInline($paragraphContent) . "</p>\n";
        }
        
        // Close open lists
        if ($inList) {
            $html .= "</$listType>\n";
        }
        
        // Offene Code-Blöcke schließen
        if ($inCodeBlock) {
            $html .= "</code></pre>\n";
        }
        
        // LaTeX-Platzhalter wieder einsetzen
        if (!empty($this->latexBlocks)) {
            $html = str_replace(array_keys($this->latexBlocks), array_values($this->latexBlocks), $html);
        }
        
        return trim($html);
    }
    
    /**
     * Parst Inline-Markdown (Fett, Kursiv, Links, etc.)
     */
    private function parseInline(string $text): string
    {
        // Download-Tag: [download datei.zip "Alt-Text"] oder [download datei.pdf "Alt-Text"]
        $text = preg_replace_callback(
            '/\[download\s+([^\s"\]]+)\s*(?:"([^"]*)")?\]/',
            function($matches) {
                $filename = $matches[1];
                $altText = isset($matches[2]) && $matches[2] !== '' ? $matches[2] : $filename;
                $downloadPath = '/public/downloads/' . $filename;
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                if ($ext === 'pdf') {
                    $icon = '<i class="bi bi-file-earmark-pdf" style="font-size:1.1em;color:#d63333;vertical-align:middle;margin-right:4px;"></i>';
                } else if ($ext === 'zip') {
                    $icon = '<i class="bi bi-file-earmark-zip" style="font-size:1.1em;color:#0d6efd;vertical-align:middle;margin-right:4px;"></i>';
                } else {
                    $icon = '<i class="bi bi-file-earmark-arrow-down" style="font-size:1.1em;vertical-align:middle;margin-right:4px;"></i>';
                }
                return '<a href="' . htmlspecialchars($downloadPath) . '" download>' . $icon . htmlspecialchars($altText) . '</a>';
            },
            $text
        );
    
        // Yellow CMS Bilder: [image dateiname.jpg - - 50%]
        // [image name.jpg "Alt-Text" - 50%] oder [image name.jpg - - 50%]
        $text = preg_replace_callback(
            '/\[image\s+([^\s"\]]+)(?:\s+(?:"([^\"]*)"|-|([0-9]+%?)))?(?:\s+-\s*([0-9]+%?))?\]/',
            function($matches) {
                // [image name.jpg "Alt-Text" - 50%] => $matches[1]=name.jpg, $matches[2]=Alt-Text, $matches[4]=Größe
                // [image name.jpg - - 50%] => $matches[1]=name.jpg, $matches[2]=null, $matches[4]=Größe
                // [image name.jpg 50%] => $matches[1]=name.jpg, $matches[3]=Größe
                // [image name.jpg] => $matches[1]=name.jpg
                $filename = $matches[1];
                $altText = isset($matches[2]) && $matches[2] !== '' ? $matches[2] : '';
                $size = '';
                if (isset($matches[4]) && $matches[4] !== '') {
                    $size = $matches[4];
                } elseif (isset($matches[3]) && $matches[3] !== '') {
                    $size = $matches[3];
                }
                $imagePath = '/public/images/' . $filename; // Physisch 
                $html = '<img src="' . htmlspecialchars($imagePath) . '"';
                if ($altText !== '') {
                    $html .= ' alt="' . htmlspecialchars($altText) . '"';
                }
                if (!empty($size)) {
                    $html .= ' style="width: ' . htmlspecialchars($size) . ';"';
                }
                $html .= '>';
                return $html;
            },
            $text
        );
        
        // Bilder: ![Alt](URL)
        $text = preg_replace('/!\[([^\]]*)\]\(([^)]+)\)/', '<img src="$2" alt="$1">', $text);

        // Fett: **Text** 
        $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
        // Fett: oder __Text__ (hier nicht aktiviert, um Konflikte mit Unterstrichen in Wörtern zu vermeiden)
        //$text = preg_replace('/__(.*?)__/', '<strong>$1</strong>', $text);

        // Kursiv: *Text*
        $text = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $text);
        // Kursiv: oder _Text_ (hier nicht aktiviert, um Konflikte mit Unterstrichen in Wörtern zu vermeiden)
        //$text = preg_replace('/_(.*?)_/', '<em>$1</em>', $text);

        // Code-Blöcke temporär durch Platzhalter ersetzen
        $codeBlocks = [];
        $codeIndex = 0;
        $text = preg_replace_callback('/`([^`]+)`/', function($matches) use (&$codeBlocks, &$codeIndex) {
            $placeholder = '___CODE_BLOCK_' . $codeIndex . '___';
            $codeBlocks[$placeholder] = '<code>' . $matches[1] . '</code>';
            $codeIndex++;
            return $placeholder;
        }, $text);

        // Durchgestrichen: ~~Text~~
        $text = preg_replace('/~~(.*?)~~/', '<del>$1</del>', $text);

        // Emojis: :emoji_name: -> 🎉 (jetzt sicher außerhalb von Code-Blöcken)
        $text = preg_replace_callback('/:([a-z_+-]+):/', [$this, 'parseEmojiSafe'], $text);

        // Auto-Links: URLs automatisch zu klickbaren Links konvertieren (außerhalb von Code-Blöcken)
        $text = $this->parseAutoLinks($text, $codeBlocks);

        // Links: [Text](URL) - jetzt auch für automatisch generierte Links
        $text = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $text);
    
        // Code-Blöcke wieder einsetzen
        $text = str_replace(array_keys($codeBlocks), array_values($codeBlocks), $text);

        // Debug-Ausgabe des aktuellen Textes
        //error_log("parseInline: " . $text);

        return $text;
    }
    
    /**
     * Konvertiert Yellow CMS Bild-Syntax zu HTML
     * [image name.jpg "Alt-Text" - 50%]
     * [image name.jpg - - 50%]
     * [image name.jpg 50%]
     * [image name.jpg]
     */
    private function parseYellowImage(array $matches): string
    {
        $filename = $matches[1];
        $altText = isset($matches[2]) && $matches[2] !== '' ? $matches[2] : '';
        $size = $matches[3] ?? '';

        $imagePath = '/public/images/migration/' . $filename;
        $html = '<img src="' . htmlspecialchars($imagePath) . '"';
        if ($altText !== '') {
            $html .= ' alt="' . htmlspecialchars($altText) . '"';
        }
        if (!empty($size)) {
            $html .= ' style="width: ' . htmlspecialchars($size) . ';"';
        }
        $html .= '>';
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
    
    /**
     * Konvertiert Emoji-Codes zu Unicode-Emojis (sichere Version)
     */
    private function parseEmojiSafe(array $matches): string
    {
        $emojiCode = $matches[1];
        
        // Emoji-Mapping (die wichtigsten ~150 Emojis)
        // Quelle: https://gist.github.com/rxaviers/7360908
        $emojiMap = [
            // Gesichter & Menschen
            'bowtie' => '🎀', 'smile' => '😄', 'laughing' => '😆', 'satisfied' => '😆', 'grinning' => '😀', 'grin' => '😁', 'joy' => '😂', 'smiley' => '😃',
            'blush' => '😊', 'relaxed' => '☺️', 'wink' => '😉', 'heart_eyes' => '😍',
            'kissing_heart' => '😘', 'kissing' => '😗', 'kissing_closed_eyes' => '😚', 'kissing_smiling_eyes' => '😙', 'stuck_out_tongue' => '😛',
            'stuck_out_tongue_closed_eyes' => '😝', 'stuck_out_tongue_winking_eye' => '😜', 'sunglasses' => '😎', 'smirk' => '😏',
            'unamused' => '😒', 'flushed' => '😳', 'relieved' => '😌', 'sweat_smile' => '😅', 'pensive' => '😔', 'confused' => '😕',
            'disappointed' => '😞', 'worried' => '😟', 'frowning' => '😦', 'anguished' => '😧', 'open_mouth' => '😮', 'grimacing' => '😬', 'cry' => '😢', 'sob' => '😭', 'angry' => '😠',
            'rage' => '😡', 'tired_face' => '😫', 'sleeping' => '😴', 'mask' => '😷',
            'innocent' => '😇', 'thumbsup' => '👍', 'thumbsdown' => '👎', '+1' => '👍',
            '-1' => '👎', 'ok_hand' => '👌', 'wave' => '👋', 'clap' => '👏',
            'pray' => '🙏', 'muscle' => '💪', 
            
            // Herzen & Liebe
            'heart' => '❤️', 'blue_heart' => '💙', 'green_heart' => '💚', 'yellow_heart' => '💛',
            'purple_heart' => '💜', 'broken_heart' => '💔', 'heartbeat' => '💓',
            'two_hearts' => '💕', 'sparkling_heart' => '💖', 'cupid' => '💘',
            
            // Aktivitäten & Objekte
            'fire' => '🔥', 'star' => '⭐', 'star2' => '🌟', 'sparkles' => '✨',
            'tada' => '🎉', 'confetti_ball' => '🎊', 'balloon' => '🎈', 'gift' => '🎁',
            'trophy' => '🏆', 'medal' => '🏅', 'crown' => '👑', 'gem' => '💎',
            
            // Technik & Arbeit
            'computer' => '💻', 'phone' => '📱', 'email' => '📧', 'rocket' => '🚀',
            'airplane' => '✈️', 'car' => '🚗', 'bike' => '🚴', 'gear' => '⚙️',
            'wrench' => '🔧', 'hammer' => '🔨', 'bulb' => '💡', 'battery' => '🔋',
            
            // Essen & Trinken
            'coffee' => '☕', 'tea' => '🍵', 'beer' => '🍺', 'wine_glass' => '🍷',
            'pizza' => '🍕', 'hamburger' => '🍔', 'cake' => '🍰', 'cookie' => '🍪',
            'apple' => '🍎', 'banana' => '🍌', 'strawberry' => '🍓', 'watermelon' => '🍉',
            
            // Natur & Tiere
            'cat' => '🐱', 'dog' => '🐶', 'mouse' => '🐭', 'bear' => '🐻',
            'panda_face' => '🐼', 'monkey_face' => '🐵', 'bird' => '🐦', 'penguin' => '🐧',
            'fish' => '🐟', 'octopus' => '🐙', 'butterfly' => '🦋', 'bee' => '🐝',
            'tree' => '🌳', 'palm_tree' => '🌴', 'cactus' => '🌵', 'rose' => '🌹',
            'sunflower' => '🌻', 'tulip' => '🌷', 'cherry_blossom' => '🌸',
            
            // Wetter & Natur
            'sunny' => '☀️', 'cloud' => '☁️', 'rain' => '🌧️', 'snow' => '❄️',
            'lightning' => '⚡', 'rainbow' => '🌈', 'ocean' => '🌊', 'volcano' => '🌋',
            
            // Zahlen & Symbole
            'zero' => '0️⃣', 'one' => '1️⃣', 'two' => '2️⃣', 'three' => '3️⃣', 'four' => '4️⃣',
            'five' => '5️⃣', 'six' => '6️⃣', 'seven' => '7️⃣', 'eight' => '8️⃣', 'nine' => '9️⃣',
            'keycap_ten' => '🔟', '1234' => '🔢', 'hash' => '#️⃣', 'symbols' => '🔣',
            'capital_abcd' => '🔠', 'abcd' => '🔡', 'abc' => '🔤',
            
            // Symbole & Zeichen
            'checkmark' => '✅', 'x' => '❌', 'warning' => '⚠️', 'question' => '❓',
            'exclamation' => '❗', 'heavy_exclamation_mark' => '❗', 'bangbang' => '‼️', 'interrobang' => '⁉️',
            'information_source' => 'ℹ️', 'ok' => '🆗', 'end' => '🔚', 'on' => '🔛', 'soon' => '🔜',
            'new' => '🆕', 'cool' => '🆒', 'free' => '🆓', '100' => '💯',
            'top' => '🔝', 'up' => '🆙', 'ng' => '🆖', 'heavy_dollar_sign' => '💲',
            'copyright' => '©️', 'registered' => '®️', 'tm' => '™️', 'o' => '⭕',
            'heavy_multiplication_x' => '✖️', 'heavy_plus_sign' => '➕', 'heavy_minus_sign' => '➖', 'heavy_division_sign' => '➗',
            'white_flower' => '💮', 'heavy_check_mark' => '✔️', 'ballot_box_with_check' => '☑️',
            'radio_button' => '🔘', 'link' => '🔗', 'curly_loop' => '➰', 'wavy_dash' => '〰️',
            'part_alternation_mark' => '〽️', 'trident' => '🔱',
            
            // Pfeile & Navigation
            'arrow_up' => '⬆️', 'arrow_down' => '⬇️', 'arrow_left' => '⬅️', 'arrow_right' => '➡️',
            'arrow_forward' => '▶️', 'arrow_backward' => '◀️', 'fast_forward' => '⏩',
            'rewind' => '⏪', 'repeat' => '🔁', 'arrows_clockwise' => '🔃',
            'arrow_upper_left' => '↖️', 'arrow_upper_right' => '↗️', 'arrow_lower_left' => '↙️',
            'arrow_lower_right' => '↘️', 'arrow_up_down' => '↕️', 'left_right_arrow' => '↔️',
            'arrow_up_small' => '🔼', 'arrow_down_small' => '🔽', 'arrow_double_up' => '⏫',
            'arrow_double_down' => '⏬', 'arrow_heading_up' => '⤴️', 'arrow_heading_down' => '⤵️',
            'leftwards_arrow_with_hook' => '↩️', 'arrow_right_hook' => '↪️', 'arrows_counterclockwise' => '🔄',
            'twisted_rightwards_arrows' => '🔀', 'repeat_one' => '🔂',
            
            // Aktivitäten & Sport
            'soccer' => '⚽', 'basketball' => '🏀', 'football' => '🏈', 'tennis' => '🎾',
            'golf' => '⛳', 'swimmer' => '🏊', 'runner' => '🏃', 'bicyclist' => '🚴',
            
            // Zeit & Kalender
            'clock1' => '🕐', 'clock130' => '🕜', 'clock2' => '🕑', 'clock230' => '🕝', 'clock3' => '🕒', 'clock330' => '🕞',
            'clock4' => '🕓', 'clock430' => '🕟', 'clock5' => '🕔', 'clock530' => '🕠', 'clock6' => '🕕', 'clock630' => '🕡',
            'clock7' => '🕖', 'clock730' => '🕢', 'clock8' => '🕗', 'clock830' => '🕣', 'clock9' => '🕘', 'clock930' => '🕤',
            'clock10' => '🕙', 'clock1030' => '🕥', 'clock11' => '🕚', 'clock1130' => '🕦', 'clock12' => '🕛', 'clock1230' => '🕧',
            'calendar' => '📅', 'date' => '📆', 'alarm_clock' => '⏰', 'watch' => '⌚',
            
            // Büro & Schule
            'book' => '📖', 'books' => '📚', 'notebook' => '📓', 'pencil' => '✏️',
            'pencil2' => '✏️', 'memo' => '📝', 'clipboard' => '📋', 'scissors' => '✂️',
            'pushpin' => '📌', 'paperclip' => '📎', 'file_folder' => '📁',
            
            // Musik & Unterhaltung
            'musical_note' => '🎵', 'notes' => '🎶', 'headphones' => '🎧',
            'microphone' => '🎤', 'guitar' => '🎸', 'trumpet' => '🎺', 'violin' => '🎻',
            'game_die' => '🎲', 'dart' => '🎯', 'video_game' => '🎮',
            
            // Fahrzeuge & Transport
            'bus' => '🚌', 'taxi' => '🚕', 'truck' => '🚚', 'train' => '🚋',
            'ship' => '🚢', 'boat' => '⛵', 'helicopter' => '🚁',
            
            // Gebäude & Orte
            'house' => '🏠', 'office' => '🏢', 'hospital' => '🏥', 'school' => '🏫',
            'hotel' => '🏨', 'bank' => '🏦', 'church' => '⛪', 'factory' => '🏭',
            
            // Geometrische Formen
            'black_square' => '⬛', 'white_square' => '⬜', 'white_check_mark' => '✅',
            'black_square_button' => '🔲', 'white_square_button' => '🔳', 'black_circle' => '⚫',
            'white_circle' => '⚪', 'red_circle' => '🔴', 'large_blue_circle' => '🔵',
            'large_blue_diamond' => '🔷', 'large_orange_diamond' => '🔶', 'small_blue_diamond' => '🔹',
            'small_orange_diamond' => '🔸', 'small_red_triangle' => '🔺', 'small_red_triangle_down' => '🔻'
        ];
        
        // Emoji zurückgeben oder ursprünglichen Code beibehalten
        return $emojiMap[$emojiCode] ?? ':' . $emojiCode . ':';
    }
    
    /**
     * Konvertiert URLs automatisch zu klickbaren Links (sichere Version)
     * Ignoriert bereits existierende Markdown-Links und Code-Inhalte
     */
    private function parseAutoLinks(string $text, array $codeBlocks = []): string
    {
        // Alle bestehenden Markdown-Links und HTML-Links temporär durch Platzhalter ersetzen
        $existingLinks = [];
        $linkIndex = 0;
        
        // 1. Markdown-Links temporär entfernen: [text](url)
        $text = preg_replace_callback('/\[([^\]]*)\]\(([^)]+)\)/', function($matches) use (&$existingLinks, &$linkIndex) {
            $placeholder = '___EXISTING_LINK_' . $linkIndex . '___';
            $existingLinks[$placeholder] = $matches[0];
            $linkIndex++;
            return $placeholder;
        }, $text);
        
        // 2. HTML-Links temporär entfernen: <a href="...">...</a>
        $text = preg_replace_callback('/<a[^>]*href=["\']([^"\']*)["\'][^>]*>.*?<\/a>/i', function($matches) use (&$existingLinks, &$linkIndex) {
            $placeholder = '___EXISTING_LINK_' . $linkIndex . '___';
            $existingLinks[$placeholder] = $matches[0];
            $linkIndex++;
            return $placeholder;
        }, $text);
        
        // 3. URL-Erkennung für nackte URLs - Zeile für Zeile verarbeiten
        $lines = explode("\n", $text);
        foreach ($lines as &$line) {
            // Prüfen ob Zeile Code-Block-Platzhalter oder bereits verlinkte Inhalte enthält
            $hasCodeBlocks = false;
            $hasExistingLinks = strpos($line, '___EXISTING_LINK_') !== false;
            
            foreach (array_keys($codeBlocks) as $codeBlockPlaceholder) {
                if (strpos($line, $codeBlockPlaceholder) !== false) {
                    $hasCodeBlocks = true;
                    break;
                }
            }
            
            // URLs nur konvertieren wenn keine Code-Blöcke oder bestehende Links vorhanden
            if (!$hasCodeBlocks && !$hasExistingLinks) {
                $line = preg_replace_callback('/(^|[\s\-:])((https?:\/\/|www\.|ftp:\/\/)[^\s<>"\'`]+)/i', function($matches) {
                    $prefix = $matches[1];  // Whitespace oder Zeilenanfang behalten
                    $url = $matches[2];
                    $displayUrl = $url;
                    
                    // Für www-Links https:// hinzufügen
                    if (strpos($url, 'www.') === 0) {
                        $url = 'https://' . $url;
                    }
                    
                    // URL kürzen für Anzeige wenn zu lang (über 60 Zeichen)
                    if (strlen($displayUrl) > 60) {
                        $displayUrl = substr($displayUrl, 0, 57) . '...';
                    }
                    
                    return $prefix . '[' . htmlspecialchars($displayUrl) . '](' . htmlspecialchars($url) . ')';
                }, $line);
            }
        }
        $text = implode("\n", $lines);
        
        // 4. Alle bestehenden Links wieder einsetzen
        $text = str_replace(array_keys($existingLinks), array_values($existingLinks), $text);
        
        return $text;
    }
    
    /**
     * Schützt LaTeX-Formeln vor Markdown-Processing
     */
    private function preprocessLaTeX(string $content): string
    {
        $this->latexBlocks = [];
        $blockIndex = 0;
        
        // Block-Formeln ($$...$$) zuerst verarbeiten
        $content = preg_replace_callback('/\$\$(.*?)\$\$/s', function($matches) use (&$blockIndex) {
            $placeholder = '___LATEX_BLOCK_' . $blockIndex . '___';
            $this->latexBlocks[$placeholder] = '$$' . trim($matches[1]) . '$$';
            $blockIndex++;
            return $placeholder;
        }, $content);
        
        // Inline-Formeln ($...$) verarbeiten - nur wenn nicht in Code-Blöcken
        $content = preg_replace_callback('/(?<!`)\$([^$\n]+)\$(?!`)/', function($matches) use (&$blockIndex) {
            $placeholder = '___LATEX_INLINE_' . $blockIndex . '___';
            $this->latexBlocks[$placeholder] = '$' . trim($matches[1]) . '$';
            $blockIndex++;
            return $placeholder;
        }, $content);
        
        return $content;
    }
}