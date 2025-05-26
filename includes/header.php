<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$currentPage = basename($_SERVER['PHP_SELF']);

// Set default values if not set
$pageTitle = $pageTitle ?? 'Flitz-Events Stageportaal';
$isHomePage = $isHomePage ?? false;
$useIcons = $useIcons ?? false;
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="<?php echo $isHomePage ? './assets/css/style.css' : '../assets/css/style.css'; ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php if ($useIcons): ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php endif; ?>
    
    <!-- Add admin CSS if user is admin -->
    <?php if ($isLoggedIn && isset($_SESSION['rol']) && strtolower($_SESSION['rol']) === 'admin'): ?>
    <link rel="stylesheet" href="<?php echo $isHomePage ? './assets/css/admin.css' : '../assets/css/admin.css'; ?>">
    <?php endif; ?>
</head>
<body>
    <header>
        <div class="header-container">
            <h1>Welkom bij Flitz-Events <?php echo (isset($_SESSION['rol']) && strtolower($_SESSION['rol']) === 'admin') ? 'Medewerkers Portal' : 'Stagiairs Portal'; ?></h1>
            <?php if ($isLoggedIn): ?>
            <div class="user-info">
                <div class="user-profile">
                    <?php if (isset($_SESSION['profile_image']) && !empty($_SESSION['profile_image'])): ?>
                        <img src="<?php echo ($isHomePage ? './uploads/profiles/' : '../uploads/profiles/') . $_SESSION['profile_image']; ?>" 
                             alt="Profielfoto" class="header-profile-img">
                    <?php else: ?>
                        <div class="header-profile-initial">
                            <?php
                            $name_parts = explode(' ', $_SESSION['naam'] ?? 'U');
                            echo strtoupper(substr($name_parts[0], 0, 1));
                            if (count($name_parts) > 1) {
                                echo strtoupper(substr(end($name_parts), 0, 1));
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                    <span id="user-name"><?php echo htmlspecialchars($_SESSION['naam'] ?? 'Gebruiker'); ?></span>
                </div>
                <a href="<?php echo $isHomePage ? './auth/logout.php' : '../auth/logout.php'; ?>" id="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Uitloggen
                </a>
            </div>
            <?php endif; ?>
        </div>
    </header>

    <?php if ($isLoggedIn): ?>
    <nav>
        <div class="container">
            <div class="menu-toggle" id="mobile-menu">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
            <ul class="nav-list">
                <?php if (isset($_SESSION['rol']) && strtolower($_SESSION['rol']) === 'admin'): ?>
                    <!-- Admin navigation -->
                    <li><a href="<?php echo $isHomePage ? './pages/admin_home.php' : 'admin_home.php'; ?>" 
                           <?php if ($currentPage == 'admin_home.php') echo 'class="active"'; ?>>Dashboard</a></li>
                    <li><a href="<?php echo $isHomePage ? './pages/admin.php' : 'admin.php'; ?>" 
                           <?php if ($currentPage == 'admin.php') echo 'class="active"'; ?>>Beheer</a></li>
                    <li><a href="<?php echo $isHomePage ? './pages/projecten.php' : 'projecten.php'; ?>" 
                           <?php if (in_array($currentPage, ['projecten.php', 'project-detail.php', 'project-edit.php'])) echo 'class="active"'; ?>>Projecten</a></li>
                    <li><a href="<?php echo $isHomePage ? './pages/chat.php' : 'chat.php'; ?>" 
                           <?php if ($currentPage == 'chat.php') echo 'class="active"'; ?>>Chat</a></li>
                    <li><a href="<?php echo $isHomePage ? './pages/rooster.php' : 'rooster.php'; ?>" 
                           <?php if (in_array($currentPage, ['rooster.php', 'rooster_detail.php'])) echo 'class="active"'; ?>>Rooster</a></li>
                <?php else: ?>
                    <!-- Regular user navigation -->
                    <li><a href="<?php echo $isHomePage ? './pages/dashboard.php' : 'dashboard.php'; ?>" 
                           <?php if ($currentPage == 'dashboard.php') echo 'class="active"'; ?>>Dashboard</a></li>
                    <li><a href="<?php echo $isHomePage ? './pages/projecten.php' : 'projecten.php'; ?>" 
                           <?php if (in_array($currentPage, ['projecten.php', 'project-detail.php'])) echo 'class="active"'; ?>>Projecten</a></li>
                    <li><a href="<?php echo $isHomePage ? './pages/chat.php' : 'chat.php'; ?>" 
                           <?php if ($currentPage == 'chat.php') echo 'class="active"'; ?>>Chat</a></li>
                    <li><a href="<?php echo $isHomePage ? './pages/rooster.php' : 'rooster.php'; ?>" 
                           <?php if (in_array($currentPage, ['rooster.php', 'rooster_detail.php'])) echo 'class="active"'; ?>>Rooster</a></li>
                    <li><a href="<?php echo $isHomePage ? './pages/profiel.php' : 'profiel.php'; ?>" 
                           <?php if ($currentPage == 'profiel.php') echo 'class="active"'; ?>>Profiel</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    <?php endif; ?>

    <main>
