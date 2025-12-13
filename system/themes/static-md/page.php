<?php
/**
 * StaticMD Theme - Page Template (Modular)
 */

// Theme configuration
$siteName = $config['system']['name'] ?? 'StaticMD';
$currentRoute = $_GET['route'] ?? 'index';
$currentTheme = 'static-md';
$themeMode = 'light'; // 'light' or 'dark'

// Include shared head section
include __DIR__ . '/../shared/head.php';
?>
</head>
<body>
    <?php 
    // Include shared navigation
    include __DIR__ . '/navigation.php';
    ?>
    
    <!-- Main Content -->
    <main class="container mt-4">
        <?= $body ?>
    </main>
    
    <?php 
    // Admin-Toolbar mit geteilter Komponente
    include __DIR__ . '/../shared/admin-toolbar.php';
    
    // Footer mit geteilter Komponente
    include __DIR__ . '/../shared/footer.php'; 
    
    // Scripts mit geteilter Komponente (vereinfacht für Blog)
    include __DIR__ . '/../shared/scripts.php'; 
    ?>
</body>
</html>