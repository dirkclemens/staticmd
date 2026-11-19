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

        // Block-Ebene: Container (:::), Blockquotes und GitHub-Alerts, dazwischen normales Markdown
        $html = $this->renderBlocks(explode("\n", $markdown), 0);

        // LaTeX-Platzhalter wieder einsetzen
        if (!empty($this->latexBlocks)) {
            $html = str_replace(array_keys($this->latexBlocks), array_values($this->latexBlocks), $html);
        }

        return trim($html);
    }

    // ------------------------------------------------------------------
    // Block-Ebene: Container (:::) und Blockquotes / Alerts
    // ------------------------------------------------------------------

    private const MAX_BLOCK_DEPTH = 20;

    /** GitHub-Alert-Typen (Basistypen) mit Bootstrap-Icon */
    private const ALERT_TYPES = [
        'note'      => 'bi-info-circle',
        'tip'       => 'bi-lightbulb',
        'important' => 'bi-chat-square-text',
        'warning'   => 'bi-exclamation-triangle',
        'caution'   => 'bi-exclamation-octagon',
    ];

    /** Aliase (Obsidian, Docusaurus, VitePress, Quarto) → Basistyp */
    private const ALERT_ALIASES = [
        'info' => 'note', 'abstract' => 'note', 'summary' => 'note', 'tldr' => 'note',
        'todo' => 'note', 'example' => 'note', 'quote' => 'note', 'cite' => 'note',
        'hint' => 'tip', 'success' => 'tip', 'check' => 'tip', 'done' => 'tip',
        'question' => 'tip', 'help' => 'tip', 'faq' => 'tip',
        'attention' => 'warning',
        'danger' => 'caution', 'error' => 'caution', 'failure' => 'caution',
        'fail' => 'caution', 'missing' => 'caution', 'bug' => 'caution',
    ];

    /**
     * Zerlegt Zeilen in Container, Blockquotes und normale Abschnitte und rendert sie.
     * Container- und Quote-Inhalte werden rekursiv gerendert (beliebig verschachtelbar).
     */
    private function renderBlocks(array $lines, int $depth): string
    {
        $html = '';
        $segment = [];
        $inCode = false;
        $count = count($lines);

        for ($i = 0; $i < $count; $i++) {
            $line = $lines[$i];

            if (str_starts_with($line, '```')) {
                $inCode = !$inCode;
                $segment[] = $line;
                continue;
            }
            if ($inCode || $depth >= self::MAX_BLOCK_DEPTH) {
                $segment[] = $line;
                continue;
            }

            // ::: Container öffnen
            $info = $this->matchContainerOpen($line);
            if ($info !== null) {
                $html .= $this->parseLines($segment);
                $segment = [];
                $end = $this->findContainerEnd($lines, $i + 1);
                $inner = array_slice($lines, $i + 1, $end - $i - 1);
                $html .= $this->renderContainer($info, $inner, $depth + 1);
                $i = $end;
                continue;
            }

            // Blockquote / Alert: aufeinanderfolgende Zeilen mit > sammeln
            if (preg_match('/^ {0,3}>/', $line)) {
                $html .= $this->parseLines($segment);
                $segment = [];
                $quote = [];
                while ($i < $count && preg_match('/^ {0,3}>/', $lines[$i])) {
                    $quote[] = preg_replace('/^ {0,3}> ?/', '', rtrim($lines[$i], "\r"));
                    $i++;
                }
                $i--;
                $html .= $this->renderQuote($quote, $depth + 1);
                continue;
            }

            $segment[] = $line;
        }

        return $html . $this->parseLines($segment);
    }

    /**
     * Öffnende Container-Zeile (::: info) → Info-String, sonst null.
     * Eine Zeile nur aus Doppelpunkten ist eine schließende Zeile.
     */
    private function matchContainerOpen(string $line): ?string
    {
        $line = rtrim($line, "\r");
        if (preg_match('/^ {0,3}:{3,}\s*(.*?)\s*:*\s*$/u', $line, $m) && $m[1] !== '') {
            return $m[1];
        }
        // Legacy-Shortcode [accordionstart id "Titel"] / [spoilerstart …] → ::: details
        if (preg_match('/^ {0,3}\[(?:accordion|spoiler)start\s+([A-Za-z0-9_-]+)\s+"([^"]+)"\]\s*$/i', $line, $m)) {
            return 'details {#accordion-' . $m[1] . ' title="' . $m[2] . '"}';
        }
        return null;
    }

    private function isContainerClose(string $line): bool
    {
        $line = rtrim($line, "\r");
        return (bool) preg_match('/^ {0,3}:{3,}\s*$/', $line)
            || (bool) preg_match('/^ {0,3}\[(?:accordion|spoiler)stop(?:\s+[^\]]*)?\]\s*$/i', $line);
    }

    /**
     * Index der passenden schließenden ::: Zeile (Verschachtelung und Code-Blöcke berücksichtigt).
     * Fehlt sie, endet der Container am Ende des Abschnitts.
     */
    private function findContainerEnd(array $lines, int $start): int
    {
        $level = 1;
        $inCode = false;
        $count = count($lines);
        for ($j = $start; $j < $count; $j++) {
            if (str_starts_with($lines[$j], '```')) {
                $inCode = !$inCode;
                continue;
            }
            if ($inCode) {
                continue;
            }
            if ($this->matchContainerOpen($lines[$j]) !== null) {
                $level++;
            } elseif ($this->isContainerClose($lines[$j]) && --$level === 0) {
                return $j;
            }
        }
        return $count;
    }

    /**
     * Zerlegt den Info-String eines Containers.
     * Unterstützt: "warning", "tip Eigener Titel", "{.columns gap=2rem}", "{#id .a .b key="v w"}",
     * "details Mehr anzeigen {open}" und Quarto "{.callout-note title="..."}".
     *
     * @return array{name:string,rawName:string,title:string,id:string,classes:array,attrs:array}
     */
    private function parseContainerInfo(string $info): array
    {
        $result = ['name' => '', 'rawName' => '', 'title' => '', 'id' => '', 'classes' => [], 'attrs' => []];

        // {…}-Attributblock herauslösen (darf vor oder nach Name/Titel stehen)
        if (preg_match('/\{([^}]*)\}/u', $info, $m)) {
            $info = trim(str_replace($m[0], ' ', $info));
            preg_match_all('/([#.][\w-]+)|([\w-]+)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|(\S+))|([\w-]+)/u', $m[1], $tokens, PREG_SET_ORDER);
            foreach ($tokens as $t) {
                if (!empty($t[1])) {
                    if ($t[1][0] === '#') {
                        $result['id'] = substr($t[1], 1);
                    } else {
                        $result['classes'][] = substr($t[1], 1);
                    }
                } elseif (!empty($t[2])) {
                    $value = $t[3] !== '' ? $t[3] : (($t[4] ?? '') !== '' ? $t[4] : ($t[5] ?? ''));
                    $result['attrs'][strtolower($t[2])] = $value;
                } elseif (!empty($t[6])) {
                    // Attribut ohne Wert: {open}, {border}
                    $result['attrs'][strtolower($t[6])] = 'true';
                }
            }
        }

        // "name Titel…" (Docusaurus / VitePress / Pandoc-Kurzform)
        if ($info !== '' && preg_match('/^([\w-]+)(?:\s+(.*))?$/u', $info, $m)) {
            $result['rawName'] = $m[1];
            $result['name'] = strtolower($m[1]);
            $result['title'] = trim($m[2] ?? '');
        } elseif ($info !== '') {
            $result['title'] = $info;
        }

        if ($result['name'] === '' && !empty($result['classes'])) {
            $result['rawName'] = array_shift($result['classes']);
            $result['name'] = strtolower($result['rawName']);
        }
        if (isset($result['attrs']['title'])) {
            $result['title'] = $result['attrs']['title'];
            unset($result['attrs']['title']);
        }

        return $result;
    }

    private function renderContainer(string $infoString, array $innerLines, int $depth): string
    {
        $info = $this->parseContainerInfo($infoString);
        $name = $info['name'];
        $inner = $this->renderBlocks($innerLines, $depth);

        // Quarto: callout-note, callout-warning, …
        $alertName = str_starts_with($name, 'callout-') ? substr($name, 8) : $name;
        $alertType = $this->resolveAlertType($alertName);
        if ($alertType !== null) {
            $collapse = strtolower($info['attrs']['collapse'] ?? '');
            unset($info['attrs']['collapse']);
            $fold = $collapse === 'true' ? '-' : ($collapse === 'false' ? '+' : '');
            return $this->renderAlert($alertType, $alertName, $info['title'], $inner, $fold, $info);
        }

        if ($name === 'details' || $name === 'spoiler') {
            $open = $this->isTruthy($info['attrs']['open'] ?? '');
            unset($info['attrs']['open']);
            $summary = $info['title'] !== '' ? $info['title'] : $this->translate('core.blocks.details', 'Details');
            return '<details' . $this->buildAttributes($info, ['md-details']) . ($open ? ' open' : '') . '>'
                . '<summary>' . $this->parseInline($summary) . '</summary>'
                . "\n<div class=\"md-details-body\">\n" . $inner . "</div></details>\n";
        }

        if ($name === 'columns') {
            return '<div' . $this->buildAttributes($info, ['md-columns'], 'columns') . ">\n" . $inner . "</div>\n";
        }

        if ($name === 'column') {
            return '<div' . $this->buildAttributes($info, ['md-column'], 'column') . ">\n" . $inner . "</div>\n";
        }

        // Generischer Container: Name wird zur CSS-Klasse (Pandoc-Verhalten), Titel optional
        $classes = ['md-container'];
        if ($info['rawName'] !== '' && preg_match('/^[A-Za-z][\w-]*$/', $info['rawName'])) {
            $classes[] = $info['rawName'];
        }
        $titleHtml = $info['title'] !== ''
            ? '<p class="md-container-title">' . $this->parseInline($info['title']) . "</p>\n"
            : '';
        return '<div' . $this->buildAttributes($info, $classes) . ">\n" . $titleHtml . $inner . "</div>\n";
    }

    /**
     * Blockquote rendern; "[!TYPE]" in der ersten Zeile macht daraus einen Alert (GitHub/Obsidian).
     */
    private function renderQuote(array $lines, int $depth): string
    {
        if (!empty($lines) && preg_match('/^\s*\[!([A-Za-z]+)\]([+-]?)\s*(.*)$/u', $lines[0], $m)) {
            $alertName = strtolower($m[1]);
            $alertType = $this->resolveAlertType($alertName);
            if ($alertType !== null) {
                $inner = $this->renderBlocks(array_slice($lines, 1), $depth);
                return $this->renderAlert($alertType, $alertName, trim($m[3]), $inner, $m[2], null);
            }
        }

        return "<blockquote>\n" . $this->renderBlocks($lines, $depth) . "</blockquote>\n";
    }

    private function resolveAlertType(string $name): ?string
    {
        if (isset(self::ALERT_TYPES[$name])) {
            return $name;
        }
        return self::ALERT_ALIASES[$name] ?? null;
    }

    /**
     * Alert im GitHub-Markup (markdown-alert markdown-alert-TYPE).
     * $fold: '' = normal, '+' = aufklappbar (offen), '-' = aufklappbar (zu)
     */
    private function renderAlert(string $type, string $name, string $title, string $inner, string $fold, ?array $info): string
    {
        if ($title === '') {
            $title = $this->translate('core.alerts.' . $name, ucfirst($name));
        }
        $icon = '<i class="bi ' . self::ALERT_TYPES[$type] . '" aria-hidden="true"></i>';
        $titleHtml = $icon . ' ' . $this->parseInline($title);
        $info = $info ?? ['id' => '', 'classes' => [], 'attrs' => []];
        $baseClasses = ['markdown-alert', 'markdown-alert-' . $type];

        if ($fold !== '') {
            return '<details' . $this->buildAttributes($info, $baseClasses) . ($fold === '+' ? ' open' : '') . '>'
                . '<summary class="markdown-alert-title">' . $titleHtml . '</summary>'
                . "\n<div class=\"markdown-alert-body\">\n" . $inner . "</div></details>\n";
        }

        return '<div' . $this->buildAttributes($info, $baseClasses) . ' role="note">'
            . '<p class="markdown-alert-title">' . $titleHtml . "</p>\n"
            . $inner . "</div>\n";
    }

    /**
     * Baut id/class/style/data-* Attribute aus geparsten Container-Infos.
     * Bekannte Schlüssel werden in CSS übersetzt, alle Werte werden validiert und escaped.
     */
    private function buildAttributes(array $info, array $baseClasses, string $role = ''): string
    {
        $classes = $baseClasses;
        foreach ($info['classes'] as $class) {
            if (preg_match('/^[A-Za-z_][\w-]*$/', $class)) {
                $classes[] = $class;
            }
        }

        $styles = [];
        $data = [];
        foreach ($info['attrs'] as $key => $value) {
            switch ($key) {
                case 'align':
                    if (in_array($value, ['left', 'center', 'right', 'justify'], true)) {
                        $styles[] = 'text-align: ' . $value;
                    }
                    break;
                case 'valign':
                    $map = ['top' => 'flex-start', 'center' => 'center', 'middle' => 'center', 'bottom' => 'flex-end', 'stretch' => 'stretch'];
                    if (isset($map[$value])) {
                        $styles[] = ($role === 'columns' ? 'align-items: ' : 'align-self: ') . $map[$value];
                    }
                    break;
                case 'width':
                    if ($css = $this->sanitizeCssValue($value)) {
                        $styles[] = $role === 'column' ? 'flex: 0 1 ' . $css : 'width: ' . $css . '; max-width: 100%';
                    }
                    break;
                case 'border':
                case 'frame':
                    $classes[] = 'md-border';
                    if (!$this->isTruthy($value) && ($css = $this->sanitizeCssValue($value))) {
                        $styles[] = 'border: ' . $css;
                    }
                    break;
                case 'gap':
                case 'padding':
                case 'color':
                    if ($css = $this->sanitizeCssValue($value)) {
                        $styles[] = $key . ': ' . $css;
                    }
                    break;
                case 'bg':
                case 'background':
                    if ($css = $this->sanitizeCssValue($value)) {
                        $styles[] = 'background: ' . $css;
                    }
                    break;
                case 'radius':
                    if ($css = $this->sanitizeCssValue($value)) {
                        $styles[] = 'border-radius: ' . $css;
                    }
                    break;
                case 'style':
                    foreach (explode(';', $value) as $declaration) {
                        if (preg_match('/^\s*([a-z-]+)\s*:\s*(.+?)\s*$/i', $declaration, $d) && ($css = $this->sanitizeCssValue($d[2]))) {
                            $styles[] = strtolower($d[1]) . ': ' . $css;
                        }
                    }
                    break;
                default:
                    if (preg_match('/^[a-z][\w-]*$/', $key)) {
                        $data[$key] = $value;
                    }
            }
        }

        $out = '';
        if ($info['id'] !== '' && preg_match('/^[A-Za-z][\w-]*$/', $info['id'])) {
            $out .= ' id="' . htmlspecialchars($info['id']) . '"';
        }
        $out .= ' class="' . htmlspecialchars(implode(' ', array_unique($classes))) . '"';
        if ($styles) {
            $out .= ' style="' . htmlspecialchars(implode('; ', $styles)) . '"';
        }
        foreach ($data as $key => $value) {
            $out .= ' data-' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
        }
        return $out;
    }

    /**
     * Erlaubt nur einfache CSS-Werte (Längen, Farben, Schlüsselwörter, calc/rgb/var) – kein url(), keine Ausdrücke.
     */
    private function sanitizeCssValue(string $value): ?string
    {
        $value = trim($value);
        if ($value === '' || strlen($value) > 100) {
            return null;
        }
        if (!preg_match('/^[#\w\s.,%()+\-\/*]+$/', $value) || preg_match('/url\s*\(|expression|javascript|@import/i', $value)) {
            return null;
        }
        return $value;
    }

    private function isTruthy(string $value): bool
    {
        return in_array(strtolower($value), ['true', '1', 'yes', 'ja', 'on'], true);
    }

    private function translate(string $key, string $fallback): string
    {
        $value = I18n::t($key);
        return $value === $key ? $fallback : $value;
    }

    /**
     * Parst einen Abschnitt ohne Container/Blockquotes (klassische Zeilenverarbeitung)
     */
    private function parseLines(array $lines): string
    {
        $html = '';
        $inCodeBlock = false;
        $inList = false;
        $listType = '';
        $listStack = []; // Stack für verschachtelte Listen
        
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
                    $html .= "</code></pre><button type=\"button\" class=\"copy-code-btn\" title=\"Code kopieren\"><i class=\"bi bi-clipboard\"></i></button></div>\n";
                    $inCodeBlock = false;
                } else {
                    $language = trim(substr($trimmedLine, 3));
                    $html .= '<div class="code-block-wrapper"><pre><code class="language-' . htmlspecialchars($language) . '">';
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
            if ($inList && !$this->isListItem($line)) {
                // Schließe alle offenen Listen mit ihren li-Elementen
                while (!empty($listStack)) {
                    $closingType = array_pop($listStack);
                    $html .= "</li>\n</$closingType>\n";
                }
                $inList = false;
                $listType = '';
            }
            
            // Leere Zeilen oder Zeilen mit nur Leerzeichen
            if (empty($trimmedLine)) {
                if ($inParagraph) {
                    $html .= '<p>' . $this->parseInline($paragraphContent) . "</p>\n";
                    $inParagraph = false;
                    $paragraphContent = '';
                }
                continue;
            }
            
            // Zeilen mit nur Leerzeichen -> <br> im Paragraphen
            if (strlen(trim($line)) === 0 && strlen($line) > 0) {
                if ($inParagraph) {
                    $paragraphContent .= '<br>';
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
            
            // Listen (mit Unterstützung für Verschachtelung)
            if ($this->isListItem($line)) {
                // Paragraph beenden falls aktiv
                if ($inParagraph) {
                    $html .= '<p>' . $this->parseInline($paragraphContent) . "</p>\n";
                    $inParagraph = false;
                    $paragraphContent = '';
                }
                
                // Berechne Einrückungstiefe (Markdown-Standard: 4 Leerzeichen = 1 Ebene)
                $indent = strlen($line) - strlen(ltrim($line));
                $indentLevel = intdiv($indent, 4);
                
                // Bestimme Listentyp
                $trimmedLine = ltrim($line);
                $isOrdered = preg_match('/^\d+\./', $trimmedLine);
                $newListType = $isOrdered ? 'ol' : 'ul';
                
                // Liste starten oder Ebene anpassen
                if (!$inList) {
                    // Erste Liste öffnen
                    $html .= "<$newListType>\n";
                    $listStack[] = $newListType;
                    $inList = true;
                    $listType = $newListType;
                } else {
                    $currentLevel = count($listStack) - 1;
                    
                    if ($indentLevel > $currentLevel) {
                        // Tiefere Ebene - neue Unterliste(n) öffnen
                        for ($j = $currentLevel; $j < $indentLevel; $j++) {
                            $html .= "\n<$newListType>\n";
                            $listStack[] = $newListType;
                        }
                    } elseif ($indentLevel < $currentLevel) {
                        // Höhere Ebene - Listen schließen
                        for ($j = $currentLevel; $j > $indentLevel; $j--) {
                            $closingType = array_pop($listStack);
                            $html .= "</li>\n</$closingType>\n";
                        }
                        // Schließe auch das vorherige <li> auf der Ziel-Ebene
                        $html .= "</li>\n";
                    } else {
                        // Gleiche Ebene - vorheriges <li> schließen
                        $html .= "</li>\n";
                    }
                    
                    // Listentyp-Wechsel auf gleicher Ebene
                    if (!empty($listStack) && end($listStack) !== $newListType) {
                        $oldType = array_pop($listStack);
                        $html .= "</$oldType>\n<$newListType>\n";
                        $listStack[] = $newListType;
                    }
                }
                
                // Listenelement hinzufügen
                $text = preg_replace('/^(\*|\+|-|\d+\.)\s+/', '', $trimmedLine);
                $html .= '<li>' . $this->parseInline($text);
                
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
            // Schließe alle noch offenen Listen mit ihren li-Elementen
            while (!empty($listStack)) {
                $closingType = array_pop($listStack);
                $html .= "</li>\n</$closingType>\n";
            }
        }
        
        // Offene Code-Blöcke schließen
        if ($inCodeBlock) {
            $html .= "</code></pre>\n";
        }
        
        return $html;
    }
    
    /**
     * Parst Inline-Markdown (Fett, Kursiv, Links, etc.)
     */
    private function parseInline(string $text): string
    {
        // WICHTIG: Code-Blöcke ZUERST durch Platzhalter ersetzen, um Formatierungszeichen zu schützen
        $codeBlocks = [];
        $codeIndex = 0;
        $text = preg_replace_callback('/`([^`]+)`/', function($matches) use (&$codeBlocks, &$codeIndex) {
            $placeholder = '___CODE_BLOCK_' . $codeIndex . '___';
            // htmlspecialchars für Code-Inhalt verwenden, um alle Zeichen sicher zu escapen
            $codeBlocks[$placeholder] = '<span class="inline-code-wrapper"><code>' . htmlspecialchars($matches[1]) . '</code><button type="button" class="copy-inline-btn" title="Code kopieren"><i class="bi bi-clipboard"></i></button></span>';
            $codeIndex++;
            return $placeholder;
        }, $text);
        
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
                $imagePath = '/assets.php?asset=images/' . urlencode($filename);
                $html = '<img src="' . htmlspecialchars($imagePath) . '"';
                if ($altText !== '') {
                    $html .= ' alt="' . htmlspecialchars($altText) . '"';
                }
                if (!empty($size)) {
                    $html .= ' style="width: ' . htmlspecialchars($size) . ';"';
                }
                $html .= ' class="img-fluid"'; // Bootstrap responsive class
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

        // Durchgestrichen: ~~Text~~
        $text = preg_replace('/~~(.*?)~~/', '<del>$1</del>', $text);

        // Emojis: :emoji_name: -> 🎉 (jetzt sicher außerhalb von Code-Blöcken)
        $text = preg_replace_callback('/:([a-z_+-]+):/', [$this, 'parseEmojiSafe'], $text);

        // Auto-Links konvertieren (außerhalb von Code-Blöcken)
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
     * @deprecated Diese Methode wird nicht mehr verwendet
     */
    private function parseYellowImage(array $matches): string
    {
        $filename = $matches[1];
        $altText = isset($matches[2]) && $matches[2] !== '' ? $matches[2] : '';
        $size = $matches[3] ?? '';

        $imagePath = '/assets.php?asset=images/' . urlencode($filename);
        $html = '<img src="' . htmlspecialchars($imagePath) . '"';
        if ($altText !== '') {
            $html .= ' alt="' . htmlspecialchars($altText) . '"';
        }
        if (!empty($size)) {
            $html .= ' style="width: ' . htmlspecialchars($size) . ';"';
        }
        $html .= ' class="img-fluid"';
        $html .= '>';
        return $html;
    }
    
    /**
     * Prüft ob eine Zeile ein Listenelement ist
     */
    private function isListItem(string $line): bool
    {
        // Akzeptiere auch eingerückte Listenpunkte (max 20 Leerzeichen für Verschachtelung)
        return preg_match('/^\s{0,20}(\*|\+|-|\d+\.)\s+/', $line);
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
     * Schützt Inline-Code vor Splitting durch Pipe-Zeichen
     */
    private function parseTableRow(string $line): array
    {
        // Führende und nachfolgende Pipes entfernen
        $line = trim($line, ' |');
        
        // 1. Inline-Code-Blöcke temporär durch Platzhalter ersetzen
        $codeBlocks = [];
        $codeIndex = 0;
        $line = preg_replace_callback('/`([^`]+)`/', function($matches) use (&$codeBlocks, &$codeIndex) {
            $placeholder = '___TABLE_CODE_' . $codeIndex . '___';
            $codeBlocks[$placeholder] = $matches[0]; // Inklusive Backticks
            $codeIndex++;
            return $placeholder;
        }, $line);
        
        // 2. In Zellen aufteilen (jetzt sicher vor Code-Blöcken)
        $cells = explode('|', $line);
        
        // 3. Code-Blöcke in jeder Zelle wiederherstellen
        $cells = array_map(function($cell) use ($codeBlocks) {
            $cell = trim($cell);
            // Code-Blöcke wieder einsetzen
            return str_replace(array_keys($codeBlocks), array_values($codeBlocks), $cell);
        }, $cells);
        
        return $cells;
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