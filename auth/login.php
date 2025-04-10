<?php
session_start();
include('../includes/config.php'); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Debug log
    error_log("Login attempt for: " . $email);

    $sql = "SELECT id, naam, email, wachtwoord, rol FROM gebruikers WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    $user = $stmt->fetch();

    // Debug log
    if ($user) {
        error_log("User found with role: " . $user['rol']);
    } else {
        error_log("No user found with email: " . $email);
    }

    if ($user && password_verify($password, $user['wachtwoord'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['naam'] = $user['naam'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['rol'] = $user['rol']; 

        // Debug log
        error_log("Login successful. Session role set to: " . $_SESSION['rol']);

        if (strtolower($user['rol']) === 'admin') {
            header("Location: ../pages/admin_home.php");
        } else {
            header("Location: ../pages/dashboard.php");
        }
        exit();
    } else {
        header("Location: ../index.php?error=Ongeldige inloggegevens");
        exit();
    }
}
?>
