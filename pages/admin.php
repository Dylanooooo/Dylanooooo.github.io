<?php
session_start();
include('../includes/config.php');

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php?error=Je moet ingelogd zijn om deze pagina te bekijken");
    exit();
}

// Debug information to check what roles are set
error_log("User role check: " . $_SESSION['rol'] ?? 'No role set');

// Check admin role with case-insensitive comparison
if (!isset($_SESSION['rol']) || strtolower($_SESSION['rol']) !== 'admin') {
    header("Location: ../index.php?error=Je hebt geen toegang tot deze pagina");
    exit();
}

// Get all projects
$sql = "SELECT * FROM projecten ORDER BY datum_aangemaakt DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$projecten = $stmt->fetchAll();

// Get all tasks
$sql = "SELECT t.*, p.naam as project_naam, u.naam as gebruiker_naam 
        FROM taken t 
        LEFT JOIN projecten p ON t.project_id = p.id 
        LEFT JOIN gebruikers u ON t.toegewezen_aan = u.id 
        ORDER BY t.datum_aangemaakt DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$taken = $stmt->fetchAll();

// Get all users
$sql = "SELECT id, naam, email, rol FROM gebruikers ORDER BY naam";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$gebruikers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Beheer | Flitz-Events Stageportaal</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .admin-tab {
            padding: 10px 20px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
        }
        .admin-tab.active {
            border-bottom-color: #a71680;
            font-weight: 500;
        }
        .admin-panel {
            display: none;
        }
        .admin-panel.active {
            display: block;
        }
        .admin-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .form-row {
            margin-bottom: 15px;
        }
        .form-row label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .form-row input, .form-row select, .form-row textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-row textarea {
            min-height: 100px;
        }
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .admin-table th {
            text-align: left;
            padding: 10px;
            background: #f0f0f0;
            border-bottom: 2px solid #ddd;
        }
        .admin-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .admin-table tr:hover {
            background-color: #f9f9f9;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .action-button {
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        .edit-button {
            background: #f0ad4e;
            color: white;
        }
        .delete-button {
            background: #d9534f;
            color: white;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            background: #eee;
        }
        .status-active {
            background: #a71680;
            color: white;
        }
        .status-upcoming {
            background: #e1f5fe;
            color: #0288d1;
        }
        .status-completed {
            background: #4caf50;
            color: white;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <h1>Flitz-Events Admin Beheer</h1>
            <div class="user-info">
                <span id="user-name"><?php echo htmlspecialchars($_SESSION['naam']); ?></span>
                <a href="../auth/logout.php" id="logout-btn">Uitloggen</a>
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
                <li><a href="admin.php" class="active">Admin</a></li>
            </ul>
        </div>
    </nav>

    <section id="admin" style="flex: 1;">
        <div class="container">
            <h2>Admin Beheer</h2>
            
            <div id="alert-container"></div>
            
            <div class="admin-tabs">
                <div class="admin-tab active" data-tab="projects">Projecten</div>
                <div class="admin-tab" data-tab="tasks">Taken</div>
                <div class="admin-tab" data-tab="users">Gebruikers</div>
            </div>
            
            <!-- Projects Panel -->
            <div class="admin-panel active" id="projects-panel">
                <h3>Project Toevoegen</h3>
                <form id="add-project-form" class="admin-form">
                    <div class="form-row">
                        <label for="project-naam">Projectnaam</label>
                        <input type="text" id="project-naam" name="naam" required>
                    </div>
                    <div class="form-row">
                        <label for="project-beschrijving">Beschrijving</label>
                        <textarea id="project-beschrijving" name="beschrijving"></textarea>
                    </div>
                    <div class="form-row" style="display: flex; gap: 15px;">
                        <div style="flex: 1;">
                            <label for="project-start-datum">Startdatum</label>
                            <input type="date" id="project-start-datum" name="start_datum" required>
                        </div>
                        <div style="flex: 1;">
                            <label for="project-eind-datum">Einddatum</label>
                            <input type="date" id="project-eind-datum" name="eind_datum" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <label for="project-status">Status</label>
                        <select id="project-status" name="status" required>
                            <option value="aankomend">Aankomend</option>
                            <option value="actief">Actief</option>
                            <option value="afgerond">Afgerond</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label for="project-voortgang">Voortgang (%)</label>
                        <input type="number" id="project-voortgang" name="voortgang" min="0" max="100" value="0" required>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="button-small" onclick="resetForm('add-project-form')">Annuleren</button>
                        <button type="submit" class="button-small">Project Toevoegen</button>
                    </div>
                </form>
                
                <h3>Bestaande Projecten</h3>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Naam</th>
                            <th>Status</th>
                            <th>Startdatum</th>
                            <th>Einddatum</th>
                            <th>Voortgang</th>
                            <th>Acties</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projecten as $project): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($project['naam']); ?></td>
                                <td>
                                    <span class="status-badge <?php 
                                        if ($project['status'] === 'actief') echo 'status-active';
                                        elseif ($project['status'] === 'afgerond') echo 'status-completed';
                                        else echo 'status-upcoming';
                                    ?>">
                                        <?php echo ucfirst($project['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d-m-Y', strtotime($project['start_datum'])); ?></td>
                                <td><?php echo date('d-m-Y', strtotime($project['eind_datum'])); ?></td>
                                <td><?php echo $project['voortgang']; ?>%</td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="project-detail.php?id=<?php echo $project['id']; ?>" class="action-button view-button">Bekijken</a>
                                        <button class="action-button edit-button" 
                                                onclick="editProject(<?php echo $project['id']; ?>, 
                                                          '<?php echo addslashes($project['naam']); ?>', 
                                                          '<?php echo addslashes($project['beschrijving'] ?? ''); ?>', 
                                                          '<?php echo $project['start_datum']; ?>', 
                                                          '<?php echo $project['eind_datum']; ?>', 
                                                          '<?php echo $project['status']; ?>', 
                                                          <?php echo $project['voortgang']; ?>)">
                                            Bewerken
                                        </button>
                                        <button class="action-button delete-button" 
                                                onclick="deleteProject(<?php echo $project['id']; ?>, '<?php echo addslashes($project['naam']); ?>')">
                                            Verwijderen
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (count($projecten) === 0): ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">Geen projecten gevonden</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Tasks Panel -->
            <div class="admin-panel" id="tasks-panel">
                <h3>Taak Toevoegen</h3>
                <form id="add-task-form" class="admin-form">
                    <div class="form-row">
                        <label for="task-project">Project</label>
                        <select id="task-project" name="project_id" required>
                            <option value="">-- Selecteer project --</option>
                            <?php foreach ($projecten as $project): ?>
                                <option value="<?php echo $project['id']; ?>"><?php echo htmlspecialchars($project['naam']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-row">
                        <label for="task-naam">Taaknaam</label>
                        <input type="text" id="task-naam" name="naam" required>
                    </div>
                    <div class="form-row">
                        <label for="task-beschrijving">Beschrijving</label>
                        <textarea id="task-beschrijving" name="beschrijving"></textarea>
                    </div>
                    <div class="form-row">
                        <label for="task-deadline">Deadline</label>
                        <input type="date" id="task-deadline" name="deadline">
                    </div>
                    <div class="form-row">
                        <label for="task-status">Status</label>
                        <select id="task-status" name="status" required>
                            <option value="open">Open</option>
                            <option value="in_uitvoering">In uitvoering</option>
                            <option value="afgerond">Afgerond</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label for="task-assigned">Toegewezen aan</label>
                        <select id="task-assigned" name="toegewezen_aan">
                            <option value="">-- Niet toegewezen --</option>
                            <?php foreach ($gebruikers as $gebruiker): ?>
                                <option value="<?php echo $gebruiker['id']; ?>"><?php echo htmlspecialchars($gebruiker['naam']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="button-small" onclick="resetForm('add-task-form')">Annuleren</button>
                        <button type="submit" class="button-small">Taak Toevoegen</button>
                    </div>
                </form>
                
                <h3>Bestaande Taken</h3>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Naam</th>
                            <th>Status</th>
                            <th>Deadline</th>
                            <th>Toegewezen aan</th>
                            <th>Acties</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($taken as $taak): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($taak['project_naam']); ?></td>
                                <td><?php echo htmlspecialchars($taak['naam']); ?></td>
                                <td>
                                    <span class="status-badge <?php 
                                        if ($taak['status'] === 'afgerond') echo 'status-completed';
                                        elseif ($taak['status'] === 'in_uitvoering') echo 'status-active';
                                        else echo '';
                                    ?>">
                                        <?php echo str_replace('_', ' ', ucfirst($taak['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo $taak['deadline'] ? date('d-m-Y', strtotime($taak['deadline'])) : '-'; ?></td>
                                <td><?php echo $taak['gebruiker_naam'] ?? '-'; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="action-button edit-button" 
                                                onclick="editTask(<?php echo $taak['id']; ?>,
                                                         <?php echo $taak['project_id']; ?>,
                                                         '<?php echo addslashes($taak['naam']); ?>',
                                                         '<?php echo addslashes($taak['beschrijving'] ?? ''); ?>',
                                                         '<?php echo $taak['deadline'] ?? ''; ?>',
                                                         '<?php echo $taak['status']; ?>',
                                                         <?php echo $taak['toegewezen_aan'] ? $taak['toegewezen_aan'] : 'null'; ?>)">
                                            Bewerken
                                        </button>
                                        <button class="action-button delete-button" 
                                                onclick="deleteTask(<?php echo $taak['id']; ?>, '<?php echo addslashes($taak['naam']); ?>')">
                                            Verwijderen
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (count($taken) === 0): ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">Geen taken gevonden</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Users Panel -->
            <div class="admin-panel" id="users-panel">
                <h3>Gebruikers</h3>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Naam</th>
                            <th>E-mail</th>
                            <th>Rol</th>
                            <th>Acties</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($gebruikers as $gebruiker): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($gebruiker['naam']); ?></td>
                                <td><?php echo htmlspecialchars($gebruiker['email']); ?></td>
                                <td><?php echo ucfirst($gebruiker['rol']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="action-button edit-button">Bewerken</button>
                                        <?php if ($gebruiker['id'] !== $_SESSION['user_id']): ?>
                                            <button class="action-button delete-button">Verwijderen</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <footer>
        <div class="footer-container">
            <p>&copy; 2025 Flitz-Events Stageportaal | Alle rechten voorbehouden</p>
        </div>
    </footer>

    <script>
        // Tab switching functionality
        document.querySelectorAll('.admin-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                document.querySelectorAll('.admin-tab').forEach(t => t.classList.remove('active'));
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Hide all panels
                document.querySelectorAll('.admin-panel').forEach(p => p.classList.remove('active'));
                // Show corresponding panel
                document.getElementById(`${this.dataset.tab}-panel`).classList.add('active');
            });
        });

        // Form reset function
        function resetForm(formId) {
            document.getElementById(formId).reset();
        }

        // Project form submission
        document.getElementById('add-project-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'add_project');
            
            fetch('../api/admin_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Project succesvol toegevoegd!', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showAlert('Fout bij toevoegen: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Er is een fout opgetreden bij het verwerken van het verzoek.', 'danger');
            });
        });

        // Task form submission
        document.getElementById('add-task-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'add_task');
            
            fetch('../api/admin_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Taak succesvol toegevoegd!', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showAlert('Fout bij toevoegen: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Er is een fout opgetreden bij het verwerken van het verzoek.', 'danger');
            });
        });

        // Function to show alerts
        function showAlert(message, type) {
            const alertContainer = document.getElementById('alert-container');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.textContent = message;
            
            alertContainer.appendChild(alert);
            
            // Remove after 5 seconds
            setTimeout(() => {
                alert.remove();
            }, 5000);
        }

        // Edit project function
        function editProject(id, naam, beschrijving, startDatum, eindDatum, status, voortgang) {
            // Here you'd implement editing functionality
            // For simplicity, we'll just alert the details for now
            alert(`Project bewerken: ${naam}`);
        }

        // Delete project function
        function deleteProject(id, naam) {
            if (confirm(`Weet je zeker dat je het project "${naam}" wilt verwijderen?`)) {
                const formData = new FormData();
                formData.append('action', 'delete_project');
                formData.append('id', id);
                
                fetch('../api/admin_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Project succesvol verwijderd!', 'success');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showAlert('Fout bij verwijderen: ' + data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Er is een fout opgetreden bij het verwerken van het verzoek.', 'danger');
                });
            }
        }

        // Edit task function
        function editTask(id, projectId, naam, beschrijving, deadline, status, toegewezenAan) {
            // Here you'd implement editing functionality
            // For simplicity, we'll just alert the details for now
            alert(`Taak bewerken: ${naam}`);
        }

        // Delete task function
        function deleteTask(id, naam) {
            if (confirm(`Weet je zeker dat je de taak "${naam}" wilt verwijderen?`)) {
                const formData = new FormData();
                formData.append('action', 'delete_task');
                formData.append('id', id);
                
                fetch('../api/admin_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Taak succesvol verwijderd!', 'success');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showAlert('Fout bij verwijderen: ' + data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Er is een fout opgetreden bij het verwerken van het verzoek.', 'danger');
                });
            }
        }
    </script>

    <script src="../assets/js/scripts.js"></script>
</body>
</html>
