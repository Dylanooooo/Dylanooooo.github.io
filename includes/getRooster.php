<?php
include __DIR__ . '/config.php'; // Zorg ervoor dat het pad klopt met je mapstructuur

header('Content-Type: application/json'); // Stel de juiste header in voor JSON-gegevens

try {
    $stmt = $pdo->query("SELECT * FROM rooster ORDER BY datum, tijdstip");

    $rooster = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $rooster[] = $row;
    }

    echo json_encode($rooster); // Stuur het rooster als JSON terug naar de client
} catch (PDOException $e) {
    echo json_encode(["error" => "Fout bij ophalen rooster: " . $e->getMessage()]);
}
