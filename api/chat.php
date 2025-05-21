<?php
session_start();
require_once('../includes/config.php');
require_once('../includes/chat_functions.php');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Je moet ingelogd zijn']);
    exit();
}

$current_user_id = $_SESSION['user_id'];

// Get users
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_users') {
    try {
        // First, get users with unread messages
        $stmt = $pdo->prepare("
            SELECT DISTINCT u.id, u.naam, u.rol, 
                (SELECT COUNT(*) FROM berichten 
                 WHERE afzender_id = u.id 
                 AND ontvanger_id = :user_id 
                 AND gelezen = 0) as unread_count
            FROM gebruikers u
            JOIN berichten b ON (b.afzender_id = u.id AND b.ontvanger_id = :user_id) 
                            OR (b.afzender_id = :user_id AND b.ontvanger_id = u.id)
            WHERE u.id != :user_id
            ORDER BY unread_count DESC, u.naam ASC
        ");
        $stmt->bindParam(':user_id', $current_user_id, PDO::PARAM_INT);
        $stmt->execute();
        $users_with_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Then, get other users without messages
        $stmt = $pdo->prepare("
            SELECT u.id, u.naam, u.rol, 0 as unread_count
            FROM gebruikers u
            WHERE u.id != :user_id
            AND u.id NOT IN (
                SELECT DISTINCT CASE 
                    WHEN b.afzender_id = :user_id THEN b.ontvanger_id
                    ELSE b.afzender_id
                END
                FROM berichten b
                WHERE b.afzender_id = :user_id OR b.ontvanger_id = :user_id
            )
            ORDER BY u.naam ASC
        ");
        $stmt->bindParam(':user_id', $current_user_id, PDO::PARAM_INT);
        $stmt->execute();
        $users_without_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Combine both sets
        $users = array_merge($users_with_messages, $users_without_messages);
        
        // Get unread counts for the navigation badge
        $stmt = $pdo->prepare("
            SELECT afzender_id, COUNT(*) as count
            FROM berichten
            WHERE ontvanger_id = :user_id AND gelezen = 0
            GROUP BY afzender_id
        ");
        $stmt->bindParam(':user_id', $current_user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $unread_counts = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $unread_counts[$row['afzender_id']] = (int)$row['count'];
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'users' => $users,
            'unread_counts' => $unread_counts
        ]);
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database fout: ' . $e->getMessage()]);
    }
    exit();
}

// Get messages between current user and specified user
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_messages' && isset($_GET['user_id'])) {
    $other_user_id = intval($_GET['user_id']);
    
    // Debug log to see what's happening
    error_log("Chat: Fetching messages between user {$current_user_id} and user {$other_user_id}");
    
    // Validate user exists
    $stmt = $pdo->prepare("SELECT id FROM gebruikers WHERE id = :id");
    $stmt->bindParam(':id', $other_user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        error_log("Chat: User {$other_user_id} not found");
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Gebruiker bestaat niet']);
        exit();
    }
    
    try {
        // This SQL query should get all messages between the two users
        $sql = "
            SELECT * FROM berichten 
            WHERE (afzender_id = :user1 AND ontvanger_id = :user2) 
            OR (afzender_id = :user2 AND ontvanger_id = :user1)
            ORDER BY timestamp ASC
        ";
        error_log("Chat: SQL Query: " . $sql);
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user1', $current_user_id, PDO::PARAM_INT);
        $stmt->bindParam(':user2', $other_user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Log the messages for debugging
        error_log("Chat: Found " . count($messages) . " messages");
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'messages' => $messages]);
        exit();
    } catch (PDOException $e) {
        error_log("Chat: Error fetching messages: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database fout: ' . $e->getMessage()]);
        exit();
    }
}

// Mark messages as read
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'mark_read' && isset($_GET['user_id'])) {
    $sender_id = intval($_GET['user_id']);
    
    try {
        $stmt = $pdo->prepare("
            UPDATE berichten 
            SET gelezen = 1 
            WHERE afzender_id = :sender_id AND ontvanger_id = :receiver_id AND gelezen = 0
        ");
        $stmt->bindParam(':sender_id', $sender_id, PDO::PARAM_INT);
        $stmt->bindParam(':receiver_id', $current_user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit();
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database fout: ' . $e->getMessage()]);
        exit();
    }
}

// Send message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_message') {
    if (!isset($_POST['receiver_id']) || !isset($_POST['message']) || empty($_POST['message'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Ongeldige parameters']);
        exit();
    }
    
    $receiver_id = intval($_POST['receiver_id']);
    $message = trim($_POST['message']);
    
    // Validate receiver exists
    $stmt = $pdo->prepare("SELECT id FROM gebruikers WHERE id = :id");
    $stmt->bindParam(':id', $receiver_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Ontvanger bestaat niet']);
        exit();
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO berichten (afzender_id, ontvanger_id, bericht, timestamp, gelezen) 
            VALUES (:afzender_id, :ontvanger_id, :bericht, NOW(), 0)
        ");
        $stmt->bindParam(':afzender_id', $current_user_id, PDO::PARAM_INT);
        $stmt->bindParam(':ontvanger_id', $receiver_id, PDO::PARAM_INT);
        $stmt->bindParam(':bericht', $message, PDO::PARAM_STR);
        $stmt->execute();
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit();
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database fout: ' . $e->getMessage()]);
        exit();
    }
}

// If we get here, action not recognized
header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Ongeldige actie']);
exit();
?>
