<?php
// Function to get all users for the chat
function getAllChatUsers($pdo, $current_user_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT g.id, g.naam, g.rol,
                (SELECT COUNT(*) FROM berichten 
                 WHERE afzender_id = g.id 
                 AND ontvanger_id = :user_id 
                 AND gelezen = 0) as unread_count
            FROM gebruikers g
            WHERE g.id != :user_id
            ORDER BY g.naam ASC
        ");
        $stmt->bindParam(':user_id', $current_user_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching chat users: " . $e->getMessage());
        return [];
    }
}

// Function to get unread message count
function getUnreadMessageCount($pdo, $current_user_id, $sender_id = null) {
    try {
        if ($sender_id) {
            // Get count for specific sender
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM berichten 
                WHERE ontvanger_id = :user_id 
                AND afzender_id = :sender_id 
                AND gelezen = 0
            ");
            $stmt->bindParam(':user_id', $current_user_id, PDO::PARAM_INT);
            $stmt->bindParam(':sender_id', $sender_id, PDO::PARAM_INT);
        } else {
            // Get total count
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM berichten 
                WHERE ontvanger_id = :user_id 
                AND gelezen = 0
            ");
            $stmt->bindParam(':user_id', $current_user_id, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error fetching unread message count: " . $e->getMessage());
        return 0;
    }
}

// Function to send a message
function sendMessage($pdo, $sender_id, $receiver_id, $message) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO berichten (afzender_id, ontvanger_id, bericht, timestamp, gelezen) 
            VALUES (:afzender_id, :ontvanger_id, :bericht, NOW(), 0)
        ");
        $stmt->bindParam(':afzender_id', $sender_id, PDO::PARAM_INT);
        $stmt->bindParam(':ontvanger_id', $receiver_id, PDO::PARAM_INT);
        $stmt->bindParam(':bericht', $message, PDO::PARAM_STR);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error sending message: " . $e->getMessage());
        return false;
    }
}

// Function to mark messages as read
function markMessagesAsRead($pdo, $receiver_id, $sender_id) {
    try {
        $stmt = $pdo->prepare("
            UPDATE berichten 
            SET gelezen = 1 
            WHERE afzender_id = :sender_id AND ontvanger_id = :receiver_id AND gelezen = 0
        ");
        $stmt->bindParam(':sender_id', $sender_id, PDO::PARAM_INT);
        $stmt->bindParam(':receiver_id', $receiver_id, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error marking messages as read: " . $e->getMessage());
        return false;
    }
}
?>
