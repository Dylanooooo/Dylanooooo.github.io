<?php
session_start();
include('../includes/config.php');

// Controleer of gebruiker is ingelogd en admin-rechten heeft
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['rol']) !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Relatief pad voor navigatie
$root_path = "../";
$pageTitle = "Admin Dashboard - Flitz Events";

// Haal statistieken op
try {
    // Totaal aantal gebruikers
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM gebruikers WHERE actief = 1");
    $stmt->execute();
    $total_users = $stmt->fetch()['total'];
    
    // Totaal aantal projecten
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM projecten");
    $stmt->execute();
    $total_projects = $stmt->fetch()['total'];
    
    // Actieve projecten
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM projecten WHERE status = 'actief'");
    $stmt->execute();
    $active_projects = $stmt->fetch()['total'];
    
    // Openstaande taken
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM taken WHERE status IN ('open', 'in_uitvoering')");
    $stmt->execute();
    $open_tasks = $stmt->fetch()['total'];
    
    // Recente activiteiten (laatste 10 berichten)
    $stmt = $pdo->prepare("
        SELECT b.*, u1.naam as afzender_naam, u2.naam as ontvanger_naam
        FROM berichten b
        JOIN gebruikers u1 ON b.afzender_id = u1.id
        JOIN gebruikers u2 ON b.ontvanger_id = u2.id
        ORDER BY b.timestamp DESC
        LIMIT 10
    ");
    $stmt->execute();
    $recent_messages = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Error fetching admin stats: " . $e->getMessage());
    $total_users = $total_projects = $active_projects = $open_tasks = 0;
    $recent_messages = [];
}
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
    <?php include('../includes/navigation.php'); ?>

    <section id="admin-dashboard">
        <div class="container">
            <h2>Admin Dashboard</h2>
            
            <!-- Statistieken overzicht -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $total_users; ?></h3>
                        <p>Actieve Gebruikers</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $total_projects; ?></h3>
                        <p>Totaal Projecten</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $active_projects; ?></h3>
                        <p>Actieve Projecten</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $open_tasks; ?></h3>
                        <p>Openstaande Taken</p>
                    </div>
                </div>
            </div>
            
            <!-- Dashboard content -->
            <div class="dashboard-content">
                <div class="dashboard-main">
                    <!-- Recente activiteit -->
                    <div class="dashboard-widget">
                        <h3><i class="fas fa-clock"></i> Recente Activiteit</h3>
                        <div class="activity-list">
                            <?php if (count($recent_messages) > 0): ?>
                                <?php foreach (array_slice($recent_messages, 0, 5) as $message): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon">
                                            <i class="fas fa-comment"></i>
                                        </div>
                                        <div class="activity-content">
                                            <p><strong><?php echo htmlspecialchars($message['afzender_naam']); ?></strong> 
                                               stuurde een bericht naar <strong><?php echo htmlspecialchars($message['ontvanger_naam']); ?></strong></p>
                                            <span class="activity-time"><?php echo date('d M Y H:i', strtotime($message['timestamp'])); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="no-activity">Geen recente activiteit gevonden.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Snelle acties -->
                    <div class="dashboard-widget">
                        <h3><i class="fas fa-bolt"></i> Snelle Acties</h3>
                        <div class="quick-actions">
                            <a href="admin.php?tab=projects" class="action-card">
                                <div class="action-icon">
                                    <i class="fas fa-plus"></i>
                                </div>
                                <div class="action-content">
                                    <h4>Nieuw Project</h4>
                                    <p>Voeg een nieuw project toe</p>
                                </div>
                            </a>
                            
                            <a href="admin.php?tab=tasks" class="action-card">
                                <div class="action-icon">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                                <div class="action-content">
                                    <h4>Nieuwe Taak</h4>
                                    <p>Maak een nieuwe taak aan</p>
                                </div>
                            </a>
                            
                            <a href="admin.php?tab=users" class="action-card">
                                <div class="action-icon">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <div class="action-content">
                                    <h4>Gebruikers Beheren</h4>
                                    <p>Beheer gebruikersaccounts</p>
                                </div>
                            </a>
                            
                            <a href="rooster.php" class="action-card">
                                <div class="action-icon">
                                    <i class="fas fa-calendar-plus"></i>
                                </div>
                                <div class="action-content">
                                    <h4>Afspraak Plannen</h4>
                                    <p>Plan een nieuwe afspraak</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="dashboard-sidebar">
                    <!-- Systeem status -->
                    <div class="sidebar-widget">
                        <h3><i class="fas fa-server"></i> Systeem Status</h3>
                        <div class="status-list">
                            <div class="status-item">
                                <span class="status-label">Database:</span>
                                <span class="status-value online">Online</span>
                            </div>
                            <div class="status-item">
                                <span class="status-label">Chat Service:</span>
                                <span class="status-value online">Actief</span>
                            </div>
                            <div class="status-item">
                                <span class="status-label">Laatste backup:</span>
                                <span class="status-value"><?php echo date('d M Y'); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent toegevoegde gebruikers -->
                    <div class="sidebar-widget">
                        <h3><i class="fas fa-user-clock"></i> Nieuwe Gebruikers</h3>
                        <?php
                        try {
                            $stmt = $pdo->prepare("
                                SELECT naam, rol, datum_aangemaakt 
                                FROM gebruikers 
                                WHERE actief = 1 
                                ORDER BY datum_aangemaakt DESC 
                                LIMIT 5
                            ");
                            $stmt->execute();
                            $new_users = $stmt->fetchAll();
                        } catch (PDOException $e) {
                            $new_users = [];
                        }
                        ?>
                        
                        <div class="users-list">
                            <?php if (count($new_users) > 0): ?>
                                <?php foreach ($new_users as $user): ?>
                                    <div class="user-item">
                                        <div class="user-avatar">
                                            <?php
                                            $name_parts = explode(' ', $user['naam']);
                                            $initials = strtoupper(substr($name_parts[0], 0, 1));
                                            if (count($name_parts) > 1) {
                                                $initials .= strtoupper(substr(end($name_parts), 0, 1));
                                            }
                                            echo $initials;
                                            ?>
                                        </div>
                                        <div class="user-info">
                                            <strong><?php echo htmlspecialchars($user['naam']); ?></strong>
                                            <span><?php echo ucfirst($user['rol']); ?></span>
                                            <small><?php echo date('d M', strtotime($user['datum_aangemaakt'])); ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="no-users">Geen nieuwe gebruikers.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include('../includes/footer.php'); ?>
</body>
</html>