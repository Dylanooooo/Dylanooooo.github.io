<?php
include __DIR__ . '/config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $stmt = $pdo->prepare("INSERT INTO rooster (datum, tijdstip, activiteit, medewerker) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_POST['datum'], $_POST['tijdstip'], $_POST['activiteit'], $_POST['medewerker']]);
    header("Location: index.php");
    exit;
}
?>

<h1>Nieuwe roosterregel toevoegen</h1>
<form method="post">
    Datum: <input type="date" name="datum" required><br>
    Tijdstip: <input type="time" name="tijdstip" required><br>
    Activiteit: <input type="text" name="activiteit" required><br>
    Medewerker: <input type="text" name="medewerker" required><br>
    <input type="submit" value="Opslaan">
</form>
<a href="../pages/rooster.html">← Terug</a>