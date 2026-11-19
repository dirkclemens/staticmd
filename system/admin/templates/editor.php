<?php
$pageTitle = __('admin.common.editor');
$currentUser = $this->auth->getUsername();
$timeRemaining = $this->auth->getTimeRemaining();

// Einstellungen für Editor-Theme laden
$settingsFile = $this->config['paths']['system'] . '/settings.json';
$settings = [];
if (file_exists($settingsFile)) {
    $settings = json_decode(file_get_contents($settingsFile), true) ?: [];
}
$editorTheme = $settings['editor_theme'] ?? 'elegant';
?>
<?php
// Security Headers setzen
require_once __DIR__ . '/../../core/SecurityHeaders.php';
use StaticMD\Core\SecurityHeaders;
SecurityHeaders::setAllSecurityHeaders('admin');
$nonce = SecurityHeaders::getNonce();
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(\StaticMD\Core\I18n::getLanguage()) ?>" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('admin.brand') ?> - <?= __('admin.common.editor') ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- CodeMirror CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/codemirror.min.css">
    
    <!-- CodeMirror Themes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/theme/elegant.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/theme/eclipse.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/theme/idea.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/theme/monokai.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/theme/solarized.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/theme/material.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/addon/dialog/dialog.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/addon/hint/show-hint.min.css">
    
    <link rel="stylesheet" href="/system/admin/admin.css">

    <!-- Container-Blöcke und Alerts für die Vorschau (inline, da /system/ per .htaccess gesperrt ist) -->
    <style><?php include __DIR__ . '/../../themes/shared/markdown-blocks.css'; ?></style>
</head>
<body>
    <!-- Admin Header -->
    <nav class="navbar admin-header navbar-expand-lg">
        <div class="container-fluid">
            <a href="/admin" class="navbar-brand mb-0 h1 text-decoration-none">
                <i class="bi bi-shield-lock me-2"></i>
                <?= __('admin.brand') ?>
            </a>
            
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <small class="session-timer">
                        <i class="bi bi-clock me-1"></i>
                        <?= __('admin.common.session') ?>: <span id="timer"><?= gmdate('H:i:s', $timeRemaining) ?></span>
                    </small>
                </div>
                
                <!-- Theme Toggle Button -->
                <button id="theme-toggle" class="btn btn-link me-3" title="<?= \StaticMD\Core\I18n::t('core.theme_toggle') ?>">
                    <i class="bi bi-moon-fill" id="theme-icon"></i>
                </button>

                <div class="dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>
                        <?= htmlspecialchars($currentUser) ?>
                    </a>
                    <ul class="dropdown-menu" style="right: 0; left: auto;">
                        <li><a class="dropdown-item" href="/admin">
                            <i class="bi bi-speedometer2 me-2"></i><?= __('admin.common.dashboard') ?>
                        </a></li>
                        <li><a class="dropdown-item" href="/">
                            <i class="bi bi-house me-2"></i><?= __('admin.common.view_site') ?>
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/help/editor-shortcuts" target="_blank">
                            <i class="bi bi-keyboard me-2"></i><?= __('admin.common.keyboard_shortcuts') ?>
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="/admin?action=logout">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($this->auth->generateCSRFToken()) ?>">
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-box-arrow-right me-2"></i><?= __('admin.common.logout') ?>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <!-- Admin Breadcrumbs -->
        <?php if (!empty($file) && $file !== 'index'): ?>
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="/admin?action=files">
                        <i class="bi bi-folder"></i> Dateien
                    </a>
                </li>
                <?php 
                $parts = explode('/', trim($file, '/'));
                $currentPath = '';
                foreach ($parts as $i => $part):
                    $currentPath .= ($currentPath ? '/' : '') . $part;
                    $isLast = ($i === count($parts) - 1);
                ?>
                <li class="breadcrumb-item <?= $isLast ? 'active' : '' ?>">
                    <?php if ($isLast): ?>
                        <i class="bi bi-file-earmark-text"></i> <?= htmlspecialchars($part) ?>
                    <?php else: ?>
                        <i class="bi bi-folder"></i> <?= htmlspecialchars($part) ?>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ol>
        </nav>
        <?php endif; ?>
        
        <form method="POST" action="/admin?action=save" id="editorForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($this->auth->generateCSRFToken()) ?>">
            <input type="hidden" name="return_url" value="<?= htmlspecialchars($_GET['return_url'] ?? '') ?>">
            
            <div class="row">
                <!-- Metadaten-Sidebar -->
                <div class="col-lg-3">
                    <div class="card editor-container mb-4">
                        <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-gear me-2"></i>
                                    <?= __('admin.editor.page_settings') ?>
                                </h5>
                        </div>
                        <div class="card-body meta-form">
                            <div class="mb-3">
                                <label for="file" class="form-label fw-bold"><?= __('admin.editor.file_route') ?></label>
                                <input type="text" class="form-control" id="file" name="file" 
                                       value="<?= htmlspecialchars($file) ?>" 
                                       placeholder="<?= __('admin.editor.file_route_placeholder') ?>" required>
                                <div class="form-text"><?= __('admin.editor.file_route_help') ?></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="title" class="form-label fw-bold"><?= __('admin.editor.title') ?></label>
                                <input type="text" class="form-control" id="title" name="meta[Title]" 
                                       value="<?= htmlspecialchars($meta['Title'] ?? $meta['title'] ?? '') ?>" 
                                       placeholder="<?= __('admin.editor.title_placeholder') ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="titleslug" class="form-label fw-bold"><?= __('admin.editor.titleslug') ?></label>
                                <input type="text" class="form-control" id="titleslug" name="meta[TitleSlug]" 
                                       value="<?= htmlspecialchars($meta['TitleSlug'] ?? $meta['titleslug'] ?? '') ?>" 
                                       placeholder="<?= __('admin.editor.titleslug_placeholder') ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="layout" class="form-label fw-bold"><?= __('admin.editor.layout') ?></label>
                                <select class="form-select" id="layout" name="meta[Layout]">
                                    <option value=""><?= __('admin.editor.layout_standard') ?></option>
                                    <option value="wiki" <?= ($meta['Layout'] ?? $meta['layout'] ?? '') === 'wiki' ? 'selected' : '' ?>><?= __('admin.editor.layout_wiki') ?></option>
                                    <option value="blog" <?= ($meta['Layout'] ?? $meta['layout'] ?? '') === 'blog' ? 'selected' : '' ?>><?= __('admin.editor.layout_blog') ?></option>
                                    <option value="page" <?= ($meta['Layout'] ?? $meta['layout'] ?? '') === 'page' ? 'selected' : '' ?>><?= __('admin.editor.layout_page') ?></option>
                                    <option value="gallery" <?= ($meta['Layout'] ?? $meta['layout'] ?? '') === 'gallery' ? 'selected' : '' ?>><?= __('admin.editor.layout_gallery') ?></option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="author" class="form-label fw-bold"><?= __('admin.editor.author') ?></label>
                                <input type="text" class="form-control" id="author" name="meta[Author]" 
                                       value="<?= htmlspecialchars($meta['Author'] ?? $meta['author'] ?? '') ?>" 
                                       placeholder="<?= __('admin.editor.author_placeholder') ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="tag" class="form-label fw-bold"><?= __('admin.editor.tags') ?></label>
                                <input type="text" class="form-control" id="tag" name="meta[Tag]" 
                                       value="<?= htmlspecialchars($meta['Tag'] ?? $meta['tags'] ?? '') ?>" 
                                       placeholder="<?= __('admin.editor.tags_placeholder') ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="visibility" class="form-label fw-bold"><?= __('admin.editor.visibility') ?></label>
                                <select class="form-select" id="visibility" name="meta[Visibility]">
                                    <option value="public" <?= ($meta['Visibility'] ?? $meta['visibility'] ?? 'public') === 'public' ? 'selected' : '' ?>><?= __('admin.editor.visibility_public') ?></option>
                                    <option value="private" <?= ($meta['Visibility'] ?? $meta['visibility'] ?? 'public') === 'private' ? 'selected' : '' ?>><?= __('admin.editor.visibility_private') ?></option>
                                </select>
                                <div class="form-text"><?= __('admin.editor.visibility_help') ?></div>
                            </div>
                            
                            <hr class="my-3">
                            <small class="text-muted"><?= __('admin.editor.additional_meta') ?></small>
                            
                            <div class="mb-3">
                                <label for="date" class="form-label fw-bold"><?= __('admin.editor.date') ?></label>
                                <input type="date" class="form-control" id="date" name="meta[date]" 
                                       value="<?= htmlspecialchars($meta['date'] ?? '') ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label fw-bold"><?= __('admin.editor.description') ?></label>
                                <textarea class="form-control" id="description" name="meta[description]" 
                                          rows="2" placeholder="<?= __('admin.editor.description_placeholder') ?>"><?= htmlspecialchars($meta['description'] ?? '') ?></textarea>
                            </div>
                            
                            <?php if (!$isNewFile): ?>
                            <div class="mb-3">
                                <a href="/<?= htmlspecialchars($file) ?>" class="btn btn-outline-info btn-sm w-100" target="_blank">
                                    <i class="bi bi-eye me-1"></i> <?= __('admin.editor.view_page') ?>
                                </a>
                            </div>
                            <div class="mb-3">
                                <button type="button" class="btn btn-outline-danger btn-sm w-100"
                                        id="confirmDeleteBtn" data-delete-file="<?= htmlspecialchars($file) ?>">
                                    <i class="bi bi-trash me-1"></i> <?= __('admin.editor.delete_file') ?>
                                </button>
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
                                    <?= $isNewFile ? __('admin.editor.new_page_create') : __('admin.editor.page_edit') ?>
                                </h5>
                            </div>
                            
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="editorTab">
                                    <i class="bi bi-code me-1"></i> Editor
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="previewTab">
                                    <i class="bi bi-eye me-1"></i> <?= __('admin.editor.preview') ?>
                                </button>
                                <button type="button" class="btn btn-outline-info btn-sm" id="splitTab">
                                    <i class="bi bi-layout-split me-1"></i> Split
                                </button>
                            </div>
                            
                            <!-- Sync Scroll Toggle (nur in Split-View sichtbar) -->
                            <button type="button" class="btn btn-outline-success btn-sm ms-2 d-none" id="syncScrollToggle" title="Synchrones Scrollen">
                                <i class="bi bi-arrow-down-up me-1"></i> Sync
                            </button>
                        </div>
                        
                        <!-- Editor Toolbar -->
                        <div class="toolbar">
                            <!-- Text-Formatierung -->
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('**', '**')" title="<?php echo __('admin.editor.toolbar.bold'); ?>">
                                <i class="bi bi-type-bold"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('*', '*')" title="<?php echo __('admin.editor.toolbar.italic'); ?>">
                                <i class="bi bi-type-italic"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('~~', '~~')" title="<?php echo __('admin.editor.toolbar.strikethrough'); ?>">
                                <i class="bi bi-type-strikethrough"></i>
                            </button>
                            
                            <div class="vr mx-2"></div>
                            
                            <!-- Überschriften -->
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('# ', '')" title="<?php echo __('admin.editor.toolbar.heading_h1'); ?>">
                                    <i class="bi bi-type-h1"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('## ', '')" title="<?php echo __('admin.editor.toolbar.heading_h2'); ?>">
                                    <i class="bi bi-type-h2"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('### ', '')" title="<?php echo __('admin.editor.toolbar.heading_h3'); ?>">
                                    <i class="bi bi-type-h3"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('#### ', '')" title="<?php echo __('admin.editor.toolbar.heading_h4'); ?>">
                                    <span style="font-size: 0.75em; font-weight: bold;">H4</span>
                                </button>
                            </div>
                            
                            <div class="vr mx-2"></div>
                            
                            <!-- Listen -->
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('- ', '')" title="<?php echo __('admin.editor.toolbar.list_unordered'); ?>">
                                <i class="bi bi-list-ul"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('1. ', '')" title="<?php echo __('admin.editor.toolbar.list_ordered'); ?>">
                                <i class="bi bi-list-ol"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('- [ ]', '')" title="<?php echo __('admin.editor.toolbar.checklist'); ?>">
                                <i class="bi bi-list-check"></i>
                            </button>
                            
                            <div class="vr mx-2"></div>
                            
                            <!-- Links & Medien -->
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('[', '](url)')" title="<?php echo __('admin.editor.toolbar.link'); ?>">
                                <i class="bi bi-link"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('![Alt-Text](', ')')" title="<?php echo __('admin.editor.toolbar.image'); ?>">
                                <i class="bi bi-image"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertDownloadTag()" title="<?php echo __('admin.editor.toolbar.download_link'); ?>">
                                <i class="bi bi-file-earmark-arrow-down"></i>
                            </button>
                            
                            <div class="vr mx-2"></div>
                            
                            <!-- Code & Spezial -->
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('`', '`')" title="<?php echo __('admin.editor.toolbar.inline_code'); ?>">
                                <i class="bi bi-code"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertCodeBlock()" title="<?php echo __('admin.editor.toolbar.code_block'); ?>">
                                <i class="bi bi-code-square"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('> ', '')" title="<?php echo __('admin.editor.toolbar.quote'); ?>">
                                <i class="bi bi-quote"></i>
                            </button>
                            
                            <div class="vr mx-2"></div>
                            
                            <!-- Struktur -->
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertTable()" title="<?php echo __('admin.editor.toolbar.table'); ?>">
                                <i class="bi bi-table"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('---', '')" title="<?php echo __('admin.editor.toolbar.horizontal_line'); ?>">
                                <i class="bi bi-hr"></i>
                            </button>
                            
                            <div class="vr mx-2"></div>
                            <!--Sprungmarke-->
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertAnchor()" title="<?php echo __('admin.editor.toolbar.anchor'); ?>">
                                <i class="bi bi-bookmark"></i> 
                            </button>                            
                            <!-- Accordion -->
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertAccordion()" title="<?php echo __('admin.editor.toolbar.accordion'); ?>">
                                <i class="bi bi-arrows-collapse"></i>
                            </button>
                            <!-- Auth-Block -->
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertAuthBlock()" title="<?php echo __('admin.editor.toolbar.auth_block'); ?>">
                                <i class="bi bi-shield-lock"></i>
                            </button>

                            <!-- Container-Blöcke & Alerts (::: / > [!NOTE]) -->
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" title="<?= __('admin.editor.blocks.title') ?>">
                                    <i class="bi bi-layout-split"></i>
                                </button>
                                <ul class="dropdown-menu blocks-dropdown">
                                    <li><h6 class="dropdown-header"><?= __('admin.editor.blocks.header_alerts') ?></h6></li>
                                    <li><a class="dropdown-item" href="#" data-insert-block="alert-note"><i class="bi bi-info-circle me-2 text-primary"></i><?= __('admin.editor.blocks.alert_note') ?></a></li>
                                    <li><a class="dropdown-item" href="#" data-insert-block="alert-tip"><i class="bi bi-lightbulb me-2 text-success"></i><?= __('admin.editor.blocks.alert_tip') ?></a></li>
                                    <li><a class="dropdown-item" href="#" data-insert-block="alert-important"><i class="bi bi-chat-square-text me-2" style="color:#8250df"></i><?= __('admin.editor.blocks.alert_important') ?></a></li>
                                    <li><a class="dropdown-item" href="#" data-insert-block="alert-warning"><i class="bi bi-exclamation-triangle me-2 text-warning"></i><?= __('admin.editor.blocks.alert_warning') ?></a></li>
                                    <li><a class="dropdown-item" href="#" data-insert-block="alert-caution"><i class="bi bi-exclamation-octagon me-2 text-danger"></i><?= __('admin.editor.blocks.alert_caution') ?></a></li>
                                    <li><a class="dropdown-item" href="#" data-insert-block="alert-fold"><i class="bi bi-chevron-bar-expand me-2"></i><?= __('admin.editor.blocks.alert_fold') ?></a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header"><?= __('admin.editor.blocks.header_layout') ?></h6></li>
                                    <li><a class="dropdown-item" href="#" data-insert-block="columns-2"><i class="bi bi-layout-split me-2"></i><?= __('admin.editor.blocks.columns_2') ?></a></li>
                                    <li><a class="dropdown-item" href="#" data-insert-block="columns-3"><i class="bi bi-layout-three-columns me-2"></i><?= __('admin.editor.blocks.columns_3') ?></a></li>
                                    <li><a class="dropdown-item" href="#" data-insert-block="columns-40-60"><i class="bi bi-layout-sidebar me-2"></i><?= __('admin.editor.blocks.columns_40_60') ?></a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header"><?= __('admin.editor.blocks.header_boxes') ?></h6></li>
                                    <li><a class="dropdown-item" href="#" data-insert-block="box"><i class="bi bi-square me-2"></i><?= __('admin.editor.blocks.box') ?></a></li>
                                    <li><a class="dropdown-item" href="#" data-insert-block="box-center"><i class="bi bi-text-center me-2"></i><?= __('admin.editor.blocks.box_center') ?></a></li>
                                    <li><a class="dropdown-item" href="#" data-insert-block="details"><i class="bi bi-caret-right-square me-2"></i><?= __('admin.editor.blocks.details') ?></a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="/help/container-alerts" target="_blank"><i class="bi bi-question-circle me-2"></i><?= __('admin.editor.blocks.help') ?></a></li>
                                </ul>
                            </div>

                            <div class="vr mx-2"></div>

                            <!-- Shortcodes -->
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertShortcodePages()" title="<?php echo __('admin.editor.toolbar.shortcode_pages'); ?>">
                                <i class="bi bi-collection"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertShortcodeTags()" title="<?php echo __('admin.editor.toolbar.shortcode_tags'); ?>">
                                <i class="bi bi-tags"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertShortcodeFolder()" title="<?php echo __('admin.editor.toolbar.shortcode_folder'); ?>">
                                <i class="bi bi-folder2-open"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertShortcodeGallery()" title="<?php echo __('admin.editor.toolbar.shortcode_gallery'); ?>">
                                <i class="bi bi-images"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertShortcodeBloglist()" title="<?php echo __('admin.editor.toolbar.shortcode_bloglist'); ?>">
                                <i class="bi bi-newspaper"></i>
                            </button>

                            <!-- Text-Werkzeuge (auf Markierung anwenden) -->
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" title="<?= __('admin.editor.text_tools.title') ?>">
                                    <i class="bi bi-type"></i>
                                </button>
                                <ul class="dropdown-menu text-tools-dropdown">
                                    <li><h6 class="dropdown-header"><?= __('admin.editor.text_tools.header_case') ?></h6></li>
                                    <li><a class="dropdown-item" href="#" data-text-transform="upper"><?= __('admin.editor.text_tools.upper') ?></a></li>
                                    <li><a class="dropdown-item" href="#" data-text-transform="lower"><?= __('admin.editor.text_tools.lower') ?></a></li>
                                    <li><a class="dropdown-item" href="#" data-text-transform="sentence"><?= __('admin.editor.text_tools.sentence') ?></a></li>
                                    <li><a class="dropdown-item" href="#" data-text-transform="title"><?= __('admin.editor.text_tools.title_case') ?></a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header"><?= __('admin.editor.text_tools.header_identifier') ?></h6></li>
                                    <li><a class="dropdown-item font-monospace" href="#" data-text-transform="camel">camelCase</a></li>
                                    <li><a class="dropdown-item font-monospace" href="#" data-text-transform="snake">snake_case</a></li>
                                    <li><a class="dropdown-item font-monospace" href="#" data-text-transform="kebab">kebab-case</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header"><?= __('admin.editor.text_tools.header_lines') ?></h6></li>
                                    <li><a class="dropdown-item" href="#" data-text-transform="sortLines"><i class="bi bi-sort-alpha-down me-2"></i><?= __('admin.editor.text_tools.sort_lines') ?></a></li>
                                    <li><a class="dropdown-item" href="#" data-text-transform="uniqueLines"><i class="bi bi-intersect me-2"></i><?= __('admin.editor.text_tools.unique_lines') ?></a></li>
                                    <li><a class="dropdown-item" href="#" data-text-transform="removeEmptyLines"><i class="bi bi-distribute-vertical me-2"></i><?= __('admin.editor.text_tools.remove_empty_lines') ?></a></li>
                                    <li><a class="dropdown-item" href="#" data-text-transform="cleanWhitespace"><i class="bi bi-eraser me-2"></i><?= __('admin.editor.text_tools.clean_whitespace') ?></a></li>
                                </ul>
                            </div>

                            <!-- Emojis -->
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" title="<?php echo __('admin.editor.toolbar.emoji'); ?>">
                                    😊
                                </button>
                                <ul class="dropdown-menu emoji-dropdown">
                                    <li><h6 class="dropdown-header">Gesichter</h6></li>
                                    <li><a class="dropdown-item" href="#" onclick="insertEmoji(':smile:')">😄 :smile:</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="insertEmoji(':grin:')">😁 :grin:</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="insertEmoji(':joy:')">😂 :joy:</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="insertEmoji(':blush:')">😊 :blush:</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="insertEmoji(':wink:')">😉 :wink:</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="insertEmoji(':heart_eyes:')">😍 :heart_eyes:</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="insertEmoji(':sunglasses:')">😎 :sunglasses:</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="insertEmoji(':cry:')">😢 :cry:</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Reaktionen</h6></li>
                                    <li><a class="dropdown-item" href="#" onclick="insertEmoji(':thumbsup:')">👍 :thumbsup:</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="insertEmoji(':thumbsdown:')">👎 :thumbsdown:</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="insertEmoji(':ok_hand:')">👌 :ok_hand:</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="insertEmoji(':clap:')">👏 :clap:</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="insertEmoji(':pray:')">🙏 :pray:</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="insertEmoji(':muscle:')">💪 :muscle:</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Herzen</h6></li>
                                    <li><a class="dropdown-item" href="#" onclick="insertEmoji(':heart:')">❤️ :heart:</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="insertEmoji(':blue_heart:')">💙 :blue_heart:</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="insertEmoji(':green_heart:')">💚 :green_heart:</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="insertEmoji(':broken_heart:')">💔 :broken_heart:</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header"><?= __('admin.editor.emoji_categories.activities') ?></h6></li>
                                    <li><a class="dropdown-item" href="#" onclick="insertEmoji(':fire:')">🔥 :fire:</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="insertEmoji(':star:')">⭐ :star:</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="insertEmoji(':rocket:')">🚀 :rocket:</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="insertEmoji(':tada:')">🎉 :tada:</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="insertEmoji(':trophy:')">🏆 :trophy:</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="insertEmoji(':gift:')">🎁 :gift:</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Technik</h6></li>
                                    <li><a class="dropdown-item" href="#" onclick="insertEmoji(':computer:')">💻 :computer:</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="insertEmoji(':phone:')">📱 :phone:</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="insertEmoji(':email:')">📧 :email:</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="insertEmoji(':gear:')">⚙️ :gear:</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="insertEmoji(':bulb:')">💡 :bulb:</a></li>
                                </ul>
                            </div>
                            
                            <div class="float-end">
                                <button type="button" class="btn btn-outline-info btn-sm" id="fullscreenBtn" title="<?= __('admin.editor.fullscreen') ?>">
                                    <i class="bi bi-arrows-fullscreen"></i>
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="validateMarkdownBtn" onclick="validateMarkdown()" title="Markdown-Syntax prüfen">
                                    <i class="bi bi-check2-circle me-1"></i> Syntax prüfen
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="cancelEditBtn">
                                    <i class="bi bi-x-circle me-1"></i> <?= __('admin.common.cancel') ?>
                                </button>
                                <button type="submit" class="btn btn-success btn-sm" title="<?= __('admin.editor.save_button_title') ?>">
                                    <i class="bi bi-floppy me-1"></i> <?= __('admin.common.save') ?>
                                </button>
                            </div>
                        </div>
                        
                        <div class="card-body p-0">
                            <div class="row g-0">
                                <!-- Editor -->
                                <div class="col-12" id="editorColumn">
                                    <textarea id="contentEditor" name="content" style="display:none;"><?= htmlspecialchars($content) ?></textarea>
                                </div>
                                
                                <!-- <?= __('admin.editor.preview') ?> -->
                                <div class="col-6 d-none" id="previewColumn">
                                    <div class="preview-content" id="previewContent">
                                        <p class="text-muted"><?= __('admin.editor.preview_loading') ?></p>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/addon/search/search.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/addon/search/searchcursor.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/addon/dialog/dialog.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/addon/hint/show-hint.min.js"></script>
    
    <script nonce="<?= $nonce ?>">
        // Download-Tag einfügen
        function insertDownloadTag() {
            if (!editor) return;
            const doc = editor.getDoc();
            const tag = '[download datei.pdf "Alt-Text"]';
            doc.replaceSelection(tag);
            editor.focus();
        }
        // Sprungmarke einfügen
        function insertAnchor() {
            if (!editor) return;
            const doc = editor.getDoc();
            const anchorText = '\n[Title](#anchor)\n\n{#anchor}\n';
            doc.replaceSelection(anchorText);
            editor.focus();
        }
        // Aufklappbaren Abschnitt einfügen (::: details; ersetzt den alten [accordionstart]-Shortcode)
        function insertAccordion() {
            insertBlock('details');
        }

        function insertAuthBlock() {
            if (!editor) return;
            const doc = editor.getDoc();
            const selected = doc.getSelection();
            const inner = selected || 'Dieser Inhalt ist nur für eingeloggte Benutzer sichtbar.';
            doc.replaceSelection('\n[authstart]\n' + inner + '\n[authstop]\n');
            editor.focus();
        }

        function insertShortcodePages() {
            if (!editor) return;
            editor.getDoc().replaceSelection('\n[pages /pfad/ 10]\n');
            editor.focus();
        }

        function insertShortcodeTags() {
            if (!editor) return;
            editor.getDoc().replaceSelection('\n[tags /pfad/ 20]\n');
            editor.focus();
        }

        function insertShortcodeFolder() {
            if (!editor) return;
            editor.getDoc().replaceSelection('\n[folder /pfad/ 10]\n');
            editor.focus();
        }

        function insertShortcodeGallery() {
            if (!editor) return;
            editor.getDoc().replaceSelection('\n[gallery ordnername]\n');
            editor.focus();
        }

        function insertShortcodeBloglist() {
            if (!editor) return;
            editor.getDoc().replaceSelection('\n[bloglist /pfad/ 10]\n');
            editor.focus();
        }

        // Markdown-Syntax-Validierung
        function validateMarkdown() {
            const content = editor.getValue();
            const validateBtn = document.getElementById('validateMarkdownBtn');
            
            // Loading state
            validateBtn.disabled = true;
            validateBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Prüfe...';
            
            // Validierung durchführen
            setTimeout(() => {
                const results = performMarkdownValidation(content);
                
                validateBtn.disabled = false;
                validateBtn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Syntax prüfen';
                
                showValidationResults(results);
            }, 300);
        }
        
        function performMarkdownValidation(markdown) {
            const issues = [];
            const warnings = [];
            const suggestions = [];
            const lines = markdown.split('\n');
            
            let inCodeBlock = false;
            let inFrontMatter = false;
            const headingLevels = [];
            const containerStack = [];   // Zeilennummern offener ::: Container
            
            lines.forEach((line, index) => {
                const lineNum = index + 1;
                
                // Track code blocks
                if (line.trim().startsWith('```')) {
                    inCodeBlock = !inCodeBlock;
                    return;
                }
                
                // Track front matter
                if (line.trim() === '---') {
                    if (index === 0) {
                        inFrontMatter = true;
                    } else if (inFrontMatter) {
                        inFrontMatter = false;
                    }
                    return;
                }
                
                // Skip validation in code blocks and front matter
                if (inCodeBlock || inFrontMatter) return;

                // ::: Container (Pandoc/Quarto) – öffnende Zeile hat Text, schließende nur Doppelpunkte
                if (/^ {0,3}:{3,}\s*$/.test(line)) {
                    if (containerStack.length === 0) {
                        warnings.push(`Zeile ${lineNum}: Schließendes ::: ohne geöffneten Container`);
                    } else {
                        containerStack.pop();
                    }
                    return;
                }
                if (/^ {0,3}:{3,}\s*\S/.test(line)) {
                    containerStack.push(lineNum);
                    return;
                }
                
                // Check for StaticMD shortcodes (valid syntax, keine Warnung)
                if (line.match(/\[(pages|tags|folder|download|image|accordionstart|accordionstop|authstart|authstop)(\s|\])/)) {
                    return; // Shortcodes sind gültig, keine weitere Validierung
                }
                
                // Check for unmatched brackets in links
                const linkMatches = line.match(/\[([^\]]*)\]/g);
                if (linkMatches) {
                    linkMatches.forEach(match => {
                        // [!NOTE], [!TIP] … sind GitHub-Alerts, keine Links
                        if (/^\[![A-Za-z]+\]$/.test(match)) return;
                        if (!match.includes('](') && !line.includes('{#')) {
                            const nextChar = line[line.indexOf(match) + match.length];
                            if (nextChar !== '(' && nextChar !== '[') {
                                warnings.push(`Zeile ${lineNum}: Möglicherweise unvollständiger Link: ${match}`);
                            }
                        }
                    });
                }
                
                // Check for broken image syntax
                if (line.includes('![') && !line.match(/!\[[^\]]*\]\([^)]+\)/) && !line.match(/!\[[^\]]*\]\[[^\]]+\]/) && !line.includes('[image')) {
                    issues.push(`Zeile ${lineNum}: Unvollständige Bild-Syntax gefunden`);
                }
                
                // Check for multiple spaces (common formatting issue)
                if (line.match(/  +/) && !line.trim().startsWith('-') && !line.trim().startsWith('*')) {
                    warnings.push(`Zeile ${lineNum}: Mehrfache Leerzeichen gefunden (möglicherweise beabsichtigt für Zeilenumbruch)`);
                }
                
                // Check heading structure
                const headingMatch = line.match(/^(#{1,6})\s+(.+)/);
                if (headingMatch) {
                    const level = headingMatch[1].length;
                    headingLevels.push(level);
                    
                    // Check for missing space after #
                    if (!line.match(/^#{1,6}\s/)) {
                        issues.push(`Zeile ${lineNum}: Fehlender Leerzeichen nach # in Überschrift`);
                    }
                    
                    // Warn about skipping heading levels
                    if (headingLevels.length > 1) {
                        const prevLevel = headingLevels[headingLevels.length - 2];
                        if (level > prevLevel + 1) {
                            warnings.push(`Zeile ${lineNum}: Überschriften-Level übersprungen (H${prevLevel} → H${level})`);
                        }
                    }
                }
                
                // Check for unescaped special characters in potential issues
                // Aber NICHT in URLs, Links oder Bildern
                if (line.includes('_') && line.match(/\w_\w/) && !line.includes('`') && 
                    !line.match(/https?:\/\//) && !line.match(/\[.*\]\(.*_.*\)/) && !line.match(/!\[.*\]\(.*_.*\)/)) {
                    suggestions.push(`Zeile ${lineNum}: Unterstrich '_' könnte als Kursiv interpretiert werden`);
                }
                
                // Check for trailing spaces (except for intentional line breaks)
                if (line.endsWith(' ') && !line.endsWith('  ')) {
                    suggestions.push(`Zeile ${lineNum}: Einzelnes Leerzeichen am Zeilenende (kein Zeilenumbruch-Effekt)`);
                }
                
                // Check for empty links
                if (line.match(/\[\]\(/)) {
                    issues.push(`Zeile ${lineNum}: Leerer Link-Text gefunden`);
                }
                if (line.match(/\[[^\]]+\]\(\)/)) {
                    issues.push(`Zeile ${lineNum}: Link ohne URL gefunden`);
                }
                
                // Check for unmatched bold/italic markers
                const asterisks = (line.match(/\*/g) || []).length;
                if (asterisks % 2 !== 0 && !line.includes('*')) {
                    warnings.push(`Zeile ${lineNum}: Ungerade Anzahl von * (möglicherweise ungeschlossenes Bold/Italic)`);
                }
                
                // Check for missing alt text in images
                if (line.match(/!\[\]\(/)) {
                    warnings.push(`Zeile ${lineNum}: Bild ohne Alt-Text (für Barrierefreiheit empfohlen)`);
                }
                
                // Check for very long lines (readability)
                if (line.length > 120 && !line.trim().startsWith('|')) {
                    suggestions.push(`Zeile ${lineNum}: Sehr lange Zeile (${line.length} Zeichen) - erwäge Umbruch für bessere Lesbarkeit`);
                }
            });
            
            // Check if file ends with newline - nur als Info, kein Fehler
            if (markdown.length > 0 && !markdown.endsWith('\n')) {
                suggestions.push('Datei endet nicht mit Zeilenumbruch (empfohlen für Git/Unix-Systeme)');
            }
            
            containerStack.forEach(lineNum => {
                warnings.push(`Zeile ${lineNum}: Container ::: wird nicht geschlossen (endet am Dateiende)`);
            });

            // Check for multiple consecutive blank lines - toleranter
            // Erlaube bis zu 3 aufeinanderfolgende Leerzeilen (häufig gewollt für Struktur)
            if (markdown.match(/\n\n\n\n+/)) {
                suggestions.push('Sehr viele aufeinanderfolgende Leerzeilen gefunden (mehr als 3)');
            }
            
            return {
                hasErrors: issues.length > 0,
                hasWarnings: warnings.length > 0,
                hasSuggestions: suggestions.length > 0,
                issues: issues,
                warnings: warnings,
                suggestions: suggestions,
                summary: getSummary(issues.length, warnings.length, suggestions.length)
            };
        }
        
        function getSummary(errorCount, warningCount, suggestionCount) {
            if (errorCount === 0 && warningCount === 0 && suggestionCount === 0) {
                return '✓ Keine Probleme gefunden! Ihr Markdown sieht gut aus.';
            }
            
            const parts = [];
            if (errorCount > 0) parts.push(`${errorCount} Fehler`);
            if (warningCount > 0) parts.push(`${warningCount} Warnung(en)`);
            if (suggestionCount > 0) parts.push(`${suggestionCount} Verbesserungsvorschlag/-schläge`);
            
            return parts.join(', ') + ' gefunden.';
        }
        
        function showValidationResults(validation) {
            const alertClass = validation.hasErrors ? 'danger' : 
                              validation.hasWarnings ? 'warning' : 
                              validation.hasSuggestions ? 'info' : 'success';
            
            const iconClass = validation.hasErrors ? 'exclamation-triangle' : 
                             validation.hasWarnings ? 'exclamation-circle' : 
                             validation.hasSuggestions ? 'info-circle' : 'check-circle';
            
            let issuesHtml = '';
            if (validation.issues.length > 0) {
                issuesHtml += '<div class="alert alert-danger"><h6><i class="bi bi-exclamation-triangle me-2"></i>Fehler</h6><ul class="mb-0">';
                validation.issues.forEach(issue => {
                    issuesHtml += `<li>${escapeHtml(issue)}</li>`;
                });
                issuesHtml += '</ul></div>';
            }
            
            if (validation.warnings.length > 0) {
                issuesHtml += '<div class="alert alert-warning"><h6><i class="bi bi-exclamation-circle me-2"></i>Warnungen</h6><ul class="mb-0">';
                validation.warnings.forEach(warning => {
                    issuesHtml += `<li>${escapeHtml(warning)}</li>`;
                });
                issuesHtml += '</ul></div>';
            }
            
            if (validation.suggestions.length > 0) {
                issuesHtml += '<div class="alert alert-info"><h6><i class="bi bi-lightbulb me-2"></i>Verbesserungsvorschläge</h6><ul class="mb-0">';
                validation.suggestions.forEach(suggestion => {
                    issuesHtml += `<li>${escapeHtml(suggestion)}</li>`;
                });
                issuesHtml += '</ul></div>';
            }
            
            const modalHtml = `
                <div class="modal fade" id="validationModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="bi bi-${iconClass} text-${alertClass} me-2"></i>
                                    Markdown Syntax-Prüfung
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-${alertClass}">
                                    <strong>${validation.summary}</strong>
                                </div>
                                ${issuesHtml}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Schließen</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remove existing modal if present
            const existingModal = document.getElementById('validationModal');
            if (existingModal) existingModal.remove();
            
            // Add new modal and show it
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            new bootstrap.Modal(document.getElementById('validationModal')).show();
        }
        
        let editor;
        let currentView = 'editor';
        let timeRemaining = <?= $timeRemaining ?>;
        
        // CodeMirror Editor initialisieren
        document.addEventListener('DOMContentLoaded', function() {
            // Theme-Name für CodeMirror anpassen
            let cmTheme = '<?= htmlspecialchars($editorTheme) ?>';
            // spezielle Fälle behandeln, hier wg. Leerzeichen im Namen
            switch(cmTheme) {
                case 'solarized-light':
                    cmTheme = 'solarized light';
                    break;
                case 'solarized-dark':
                    cmTheme = 'solarized dark';
                    break;
            }
            
            editor = CodeMirror.fromTextArea(document.getElementById('contentEditor'), {
                mode: 'markdown',
                theme: cmTheme,
                lineNumbers: true,
                lineWrapping: true,
                autoCloseBrackets: true,
                matchBrackets: true,
                indentUnit: 4,
                tabSize: 4,
                hintOptions: {
                    hint: wordHint,
                    completeSingle: false,
                    closeCharacters: /[^\p{L}\p{N}_]/u
                },
                extraKeys: {
                    "Ctrl-Z": "undo",                    // Rückgängig
                    "Ctrl-Y": "redo",                    // Wiederherstellen
                    "Ctrl-Shift-Z": "redo",              // Alternative für Redo
                    "Ctrl-A": "selectAll",               // Alles markieren
                    "Ctrl-X": "cut",                     // Ausschneiden (meist Browser-Default)
                    "Ctrl-C": "copy",                    // Kopieren (meist Browser-Default)
                    "Ctrl-V": "paste",                   // Einfügen (meist Browser-Default)
                    "Ctrl-S": function() { saveInPlace(); },   // Speichern ohne den Editor zu verlassen
                    "Cmd-S": function() { saveInPlace(); },    // macOS
                    "F11": function() { toggleFullscreen(); },
                    "Esc": function() {                  // Vollbild verlassen (nur wenn aktiv)
                        if (!document.querySelector('.editor-container').classList.contains('fullscreen')) {
                            return CodeMirror.Pass;
                        }
                        toggleFullscreen();
                    },
                    "Ctrl-F": "find",
                    "Cmd-F": "find",  // macOS
                    "F3": "findNext",  // Weitersuchen (vorwärts)
                    "Ctrl-G": "findNext",
                    "Shift-F3": "findPrev",  // Weitersuchen (rückwärts)
                    "Ctrl-Shift-G": "findPrev",
                    "Ctrl-H": "replace",
                    "Cmd-Alt-F": "replace",  // macOS
                    "Ctrl-D": function(cm) { cm.execCommand("deleteLine"); },  // Zeile löschen
                    "Ctrl-/": function(cm) { cm.toggleComment(); },  // Kommentar umschalten
                    "Cmd-/": function(cm) { cm.toggleComment(); },  // macOS
                    "Alt-Up": function(cm) { moveLines(cm, -1); },          // Zeile(n) nach oben verschieben
                    "Alt-Down": function(cm) { moveLines(cm, 1); },         // Zeile(n) nach unten verschieben
                    "Shift-Alt-Up": function(cm) { duplicateLines(cm, -1); },   // Zeile(n) nach oben duplizieren
                    "Shift-Alt-Down": function(cm) { duplicateLines(cm, 1); },  // Zeile(n) nach unten duplizieren
                    "Ctrl-Space": function(cm) { cm.showHint({ minChars: 1 }); },  // Autovervollständigung manuell
                    // Markdown-spezifische Shortcuts
                    "Ctrl-B": function(cm) { insertMarkdown('**', '**'); },  // Bold
                    "Cmd-B": function(cm) { insertMarkdown('**', '**'); },   // Bold (macOS)
                    "Ctrl-I": function(cm) { insertMarkdown('*', '*'); },    // Italic
                    "Cmd-I": function(cm) { insertMarkdown('*', '*'); },     // Italic (macOS)
                    "Ctrl-K": function(cm) { insertMarkdown('`', '`'); },    // Inline Code
                    "Cmd-K": function(cm) { insertMarkdown('`', '`'); },     // Inline Code (macOS)
                    "Ctrl-L": function(cm) { insertMarkdown('[', '](url)'); },  // Link
                    "Cmd-L": function(cm) { insertMarkdown('[', '](url)'); },    // Link (macOS)
                    "Shift-Ctrl-D": function(cm) { duplicateLines(cm, 1); },  // Zeile duplizieren
                    "Ctrl-[": "indentLess",              // Einzug verringern
                    "Ctrl-]": "indentMore",              // Einzug erhöhen
                    "Ctrl-Alt-5": "indentLess",
                    "Ctrl-Alt-6": "indentMore",
                    "Cmd-Alt-5": "indentLess",
                    "Cmd-Alt-6": "indentMore",
                    "Tab": "indentMore",                 // Einzug mit Tab
                    "Shift-Tab": "indentLess",           // Einzug zurück
                    "Ctrl-K Ctrl-U": "toUpperCase",      // Großbuchstaben
                    "Ctrl-K Ctrl-L": "toLowerCase"      // Kleinbuchstaben
                }
            });

            // Autovervollständigung: Popup automatisch ab dem 4. Buchstaben
            editor.on('inputRead', function(cm, change) {
                if (cm.state.completionActive || change.origin !== '+input') return;
                if (cm.listSelections().length > 1) return;
                const cur = cm.getCursor();
                const line = cm.getLine(cur.line);
                const before = line.slice(0, cur.ch).match(/\p{L}[\p{L}\p{N}_]*$/u);
                const after = line.charAt(cur.ch);
                if (before && before[0].length >= AUTOCOMPLETE_MIN_CHARS && !/[\p{L}\p{N}_]/u.test(after)) {
                    cm.showHint();
                }
            });
            loadWordIndex();
            const recountDocWords = debounce(() => countDocWords(editor), 400);
            editor.on('changes', recountDocWords);

            // Zeilenende-Markierungen hinzufügen
            editor.on('renderLine', function(cm, line, element) {
                // Entferne existierende Pilcrows, falls vorhanden (bei Re-Render)
                const existingPilcrow = element.querySelector('.cm-pilcrow');
                if (existingPilcrow) {
                    existingPilcrow.remove();
                }
                
                // Füge Pilcrow-Zeichen am Ende jeder Zeile hinzu
                const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                const color = isDark ? 'rgba(128,128,128,0.5)' : 'rgba(128,128,128,0.5)';
                
                const pilcrow = document.createElement('span');
                // pilcrow.textContent = '¶';
                pilcrow.textContent = '↵'; 
                pilcrow.className = 'cm-pilcrow';
                pilcrow.style.cssText = `color: ${color}; font-size: 0.85em; margin-left: 2px; user-select: none; pointer-events: none;`;
                element.appendChild(pilcrow);
            });
            
            // Initiales Refresh, um alle Zeilen zu rendern
            setTimeout(() => {
                editor.refresh();
            }, 100);
            
            // Dark mode Anpassung für Pilcrow - bei Theme-Wechsel neu rendern
            const updatePilcrowColor = function() {
                editor.refresh(); // Erzwingt komplettes Re-Render aller Zeilen
            };
            
            // Theme-Wechsel überwachen
            const observer = new MutationObserver(updatePilcrowColor);
            observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });
            
            // Bei Scroll und Changes auch refreshen (falls Zeilen neu gerendert werden)
            editor.on('changes', function() {
                // Kurze Verzögerung, damit CodeMirror erst seine internen Updates macht
                setTimeout(() => editor.refresh(), 10);
            });

            // Drag&Drop-Upload für Bilder (jetzt nach Initialisierung)
            editor.getWrapperElement().addEventListener('drop', function(e) {
                e.preventDefault();
                if (!e.dataTransfer || !e.dataTransfer.files || e.dataTransfer.files.length === 0) return;
                const file = e.dataTransfer.files[0];
                const formData = new FormData();
                const csrfToken = document.querySelector('#editorForm [name="csrf_token"]')?.value ?? '';
                if (file.type.match(/^image\//)) {
                    formData.append('image', file);
                    formData.append('csrf_token', csrfToken);
                    fetch('/admin?action=upload_image', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.filename) {
                            const doc = editor.getDoc();
                            const tag = `[image ${data.filename} "Alt-Text" - 50%]`;
                            doc.replaceSelection(tag);
                            editor.focus();
                        } else {
                            alert('Upload fehlgeschlagen: ' + (data.error || 'Unbekannter Fehler'));
                        }
                    })
                    .catch(() => alert('Upload fehlgeschlagen.'));
                } else if (
                    file.type === 'application/pdf' ||
                    file.type === 'application/zip' ||
                    file.name.endsWith('.pdf') ||
                    file.name.endsWith('.zip')
                ) {
                    formData.append('file', file);
                    formData.append('csrf_token', csrfToken);
                    fetch('/admin?action=upload_file', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.filename) {
                            const doc = editor.getDoc();
                            const tag = `[download ${data.filename} "${file.name}"]`;
                            doc.replaceSelection(tag);
                            editor.focus();
                        } else {
                            alert('Upload fehlgeschlagen: ' + (data.error || 'Unbekannter Fehler'));
                        }
                    })
                    .catch(() => alert('Upload fehlgeschlagen.'));
                }
            });
            
            // Editor-Höhe auf volle verfügbare Höhe setzen
            const editorHeight = Math.max(500, window.innerHeight - 200);
            editor.setSize(null, editorHeight + 'px');
            
            // Auto-Preview bei Änderungen
            editor.on('change', debounce(updatePreview, 500));
            
            // Initial Preview laden
            updatePreview();
        });
        
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
            const syncButton = document.getElementById('syncScrollToggle');
            
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
                const editorHeight = Math.max(500, window.innerHeight - 200);
                editor.setSize(null, editorHeight + 'px');
                
                // Sync-Button verstecken
                syncButton.classList.add('d-none');
            } else if (view === 'preview') {
                document.getElementById('previewTab').classList.remove('btn-outline-secondary');
                document.getElementById('previewTab').classList.add('btn-primary');
                
                editorColumn.className = 'col-12 d-none';
                previewColumn.className = 'col-12';
                updatePreview();
                
                // Sync-Button verstecken
                syncButton.classList.add('d-none');
            } else if (view === 'split') {
                document.getElementById('splitTab').classList.remove('btn-outline-info');
                document.getElementById('splitTab').classList.add('btn-primary');
                
                editorColumn.className = 'col-6';
                previewColumn.className = 'col-6';
                const editorHeight = Math.max(500, window.innerHeight - 200);
                editor.setSize(null, editorHeight + 'px');
                updatePreview();
                
                // Sync-Button anzeigen und aktivieren
                syncButton.classList.remove('d-none');
                if (syncScrollEnabled) {
                    syncButton.classList.remove('btn-outline-success');
                    syncButton.classList.add('btn-success');
                }
                
                // Scroll-Listener aktivieren
                attachScrollListeners();
            }
            
            // CodeMirror refresh nach Layout-Änderung
            setTimeout(() => editor.refresh(), 50);
        }
        
        // Synchrones Scrollen zwischen Editor und Preview
        let syncScrollEnabled = true; // Standardmäßig aktiviert
        let isScrolling = false;
        let editorScrollListener = null;
        let previewScrollListener = null;
        
        function enableSyncScroll() {
            if (syncScrollEnabled) return;
            syncScrollEnabled = true;
            
            const syncButton = document.getElementById('syncScrollToggle');
            if (syncButton) {
                syncButton.classList.remove('btn-outline-success');
                syncButton.classList.add('btn-success');
            }
            
            attachScrollListeners();
        }
        
        function disableSyncScroll() {
            syncScrollEnabled = false;
            
            const syncButton = document.getElementById('syncScrollToggle');
            if (syncButton) {
                syncButton.classList.remove('btn-success');
                syncButton.classList.add('btn-outline-success');
            }
        }
        
        function attachScrollListeners() {
            const editorScroller = editor.getScrollerElement();
            const previewElement = document.getElementById('previewContent');
            
            // Editor scrollt → Preview scrollt mit
            editorScrollListener = function() {
                if (!syncScrollEnabled || isScrolling) return;
                isScrolling = true;
                
                const editorScrollInfo = editor.getScrollInfo();
                const scrollPercentage = editorScrollInfo.top / (editorScrollInfo.height - editorScrollInfo.clientHeight);
                
                const previewScrollHeight = previewElement.scrollHeight - previewElement.clientHeight;
                previewElement.scrollTop = scrollPercentage * previewScrollHeight;
                
                setTimeout(() => { isScrolling = false; }, 50);
            };
            
            // Preview scrollt → Editor scrollt mit
            previewScrollListener = function() {
                if (!syncScrollEnabled || isScrolling) return;
                isScrolling = true;
                
                const scrollPercentage = previewElement.scrollTop / (previewElement.scrollHeight - previewElement.clientHeight);
                
                const editorScrollInfo = editor.getScrollInfo();
                const editorScrollHeight = editorScrollInfo.height - editorScrollInfo.clientHeight;
                editor.scrollTo(0, scrollPercentage * editorScrollHeight);
                
                setTimeout(() => { isScrolling = false; }, 50);
            };
            
            editorScroller.addEventListener('scroll', editorScrollListener);
            previewElement.addEventListener('scroll', previewScrollListener);
        }
        
        // Sync Scroll Toggle Button
        document.getElementById('syncScrollToggle').addEventListener('click', function() {
            if (syncScrollEnabled) {
                disableSyncScroll();
            } else {
                enableSyncScroll();
            }
        });
        
        // Server-seitiges Preview mit vollständigem MarkdownParser + Shortcodes
        function updatePreview() {
            const content = editor.getValue();
            const previewElement = document.getElementById('previewContent');
            const fileRoute = document.getElementById('file').value || '/';
            
            // Loading-Indikator anzeigen
            previewElement.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2 text-muted"><?= __('admin.editor.preview_loading') ?></p></div>';
            
            // AJAX-Request an Preview-Endpoint
            fetch('/system/admin/preview.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    content: content,
                    route: fileRoute
                })
            })
            .then(response => {
                // Auch bei Fehler-Status versuchen, JSON zu lesen (für detaillierte Fehlermeldung)
                return response.json().then(data => {
                    if (!response.ok) {
                        data.httpStatus = response.status;
                        data.httpStatusText = response.statusText;
                    }
                    return data;
                });
            })
            .then(data => {
                if (data.success) {
                    previewElement.innerHTML = data.html;
                } else {
                    // Detaillierte Fehlermeldung anzeigen
                    let errorMsg = '<div class="alert alert-danger">';
                    errorMsg += '<h6 class="alert-heading"><i class="bi bi-exclamation-triangle me-2"></i>Preview Fehler</h6>';
                    errorMsg += '<p><strong>Error:</strong> ' + (data.error || 'Unknown error') + '</p>';
                    if (data.message) {
                        errorMsg += '<p><strong>Message:</strong> ' + data.message + '</p>';
                    }
                    if (data.file) {
                        errorMsg += '<p><small><strong>File:</strong> ' + data.file + ':' + data.line + '</small></p>';
                    }
                    if (data.trace) {
                        errorMsg += '<details><summary>Stack Trace</summary><pre style="font-size:10px; max-height:200px; overflow:auto;">' + data.trace + '</pre></details>';
                    }
                    errorMsg += '</div>';
                    previewElement.innerHTML = errorMsg;
                    console.error('Preview error:', data);
                }
            })
            .catch(error => {
                console.error('Preview fetch error:', error);
                previewElement.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><strong>Netzwerkfehler:</strong> ' + error.message + '<br><small>Bitte Browser-Konsole prüfen (F12)</small></div>';
            });
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
        
        // Code-Block einfügen
        function insertCodeBlock() {
            const doc = editor.getDoc();
            const cursor = doc.getCursor();
            const selection = doc.getSelection();
            
            if (selection) {
                const codeBlock = '```' + selection + '```';
                doc.replaceSelection(codeBlock);
            } else {
                const codeBlock = '``````';
                doc.replaceRange(codeBlock, cursor);
                doc.setCursor({line: cursor.line + 1, ch: 0});
            }
            
            editor.focus();
        }
        
        // Tabelle einfügen
        function insertTable() {
            const doc = editor.getDoc();
            const cursor = doc.getCursor();
            
            const table = [
                '| Spalte 1 | Spalte 2 | Spalte 3 |\n',
                '|----------|----------|----------|\n',
                '| Zeile 1  | Daten    | Daten    |\n',
                '| Zeile 2  | Daten    | Daten    |\n'
            ].join('');
            
            doc.replaceRange(table, cursor);
            
            editor.focus();
        }
        
        // Emoji einfügen
        function insertEmoji(emojiCode) {
            const doc = editor.getDoc();
            const cursor = doc.getCursor();
            
            doc.replaceRange(emojiCode + ' ', cursor);
            
            editor.focus();
            
            // Dropdown schließen
            const dropdown = bootstrap.Dropdown.getInstance(document.querySelector('.emoji-dropdown').previousElementSibling);
            if (dropdown) dropdown.hide();
            
            return false; // Verhindert Standard-Link-Verhalten
        }
        
        // ---------------------------------------------------------------
        // Text-Werkzeuge: Transformationen auf markiertem Text
        // ---------------------------------------------------------------
        const TEXT_TOOL_LOCALE = document.documentElement.lang || 'de';

        // Wörter für Bezeichner-Formate zerlegen (erkennt auch camelCase/PascalCase, snake_case, kebab-case)
        function splitIdentifierWords(text) {
            return text
                .replace(/([\p{Ll}\p{N}])(\p{Lu})/gu, '$1 $2')
                .replace(/(\p{Lu})(\p{Lu}\p{Ll})/gu, '$1 $2')
                .split(/[^\p{L}\p{N}]+/u)
                .filter(Boolean)
                .map(w => w.toLocaleLowerCase(TEXT_TOOL_LOCALE));
        }

        // Funktion zeilenweise anwenden, Einrückung und Zeilenende-Leerraum der Zeile bleiben erhalten
        function mapLinesKeepIndent(text, fn) {
            return text.split('\n').map(line => {
                const m = line.match(/^(\s*)(.*?)(\s*)$/su);
                return m[2] === '' ? line : m[1] + fn(m[2]) + m[3];
            }).join('\n');
        }

        function capitalizeFirst(word) {
            return word.charAt(0).toLocaleUpperCase(TEXT_TOOL_LOCALE) + word.slice(1);
        }

        // Zeilenoperationen: abschließenden Zeilenumbruch der Markierung erhalten
        function mapLineList(text, fn) {
            const trailingNewline = text.endsWith('\n');
            const lines = (trailingNewline ? text.slice(0, -1) : text).split('\n');
            return fn(lines).join('\n') + (trailingNewline ? '\n' : '');
        }

        const textTransforms = {
            upper: { lines: false, fn: t => t.toLocaleUpperCase(TEXT_TOOL_LOCALE) },
            lower: { lines: false, fn: t => t.toLocaleLowerCase(TEXT_TOOL_LOCALE) },
            sentence: {
                lines: false,
                fn: t => t.toLocaleLowerCase(TEXT_TOOL_LOCALE)
                    // Erster Buchstabe des Textes, jeder Zeile und nach Satzende (. ! ?)
                    .replace(/(^|\n|[.!?]\s+)([^\p{L}\n]*)(\p{L})/gu,
                        (m, pre, between, ch) => pre + between + ch.toLocaleUpperCase(TEXT_TOOL_LOCALE))
            },
            title: {
                lines: false,
                fn: t => t.replace(/\p{L}[\p{L}\p{M}\p{N}'’]*/gu,
                    w => capitalizeFirst(w.toLocaleLowerCase(TEXT_TOOL_LOCALE)))
            },
            camel: {
                lines: false,
                fn: t => mapLinesKeepIndent(t, s => splitIdentifierWords(s)
                    .map((w, i) => i === 0 ? w : capitalizeFirst(w)).join(''))
            },
            snake: { lines: false, fn: t => mapLinesKeepIndent(t, s => splitIdentifierWords(s).join('_')) },
            kebab: { lines: false, fn: t => mapLinesKeepIndent(t, s => splitIdentifierWords(s).join('-')) },
            sortLines: {
                lines: true,
                fn: t => {
                    const collator = new Intl.Collator(TEXT_TOOL_LOCALE, { sensitivity: 'base', numeric: true });
                    return mapLineList(t, lines => lines.slice().sort(collator.compare));
                }
            },
            uniqueLines: {
                lines: true,
                // Erstes Vorkommen bleibt; Leerzeilen bleiben unangetastet
                fn: t => mapLineList(t, lines => {
                    const seen = new Set();
                    return lines.filter(l => {
                        if (l.trim() === '') return true;
                        if (seen.has(l)) return false;
                        seen.add(l);
                        return true;
                    });
                })
            },
            removeEmptyLines: {
                lines: true,
                fn: t => mapLineList(t, lines => lines.filter(l => l.trim() !== ''))
            },
            cleanWhitespace: {
                lines: true,
                // Einrückung bleibt erhalten; mehrfache Leerzeichen/Tabs im Text -> ein Leerzeichen; Zeilenende getrimmt
                fn: t => t.split('\n').map(line => {
                    const indent = line.match(/^[ \t]*/)[0];
                    return indent + line.slice(indent.length)
                        .replace(/[\t  -   　 ]+/g, ' ')
                        .replace(/ +$/, '');
                }).join('\n').replace(/^[ \t]+$/gm, '')
            }
        };

        function applyTextTransform(name) {
            const transform = textTransforms[name];
            if (!transform || !editor) return;
            const doc = editor.getDoc();

            let ranges = doc.listSelections().map(r => {
                let from = CodeMirror.cmpPos(r.anchor, r.head) <= 0 ? r.anchor : r.head;
                let to = from === r.anchor ? r.head : r.anchor;

                if (CodeMirror.cmpPos(from, to) === 0) {
                    if (transform.lines) {
                        return null;
                    }
                    // Keine Markierung: Wort unter dem Cursor verwenden
                    const word = editor.findWordAt(from);
                    from = word.anchor;
                    to = word.head;
                } else if (transform.lines) {
                    // Zeilenoperationen immer auf ganze Zeilen ausdehnen
                    from = { line: from.line, ch: 0 };
                    if (to.ch === 0 && to.line > from.line) {
                        to = { line: to.line - 1, ch: doc.getLine(to.line - 1).length };
                    } else {
                        to = { line: to.line, ch: doc.getLine(to.line).length };
                    }
                }
                return { anchor: from, head: to };
            }).filter(Boolean);

            if (ranges.length === 0 || ranges.every(r => CodeMirror.cmpPos(r.anchor, r.head) === 0)) {
                showTextToolHint();
                return;
            }

            editor.operation(() => {
                doc.setSelections(ranges);
                const results = doc.getSelections().map(transform.fn);
                doc.replaceSelections(results, 'around', '+textTool');
            });
            editor.focus();
        }

        function showTextToolHint() {
            const btn = document.querySelector('.text-tools-dropdown').previousElementSibling;
            const tip = bootstrap.Tooltip.getOrCreateInstance(btn, {
                title: '<?= __('admin.editor.text_tools.no_selection') ?>',
                trigger: 'manual',
                placement: 'bottom'
            });
            tip.show();
            setTimeout(() => tip.dispose(), 2000);
            editor.focus();
        }

        document.querySelectorAll('[data-text-transform]').forEach(item => {
            item.addEventListener('click', e => {
                e.preventDefault();
                applyTextTransform(item.dataset.textTransform);
            });
        });

        // ---------------------------------------------------------------
        // Container-Blöcke & Alerts einfügen (Toolbar-Menü)
        // ---------------------------------------------------------------
        const BLOCK_TEXT = <?= json_encode([
            'content' => __('admin.editor.blocks.placeholder_content'),
            'left'    => __('admin.editor.blocks.placeholder_left'),
            'middle'  => __('admin.editor.blocks.placeholder_middle'),
            'right'   => __('admin.editor.blocks.placeholder_right'),
            'more'    => __('admin.editor.blocks.placeholder_more'),
            'fold'    => __('admin.editor.blocks.placeholder_fold'),
        ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
        const SEL_START = '\u0001', SEL_END = '\u0002';   // markieren den Text, der danach ausgewählt wird
        const ph = text => SEL_START + text + SEL_END;
        const quoteLines = text => text.split('\n').map(l => l.trim() === '' ? '>' : '> ' + l).join('\n');
        const alertBlock = type => sel => '> [!' + type + ']\n' + quoteLines(sel ?? ph(BLOCK_TEXT.content));

        // sel = markierter Text (ganze Zeilen) oder null
        const blockTemplates = {
            'alert-note': alertBlock('NOTE'),
            'alert-tip': alertBlock('TIP'),
            'alert-important': alertBlock('IMPORTANT'),
            'alert-warning': alertBlock('WARNING'),
            'alert-caution': alertBlock('CAUTION'),
            'alert-fold': sel => '> [!TIP]- ' + ph(BLOCK_TEXT.fold) + '\n' + quoteLines(sel ?? BLOCK_TEXT.content),
            'columns-2': sel => ':::: columns\n::: column\n' + (sel ?? ph(BLOCK_TEXT.left)) + '\n:::\n::: column\n'
                + BLOCK_TEXT.right + '\n:::\n::::',
            'columns-3': sel => ':::: columns\n::: column\n' + (sel ?? ph(BLOCK_TEXT.left)) + '\n:::\n::: column\n'
                + BLOCK_TEXT.middle + '\n:::\n::: column\n' + BLOCK_TEXT.right + '\n:::\n::::',
            'columns-40-60': sel => ':::: {.columns gap=2rem}\n::: {.column width="40%"}\n' + (sel ?? ph(BLOCK_TEXT.left))
                + '\n:::\n::: column\n' + BLOCK_TEXT.right + '\n:::\n::::',
            'box': sel => '::: {border}\n' + (sel ?? ph(BLOCK_TEXT.content)) + '\n:::',
            'box-center': sel => '::: {border align=center}\n' + (sel ?? ph(BLOCK_TEXT.content)) + '\n:::',
            'details': sel => '::: details ' + BLOCK_TEXT.more + '\n' + (sel ?? ph(BLOCK_TEXT.content)) + '\n:::'
        };

        function insertBlock(kind) {
            const template = blockTemplates[kind];
            if (!template || !editor) return;
            const doc = editor.getDoc();
            let from, to, selected = null;

            if (doc.somethingSelected()) {
                // Markierung auf ganze Zeilen ausdehnen und umschließen
                const r = doc.listSelections()[0];
                const start = CodeMirror.cmpPos(r.anchor, r.head) <= 0 ? r.anchor : r.head;
                const end = start === r.anchor ? r.head : r.anchor;
                from = { line: start.line, ch: 0 };
                to = (end.ch === 0 && end.line > start.line)
                    ? { line: end.line - 1, ch: doc.getLine(end.line - 1).length }
                    : { line: end.line, ch: doc.getLine(end.line).length };
                selected = doc.getRange(from, to);
            } else {
                const cur = doc.getCursor();
                const line = doc.getLine(cur.line);
                // Leere Zeile ersetzen, sonst hinter der aktuellen Zeile einfügen
                from = line.trim() === '' ? { line: cur.line, ch: 0 } : { line: cur.line, ch: line.length };
                to = { line: cur.line, ch: line.length };
            }

            // Block braucht Leerzeilen davor und danach
            let prefix = '';
            if (from.ch > 0) {
                prefix = '\n\n';
            } else if (from.line > 0 && doc.getLine(from.line - 1).trim() !== '') {
                prefix = '\n';
            }
            const suffix = (to.line < doc.lastLine() && doc.getLine(to.line + 1).trim() !== '') ? '\n' : '';

            let text = prefix + template(selected) + suffix;
            const selStart = text.indexOf(SEL_START);
            const selEnd = text.indexOf(SEL_END);
            text = text.replace(SEL_START, '').replace(SEL_END, '');

            editor.operation(() => {
                const base = doc.indexFromPos(from);
                doc.replaceRange(text, from, to, '+insertBlock');
                if (selStart >= 0 && selEnd > selStart) {
                    doc.setSelection(doc.posFromIndex(base + selStart), doc.posFromIndex(base + selEnd - 1));
                } else {
                    doc.setCursor(doc.posFromIndex(base + text.length - suffix.length));
                }
            });
            editor.focus();
        }

        document.querySelectorAll('[data-insert-block]').forEach(item => {
            item.addEventListener('click', e => {
                e.preventDefault();
                insertBlock(item.dataset.insertBlock);
            });
        });

        // ---------------------------------------------------------------
        // Zeilen verschieben / duplizieren (mehrere Zeilen & Mehrfachauswahl)
        // ---------------------------------------------------------------

        // Zusammenhängende Zeilenblöcke aller Markierungen (sortiert, angrenzende zusammengefasst)
        function getSelectedLineBlocks(cm) {
            const blocks = cm.listSelections().map(r => {
                const from = CodeMirror.cmpPos(r.anchor, r.head) <= 0 ? r.anchor : r.head;
                const to = from === r.anchor ? r.head : r.anchor;
                // Markierung, die am Anfang einer Zeile endet, schließt diese Zeile nicht ein
                const end = (to.ch === 0 && to.line > from.line) ? to.line - 1 : to.line;
                return { start: from.line, end: end };
            }).sort((a, b) => a.start - b.start);

            const merged = [];
            blocks.forEach(b => {
                const last = merged[merged.length - 1];
                if (last && b.start <= last.end + 1) {
                    last.end = Math.max(last.end, b.end);
                } else {
                    merged.push({ start: b.start, end: b.end });
                }
            });
            return merged;
        }

        function getLineBlockText(cm, block) {
            return cm.getRange({ line: block.start, ch: 0 }, { line: block.end, ch: cm.getLine(block.end).length });
        }

        function shiftSelection(range, offset) {
            return {
                anchor: { line: range.anchor.line + offset, ch: range.anchor.ch },
                head: { line: range.head.line + offset, ch: range.head.ch }
            };
        }

        // dir = -1 (hoch) / +1 (runter)
        function moveLines(cm, dir) {
            const blocks = getSelectedLineBlocks(cm);
            if (dir < 0 && blocks[0].start === 0) return;
            if (dir > 0 && blocks[blocks.length - 1].end >= cm.lastLine()) return;

            const selections = cm.listSelections();
            cm.operation(() => {
                // Zeilenanzahl bleibt pro Block gleich, daher beeinflussen sich die Blöcke nicht
                blocks.forEach(b => {
                    const text = getLineBlockText(cm, b);
                    if (dir < 0) {
                        const above = cm.getLine(b.start - 1);
                        cm.replaceRange(text + '\n' + above,
                            { line: b.start - 1, ch: 0 },
                            { line: b.end, ch: cm.getLine(b.end).length }, '+moveLine');
                    } else {
                        const below = cm.getLine(b.end + 1);
                        cm.replaceRange(below + '\n' + text,
                            { line: b.start, ch: 0 },
                            { line: b.end + 1, ch: below.length }, '+moveLine');
                    }
                });
                cm.setSelections(selections.map(r => shiftSelection(r, dir)));
            });
            cm.scrollIntoView(null, 40);
        }

        // dir = -1: Kopie oberhalb, Markierung bleibt oben; dir = +1: Kopie unterhalb, Markierung wandert mit
        function duplicateLines(cm, dir) {
            const blocks = getSelectedLineBlocks(cm);
            const selections = cm.listSelections();

            cm.operation(() => {
                // Von unten nach oben einfügen, damit Zeilennummern weiter oben gültig bleiben
                for (let i = blocks.length - 1; i >= 0; i--) {
                    const b = blocks[i];
                    cm.replaceRange(getLineBlockText(cm, b) + '\n', { line: b.start, ch: 0 }, null, '+duplicateLine');
                }

                cm.setSelections(selections.map(r => {
                    const firstLine = Math.min(r.anchor.line, r.head.line);
                    let offset = 0;
                    for (const b of blocks) {
                        const len = b.end - b.start + 1;
                        if (b.end < firstLine) {
                            offset += len;
                        } else {
                            if (dir > 0) offset += len;
                            break;
                        }
                    }
                    return shiftSelection(r, offset);
                }));
            });
            cm.scrollIntoView(null, 40);
        }

        // ---------------------------------------------------------------
        // Autovervollständigung mit Wörtern aller Seiten + aktuellem Text
        // ---------------------------------------------------------------
        const AUTOCOMPLETE_MIN_CHARS = 4;
        const AUTOCOMPLETE_MAX_ITEMS = 12;
        const WORD_PATTERN = /\p{L}[\p{L}\p{N}_]*/gu;
        let globalWordCounts = new Map();            // Wörter aller Seiten (Server)
        let docWordCounts = null;                    // Wörter des aktuellen Texts (null = neu zählen)

        function loadWordIndex() {
            fetch('/admin?action=word_index', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                .then(r => r.ok ? r.json() : Promise.reject(new Error('HTTP ' + r.status)))
                .then(data => {
                    globalWordCounts = new Map(Object.entries((data && data.words) || {}));
                })
                .catch(err => console.warn('Wortindex für Autovervollständigung nicht verfügbar:', err));
        }

        function countDocWords(cm) {
            const counts = new Map();
            for (const w of cm.getValue().normalize('NFC').match(WORD_PATTERN) || []) {
                if (w.length > AUTOCOMPLETE_MIN_CHARS) counts.set(w, (counts.get(w) || 0) + 1);
            }
            docWordCounts = counts;
        }

        // Nicht bei jedem Tastendruck zählen, sondern nach einer kurzen Tipp-Pause (siehe 'changes'-Handler)
        function getDocWordCounts(cm) {
            if (!docWordCounts) countDocWords(cm);
            return docWordCounts;
        }

        // Schreibweise des getippten Präfixes übernehmen (Satzanfang groß, ALLES GROSS)
        function adaptCase(word, prefix) {
            if (prefix.length > 1 && prefix === prefix.toLocaleUpperCase(TEXT_TOOL_LOCALE) && prefix !== prefix.toLocaleLowerCase(TEXT_TOOL_LOCALE)) {
                return word.toLocaleUpperCase(TEXT_TOOL_LOCALE);
            }
            const first = prefix.charAt(0);
            if (first === first.toLocaleUpperCase(TEXT_TOOL_LOCALE) && first !== first.toLocaleLowerCase(TEXT_TOOL_LOCALE)) {
                return capitalizeFirst(word);
            }
            return word;
        }

        function wordHint(cm, options) {
            const cur = cm.getCursor();
            const line = cm.getLine(cur.line);
            const isWordChar = ch => /[\p{L}\p{N}_]/u.test(ch);

            let start = cur.ch;
            while (start > 0 && isWordChar(line.charAt(start - 1))) start--;
            let end = cur.ch;
            while (end < line.length && isWordChar(line.charAt(end))) end++;

            const prefix = line.slice(start, cur.ch).normalize('NFC');
            const minChars = (options && options.minChars) || AUTOCOMPLETE_MIN_CHARS;
            if (prefix.length < minChars || !/^\p{L}/u.test(prefix)) return null;

            const prefixLower = prefix.toLocaleLowerCase(TEXT_TOOL_LOCALE);
            const currentWord = line.slice(start, end).normalize('NFC');
            // Schreibvarianten (Wolken/wolken) zusammenfassen: Summe der Häufigkeiten, häufigste Schreibweise gewinnt
            const groups = new Map();

            const collect = (counts, isDoc) => {
                counts.forEach((count, word) => {
                    if (word.length <= prefix.length) return;
                    const lower = word.toLocaleLowerCase(TEXT_TOOL_LOCALE);
                    if (!lower.startsWith(prefixLower)) return;
                    // Das gerade getippte Wort selbst nicht mitzählen
                    if (isDoc && word === currentWord) count--;
                    if (count <= 0) return;
                    const group = groups.get(lower) || { total: 0, variants: new Map() };
                    group.total += count;
                    group.variants.set(word, (group.variants.get(word) || 0) + count);
                    groups.set(lower, group);
                });
            };
            collect(globalWordCounts, false);
            collect(getDocWordCounts(cm), true);

            const list = Array.from(groups.values())
                .map(g => {
                    const best = Array.from(g.variants.entries()).sort((a, b) => b[1] - a[1])[0][0];
                    return [adaptCase(best, prefix), g.total];
                })
                .sort((a, b) => b[1] - a[1] || a[0].localeCompare(b[0], TEXT_TOOL_LOCALE))
                .slice(0, AUTOCOMPLETE_MAX_ITEMS)
                .map(([text]) => text);

            if (list.length === 0) return null;
            return {
                list: list,
                from: CodeMirror.Pos(cur.line, start),
                to: CodeMirror.Pos(cur.line, cur.ch)
            };
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
            // Bereits von CodeMirror behandelt (z. B. Autovervollständigung schließen)
            if (e.defaultPrevented) return;
            if (e.key === 'Escape') {
                const container = document.querySelector('.editor-container');
                if (container.classList.contains('fullscreen')) {
                    toggleFullscreen();
                }
            }
        });
        
        // Window resize handler - Editor-Höhe anpassen
        window.addEventListener('resize', function() {
            if (editor && !document.querySelector('.editor-container').classList.contains('fullscreen')) {
                const editorHeight = Math.max(500, window.innerHeight - 200);
                editor.setSize(null, editorHeight + 'px');
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
                console.log('<?php echo __('admin.editor.toolbar.auto_save_placeholder'); ?>');
            }
        }, 30000);
        
        // Datei löschen
        function confirmDelete(fileName) {
            document.getElementById('deleteFileName').textContent = fileName;
            
            // Delete-Form erstellen
            const deleteForm = document.getElementById('deleteForm');
            deleteForm.querySelector('input[name="file"]').value = fileName;
            
            // Return URL: Zurück zur ursprünglichen Frontend-Seite oder Editor return_url
            const returnUrlInput = deleteForm.querySelector('input[name="return_url"]');
            if (returnUrlInput) {
                // Nutze die return_url aus dem aktuellen Request, falls vorhanden
                const urlParams = new URLSearchParams(window.location.search);
                const currentReturnUrl = urlParams.get('return_url');
                returnUrlInput.value = currentReturnUrl || '/admin?action=files';
            }
            
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
    
    <!-- Lösch-Bestätigung Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                        <?= __('admin.delete_modal.title') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><?= __('admin.editor.delete_confirm') ?> <strong id="deleteFileName"></strong>?</p>
                    <p class="text-muted"><?= __('admin.delete_modal.warning') ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('admin.common.cancel') ?></button>
                    <button type="button" class="btn btn-danger" id="executeDeleteBtn">
                        <i class="bi bi-trash me-1"></i> <?= __('admin.common.delete') ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Hidden Delete Form -->
    <!-- Toast für Speichern ohne Verlassen (Strg+S / Cmd+S) – über dem Vollbild-Editor (z-index 9999) -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 10060;">
        <div id="saveToastSuccess" class="toast align-items-center border-0 text-bg-success" role="status" aria-live="polite" aria-atomic="true" data-bs-delay="2500">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill"></i>
                    <span class="save-toast-text"></span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="<?= __('admin.editor.toast_close') ?>"></button>
            </div>
        </div>
        <div id="saveToastError" class="toast align-items-center border-0 text-bg-danger" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="8000">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span class="save-toast-text"></span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="<?= __('admin.editor.toast_close') ?>"></button>
            </div>
        </div>
    </div>

    <form method="POST" action="/admin?action=delete" id="deleteForm" style="display: none;">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($this->auth->generateCSRFToken()) ?>">
        <input type="hidden" name="file" value="">
        <input type="hidden" name="return_url" value="">
    </form>
    
    <!-- Path Validation Modal -->
    <div class="modal fade" id="pathValidationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-circle text-warning me-2"></i>
                        <?= __('admin.editor.path_validation_title') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="pathExistsWarning" class="alert alert-danger d-none">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong><?= __('admin.editor.path_exists') ?></strong>
                        <p class="mb-0 mt-2"><?= __('admin.editor.path_exists_message') ?></p>
                    </div>
                    
                    <div id="pathNewConfirm" class="alert alert-info d-none">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong><?= __('admin.editor.path_new') ?></strong>
                        <p class="mb-0 mt-2"><?= __('admin.editor.path_new_message') ?>:</p>
                        <code class="d-block mt-2 p-2 border rounded" id="newPathDisplay"></code>
                    </div>
                    
                    <div id="pathSuggestions" class="d-none">
                        <p class="mb-2"><?= __('admin.editor.similar_paths') ?>:</p>
                        <ul id="pathSuggestionsList" class="list-unstyled"></ul>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <?= __('admin.common.cancel') ?>
                    </button>
                    <button type="button" class="btn btn-primary" id="proceedWithSave">
                        <i class="bi bi-check-circle me-1"></i>
                        <?= __('admin.editor.create_anyway') ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script nonce="<?= $nonce ?>">
        let validationModal = null;
        let formSubmitPending = false;
        let originalPath = '<?= htmlspecialchars($file) ?>';
        let pendingSaveMode = 'exit';   // 'exit' = Button (speichern & verlassen), 'stay' = Strg+S
        let saveInFlight = false;
        let saveQueued = false;
        const SAVE_TEXT = <?= json_encode([
            'saved'           => __('admin.editor.save_toast'),
            'failed'          => __('admin.editor.save_failed'),
            'sessionExpired'  => __('admin.editor.session_expired'),
            'fileRequired'    => __('admin.errors.filename_required'),
        ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;

        // Strg+S / Cmd+S: speichern ohne den Editor zu verlassen
        function saveInPlace() {
            const currentPath = document.querySelector('input[name="file"]').value.trim();
            if (!currentPath) {
                showSaveToast(SAVE_TEXT.fileRequired, false);
                return;
            }
            // Neue oder geänderte Datei: erst Pfad bestätigen lassen (gleicher Dialog wie beim Button)
            if (currentPath !== originalPath) {
                pendingSaveMode = 'stay';
                validatePathBeforeSave();
                return;
            }
            ajaxSave();
        }

        async function ajaxSave() {
            if (saveInFlight) {
                saveQueued = true;   // während des Speicherns erneut gedrückt → danach nochmal speichern
                return;
            }
            saveInFlight = true;
            if (typeof editor !== 'undefined' && editor) {
                editor.save();
            }
            const form = document.getElementById('editorForm');
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                const isJson = (response.headers.get('Content-Type') || '').includes('application/json');
                if (!isJson) {
                    // z. B. Weiterleitung auf die Login-Seite
                    throw new Error(SAVE_TEXT.sessionExpired);
                }
                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.error || ('HTTP ' + response.status));
                }

                // Gespeicherter Pfad ist ab jetzt der aktuelle (neue Datei / umbenannt)
                if (data.file !== originalPath) {
                    originalPath = data.file;
                    const url = new URL(window.location.href);
                    url.searchParams.set('action', 'edit');
                    url.searchParams.set('file', data.file);
                    history.replaceState(null, '', url);
                }
                if (typeof data.sessionRemaining === 'number' && typeof timeRemaining !== 'undefined') {
                    timeRemaining = data.sessionRemaining;
                }
                const time = new Date().toLocaleTimeString(document.documentElement.lang || 'de',
                    { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
                showSaveToast(SAVE_TEXT.saved.replace('{time}', time), true);
            } catch (err) {
                showSaveToast(SAVE_TEXT.failed + ': ' + err.message, false);
            } finally {
                saveInFlight = false;
                if (saveQueued) {
                    saveQueued = false;
                    ajaxSave();
                }
            }
        }

        // Getrennte Toasts für Erfolg (2,5 s) und Fehler (8 s); erneutes show() startet die Anzeigezeit neu
        function showSaveToast(message, success) {
            const showEl = document.getElementById(success ? 'saveToastSuccess' : 'saveToastError');
            const otherEl = document.getElementById(success ? 'saveToastError' : 'saveToastSuccess');
            bootstrap.Toast.getOrCreateInstance(otherEl).hide();
            showEl.querySelector('.save-toast-text').textContent = message;
            bootstrap.Toast.getOrCreateInstance(showEl).show();
        }

        // Strg+S / Cmd+S auch außerhalb von CodeMirror (z. B. in den Metadaten-Feldern) abfangen
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && !e.altKey && !e.shiftKey && e.key.toLowerCase() === 's') {
                e.preventDefault();
                if (e.target.closest && e.target.closest('.CodeMirror')) {
                    return; // CodeMirror-Keymap hat bereits gespeichert
                }
                saveInPlace();
            }
        });
        
        // Helper function to submit form (used by Ctrl-S and submit button)
        function submitEditorForm() {
            // Sync CodeMirror content back to textarea
            if (typeof editor !== 'undefined' && editor) {
                editor.save();
            }
            // Trigger form submit event (will be caught by our validator)
            const form = document.getElementById('editorForm');
            const submitEvent = new Event('submit', { cancelable: true, bubbles: true });
            form.dispatchEvent(submitEvent);
        }
        
        // Form submit handler - validate path for new or changed files
        document.addEventListener('DOMContentLoaded', function() {
            const editorForm = document.getElementById('editorForm');
            const fileInput = document.querySelector('input[name="file"]');

            validationModal = new bootstrap.Modal(document.getElementById('pathValidationModal'));

            editorForm.addEventListener('submit', function(e) {
                const currentPath = fileInput.value.trim();

                // Sync CodeMirror content
                if (typeof editor !== 'undefined' && editor) {
                    editor.save();
                }

                // Skip validation if already confirmed
                if (formSubmitPending) {
                    formSubmitPending = false;
                    return true;
                }

                // Validate if path has changed or is new
                if (currentPath !== originalPath) {
                    e.preventDefault();
                    pendingSaveMode = 'exit';
                    validatePathBeforeSave();
                    return false;
                }
            });

            // Wire up buttons whose onclick is blocked by CSP (no 'unsafe-inline' in script-src)
            document.getElementById('proceedWithSave').addEventListener('click', proceedWithSave);

            document.getElementById('cancelEditBtn').addEventListener('click', cancelEdit);

            const executeDeleteBtn = document.getElementById('executeDeleteBtn');
            if (executeDeleteBtn) {
                executeDeleteBtn.addEventListener('click', executeDelete);
            }

            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
            if (confirmDeleteBtn) {
                confirmDeleteBtn.addEventListener('click', function() {
                    confirmDelete(this.dataset.deleteFile);
                });
            }
        });
        
        // Validate path and show modal if needed
        function validatePathBeforeSave() {
            const fileInput = document.querySelector('input[name="file"]');
            const path = fileInput.value.trim();
            
            if (!path) {
                alert('<?= __('admin.errors.filename_required') ?>');
                return;
            }
            
            const url = '/admin?action=validate_path&path=' + encodeURIComponent(path);
            
            // Call validation endpoint
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        // File already exists
                        showPathExistsWarning(path);
                    } else {
                        // Path is new
                        showNewPathConfirmation(path);
                    }
                })
                .catch(error => {
                    console.error('Path validation error:', error);
                    // On error, allow submit
                    submitFormDirectly();
                });
        }
        
        // Show warning that file already exists
        function showPathExistsWarning(path) {
            document.getElementById('pathExistsWarning').classList.remove('d-none');
            document.getElementById('pathNewConfirm').classList.add('d-none');
            document.getElementById('pathSuggestions').classList.add('d-none');
            document.getElementById('proceedWithSave').disabled = true;
            
            validationModal.show();
        }
                
        // Show confirmation for completely new path
        function showNewPathConfirmation(path) {
            document.getElementById('pathExistsWarning').classList.add('d-none');
            document.getElementById('pathNewConfirm').classList.remove('d-none');
            document.getElementById('newPathDisplay').textContent = path;
            document.getElementById('pathSuggestions').classList.add('d-none');
            document.getElementById('proceedWithSave').disabled = false;
            
            validationModal.show();
        }
        
        // Proceed with save after confirmation
        function proceedWithSave() {
            if (pendingSaveMode === 'stay') {
                pendingSaveMode = 'exit';
                closeValidationModal();
                ajaxSave();
                return;
            }
            formSubmitPending = true;
            if (typeof editor !== 'undefined' && editor) {
                editor.save();
            }
            document.getElementById('editorForm').submit();
        }
        
        // Dialog schließen – auch wenn er gerade noch einblendet (dann ignoriert Bootstrap hide())
        function closeValidationModal() {
            const modalEl = document.getElementById('pathValidationModal');
            const hideWhenShown = () => validationModal.hide();
            // Läuft noch die Einblend-Animation, wird nach deren Ende geschlossen
            modalEl.addEventListener('shown.bs.modal', hideWhenShown, { once: true });
            modalEl.addEventListener('hidden.bs.modal', () => modalEl.removeEventListener('shown.bs.modal', hideWhenShown), { once: true });
            validationModal.hide();
        }

        // Submit form directly without validation
        function submitFormDirectly() {
            if (pendingSaveMode === 'stay') {
                pendingSaveMode = 'exit';
                ajaxSave();
                return;
            }
            formSubmitPending = true;
            document.getElementById('editorForm').submit();
        }
        
        // Escape HTML for safe display
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Delete-Funktion ausführen
        function executeDelete() {
            document.getElementById('deleteForm').submit();
        }
        
        // Abbrechen-Funktion - zurück zur ursprünglichen Seite
        function cancelEdit() {
            const returnUrl = '<?= htmlspecialchars($_GET['return_url'] ?? '') ?>';
            if (returnUrl) {
                // Entferne saved=1 und andere Success-Parameter aus der URL
                const cleanUrl = returnUrl.replace(/[?&]saved=1(&|$)/g, '$1')
                                          .replace(/[?&]message=[^&]*(&|$)/g, '$1')
                                          .replace(/[?&]$/g, '')  // Trailing ? oder & entfernen
                                          .replace(/\?&/g, '?');   // ?& zu ? korrigieren
                window.location.href = cleanUrl;
            } else if ('<?= htmlspecialchars($file) ?>') {
                window.location.href = '/<?= htmlspecialchars($file) ?>';
            } else {
                window.location.href = '/admin';
            }
        }
        
        // Theme-Wechsel-Funktionalität (für zukünftige Erweiterungen)
        function changeEditorTheme(themeName) {
            if (editor) {
                editor.setOption('theme', themeName);
                
                // Theme-spezifische Anpassungen
                const editorElement = document.querySelector('.CodeMirror');
                if (editorElement) {
                    // Entferne alte Theme-Klassen
                    editorElement.classList.remove('cm-s-elegant', 'cm-s-monokai', 'cm-s-solarized', 'cm-s-material');
                    
                    // Füge neue Theme-Klasse hinzu
                    switch(themeName) {
                        case 'elegant':
                            editorElement.classList.add('cm-s-elegant');
                            break;
                        case 'monokai':
                            editorElement.classList.add('cm-s-monokai');
                            break;
                        case 'solarized-light':
                            editorElement.classList.add('cm-s-solarized');
                            break;
                        case 'solarized-dark':
                            editorElement.classList.add('cm-s-solarized');
                            break;
                        case 'material':
                            editorElement.classList.add('cm-s-material');
                            break;
                    }
                }
                
                // Editor refresh nach Theme-Änderung
                setTimeout(() => editor.refresh(), 50);
            }
        }
        
        // Theme toggle functionality (shared across all admin pages)
        const themeToggle = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');
        const htmlElement = document.documentElement;
        
        // Load saved theme or default to light
        const savedTheme = localStorage.getItem('adminTheme') || 'light';
        htmlElement.setAttribute('data-bs-theme', savedTheme);
        updateThemeIcon(savedTheme);
        
        themeToggle.addEventListener('click', function() {
            const currentTheme = htmlElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            htmlElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('adminTheme', newTheme);
            updateThemeIcon(newTheme);
        });
        
        function updateThemeIcon(theme) {
            if (theme === 'dark') {
                themeIcon.classList.remove('bi-moon-fill');
                themeIcon.classList.add('bi-sun-fill');
            } else {
                themeIcon.classList.remove('bi-sun-fill');
                themeIcon.classList.add('bi-moon-fill');
            }
        }
    </script>
    <script nonce="<?= $nonce ?>">
        // CSP has nonce-based script-src without 'unsafe-inline', which blocks onclick attributes.
        // Re-register every remaining onclick attribute as an addEventListener so toolbar buttons
        // and emoji links work. 'unsafe-eval' is permitted in the admin CSP for CodeMirror.
        document.querySelectorAll('[onclick]').forEach(function(el) {
            var handler = el.getAttribute('onclick');
            el.removeAttribute('onclick');
            el.addEventListener('click', new Function(handler));
        });
    </script>
</body>
</html>