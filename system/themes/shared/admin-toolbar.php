<?php
/**
 * Shared Admin Toolbar
 * Nur sichtbar wenn Admin eingeloggt ist
 */
?>
    <div class="admin-toolbar">
        <div class="btn-group-vertical" role="group">
        <?php if (!empty($is_admin_logged_in)): ?>
            <?php
                $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
                $fallbackRoute = trim((string)$requestPath, '/');
                $editRoute = $content['route'] ?? $fallbackRoute;
                if ($editRoute === '') {
                    $editRoute = 'index';
                }
            ?>
            <!-- Edit Page Button -->
            <a href="/admin?action=edit&file=<?= urlencode($editRoute) ?>&return_url=<?= urlencode($_SERVER['REQUEST_URI']) ?>" 
                class="btn btn-warning rounded-circle shadow" title="<?= \StaticMD\Core\I18n::t('admin.toolbar.edit_page') ?>">
                <i class="bi bi-pencil"></i>
            </a>
            <!-- New Page Button -->
            <a href="/admin?action=new&prefill_path=<?= urlencode($_SERVER['REQUEST_URI']) ?>&return_url=<?= urlencode($_SERVER['REQUEST_URI']) ?>" 
                class="btn btn-success rounded-circle shadow" title="<?= \StaticMD\Core\I18n::t('admin.toolbar.new_page') ?>">
                <i class="bi bi-file-earmark-plus"></i>
            </a>                
            <!-- Save Page Button -->
            <button id="savePageBtn" class="btn btn-info rounded-circle shadow" title="<?= \StaticMD\Core\I18n::t('admin.toolbar.save_page') ?>">
                <i class="bi bi-download"></i>
            </button>
            <!-- Logout Button -->
            <form method="POST" action="/admin?action=logout" class="m-0">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($admin_csrf_token ?? '') ?>">
                <button type="submit" class="btn btn-secondary rounded-circle shadow" title="<?= \StaticMD\Core\I18n::t('admin.toolbar.logout') ?>">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </form>
        <?php endif; ?>
        <!-- Admin Dashboard Button -->
        <a href="/admin?return_to_frontend=1" class="btn btn-primary rounded-circle shadow" title="<?= \StaticMD\Core\I18n::t('admin.toolbar.admin_dashboard') ?>">
            <i class="bi bi-gear"></i>
        </a>
        <!-- Scroll to Top Button -->
        <button id="scrollTopBtn" type="button" class="scroll-to-top btn rounded-circle shadow" aria-label="<?= \StaticMD\Core\I18n::t('admin.toolbar.scroll_to_top') ?>">
            <i class="bi bi-arrow-up"></i>
        </button>
        </div>
    </div>
