<?php
/**
 * Shared JavaScript Section
 * Verwendet von allen Themes und Layouts
 */
?>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Highlight.js for Syntax Highlighting -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.11.1/highlight.min.js"></script>
    
    <!-- Custom JS -->
    <script nonce="<?= $nonce ?>">
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

        // Copy-to-clipboard for code blocks and inline code.
        // Event delegation instead of inline onclick: the nonce-based CSP blocks inline event handlers.
        function copyTextToClipboard(text) {
            if (navigator.clipboard && window.isSecureContext) {
                return navigator.clipboard.writeText(text);
            }
            // Fallback for non-secure contexts (e.g. local http)
            return new Promise((resolve, reject) => {
                const ta = document.createElement('textarea');
                ta.value = text;
                ta.setAttribute('readonly', '');
                ta.style.position = 'fixed';
                ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.select();
                try {
                    document.execCommand('copy') ? resolve() : reject(new Error('execCommand failed'));
                } catch (e) {
                    reject(e);
                } finally {
                    document.body.removeChild(ta);
                }
            });
        }

        document.addEventListener('click', function (e) {
            const button = e.target.closest('.copy-code-btn, .copy-inline-btn');
            if (!button) return;
            e.preventDefault();

            const container = button.closest('.code-block-wrapper, .inline-code-wrapper');
            const codeElement = container ? container.querySelector('code') : null;
            if (!codeElement) return;

            copyTextToClipboard(codeElement.textContent).then(() => {
                if (!button.dataset.originalHtml) {
                    button.dataset.originalHtml = button.innerHTML;
                }
                button.innerHTML = '<i class="bi bi-check"></i>';
                button.classList.add('copied');
                clearTimeout(button._copyTimer);
                button._copyTimer = setTimeout(() => {
                    button.innerHTML = button.dataset.originalHtml;
                    button.classList.remove('copied');
                }, 2000);
            }).catch(err => {
                console.error('Fehler beim Kopieren:', err);
            });
        });

        // Code syntax highlighting with Highlight.js
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('pre code').forEach((block) => {
                hljs.highlightElement(block);
            });
        });

        // Scroll to Top Button
        const scrollTopBtn = document.getElementById('scrollTopBtn');        
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                scrollTopBtn.classList.add('visible');
            } else {
                scrollTopBtn.classList.remove('visible');
            }
        });        
        scrollTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Save Markdown Content functionality
        const savePageBtn = document.getElementById('savePageBtn');
        if (savePageBtn) {
            savePageBtn.addEventListener('click', async function() {
                const btn = this;
                const originalIcon = btn.querySelector('i');
                const originalClass = originalIcon.className;
                
                try {
                    // Get current route from meta tag or URL
                    const route = document.querySelector('meta[name="route"]')?.content || 
                                 window.location.pathname.replace(/^\//, '').replace(/\/$/, '');
                    
                    if (!route) {
                        alert('Konnte aktuelle Route nicht ermitteln');
                        return;
                    }
                    
                    // Show loading state
                    originalIcon.className = 'bi bi-hourglass-split';
                    btn.disabled = true;
                    
                    // Fetch markdown content from API
                    const response = await fetch(`/download.php?route=${encodeURIComponent(route)}`);
                    
                    if (!response.ok) {
                        throw new Error('Download fehlgeschlagen');
                    }
                    
                    // Get filename from Content-Disposition header
                    const contentDisposition = response.headers.get('Content-Disposition');
                    let filename = 'page.md';
                    if (contentDisposition) {
                        const filenameMatch = contentDisposition.match(/filename="?(.+?)"?$/);
                        if (filenameMatch) {
                            filename = filenameMatch[1];
                        }
                    }
                    
                    // Get content and create download
                    const blob = await response.blob();
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = filename;
                    a.click();
                    
                    // Cleanup
                    URL.revokeObjectURL(url);
                    
                    // Show success
                    originalIcon.className = 'bi bi-check-lg';
                    setTimeout(() => {
                        originalIcon.className = originalClass;
                        btn.disabled = false;
                    }, 1500);
                    
                } catch (error) {
                    console.error('Save failed:', error);
                    alert('Fehler beim Speichern: ' + error.message);
                    originalIcon.className = originalClass;
                    btn.disabled = false;
                }
            });
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
            updateHighlightTheme(newTheme);
        });
        
        function updateHighlightTheme(mode) {
            const themeMap = window.HIGHLIGHT_THEME_MAP || {};
            const currentTheme = window.CURRENT_THEME || 'bootstrap';
            
            let highlightStyle;
            if (themeMap[currentTheme] && themeMap[currentTheme][mode]) {
                highlightStyle = themeMap[currentTheme][mode];
            } else {
                highlightStyle = 'stackoverflow-' + mode + '.min';
            }
            
            const highlightLink = document.getElementById('highlight-theme');
            if (highlightLink) {
                const newUrl = `https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.11.1/styles/${highlightStyle}.css`;
                highlightLink.href = newUrl;
            }
        }
        
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
    
    <?php if (isset($meta['js'])): ?>
    <script nonce="<?= $nonce ?>"><?= $meta['js'] ?></script>
    <?php endif; ?>
