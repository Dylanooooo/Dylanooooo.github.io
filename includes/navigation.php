<?php
// Zorg ervoor dat sessie beschikbaar is
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bepaal huidige pagina voor active class
$current_page = basename($_SERVER['PHP_SELF']);

// Bepaal of gebruiker is ingelogd en wat hun rol is
$is_logged_in = isset($_SESSION['user_id']);
$user_role = $is_logged_in ? strtolower($_SESSION['rol']) : '';
?>

<header>
    <div class="header-container">
        <h1>Welkom bij Flitz-Events<?php echo ($user_role === 'admin' ? ' Medewerkers Portal' : ''); ?></h1>
        <?php if($is_logged_in): ?>
        <div class="user-info">
            <span id="user-name"><?php echo htmlspecialchars($_SESSION['naam'] ?? 'Gebruiker'); ?></span>
            <form action="<?php echo $root_path ?? ''; ?>auth/logout.php" method="post">
                <button type="submit" id="logout-btn">Uitloggen</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</header>

<nav>
    <div class="container">
        <div class="menu-toggle" id="mobile-menu">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </div>
        <ul class="nav-list">
            <?php if(!$is_logged_in): ?>
                <!-- Navigatie voor niet-ingelogde gebruikers -->
                <li><a href="<?php echo $root_path ?? ''; ?>index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">Home</a></li>
                <li><a href="<?php echo $root_path ?? ''; ?>pages/about.php" class="<?php echo $current_page == 'about.php' ? 'active' : ''; ?>">Over Ons</a></li>
                <li><a href="<?php echo $root_path ?? ''; ?>pages/contact.php" class="<?php echo $current_page == 'contact.php' ? 'active' : ''; ?>">Contact</a></li>
            
            <?php elseif($user_role === 'admin'): ?>
                <!-- Navigatie voor medewerkers -->
                <li><a href="<?php echo $root_path ?? ''; ?>pages/admin_home.php" class="<?php echo $current_page == 'admin_home.php' ? 'active' : ''; ?>">Dashboard</a></li>
                <li><a href="<?php echo $root_path ?? ''; ?>pages/admin.php" class="<?php echo $current_page == 'admin.php' ? 'active' : ''; ?>">Beheer</a></li>
                <li><a href="<?php echo $root_path ?? ''; ?>pages/projecten.php" class="<?php echo $current_page == 'projecten.php' ? 'active' : ''; ?>">Projecten</a></li>
                <li><a href="<?php echo $root_path ?? ''; ?>pages/chat.php" class="<?php echo $current_page == 'chat.php' ? 'active' : ''; ?>">Chat</a></li>
                <li><a href="<?php echo $root_path ?? ''; ?>pages/events.php" class="<?php echo $current_page == 'events.php' ? 'active' : ''; ?>">Evenementen</a></li>
                <li><a href="<?php echo $root_path ?? ''; ?>pages/gebruikers.php" class="<?php echo $current_page == 'gebruikers.php' ? 'active' : ''; ?>">Gebruikers</a></li>
            
            <?php else: ?>
                <!-- Navigatie voor stagiairs/standaardgebruikers -->
                <li><a href="<?php echo $root_path ?? ''; ?>pages/dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a></li>
                <li><a href="<?php echo $root_path ?? ''; ?>pages/projecten.php" class="<?php echo $current_page == 'projecten.php' ? 'active' : ''; ?>">Mijn projecten</a></li>
                <li><a href="<?php echo $root_path ?? ''; ?>pages/profiel.php" class="<?php echo $current_page == 'profiel.php' ? 'active' : ''; ?>">Mijn profiel</a></li>
                <li><a href="<?php echo $root_path ?? ''; ?>pages/chat.php" class="<?php echo $current_page == 'chat.php' ? 'active' : ''; ?>">Chat</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>