<?php
session_start();
include('../includes/config.php');

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['valid' => false, 'message' => 'Not logged in']);
    exit;
}

// Verify user exists in database
try {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT id FROM gebruikers WHERE id = :id");
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch();
    
    if ($user) {
        echo json_encode(['valid' => true]);
    } else {
        // Session refers to a user that doesn't exist in the database
        echo json_encode(['valid' => false, 'message' => 'User not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['valid' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>