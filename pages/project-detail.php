<?php
session_start();
include('../includes/config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Check if project ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: projecten.php");
    exit();
}

$project_id = $_GET['id'];

// Get project details
$sql = "SELECT * FROM projecten WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $project_id);
$stmt->execute();
$project = $stmt->fetch();

// If project doesn't exist, redirect
if (!$project) {
    header("Location: projecten.php");
    exit();
}

// Get project tasks
$sql = "SELECT * FROM taken WHERE project_id = :project_id ORDER BY deadline, status";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':project_id', $project_id);
$stmt->execute();
$taken = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($project['naam']); ?> | Flitz-Events Stageportaal</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
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
    
    <nav>
        <div class="container">
            <div class="menu-toggle" id="mobile-menu">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
            <ul class="nav-list">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="projecten.php">Projecten</a></li>
                <li><a href="chat.php">Chat</a></li>
            </ul>
        </div>
    </nav>
    
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
                                <path class="meter-bg" d="M10,100 A90,90 0 0,1 190,100" stroke="#eee" stroke-width="10" fill="none" />
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
                        <div class="project-info">
                            <div class="info-item">
                                <span class="info-label">Status:</span>
                                <span class="info-value"><?php echo ucfirst($project['status']); ?></span>
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
                            <h4>Beschrijving</h4>
                            <p><?php echo nl2br(htmlspecialchars($project['beschrijving'] ?? 'Geen beschrijving beschikbaar.')); ?></p>
                        </div>
                    </div>
                    
                    <div class="project-tasks">
                        <h4>Taken</h4>
                        <?php if(count($taken) > 0): ?>
                            <ul class="task-list">
                                <?php foreach($taken as $taak): ?>
                                    <li class="task-item">
                                        <input type="checkbox" id="task<?php echo $taak['id']; ?>" class="task-checkbox" <?php echo ($taak['status'] == 'afgerond') ? 'checked' : ''; ?>>
                                        <label for="task<?php echo $taak['id']; ?>"><?php echo htmlspecialchars($taak['naam']); ?></label>
                                        <span class="task-due">
                                            <?php if($taak['status'] == 'afgerond'): ?>
                                                Afgerond
                                            <?php elseif($taak['deadline']): ?>
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
        </div>
    </section>

    <footer>
        <div class="footer-container">
            <p>&copy; 2025 Flitz-Events Stageportaal | Alle rechten voorbehouden</p>
        </div>
    </footer>

    <script src="../assets/js/scripts.js"></script>
</body>
</html>
