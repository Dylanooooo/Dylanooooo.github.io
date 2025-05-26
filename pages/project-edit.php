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

// Check if user is admin or can edit this project
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $naam = trim($_POST['naam']);
        $beschrijving = trim($_POST['beschrijving']);
        $start_datum = $_POST['start_datum'];
        $eind_datum = $_POST['eind_datum'];
        $status = $_POST['status'];
        $voortgang = intval($_POST['voortgang']);
        
        // Validate inputs
        if (empty($naam) || empty($start_datum) || empty($eind_datum)) {
            throw new Exception("Naam, startdatum en einddatum zijn verplicht.");
        }
        
        if ($voortgang < 0 || $voortgang > 100) {
            throw new Exception("Voortgang moet tussen 0 en 100% zijn.");
        }
        
        if (strtotime($start_datum) > strtotime($eind_datum)) {
            throw new Exception("Startdatum kan niet na de einddatum zijn.");
        }
        
        // Update project
        $stmt = $pdo->prepare("
            UPDATE projecten 
            SET naam = :naam, beschrijving = :beschrijving, start_datum = :start_datum, 
                eind_datum = :eind_datum, status = :status, voortgang = :voortgang,
                laatst_bijgewerkt = NOW()
            WHERE id = :id
        ");
        
        $stmt->execute([
            'naam' => $naam,
            'beschrijving' => $beschrijving,
            'start_datum' => $start_datum,
            'eind_datum' => $eind_datum,
            'status' => $status,
            'voortgang' => $voortgang,
            'id' => $project_id
        ]);
        
        // Log activity
        if (function_exists('logActivity')) {
            logActivity($_SESSION['user_id'], 'project_updated', "Updated project: $naam");
        }
        
        $success_message = "Project succesvol bijgewerkt!";
        
        // Refresh project data
        $stmt = $pdo->prepare("SELECT * FROM projecten WHERE id = :id");
        $stmt->execute(['id' => $project_id]);
        $project = $stmt->fetch();
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

$pageTitle = "Project Bewerken - " . htmlspecialchars($project['naam']);
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

    <section id="project-edit">
        <div class="container">
            <div class="page-header">
                <h2><i class="fas fa-edit"></i> Project Bewerken</h2>
                <div class="header-actions">
                    <a href="project-detail.php?id=<?php echo $project_id; ?>" class="button-small">
                        <i class="fas fa-arrow-left"></i> Terug naar Project
                    </a>
                </div>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="edit-form-container">
                <form method="POST" class="project-edit-form">
                    <div class="form-section">
                        <h3>Basis Informatie</h3>
                        
                        <div class="form-row">
                            <label for="naam">Projectnaam *</label>
                            <input type="text" id="naam" name="naam" value="<?php echo htmlspecialchars($project['naam']); ?>" required>
                        </div>

                        <div class="form-row">
                            <label for="beschrijving">Beschrijving</label>
                            <textarea id="beschrijving" name="beschrijving" rows="4"><?php echo htmlspecialchars($project['beschrijving'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Planning</h3>
                        
                        <div class="form-row-group">
                            <div class="form-row">
                                <label for="start_datum">Startdatum *</label>
                                <input type="date" id="start_datum" name="start_datum" value="<?php echo $project['start_datum']; ?>" required>
                            </div>

                            <div class="form-row">
                                <label for="eind_datum">Einddatum *</label>
                                <input type="date" id="eind_datum" name="eind_datum" value="<?php echo $project['eind_datum']; ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Status & Voortgang</h3>
                        
                        <div class="form-row-group">
                            <div class="form-row">
                                <label for="status">Status</label>
                                <select id="status" name="status" required>
                                    <option value="aankomend" <?php echo ($project['status'] === 'aankomend') ? 'selected' : ''; ?>>Aankomend</option>
                                    <option value="actief" <?php echo ($project['status'] === 'actief') ? 'selected' : ''; ?>>Actief</option>
                                    <option value="afgerond" <?php echo ($project['status'] === 'afgerond') ? 'selected' : ''; ?>>Afgerond</option>
                                    <option value="geannuleerd" <?php echo ($project['status'] === 'geannuleerd') ? 'selected' : ''; ?>>Geannuleerd</option>
                                </select>
                            </div>

                            <div class="form-row">
                                <label for="voortgang">Voortgang (%)</label>
                                <div class="progress-input-container">
                                    <input type="range" id="voortgang" name="voortgang" min="0" max="100" value="<?php echo $project['voortgang']; ?>" oninput="updateProgressDisplay(this.value)">
                                    <span id="progress-display"><?php echo $project['voortgang']; ?>%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="project-detail.php?id=<?php echo $project_id; ?>" class="button-secondary">
                            <i class="fas fa-times"></i> Annuleren
                        </a>
                        <button type="submit" class="button-small">
                            <i class="fas fa-save"></i> Wijzigingen Opslaan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <?php include('../includes/footer.php'); ?>

    <script>
        function updateProgressDisplay(value) {
            document.getElementById('progress-display').textContent = value + '%';
        }

        // Form validation
        document.querySelector('.project-edit-form').addEventListener('submit', function(e) {
            const startDate = new Date(document.getElementById('start_datum').value);
            const endDate = new Date(document.getElementById('eind_datum').value);
            
            if (startDate >= endDate) {
                e.preventDefault();
                alert('Startdatum moet voor de einddatum liggen.');
                return false;
            }
        });
    </script>
</body>
</html>