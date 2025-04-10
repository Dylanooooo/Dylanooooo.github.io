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
            try {
                // Check if the uren_per_week column exists
                $columnCheck = $pdo->query("SHOW COLUMNS FROM gebruikers LIKE 'uren_per_week'");
                $hasUrenColumn = $columnCheck->rowCount() > 0;
                
                // Hash the password
                $hashedPassword = password_hash($wachtwoord, PASSWORD_BCRYPT);
                
                // Prepare the SQL statement based on whether the column exists
                if ($hasUrenColumn) {
                    $sql = "INSERT INTO gebruikers (naam, email, wachtwoord, rol, school, opleiding, uren_per_week) 
                            VALUES (:naam, :email, :wachtwoord, 'stagiair', :school, :opleiding, :uren)";
                    $params = [
                        'naam' => $naam,
                        'email' => $email,
                        'wachtwoord' => $hashedPassword,
                        'school' => $school,
                        'opleiding' => $opleiding,
                        'uren' => $uren
                    ];
                } else {
                    // Fallback if the column doesn't exist
                    $sql = "INSERT INTO gebruikers (naam, email, wachtwoord, rol, school, opleiding) 
                            VALUES (:naam, :email, :wachtwoord, 'stagiair', :school, :opleiding)";
                    $params = [
                        'naam' => $naam,
                        'email' => $email,
                        'wachtwoord' => $hashedPassword,
                        'school' => $school,
                        'opleiding' => $opleiding
                    ];
                    
                    // Log message to alert admin that column needs to be added
                    error_log("Warning: uren_per_week column missing in gebruikers table");
                }
                
                // Prepare and execute the statement
                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute($params)) {
                    $success = true;
                    $message = 'Account succesvol aangemaakt!';
                    $_SESSION['register_message'] = $message;
                    $_SESSION['register_success'] = true;
                    header("Location: ../index.php");
                    exit(); 
                } else {
                    $message = 'Er is een fout opgetreden! Probeer het later opnieuw.';
                }
            } catch (PDOException $e) {
                // Better error handling - log the actual error
                error_log("Registration error: " . $e->getMessage());
                $message = 'Er is een database fout opgetreden. Neem contact op met de beheerder.';
            }
        }
    }

    $_SESSION['register_message'] = $message;
    $_SESSION['register_success'] = false;
    header("Location: ../pages/register.php");
    exit();
}
