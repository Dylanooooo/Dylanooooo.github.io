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
$pageTitle = "Admin Beheer - Flitz Events";

// Haal gebruikersgegevens op
$stmt = $pdo->prepare("SELECT * FROM gebruikers");
$stmt->execute();
$gebruikers = $stmt->fetchAll();

// Haal projecten op
$stmt = $pdo->prepare("SELECT * FROM projecten");
$stmt->execute();
$projecten = $stmt->fetchAll();

// Haal taken op
$stmt = $pdo->prepare("SELECT t.*, p.naam as project_naam, g.naam as gebruiker_naam 
                      FROM taken t 
                      LEFT JOIN projecten p ON t.project_id = p.id
                      LEFT JOIN gebruikers g ON t.toegewezen_aan = g.id
                      ORDER BY t.deadline ASC");
$stmt->execute();
$taken = $stmt->fetchAll();
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
    <style>
        /* Tabbladen stijl */
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
        
        /* Formulier stijlen */
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
        
        /* Tabel stijlen */
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
        
        /* Actie knoppen */
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
        .view-button {
            background: #5bc0de;
            color: white;
        }
        
        /* Status badges */
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
        
        /* Melding stijlen */
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
    <!-- Inclusie van de consistente navigatie component -->
    <?php include('../includes/navigation.php'); ?>

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

    <?php include('../includes/footer.php'); ?>

    <script>
        // Tab switching functionality
        document.querySelectorAll('.admin-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                // Remove active class from all tabs
                document.querySelectorAll('.admin-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                
                // Hide all panels
                document.querySelectorAll('.admin-panel').forEach(p => p.classList.remove('active'));
                
                // Show selected panel
                const panelId = tab.getAttribute('data-tab') + '-panel';
                document.getElementById(panelId).classList.add('active');
            });
        });
        
        // Project form submission
        document.getElementById('add-project-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'create_project');
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Bezig...';
            submitBtn.disabled = true;
            
            fetch('../api/admin_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    this.reset();
                    setTimeout(() => {
                        location.reload(); // Reload to show new project in table
                    }, 1500);
                } else {
                    showAlert('danger', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', 'Er is een onverwachte fout opgetreden');
            })
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });
        
        // Task form submission
        document.getElementById('add-task-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'create_task');
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Bezig...';
            submitBtn.disabled = true;
            
            fetch('../api/admin_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    this.reset();
                    setTimeout(() => {
                        location.reload(); // Reload to show new task in table
                    }, 1500);
                } else {
                    showAlert('danger', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', 'Er is een onverwachte fout opgetreden');
            })
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });
        
        // Alert function
        function showAlert(type, message) {
            const alertContainer = document.getElementById('alert-container');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.textContent = message;
            
            alertContainer.innerHTML = '';
            alertContainer.appendChild(alert);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                alert.remove();
            }, 5000);
        }
        
        // Reset form function
        function resetForm(formId) {
            document.getElementById(formId).reset();
        }
        
        // Project actions
        function editProject(id, naam, beschrijving, startDatum, eindDatum, status, voortgang) {
            // Deze functie zou het project in een formulier kunnen laden voor bewerking
            console.log("Edit project: ", id);
            alert("Edit functionaliteit wordt binnenkort toegevoegd");
        }
        
        function deleteProject(id, naam) {
            // Deze functie zou een bevestiging vragen en dan het project verwijderen
            if (confirm(`Weet je zeker dat je het project "${naam}" wilt verwijderen?`)) {
                console.log("Delete project: ", id);
                alert("Delete functionaliteit wordt binnenkort toegevoegd");
            }
        }
        
        // Task actions
        function editTask(id, projectId, naam, beschrijving, deadline, status, toegewezenAan) {
            // Deze functie zou de taak in een formulier kunnen laden voor bewerking
            console.log("Edit task: ", id);
            alert("Edit functionaliteit wordt binnenkort toegevoegd");
        }
        
        function deleteTask(id, naam) {
            // Deze functie zou een bevestiging vragen en dan de taak verwijderen
            if (confirm(`Weet je zeker dat je de taak "${naam}" wilt verwijderen?`)) {
                console.log("Delete task: ", id);
                alert("Delete functionaliteit wordt binnenkort toegevoegd");
            }
        }
        
        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            // Validate project dates
            const startDateInput = document.getElementById('project-start-datum');
            const endDateInput = document.getElementById('project-eind-datum');
            
            function validateDates() {
                if (startDateInput.value && endDateInput.value) {
                    if (new Date(startDateInput.value) > new Date(endDateInput.value)) {
                        endDateInput.setCustomValidity('Einddatum moet na de startdatum liggen');
                    } else {
                        endDateInput.setCustomValidity('');
                    }
                }
            }
            
            startDateInput.addEventListener('change', validateDates);
            endDateInput.addEventListener('change', validateDates);
            
            // Set minimum date to today for new projects
            const today = new Date().toISOString().split('T')[0];
            startDateInput.setAttribute('min', today);
            endDateInput.setAttribute('min', today);
        });
        
        // Initialize any tabs from URL parameter
        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab');
            if (tab) {
                const tabElement = document.querySelector(`.admin-tab[data-tab="${tab}"]`);
                if (tabElement) {
                    tabElement.click();
                }
            }
        });
    </script>
</body>
</html>
