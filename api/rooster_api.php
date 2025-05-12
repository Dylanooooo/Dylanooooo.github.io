<?php
session_start();
require_once('../includes/config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        http_response_code(401);
        echo '<div class="error-message">Niet geautoriseerd. Log in om het rooster te bekijken.</div>';
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Je moet ingelogd zijn.']);
    }
    exit;
}

// This file handles both viewing the roster and creating meetings
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : 'view');

// CREATE TABLE IF NEEDED
function createRoosterTable($pdo) {
    try {
        // First check if gebruikers table exists (required for foreign key)
        $stmt = $pdo->query("SHOW TABLES LIKE 'gebruikers'");
        $gebruikers_exists = ($stmt->rowCount() > 0);
        
        if (!$gebruikers_exists) {
            // Can't create rooster table without gebruikers table
            return false;
        }
        
        // Create the rooster table with proper foreign key
        $pdo->exec("CREATE TABLE IF NOT EXISTS `rooster` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `gebruiker_id` int(11) NOT NULL,
            `dag` date NOT NULL,
            `start_tijd` time NOT NULL,
            `eind_tijd` time NOT NULL,
            `locatie` varchar(255) NOT NULL,
            `opmerkingen` text DEFAULT NULL,
            `datum_aangemaakt` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `gebruiker_id` (`gebruiker_id`),
            CONSTRAINT `rooster_gebruiker_fk` FOREIGN KEY (`gebruiker_id`) 
            REFERENCES `gebruikers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        
        return true;
    } catch (Exception $e) {
        error_log("Failed to create rooster table: " . $e->getMessage());
        return false;
    }
}

// HANDLE ROSTER VIEW
if ($action === 'view') {
    // Get the requested week (default to current week)
    $week_offset = isset($_GET['week_offset']) ? intval($_GET['week_offset']) : 0;
    $search = isset($_GET['search']) ? $_GET['search'] : '';

    // Calculate start and end dates for the requested week
    $start_date = date('Y-m-d', strtotime("monday this week {$week_offset} week"));
    $end_date = date('Y-m-d', strtotime("sunday this week {$week_offset} week"));

    try {
        // Check if table exists first
        $stmt = $pdo->query("SHOW TABLES LIKE 'rooster'");
        $table_exists = ($stmt->rowCount() > 0);
        
        if (!$table_exists) {
            // Try to create the table
            $table_created = createRoosterTable($pdo);
            
            if (!$table_created) {
                echo '<div class="error-message">
                    De rooster tabel bestaat nog niet. Importeer het bestand flitz_events.sql in de database.
                    <a href="../docs/setup.md" target="_blank">Zie setup instructies</a> voor meer informatie.
                </div>';
                exit;
            }
        }
        
        // Build query - join with gebruikers to get names
        // Only show meetings for the current user
        $query = "SELECT r.*, g.naam as gebruiker_naam 
                FROM rooster r
                JOIN gebruikers g ON r.gebruiker_id = g.id
                WHERE r.dag BETWEEN :start_date AND :end_date
                AND r.gebruiker_id = :current_user_id";
        
        // Add search filter if provided
        if (!empty($search)) {
            $query .= " AND g.naam LIKE :search";
        }
        
        $query .= " ORDER BY r.dag ASC, r.start_tijd ASC";
        
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->bindParam(':current_user_id', $_SESSION['user_id']);
        
        if (!empty($search)) {
            $search_param = "%{$search}%";
            $stmt->bindParam(':search', $search_param);
        }
        
        $stmt->execute();
        $roster_items = $stmt->fetchAll();
        
        // Generate weekly calendar grid
        echo '<div class="calendar-grid">';
        
        // Update the CSS for the calendar and events with improved styling
        echo '<style>
        .calendar-grid {
            display: flex;
            flex-direction: column;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            background: white;
            margin-top: 20px;
            font-size: 13px;
        }
        
        .calendar-header {
            display: grid;
            grid-template-columns: 60px repeat(7, 1fr);
            background: linear-gradient(90deg, #a71680 0%, #ec6708 100%);
            color: white;
            font-weight: 500;
        }
        
        .day-header, .time-column {
            padding: 8px 5px;
            text-align: center;
            border-right: 1px solid rgba(255,255,255,0.2);
        }
        
        .day-header {
            display: flex;
            flex-direction: column;
        }
        
        .day-name {
            font-weight: bold;
            margin-bottom: 3px;
            font-size: 0.9em;
        }
        
        .day-date {
            font-size: 0.8em;
            opacity: 0.9;
        }
        
        .calendar-body {
            display: flex;
            flex-direction: column;
        }
        
        .time-row {
            display: grid;
            grid-template-columns: 60px repeat(7, 1fr);
            border-bottom: 1px solid #eee;
            height: 80px; /* Reduced height for each row */
            min-height: 80px;
            position: relative;
        }
        
        .time-label {
            padding: 5px;
            background: #f8f9fa;
            border-right: 1px solid #eee;
            font-size: 0.75em;
            color: #666;
            text-align: center;
            position: relative;
        }
        
        .day-cell {
            padding: 2px;
            border-right: 1px solid #eee;
            position: relative;
            height: 100%;
            cursor: pointer;
            overflow: visible; /* Allow events to overflow */
        }
        
        .day-cell:hover {
            background-color: rgba(167, 22, 128, 0.03);
        }
        
        .day-cell.today {
            background-color: rgba(167, 22, 128, 0.05);
        }
        
        .event-item {
            position: absolute;
            background: linear-gradient(45deg, #a71680 0%, #ec6708 100%);
            color: white;
            border-radius: 4px;
            font-size: 0.8em;
            padding: 6px;
            margin: 0 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            z-index: 10;
        }
        
        .event-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            z-index: 100;
        }
        
        .event-time {
            font-weight: bold;
            margin-bottom: 3px;
            font-size: 0.9em;
            line-height: 1.1;
        }
        
        .event-title {
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.2;
        }
        
        /* Mobile adjustments */
        @media (max-width: 768px) {
            .time-row {
                height: 60px;
                min-height: 60px;
            }
            
            .day-header, .time-column {
                padding: 5px 2px;
                font-size: 0.8em;
            }
        }
        
        @media (max-width: 576px) {
            .calendar-grid {
                overflow-x: auto;
            }
            
            .calendar-header, .time-row {
                min-width: 700px;
            }
        }
        </style>';
        
        // Array of days for the week
        $days = ['Maandag', 'Dinsdag', 'Woensdag', 'Donderdag', 'Vrijdag', 'Zaterdag', 'Zondag'];
        
        // Create header row with the days
        echo '<div class="calendar-header">';
        echo '<div class="time-column">Tijd</div>';
        for ($i = 0; $i < 7; $i++) {
            $current_date = date('Y-m-d', strtotime($start_date . " +{$i} days"));
            $is_today = $current_date == date('Y-m-d');
            $day_class = $is_today ? 'day-header today' : 'day-header';
            
            echo "<div class='{$day_class}' data-date='{$current_date}'>";
            echo "<span class='day-name'>{$days[$i]}</span>";
            echo "<span class='day-date'>" . date('d/m', strtotime($current_date)) . "</span>";
            echo '</div>';
        }
        echo '</div>';
        
        // Group roster items by day
        $grouped_items = [];
        for ($i = 0; $i < 7; $i++) {
            $current_date = date('Y-m-d', strtotime($start_date . " +{$i} days"));
            $grouped_items[$current_date] = [];
        }
        
        foreach ($roster_items as $item) {
            $grouped_items[$item['dag']][] = $item;
        }
        
        // Display calendar content
        echo '<div class="calendar-body">';
        
        // Time slots (00:00 - 24:00 in 2-hour increments)
        $time_slots = [
            '00:00', '02:00', '04:00', '06:00', '08:00', '10:00', '12:00', 
            '14:00', '16:00', '18:00', '20:00', '22:00', '24:00'
        ];
        
        // Output the time slots and events
        foreach ($time_slots as $index => $time_slot) {
            // Skip the last time slot since it's just the end boundary
            if ($time_slot === '24:00') continue;
            
            echo '<div class="time-row">';
            echo '<div class="time-label">' . $time_slot . '</div>';
            
            for ($i = 0; $i < 7; $i++) {
                $current_date = date('Y-m-d', strtotime($start_date . " +{$i} days"));
                $is_today = $current_date == date('Y-m-d');
                $cell_class = $is_today ? 'day-cell today' : 'day-cell';
                
                echo "<div class='{$cell_class}' data-date='{$current_date}' data-time='{$time_slot}'>";
                
                // Display events that start within this time slot
                foreach ($grouped_items[$current_date] as $item) {
                    $item_time = date('H:i', strtotime($item['start_tijd']));
                    $next_time_slot = $index + 1 < count($time_slots) ? $time_slots[$index + 1] : '24:00';
                    
                    // Check if event starts in this time slot
                    if ($item_time >= $time_slot && $item_time < $next_time_slot) {
                        // Calculate event duration in minutes
                        $start_minutes = strtotime($item['start_tijd']) / 60;
                        $end_minutes = strtotime($item['eind_tijd']) / 60;
                        $duration_minutes = $end_minutes - $start_minutes;
                        
                        // Calculate exact position and height
                        $slot_start_minutes = strtotime($time_slot) / 60;
                        $slot_end_minutes = $slot_start_minutes + 120; // 2 hours = 120 minutes
                        
                        // Calculate offset from top of cell (in percentage)
                        $top_offset = (($start_minutes - $slot_start_minutes) / 120) * 100;
                        
                        // Calculate the height based on duration with better sizing proportions
                        $scaled_factor = 1.0; // Full height
                        $max_height = 90; // Increase maximum height
                        $height_percentage = min(($duration_minutes / 120) * 100 * $scaled_factor, $max_height);
                        
                        // Ensure minimum height for very short events
                        $height_percentage = max($height_percentage, 30); // Increase minimum height
                        
                        // Prepare full details for data attribute (JSON encoded)
                        $details = [
                            'id' => $item['id'],
                            'title' => $item['gebruiker_naam'],
                            'time' => date('H:i', strtotime($item['start_tijd'])) . ' - ' . date('H:i', strtotime($item['eind_tijd'])),
                            'location' => $item['locatie'],
                            'notes' => $item['opmerkingen'],
                            'date' => date('d-m-Y', strtotime($item['dag']))
                        ];
                        $details_json = htmlspecialchars(json_encode($details), ENT_QUOTES, 'UTF-8');
                        
                        // Use a div instead of a button for better clickability
                        $event_time = date('H:i', strtotime($item['start_tijd']));
                        $event_name = htmlspecialchars($item['gebruiker_naam']);
                        
                        echo "<div class='event-item' 
                            style='
                            position: absolute;
                            top: {$top_offset}%;
                            height: {$height_percentage}%;
                            left: 5px;
                            right: 5px;
                            background: linear-gradient(45deg, #a71680 0%, #ec6708 100%);
                            z-index: 10;
                            ' 
                            data-details='{$details_json}' 
                            onclick='showEventDetailsModal(this); event.stopPropagation();'
                        >
                            <div class='event-time'>{$event_time}</div>
                            <div class='event-title'>{$event_name}</div>
                        </div>";
                    }
                }
                
                echo '</div>';
            }
            
            echo '</div>';
        }
        
        if (count($roster_items) === 0) {
            echo '<div class="info-message">Geen roostermomenten gevonden voor deze week.</div>';
        }
        
        echo '</div>'; // End calendar-body
        echo '</div>'; // End calendar-grid
        
        // Replace the event details JavaScript with a better implementation
        echo '<script>
        // Function to show event details in modal
        function showEventDetailsModal(button) {
            // Get event details from data attribute
            const details = JSON.parse(button.getAttribute("data-details"));
            
            // Create a modal for the details
            const modal = document.createElement("div");
            modal.style.position = "fixed";
            modal.style.top = "0";
            modal.style.left = "0";
            modal.style.width = "100%";
            modal.style.height = "100%";
            modal.style.backgroundColor = "rgba(0,0,0,0.5)";
            modal.style.zIndex = "1000";
            modal.style.display = "flex";
            modal.style.justifyContent = "center";
            modal.style.alignItems = "center";
            
            // Create modal content
            const content = document.createElement("div");
            content.style.backgroundColor = "white";
            content.style.padding = "20px";
            content.style.borderRadius = "8px";
            content.style.width = "90%";
            content.style.maxWidth = "400px";
            content.style.boxShadow = "0 4px 20px rgba(0,0,0,0.2)";
            content.style.position = "relative";
            
            // Create close button
            const closeBtn = document.createElement("button");
            closeBtn.innerHTML = "×";
            closeBtn.style.position = "absolute";
            closeBtn.style.top = "10px";
            closeBtn.style.right = "15px";
            closeBtn.style.border = "none";
            closeBtn.style.background = "none";
            closeBtn.style.fontSize = "22px";
            closeBtn.style.cursor = "pointer";
            closeBtn.style.color = "#666";
            closeBtn.onclick = function() {
                document.body.removeChild(modal);
            };
            
            // Create header
            const header = document.createElement("div");
            header.style.marginBottom = "15px";
            header.style.paddingBottom = "10px";
            header.style.borderBottom = "1px solid #eee";
            
            const title = document.createElement("h3");
            title.textContent = details.title;
            title.style.color = "#a71680";
            title.style.margin = "0 0 5px 0";
            
            const date = document.createElement("p");
            date.textContent = details.date;
            date.style.margin = "0";
            date.style.color = "#666";
            
            header.appendChild(title);
            header.appendChild(date);
            
            // Create body
            const body = document.createElement("div");
            
            // Time
            const timeRow = document.createElement("div");
            timeRow.style.margin = "10px 0";
            timeRow.style.display = "flex";
            
            const timeLabel = document.createElement("span");
            timeLabel.textContent = "Tijd:";
            timeLabel.style.fontWeight = "500";
            timeLabel.style.minWidth = "80px";
            
            const timeValue = document.createElement("span");
            timeValue.textContent = details.time;
            
            timeRow.appendChild(timeLabel);
            timeRow.appendChild(timeValue);
            
            // Location
            const locationRow = document.createElement("div");
            locationRow.style.margin = "10px 0";
            locationRow.style.display = "flex";
            
            const locationLabel = document.createElement("span");
            locationLabel.textContent = "Locatie:";
            locationLabel.style.fontWeight = "500";
            locationLabel.style.minWidth = "80px";
            
            const locationValue = document.createElement("span");
            locationValue.textContent = details.location;
            
            locationRow.appendChild(locationLabel);
            locationRow.appendChild(locationValue);
            
            // Notes (if any)
            if (details.notes && details.notes.trim() !== "") {
                const notesRow = document.createElement("div");
                notesRow.style.margin = "10px 0";
                notesRow.style.display = "flex";
                
                const notesLabel = document.createElement("span");
                notesLabel.textContent = "Opmerkingen:";
                notesLabel.style.fontWeight = "500";
                notesLabel.style.minWidth = "80px";
                
                const notesValue = document.createElement("span");
                notesValue.textContent = details.notes;
                
                notesRow.appendChild(notesLabel);
                notesRow.appendChild(notesValue);
                
                body.appendChild(notesRow);
            }
            
            body.appendChild(timeRow);
            body.appendChild(locationRow);
            
            // Add action buttons (edit and delete)
            const actionRow = document.createElement("div");
            actionRow.style.marginTop = "20px";
            actionRow.style.paddingTop = "15px";
            actionRow.style.borderTop = "1px solid #eee";
            actionRow.style.display = "flex";
            actionRow.style.justifyContent = "flex-end";
            actionRow.style.gap = "10px";
            
            // Delete button
            const deleteBtn = document.createElement("button");
            deleteBtn.textContent = "Verwijderen";
            deleteBtn.style.padding = "8px 12px";
            deleteBtn.style.background = "#f44336";
            deleteBtn.style.color = "white";
            deleteBtn.style.border = "none";
            deleteBtn.style.borderRadius = "4px";
            deleteBtn.style.cursor = "pointer";
            deleteBtn.onclick = function() {
                if (confirm("Weet je zeker dat je deze afspraak wilt verwijderen?")) {
                    deleteMeeting(details.id);
                }
            };
            
            // Edit button
            const editBtn = document.createElement("button");
            editBtn.textContent = "Bewerken";
            editBtn.style.padding = "8px 12px";
            editBtn.style.background = "#4CAF50";
            editBtn.style.color = "white";
            editBtn.style.border = "none";
            editBtn.style.borderRadius = "4px";
            editBtn.style.cursor = "pointer";
            editBtn.onclick = function() {
                document.body.removeChild(modal);
                editMeeting(details);
            };
            
            actionRow.appendChild(deleteBtn);
            actionRow.appendChild(editBtn);
            
            // Assemble modal
            content.appendChild(closeBtn);
            content.appendChild(header);
            content.appendChild(body);
            content.appendChild(actionRow);
            modal.appendChild(content);
            
            // Add to document
            document.body.appendChild(modal);
            
            // Close when clicking outside
            modal.onclick = function(event) {
                if (event.target === modal) {
                    document.body.removeChild(modal);
                }
            };
        }
        
        // Function to delete a meeting
        function deleteMeeting(id) {
            fetch("api/rooster_api.php?action=delete_meeting", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: "id=" + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Afspraak succesvol verwijderd!");
                    // Refresh the page to show the updated calendar
                    window.location.reload();
                } else {
                    alert("Fout bij verwijderen: " + data.message);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("Er is een fout opgetreden bij het verwijderen van de afspraak.");
            });
        }
        
        // Function to edit a meeting
        function editMeeting(details) {
            // Redirect to a form page with the meeting details
            window.location.href = "rooster.php?action=edit&id=" + details.id;
        }
        </script>';
    } catch (Exception $e) {
        echo '<div class="error-message">Fout bij ophalen rooster: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
// HANDLE MEETING CREATION
else if ($action === 'create_meeting') {
    header('Content-Type: application/json');
    // Validate required fields
    $required_fields = ['dag', 'start_tijd', 'eind_tijd', 'locatie', 'gebruiker_id'];
    $missing_fields = [];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || (is_array($_POST[$field]) && empty($_POST[$field])) || (!is_array($_POST[$field]) && trim($_POST[$field]) === '')) {
            $missing_fields[] = $field;
        }
    }
    if (!empty($missing_fields)) {
        echo json_encode(['success' => false, 'message' => 'Vul alle verplichte velden in: ' . implode(', ', $missing_fields)]);
        exit;
    }
    // Validate date and times
    $dag = $_POST['dag'];
    $start_tijd = $_POST['start_tijd'];
    $eind_tijd = $_POST['eind_tijd'];
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dag)) {
        echo json_encode(['success' => false, 'message' => 'Ongeldige datumnotatie. Gebruik YYYY-MM-DD.']);
        exit;
    }
    if (!preg_match('/^\d{2}:\d{2}$/', $start_tijd) || !preg_match('/^\d{2}:\d{2}$/', $eind_tijd)) {
        echo json_encode(['success' => false, 'message' => 'Ongeldige tijdnotatie. Gebruik HH:MM.']);
        exit;
    }
    if (strtotime($eind_tijd) <= strtotime($start_tijd)) {
        echo json_encode(['success' => false, 'message' => 'Eindtijd moet na starttijd liggen.']);
        exit;
    }
    // Process the meeting data
    $locatie = trim($_POST['locatie']);
    $opmerkingen = isset($_POST['opmerkingen']) ? trim($_POST['opmerkingen']) : '';
    $gebruiker_ids = $_POST['gebruiker_id'];
    // If gebruiker_ids is not an array, convert it to an array
    if (!is_array($gebruiker_ids)) {
        $gebruiker_ids = [$gebruiker_ids];
    }
    try {
        // Ensure the table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'rooster'");
        if ($stmt->rowCount() === 0) {
            createRoosterTable($pdo);
        }
        // Check for existing meetings that would overlap with this one
        $overlap_check_sql = "SELECT r.*, g.naam as gebruiker_naam 
                             FROM rooster r
                             JOIN gebruikers g ON r.gebruiker_id = g.id
                             WHERE r.gebruiker_id = :gebruiker_id
                             AND r.dag = :dag
                             AND (
                                 (r.start_tijd <= :start_tijd AND r.eind_tijd > :start_tijd) OR
                                 (r.start_tijd < :eind_tijd AND r.eind_tijd >= :eind_tijd) OR
                                 (r.start_tijd >= :start_tijd AND r.eind_tijd <= :eind_tijd)
                             )";
        $overlap_check_stmt = $pdo->prepare($overlap_check_sql);
        // Check each participant for overlaps
        $conflicts = [];
        foreach ($gebruiker_ids as $gebruiker_id) {
            $overlap_check_stmt->execute([
                'gebruiker_id' => $gebruiker_id,
                'dag' => $dag,
                'start_tijd' => $start_tijd,
                'eind_tijd' => $eind_tijd
            ]);
            $overlapping_meetings = $overlap_check_stmt->fetchAll();
            if (!empty($overlapping_meetings)) {
                // Get user name for the conflict message
                $user_sql = "SELECT naam FROM gebruikers WHERE id = :id";
                $user_stmt = $pdo->prepare($user_sql);
                $user_stmt->execute(['id' => $gebruiker_id]);
                $user = $user_stmt->fetch();
                
                foreach ($overlapping_meetings as $meeting) {
                    $conflicts[] = "• " . $user['naam'] . " heeft al een afspraak op " . 
                                   date('d-m-Y', strtotime($meeting['dag'])) . " van " .
                                   date('H:i', strtotime($meeting['start_tijd'])) . " tot " .
                                   date('H:i', strtotime($meeting['eind_tijd'])) . " (" . $meeting['locatie'] . ")";
                }
            }
        }
        // If conflicts were found, return error with details
        if (!empty($conflicts)) {
            echo json_encode([
                'success' => false, 
                'message' => 'Deze afspraak overlapt met bestaande afspraken:',
                'conflicts' => $conflicts
            ]);
            exit;
        }
        // Begin transaction
        $pdo->beginTransaction();
        // Insert meetings for each participant
        foreach ($gebruiker_ids as $gebruiker_id) {
            $sql = "INSERT INTO rooster (gebruiker_id, dag, start_tijd, eind_tijd, locatie, opmerkingen) 
                    VALUES (:gebruiker_id, :dag, :start_tijd, :eind_tijd, :locatie, :opmerkingen)";
            $stmt = $pdo->prepare($sql);
            
            $result = $stmt->execute([
                'gebruiker_id' => $gebruiker_id,
                'dag' => $dag,
                'start_tijd' => $start_tijd,
                'eind_tijd' => $eind_tijd,
                'locatie' => $locatie,
                'opmerkingen' => $opmerkingen
            ]);
            if (!$result) {
                throw new Exception("Kon afspraak niet toevoegen voor gebruiker " . $gebruiker_id);
            }
        }
        // Commit transaction
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Afspraak succesvol gepland.']);
    } catch (Exception $e) {
        // Rollback on error
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Fout bij aanmaken afspraak: ' . $e->getMessage()]);
    }
}
// HANDLE MEETING DELETION
else if ($action === 'delete_meeting') {
    header('Content-Type: application/json');
    
    // Check if ID was provided
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        echo json_encode(['success' => false, 'message' => 'Ongeldige ID opgegeven.']);
        exit;
    }
    $id = intval($_POST['id']);
    
    try {
        // Verify the meeting belongs to the current user
        $check_sql = "SELECT * FROM rooster WHERE id = :id AND gebruiker_id = :gebruiker_id";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([
            'id' => $id,
            'gebruiker_id' => $_SESSION['user_id']
        ]);
        $meeting = $check_stmt->fetch();
        if (!$meeting) {
            echo json_encode(['success' => false, 'message' => 'Je hebt geen toegang tot deze afspraak of de afspraak bestaat niet.']);
            exit;
        }
        // Delete the meeting
        $delete_sql = "DELETE FROM rooster WHERE id = :id";
        $delete_stmt = $pdo->prepare($delete_sql);
        $result = $delete_stmt->execute(['id' => $id]);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Afspraak succesvol verwijderd.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Kon de afspraak niet verwijderen.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Fout bij verwijderen afspraak: ' . $e->getMessage()]);
    }
}
// HANDLE DIAGNOSTICS
else if ($action === 'diagnostic' && isset($_SESSION['rol']) && strtolower($_SESSION['rol']) === 'admin') {
    // Only allow admin users
    include_once('../rooster/diagnostic.php');
}
// INVALID ACTION
else {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        echo '<div class="error-message">Ongeldige actie.</div>';
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Ongeldige actie']);
    }
}
?>