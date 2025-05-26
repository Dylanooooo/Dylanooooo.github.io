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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                        <option value="actief">Actief</option>
                        <option value="afgerond">Afgerond</option>
                        <option value="aankomend">Aankomend</option>
                    </select>
                    <input type="text" placeholder="Zoeken..." id="project-search">
                </div>
            </div>
            
            <div class="projects-grid">
                <?php if (count($projecten) > 0): ?>
                    <?php foreach ($projecten as $project): ?>
                        <?php 
                            // Bepaal status class
                            $statusClass = '';
                            if ($project['status'] === 'actief') {
                                $statusClass = 'active';
                            } elseif ($project['status'] === 'afgerond') {
                                $statusClass = 'completed';
                            } else {
                                $statusClass = 'upcoming';
                            }
                        ?>
                        <div class="project-card <?php echo $statusClass; ?>" data-status="<?php echo $project['status']; ?>">
                            <span class="project-status <?php echo $statusClass; ?>">
                                <?php echo ucfirst($project['status']); ?>
                            </span>
                            <h3><?php echo htmlspecialchars($project['naam']); ?></h3>
                            <div class="project-dates">
                                <span><i class="fas fa-calendar-start"></i> Start: <?php echo date('d M Y', strtotime($project['start_datum'])); ?></span>
                                <span><i class="fas fa-calendar-check"></i> Einde: <?php echo date('d M Y', strtotime($project['eind_datum'])); ?></span>
                            </div>
                            <div class="project-progress">
                                <label>Voortgang</label>
                                <div class="progress-bar">
                                    <div class="progress" style="width: <?php echo $project['voortgang']; ?>%"></div>
                                </div>
                                <span><?php echo $project['voortgang']; ?>% voltooid</span>
                            </div>
                            <?php if (!empty($project['beschrijving'])): ?>
                                <div class="project-description">
                                    <p><?php echo htmlspecialchars(substr($project['beschrijving'], 0, 100)) . (strlen($project['beschrijving']) > 100 ? '...' : ''); ?></p>
                                </div>
                            <?php endif; ?>
                            <div class="project-team">
                                <span class="team-label"><i class="fas fa-users"></i> Team:</span>
                                <div class="team-avatars">
                                    <?php
                                    // Get team members for this project
                                    $team_stmt = $pdo->prepare("SELECT u.naam FROM gebruikers u 
                                                              JOIN taken t ON u.id = t.toegewezen_aan 
                                                              WHERE t.project_id = :project_id 
                                                              GROUP BY u.id LIMIT 3");
                                    $team_stmt->execute(['project_id' => $project['id']]);
                                    $team_members = $team_stmt->fetchAll();
                                    
                                    if (count($team_members) > 0):
                                        foreach ($team_members as $member):
                                            $initials = '';
                                            $name_parts = explode(' ', $member['naam']);
                                            foreach ($name_parts as $part) {
                                                if (!empty($part)) {
                                                    $initials .= strtoupper(substr($part, 0, 1));
                                                }
                                            }
                                    ?>
                                        <div class="avatar" title="<?php echo htmlspecialchars($member['naam']); ?>"><?php echo $initials; ?></div>
                                    <?php 
                                        endforeach;
                                    else:
                                    ?>
                                        <span class="no-team">Geen team toegewezen</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="project-actions">
                                <a href="project-detail.php?id=<?php echo $project['id']; ?>" class="project-details-btn">
                                    <i class="fas fa-eye"></i> Details bekijken
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-projects">
                        <div class="no-projects-icon">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <h3>Geen projecten gevonden</h3>
                        <p>Er zijn momenteel geen projecten beschikbaar.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php include('../includes/footer.php'); ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusFilter = document.getElementById('status-filter');
            const searchInput = document.getElementById('project-search');
            const projectCards = document.querySelectorAll('.project-card');

            // Filter functionality
            function filterProjects() {
                const statusValue = statusFilter.value;
                const searchValue = searchInput.value.toLowerCase();

                projectCards.forEach(card => {
                    const cardStatus = card.dataset.status;
                    const cardTitle = card.querySelector('h3').textContent.toLowerCase();
                    const cardDescription = card.querySelector('.project-description p');
                    const cardText = cardTitle + (cardDescription ? cardDescription.textContent.toLowerCase() : '');

                    const statusMatch = statusValue === 'all' || cardStatus === statusValue;
                    const searchMatch = searchValue === '' || cardText.includes(searchValue);

                    if (statusMatch && searchMatch) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }

            statusFilter.addEventListener('change', filterProjects);
            searchInput.addEventListener('input', filterProjects);
        });
    </script>
</body>
</html>
