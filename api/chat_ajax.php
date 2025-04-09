<?php
session_start();
include('../includes/config.php');

// Add debug logging to trace issues
error_log("Chat AJAX request from user ID: " . ($_SESSION['user_id'] ?? 'Not set') . " with role: " . ($_SESSION['rol'] ?? 'Unknown'));

// Check if user is logged in - simplify the check
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Niet ingelogd']);
    exit();
}

// Handle sending a message - fix for all user types
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_message') {
    if (isset($_POST['contact_id']) && isset($_POST['message']) && !empty($_POST['message'])) {
        $contact_id = $_POST['contact_id'];
        $message = trim($_POST['message']);
        
        // Log message attempt
        error_log("User {$_SESSION['user_id']} attempting to send message to user {$contact_id}");
        
        // Simplify database operation - no role checks
        try {
            $sql = "INSERT INTO berichten (afzender_id, ontvanger_id, bericht) VALUES (:sender, :receiver, :message)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':sender', $_SESSION['user_id']);
            $stmt->bindParam(':receiver', $contact_id);
            $stmt->bindParam(':message', $message);
            
            if ($stmt->execute()) {
                // Get the newly inserted message with its details
                $messageId = $pdo->lastInsertId();
                $sql = "SELECT * FROM berichten WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $messageId);
                $stmt->execute();
                $message = $stmt->fetch(PDO::FETCH_ASSOC);
                
                error_log("Message successfully sent with ID: {$messageId}");
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => 'Bericht verzonden', 
                    'data' => [
                        'id' => $message['id'],
                        'bericht' => $message['bericht'],
                        'datum' => $message['datum_verzonden']
                    ]
                ]);
                exit();
            } else {
                error_log("Database error when sending message");
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Kon bericht niet verzenden']);
                exit();
            }
        } catch (PDOException $e) {
            error_log("PDO Exception when sending message: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Database fout: ' . $e->getMessage()]);
            exit();
        }
    } else {
        error_log("Invalid message parameters");
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Ongeldig bericht of ontvanger']);
        exit();
    }
}

// Handle getting new messages
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_messages') {
    if (isset($_GET['contact_id']) && is_numeric($_GET['contact_id'])) {
        $contact_id = $_GET['contact_id'];
        $last_id = isset($_GET['last_id']) && is_numeric($_GET['last_id']) ? $_GET['last_id'] : 0;
        
        // Get any new messages since the last message ID
        $sql = "SELECT * FROM berichten 
                WHERE ((afzender_id = :user_id AND ontvanger_id = :contact_id) 
                OR (afzender_id = :contact_id AND ontvanger_id = :user_id))
                AND id > :last_id
                ORDER BY datum_verzonden";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->bindParam(':contact_id', $contact_id);
        $stmt->bindParam(':last_id', $last_id);
        $stmt->execute();
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Mark messages as read
        $sql = "UPDATE berichten 
                SET gelezen = 1 
                WHERE afzender_id = :contact_id AND ontvanger_id = :user_id AND gelezen = 0";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':contact_id', $contact_id);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'messages' => $messages]);
        exit();
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Ongeldige contact ID']);
        exit();
    }
}

// Handle getting unread message count
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_unread_count') {
    // Get unread message count for all contacts
    $sql = "SELECT afzender_id, COUNT(*) as count FROM berichten 
            WHERE ontvanger_id = :user_id AND gelezen = 0
            GROUP BY afzender_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $unread = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'unread' => $unread]);
    exit();
}

// If we reach here, unknown action
header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Ongeldige actie']);
exit();
?>
