<?php
include __DIR__ . '/config.php';

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM rooster WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    die("Item niet gevonden");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $stmt = $pdo->prepare("UPDATE rooster SET datum=?, tijdstip=?, activiteit=?, medewerker=? WHERE id=?");
    $stmt->execute([$_POST['datum'], $_POST['tijdstip'], $_POST['activiteit'], $_POST['medewerker'], $id]);
    header("Location: ../pages/rooster.html");
    exit;
}
?>

<h1>Roosterregel bewerken</h1>
<form method="post">
    Datum: <input type="date" name="datum" value="<?= htmlspecialchars($item['datum']) ?>" required><br>
    Tijdstip: <input type="time" name="tijdstip" value="<?= htmlspecialchars($item['tijdstip']) ?>" required><br>
    Activiteit: <input type="text" name="activiteit" value="<?= htmlspecialchars($item['activiteit']) ?>" required><br>
    Medewerker: <input type="text" name="medewerker" value="<?= htmlspecialchars($item['medewerker']) ?>" required><br>
    <input type="submit" value="Bijwerken">
</form>
<a href="../pages/rooster.html">← Terug</a>