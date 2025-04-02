<?php
session_start();
include('../includes/config.php'); // Zorg ervoor dat je databaseconfiguratie is geladen

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Checken of email en wachtwoord overeenkomen
    $sql = "SELECT id, naam, email, wachtwoord, rol FROM gebruikers WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['wachtwoord'])) {
        // Als de gebruiker bestaat en het wachtwoord klopt, start een sessie
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['naam'] = $user['naam'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['rol'] = $user['rol']; // De rol van de gebruiker wordt opgeslagen in de sessie

        // Doorsturen naar de juiste pagina op basis van de rol
        if ($user['rol'] === 'admin') {
            header("Location: ../pages/admin.php");
        } else {
            header("Location: ../pages/dashboard.php");
        }
        exit();
    } else {
        // Foutmelding bij onjuiste login
        header("Location: ../index.php?error=Ongeldige inloggegevens");
        exit();
    }
}
?>
