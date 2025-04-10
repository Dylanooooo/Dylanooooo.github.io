<?php
session_start();
require_once('../includes/config.php');

// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/rooster.php");
    exit;
}

// Toevoegen van een nieuw roosteritem
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $dag = $_POST['dag'];
    $start_tijd = $_POST['start_tijd'];
    $eind_tijd = $_POST['eind_tijd'];
    $gebruiker_id = $_POST['gebruiker_id'];
    $locatie = $_POST['locatie'];
    $opmerkingen = $_POST['opmerkingen'];

    // Controleer of er al een roosteritem bestaat op de gekozen datum en tijd
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM rooster WHERE dag = ? AND 
                           ((start_tijd BETWEEN ? AND ?) OR (eind_tijd BETWEEN ? AND ?) OR
                           (? BETWEEN start_tijd AND eind_tijd) OR (? BETWEEN start_tijd AND eind_tijd))");
    $stmt->execute([$dag, $start_tijd, $eind_tijd, $start_tijd, $eind_tijd, $start_tijd, $eind_tijd]);
    $overlap = $stmt->fetchColumn();

    if ($overlap > 0) {
        // Als er een overlap is, toon een waarschuwing en stop de uitvoering
        $error_message = "Dit tijdsblok is al ingepland door [Naam]. Gelieve een ander tijdstip te selecteren.";
    } else {
        // Als er geen overlap is, voeg het roosteritem toe
        $stmt = $pdo->prepare("INSERT INTO rooster (dag, start_tijd, eind_tijd, gebruiker_id, locatie, opmerkingen) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$dag, $start_tijd, $eind_tijd, $gebruiker_id, $locatie, $opmerkingen]);

        header("Location: ../pages/rooster.php");  // Redirect naar het roosteroverzicht
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nieuw Roosteritem Toevoegen</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <?php include('../includes/navigation.php'); ?>

    <div class="intro-banner-wrapper">
        <div class="intro-banner">
            <img src="../assets/images/FlitzBanner.png" alt="Flitz Events Banner" class="banner-img">
            <div class="banner-text">
                <div class="banner-container">
                    <h3>Nieuw Roosteritem Toevoegen</h3>
                    <p>Vul hieronder de details in om een nieuw roosteritem toe te voegen.</p>
                </div>
            </div>
        </div>
    </div>

    <section id="rooster-page">
        <div class="container">
            <h2>Nieuw Roosteritem Toevoegen</h2>

            <!-- Formulier voor het toevoegen van een nieuw roosteritem -->
            <?php if (isset($error_message)): ?>
                <div class="error-message">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <table class="form-table">
                    <tr>
                        <td><label for="dag">Datum:</label></td>
                        <td><input type="date" name="dag" id="dag" required></td>
                    </tr>
                    <tr>
                        <td><label for="start_tijd">Starttijd:</label></td>
                        <td><input type="time" name="start_tijd" id="start_tijd" required></td>
                    </tr>
                    <tr>
                        <td><label for="eind_tijd">Eindtijd:</label></td>
                        <td><input type="time" name="eind_tijd" id="eind_tijd" required></td>
                    </tr>
                    <tr>
                        <td><label for="gebruiker_id">Medewerker:</label></td>
                        <td>
                            <select name="gebruiker_id" id="gebruiker_id" required>
                                <?php
                                $stmt = $pdo->query("SELECT id, naam FROM gebruikers");
                                while ($row = $stmt->fetch()) {
                                    echo "<option value=\"{$row['id']}\">{$row['naam']}</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="locatie">Locatie:</label></td>
                        <td><input type="text" name="locatie" id="locatie" required></td>
                    </tr>
                    <tr>
                        <td><label for="opmerkingen">Opmerkingen:</label></td>
                        <td><textarea name="opmerkingen" id="opmerkingen"></textarea></td>
                    </tr>
                </table>

                <div class="form-actions">
                    <input type="submit" value="Opslaan" class="btn btn-primary">
                    <a href="../pages/rooster.php" class="btn btn-secondary">← Terug naar Rooster</a>
                </div>
            </form>
        </div>
    </section>

    <footer>
        <div class="footer-container">
            <p>&copy; <?php echo date('Y'); ?> Flitz-Events Stageportaal | Alle rechten voorbehouden</p>
        </div>
    </footer>

    <script src="../assets/js/scripts.js"></script>
</body>

</html>