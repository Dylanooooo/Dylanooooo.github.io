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

// Als de gebruiker is ingelogd, controleer of er een profielfoto is
$profile_image = '';
if ($is_logged_in && isset($_SESSION['profile_image']) && !empty($_SESSION['profile_image'])) {
    $profile_image = $_SESSION['profile_image'];
}

// Genereer initialen voor avatar als fallback
$initials = '';
if ($is_logged_in && isset($_SESSION['naam']) && !empty($_SESSION['naam'])) {
    $name_parts = explode(' ', $_SESSION['naam']);
    $initials = strtoupper(substr($name_parts[0], 0, 1));
    if (count($name_parts) > 1) {
        $initials .= strtoupper(substr(end($name_parts), 0, 1));
    }
}
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

<?php if($is_logged_in): ?>
<nav>
    <div class="container">
        <div class="menu-toggle" id="mobile-menu">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </div>
        <ul class="nav-list">
            <?php if($user_role === 'admin'): ?>
                <!-- Navigatie voor medewerkers -->
                <li><a href="<?php echo $root_path ?? ''; ?>pages/admin_home.php" class="<?php echo $current_page == 'admin_home.php' ? 'active' : ''; ?>">Dashboard</a></li>
                <li><a href="<?php echo $root_path ?? ''; ?>pages/admin.php" class="<?php echo $current_page == 'admin.php' ? 'active' : ''; ?>">Beheer</a></li>
                <li><a href="<?php echo $root_path ?? ''; ?>pages/projecten.php" class="<?php echo $current_page == 'projecten.php' ? 'active' : ''; ?>">Projecten</a></li>
                <li><a href="<?php echo $root_path ?? ''; ?>pages/events.php" class="<?php echo $current_page == 'events.php' ? 'active' : ''; ?>">Evenementen</a></li>
                <li><a href="<?php echo $root_path ?? ''; ?>pages/gebruikers.php" class="<?php echo $current_page == 'gebruikers.php' ? 'active' : ''; ?>">Gebruikers</a></li>
                <li><a href="<?php echo $root_path ?? ''; ?>pages/rooster.php" class="<?php echo in_array($current_page, ['rooster.php', 'rooster_detail.php']) ? 'active' : ''; ?>">Rooster</a></li>
                <li><a href="<?php echo $root_path ?? ''; ?>pages/chat.php" class="<?php echo $current_page == 'chat.php' ? 'active' : ''; ?>">Chat</a></li>
            
            <?php else: ?>
                <!-- Navigatie voor stagiairs/standaardgebruikers -->
                <li><a href="<?php echo $root_path ?? ''; ?>pages/dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a></li>
                <li><a href="<?php echo $root_path ?? ''; ?>pages/projecten.php" class="<?php echo $current_page == 'projecten.php' ? 'active' : ''; ?>">Mijn projecten</a></li>
                <li><a href="<?php echo $root_path ?? ''; ?>pages/rooster.php" class="<?php echo in_array($current_page, ['rooster.php', 'rooster_detail.php']) ? 'active' : ''; ?>">Rooster</a></li>
                <li><a href="<?php echo $root_path ?? ''; ?>pages/chat.php" class="<?php echo $current_page == 'chat.php' ? 'active' : ''; ?>">Chat</a></li>
            <?php endif; ?>
        </ul>

        <!-- Profiel navigatie - rechts uitgelijnd -->
        <div class="profile-nav">
            <a href="<?php echo $root_path ?? ''; ?>pages/profiel.php" class="profile-link <?php echo $current_page == 'profiel.php' ? 'active' : ''; ?>">
                <?php if (!empty($profile_image) && file_exists(($root_path ?? '') . 'uploads/profiles/' . $profile_image)): ?>
                    <img src="<?php echo ($root_path ?? '') . 'uploads/profiles/' . $profile_image; ?>" alt="Profielfoto" class="nav-profile-img">
                <?php elseif (!empty($initials)): ?>
                    <div class="nav-profile-initial"><?php echo $initials; ?></div>
                <?php else: ?>
                    <div class="nav-profile-initial">
                        <i class="fas fa-user-circle"></i>
                    </div>
                <?php endif; ?>
                <span class="profile-name"><?php echo htmlspecialchars($_SESSION['naam'] ?? 'Gebruiker'); ?></span>
            </a>
        </div>
    </div>
</nav>
<?php else: ?>
<nav>
    <div class="container">
        <div class="menu-toggle" id="mobile-menu">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </div>
        <ul class="nav-list">
            <!-- Navigatie voor niet-ingelogde gebruikers -->
            <li><a href="<?php echo $root_path ?? ''; ?>index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">Home</a></li>
            <li><a href="<?php echo $root_path ?? ''; ?>pages/about.php" class="<?php echo $current_page == 'about.php' ? 'active' : ''; ?>">Over Ons</a></li>
            <li><a href="<?php echo $root_path ?? ''; ?>pages/contact.php" class="<?php echo $current_page == 'contact.php' ? 'active' : ''; ?>">Contact</a></li>
        </ul>
    </div>
</nav>
<?php endif; ?>

<main>