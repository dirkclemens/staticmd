<?php
$pageTitle = __('admin.common.settings');
$currentUser = $this->auth->getUsername();
$timeRemaining = $this->auth->getTimeRemaining();
?>
<?php
// Security Headers setzen
require_once __DIR__ . '/../../core/SecurityHeaders.php';
use StaticMD\Core\SecurityHeaders;
SecurityHeaders::setAllSecurityHeaders('admin');
$nonce = SecurityHeaders::getNonce();
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(\StaticMD\Core\I18n::getLanguage()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('admin.brand') ?> - <?= __('admin.settings.title') ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <link rel="stylesheet" href="/public/admin.css">
</head>
<body>
    <!-- Admin Header -->
    <nav class="navbar admin-header navbar-dark">
        <div class="container-fluid">
            <a href="/admin" class="navbar-brand mb-0 h1 text-decoration-none">
                <i class="bi bi-shield-lock me-2"></i>
                <?= __('admin.brand') ?>
            </a>
            
            <div class="d-flex align-items-center text-white">
                <div class="me-3">
                    <small class="session-timer">
                        <i class="bi bi-clock me-1"></i>
                        <?= __('admin.common.session') ?>: <span id="timer"><?= gmdate('H:i:s', $timeRemaining) ?></span>
                    </small>
                            <!-- Button ins Formular verschoben -->
                    </a>
                    <ul class="dropdown-menu" style="right: 0; left: auto;">
                        <li><a class="dropdown-item" href="/admin">
                            <i class="bi bi-speedometer2 me-2"></i><?= __('admin.common.dashboard') ?>
                        </a></li>
                        <li><a class="dropdown-item" href="/">
                            <i class="bi bi-house me-2"></i><?= __('admin.common.view_site') ?>
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/admin?action=logout">
                            <i class="bi bi-box-arrow-right me-2"></i><?= __('admin.common.logout') ?>
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 admin-sidebar">
                <div class="p-3">
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?= ($_GET['action'] ?? 'dashboard') === 'dashboard' ? 'active' : '' ?>" 
                               href="/admin">
                                <i class="bi bi-speedometer2 me-2"></i>
                                <?= __('admin.common.dashboard') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($_GET['action'] ?? '') === 'files' ? 'active' : '' ?>" 
                               href="/admin?action=files">
                                <i class="bi bi-folder me-2"></i>
                                <?= __('admin.common.files') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($_GET['action'] ?? '') === 'new' ? 'active' : '' ?>" 
                               href="/admin?action=new">
                                <i class="bi bi-file-earmark-plus me-2"></i>
                                <?= __('admin.common.new_page') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($_GET['action'] ?? '') === 'edit' ? 'active' : '' ?>" 
                               href="/admin?action=edit">
                                <i class="bi bi-pencil me-2"></i>
                                <?= __('admin.common.editor') ?>
                            </a>
                        </li>
                        <hr class="my-3">
                        <li class="nav-item">
                            <a class="nav-link <?= ($_GET['action'] ?? '') === 'settings' ? 'active' : '' ?>" 
                               href="/admin?action=settings">
                                <i class="bi bi-gear me-2"></i>
                                <?= __('admin.common.settings') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/">
                                <i class="bi bi-eye me-2"></i>
                                <?= __('admin.common.view_site') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin?action=logout">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                <?= __('admin.common.logout') ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            <!-- Hauptinhalt -->
            <main class="col-md-9 col-lg-10 admin-content">
                <div class="card settings-container">
                    <form method="POST" action="/admin?action=save_settings" id="settings-form">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($this->auth->generateCSRFToken()) ?>">
                        <div class="card-header">
                            <h4 class="card-title mb-0">
                                <i class="bi bi-gear me-2"></i>
                                <?= __('admin.settings.title') ?>
                            </h4>
                            <div class="d-flex justify-content-end align-items-center mt-2 mb-1">
                                <a href="/admin" class="btn btn-secondary me-2"><?= __('admin.common.cancel') ?></a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-floppy me-1"></i> <?= __('admin.settings.save_settings') ?>
                                </button>
                            </div>
                        </div>                    
                
                        <div class="card-body">
                            <!-- Nachrichten -->
                            <?php if (isset($_GET['message'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle me-2"></i>
                                <?php
                                switch ($_GET['message']) {
                                    case 'settings_saved': echo __('admin.alerts.settings_saved'); break;
                                    case 'backup_created': echo __('admin.alerts.backup_created'); break;
                                    default: echo __('admin.alerts.success');
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
                                    case 'save_failed': echo __('admin.errors.settings_save_failed'); break;
                                    case 'csrf_invalid': echo __('admin.errors.csrf_invalid'); break;
                                    case 'backup_failed': echo __('admin.errors.backup_failed'); break;
                                    default: echo __('admin.errors.generic');
                                }
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php endif; ?>

                            <!-- Seiten-Einstellungen -->                                
                            <div class="card mt-4">                                                                
                                <div class="card-header">
                                    <h5><i class="bi bi-globe me-2"></i><?= __('admin.settings.website') ?></h5>
                                </div>
                                <div class="card-body">                                                                
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="site_name" class="form-label"><?= __('admin.settings.website_name') ?></label>
                                                <input type="text" class="form-control" id="site_name" name="site_name" 
                                                    value="<?= htmlspecialchars($settings['site_name']) ?>" 
                                                    placeholder="StaticMD" required>
                                                <div class="form-text"><?= __('admin.settings.website_name_help') ?></div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="site_logo" class="form-label"><?= __('admin.settings.logo_url') ?></label>
                                                <input type="url" class="form-control" id="site_logo" name="site_logo" 
                                                    value="<?= htmlspecialchars($settings['site_logo']) ?>" 
                                                    placeholder="https://example.com/logo.png">
                                                <div class="form-text"><?= __('admin.settings.logo_url_help') ?></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="language" class="form-label"><?= __('admin.settings.language') ?></label>
                                                <select class="form-select" id="language" name="language">
                                                    <option value="en" <?= ($settings['language'] ?? 'en') === 'en' ? 'selected' : '' ?>>English</option>
                                                    <option value="de" <?= ($settings['language'] ?? 'en') === 'de' ? 'selected' : '' ?>>Deutsch</option>
                                                </select>
                                                <div class="form-text"><?= __('admin.settings.language_help') ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>    
                            </div>
                            
                            <!-- Dashboard-Einstellungen -->
                            <div class="card mt-4">                                                                
                                <div class="card-header">
                                    <h5><i class="bi bi-speedometer2 me-2"></i><?= __('admin.settings.dashboard') ?></h5>
                                </div>
                                <div class="card-body">                                
                                    <div class="row">          
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="recent_files_count" class="form-label">
                                                    <?= __('admin.settings.recent_files_count') ?>:
                                                    <span class="range-value" id="recent_files_value"><?= $settings['recent_files_count'] ?></span>
                                                </label>
                                                <input type="range" class="form-range" id="recent_files_count" name="recent_files_count"
                                                    min="5" max="50" value="<?= $settings['recent_files_count'] ?>"
                                                    oninput="document.getElementById('recent_files_value').textContent = this.value">
                                                <div class="form-text"><?= __('admin.settings.recent_files_count_help') ?></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="search_result_limit" class="form-label">
                                                    <?= __('admin.search.result_limit_label') ?>:
                                                    <span class="range-value" id="search_result_limit_value"><?= $settings['search_result_limit'] ?? 50 ?></span>
                                                </label>
                                                <input type="range" class="form-range" id="search_result_limit" name="search_result_limit"
                                                    min="10" max="200" step="10" value="<?= $settings['search_result_limit'] ?? 50 ?>"
                                                    oninput="document.getElementById('search_result_limit_value').textContent = this.value">
                                                <div class="form-text"><?= __('admin.search.result_limit_help') ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="show_file_stats" name="show_file_stats"
                                                <?= $settings['show_file_stats'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="show_file_stats">
                                                <?= __('admin.settings.show_file_stats') ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>    
                            </div>
                            
                            <!-- Frontend-Theme -->
                            <div class="card mt-4">                                                                
                                <div class="card-header">
                                    <h5><i class="bi bi-palette me-2"></i><?= __('admin.settings.frontend_theme') ?></h5>
                                </div>
                                <div class="card-body">                                
                                    <div class="row">                                                                    
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="frontend_theme" class="form-label"><?= __('admin.settings.frontend_theme') ?></label>
                                                <select class="form-select" id="frontend_theme" name="frontend_theme">
                                                    <?php foreach ($availableThemes as $theme): ?>
                                                        <option value="<?= htmlspecialchars($theme) ?>" <?= ($settings['frontend_theme'] ?? 'bootstrap') === $theme ? 'selected' : '' ?>>
                                                            <?= ucfirst(str_replace(['-', '_'], [' ', ' '], $theme)) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <div class="form-text">
                                                    <?= __('admin.settings.frontend_theme_help') ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label"><?= __('admin.settings.theme_preview') ?></label>
                                                <div class="border rounded p-3" style="background: linear-gradient(45deg, #f8f9fa 25%, transparent 25%), linear-gradient(-45deg, #f8f9fa 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #f8f9fa 75%), linear-gradient(-45deg, transparent 75%, #f8f9fa 75%); background-size: 20px 20px; background-position: 0 0, 0 10px, 10px -10px, -10px 0px;">
                                                    <div class="text-center text-muted">
                                                        <i class="bi bi-eye fs-1"></i><br>
                                                        <small><?= __('admin.settings.theme_preview') ?></small><br>
                                                        <small><?= __('admin.settings.visit_frontend') ?></small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>    
                                </div>
                            </div>
                            
                            <!-- Editor-Einstellungen -->
                            <div class="card mt-4">                                                                
                                <div class="card-header">
                                    <h5><i class="bi bi-pencil me-2"></i><?= __('admin.settings.editor') ?></h5>
                                </div>
                                <div class="card-body">                                
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="editor_theme" class="form-label"><?= __('admin.settings.editor_theme') ?></label>
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
                                                    <?= __('admin.settings.auto_save_interval') ?>: 
                                                    <span class="range-value" id="auto_save_value"><?= $settings['auto_save_interval'] ?></span>s
                                                </label>
                                                <input type="range" class="form-range" id="auto_save_interval" name="auto_save_interval" 
                                                    min="30" max="300" step="30" value="<?= $settings['auto_save_interval'] ?>"
                                                    oninput="document.getElementById('auto_save_value').textContent = this.value">
                                                <div class="form-text"><?= __('admin.settings.auto_save_interval_help') ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Navigation-Sortierung -->
                            <div class="card mt-4">                                                                
                                <div class="card-header">
                                    <h5><i class="bi bi-list-ol me-2"></i><?= __('admin.settings.navigation') ?></h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="mb-3">
                                                <label for="navigation_order" class="form-label"><?= __('admin.settings.navigation_order') ?></label>
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
                                                    <strong><?= __('admin.settings.navigation_format') ?></strong><br>
                                                    <code>section</code> <?= __('admin.settings.navigation_or') ?> <code>section:<?= __('admin.settings.navigation_priority') ?></code><br>
                                                    <strong><?= __('admin.settings.navigation_example') ?></strong><br>
                                                    <code>about:1</code><br>
                                                    <code>blog:2</code><br>
                                                    <code>tech</code> <?= __('admin.settings.navigation_auto') ?><br>
                                                    <code>diy</code>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="card bg-light">
                                                <div class="card-header">
                                                    <small class="fw-bold"><?= __('admin.settings.current_navigation') ?></small>
                                                </div>
                                                <div class="card-body">
                                                    <small class="text-muted">
                                                        <?= __('admin.settings.current_order') ?><br><br>
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
                                                                echo '<em>' . __('admin.settings.no_navigation_found') . '</em>';
                                                            }
                                                        }
                                                        ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- SEO & Robots Settings -->
                            <div class="card mt-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-search me-2"></i>
                                        <?= __('admin.settings.seo.title') ?>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="seo_robots_policy" class="form-label">
                                                    <i class="bi bi-robot me-1"></i>
                                                    <?= __('admin.settings.seo.robots_policy_label') ?>
                                                </label>
                                                <select class="form-select" id="seo_robots_policy" name="seo_robots_policy">
                                                    <option value="index,follow" <?= ($settings['seo_robots_policy'] ?? 'index,follow') === 'index,follow' ? 'selected' : '' ?>>
                                                        <?= __('admin.settings.seo.robots_index_follow') ?>
                                                    </option>
                                                    <option value="index,nofollow" <?= ($settings['seo_robots_policy'] ?? '') === 'index,nofollow' ? 'selected' : '' ?>>
                                                        <?= __('admin.settings.seo.robots_index_nofollow') ?>
                                                    </option>
                                                    <option value="noindex,follow" <?= ($settings['seo_robots_policy'] ?? '') === 'noindex,follow' ? 'selected' : '' ?>>
                                                        <?= __('admin.settings.seo.robots_noindex_follow') ?>
                                                    </option>
                                                    <option value="noindex,nofollow" <?= ($settings['seo_robots_policy'] ?? '') === 'noindex,nofollow' ? 'selected' : '' ?>>
                                                        <?= __('admin.settings.seo.robots_noindex_nofollow') ?>
                                                    </option>
                                                </select>
                                                <small class="text-muted">
                                                    <?= __('admin.settings.seo.robots_policy_help') ?>
                                                </small>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="seo_block_crawlers" 
                                                           name="seo_block_crawlers" value="1" 
                                                           <?= ($settings['seo_block_crawlers'] ?? false) ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="seo_block_crawlers">
                                                        <i class="bi bi-shield-x me-1"></i>
                                                        <strong><?= __('admin.settings.seo.block_crawlers_label') ?></strong>
                                                    </label>
                                                </div>
                                                <small class="text-muted">
                                                    <?= __('admin.settings.seo.block_crawlers_help') ?><br>
                                                    <strong><?= __('admin.settings.seo.block_crawlers_warning') ?></strong>
                                                </small>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="seo_generate_robots_txt" 
                                                           name="seo_generate_robots_txt" value="1" 
                                                           <?= ($settings['seo_generate_robots_txt'] ?? true) ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="seo_generate_robots_txt">
                                                        <i class="bi bi-file-text me-1"></i>
                                                        <?= __('admin.settings.seo.generate_robots_txt_label') ?>
                                                    </label>
                                                </div>
                                                <small class="text-muted">
                                                    <?= __('admin.settings.seo.generate_robots_txt_help') ?> <a href="/robots.php" target="_blank">/robots.txt</a>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-info">
                                        <h6><i class="bi bi-info-circle me-2"></i><?= __('admin.settings.seo.features_title') ?></h6>
                                        <ul class="mb-0">
                                            <li><?= __('admin.settings.seo.features_per_page') ?> <code>Robots: noindex,nofollow</code></li>
                                            <li><?= __('admin.settings.seo.features_robots_txt') ?> <a href="/robots.php" target="_blank">/robots.txt</a></li>
                                            <li><?= __('admin.settings.seo.features_meta_tags') ?></li>
                                            <li><?= __('admin.settings.seo.features_http_headers') ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Aktionen -->
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div>
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        <?= __('admin.settings.actions_saved_in') ?> <code>system/settings.json</code>
                                    </small>
                                </div>
                                
                                <div>
                                    <a href="/admin" class="btn btn-secondary me-2"><?= __('admin.common.cancel') ?></a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-floppy me-1"></i> <?= __('admin.settings.save_settings') ?>
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <!-- Backup & Wiederherstellung (außerhalb des Settings-Forms) -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-archive me-2"></i>
                                    <?= __('admin.settings.backup.title') ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <p class="text-muted">
                                            <i class="bi bi-info-circle me-1"></i>
                                            <?= __('admin.settings.backup.description') ?>
                                        </p>
                                        
                                        <div class="alert alert-info">
                                            <h6><i class="bi bi-box-seam me-2"></i><?= __('admin.settings.backup.includes_title') ?></h6>
                                            <ul class="mb-0">
                                                <li><?= __('admin.settings.backup.includes_content') ?> <code>/content/</code></li>
                                                <li><?= __('admin.settings.backup.includes_config') ?> <code>config.php</code>, <code>system/settings.json</code></li>
                                                <li><?= __('admin.settings.backup.includes_themes') ?> <code>/system/themes/</code></li>
                                                <li><?= __('admin.settings.backup.includes_assets') ?> <code>/public/images/</code>, <code>/public/downloads/</code></li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <i class="bi bi-download fs-1 text-primary mb-3"></i>
                                                <h6><?= __('admin.settings.backup.create_backup') ?></h6>
                                                <p class="small text-muted mb-3">
                                                    <?= __('admin.settings.backup.create_description') ?>
                                                </p>
                                                
                                                <div class="alert alert-light border mb-3">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <small class="text-muted">
                                                            <i class="bi bi-files me-1"></i>
                                                            <strong><?= number_format($backupStats['files']) ?></strong> Dateien
                                                        </small>
                                                        <small class="text-muted">
                                                            <i class="bi bi-hdd me-1"></i>
                                                            <strong><?= $backupStats['size_formatted'] ?></strong>
                                                            <div class="text-muted" style="font-size: 0.75rem;"><?= __('admin.settings.backup.size_info') ?></div>
                                                        </small>
                                                    </div>
                                                </div>
                                                
                                                <form method="POST" action="/admin?action=create_backup" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($this->auth->generateCSRFToken()) ?>">
                                                    <button type="submit" class="btn btn-primary" id="createBackupBtn">
                                                        <i class="bi bi-archive me-2"></i>
                                                        <?= __('admin.settings.backup.create_button') ?>
                                                    </button>
                                                </form>
                                                
                                                <div id="backupProgress" class="mt-3" style="display: none;">
                                                    <div class="spinner-border spinner-border-sm me-2" role="status">
                                                        <span class="visually-hidden">Loading...</span>
                                                    </div>
                                                    <small class="text-muted"><?= __('admin.settings.backup.creating') ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script nonce="<?= $nonce ?>">
        let timeRemaining = <?= $timeRemaining ?>;
        
        // Session-Timer
        function updateTimer() {
            if (timeRemaining <= 0) {
                alert('<?= __('admin.session.expired_alert') ?>');
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
        
        // Backup-Formular Handler
        document.getElementById('createBackupBtn')?.addEventListener('click', function(e) {
            const progressDiv = document.getElementById('backupProgress');
            const button = this;
            
            // Zeige Progress
            button.style.display = 'none';
            progressDiv.style.display = 'block';
            
            // Funktion zum Zurücksetzen des UI
            function resetBackupUI() {
                button.style.display = 'block';
                progressDiv.style.display = 'none';
            }
            
            // Option 1: Timeout nach 5 Sekunden (Download sollte bis dahin gestartet sein)
            const timeoutId = setTimeout(resetBackupUI, 5000);
            
            // Option 2: Zurücksetzen wenn Fenster wieder Fokus bekommt
            // (passiert oft nach Download-Dialog)
            const focusHandler = function() {
                clearTimeout(timeoutId);
                resetBackupUI();
                window.removeEventListener('focus', focusHandler);
            };
            
            // Fokus-Handler mit kurzer Verzögerung hinzufügen
            // (damit er nicht sofort bei Form-Submit triggert)
            setTimeout(() => {
                window.addEventListener('focus', focusHandler);
            }, 1000);
            
            // Form wird normal submitted, aber wir zeigen visuelles Feedback
        });
    </script>
</body>
</html>