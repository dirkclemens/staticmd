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
$editorTheme = $settings['editor_theme'] ?? 'github';
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
    <title><?= __('admin.brand') ?> - <?= __('admin.common.editor') ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- CodeMirror CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/codemirror.min.css">
    
    <!-- CodeMirror Themes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/theme/github.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/theme/monokai.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/theme/solarized.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/theme/material.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/addon/dialog/dialog.min.css">
    
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
                </div>
                
                <div class="dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
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
                        <li><a class="dropdown-item" href="/admin?action=logout">
                            <i class="bi bi-box-arrow-right me-2"></i><?= __('admin.common.logout') ?>
                        </a></li>
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
                                        onclick="confirmDelete('<?= htmlspecialchars($file) ?>')">
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
                        </div>
                        
                        <!-- Editor Toolbar -->
                        <div class="toolbar">
                            <!-- Text-Formatierung -->
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('**', '**')" title="<?php echo \StaticMD\Core\I18n::t('admin.editor.toolbar.bold'); ?>">
                                <i class="bi bi-type-bold"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('*', '*')" title="<?php echo \StaticMD\Core\I18n::t('admin.editor.toolbar.italic'); ?>">
                                <i class="bi bi-type-italic"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('~~', '~~')" title="<?php echo \StaticMD\Core\I18n::t('admin.editor.toolbar.strikethrough'); ?>">
                                <i class="bi bi-type-strikethrough"></i>
                            </button>
                            
                            <div class="vr mx-2"></div>
                            
                            <!-- Überschriften -->
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('# ', '')" title="<?php echo \StaticMD\Core\I18n::t('admin.editor.toolbar.heading_h1'); ?>">
                                    <i class="bi bi-type-h1"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('## ', '')" title="<?php echo \StaticMD\Core\I18n::t('admin.editor.toolbar.heading_h2'); ?>">
                                    <i class="bi bi-type-h2"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('### ', '')" title="<?php echo \StaticMD\Core\I18n::t('admin.editor.toolbar.heading_h3'); ?>">
                                    <i class="bi bi-type-h3"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('#### ', '')" title="<?php echo \StaticMD\Core\I18n::t('admin.editor.toolbar.heading_h4'); ?>">
                                    <span style="font-size: 0.75em; font-weight: bold;">H4</span>
                                </button>
                            </div>
                            
                            <div class="vr mx-2"></div>
                            
                            <!-- Listen -->
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('- ', '')" title="<?php echo \StaticMD\Core\I18n::t('admin.editor.toolbar.list_unordered'); ?>">
                                <i class="bi bi-list-ul"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('1. ', '')" title="<?php echo \StaticMD\Core\I18n::t('admin.editor.toolbar.list_ordered'); ?>">
                                <i class="bi bi-list-ol"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('- [ ]', '')" title="<?php echo \StaticMD\Core\I18n::t('admin.editor.toolbar.checklist'); ?>">
                                <i class="bi bi-list-check"></i>
                            </button>
                            
                            <div class="vr mx-2"></div>
                            
                            <!-- Links & Medien -->
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('[', '](url)')" title="<?php echo \StaticMD\Core\I18n::t('admin.editor.toolbar.link'); ?>">
                                <i class="bi bi-link"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('![Alt-Text](', ')')" title="<?php echo \StaticMD\Core\I18n::t('admin.editor.toolbar.image'); ?>">
                                <i class="bi bi-image"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertDownloadTag()" title="<?php echo \StaticMD\Core\I18n::t('admin.editor.toolbar.download_link'); ?>">
                                <i class="bi bi-file-earmark-arrow-down"></i>
                            </button>
                            
                            <div class="vr mx-2"></div>
                            
                            <!-- Code & Spezial -->
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('`', '`')" title="<?php echo \StaticMD\Core\I18n::t('admin.editor.toolbar.inline_code'); ?>">
                                <i class="bi bi-code"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertCodeBlock()" title="<?php echo \StaticMD\Core\I18n::t('admin.editor.toolbar.code_block'); ?>">
                                <i class="bi bi-code-square"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('> ', '')" title="<?php echo \StaticMD\Core\I18n::t('admin.editor.toolbar.quote'); ?>">
                                <i class="bi bi-quote"></i>
                            </button>
                            
                            <div class="vr mx-2"></div>
                            
                            <!-- Struktur -->
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertTable()" title="<?php echo \StaticMD\Core\I18n::t('admin.editor.toolbar.table'); ?>">
                                <i class="bi bi-table"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMarkdown('---', '')" title="<?php echo \StaticMD\Core\I18n::t('admin.editor.toolbar.horizontal_line'); ?>">
                                <i class="bi bi-hr"></i>
                            </button>
                            
                            <div class="vr mx-2"></div>
                            <!--Sprungmarke-->
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertAnchor()" title="<?php echo \StaticMD\Core\I18n::t('admin.editor.toolbar.anchor'); ?>">
                                <i class="bi bi-bookmark"></i> 
                            </button>                            
                            <!-- Accordion -->
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertAccordion()" title="<?php echo \StaticMD\Core\I18n::t('admin.editor.toolbar.accordion'); ?>">
                                <i class="bi bi-arrows-collapse"></i>
                            </button>

                            <!-- Emojis -->
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" title="<?php echo \StaticMD\Core\I18n::t('admin.editor.toolbar.emoji'); ?>">
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
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cancelEdit()">
                                    <i class="bi bi-x-circle me-1"></i> <?= __('admin.common.cancel') ?>
                                </button>
                                <button type="submit" class="btn btn-success btn-sm">
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
        // Accordion-Shortcode einfügen
        function insertAccordion() {
            if (!editor) return;
            const doc = editor.getDoc();
            const accordionText = '\n[accordionstart id "Titel"]\n\n[accordionstop id]\n';
            doc.replaceSelection(accordionText);
            editor.focus();
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
                extraKeys: {
                    "Ctrl-Z": "undo",                    // Rückgängig
                    "Ctrl-Y": "redo",                    // Wiederherstellen
                    "Ctrl-Shift-Z": "redo",              // Alternative für Redo
                    "Ctrl-A": "selectAll",               // Alles markieren
                    "Ctrl-X": "cut",                     // Ausschneiden (meist Browser-Default)
                    "Ctrl-C": "copy",                    // Kopieren (meist Browser-Default)
                    "Ctrl-V": "paste",                   // Einfügen (meist Browser-Default)
                    "Ctrl-S": function() { submitEditorForm(); },
                    "Cmd-S": function() { submitEditorForm(); },  // macOS
                    "F11": function() { toggleFullscreen(); },
                    "Esc": function() { toggleFullscreen(); },  // Vollbild verlassen
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
                    "Alt-Up": "swapLineUp",  // Zeile nach oben verschieben
                    "Alt-Down": "swapLineDown",  // Zeile nach unten verschieben
                    "Ctrl-Space": "autocomplete",  // Autovervollständigung
                    // Markdown-spezifische Shortcuts
                    "Ctrl-B": function(cm) { insertMarkdown('**', '**'); },  // Bold
                    "Cmd-B": function(cm) { insertMarkdown('**', '**'); },   // Bold (macOS)
                    "Ctrl-I": function(cm) { insertMarkdown('*', '*'); },    // Italic
                    "Cmd-I": function(cm) { insertMarkdown('*', '*'); },     // Italic (macOS)
                    "Ctrl-K": function(cm) { insertMarkdown('`', '`'); },    // Inline Code
                    "Cmd-K": function(cm) { insertMarkdown('`', '`'); },     // Inline Code (macOS)
                    "Ctrl-L": function(cm) { insertMarkdown('[', '](url)'); },  // Link
                    "Cmd-L": function(cm) { insertMarkdown('[', '](url)'); },    // Link (macOS)
                    "Ctrl-Shift-D": "duplicateLine",     // Zeile duplizieren
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

            // Drag&Drop-Upload für Bilder (jetzt nach Initialisierung)
            editor.getWrapperElement().addEventListener('drop', function(e) {
                e.preventDefault();
                if (!e.dataTransfer || !e.dataTransfer.files || e.dataTransfer.files.length === 0) return;
                const file = e.dataTransfer.files[0];
                const formData = new FormData();
                if (file.type.match(/^image\//)) {
                    formData.append('image', file);
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
                const editorHeight = Math.max(500, window.innerHeight - 200);
                editor.setSize(null, editorHeight + 'px');
                updatePreview();
            }
            
            // CodeMirror refresh nach Layout-Änderung
            setTimeout(() => editor.refresh(), 50);
        }
        
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
                console.log('<?php echo \StaticMD\Core\I18n::t('admin.editor.toolbar.auto_save_placeholder'); ?>');
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
                    <button type="button" class="btn btn-danger" onclick="executeDelete()">
                        <i class="bi bi-trash me-1"></i> <?= __('admin.common.delete') ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Hidden Delete Form -->
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
                        <code class="d-block mt-2 p-2 bg-light border rounded" id="newPathDisplay"></code>
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
                    <button type="button" class="btn btn-primary" id="proceedWithSave" onclick="proceedWithSave()">
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
        const originalPath = '<?= htmlspecialchars($file) ?>';
        
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
                    validatePathBeforeSave();
                    return false;
                }
            });
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
            formSubmitPending = true;
            validationModal.hide();
            document.getElementById('editorForm').submit();
        }
        
        // Submit form directly without validation
        function submitFormDirectly() {
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
                    editorElement.classList.remove('cm-s-github', 'cm-s-monokai', 'cm-s-solarized', 'cm-s-material');
                    
                    // Füge neue Theme-Klasse hinzu
                    switch(themeName) {
                        case 'github':
                            editorElement.classList.add('cm-s-github');
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
    </script>
</body>
</html>