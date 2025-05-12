<?php
session_start();
include('../includes/config.php');

// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

// Set relative path for navigation
$root_path = "../";
$pageTitle = "Rooster - Flitz Events";
$useIcons = true;

// Get the requested week (default to current week)
$week_offset = isset($_GET['week_offset']) ? intval($_GET['week_offset']) : 0;
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Calculate start and end dates for the requested week
$start_date = date('Y-m-d', strtotime("monday this week {$week_offset} week"));
$end_date = date('Y-m-d', strtotime("sunday this week {$week_offset} week"));

// Get all users for the meeting planner
$stmt = $pdo->prepare("SELECT id, naam FROM gebruikers ORDER BY naam");
$stmt->execute();
$users = $stmt->fetchAll();
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
        /* Rooster specific styles */
        .rooster-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .date-selector {
            display: flex;
            align-items: center;
            gap: 10px;
            background-color: #f8f9fa;
            padding: 8px 15px;
            border-radius: 25px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        #current-week {
            font-weight: 500;
            color: #333;
            font-size: 0.95rem;
        }
        
        .date-nav-btn {
            background: none;
            border: none;
            color: #a71680;
            cursor: pointer;
            font-size: 16px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        
        .date-nav-btn:hover {
            background-color: rgba(167, 22, 128, 0.1);
        }
        
        .search-input {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 25px;
            font-size: 14px;
            width: 200px;
            transition: all 0.3s;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #a71680;
            box-shadow: 0 0 0 3px rgba(167, 22, 128, 0.1);
            width: 220px;
        }
        
        #plan-meeting-btn {
            background: linear-gradient(90deg, #a71680 0%, #ec6708 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        #plan-meeting-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(167, 22, 128, 0.2);
        }
        
        #plan-meeting-btn:before {
            content: '\f067';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
        }
        
        .rooster-container {
            margin-top: 15px;
            position: relative;
            min-height: 300px;
        }
        
        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 50px 0;
            color: #666;
            font-style: italic;
        }
        
        /* Improved Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            overflow: auto;
            backdrop-filter: blur(3px);
            animation: fadeIn 0.3s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            width: 90%;
            max-width: 550px;
            animation: slideDown 0.3s ease-out;
            transform-origin: top center;
            position: relative;
        }
        
        @keyframes slideDown {
            from { 
                transform: translateY(-50px);
                opacity: 0;
            }
            to { 
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .close {
            position: absolute;
            top: 15px;
            right: 20px;
            color: #888;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.2s;
        }
        
        .close:hover,
        .close:focus {
            color: #a71680;
            text-decoration: none;
        }
        
        /* Form styling */
        .modal h2 {
            color: #333;
            margin: 0 0 20px 0;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
            font-size: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #444;
            font-size: 0.95rem;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.2s, box-shadow 0.2s;
            box-sizing: border-box;
            font-family: inherit;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #a71680;
            outline: none;
            box-shadow: 0 0 0 3px rgba(167, 22, 128, 0.1);
        }
        
        .form-group select[multiple] {
            height: 120px;
        }
        
        .form-group small {
            display: block;
            margin-top: 6px;
            color: #777;
            font-size: 0.85em;
        }
        
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .form-actions button {
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            font-size: 16px;
        }
        
        .btn-primary {
            background: linear-gradient(90deg, #a71680 0%, #ec6708 100%);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(90deg, #8a1269 0%, #d35600 100%);
            box-shadow: 0 4px 8px rgba(167, 22, 128, 0.2);
        }
        
        .btn-secondary {
            background-color: #f0f0f0;
            color: #333;
        }
        
        .btn-secondary:hover {
            background-color: #e0e0e0;
        }
        
        /* Mobile adjustments */
        @media (max-width: 768px) {
            .rooster-controls {
                flex-direction: column;
                align-items: stretch;
            }
            
            .date-selector {
                justify-content: center;
            }
            
            .search-input {
                width: 100%;
            }
            
            #plan-meeting-btn {
                width: 100%;
                justify-content: center;
            }
            
            .modal-content {
                margin: 10% auto;
                padding: 20px;
                width: 95%;
            }
            
            .form-actions {
                flex-direction: column-reverse;
                gap: 10px;
            }
            
            .form-actions button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Include the consistent navigation component -->
    <?php include('../includes/navigation.php'); ?>

    <div class="intro-banner-wrapper">
        <div class="intro-banner">
            <img src="../assets/images/FlitzBanner.png" alt="Flitz Events Banner" class="banner-img">
            <div class="banner-text">
                <div class="banner-container">
                    <h3>Welkom bij je stage!</h3>
                    <p>Belangrijke informatie: Stagebegeleider: Milan Laroes (te bereiken via chat) | Aanwezigheid: Ma-Do 9:00-17:00</p>
                </div>
            </div>
        </div>
    </div>

    <section id="rooster-page">
        <div class="container">
            <h2>Rooster</h2>
            
            <div class="rooster-controls">
                <input type="text" id="search" placeholder="Zoek op medewerker..." class="search-input">
                <div class="date-selector">
                    <button id="prev-week" class="date-nav-btn" aria-label="Vorige week"><i class="fas fa-chevron-left"></i></button>
                    <span id="current-week">Week <?php echo date('W', strtotime($start_date)); ?> (<?php echo date('d M', strtotime($start_date)); ?> - <?php echo date('d M', strtotime($end_date)); ?>)</span>
                    <button id="next-week" class="date-nav-btn" aria-label="Volgende week"><i class="fas fa-chevron-right"></i></button>
                </div>
                <button type="button" id="plan-meeting-btn" class="btn">Plan Afspraak</button>
            </div>
            
            <div id="rooster" class="rooster-container">
                <div class="loading">Rooster wordt geladen...</div>
            </div>
        </div>
    </section>

    <!-- Add meeting planning modal with improved styling -->
    <div id="meeting-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('meeting-modal').style.display='none'">&times;</span>
            <h2>Plan Nieuwe Afspraak</h2>
            <form id="meeting-form">
                <div class="form-group">
                    <label for="meeting-date">Datum:</label>
                    <input type="date" id="meeting-date" name="dag" required>
                </div>
                
                <div class="form-group">
                    <label for="meeting-start">Starttijd:</label>
                    <input type="time" id="meeting-start" name="start_tijd" required>
                </div>
                
                <div class="form-group">
                    <label for="meeting-end">Eindtijd:</label>
                    <input type="time" id="meeting-end" name="eind_tijd" required>
                </div>
                
                <div class="form-group">
                    <label for="meeting-location">Locatie:</label>
                    <input type="text" id="meeting-location" name="locatie" placeholder="Bijv. Vergaderzaal, Kantoor" required>
                </div>
                
                <div class="form-group">
                    <label for="meeting-notes">Opmerkingen:</label>
                    <textarea id="meeting-notes" name="opmerkingen" placeholder="Extra informatie over de afspraak"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="meeting-participants">Deelnemers:</label>
                    <select id="meeting-participants" name="gebruiker_id[]" multiple required>
                        <option value="<?php echo $_SESSION['user_id']; ?>" selected><?php echo htmlspecialchars($_SESSION['naam']); ?> (jij)</option>
                        <?php foreach ($users as $user): ?>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['naam']); ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <small>Houd Ctrl ingedrukt om meerdere deelnemers te selecteren</small>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="cancel-meeting" onclick="document.getElementById('meeting-modal').style.display='none'">Annuleren</button>
                    <button type="submit" class="btn btn-primary">Afspraak Plannen</button>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <div class="footer-container">
            <p>&copy; <?php echo date('Y'); ?> Flitz-Events Stageportaal | Alle rechten voorbehouden</p>
        </div>
    </footer>

    <script>
        let currentWeekOffset = <?php echo $week_offset; ?>;
        
        function updateWeekDisplay() {
            // Calculate dates for current offset
            const today = new Date();
            const startOfWeek = new Date(today);
            startOfWeek.setDate(today.getDate() - today.getDay() + 1 + (currentWeekOffset * 7));
            
            const endOfWeek = new Date(startOfWeek);
            endOfWeek.setDate(startOfWeek.getDate() + 6);
            
            // Format dates
            const startStr = startOfWeek.getDate() + ' ' + 
                             startOfWeek.toLocaleString('nl-NL', { month: 'short' });
            
            const endStr = endOfWeek.getDate() + ' ' + 
                           endOfWeek.toLocaleString('nl-NL', { month: 'short' });
            
            const weekNum = getWeekNumber(startOfWeek);
            
            // Update display
            document.getElementById('current-week').textContent = 
                `Week ${weekNum} (${startStr} - ${endStr})`;
        }
        
        function getWeekNumber(d) {
            // Copy date so don't modify original
            d = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
            // Set to nearest Thursday: current date + 4 - current day number
            d.setUTCDate(d.getUTCDate() + 4 - (d.getUTCDay()||7));
            // Get first day of year
            const yearStart = new Date(Date.UTC(d.getUTCFullYear(),0,1));
            // Calculate full weeks to nearest Thursday
            const weekNo = Math.ceil(( ( (d - yearStart) / 86400000) + 1)/7);
            return weekNo;
        }
        
        function laadRooster() {
            const searchTerm = document.getElementById('search').value;
            const url = `../api/rooster_api.php?action=view&week_offset=${currentWeekOffset}&search=${encodeURIComponent(searchTerm)}`;
            
            document.getElementById('rooster').innerHTML = '<div class="loading">Rooster wordt geladen...</div>';
            
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('rooster').innerHTML = html;
                    
                    // Add click handlers to day cells after loading
                    document.querySelectorAll('.day-cell').forEach(cell => {
                        cell.addEventListener('click', function(e) {
                            if (e.target.closest('.event-item')) return;
                            
                            const date = this.getAttribute('data-date');
                            const time = this.getAttribute('data-time');
                            
                            if (date && time) {
                                document.getElementById('meeting-date').value = date;
                                document.getElementById('meeting-start').value = time;
                                
                                // Set end time to one hour later
                                const parts = time.split(':');
                                const endHour = (parseInt(parts[0]) + 1).toString().padStart(2, '0');
                                document.getElementById('meeting-end').value = endHour + ':' + parts[1];
                                
                                document.getElementById('meeting-modal').style.display = 'block';
                            }
                        });
                    });
                })
                .catch(error => {
                    console.error('Fout bij laden rooster:', error);
                    document.getElementById('rooster').innerHTML = 
                        '<div class="error-message">Er is een fout opgetreden bij het laden van het rooster.</div>';
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateWeekDisplay();
            laadRooster();
            
            // Navigation through weeks
            document.getElementById('prev-week').addEventListener('click', function() {
                currentWeekOffset--;
                updateWeekDisplay();
                laadRooster();
            });
            
            document.getElementById('next-week').addEventListener('click', function() {
                currentWeekOffset++;
                updateWeekDisplay();
                laadRooster();
            });
            
            // Search functionality - debounced search
            let searchTimeout;
            document.getElementById('search').addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(laadRooster, 500);
            });
            
            // Plan meeting button
            document.getElementById('plan-meeting-btn').addEventListener('click', function() {
                // Set default date to today
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('meeting-date').value = today;
                document.getElementById('meeting-start').value = '09:00';
                document.getElementById('meeting-end').value = '10:00';
                
                document.getElementById('meeting-modal').style.display = 'block';
            });
            
            // Form submission for meeting
            document.getElementById('meeting-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                formData.append('action', 'create_meeting');
                
                fetch('../api/rooster_api.php', {
                    method: 'POST',
                    body: formData
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success) {
                        alert('Afspraak succesvol gepland!');
                        document.getElementById('meeting-modal').style.display = 'none';
                        laadRooster(); // Reload the roster instead of the whole page
                    } else {
                        // Check if we have conflict details
                        if (data.conflicts) {
                            let errorMessage = data.message + '\n\n' + data.conflicts.join('\n');
                            alert(errorMessage);
                        } else {
                            alert('Fout: ' + data.message);
                        }
                    }
                })
                .catch(function(error) {
                    console.error('Error:', error);
                    alert('Er is een fout opgetreden bij het plannen van de afspraak.');
                });
            });
        });

        // Auto-refresh every 5 minutes
        setInterval(laadRooster, 300000);
    </script>
</body>
</html>