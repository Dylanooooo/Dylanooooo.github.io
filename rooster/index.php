<?php
session_start();
require_once('../includes/config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo '<div class="error-message">Niet geautoriseerd. Log in om het rooster te bekijken.</div>';
    exit;
}

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
        echo '<div class="error-message">
            De rooster tabel bestaat nog niet. Importeer het bestand flitz_events.sql in de database.
            <a href="../docs/setup.md" target="_blank">Zie setup instructies</a> voor meer informatie.
        </div>';
        exit;
    }
    
    // Build query - join with gebruikers to get names
    $query = "SELECT r.*, g.naam as gebruiker_naam 
              FROM rooster r
              JOIN gebruikers g ON r.gebruiker_id = g.id
              WHERE r.dag BETWEEN :start_date AND :end_date";
    
    // Add search filter if provided
    if (!empty($search)) {
        $query .= " AND g.naam LIKE :search";
    }
    
    $query .= " ORDER BY r.dag ASC, r.start_tijd ASC";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    
    if (!empty($search)) {
        $search_param = "%{$search}%";
        $stmt->bindParam(':search', $search_param);
    }
    
    $stmt->execute();
    $roster_items = $stmt->fetchAll();
    
    if (count($roster_items) > 0) {
        // Generate weekly calendar view
        $days = ['Maandag', 'Dinsdag', 'Woensdag', 'Donderdag', 'Vrijdag', 'Zaterdag', 'Zondag'];
        
        // Week info
        $week_number = date('W', strtotime($start_date));
        $week_start = date('d M', strtotime($start_date));
        $week_end = date('d M Y', strtotime($end_date));
        
        echo "<h3>Week {$week_number} ({$week_start} - {$week_end})</h3>";
        
        echo '<table class="rooster-table">';
        echo '<thead><tr><th>Dag</th><th>Naam</th><th>Tijd</th><th>Locatie</th><th>Opmerkingen</th></tr></thead>';
        echo '<tbody>';
        
        foreach ($roster_items as $item) {
            $day_name = $days[date('N', strtotime($item['dag'])) - 1];
            $day_date = date('d M', strtotime($item['dag']));
            
            echo '<tr>';
            echo "<td>{$day_name}<br><span class='date-small'>{$day_date}</span></td>";
            echo "<td>{$item['gebruiker_naam']}</td>";
            echo "<td>" . date('H:i', strtotime($item['start_tijd'])) . " - " . 
                 date('H:i', strtotime($item['eind_tijd'])) . "</td>";
            echo "<td>{$item['locatie']}</td>";
            echo "<td>" . (empty($item['opmerkingen']) ? '-' : htmlspecialchars($item['opmerkingen'])) . "</td>";
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    } else {
        echo '<div class="info-message">Geen roostermomenten gevonden voor deze week.</div>';
    }
} catch (Exception $e) {
    echo '<div class="error-message">Fout bij ophalen rooster: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>