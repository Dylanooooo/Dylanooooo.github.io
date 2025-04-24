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
$pageTitle = "Rooster - Flitz Events";
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

    <section id="rooster-page">
        <div class="container">
            <h2>Rooster</h2>

            <!-- Toevoeg Knop -->
            <div class="add-rooster-btn">
                <a href="../rooster/create.php" class="btn btn-primary">Voeg Rooster Toe</a>
            </div>

            <div class="rooster-controls">
                <input type="text" id="search" placeholder="Zoek op medewerker" class="search-input">
                <div class="date-selector">
                    <button id="prev-week" class="date-nav-btn"><i class="fas fa-chevron-left"></i></button>
                    <span id="current-week">Week 15 (10 apr - 16 apr)</span>
                    <button id="next-week" class="date-nav-btn"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>

            <div id="rooster" class="rooster-container">
                <div class="loading">Rooster wordt geladen...</div>
            </div>
        </div>
    </section>

    <footer>
        <div class="footer-container">
            <p>&copy; <?php echo date('Y'); ?> Flitz-Events Stageportaal | Alle rechten voorbehouden</p>
        </div>
    </footer>

    <script>
    let currentWeekOffset = 0;

    function updateWeekDisplay() {
        // Calculate dates for current offset
        const today = new Date();
        const startOfWeek = new Date(today);
        startOfWeek.setDate(today.getDate() - today.getDay() + 1 + (currentWeekOffset * 7));

        const endOfWeek = new Date(startOfWeek);
        endOfWeek.setDate(startOfWeek.getDate() + 6);

        // Format dates
        const startStr = startOfWeek.getDate() + ' ' +
            startOfWeek.toLocaleString('nl-NL', {
                month: 'short'
            });

        const endStr = endOfWeek.getDate() + ' ' +
            endOfWeek.toLocaleString('nl-NL', {
                month: 'short'
            });

        const weekNum = getWeekNumber(startOfWeek);

        // Update display
        document.getElementById('current-week').textContent =
            `Week ${weekNum} (${startStr} - ${endStr})`;
    }

    function getWeekNumber(d) {
        // Copy date so don't modify original
        d = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
        // Set to nearest Thursday: current date + 4 - current day number
        d.setUTCDate(d.getUTCDate() + 4 - (d.getUTCDay() || 7));
        // Get first day of year
        const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
        // Calculate full weeks to nearest Thursday
        const weekNo = Math.ceil((((d - yearStart) / 86400000) + 1) / 7);
        return weekNo;
    }

    function laadRooster() {
        const searchTerm = document.getElementById('search').value;
        const url = `../rooster/index.php?week_offset=${currentWeekOffset}&search=${encodeURIComponent(searchTerm)}`;

        document.getElementById('rooster').innerHTML = '<div class="loading">Rooster wordt geladen...</div>';

        fetch(url)
            .then(response => response.text())
            .then(html => {
                document.getElementById('rooster').innerHTML = html;
            })
            .catch(error => {
                console.error('Fout bij laden rooster:', error);
                document.getElementById('rooster').innerHTML =
                    '<div class="error-message">Er is een fout opgetreden bij het laden van het rooster.</div>';
            });
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateWeekDisplay();
        laadRooster();

        // Navigatie door weken
        document.getElementById('prev-week').addEventListener('click', function() {
            currentWeekOffset--;
            updateWeekDisplay();
            laadRooster();
        });

        document.getElementById('next-week').addEventListener('click', function() {
            currentWeekOffset++;
            updateWeekDisplay();
            laadRooster();
        });

        // Zoekfunctionaliteit - debounced search
        let searchTimeout;
        document.getElementById('search').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(laadRooster, 500);
        });
    });

    // Auto-refresh elke 5 minuten
    setInterval(laadRooster, 300000);
    </script>

    <script src="../assets/js/scripts.js"></script>
</body>

</html>