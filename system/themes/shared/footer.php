<?php
/**
 * Shared Footer Section
 * Verwendet von allen Themes und Layouts
 */
?>
    <!-- Footer -->
    <footer class="py-4">
        <div class="container-fluid px-4">
            <div class="row">
                <div class="col-md-4 text-md-start"">
                    <p class="mb-1">&copy; <?= date('Y') ?> <?= htmlspecialchars($siteName) ?></p>
                    <p class="mb-0">
                        <small class="">Powered by StaticMD</small>
                    </p>
                </div>

                <div class="col-md-4 text-center">
                    <!-- Suchformular -->
                    <form class="d-flex me-3" action="/search" method="GET">
                        <div class="input-group">
                            <input type="search" name="q" class="form-control border-secondary" 
                                    placeholder="Suchen..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" style="width: 250px;">
                            <button class="btn border-secondary" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>                    
                </div>

                <div class="col-md-4 text-md-end">
                    <a href="/admin" class="text-decoration-none">
                        <i class="bi bi-shield-lock me-1"></i> Admin-Bereich
                    </a>
                </div>
            </div>
        </div>
    </footer>