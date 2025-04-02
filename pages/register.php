<?php
session_start(); 
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registreren - Flitz-Events Portal</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body class="login-page">
    <header>
        <div class="header-container">
            <h1>Flitz-Events Portal</h1>
        </div>
    </header>

    <section id="register">
        <div class="container">
            <div class="register-container">
                <div class="login-logo">
                    <img src="../assets/images/FlitzLogo.png" alt="Flitz-Events Logo">
                </div>
                <h2>Registreren</h2>


                <form action="../auth/registerb.php" method="POST">
                    <input type="text" name="naam" placeholder="Naam" required>
                    <input type="email" name="email" placeholder="E-mail" required>
                    <input type="password" name="password" placeholder="Wachtwoord" required>
                    <input type="text" name="school" placeholder="School" required>
                    <input type="text" name="opleiding" placeholder="Opleiding" required>
                    <input type="number" name="uren" placeholder="Uren per week" required>
                    <button type="submit">Registreren</button>
                </form>

                <?php if (isset($_SESSION['register_message'])) : ?>
                    <div id="register-message" style="color: <?= $_SESSION['register_success'] ? 'green' : 'red' ?>; font-weight: bold; text-align: center; margin-top: 10px;">
                        <?= $_SESSION['register_message']; ?>
                    </div>
                    <?php 
                    unset($_SESSION['register_message']);
                    unset($_SESSION['register_success']);
                    ?>
                <?php endif; ?>

                <p class="register-help">Al een account? <a href="../index.php">Inloggen</a></p>
            </div>
        </div>
    </section>

    <footer>
        <div class="footer-container">
            <p>&copy; 2025 Flitz-Events. Alle rechten voorbehouden.</p>
        </div>
    </footer>
</body>
</html>
