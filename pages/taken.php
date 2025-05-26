<?php
session_start();
include('../includes/config.php');

// Relatief pad voor navigatie
$root_path = "../";
$pageTitle = "Taken Overzicht | Flitz-Events";
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
    
    <section id="taken-overzicht">
        <div class="content-container">
            <div class="project-header">
                <h2>Taken Overzicht</h2>
                <div class="project-filters">
                    <select id="priority-filter">
                        <option value="all">Alle prioriteiten</option>
                        <option value="high">Hoge prioriteit</option>
                        <option value="medium">Gemiddelde prioriteit</option>
                        <option value="low">Lage prioriteit</option>
                    </select>
                    <input type="text" placeholder="Zoeken..." id="task-search">
                </div>
            </div>

            <div class="task-list">
                <div class="task-item priority-high">
                    <input type="checkbox" class="task-checkbox" id="task1">
                    <label for="task1">Website ontwerp afronden voor Evenement X</label>
                    <span class="task-due">Deadline: 25 Dec 2023</span>
                </div>

                <div class="task-item priority-medium">
                    <input type="checkbox" class="task-checkbox" id="task2">
                    <label for="task2">Content voorbereiden voor social media campagne</label>
                    <span class="task-due">Deadline: 28 Dec 2023</span>
                </div>

                <div class="task-item priority-medium">
                    <input type="checkbox" class="task-checkbox" id="task3">
                    <label for="task3">Vergadering plannen met stakeholders</label>
                    <span class="task-due">Deadline: 30 Dec 2023</span>
                </div>
        
                <div class="task-item priority-low">
                    <input type="checkbox" class="task-checkbox" id="task4">
                    <label for="task4">Documentatie bijwerken</label>
                    <span class="task-due">Deadline: 5 Jan 2024</span>
                </div>

                <div class="task-item priority-low">
                    <input type="checkbox" class="task-checkbox" id="task5" checked>
                    <label for="task5">Wekelijkse voortgangsrapport opstellen</label>
                    <span class="task-due">Afgerond</span>
                </div>
            </div>
            
            <button class="button-small" style="margin-top: 20px;">
                <i class="fas fa-plus"></i> Nieuwe Taak Toevoegen
            </button>
        </div>
    </section>

        <?php include('../includes/footer.php'); ?>

    <script>
        document.getElementById('priority-filter').addEventListener('change', function(e) {
            const priority = e.target.value;
            const tasks = document.querySelectorAll('.task-item');
            
            tasks.forEach(task => {
                if (priority === 'all') {
                    task.style.display = 'flex';
                } else {
                    if (task.classList.contains(`priority-${priority}`)) {
                        task.style.display = 'flex';
                    } else {
                        task.style.display = 'none';
                    }
                }
            });
        });
        
        document.getElementById('task-search').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const tasks = document.querySelectorAll('.task-item');
            
            tasks.forEach(task => {
                const taskText = task.querySelector('label').textContent.toLowerCase();
                if (taskText.includes(searchTerm)) {
                    task.style.display = 'flex';
                } else {
                    task.style.display = 'none';
                }
            });
        });
        
        document.querySelectorAll('.task-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const label = this.nextElementSibling;
                const dueDate = label.nextElementSibling;
                
                if (this.checked) {
                    label.style.textDecoration = 'line-through';
                    label.style.color = '#888';
                    dueDate.textContent = 'Afgerond';
                } else {
                    label.style.textDecoration = 'none';
                    label.style.color = '#333';
                }
            });
        });
    </script>
</body>
</html>
