<?php
session_start();
include('../includes/config.php');

// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

// Relatief pad voor navigatie
$root_path = "../";
$pageTitle = "Rooster Toevoegen - Flitz Events";
$useIcons = true;
?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <!-- Inclusie van de consistente navigatie component -->
    <?php include('../includes/navigation.php'); ?>

    <div class="intro-banner-wrapper">
        <div class="intro-banner">
            <img src="../assets/images/FlitzBanner.png" alt="Flitz Events Banner" class="banner-img">
            <div class="banner-text">
                <div class="banner-container">
                    <h3>Welkom bij je stage!</h3>
                    <p>Belangrijke informatie: Stagebegeleider: Milan Laroes (te bereiken via chat) | Aanwezigheid:
                        Ma-Do 9:00-17:00</p>
                </div>
            </div>
        </div>
    </div>

    <section id="create-rooster-page">
        <div class="container">
            <h2>Nieuwe Roosterregel Toevoegen</h2>

            <!-- Formulier voor rooster toevoeging -->
            <form method="post" class="form-container">
                <div class="form-group">
                    <label for="gebruiker_id">Gebruiker ID</label>
                    <input type="text" name="gebruiker_id" id="gebruiker_id" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="dag">Datum</label>
                    <input type="date" name="dag" id="dag" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="start_tijd">Start Tijd</label>
                    <input type="time" name="start_tijd" id="start_tijd" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="eind_tijd">Eind Tijd</label>
                    <input type="time" name="eind_tijd" id="eind_tijd" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="locatie">Locatie</label>
                    <input type="text" name="locatie" id="locatie" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="opmerkingen">Opmerkingen</label>
                    <textarea name="opmerkingen" id="opmerkingen" class="form-control" required></textarea>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Opslaan</button>
                </div>
            </form>

            <!-- Terug link -->
            <div class="form-back-link">
                <a href="../pages/rooster.php" class="btn btn-secondary">← Terug</a>
            </div>
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