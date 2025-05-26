<?php
session_start();
include('../includes/config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

// Relatief pad voor navigatie
$root_path = "../";
$pageTitle = "Chat - Flitz Events";
$useIcons = true;

$user_id = $_SESSION['user_id'];

// Check if the current user exists
try {
    $stmt = $pdo->prepare("SELECT * FROM gebruikers WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    $current_user = $stmt->fetch();
    
    if (!$current_user) {
        session_destroy();
        header("Location: ../index.php");
        exit;
    }
} catch (PDOException $e) {
    error_log("Error checking user: " . $e->getMessage());
    header("Location: ../index.php");
    exit;
}

// Get all users for the contacts list (excluding current user)
try {
    $stmt = $pdo->prepare("
        SELECT id, naam, email, rol 
        FROM gebruikers 
        WHERE id != :current_user_id 
        AND actief = 1 
        ORDER BY naam ASC
    ");
    $stmt->execute(['current_user_id' => $user_id]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching users: " . $e->getMessage());
    $users = [];
}

// Get unread counts
try {
    $stmt = $pdo->prepare("
        SELECT afzender_id, COUNT(*) as unread_count 
        FROM berichten 
        WHERE ontvanger_id = :current_user_id 
        AND gelezen = 0 
        GROUP BY afzender_id
    ");
    $stmt->execute(['current_user_id' => $user_id]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $unread_counts = [];
    foreach ($results as $result) {
        $unread_counts[$result['afzender_id']] = (int)$result['unread_count'];
    }
} catch (PDOException $e) {
    error_log("Error fetching unread counts: " . $e->getMessage());
    $unread_counts = [];
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Simple chat interface styles */
        .chat-container {
            display: flex;
            height: 600px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: 20px;
        }

        .contacts-sidebar {
            width: 300px;
            background: #f8f9fa;
            border-right: 1px solid #e1e1e1;
        }

        .contacts-header {
            padding: 20px;
            background: linear-gradient(90deg, #a71680 0%, #ec6708 100%);
            color: white;
        }

        .contacts-header h3 {
            margin: 0;
            font-size: 1.1rem;
        }

        .contact-search {
            padding: 15px;
            border-bottom: 1px solid #e1e1e1;
        }

        .contact-search input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .contacts-list {
            height: calc(100% - 140px);
            overflow-y: auto;
        }

        .contact-item {
            padding: 12px 20px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .contact-item:hover {
            background-color: #f0f0f0;
        }

        .contact-item.active {
            background-color: #e3f2fd;
            border-left: 3px solid #a71680;
        }

        .contact-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #a71680;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }

        .contact-info h4 {
            margin: 0 0 4px 0;
            font-size: 14px;
            color: #333;
        }

        .contact-info p {
            margin: 0;
            font-size: 12px;
            color: #666;
        }

        .unread-badge {
            background: #ff4444;
            color: white;
            border-radius: 10px;
            padding: 2px 6px;
            font-size: 11px;
            font-weight: 600;
            margin-left: auto;
        }

        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .chat-header {
            padding: 15px 20px;
            background: white;
            border-bottom: 1px solid #e1e1e1;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .chat-header h4 {
            margin: 0;
            color: #333;
        }

        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #fafafa;
        }

        .message {
            margin-bottom: 15px;
            padding: 10px 15px;
            border-radius: 8px;
            max-width: 70%;
        }

        .message.sent {
            background: #e3f2fd;
            margin-left: auto;
            text-align: right;
        }

        .message.received {
            background: white;
            margin-right: auto;
        }

        .message-content {
            margin: 0;
            color: #333;
        }

        .message-time {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }

        .chat-input {
            padding: 15px 20px;
            background: white;
            border-top: 1px solid #e1e1e1;
        }

        .input-group {
            display: flex;
            gap: 0;
            width: 100%;
        }

        .input-group input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px 0 0 4px;
            font-size: 14px;
            outline: none;
            width: 100%;
            box-sizing: border-box;
        }

        .input-group input:focus {
            border-color: #a71680;
        }

        .input-group button {
            padding: 10px 20px;
            background: linear-gradient(90deg, #a71680 0%, #ec6708 100%);
            color: white;
            border: 1px solid #a71680;
            border-radius: 0 4px 4px 0;
            border-left: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
        }

        .input-group button:hover {
            opacity: 0.9;
        }

        .no-chat-selected {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #666;
            text-align: center;
        }

        .no-chat-selected i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .loading {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }

        .error {
            color: #d9534f;
            text-align: center;
            padding: 20px;
        }

        .no-messages {
            text-align: center;
            color: #999;
            padding: 30px;
            font-style: italic;
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .chat-container {
                height: 500px;
            }
            
            .contacts-sidebar {
                width: 250px;
            }
            
            .message {
                max-width: 85%;
            }
        }
    </style>
</head>
<body>
    <?php include('../includes/navigation.php'); ?>

    <section id="chat-page">
        <div class="container">
            <h2>Chat</h2>
            
            <div class="chat-container">
                <!-- Contacts sidebar -->
                <div class="contacts-sidebar">
                    <div class="contacts-header">
                        <h3>Contacten</h3>
                    </div>
                    
                    <div class="contact-search">
                        <input type="text" id="contact-search" placeholder="Zoek contacten...">
                    </div>
                    
                    <div class="contacts-list" id="contacts-list">
                        <?php foreach ($users as $user): ?>
                            <div class="contact-item" data-user-id="<?php echo $user['id']; ?>" data-user-name="<?php echo htmlspecialchars($user['naam']); ?>">
                                <div class="contact-avatar">
                                    <?php
                                    $name_parts = explode(' ', $user['naam']);
                                    $initials = strtoupper(substr($name_parts[0], 0, 1));
                                    if (count($name_parts) > 1) {
                                        $initials .= strtoupper(substr(end($name_parts), 0, 1));
                                    }
                                    echo $initials;
                                    ?>
                                </div>
                                <div class="contact-info">
                                    <h4><?php echo htmlspecialchars($user['naam']); ?></h4>
                                    <p><?php echo ucfirst($user['rol']); ?></p>
                                </div>
                                <?php if (isset($unread_counts[$user['id']]) && $unread_counts[$user['id']] > 0): ?>
                                    <span class="unread-badge"><?php echo $unread_counts[$user['id']]; ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (empty($users)): ?>
                            <div class="no-contacts">
                                <p>Geen contacten beschikbaar.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Chat area -->
                <div class="chat-area" id="chat-area">
                    <div class="no-chat-selected">
                        <i class="fas fa-comments"></i>
                        <h3>Selecteer een contact</h3>
                        <p>Kies een contact uit de lijst om te beginnen met chatten.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Hidden input for current user ID -->
    <input type="hidden" id="current-user-id" value="<?php echo $user_id; ?>">

    <?php include('../includes/footer.php'); ?>

    <script>
        let currentChatUserId = null;
        let messageRefreshInterval = null;

        document.addEventListener('DOMContentLoaded', function() {
            initializeChat();
        });

        function initializeChat() {
            const contactItems = document.querySelectorAll('.contact-item');
            
            contactItems.forEach(item => {
                item.addEventListener('click', function() {
                    const userId = this.getAttribute('data-user-id');
                    const userName = this.getAttribute('data-user-name');
                    openChat(userId, userName, this);
                });
            });

            // Contact search functionality
            document.getElementById('contact-search').addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const contacts = document.querySelectorAll('.contact-item');
                
                contacts.forEach(contact => {
                    const name = contact.querySelector('h4').textContent.toLowerCase();
                    const role = contact.querySelector('p').textContent.toLowerCase();
                    
                    if (name.includes(searchTerm) || role.includes(searchTerm)) {
                        contact.style.display = 'flex';
                    } else {
                        contact.style.display = 'none';
                    }
                });
            });
        }

        function openChat(userId, userName, contactElement) {
            // Update active contact
            document.querySelectorAll('.contact-item').forEach(item => {
                item.classList.remove('active');
            });
            contactElement.classList.add('active');
            
            // Store current chat user
            currentChatUserId = userId;
            
            // Build chat interface
            const chatArea = document.getElementById('chat-area');
            chatArea.innerHTML = `
                <div class="chat-header">
                    <div class="contact-avatar">
                        ${contactElement.querySelector('.contact-avatar').textContent}
                    </div>
                    <h4>Chat met ${userName}</h4>
                </div>
                <div class="chat-messages" id="chat-messages">
                    <div class="loading">Berichten worden geladen...</div>
                </div>
                <div class="chat-input">
                    <form class="input-group" id="message-form">
                        <input type="text" id="message-input" placeholder="Type een bericht..." autocomplete="off">
                        <button type="submit">
                            <i class="fas fa-paper-plane"></i> Versturen
                        </button>
                    </form>
                </div>
            `;
            
            // Load messages
            loadMessages(userId);
            
            // Set up message form
            setupMessageForm();
            
            // Clear unread indicator
            const unreadIndicator = contactElement.querySelector('.unread-badge');
            if (unreadIndicator) {
                unreadIndicator.remove();
            }
            
            // Mark messages as read
            markMessagesAsRead(userId);
            
            // Start auto-refresh
            startMessageRefresh();
        }

        function loadMessages(userId) {
            const chatMessages = document.getElementById('chat-messages');
            if (!chatMessages) return;

            chatMessages.innerHTML = '<div class="loading">Berichten laden...</div>';

            // Use the working chat_ajax.php endpoint
            fetch(`../api/chat_ajax.php?action=get_messages&contact_id=${userId}&last_id=0`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayMessages(data.messages);
                    } else {
                        chatMessages.innerHTML = '<div class="error">Fout bij het laden van berichten: ' + data.message + '</div>';
                    }
                })
                .catch(error => {
                    console.error('Error loading messages:', error);
                    chatMessages.innerHTML = '<div class="error">Fout bij het laden van berichten.</div>';
                });
        }

        function displayMessages(messages) {
            const messagesContainer = document.getElementById('chat-messages');
            const currentUserId = document.getElementById('current-user-id').value;
            
            if (messages.length === 0) {
                messagesContainer.innerHTML = '<div class="no-messages">Geen berichten. Start het gesprek!</div>';
                return;
            }
            
            let html = '';
            
            messages.forEach(message => {
                // Handle different property names for message content
                const messageText = message.bericht || message.message || 'Bericht niet beschikbaar';
                
                const isFromMe = parseInt(message.afzender_id) === parseInt(currentUserId);
                const messageClass = isFromMe ? 'sent' : 'received';
                
                // Handle timestamp properly
                let messageDate;
                if (message.timestamp) {
                    messageDate = new Date(message.timestamp);
                } else if (message.datum_verzonden) {
                    messageDate = new Date(message.datum_verzonden);
                } else {
                    messageDate = new Date();
                }
                
                const time = messageDate.toLocaleTimeString('nl-NL', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                html += `
                    <div class="message ${messageClass}">
                        <div class="message-content">${escapeHtml(messageText)}</div>
                        <div class="message-time">${time}</div>
                    </div>
                `;
            });
            
            messagesContainer.innerHTML = html;
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        // Add helper function for HTML escaping
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function setupMessageForm() {
            const form = document.getElementById('message-form');
            const input = document.getElementById('message-input');
            
            if (!form || !input) {
                console.error('Message form elements not found');
                return;
            }
            
            // Remove any existing event listeners
            form.removeEventListener('submit', handleFormSubmit);
            
            // Add new event listener
            form.addEventListener('submit', handleFormSubmit);
            
            input.focus();
        }
        
        function handleFormSubmit(e) {
            e.preventDefault();
            sendMessage();
        }

        function sendMessage() {
            const input = document.getElementById('message-input');
            const message = input.value.trim();
            
            if (!message || !currentChatUserId) {
                console.log('No message or no current chat user');
                return;
            }
            
            console.log('Sending message:', message, 'to user:', currentChatUserId);
            
            // Disable input while sending
            input.disabled = true;
            
            const formData = new FormData();
            formData.append('action', 'send_message');
            formData.append('contact_id', currentChatUserId);
            formData.append('message', message);
            
            fetch('../api/chat_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Send message response:', data);
                if (data.success) {
                    input.value = '';
                    loadMessages(currentChatUserId);
                } else {
                    alert('Fout bij verzenden: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error sending message:', error);
                alert('Fout bij het verzenden van het bericht.');
            })
            .finally(() => {
                // Re-enable input
                input.disabled = false;
                input.focus();
            });
        }

        function markMessagesAsRead(userId) {
            fetch(`../api/chat_ajax.php?action=mark_read&contact_id=${userId}`)
                .then(response => response.json())
                .catch(error => console.error('Error marking messages as read:', error));
        }

        function startMessageRefresh() {
            // Clear existing interval
            if (messageRefreshInterval) {
                clearInterval(messageRefreshInterval);
            }
            
            // Set new interval
            messageRefreshInterval = setInterval(() => {
                if (currentChatUserId) {
                    loadMessages(currentChatUserId);
                }
            }, 10000); // Refresh every 10 seconds
        }

        // Clean up on page unload
        window.addEventListener('beforeunload', function() {
            if (messageRefreshInterval) {
                clearInterval(messageRefreshInterval);
            }
        });
    </script>
</body>
</html>