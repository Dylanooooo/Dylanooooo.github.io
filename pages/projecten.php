<?php
session_start();
require_once('../includes/config.php');

// Redirect naar login als niet ingelogd
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

// Relatief pad voor navigatie
$root_path = "../";
$pageTitle = "Projecten Overzicht | Flitz-Events";

// Haal alle projecten op
$stmt = $pdo->prepare("SELECT * FROM projecten ORDER BY status ASC, eind_datum ASC");
$stmt->execute();
$projecten = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Inclusie van de consistente navigatie component -->
    <?php include('../includes/navigation.php'); ?>
    
    <section id="projecten-overzicht">
        <div class="content-container">
            <div class="project-header">
                <h2>Projecten Overzicht</h2>
                <div class="project-filters">
                    <select id="status-filter">
                        <option value="all">Alle statussen</option>
                        <option value="active">Actief</option>
                        <option value="completed">Afgerond</option>
                        <option value="upcoming">Aankomend</option>
                    </select>
                    <input type="text" placeholder="Zoeken..." id="project-search">
                </div>
            </div>
            
            <div class="projects-grid">
                <?php foreach ($projecten as $project): ?>
                    <?php 
                        // Bepaal status class
                        $statusClass = '';
                        $statusStyle = '';
                        if ($project['status'] === 'actief') {
                            $statusClass = 'active';
                        } elseif ($project['status'] === 'afgerond') {
                            $statusClass = 'completed';
                        } else {
                            $statusClass = '';
                        }
                    ?>
                    <div class="project-card <?php echo $statusClass; ?>">
                        <span class="project-status <?php echo $statusClass; ?>">
                            <?php echo ucfirst($project['status']); ?>
                        </span>
                        <h3><?php echo htmlspecialchars($project['naam']); ?></h3>
                        <div class="project-dates">
                            <span>Start: <?php echo date('d M Y', strtotime($project['start_datum'])); ?></span>
                            <span>Einde: <?php echo date('d M Y', strtotime($project['eind_datum'])); ?></span>
                        </div>
                        <div class="project-progress">
                            <label>Voortgang</label>
                            <div class="progress-bar">
                                <div class="progress" style="width: <?php echo $project['voortgang']; ?>%"></div>
                            </div>
                            <span><?php echo $project['voortgang']; ?>% voltooid</span>
                        </div>
                        <div class="project-team">
                            <span class="team-label">Team:</span>
                            <div class="team-avatars">
                                <div class="avatar">JD</div>
                                <div class="avatar">KL</div>
                                <div class="avatar">+3</div>
                            </div>
                        </div>
                        <a href="project-detail.php?id=<?php echo $project['id']; ?>" class="project-details-btn">Details bekijken</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <footer>
        <div class="footer-container">
            <p>&copy; <?php echo date('Y'); ?> Flitz-Events Stageportaal | Alle rechten voorbehouden</p>
        </div>
    </footer>

    <script src="../assets/js/scripts.js"></script>
</body>
</html>
