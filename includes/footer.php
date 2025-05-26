</main>

    <footer>
        <div class="footer-container">
            <p>&copy; <?php echo date('Y'); ?> Flitz-Events Stageportaal | Alle rechten voorbehouden</p>
            <div class="footer-links">
                <a href="<?php echo ($isHomePage ?? false) ? './pages/contact.php' : 'contact.php'; ?>">Contact</a>
                <a href="<?php echo ($isHomePage ?? false) ? './pages/privacy.php' : 'privacy.php'; ?>">Privacy</a>
                <a href="<?php echo ($isHomePage ?? false) ? './pages/terms.php' : 'terms.php'; ?>">Voorwaarden</a>
            </div>
        </div>
    </footer>

    <!-- Custom scripts from page -->
    <?php if (isset($customScripts)): ?>
        <?php echo $customScripts; ?>
    <?php endif; ?>
    
    <!-- Main scripts -->
    <script src="<?php echo ($isHomePage ?? false) ? './assets/js/scripts.js' : '../assets/js/scripts.js'; ?>"></script>
    
    <!-- Login script for login page -->
    <?php if (basename($_SERVER['PHP_SELF']) === 'index.php'): ?>
    <script src="<?php echo ($isHomePage ?? false) ? './assets/js/login.js' : '../assets/js/login.js'; ?>"></script>
    <?php endif; ?>
    
    <!-- Admin scripts for admin pages -->
    <?php if (isset($_SESSION['rol']) && strtolower($_SESSION['rol']) === 'admin' && 
              in_array(basename($_SERVER['PHP_SELF']), ['admin.php', 'admin_home.php'])): ?>
    <script>
        // Admin-specific JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize admin features
            console.log('Admin features initialized');
            
            // Auto-refresh for admin dashboard
            if (document.getElementById('admin-dashboard')) {
                setInterval(function() {
                    // Refresh dashboard data if needed
                }, 300000); // 5 minutes
            }
        });
    </script>
    <?php endif; ?>
    
    <!-- Error handling for production -->
    <script>
        window.addEventListener('error', function(e) {
            console.error('JavaScript Error:', e.error);
            // In production, you might want to send this to a logging service
        });
        
        // Service worker registration for PWA features (optional)
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').then(function(registration) {
                    console.log('SW registered: ', registration);
                }).catch(function(registrationError) {
                    console.log('SW registration failed: ', registrationError);
                });
            });
        }
    </script>
</body>
</html>
