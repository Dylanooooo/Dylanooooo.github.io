<?php
// Chat functions for the Flitz Events application

/**
 * Get all users for chat (excluding current user)
 * @param PDO $pdo Database connection
 * @param int $current_user_id Current user ID to exclude
 * @return array Array of users
 */
function getAllChatUsers($pdo, $current_user_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT id, naam, email, rol 
            FROM gebruikers 
            WHERE id != :current_user_id 
            AND actief = 1 
            ORDER BY naam ASC
        ");
        $stmt->execute(['current_user_id' => $current_user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching chat users: " . $e->getMessage());
        return [];
    }
}

/**
 * Get unread message counts for all users
 * @param PDO $pdo Database connection
 * @param int $current_user_id Current user ID
 * @return array Associative array with user_id => unread_count
 */
function getAllUnreadCounts($pdo, $current_user_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT afzender_id, COUNT(*) as unread_count 
            FROM berichten 
            WHERE ontvanger_id = :current_user_id 
            AND gelezen = 0 
            GROUP BY afzender_id
        ");
        $stmt->execute(['current_user_id' => $current_user_id]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $unread_counts = [];
        foreach ($results as $result) {
            $unread_counts[$result['afzender_id']] = (int)$result['unread_count'];
        }
        
        return $unread_counts;
    } catch (PDOException $e) {
        error_log("Error fetching unread counts: " . $e->getMessage());
        return [];
    }
}

/**
 * Get messages between current user and another user
 * @param PDO $pdo Database connection
 * @param int $current_user_id Current user ID
 * @param int $other_user_id Other user ID
 * @return array Array of messages
 */
function getChatMessages($pdo, $current_user_id, $other_user_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT *, 
                   COALESCE(datum_verzonden, timestamp) as timestamp
            FROM berichten 
            WHERE (afzender_id = :current_user_id AND ontvanger_id = :other_user_id) 
            OR (afzender_id = :other_user_id_2 AND ontvanger_id = :current_user_id_2) 
            ORDER BY COALESCE(datum_verzonden, timestamp) ASC
        ");
        $stmt->execute([
            'current_user_id' => $current_user_id,
            'other_user_id' => $other_user_id,
            'other_user_id_2' => $other_user_id,
            'current_user_id_2' => $current_user_id
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching chat messages: " . $e->getMessage());
        return [];
    }
}

/**
 * Send a new message
 * @param PDO $pdo Database connection
 * @param int $sender_id Sender user ID
 * @param int $receiver_id Receiver user ID
 * @param string $message Message content
 * @return bool Success status
 */
function sendMessage($pdo, $sender_id, $receiver_id, $message) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO berichten (afzender_id, ontvanger_id, bericht, gelezen, datum_verzonden, timestamp) 
            VALUES (:sender_id, :receiver_id, :message, 0, NOW(), NOW())
        ");
        
        return $stmt->execute([
            'sender_id' => $sender_id,
            'receiver_id' => $receiver_id,
            'message' => trim($message)
        ]);
    } catch (PDOException $e) {
        error_log("Error sending message: " . $e->getMessage());
        return false;
    }
}

/**
 * Mark messages as read
 * @param PDO $pdo Database connection
 * @param int $current_user_id Current user ID
 * @param int $sender_id Sender user ID whose messages to mark as read
 * @return bool Success status
 */
function markMessagesAsRead($pdo, $current_user_id, $sender_id) {
    try {
        $stmt = $pdo->prepare("
            UPDATE berichten 
            SET gelezen = 1 
            WHERE ontvanger_id = :current_user_id 
            AND afzender_id = :sender_id 
            AND gelezen = 0
        ");
        
        return $stmt->execute([
            'current_user_id' => $current_user_id,
            'sender_id' => $sender_id
        ]);
    } catch (PDOException $e) {
        error_log("Error marking messages as read: " . $e->getMessage());
        return false;
    }
}

/**
 * Get total unread message count for a user
 * @param PDO $pdo Database connection
 * @param int $user_id User ID
 * @return int Total unread message count
 */
function getTotalUnreadCount($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total_unread 
            FROM berichten 
            WHERE ontvanger_id = :user_id 
            AND gelezen = 0
        ");
        $stmt->execute(['user_id' => $user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int)($result['total_unread'] ?? 0);
    } catch (PDOException $e) {
        error_log("Error getting total unread count: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get last message between two users
 * @param PDO $pdo Database connection
 * @param int $user1_id First user ID
 * @param int $user2_id Second user ID
 * @return array|null Last message or null if no messages
 */
function getLastMessage($pdo, $user1_id, $user2_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT *, 
                   COALESCE(datum_verzonden, timestamp) as timestamp
            FROM berichten 
            WHERE (afzender_id = :user1_id AND ontvanger_id = :user2_id) 
            OR (afzender_id = :user2_id AND ontvanger_id = :user1_id) 
            ORDER BY COALESCE(datum_verzonden, timestamp) DESC 
            LIMIT 1
        ");
        $stmt->execute([
            'user1_id' => $user1_id,
            'user2_id' => $user2_id
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting last message: " . $e->getMessage());
        return null;
    }
}

/**
 * Check if user exists and is active
 * @param PDO $pdo Database connection
 * @param int $user_id User ID to check
 * @return bool True if user exists and is active
 */
function isValidChatUser($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM gebruikers 
            WHERE id = :user_id 
            AND actief = 1
        ");
        $stmt->execute(['user_id' => $user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int)($result['count'] ?? 0) > 0;
    } catch (PDOException $e) {
        error_log("Error checking valid chat user: " . $e->getMessage());
        return false;
    }
}

/**
 * Get user info by ID
 * @param PDO $pdo Database connection
 * @param int $user_id User ID
 * @return array|null User info or null if not found
 */
function getChatUserInfo($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT id, naam, email, rol 
            FROM gebruikers 
            WHERE id = :user_id 
            AND actief = 1
        ");
        $stmt->execute(['user_id' => $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting chat user info: " . $e->getMessage());
        return null;
    }
}

/**
 * Delete old messages (cleanup function)
 * @param PDO $pdo Database connection
 * @param int $days_old Number of days old messages to delete (default 365)
 * @return bool Success status
 */
function deleteOldMessages($pdo, $days_old = 365) {
    try {
        $stmt = $pdo->prepare("
            DELETE FROM berichten 
            WHERE COALESCE(datum_verzonden, timestamp) < DATE_SUB(NOW(), INTERVAL :days_old DAY)
        ");
        
        return $stmt->execute(['days_old' => $days_old]);
    } catch (PDOException $e) {
        error_log("Error deleting old messages: " . $e->getMessage());
        return false;
    }
}

/**
 * Search messages
 * @param PDO $pdo Database connection
 * @param int $user_id Current user ID
 * @param string $search_term Search term
 * @param int $limit Maximum number of results
 * @return array Array of matching messages
 */
function searchMessages($pdo, $user_id, $search_term, $limit = 50) {
    try {
        $stmt = $pdo->prepare("
            SELECT b.*, u.naam as sender_name,
                   COALESCE(b.datum_verzonden, b.timestamp) as timestamp
            FROM berichten b
            JOIN gebruikers u ON b.afzender_id = u.id
            WHERE (b.afzender_id = :user_id OR b.ontvanger_id = :user_id)
            AND b.bericht LIKE :search_term
            ORDER BY COALESCE(b.datum_verzonden, b.timestamp) DESC
            LIMIT :limit
        ");
        
        $search_param = '%' . $search_term . '%';
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':search_term', $search_param, PDO::PARAM_STR);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error searching messages: " . $e->getMessage());
        return [];
    }
}
?>
