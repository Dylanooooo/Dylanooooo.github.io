<?php
session_start();
include('../includes/config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Get active project for the user
$sql = "SELECT * FROM projecten WHERE status = 'actief' ORDER BY voortgang DESC LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$active_project = $stmt->fetch();

// Get tasks for the active project
$taken = [];
if ($active_project) {
    $sql = "SELECT * FROM taken WHERE project_id = :project_id AND toegewezen_aan = :user_id ORDER BY deadline LIMIT 3";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':project_id', $active_project['id']);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $taken = $stmt->fetchAll();
}

// Get unread messages
$sql = "SELECT COUNT(*) as count FROM berichten WHERE ontvanger_id = :user_id AND gelezen = 0";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$unread_messages = $stmt->fetch()['count'];
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Flitz-Events Stagiairs Portal</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>

    <!-- 1. Header -->
    <header>
        <div class="header-container">
            <h1>Welkom bij Flitz-Events Stagiairs Portal</h1>
            <div class="user-info">
                <span id="user-name"><?php echo htmlspecialchars($_SESSION['naam']); ?></span>
                <form action="../auth/logout.php" method="post">
                <button type="submit" id="logout-btn">Uitloggen</button>
                </form>
            </div>
        </div>
    </header>

    <!-- 2. Banner -->
    <div class="intro-banner-wrapper">
        <div class="intro-banner">
            <img src="../assets/images/FlitzBanner.png" alt="Flitz Events Banner" class="banner-img">
            <div class="banner-text">
                <div class="banner-container">
                    <h3>Welkom bij je stage!</h3>
                    <p>Belangrijke informatie: Stagebegeleider: Milan Laroes (te bereiken via chat) | Aanwezigheid: Ma-Do 9:00-17:00
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Navigatie -->
    <nav>
        <div class="container">
            <div class="menu-toggle" id="mobile-menu">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
            <ul class="nav-list">
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="projecten.php">Projecten</a></li>
                <li><a href="chat.php">Chat</a></li>
                <?php if (isset($_SESSION['rol']) && strtolower($_SESSION['rol']) === 'admin'): ?>
                <li><a href="admin.php">Admin</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- 4. Dashboard content -->
    <section id="dashboard">
        <div class="container">
            <h2>Jouw Dashboard</h2>
            
            <!-- Project Overzicht Sectie -->
            <?php if ($active_project): ?>
            <div class="dashboard-widget featured-widget">
                <div class="widget-header">
                    <h3>Actief Project: <?php echo htmlspecialchars($active_project['naam']); ?></h3>
                    <a href="projecten.php" class="button-small">Alle Projecten</a>
                </div>
                
                <div class="project-overview">
                    <!-- Voortgangsmeter -->
                    <div class="project-progress-container">
                        <div class="svg-meter" data-percentage="<?php echo $active_project['voortgang']; ?>">
                            <svg width="200" height="100" viewBox="0 0 200 100">
                                <!-- Grijze achtergrond boog -->
                                <path class="meter-bg" d="M10,100 A90,90 0 0,1 190,100" stroke="#eee" stroke-width="10" fill="none" />
                                <!-- Gekleurde voortgangsboog -->
                                <path class="meter-fg" d="M10,100 A90,90 0 0,1 190,100" stroke="url(#gradient)" stroke-width="10" fill="none" />
                                <!-- Gradient definitie -->
                                <defs>
                                    <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                        <stop offset="0%" stop-color="#a71680" />
                                        <stop offset="100%" stop-color="#ec6708" />
                                    </linearGradient>
                                </defs>
                            </svg>
                            <div class="meter-needle"></div>
                            <div class="meter-center"><?php echo $active_project['voortgang']; ?>%</div>
                        </div>
                        <div class="meter-label">Projectvoortgang</div>
                    </div>
                    
                    <div class="project-details">
                        <div class="project-info">
                            <div class="info-item">
                                <span class="info-label">Start:</span>
                                <span class="info-value"><?php echo date('d M Y', strtotime($active_project['start_datum'])); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Deadline:</span>
                                <span class="info-value"><?php echo date('d M Y', strtotime($active_project['eind_datum'])); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="project-tasks">
                        <h4>Te doen deze week:</h4>
                        <?php if (count($taken) > 0): ?>
                        <ul class="task-list">
                            <?php foreach ($taken as $taak): ?>
                            <li class="task-item">
                                <input type="checkbox" id="task<?php echo $taak['id']; ?>" class="task-checkbox" <?php echo ($taak['status'] == 'afgerond') ? 'checked' : ''; ?>>
                                <label for="task<?php echo $taak['id']; ?>"><?php echo htmlspecialchars($taak['naam']); ?></label>
                                <span class="task-due">
                                    <?php if ($taak['deadline']): ?>
                                    Deadline: <?php echo date('d M', strtotime($taak['deadline'])); ?>
                                    <?php else: ?>
                                    Geen deadline
                                    <?php endif; ?>
                                </span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <a href="project-detail.php?id=<?php echo $active_project['id']; ?>" class="view-all">Bekijk alle taken</a>
                        <?php else: ?>
                        <p>Geen openstaande taken voor dit project.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="dashboard-widget featured-widget">
                <div class="widget-header">
                    <h3>Geen actief project</h3>
                    <a href="projecten.php" class="button-small">Bekijk Projecten</a>
                </div>
                <p>Er zijn momenteel geen actieve projecten toegewezen.</p>
            </div>
            <?php endif; ?>
            
            <div class="dashboard-grid">
                <!-- Snelle Links Widget -->
                <div class="dashboard-widget">
                    <h3>Snelle Links</h3>
                    <ul class="quick-links">
                        <li><a href="projecten.php">Actieve Projecten</a></li>
                        <li><a href="chat.php">
                            Berichten
                            <?php if ($unread_messages > 0): ?>
                            <span class="badge"><?php echo $unread_messages; ?></span>
                            <?php endif; ?>
                        </a></li>
                        <li><a href="trainingen.php">Trainingsmateriaal</a></li>
                    </ul>
                </div>
                
                <!-- Team Updates Widget -->
                <div class="dashboard-widget">
                    <h3>Team Updates</h3>
                    <div class="updates-list">
                        <div class="update-item">
                            <h4>Teamuitje</h4>
                            <p>Vergeet niet: ons teamuitje is gepland op 25 maart!</p>
                            <span class="date">18 maart 2025</span>
                        </div>
                        <div class="update-item">
                            <h4>Nieuwe Zomerplanning</h4>
                            <p>De planning voor de zomerperiode is nu beschikbaar.</p>
                            <span class="date">15 maart 2025</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. Footer -->
    <footer>
        <div class="footer-container">
            <p>&copy; 2025 Flitz-Events Stageportaal | Alle rechten voorbehouden</p>
        </div>
    </footer>

    <script src="../assets/js/scripts.js"></script>
</body>
</html>