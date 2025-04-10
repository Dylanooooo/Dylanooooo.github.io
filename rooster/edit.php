<?php
session_start();
include('../includes/config.php');

// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

// Haal het evenement op als 'id' is gegeven
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM rooster WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $event = $stmt->fetch();
}

// Verwerk formulier bij POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Controleer of het roosteritem overlapt
    $stmt = $pdo->prepare("SELECT gebruikers.naam FROM rooster 
                           JOIN gebruikers ON rooster.gebruiker_id = gebruikers.id 
                           WHERE dag = ? AND (start_tijd < ? AND eind_tijd > ?)");
    $stmt->execute([$_POST['dag'], $_POST['eind_tijd'], $_POST['start_tijd']]);
    $overlap = $stmt->fetch();

    if ($overlap) {
        // Als er een overlap is, toon de foutmelding
        $errorMessage = "Dit tijdstip is al bezet door " . $overlap['naam'] . ". Kies een ander tijdslot.";
    } else {
        // Als er geen overlap is, werk het evenement bij
        $stmt = $pdo->prepare("UPDATE rooster SET dag = ?, start_tijd = ?, eind_tijd = ?, activiteit = ?, gebruiker_id = ? WHERE id = ?");
        $stmt->execute([$_POST['dag'], $_POST['start_tijd'], $_POST['eind_tijd'], $_POST['activiteit'], $_POST['gebruiker_id'], $_GET['id']]);
        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evenement Bewerken</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <?php include('../includes/navigation.php'); ?>

    <div class="intro-banner-wrapper">
        <div class="intro-banner">
            <img src="../assets/images/FlitzBanner.png" alt="Flitz Events Banner" class="banner-img">
            <div class="banner-text">
                <div class="banner-container">
                    <h3>Evenement Bewerken</h3>
                    <p>Werk de details van het evenement bij.</p>
                </div>
            </div>
        </div>
    </div>

    <section id="rooster-page">
        <div class="container">
            <h2>Evenement Bewerken</h2>

            <!-- Als er een foutmelding is, toon die hier -->
            <?php if (isset($errorMessage)): ?>
            <div class="error-message"><?php echo $errorMessage; ?></div>
            <?php endif; ?>

            <!-- Begin Formulier in Tabelstructuur -->
            <form method="POST">
                <table class="form-table">
                    <tr>
                        <td><label for="dag">Datum:</label></td>
                        <td><input type="date" name="dag"
                                value="<?php echo isset($event['dag']) ? $event['dag'] : ''; ?>" required></td>
                    </tr>
                    <tr>
                        <td><label for="start_tijd">Starttijd:</label></td>
                        <td><input type="time" name="start_tijd"
                                value="<?php echo isset($event['start_tijd']) ? $event['start_tijd'] : ''; ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="eind_tijd">Eindtijd:</label></td>
                        <td><input type="time" name="eind_tijd"
                                value="<?php echo isset($event['eind_tijd']) ? $event['eind_tijd'] : ''; ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="activiteit">Activiteit:</label></td>
                        <td><input type="text" name="activiteit"
                                value="<?php echo isset($event['activiteit']) ? $event['activiteit'] : ''; ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="gebruiker_id">Medewerker:</label></td>
                        <td>
                            <select name="gebruiker_id" required>
                                <?php
                                $stmt = $pdo->query("SELECT id, naam FROM gebruikers");
                                while ($row = $stmt->fetch()) {
                                    $selected = ($row['id'] == $event['gebruiker_id']) ? 'selected' : '';
                                    echo "<option value=\"{$row['id']}\" $selected>{$row['naam']}</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                </table>

                <div class="form-actions">
                    <input type="submit" value="Opslaan" class="btn btn-primary">
                    <a href="../pages/rooster.php" class="btn btn-secondary">← Terug naar Rooster</a>
                </div>
            </form>
            <!-- Einde Formulier in Tabelstructuur -->
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