<?php
/**
 * Shared Footer Section
 * Verwendet von allen Themes und Layouts
 */
?>
    <!-- Footer -->
    <footer class="py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <p class="mb-1">&copy; <?= date('Y') ?> <?= htmlspecialchars($siteName) ?></p>
                    <p class="mb-0">
                        <small class="text-light">
                            Powered by StaticMD
                        </small>
                    </p>
                </div>

                <div class="col-md-4 text-center">
                    <!-- Suchformular -->
                    <form class="d-flex me-3" action="/search" method="GET">
                        <input class="form-control me-2" type="search" name="q" placeholder="Suchen..." 
                            value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" style="width: 250px;">
                        <button class="btn btn-outline-primary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>
                </div>

                <div class="col-md-4 text-md-end">
                    <a href="/admin" class="text-light text-decoration-none">
                        <i class="bi bi-shield-lock me-1"></i> Admin-Bereich
                    </a>
                </div>
            </div>
        </div>
    </footer>