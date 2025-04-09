<?php
include __DIR__ . '/config.php';

try {
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