<?php
include __DIR__ . '/config.php';

try {
<<<<<<< Updated upstream
    $stmt = $pdo->query("SELECT * FROM rooster ORDER BY datum, tijdstip");

    echo "<h1>Rooster</h1>";
    echo "<p><a href='../rooster/create.php'>+ Nieuwe regel toevoegen</a></p>";
    echo "<table border='1'>";
    echo "<tr><th>Datum</th><th>Tijdstip</th><th>Activiteit</th><th>Medewerker</th><th>Bewerk</th><th>Verwijder</th></tr>";

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['datum']) . "</td>";
        echo "<td>" . htmlspecialchars($row['tijdstip']) . "</td>";
        echo "<td>" . htmlspecialchars($row['activiteit']) . "</td>";
        echo "<td>" . htmlspecialchars($row['medewerker']) . "</td>";
        echo "<td><a href='../rooster/edit.php?id=" . $row['id'] . "'>✏️</a></td>";
        echo "<td><a href='../rooster/delete.php?id=" . $row['id'] . "' onclick=\"return confirm('Weet je zeker dat je dit item wilt verwijderen?');\">🗑️</a></td>";
        echo "</tr>";
    }

    echo "</table>";
} catch (PDOException $e) {
    echo "<p>Fout bij ophalen rooster: " . htmlspecialchars($e->getMessage()) . "</p>";
}
=======
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
        echo '<thead><tr><th>Dag</th><th>Naam</th><th>Tijd</th><th>Locatie</th><th>Opmerkingen</th><th>Acties</th></tr></thead>';
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

            // Actieknoppen voor bewerken en verwijderen
            echo "<td>
                    <a href='../rooster/edit.php?id={$item['id']}' class='btn btn-edit'><i class='fas fa-edit'></i> Bewerk </a>
                    <a href='../rooster/delete.php?id={$item['id']}' class='btn btn-delete' onclick='return confirm(\"Weet je zeker dat je dit wilt verwijderen?\")'><i class='fas fa-trash-alt'></i> Verwijder </a>
                  </td>";
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

<!-- Voeg onderstaande CSS en Font Awesome toe in je HTML-head -->

<!-- Voeg dit toe in de head van je HTML -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
    /* Algemene opmaak voor de tabel */
    .rooster-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .rooster-table th,
    .rooster-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    .rooster-table th {
        background-color: #f4f4f4;
        font-weight: bold;
    }

    .rooster-table tr:hover {
        background-color: #f9f9f9;
    }

    .rooster-table .date-small {
        font-size: 0.85em;
        color: #666;
    }

    /* Styling voor de actieknoppen */
    .btn {
        padding: 6px 12px;
        border-radius: 4px;
        text-decoration: none;
        font-size: 0.9em;
        margin: 5px 0;
        display: inline-block;
    }

    .btn-edit {
        background-color: #4CAF50;
        color: white;
    }

    .btn-edit:hover {
        background-color: #45a049;
    }

    .btn-delete {
        background-color: #f44336;
        color: white;
    }

    .btn-delete:hover {
        background-color: #e53935;
    }

    /* Berichten */
    .error-message,
    .info-message {
        padding: 15px;
        background-color: #f2dede;
        border-left: 5px solid #d9534f;
        margin: 10px 0;
    }

    .info-message {
        background-color: #d9edf7;
        border-left: 5px solid #5bc0de;
    }
</style>
>>>>>>> Stashed changes
