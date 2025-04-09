    </main>

    <footer>
        <div class="footer-container">
            <p>&copy; <?php echo date('Y'); ?> Flitz-Events Stageportaal | Alle rechten voorbehouden</p>
        </div>
    </footer>

    <?php if (isset($customScripts)): ?>
        <?php echo $customScripts; ?>
    <?php endif; ?>
    
    <script src="<?php echo $isHomePage ? './assets/js/scripts.js' : '../assets/js/scripts.js'; ?>"></script>
</body>
</html>
