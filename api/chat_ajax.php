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

// Handle sending a message - fix parameter binding
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_message') {
    if (isset($_POST['contact_id']) && isset($_POST['message']) && !empty($_POST['message'])) {
        $contact_id = (int)$_POST['contact_id'];
        $message = trim($_POST['message']);
        
        // Log message attempt
        error_log("User {$_SESSION['user_id']} attempting to send message to user {$contact_id}");
        
        try {
            $sql = "INSERT INTO berichten (afzender_id, ontvanger_id, bericht) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$_SESSION['user_id'], $contact_id, $message])) {
                // Get the newly inserted message with its details
                $messageId = $pdo->lastInsertId();
                $sql = "SELECT * FROM berichten WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$messageId]);
                $insertedMessage = $stmt->fetch(PDO::FETCH_ASSOC);
                
                error_log("Message successfully sent with ID: {$messageId}");
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => 'Bericht verzonden', 
                    'data' => [
                        'id' => $insertedMessage['id'],
                        'bericht' => $insertedMessage['bericht'],
                        'datum' => $insertedMessage['datum_verzonden']
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

// Handle getting all users for chat
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_users') {
    try {
        // Get all users except current user
        $sql = "SELECT id, naam, email, rol FROM gebruikers WHERE id != ? AND actief = 1 ORDER BY naam ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['user_id']]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get unread counts
        $sql = "SELECT afzender_id, COUNT(*) as count FROM berichten 
                WHERE ontvanger_id = ? AND gelezen = 0
                GROUP BY afzender_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['user_id']]);
        $unread_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $unread_counts = [];
        foreach ($unread_results as $result) {
            $unread_counts[$result['afzender_id']] = (int)$result['count'];
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'users' => $users,
            'unread_counts' => $unread_counts
        ]);
        exit();
    } catch (PDOException $e) {
        error_log("Error fetching users: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database fout bij ophalen gebruikers']);
        exit();
    }
}

// Handle getting messages - fix the response format
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_messages') {
    if (isset($_GET['contact_id']) && is_numeric($_GET['contact_id'])) {
        $contact_id = (int)$_GET['contact_id'];
        $last_id = isset($_GET['last_id']) && is_numeric($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
        
        try {
            // Simplified query with proper parameter binding
            if ($last_id == 0) {
                // Get all messages for initial load
                $sql = "SELECT id, afzender_id, ontvanger_id, bericht, gelezen, datum_verzonden
                        FROM berichten 
                        WHERE (afzender_id = ? AND ontvanger_id = ?) 
                        OR (afzender_id = ? AND ontvanger_id = ?)
                        ORDER BY datum_verzonden ASC, id ASC";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_SESSION['user_id'], $contact_id, $contact_id, $_SESSION['user_id']]);
            } else {
                // Get only new messages since last_id
                $sql = "SELECT id, afzender_id, ontvanger_id, bericht, gelezen, datum_verzonden
                        FROM berichten 
                        WHERE ((afzender_id = ? AND ontvanger_id = ?) 
                        OR (afzender_id = ? AND ontvanger_id = ?))
                        AND id > ?
                        ORDER BY datum_verzonden ASC, id ASC";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_SESSION['user_id'], $contact_id, $contact_id, $_SESSION['user_id'], $last_id]);
            }
            
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Add timestamp and message fields for compatibility
            foreach ($messages as &$message) {
                $message['timestamp'] = $message['datum_verzonden'];
                $message['message'] = $message['bericht'];
            }
            
            // Mark messages as read using simple query
            $sql = "UPDATE berichten SET gelezen = 1 WHERE afzender_id = ? AND ontvanger_id = ? AND gelezen = 0";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$contact_id, $_SESSION['user_id']]);
            
            error_log("Successfully fetched " . count($messages) . " messages for user {$_SESSION['user_id']} from contact {$contact_id}");
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'messages' => $messages]);
            exit();
        } catch (PDOException $e) {
            error_log("Error fetching messages: " . $e->getMessage());
            error_log("SQL Query failed for user {$_SESSION['user_id']} and contact {$contact_id}");
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Database fout: ' . $e->getMessage()]);
            exit();
        }
    } else {
        error_log("Invalid contact_id parameter: " . ($_GET['contact_id'] ?? 'not set'));
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Ongeldige contact ID']);
        exit();
    }
}

// Add mark_read action handler
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'mark_read') {
    if (isset($_GET['contact_id']) && is_numeric($_GET['contact_id'])) {
        $contact_id = (int)$_GET['contact_id'];
        
        try {
            $sql = "UPDATE berichten SET gelezen = 1 WHERE afzender_id = ? AND ontvanger_id = ? AND gelezen = 0";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$contact_id, $_SESSION['user_id']]);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit();
        } catch (PDOException $e) {
            error_log("Error marking messages as read: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Database fout']);
            exit();
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Ongeldige contact ID']);
        exit();
    }
}

// If we reach here, unknown action
header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Ongeldige actie']);
exit();
?>
