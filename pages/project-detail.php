<?php
session_start();
include('../includes/config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

// Check if project ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: projecten.php");
    exit;
}

$project_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];
$is_admin = isset($_SESSION['rol']) && strtolower($_SESSION['rol']) === 'admin';

// Get project details
try {
    $stmt = $pdo->prepare("SELECT * FROM projecten WHERE id = :id");
    $stmt->execute(['id' => $project_id]);
    $project = $stmt->fetch();
    
    if (!$project) {
        header("Location: projecten.php?error=project_not_found");
        exit;
    }
} catch (PDOException $e) {
    error_log("Error fetching project: " . $e->getMessage());
    header("Location: projecten.php?error=database_error");
    exit;
}

// Get project tasks
try {
    $stmt = $pdo->prepare("
        SELECT t.*, g.naam as toegewezen_naam 
        FROM taken t 
        LEFT JOIN gebruikers g ON t.toegewezen_aan = g.id 
        WHERE t.project_id = :project_id 
        ORDER BY 
            CASE t.status 
                WHEN 'open' THEN 1 
                WHEN 'in_uitvoering' THEN 2 
                WHEN 'afgerond' THEN 3 
                WHEN 'geannuleerd' THEN 4 
            END,
            t.deadline ASC
    ");
    $stmt->execute(['project_id' => $project_id]);
    $tasks = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching tasks: " . $e->getMessage());
    $tasks = [];
}

$pageTitle = htmlspecialchars($project['naam']) . " - Project Details";
$root_path = "../";
$useIcons = true;
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include('../includes/navigation.php'); ?>

    <section id="project-detail">
        <div class="container">
            <div class="project-header">
                <div class="project-title-section">
                    <h1><?php echo htmlspecialchars($project['naam']); ?></h1>
                    <span class="project-status status-<?php echo $project['status']; ?>">
                        <?php echo ucfirst($project['status']); ?>
                    </span>
                </div>
                
                <div class="project-actions">
                    <a href="projecten.php" class="button-secondary">
                        <i class="fas fa-arrow-left"></i> Terug naar Overzicht
                    </a>
                    <?php if ($is_admin): ?>
                        <a href="project-edit.php?id=<?php echo $project_id; ?>" class="button-small">
                            <i class="fas fa-edit"></i> Bewerken
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="project-content">
                <div class="project-main">
                    <!-- Project Information -->
                    <div class="project-info-card">
                        <h3><i class="fas fa-info-circle"></i> Project Informatie</h3>
                        
                        <?php if (!empty($project['beschrijving'])): ?>
                            <div class="project-description">
                                <h4>Beschrijving</h4>
                                <p><?php echo nl2br(htmlspecialchars($project['beschrijving'])); ?></p>
                            </div>
                        <?php endif; ?>

                        <div class="project-dates">
                            <div class="date-item">
                                <i class="fas fa-calendar-plus"></i>
                                <div>
                                    <strong>Startdatum</strong>
                                    <span><?php echo date('d M Y', strtotime($project['start_datum'])); ?></span>
                                </div>
                            </div>
                            <div class="date-item">
                                <i class="fas fa-calendar-check"></i>
                                <div>
                                    <strong>Einddatum</strong>
                                    <span><?php echo date('d M Y', strtotime($project['eind_datum'])); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Progress Section -->
                        <div class="project-progress-section">
                            <h4>Voortgang</h4>
                            <div class="progress-container">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $project['voortgang']; ?>%"></div>
                                </div>
                                <span class="progress-text"><?php echo $project['voortgang']; ?>% voltooid</span>
                            </div>
                        </div>
                    </div>

                    <!-- Tasks Section -->
                    <div class="project-tasks-card">
                        <div class="card-header">
                            <h3><i class="fas fa-tasks"></i> Taken (<?php echo count($tasks); ?>)</h3>
                        </div>

                        <?php if (count($tasks) > 0): ?>
                            <div class="tasks-container">
                                <?php foreach ($tasks as $task): ?>
                                    <div class="task-item status-<?php echo $task['status']; ?>">
                                        <div class="task-header">
                                            <h4><?php echo htmlspecialchars($task['naam']); ?></h4>
                                            <span class="task-status"><?php echo ucfirst(str_replace('_', ' ', $task['status'])); ?></span>
                                        </div>
                                        
                                        <?php if (!empty($task['beschrijving'])): ?>
                                            <p class="task-description"><?php echo htmlspecialchars($task['beschrijving']); ?></p>
                                        <?php endif; ?>

                                        <div class="task-meta">
                                            <?php if ($task['deadline']): ?>
                                                <span class="task-deadline">
                                                    <i class="fas fa-calendar"></i>
                                                    Deadline: <?php echo date('d M Y', strtotime($task['deadline'])); ?>
                                                </span>
                                            <?php endif; ?>
                                            
                                            <?php if ($task['toegewezen_naam']): ?>
                                                <span class="task-assignee">
                                                    <i class="fas fa-user"></i>
                                                    <?php echo htmlspecialchars($task['toegewezen_naam']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="no-tasks">
                                <div class="no-tasks-icon">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                                <p>Nog geen taken toegevoegd aan dit project.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="project-sidebar">
                    <!-- Project Statistics -->
                    <div class="sidebar-card">
                        <h3><i class="fas fa-chart-pie"></i> Statistieken</h3>
                        <div class="stats-list">
                            <?php
                            $total_tasks = count($tasks);
                            $completed_tasks = count(array_filter($tasks, fn($task) => $task['status'] === 'afgerond'));
                            $in_progress_tasks = count(array_filter($tasks, fn($task) => $task['status'] === 'in_uitvoering'));
                            $open_tasks = count(array_filter($tasks, fn($task) => $task['status'] === 'open'));
                            ?>
                            
                            <div class="stat-item">
                                <span class="stat-label">Totaal taken:</span>
                                <span class="stat-value"><?php echo $total_tasks; ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Voltooid:</span>
                                <span class="stat-value text-success"><?php echo $completed_tasks; ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">In uitvoering:</span>
                                <span class="stat-value text-warning"><?php echo $in_progress_tasks; ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Open:</span>
                                <span class="stat-value text-info"><?php echo $open_tasks; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include('../includes/footer.php'); ?>
</body>
</html>