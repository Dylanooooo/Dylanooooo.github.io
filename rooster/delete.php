<?php
session_start();
require_once('../includes/config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo '<div class="error-message">Niet geautoriseerd. Log in om deze actie uit te voeren.</div>';
    exit;
}

// Haal het ID van het roosteritem op
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<div class="error-message">Ongeldig rooster-ID opgegeven.</div>';
    exit;
}

$rooster_id = intval($_GET['id']);

try {
    // Verwijder het roosteritem uit de database
    $stmt = $pdo->prepare("DELETE FROM rooster WHERE id = :id");
    $stmt->bindParam(':id', $rooster_id, PDO::PARAM_INT);
    $stmt->execute();

    // Controleer of de verwijdering succesvol was
    if ($stmt->rowCount() > 0) {
        echo '<div class="info-message">Roostermoment succesvol verwijderd.</div>';
    } else {
        echo '<div class="error-message">Fout bij het verwijderen van het roostermoment. Probeer het opnieuw.</div>';
    }
} catch (Exception $e) {
    echo '<div class="error-message">Er is een fout opgetreden: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

// Redirect naar de roosterpagina (optioneel)
header("Location: ../pages/rooster.php");
exit;
?>