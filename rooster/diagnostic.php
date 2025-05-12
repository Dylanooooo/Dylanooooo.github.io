<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['rol']) || strtolower($_SESSION['rol']) !== 'admin') {
    echo "<p>Je hebt geen toegang tot deze pagina.</p>";
    echo "<p><a href='../pages/dashboard.php'>Terug naar dashboard</a></p>";
    exit;
}

// Redirect to the new API endpoint for diagnostics
header("Location: ../api/rooster_api.php?action=diagnostic");
exit;
?>
