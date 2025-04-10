<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Flitz-Events Stageportaal'; ?></title>
    <link rel="stylesheet" href="<?php echo $isHomePage ? './assets/css/style.css' : '../assets/css/style.css'; ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php if (isset($useIcons) && $useIcons): ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php endif; ?>
</head>
<body>
    <header>
        <div class="header-container">
            <h1>Welkom bij Flitz-Events Stagiairs Portal</h1>
            <?php if ($isLoggedIn): ?>
            <div class="user-info">
                <span id="user-name"><?php echo htmlspecialchars($_SESSION['naam']); ?></span>
                <a href="<?php echo $isHomePage ? './auth/logout.php' : '../auth/logout.php'; ?>" id="logout-btn">Uitloggen</a>
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
                <li><a href="<?php echo $isHomePage ? './pages/dashboard.php' : 'dashboard.php'; ?>" <?php if ($currentPage == 'dashboard.php') echo 'class="active"'; ?>>Dashboard</a></li>
                <li><a href="<?php echo $isHomePage ? './pages/projecten.php' : 'projecten.php'; ?>" <?php if ($currentPage == 'projecten.php' || $currentPage == 'project-detail.php') echo 'class="active"'; ?>>Projecten</a></li>
                <li><a href="<?php echo $isHomePage ? './pages/chat.php' : 'chat.php'; ?>" <?php if ($currentPage == 'chat.php') echo 'class="active"'; ?>>Chat</a></li>
                <?php if (isset($_SESSION['rol']) && strtolower($_SESSION['rol']) === 'admin'): ?>
                <li><a href="<?php echo $isHomePage ? './pages/admin.php' : 'admin.php'; ?>" <?php if ($currentPage == 'admin.php') echo 'class="active"'; ?>>Admin</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    <?php endif; ?>

    <main>
