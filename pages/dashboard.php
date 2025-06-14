<?php
session_start();
include('../includes/config.php');

// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['user_id'])) {
    // Redirect naar de login pagina als de gebruiker niet is ingelogd
    header("Location: ../index.php");
    exit;
}

// Relatief pad voor navigatie
$root_path = "../";
$pageTitle = "Stagiair Dashboard - Flitz Events";

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

// Upcoming shifts - dummy data for now
// In een echte applicatie zouden deze uit een "roosters" tabel komen
$upcoming_shifts = [
    [
        'day' => 'Maandag',
        'date' => '24 Apr',
        'time' => '09:00 - 17:00',
        'location' => 'Kantoor'
    ],
    [
        'day' => 'Woensdag',
        'date' => '26 Apr',
        'time' => '09:00 - 17:00',
        'location' => 'Kantoor'
    ]
];
?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Inclusie van de consistente navigatie component -->
    <?php include('../includes/navigation.php'); ?>

    <!-- 2. Banner -->
    <div class="intro-banner-wrapper">
        <div class="intro-banner">
            <img src="../assets/images/FlitzBanner.png" alt="Flitz Events Banner" class="banner-img">
            <div class="banner-text">
                <div class="banner-container">
                    <h3>Welkom bij je stage!</h3>
                    <p>Belangrijke informatie: Stagebegeleider: Milan Laroes (te bereiken via chat) | Aanwezigheid: Ma-Do 9:00-17:00</p>
                </div>
            </div>
        </div>
    </div>

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
                                <path class="meter-bg" d="M10,100 A90,90 0 0,1 190,100" stroke="#eee" stroke-width="10"
                                    fill="none" />
                                <!-- Gekleurde voortgangsboog -->
                                <path class="meter-fg" d="M10,100 A90,90 0 0,1 190,100" stroke="url(#gradient)"
                                    stroke-width="10" fill="none" />
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
                                <span
                                    class="info-value"><?php echo date('d M Y', strtotime($active_project['start_datum'])); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Deadline:</span>
                                <span
                                    class="info-value"><?php echo date('d M Y', strtotime($active_project['eind_datum'])); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="project-tasks">
                        <h4><i class="fas fa-tasks"></i> Te doen deze week:</h4>
                        <?php if (count($taken) > 0): ?>
                        <ul class="task-list">
                            <?php foreach ($taken as $taak): ?>
                            <li class="task-item">
                                <input type="checkbox" id="task<?php echo $taak['id']; ?>" class="task-checkbox"
                                    <?php echo ($taak['status'] == 'afgerond') ? 'checked' : ''; ?>>
                                <label for="task<?php echo $taak['id']; ?>">
                                    <span class="task-name"><?php echo htmlspecialchars($taak['naam']); ?></span>
                                </label>
                                <span class="task-due">
                                    <?php if ($taak['deadline']): ?>
                                    <i class="fas fa-calendar"></i> Deadline: <?php echo date('d M', strtotime($taak['deadline'])); ?>
                                    <?php else: ?>
                                    <i class="fas fa-calendar-times"></i> Geen deadline
                                    <?php endif; ?>
                                </span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <a href="project-detail.php?id=<?php echo $active_project['id']; ?>" class="view-all">
                            <i class="fas fa-external-link-alt"></i> Bekijk alle taken
                        </a>
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
                    <a href="projecten.php" class="button-small">
                        <i class="fas fa-folder-open"></i> Bekijk Projecten
                    </a>
                </div>
                <div class="no-project-state">
                    <div class="no-project-icon">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <p>Er zijn momenteel geen actieve projecten toegewezen.</p>
                    <a href="projecten.php" class="button">Projecten bekijken</a>
                </div>
            </div>
            <?php endif; ?>

            <div class="dashboard-grid">
                <!-- Snelle Links Widget -->
                <div class="dashboard-widget">
                    <h3><i class="fas fa-bolt"></i> Snelle Links</h3>
                    <ul class="quick-links">
                        <li><a href="projecten.php"><i class="fas fa-project-diagram"></i> Projecten Overzicht</a></li>
                        <li><a href="rooster.php"><i class="fas fa-calendar-week"></i> Weekrooster</a></li>
                        <li><a href="chat.php"><i class="fas fa-comments"></i> Chat</a></li>
                        <li><a href="profiel.php"><i class="fas fa-user-cog"></i> Profiel Bewerken</a></li>
                    </ul>
                </div>
                
                <!-- Aankomende Diensten Widget -->
                <div class="dashboard-widget">
                    <div class="widget-header">
                        <h3><i class="fas fa-calendar-alt"></i> Mijn Rooster</h3>
                        <a href="rooster.php" class="view-all">
                            <i class="fas fa-external-link-alt"></i> Volledig rooster
                        </a>
                    </div>
                    <div class="widget-content">
                        <!-- Upcoming shifts display or summary -->
                        <div class="upcoming-shifts">
                            <?php
                            // Get upcoming shifts for this user
                            $today = date('Y-m-d');
                            $next_week = date('Y-m-d', strtotime('+7 days'));
                            
                            $stmt = $pdo->prepare("SELECT r.*, DATE_FORMAT(r.dag, '%d-%m-%Y') as formatted_date
                                               FROM rooster r
                                               WHERE r.gebruiker_id = :user_id
                                               AND r.dag BETWEEN :today AND :next_week
                                               ORDER BY r.dag ASC, r.start_tijd ASC
                                               LIMIT 3");
                            $stmt->execute([
                                'user_id' => $_SESSION['user_id'],
                                'today' => $today,
                                'next_week' => $next_week
                            ]);
                            $shifts = $stmt->fetchAll();
                            
                            if (count($shifts) > 0): ?>
                                <ul class="shifts-list">
                                <?php foreach($shifts as $shift): ?>
                                    <li class="shift-item">
                                        <div class="shift-date">
                                            <i class="fas fa-calendar"></i>
                                            <?php echo $shift['formatted_date']; ?>
                                        </div>
                                        <div class="shift-time">
                                            <i class="fas fa-clock"></i>
                                            <?php echo date('H:i', strtotime($shift['start_tijd'])); ?> - 
                                            <?php echo date('H:i', strtotime($shift['eind_tijd'])); ?>
                                        </div>
                                        <div class="shift-location">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <?php echo htmlspecialchars($shift['locatie']); ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <div class="no-shifts">
                                    <div class="no-shifts-icon">
                                        <i class="fas fa-calendar-times"></i>
                                    </div>
                                    <p class="no-data">Geen ingeplande shifts voor de komende week.</p>
                                    <a href="rooster.php" class="button-small">
                                        <i class="fas fa-plus"></i> Plan een afspraak
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Updates Widget -->
                <div class="dashboard-widget">
                    <h3><i class="fas fa-bell"></i> Laatste Updates</h3>
                    <div class="updates-list">
                        <div class="update-item">
                            <div class="update-icon">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                            <div class="update-content">
                                <h4>Nieuwe stageopdracht</h4>
                                <p>Er staat een nieuwe opdracht klaar voor het Zomerfestival project.</p>
                                <span class="date">Vandaag, 10:15</span>
                            </div>
                        </div>
                        <div class="update-item">
                            <div class="update-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="update-content">
                                <h4>Trainingsmodule beschikbaar</h4>
                                <p>De module "Event Veiligheid" staat nu voor je klaar.</p>
                                <span class="date">Gisteren, 16:30</span>
                            </div>
                        </div>
                    </div>
                    <a href="#" class="view-all">
                        <i class="fas fa-external-link-alt"></i> Alle updates bekijken
                    </a>
                </div>
            </div>
        </div>
    </section>

    <?php include('../includes/footer.php'); ?>

    <script src="../assets/js/scripts.js"></script>
    <script>
        // Task checkbox functionality
        document.addEventListener('DOMContentLoaded', function() {
            const taskCheckboxes = document.querySelectorAll('.task-checkbox');
            
            taskCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const taskId = this.id.replace('task', '');
                    const isCompleted = this.checked;
                    
                    // Visual feedback
                    const taskItem = this.closest('.task-item');
                    if (isCompleted) {
                        taskItem.classList.add('completed');
                    } else {
                        taskItem.classList.remove('completed');
                    }
                    
                    // Here you could add AJAX call to update task status
                    console.log(`Task ${taskId} marked as ${isCompleted ? 'completed' : 'pending'}`);
                });
            });
        });
    </script>
</body>
</html>