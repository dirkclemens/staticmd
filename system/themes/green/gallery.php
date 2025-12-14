<?php
/**
 * Green Theme - Gallery Layout
 * Spezielles Layout für Bildergalerien mit responsivem Grid
 */

// Theme configuration
$siteName = $config['system']['name'] ?? 'StaticMD';
$currentRoute = $_GET['route'] ?? 'index';
$currentTheme = 'green';
$themeMode = 'light'; // 'light' or 'dark'

// Include shared head section
include __DIR__ . '/../shared/head.php';
?>
    <!-- GLightbox CSS für Lightbox-Funktionalität -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">

    <!-- Custom Gallery CSS -->
    <style>
        .content {
            padding: 0 2rem;
            min-height: 60vh;
        }

        /* Gallery-spezifische Styles */
        .gallery-header {
            text-align: center;
            margin-bottom: 0.1rem;
            padding: 0.1rem 0;
            /* background: linear-gradient(135deg, #f8f9fa 0%, #eee 100%); */
            border-radius: 0.5rem;
        }
        
        .gallery-header h1 {
            margin-bottom: 0.1rem;
        }
        
        .gallery-grid {
            margin: 0.2rem 0;
        }
        
        .gallery-item {
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .gallery-item img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: opacity 0.3s ease;
        }
        
        .gallery-item img:hover {
            opacity: 0.9;
        }
        
        .gallery-item-info {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            color: white;
            padding: 1rem;
            border-radius: 0 0 0.5rem 0.5rem;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .gallery-item:hover .gallery-item-info {
            opacity: 1;
        }
        
        .gallery-item-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .gallery-item-description {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .gallery-stats {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .gallery-stats .stat-item {
            margin: 0 1rem;
        }
        
        .gallery-stats .stat-number {
            font-size: 2rem;
            font-weight: 600;
            color: var(--bs-primary);
            display: block;
        }
        
        .gallery-filter {
            margin-bottom: 0.1rem;
            text-align: center;
        }
        
        .gallery-filter .btn {
            margin: 0.25rem;
        }
        
        /* Masonry-ähnliches Layout für verschiedene Bildgrößen */
        @media (min-width: 768px) {
            .gallery-masonry .gallery-item:nth-child(5n+1) img {
                height: 300px;
            }
            .gallery-masonry .gallery-item:nth-child(5n+3) img {
                height: 200px;
            }
        }
        
        /* Auto-Gallery Styles (für [gallery] Shortcode) */
        .auto-gallery-info {
            text-align: center;
            padding: 0.1rem;
            border-radius: 0.5rem;
            margin-bottom: 0.1rem;
        }
        
        /* Direkte Bilder im Gallery-Container (vor JavaScript-Verarbeitung) */
        #gallery-container > img {
            width: 100%;
            max-width: 300px;
            height: 250px;
            object-fit: cover;
            border-radius: 0.5rem;
            margin: 0.75rem;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: inline-block;
            vertical-align: top;
        }
        
        #gallery-container > img:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        /* Responsive Anpassungen */
        @media (max-width: 767px) {
            .gallery-item img {
                height: 200px;
            }
            
            #gallery-container > img {
                height: 200px;
                max-width: 100%;
                margin: 0.5rem 0;
            }
            
            .gallery-header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php
        // Navigation mit geteilter Komponente
        include __DIR__ . '/../shared/navigation.php'; 
    ?>
    
    <!-- Main Content -->    
    <div class="container-fluid px-4 pt-4">
        <!-- Breadcrumb Navigation -->
        <?= $themeHelper->renderBreadcrumbs($breadcrumbs ?? []) ?>
        
        <!-- Gallery Header -->
        <?php if (isset($title) && strlen($title) > 0): ?>             
        <div class="gallery-header">
            <h1><i class="bi bi-images me-3"></i><?= htmlspecialchars($title) ?></h1>
            <?php if (!empty($meta['description'])): ?>
            <p class="lead"><?= htmlspecialchars($meta['description']) ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
                    
        <!-- Erfolgsmeldung nach Speichern -->
        <?php if (isset($_GET['saved'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            Die Galerie wurde erfolgreich gespeichert.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <!-- Private Seiten-Hinweis für Admins -->
        <?php 
        $visibility = $meta['Visibility'] ?? $meta['visibility'] ?? 'public';
        if ($visibility === 'private'): 
        ?>
        <div class="alert alert-warning" role="alert">
            <i class="bi bi-lock me-2"></i>
            <strong>Private Galerie:</strong> Diese Galerie ist nur für angemeldete Admins sichtbar.
        </div>
        <?php endif; ?>
        
        <!-- Gallery Content -->
        <article class="content">
            <div class="gallery-grid">
                <div class="row gallery-masonry" id="gallery-container">
                    <!-- Der Markdown-Content wird hier eingefügt -->
                    <?= $body ?>
                </div>
            </div>
        </article>
    </div>
    

    <?php 
        // Admin-Toolbar mit geteilter Komponente
        include __DIR__ . '/../shared/admin-toolbar.php';

        // Footer mit geteilter Komponente
        include __DIR__ . '/../shared/footer.php'; 

        // Scripts mit geteilter Komponente (vereinfacht für Blog)
        include __DIR__ . '/../shared/scripts.php'; 
    ?>
    <!-- GLightbox JS für Lightbox-Funktionalität -->
    <script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
        
    <!-- Gallery Custom JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gallery setup first
            setupGallery();
            
            // Initialize Lightbox after gallery setup
            const lightbox = GLightbox({
                touchNavigation: true,
                loop: true,
                autoplayVideos: false,
                selector: '#gallery-container img' // All images in gallery container
            });
            
            // Filter functionality
            setupGalleryFilter();
        });
        
        function setupGallery() {
            const galleryContainer = document.getElementById('gallery-container');
            const allImages = galleryContainer.querySelectorAll('img');
            
            // Update image count (nur wenn Element existiert)
            const imageCountElement = document.getElementById('image-count');
            if (imageCountElement) {
                imageCountElement.textContent = allImages.length;
            }
            
            // Add lightbox attributes to all images first
            allImages.forEach(function(img, index) {
                // Add lightbox attributes
                img.setAttribute('data-gallery', 'gallery');
                img.setAttribute('data-glightbox', 'title: ' + (img.alt || 'Bild ' + (index + 1)));
            });
            
            // Wrap images in gallery items
            allImages.forEach(function(img, index) {
                if (!img.closest('.gallery-item')) {
                    const galleryItem = document.createElement('div');
                    galleryItem.className = 'col-md-4 col-lg-3 gallery-item';
                    
                    const imgContainer = document.createElement('div');
                    imgContainer.className = 'position-relative';
                    
                    // Add tags from image alt or title for filtering
                    const imgTags = img.getAttribute('title') || img.getAttribute('alt') || '';
                    if (imgTags) {
                        galleryItem.setAttribute('data-tags', imgTags.toLowerCase());
                    }
                    
                    // Create info overlay if image has alt text
                    if (img.alt) {
                        const infoDiv = document.createElement('div');
                        infoDiv.className = 'gallery-item-info';
                        
                        const titleDiv = document.createElement('div');
                        titleDiv.className = 'gallery-item-title';
                        titleDiv.textContent = img.alt;
                        
                        infoDiv.appendChild(titleDiv);
                        imgContainer.appendChild(infoDiv);
                    }
                    
                    // Wrap image
                    img.parentNode.insertBefore(galleryItem, img);
                    imgContainer.appendChild(img);
                    galleryItem.appendChild(imgContainer);
                }
            });
        }
        
        function setupGalleryFilter() {
            const filterButtons = document.querySelectorAll('[data-filter]');
            const galleryItems = document.querySelectorAll('.gallery-item');
            
            filterButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    const filter = this.getAttribute('data-filter');
                    
                    // Update active button
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Filter items
                    galleryItems.forEach(function(item) {
                        const tags = item.getAttribute('data-tags') || '';
                        
                        if (filter === 'all' || tags.includes(filter)) {
                            item.style.display = 'block';
                            item.style.opacity = '1';
                        } else {
                            item.style.opacity = '0';
                            setTimeout(() => {
                                if (item.style.opacity === '0') {
                                    item.style.display = 'none';
                                }
                            }, 300);
                        }
                    });
                    
                    // Update image count (nur wenn Element existiert)
                    setTimeout(() => {
                        const visibleItems = document.querySelectorAll('.gallery-item:not([style*="display: none"])');
                        const imageCountElement = document.getElementById('image-count');
                        if (imageCountElement) {
                            imageCountElement.textContent = visibleItems.length;
                        }
                    }, 350);
                });
            });
        }
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>  
    
    <?php if (isset($meta['js'])): ?>
    <script><?= $meta['js'] ?></script>
    <?php endif; ?>
</body>
</html>