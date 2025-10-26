<?php
$pageTitle = 'Editor';
$currentUser = $this->auth->getUsername();
$timeRemaining = $this->auth->getTimeRemaining();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StaticMD Admin - Editor</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- CodeMirror CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/theme/github.min.css">
    
    <style>
        body { background-color: #f8f9fa; }
        .admin-header {
            background: linear-gradient(90deg, #0d6efd, #0a58ca);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .editor-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
        }
        
        .CodeMirror {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .CodeMirror-focused {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        
        .preview-content {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 1rem;
            background: white;
            min-height: 400px;
            max-height: 600px;
            overflow-y: auto;
        }
        
        .meta-form .form-control {
            border-radius: 5px;
        }
        
        .session-timer {
            font-size: 0.85rem;
            color: #ffc107;
        }
        
        .toolbar {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-bottom: none;
            border-radius: 5px 5px 0 0;
            padding: 0.5rem;
        }
        
        .toolbar .btn {
            margin-right: 0.25rem;
            margin-bottom: 0.25rem;
        }
        
        .preview-tab {
            display: none;
        }
        
        .preview-tab.active {
            display: block;
        }
        
        .editor-tab.active {
            display: block;
        }
        
        .tab-content {
            position: relative;
        }
        
        .fullscreen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 9999;
            background: white;
            padding: 1rem;
        }
        
        .fullscreen .CodeMirror {
            height: calc(100vh - 150px);
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <nav class="navbar admin-header navbar-dark">
        <div class="container-fluid">
            <a href="/admin" class="navbar-brand mb-0 h1 text-decoration-none">
                <i class="bi bi-shield-lock me-2"></i>
                StaticMD Admin
            </a>
            
            <div class="d-flex align-items-center text-white">
                <div class="me-3">
                    <small class="session-timer">
                        <i class="bi bi-clock me-1"></i>
                        Session: <span id="timer"><?= gmdate('H:i:s', $timeRemaining) ?></span>
                    </small>
                </div>
                
                <div class="dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>
                        <?= htmlspecialchars($currentUser) ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/admin">
                            <i class="bi bi-speedometer2 me-2"></i>Dashboard
                        </a></li>
                        <li><a class="dropdown-item" href="/">
                            <i class="bi bi-house me-2"></i>Zur Website
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/admin?action=logout">
                            <i class="bi bi-box-arrow-right me-2"></i>Abmelden
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <form method="POST" action="/admin?action=save" id="editorForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($this->auth->generateCSRFToken()) ?>">
            
            <div class="row">
                <!-- Metadaten-Sidebar -->
                <div class="col-lg-3">
                    <div class="card editor-container mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-gear me-2"></i>
                                Seiten-Einstellungen
                            </h5>
                        </div>
                        <div class="card-body meta-form">
                            <div class="mb-3">
                                <label for="file" class="form-label">Datei / Route</label>
                                <input type="text" class="form-control" id="file" name="file" 
                                       value="<?= htmlspecialchars($file) ?>" 
                                       placeholder="z.B. meine-seite oder blog/artikel" required>
                                <div class="form-text">URL der Seite (ohne .md)</div>
                            </div>
                            
                            <!-- Yellow CMS kompatible Felder -->
                            <div class="mb-3">
                                <label for="title" class="form-label">Title / Titel</label>
                                <input type="text" class="form-control" id="title" name="meta[Title]" 
                                       value="<?= htmlspecialchars($meta['Title'] ?? $meta['title'] ?? '') ?>" 
                                       placeholder="Seitentitel">
                                <div class="form-text">Yellow: Title</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="titleslug" class="form-label">TitleSlug</label>
                                <input type="text" class="form-control" id="titleslug" name="meta[TitleSlug]" 
                                       value="<?= htmlspecialchars($meta['TitleSlug'] ?? $meta['titleslug'] ?? '') ?>" 
                                       placeholder="Titel für Dateiname">
                                <div class="form-text">Yellow: TitleSlug</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="layout" class="form-label">Layout</label>
                                <select class="form-select" id="layout" name="meta[Layout]">
                                    <option value="">Standard</option>
                                    <option value="wiki" <?= ($meta['Layout'] ?? $meta['layout'] ?? '') === 'wiki' ? 'selected' : '' ?>>Wiki</option>
                                    <option value="blog" <?= ($meta['Layout'] ?? $meta['layout'] ?? '') === 'blog' ? 'selected' : '' ?>>Blog</option>
                                    <option value="page" <?= ($meta['Layout'] ?? $meta['layout'] ?? '') === 'page' ? 'selected' : '' ?>>Page</option>
                                </select>
                                <div class="form-text">Yellow: Layout</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="author" class="form-label">Author / Autor</label>
                                <input type="text" class="form-control" id="author" name="meta[Author]" 
                                       value="<?= htmlspecialchars($meta['Author'] ?? $meta['author'] ?? '') ?>" 
                                       placeholder="Autor(en), kommagetrennt">
                                <div class="form-text">Yellow: Author</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="tag" class="form-label">Tag / Tags</label>
                                <input type="text" class="form-control" id="tag" name="meta[Tag]" 
                                       value="<?= htmlspecialchars($meta['Tag'] ?? $meta['tags'] ?? '') ?>" 
                                       placeholder="Tags, kommagetrennt">
                                <div class="form-text">Yellow: Tag</div>
                            </div>
                            
                            <hr class="my-3">
                            <small class="text-muted">Zusätzliche Metadaten:</small>
                            
                            <div class="mb-3">
                                <label for="date" class="form-label">Datum</label>
                                <input type="date" class="form-control" id="date" name="meta[date]" 
                                       value="<?= htmlspecialchars($meta['date'] ?? '') ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Beschreibung (SEO)</label>
                                <textarea class="form-control" id="description" name="meta[description]" 
                                          rows="2" placeholder="SEO-Beschreibung"><?= htmlspecialchars($meta['description'] ?? '') ?></textarea>
                            </div>
                            
                            <?php if (!$isNewFile): ?>
                            <div class="mb-3">
                                <a href="/<?= htmlspecialchars($file) ?>" class="btn btn-outline-info btn-sm w-100" target="_blank">
                                    <i class="bi bi-eye me-1"></i> Seite ansehen
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Haupteditor -->
                <div class="col-lg-9">
                    <div class="card editor-container">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-pencil me-2"></i>
                                    <?= $isNewFile ? 'Neue Seite erstellen' : 'Seite bearbeiten' ?>
                                </h5>
                            </div>
                            
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="editorTab">
                                    <i class="bi bi-code me-1"></i> Editor
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="previewTab">
                                    <i class="bi bi-eye me-1"></i> Vorschau
                                </button>
                                <button type="button" class="btn btn-outline-info btn-sm" id="splitTab">
                                    <i class="bi bi-layout-split me-1"></i> Split
                                </button>
                            </div>
                        </div>
                        
                        <!-- Editor Toolbar -->
                        <div class="toolbar">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('**', '**')" title="Fett">
                                <i class="bi bi-type-bold"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('*', '*')" title="Kursiv">
                                <i class="bi bi-type-italic"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('# ', '')" title="Überschrift">
                                <i class="bi bi-type-h1"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('[', '](url)')" title="Link">
                                <i class="bi bi-link"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('- ', '')" title="Liste">
                                <i class="bi bi-list-ul"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('`', '`')" title="Code">
                                <i class="bi bi-code"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('> ', '')" title="Zitat">
                                <i class="bi bi-quote"></i>
                            </button>
                            
                            <div class="float-end">
                                <button type="button" class="btn btn-outline-info btn-sm" id="fullscreenBtn" title="Vollbild">
                                    <i class="bi bi-arrows-fullscreen"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.location.href='/admin'">
                                    <i class="bi bi-x-circle me-1"></i> Abbrechen
                                </button>
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="bi bi-floppy me-1"></i> Speichern
                                </button>
                            </div>
                        </div>
                        
                        <div class="card-body p-0">
                            <div class="row g-0">
                                <!-- Editor -->
                                <div class="col-12" id="editorColumn">
                                    <textarea id="contentEditor" name="content" style="display:none;"><?= htmlspecialchars($content) ?></textarea>
                                </div>
                                
                                <!-- Vorschau -->
                                <div class="col-6 d-none" id="previewColumn">
                                    <div class="preview-content" id="previewContent">
                                        <p class="text-muted">Vorschau wird geladen...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- CodeMirror JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/mode/markdown/markdown.min.js"></script>
    
    <script>
        let editor;
        let currentView = 'editor';
        let timeRemaining = <?= $timeRemaining ?>;
        
        // CodeMirror Editor initialisieren
        document.addEventListener('DOMContentLoaded', function() {
            editor = CodeMirror.fromTextArea(document.getElementById('contentEditor'), {
                mode: 'markdown',
                theme: 'github',
                lineNumbers: true,
                lineWrapping: true,
                autoCloseBrackets: true,
                matchBrackets: true,
                indentUnit: 4,
                tabSize: 4,
                extraKeys: {
                    "Ctrl-S": function() {
                        document.getElementById('editorForm').submit();
                    },
                    "F11": function() {
                        toggleFullscreen();
                    }
                }
            });
            
            // Editor-Höhe setzen
            editor.setSize(null, '500px');
            
            // Auto-Preview bei Änderungen
            editor.on('change', debounce(updatePreview, 500));
            
            // Initial Preview laden
            updatePreview();
        });
        
        // Session-Timer
        function updateTimer() {
            if (timeRemaining <= 0) {
                alert('Ihre Session ist abgelaufen. Sie werden zur Login-Seite weitergeleitet.');
                window.location.href = '/admin?action=login';
                return;
            }
            
            const hours = Math.floor(timeRemaining / 3600);
            const minutes = Math.floor((timeRemaining % 3600) / 60);
            const seconds = timeRemaining % 60;
            
            const timerElement = document.getElementById('timer');
            if (timerElement) {
                timerElement.textContent = 
                    String(hours).padStart(2, '0') + ':' +
                    String(minutes).padStart(2, '0') + ':' +
                    String(seconds).padStart(2, '0');
            }
            
            timeRemaining--;
        }
        setInterval(updateTimer, 1000);
        
        // Tab-Switching
        document.getElementById('editorTab').addEventListener('click', function() {
            switchView('editor');
        });
        
        document.getElementById('previewTab').addEventListener('click', function() {
            switchView('preview');
        });
        
        document.getElementById('splitTab').addEventListener('click', function() {
            switchView('split');
        });
        
        function switchView(view) {
            currentView = view;
            const editorColumn = document.getElementById('editorColumn');
            const previewColumn = document.getElementById('previewColumn');
            
            // Tab-Buttons aktualisieren
            document.querySelectorAll('.btn-group .btn').forEach(btn => {
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-outline-primary');
            });
            
            if (view === 'editor') {
                document.getElementById('editorTab').classList.remove('btn-outline-primary');
                document.getElementById('editorTab').classList.add('btn-primary');
                
                editorColumn.className = 'col-12';
                previewColumn.className = 'col-6 d-none';
                editor.setSize(null, '500px');
            } else if (view === 'preview') {
                document.getElementById('previewTab').classList.remove('btn-outline-secondary');
                document.getElementById('previewTab').classList.add('btn-primary');
                
                editorColumn.className = 'col-12 d-none';
                previewColumn.className = 'col-12';
                updatePreview();
            } else if (view === 'split') {
                document.getElementById('splitTab').classList.remove('btn-outline-info');
                document.getElementById('splitTab').classList.add('btn-primary');
                
                editorColumn.className = 'col-6';
                previewColumn.className = 'col-6';
                editor.setSize(null, '500px');
                updatePreview();
            }
            
            // CodeMirror refresh nach Layout-Änderung
            setTimeout(() => editor.refresh(), 50);
        }
        
        // Markdown zu HTML (einfache Client-side Konvertierung)
        function updatePreview() {
            const content = editor.getValue();
            const html = parseMarkdown(content);
            document.getElementById('previewContent').innerHTML = html;
        }
        
        // Einfacher Markdown-Parser für Vorschau
        function parseMarkdown(text) {
            // Überschriften
            text = text.replace(/^### (.*$)/gim, '<h3>$1</h3>');
            text = text.replace(/^## (.*$)/gim, '<h2>$1</h2>');
            text = text.replace(/^# (.*$)/gim, '<h1>$1</h1>');
            
            // Fett und Kursiv
            text = text.replace(/\*\*(.*)\*\*/gim, '<strong>$1</strong>');
            text = text.replace(/\*(.*)\*/gim, '<em>$1</em>');
            
            // Code
            text = text.replace(/`(.*?)`/gim, '<code>$1</code>');
            
            // Links
            text = text.replace(/\[([^\]]+)\]\(([^)]+)\)/gim, '<a href="$2">$1</a>');
            
            // Zeilenumbrüche zu Paragraphen
            text = text.replace(/\n\n/gim, '</p><p>');
            text = '<p>' + text + '</p>';
            
            // Listen (einfach)
            text = text.replace(/^\- (.*$)/gim, '<li>$1</li>');
            text = text.replace(/(<li>.*<\/li>)/gims, '<ul>$1</ul>');
            
            return text;
        }
        
        // Markdown-Shortcuts einfügen
        function insertMarkdown(prefix, suffix) {
            const doc = editor.getDoc();
            const cursor = doc.getCursor();
            const selection = doc.getSelection();
            
            if (selection) {
                doc.replaceSelection(prefix + selection + suffix);
            } else {
                doc.replaceRange(prefix + suffix, cursor);
                doc.setCursor({line: cursor.line, ch: cursor.ch + prefix.length});
            }
            
            editor.focus();
        }
        
        // Vollbild-Modus
        function toggleFullscreen() {
            const container = document.querySelector('.editor-container');
            
            if (container.classList.contains('fullscreen')) {
                container.classList.remove('fullscreen');
                editor.setSize(null, '500px');
                document.getElementById('fullscreenBtn').innerHTML = '<i class="bi bi-arrows-fullscreen"></i>';
            } else {
                container.classList.add('fullscreen');
                editor.setSize(null, 'calc(100vh - 150px)');
                document.getElementById('fullscreenBtn').innerHTML = '<i class="bi bi-fullscreen-exit"></i>';
            }
            
            setTimeout(() => editor.refresh(), 50);
        }
        
        document.getElementById('fullscreenBtn').addEventListener('click', toggleFullscreen);
        
        // ESC für Vollbild verlassen
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const container = document.querySelector('.editor-container');
                if (container.classList.contains('fullscreen')) {
                    toggleFullscreen();
                }
            }
        });
        
        // Debounce-Funktion
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
        
        // Auto-Save (alle 30 Sekunden)
        setInterval(function() {
            if (editor && editor.getValue().trim()) {
                // Hier könnte ein Auto-Save implementiert werden
                console.log('Auto-save könnte hier implementiert werden');
            }
        }, 30000);
    </script>
</body>
</html>