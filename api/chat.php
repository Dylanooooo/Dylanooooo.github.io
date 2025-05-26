<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Niet ingelogd']);
    exit;
}

include('../includes/config.php');
include('../includes/chat_functions.php');

$current_user_id = $_SESSION['user_id'];

// Handle different actions
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_messages':
        handleGetMessages($pdo, $current_user_id);
        break;
    
    case 'send_message':
        handleSendMessage($pdo, $current_user_id);
        break;
    
    case 'mark_read':
        handleMarkRead($pdo, $current_user_id);
        break;
    
    case 'get_users':
        handleGetUsers($pdo, $current_user_id);
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Ongeldige actie']);
        break;
}

function handleGetMessages($pdo, $current_user_id) {
    $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
    
    if (!$user_id) {
        echo json_encode(['success' => false, 'message' => 'Gebruiker ID ontbreekt']);
        return;
    }
    
    // Validate that the user exists
    if (!isValidChatUser($pdo, $user_id)) {
        echo json_encode(['success' => false, 'message' => 'Ongeldige gebruiker']);
        return;
    }
    
    $messages = getChatMessages($pdo, $current_user_id, $user_id);
    
    echo json_encode([
        'success' => true,
        'messages' => $messages
    ]);
}

function handleSendMessage($pdo, $current_user_id) {
    $receiver_id = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    
    if (!$receiver_id) {
        echo json_encode(['success' => false, 'message' => 'Ontvanger ID ontbreekt']);
        return;
    }
    
    if (!$message) {
        echo json_encode(['success' => false, 'message' => 'Bericht mag niet leeg zijn']);
        return;
    }
    
    if (strlen($message) > 1000) {
        echo json_encode(['success' => false, 'message' => 'Bericht is te lang']);
        return;
    }
    
    // Validate that the receiver exists
    if (!isValidChatUser($pdo, $receiver_id)) {
        echo json_encode(['success' => false, 'message' => 'Ongeldige ontvanger']);
        return;
    }
    
    $success = sendMessage($pdo, $current_user_id, $receiver_id, $message);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Bericht verzonden']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Fout bij verzenden bericht']);
    }
}

function handleMarkRead($pdo, $current_user_id) {
    $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
    
    if (!$user_id) {
        echo json_encode(['success' => false, 'message' => 'Gebruiker ID ontbreekt']);
        return;
    }
    
    $success = markMessagesAsRead($pdo, $current_user_id, $user_id);
    
    echo json_encode(['success' => $success]);
}

function handleGetUsers($pdo, $current_user_id) {
    $users = getAllChatUsers($pdo, $current_user_id);
    $unread_counts = getAllUnreadCounts($pdo, $current_user_id);
    
    echo json_encode([
        'success' => true,
        'users' => $users,
        'unread_counts' => $unread_counts
    ]);
}
?>
