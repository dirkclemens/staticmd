<?php
$pageTitle = 'Einstellungen';
$currentUser = $this->auth->getUsername();
$timeRemaining = $this->auth->getTimeRemaining();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StaticMD Admin - Einstellungen</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body { background-color: #f8f9fa; }
        .admin-header {
            background: linear-gradient(45deg, #AC1200, #940f00ff); 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .settings-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
        }
        
        .settings-section {
            border-bottom: 1px solid #e9ecef;
            padding: 1.5rem 0;
        }
        
        .settings-section:last-child {
            border-bottom: none;
        }
        
        .session-timer {
            font-size: 0.85rem;
            color: #ffc107;
        }
        
        .form-range {
            margin: 0.5rem 0;
        }
        
        .range-value {
            font-weight: bold;
            color: #AC1200;
        }
        
        /* Theme Preview Styles */
        #theme-preview {
            min-height: 80px;
            transition: all 0.3s ease;
        }
        
        /* GitHub Theme */
        .theme-github {
            background-color: #ffffff;
            color: #24292e;
            border-color: #e1e4e8;
        }
        .theme-github .theme-keyword { color: #d73a49; }
        .theme-github .theme-function { color: #6f42c1; }
        .theme-github .theme-string { color: #032f62; }
        .theme-github .theme-comment { color: #6a737d; }
        .theme-github .theme-text { color: #24292e; }
        
        /* Monokai Theme */
        .theme-monokai {
            background-color: #272822;
            color: #f8f8f2;
            border-color: #49483e;
        }
        .theme-monokai .theme-keyword { color: #f92672; }
        .theme-monokai .theme-function { color: #a6e22e; }
        .theme-monokai .theme-string { color: #e6db74; }
        .theme-monokai .theme-comment { color: #75715e; }
        .theme-monokai .theme-text { color: #f8f8f2; }
        
        /* Solarized Light Theme */
        .theme-solarized-light {
            background-color: #fdf6e3;
            color: #657b83;
            border-color: #eee8d5;
        }
        .theme-solarized-light .theme-keyword { color: #859900; }
        .theme-solarized-light .theme-function { color: #268bd2; }
        .theme-solarized-light .theme-string { color: #2aa198; }
        .theme-solarized-light .theme-comment { color: #93a1a1; }
        .theme-solarized-light .theme-text { color: #657b83; }
        
        /* Solarized Dark Theme */
        .theme-solarized-dark {
            background-color: #002b36;
            color: #839496;
            border-color: #073642;
        }
        .theme-solarized-dark .theme-keyword { color: #859900; }
        .theme-solarized-dark .theme-function { color: #268bd2; }
        .theme-solarized-dark .theme-string { color: #2aa198; }
        .theme-solarized-dark .theme-comment { color: #586e75; }
        .theme-solarized-dark .theme-text { color: #839496; }
        
        /* Material Theme */
        .theme-material {
            background-color: #263238;
            color: #eeffff;
            border-color: #37474f;
        }
        .theme-material .theme-keyword { color: #c792ea; }
        .theme-material .theme-function { color: #82aaff; }
        .theme-material .theme-string { color: #c3e88d; }
        .theme-material .theme-comment { color: #546e7a; }
        .theme-material .theme-text { color: #eeffff; }
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
                    <ul class="dropdown-menu" style="right: 0; left: auto;">
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
        <div class="row">
            <div class="col-md-2">
                <!-- Navigation zurück -->
                <div class="mb-3">
                    <a href="/admin" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Zurück zum Dashboard
                    </a>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card settings-container">
                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            <i class="bi bi-gear me-2"></i>
                            System-Einstellungen
                        </h4>
                    </div>
                    
                    <div class="card-body">
                        <!-- Nachrichten -->
                        <?php if (isset($_GET['message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i>
                            <?php
                            switch ($_GET['message']) {
                                case 'settings_saved': echo 'Einstellungen wurden erfolgreich gespeichert.'; break;
                                default: echo 'Aktion wurde erfolgreich ausgeführt.';
                            }
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <?php
                            switch ($_GET['error']) {
                                case 'save_failed': echo 'Fehler beim Speichern der Einstellungen.'; break;
                                case 'csrf_invalid': echo 'Sicherheitstoken ungültig.'; break;
                                default: echo 'Ein Fehler ist aufgetreten.';
                            }
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="/admin?action=save_settings">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($this->auth->generateCSRFToken()) ?>">
                            
                            <!-- Seiten-Einstellungen -->
                            <div class="settings-section">
                                <h5><i class="bi bi-globe me-2"></i>Website-Einstellungen</h5>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="site_name" class="form-label">Website-Name</label>
                                            <input type="text" class="form-control" id="site_name" name="site_name" 
                                                   value="<?= htmlspecialchars($settings['site_name']) ?>" 
                                                   placeholder="StaticMD" required>
                                            <div class="form-text">Wird in der Navigation und im Titel angezeigt</div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="site_logo" class="form-label">Logo-URL</label>
                                            <input type="url" class="form-control" id="site_logo" name="site_logo" 
                                                   value="<?= htmlspecialchars($settings['site_logo']) ?>" 
                                                   placeholder="https://example.com/logo.png">
                                            <div class="form-text">Optional: URL zu einem Logo-Bild</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Dashboard-Einstellungen -->
                            <div class="settings-section">
                                <h5><i class="bi bi-speedometer2 me-2"></i>Dashboard-Einstellungen</h5>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="recent_files_count" class="form-label">
                                                Anzahl zuletzt bearbeiteter Dateien: 
                                                <span class="range-value" id="recent_files_value"><?= $settings['recent_files_count'] ?></span>
                                            </label>
                                            <input type="range" class="form-range" id="recent_files_count" name="recent_files_count" 
                                                   min="5" max="50" value="<?= $settings['recent_files_count'] ?>"
                                                   oninput="document.getElementById('recent_files_value').textContent = this.value">
                                            <div class="form-text">5-50 Dateien im "Zuletzt bearbeitet" Bereich</div>
                                        </div>
                                    </div>
                                    
                                    <!-- deactivated, for later use
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="items_per_page" class="form-label">
                                                Dateien pro Seite: 
                                                <span class="range-value" id="items_per_page_value"><?= $settings['items_per_page'] ?></span>
                                            </label>
                                            <input type="range" class="form-range" id="items_per_page" name="items_per_page" 
                                                   min="10" max="100" step="5" value="<?= $settings['items_per_page'] ?>"
                                                   oninput="document.getElementById('items_per_page_value').textContent = this.value">
                                            <div class="form-text">10-100 Dateien im Datei-Manager</div>
                                        </div>
                                    </div>
                                    -->
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="show_file_stats" name="show_file_stats" 
                                               <?= $settings['show_file_stats'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="show_file_stats">
                                            Datei-Statistiken im Dashboard anzeigen
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Frontend-Theme -->
                            <div class="settings-section">
                                <h5><i class="bi bi-palette me-2"></i>Frontend-Theme</h5>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="frontend_theme" class="form-label">Website-Theme</label>
                                            <select class="form-select" id="frontend_theme" name="frontend_theme">
                                                <option value="bootstrap" <?= ($settings['frontend_theme'] ?? 'bootstrap') === 'bootstrap' ? 'selected' : '' ?>>Bootstrap (Standard)</option>
                                                <option value="solarized-light" <?= ($settings['frontend_theme'] ?? '') === 'solarized-light' ? 'selected' : '' ?>>Solarized Light</option>
                                                <option value="solarized-dark" <?= ($settings['frontend_theme'] ?? '') === 'solarized-dark' ? 'selected' : '' ?>>Solarized Dark</option>
                                                <option value="monokai-light" <?= ($settings['frontend_theme'] ?? '') === 'monokai-light' ? 'selected' : '' ?>>Monokai Light</option>
                                                <option value="monokai-dark" <?= ($settings['frontend_theme'] ?? '') === 'monokai-dark' ? 'selected' : '' ?>>Monokai Dark</option>
                                                <option value="github-light" <?= ($settings['frontend_theme'] ?? '') === 'github-light' ? 'selected' : '' ?>>GitHub Light</option>
                                                <option value="github-dark" <?= ($settings['frontend_theme'] ?? '') === 'github-dark' ? 'selected' : '' ?>>GitHub Dark</option>
                                                <option value="static-md" <?= ($settings['frontend_theme'] ?? '') === 'static-md' ? 'selected' : '' ?>>StaticMD</option>
                                            </select>
                                            <div class="form-text">
                                                Wählen Sie das Aussehen Ihrer Website. Änderungen werden sofort auf der Frontend-Seite sichtbar.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Theme-Vorschau</label>
                                            <div class="border rounded p-3" style="background: linear-gradient(45deg, #f8f9fa 25%, transparent 25%), linear-gradient(-45deg, #f8f9fa 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #f8f9fa 75%), linear-gradient(-45deg, transparent 75%, #f8f9fa 75%); background-size: 20px 20px; background-position: 0 0, 0 10px, 10px -10px, -10px 0px;">
                                                <div class="text-center text-muted">
                                                    <i class="bi bi-eye fs-1"></i><br>
                                                    <small>Theme-Vorschau</small><br>
                                                    <small>Besuchen Sie die Frontend-Seite um das gewählte Theme zu sehen</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Editor-Einstellungen -->
                            <div class="settings-section">
                                <h5><i class="bi bi-pencil me-2"></i>Editor-Einstellungen</h5>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="editor_theme" class="form-label">Editor-Theme</label>
                                            <select class="form-select" id="editor_theme" name="editor_theme" onchange="previewTheme(this.value)">
                                                <option value="github" <?= $settings['editor_theme'] === 'github' ? 'selected' : '' ?>>GitHub (hell)</option>
                                                <option value="monokai" <?= $settings['editor_theme'] === 'monokai' ? 'selected' : '' ?>>Monokai (dunkel)</option>
                                                <option value="solarized-light" <?= $settings['editor_theme'] === 'solarized-light' ? 'selected' : '' ?>>Solarized Light</option>
                                                <option value="solarized-dark" <?= $settings['editor_theme'] === 'solarized-dark' ? 'selected' : '' ?>>Solarized Dark</option>
                                                <option value="material" <?= $settings['editor_theme'] === 'material' ? 'selected' : '' ?>>Material (dunkel)</option>
                                            </select>
                                            
                                            <!-- Theme-Vorschau -->
                                            <div class="mt-2">
                                                <div id="theme-preview" class="border rounded p-2" style="font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace; font-size: 12px; line-height: 1.4;">
                                                    <div id="theme-preview-content">
                                                        <span class="theme-keyword"># Markdown</span><br>
                                                        <span class="theme-text">**Bold text** and *italic text*</span><br>
                                                        <span class="theme-comment">```javascript</span><br>
                                                        <span class="theme-keyword">function</span> <span class="theme-function">example</span>() {<br>
                                                        &nbsp;&nbsp;<span class="theme-keyword">return</span> <span class="theme-string">"Hello World"</span>;<br>
                                                        }<br>
                                                        <span class="theme-comment">```</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="auto_save_interval" class="form-label">
                                                Auto-Save Intervall: 
                                                <span class="range-value" id="auto_save_value"><?= $settings['auto_save_interval'] ?></span>s
                                            </label>
                                            <input type="range" class="form-range" id="auto_save_interval" name="auto_save_interval" 
                                                   min="30" max="300" step="30" value="<?= $settings['auto_save_interval'] ?>"
                                                   oninput="document.getElementById('auto_save_value').textContent = this.value">
                                            <div class="form-text">30-300 Sekunden für automatisches Speichern</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Navigation-Sortierung -->
                            <div class="settings-section">
                                <h5><i class="bi bi-list-ol me-2"></i>Navigation-Sortierung</h5>
                                <p class="text-muted">Bestimme die Reihenfolge der Hauptnavigation</p>
                                
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="navigation_order" class="form-label">Navigation-Reihenfolge</label>
                                            <textarea class="form-control" id="navigation_order" name="navigation_order" 
                                                      rows="6" placeholder="about&#10;blog&#10;tech&#10;diy"><?php
// Navigation-Order als Text formatieren
$navOrder = $settings['navigation_order'] ?? [];
$orderText = '';
foreach ($navOrder as $section => $priority) {
    $orderText .= $section . ':' . $priority . "\n";
}
echo htmlspecialchars(trim($orderText));
?></textarea>
                                            <div class="form-text">
                                                <strong>Format:</strong> Ein Bereich pro Zeile<br>
                                                <code>section</code> oder <code>section:priorität</code><br>
                                                <strong>Beispiel:</strong><br>
                                                <code>about:1</code><br>
                                                <code>blog:2</code><br>
                                                <code>tech</code> (automatische Priorität)<br>
                                                <code>diy</code>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="card bg-light">
                                            <div class="card-header">
                                                <small class="fw-bold">Aktuelle Navigation</small>
                                            </div>
                                            <div class="card-body">
                                                <small class="text-muted">
                                                    Aktuelle Reihenfolge (vor Änderungen):<br><br>
                                                    <?php
                                                    // Zeige aktuelle Navigation-Sortierung
                                                    $contentPath = $this->config['paths']['content'];
                                                    $currentOrder = $settings['navigation_order'] ?? [];
                                                    
                                                    if (is_dir($contentPath)) {
                                                        $sections = [];
                                                        
                                                        // Sammle sowohl Ordner als auch Root-Dateien
                                                        $items = glob($contentPath . '/*');
                                                        foreach ($items as $item) {
                                                            $basename = basename($item);
                                                            
                                                            if (is_dir($item)) {
                                                                // Ordner hinzufügen
                                                                $sections[] = $basename;
                                                            } elseif (is_file($item) && str_ends_with($basename, '.md')) {
                                                                // Markdown-Dateien hinzufügen (ohne .md Extension)
                                                                $section = substr($basename, 0, -3);
                                                                if ($section !== 'index') { // index.md ausschließen
                                                                    $sections[] = $section;
                                                                }
                                                            }
                                                        }
                                                        
                                                        // Duplikate entfernen (falls sowohl Ordner als auch Datei existieren)
                                                        $sections = array_unique($sections);
                                                        
                                                        if (!empty($sections)) {
                                                            // Sortiere Sections nach aktueller Navigation-Order
                                                            usort($sections, function($a, $b) use ($currentOrder) {
                                                                $orderA = $currentOrder[$a] ?? 999;
                                                                $orderB = $currentOrder[$b] ?? 999;
                                                                
                                                                if ($orderA === $orderB) {
                                                                    return strcasecmp($a, $b);
                                                                }
                                                                
                                                                return $orderA <=> $orderB;
                                                            });
                                                            
                                                            // Zeige sortierte Sections mit aktueller Priorität
                                                            foreach ($sections as $section) {
                                                                $priority = $currentOrder[$section] ?? 'auto';
                                                                $badgeClass = isset($currentOrder[$section]) ? 'text-bg-primary' : 'text-bg-secondary';
                                                                echo '<div class="d-flex justify-content-between align-items-center mb-1">';
                                                                echo '<span class="badge ' . $badgeClass . '">' . htmlspecialchars($section) . '</span>';
                                                                echo '<small class="text-muted ms-2">(' . $priority . ')</small>';
                                                                echo '</div>';
                                                            }
                                                        } else {
                                                            echo '<em>Keine Navigation-Bereiche gefunden</em>';
                                                        }
                                                    }
                                                    ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Aktionen -->
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Einstellungen werden in <code>system/settings.json</code> gespeichert
                                    </small>
                                </div>
                                
                                <div>
                                    <a href="/admin" class="btn btn-secondary me-2">Abbrechen</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-floppy me-1"></i> Einstellungen speichern
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let timeRemaining = <?= $timeRemaining ?>;
        
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
        
        // Theme-Vorschau-Funktion
        function previewTheme(themeName) {
            const preview = document.getElementById('theme-preview');
            
            // Entferne alle Theme-Klassen
            preview.classList.remove('theme-github', 'theme-monokai', 'theme-solarized-light', 'theme-solarized-dark', 'theme-material');
            
            // Füge neue Theme-Klasse hinzu
            if (themeName) {
                preview.classList.add('theme-' + themeName);
            }
        }
        
        // Initial theme preview laden
        document.addEventListener('DOMContentLoaded', function() {
            const currentTheme = document.getElementById('editor_theme').value;
            previewTheme(currentTheme);
        });
    </script>
</body>
</html>