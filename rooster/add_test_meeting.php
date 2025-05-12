<?php
session_start();
require_once('../includes/config.php');

// Only allow admin users to access this page
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['rol']) !== 'admin') {
    echo "<p>Je hebt geen toegang tot deze pagina.</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("INSERT INTO rooster (gebruiker_id, dag, start_tijd, eind_tijd, locatie, opmerkingen) 
                               VALUES (:gebruiker_id, :dag, :start_tijd, :eind_tijd, :locatie, :opmerkingen)");
        
        $result = $stmt->execute([
            'gebruiker_id' => $_POST['gebruiker_id'],
            'dag' => $_POST['dag'],
            'start_tijd' => $_POST['start_tijd'],
            'eind_tijd' => $_POST['eind_tijd'],
            'locatie' => $_POST['locatie'],
            'opmerkingen' => $_POST['opmerkingen']
        ]);
        
        if ($result) {
            echo "<p>Test afspraak succesvol toegevoegd! <a href='diagnostic.php'>Terug naar diagnostiek</a></p>";
        } else {
            echo "<p>Fout bij toevoegen van test afspraak.</p>";
        }
    } catch (PDOException $e) {
        echo "<p>Database error: " . $e->getMessage() . "</p>";
    }
}
?>
