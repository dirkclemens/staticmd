<?php
/**
 * Shared Admin Toolbar
 * Nur sichtbar wenn Admin eingeloggt ist
 */
?>
    <div class="admin-toolbar">
        <div class="btn-group-vertical" role="group">
        <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']): ?>
            <?php if (isset($content['file_path'])): ?>
                <!-- Edit Page Button -->
                <a href="/admin?action=edit&file=<?= urlencode($content['route']) ?>&return_url=<?= urlencode($_SERVER['REQUEST_URI']) ?>" 
                   class="btn btn-warning rounded-circle shadow" title="Seite bearbeiten">
                    <i class="bi bi-pencil"></i>
                </a>
                <!-- New Page Button -->
                <a href="/admin?action=new&prefill_path=<?= urlencode($_SERVER['REQUEST_URI']) ?>&return_url=<?= urlencode($_SERVER['REQUEST_URI']) ?>" 
                   class="btn btn-success rounded-circle shadow" title="Neue Seite">
                    <i class="bi bi-file-earmark-plus"></i>
                </a>                
            <?php endif; ?>
            <!-- Logout Button -->
            <a href="/admin?action=logout" class="btn btn-secondary rounded-circle shadow" title="Logout">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        <?php endif; ?>
        <!-- Admin Dashboard Button -->
        <a href="/admin?return_to_frontend=1" class="btn btn-primary rounded-circle shadow" title="Admin Dashboard">
            <i class="bi bi-gear"></i>
        </a>
        <!-- Scroll to Top Button -->
        <a href="#" id="scrollTopBtn" class="scroll-to-top btn rounded-circle shadow" aria-label="Nach oben scrollen">
            <i class="bi bi-arrow-up"></i>
        </a>
        </div>
    </div>