<?php
session_start();
include('../includes/config.php');

// Controleer of de gebruiker is ingelogd en admin/medewerker rol heeft
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['rol']) !== 'admin') {
    // Redirect naar de login pagina als de gebruiker niet is ingelogd of geen admin/medewerker is
    header("Location: ../index.php");
    exit;
}

// Haal de gebruikersnaam op uit de sessie
$username = $_SESSION['naam'] ?? 'Medewerker';

// Haal actuele projectdata op
$sql = "SELECT * FROM projecten ORDER BY status, start_datum LIMIT 3";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$projecten = $stmt->fetchAll();

// Haal recente taken op
$sql = "SELECT t.*, p.naam as project_naam FROM taken t 
        LEFT JOIN projecten p ON t.project_id = p.id 
        ORDER BY t.datum_aangemaakt DESC LIMIT 5";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$taken = $stmt->fetchAll();

// Haal gebruikersgegevens op (voor statistieken)
$sql = "SELECT COUNT(*) as total FROM gebruikers";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$user_count = $stmt->fetch()['total'];

$pageTitle = "Medewerker Dashboard - Flitz Events";
$useIcons = true;
// Relatief pad voor navigatie
$root_path = "../";
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Inclusie van de consistente navigatie component -->
    <?php include('../includes/navigation.php'); ?>

    <section id="dashboard">
        <div class="container">
            <h2>Medewerker Dashboard</h2>
            
            <!-- Welkomstsectie -->
            <div class="dashboard-widget featured-widget">
                <div class="widget-header">
                    <h3>Overzicht</h3>
                </div>
                <div class="stats-container">
                    <div class="stat-box">
                        <div class="stat-number"><?php echo $user_count; ?></div>
                        <div class="stat-label">Gebruikers</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number"><?php echo count($projecten); ?></div>
                        <div class="stat-label">Actieve Projecten</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number"><?php echo count($taken); ?></div>
                        <div class="stat-label">Recente Taken</div>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-grid">
                <!-- Snelle toegang -->
                <div class="dashboard-widget">
                    <h3>Snelle Toegang</h3>
                    <ul class="quick-links">
                        <li><a href="admin.php"><i class="fas fa-cog"></i> Beheer Instellingen</a></li>
                        <li><a href="admin.php?tab=users"><i class="fas fa-users"></i> Gebruikersbeheer</a></li>
                        <li><a href="admin.php?tab=projects"><i class="fas fa-tasks"></i> Projectbeheer</a></li>
                        <li><a href="admin.php?tab=tasks"><i class="fas fa-clipboard-list"></i> Takenbeheer</a></li>
                    </ul>
                </div>
                
                <!-- Recente projecten -->
                <div class="dashboard-widget">
                    <h3>Recente Projecten</h3>
                    <?php if (count($projecten) > 0): ?>
                        <ul class="project-list">
                            <?php foreach ($projecten as $project): ?>
                                <li class="project-item">
                                    <div class="project-info">
                                        <h4><?php echo htmlspecialchars($project['naam']); ?></h4>
                                        <span class="status-badge <?php 
                                            if ($project['status'] === 'actief') echo 'status-active';
                                            elseif ($project['status'] === 'afgerond') echo 'status-completed';
                                            else echo 'status-upcoming';
                                        ?>">
                                            <?php echo ucfirst($project['status']); ?>
                                        </span>
                                        <div class="project-dates">
                                            <span class="date">Start: <?php echo date('d M Y', strtotime($project['start_datum'])); ?></span>
                                            <span class="date">Einde: <?php echo date('d M Y', strtotime($project['eind_datum'])); ?></span>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <a href="projecten.php" class="view-all">Alle projecten bekijken</a>
                    <?php else: ?>
                        <p>Geen projecten gevonden.</p>
                    <?php endif; ?>
                </div>
                
                <!-- Recente taken -->
                <div class="dashboard-widget">
                    <h3>Recente Taken</h3>
                    <?php if (count($taken) > 0): ?>
                        <ul class="task-list">
                            <?php foreach ($taken as $taak): ?>
                                <li class="task-item">
                                    <span class="task-project"><?php echo htmlspecialchars($taak['project_naam']); ?></span>
                                    <span class="task-name"><?php echo htmlspecialchars($taak['naam']); ?></span>
                                    <span class="task-status <?php 
                                        if ($taak['status'] === 'afgerond') echo 'status-completed';
                                        elseif ($taak['status'] === 'in_uitvoering') echo 'status-active';
                                        else echo '';
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $taak['status'])); ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <a href="admin.php?tab=tasks" class="view-all">Alle taken beheren</a>
                    <?php else: ?>
                        <p>Geen recente taken gevonden.</p>
                    <?php endif; ?>
                </div>
                
                <!-- Mededelingen voor medewerkers -->
                <div class="dashboard-widget">
                    <h3>Mededelingen</h3>
                    <div class="updates-list">
                        <div class="update-item">
                            <h4>Nieuwe Stagairs</h4>
                            <p>Er starten volgende week 3 nieuwe stagiairs. Zorg ervoor dat hun accounts klaargezet zijn.</p>
                            <span class="date">Vandaag</span>
                        </div>
                        <div class="update-item">
                            <h4>Projectplanning</h4>
                            <p>De planning voor het zomerfestival moet uiterlijk vrijdag zijn afgerond.</p>
                            <span class="date">Gisteren</span>
                        </div>
                    </div>
                </div>
                
                <!-- Add roster management widget to admin dashboard -->
                <div class="dashboard-widget">
                    <div class="widget-header">
                        <h3>Roosterbeheer</h3>
                        <a href="rooster.php" class="view-all">Volledig rooster</a>
                    </div>
                    <div class="widget-content">
                        <div class="roster-stats">
                            <?php
                            // Get upcoming roster stats
                            $today = date('Y-m-d');
                            $next_week = date('Y-m-d', strtotime('+7 days'));
                            
                            // Count total meetings in the next week
                            $stmt = $pdo->prepare("SELECT COUNT(*) as total_meetings,
                                                 COUNT(DISTINCT gebruiker_id) as total_users
                                                 FROM rooster
                                                 WHERE dag BETWEEN :today AND :next_week");
                            $stmt->execute([
                                'today' => $today,
                                'next_week' => $next_week
                            ]);
                            $stats = $stmt->fetch();
                            ?>
                            
                            <div class="stat-box">
                                <div class="stat-number"><?php echo $stats['total_meetings']; ?></div>
                                <div class="stat-label">Geplande afspraken</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-number"><?php echo $stats['total_users']; ?></div>
                                <div class="stat-label">Betrokken personen</div>
                            </div>
                            
                            <div class="action-buttons">
                                <a href="rooster.php" class="button">Rooster bekijken</a>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
            
            <!-- Activiteitenlogboek -->
            <div class="dashboard-widget">
                <div class="widget-header">
                    <h3>Recente Activiteit</h3>
                </div>
                <div class="activity-log">
                    <div class="log-item">
                        <span class="log-time">10:30</span>
                        <span class="log-message">Nieuw project aangemaakt: "Zomerfestival Arnhem"</span>
                    </div>
                    <div class="log-item">
                        <span class="log-time">09:15</span>
                        <span class="log-message">Gebruiker "JanDoe" toegevoegd aan project "Bedrijfsevent Amsterdam"</span>
                    </div>
                    <div class="log-item">
                        <span class="log-time">Gisteren</span>
                        <span class="log-message">Taak "Locatie bezoeken" gemarkeerd als afgerond</span>
                    </div>
                    <div class="log-item">
                        <span class="log-time">Gisteren</span>
                        <span class="log-message">Nieuwe gebruiker geregistreerd: "Emma de Vries"</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="footer-container">
            <p>&copy; <?php echo date("Y"); ?> Flitz-Events Medewerkers Portal | Alle rechten voorbehouden</p>
        </div>
    </footer>

    <script src="../assets/js/scripts.js"></script>
</body>
</html>