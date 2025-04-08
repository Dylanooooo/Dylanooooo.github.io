<?php
session_start();
include('../includes/config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Get projects from database
$sql = "SELECT * FROM projecten ORDER BY status, eind_datum";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$projecten = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projecten | Flitz-Events Stageportaal</title>
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
                <li><a href="projecten.php" class="active">Projecten</a></li>
                <li><a href="chat.php">Chat</a></li>
                <?php if (isset($_SESSION['rol']) && strtolower($_SESSION['rol']) === 'admin'): ?>
                <li><a href="admin.php">Admin</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    
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
                        $statusLabel = '';
                        
                        switch($project['status']) {
                            case 'actief':
                                $statusClass = 'active';
                                $statusLabel = 'Actief';
                                break;
                            case 'afgerond':
                                $statusClass = 'completed';
                                $statusLabel = 'Afgerond';
                                break;
                            case 'aankomend':
                                $statusClass = '';
                                $statusLabel = 'Aankomend';
                                break;
                        }
                    ?>
                    <div class="project-card <?php echo $statusClass; ?>">
                        <span class="project-status" <?php if($project['status'] == 'aankomend'): ?>style="background-color: #e1f5fe; color: #0288d1;"<?php endif; ?>><?php echo $statusLabel; ?></span>
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
                        <a href="project-detail.php?id=<?php echo $project['id']; ?>" class="project-details-btn">Details bekijken</a>
                    </div>
                <?php endforeach; ?>
                
                <?php if(count($projecten) == 0): ?>
                    <p>Er zijn momenteel geen projecten beschikbaar.</p>
                <?php endif; ?>
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
