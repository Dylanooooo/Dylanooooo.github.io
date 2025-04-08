<?php
session_start(); 

include('../includes/config.php'); 


$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $naam = trim($_POST['naam']);
    $email = trim($_POST['email']);
    $wachtwoord = trim($_POST['password']);
    $school = trim($_POST['school']);
    $opleiding = trim($_POST['opleiding']);
    $uren = isset($_POST['uren']) ? (int)$_POST['uren'] : 0;

    if (empty($naam) || empty($email) || empty($wachtwoord) || empty($school) || empty($opleiding) || $uren <= 0) {
        $message = 'Vul alle velden correct in!';
    } else {
        $sqlCheck = "SELECT id FROM gebruikers WHERE email = :email";
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->bindParam(':email', $email);
        $stmtCheck->execute();

        if ($stmtCheck->rowCount() > 0) {
            $message = 'E-mailadres is al in gebruik! Probeer een ander e-mailadres.';
        } else {
            $hashedPassword = password_hash($wachtwoord, PASSWORD_BCRYPT);
            $sql = "INSERT INTO gebruikers (naam, email, wachtwoord, rol, school, opleiding, uren_per_week) 
                    VALUES (:naam, :email, :wachtwoord, 'stagiair', :school, :opleiding, :uren)";
            $stmt = $pdo->prepare($sql);

            if ($stmt->execute([ 
                'naam' => $naam,
                'email' => $email,
                'wachtwoord' => $hashedPassword,
                'school' => $school,
                'opleiding' => $opleiding,
                'uren' => $uren
            ])) {
                $success = true;
                $message = 'Account succesvol aangemaakt!';
                $_SESSION['register_message'] = $message;
                $_SESSION['register_success'] = true;
                header("Location: ../index.php");
                exit(); 
            } else {
                $message = 'Er is een fout opgetreden! Probeer het later opnieuw.';
            }
        }
    }

    $_SESSION['register_message'] = $message;
    $_SESSION['register_success'] = false;
    header("Location: ../pages/register.php");
    exit();
}
