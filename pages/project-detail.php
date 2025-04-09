<?php
session_start();
include('../includes/config.php');

// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

// Relatief pad voor navigatie
$root_path = "../";

// Controleer of een project ID is opgegeven
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: projecten.php");
    exit;
}

$project_id = $_GET['id'];

// Haal projectgegevens op
$sql = "SELECT * FROM projecten WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $project_id);
$stmt->execute();
$project = $stmt->fetch();

// Als project niet bestaat, redirect naar projecten overzicht
if (!$project) {
    header("Location: projecten.php");
    exit;
}

// Haal taken voor dit project op
$sql = "SELECT t.*, u.naam as toegewezen_aan_naam 
        FROM taken t 
        LEFT JOIN gebruikers u ON t.toegewezen_aan = u.id
        WHERE t.project_id = :project_id
        ORDER BY t.deadline";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':project_id', $project_id);
$stmt->execute();
$taken = $stmt->fetchAll();

$pageTitle = htmlspecialchars($project['naam']) . " | Flitz-Events";
$useIcons = true;
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
    
    <section id="project-detail">
        <div class="container">
            <div class="dashboard-widget featured-widget">
                <div class="widget-header">
                    <h3><?php echo htmlspecialchars($project['naam']); ?></h3>
                    <a href="projecten.php" class="button-small">Terug naar Projecten</a>
                </div>
                
                <div class="project-overview">
                    <div class="project-progress-container">
                        <div class="svg-meter" data-percentage="<?php echo $project['voortgang']; ?>">
                            <svg width="200" height="100" viewBox="0 0 200 100">
                                <!-- Grijze achtergrond boog -->
                                <path class="meter-bg" d="M10,100 A90,90 0 0,1 190,100" stroke="#eee" stroke-width="10" fill="none" />
                                <!-- Gekleurde voortgangsboog -->
                                <path class="meter-fg" d="M10,100 A90,90 0 0,1 190,100" stroke="url(#gradient)" stroke-width="10" fill="none" />
                                <defs>
                                    <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                        <stop offset="0%" stop-color="#a71680" />
                                        <stop offset="100%" stop-color="#ec6708" />
                                    </linearGradient>
                                </defs>
                            </svg>
                            <div class="meter-needle"></div>
                            <div class="meter-center"><?php echo $project['voortgang']; ?>%</div>
                        </div>
                        <div class="meter-label">Projectvoortgang</div>
                    </div>
                    
                    <div class="project-details">
                        <h4>Projectdetails</h4>
                        <div class="project-info">
                            <div class="info-item">
                                <span class="info-label">Status:</span>
                                <span class="info-value">
                                    <span class="status-badge <?php 
                                        if ($project['status'] === 'actief') echo 'status-active';
                                        elseif ($project['status'] === 'afgerond') echo 'status-completed';
                                        else echo 'status-upcoming';
                                    ?>">
                                        <?php echo ucfirst($project['status']); ?>
                                    </span>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Start:</span>
                                <span class="info-value"><?php echo date('d M Y', strtotime($project['start_datum'])); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Deadline:</span>
                                <span class="info-value"><?php echo date('d M Y', strtotime($project['eind_datum'])); ?></span>
                            </div>
                        </div>
                        
                        <div class="project-description">
                            <h4>Omschrijving</h4>
                            <p><?php echo nl2br(htmlspecialchars($project['beschrijving'] ?? 'Geen beschrijving beschikbaar.')); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-widget">
                <div class="widget-header">
                    <h3>Taken</h3>
                    <?php if (strtolower($_SESSION['rol']) === 'admin'): ?>
                    <a href="admin.php?tab=tasks&project=<?php echo $project['id']; ?>" class="button-small">Taken Beheren</a>
                    <?php endif; ?>
                </div>
                <div class="tasks-container">
                    <?php if (count($taken) > 0): ?>
                        <ul class="task-list">
                            <?php foreach ($taken as $taak): ?>
                                <li class="task-item <?php echo 'priority-' . ($taak['prioriteit'] ?? 'medium'); ?>">
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
                        
                        <a href="#" class="view-all">Alle taken beheren</a>
                    <?php else: ?>
                        <p>Er zijn geen taken voor dit project.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="footer-container">
            <p>&copy; <?php echo date("Y"); ?> Flitz-Events | Alle rechten voorbehouden</p>
        </div>
    </footer>

    <script src="../assets/js/scripts.js"></script>
</body>
</html>
