
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Flitz-Events Portal</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body class="login-page">
    <header>
        <div class="header-container">
            <h1>Flitz-Events Portal</h1>
        </div>
    </header>

    <section id="login">
        <div class="container">
            <div class="login-container">
                <div class="login-logo">
                    <img src="./assets/images/FlitzLogo.png" alt="Flitz-Events Logo">
                </div>
                <h2>Inloggen</h2>

                <?php if (isset($_GET['error'])): ?>
                    <p style="color: red; text-align: center;">
                        <?php echo htmlspecialchars($_GET['error']); ?>
                    </p>
                <?php endif; ?>

                <form action="auth/login.php" method="POST">
                    <input type="email" name="email" placeholder="E-mailadres" required>
                    <input type="password" name="password" placeholder="Wachtwoord" required>
                    <button type="submit">Inloggen</button>
                </form>

                <p class="register-link">
                    Nog geen account? <a href="./pages/register.php">Registreer hier</a>
                </p>

                <p class="login-help">Problemen met inloggen? Neem contact op met je begeleider.</p>
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
