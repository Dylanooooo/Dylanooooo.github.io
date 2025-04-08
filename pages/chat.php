<?php
session_start();
include('../includes/config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Get all users for chat contacts
$sql = "SELECT id, naam, rol FROM gebruikers WHERE id != :current_user";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':current_user', $_SESSION['user_id']);
$stmt->execute();
$contacts = $stmt->fetchAll();

// Get chat messages if a contact is selected
$selected_contact = null;
$messages = [];
$last_message_id = 0;

if (isset($_GET['contact']) && is_numeric($_GET['contact'])) {
    $contact_id = $_GET['contact'];
    
    // Get contact details
    $sql = "SELECT id, naam, rol FROM gebruikers WHERE id = :contact_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':contact_id', $contact_id);
    $stmt->execute();
    $selected_contact = $stmt->fetch();
    
    if ($selected_contact) {
        // Get messages between current user and selected contact
        $sql = "SELECT * FROM berichten 
                WHERE (afzender_id = :user_id AND ontvanger_id = :contact_id) 
                OR (afzender_id = :contact_id AND ontvanger_id = :user_id) 
                ORDER BY datum_verzonden";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->bindParam(':contact_id', $contact_id);
        $stmt->execute();
        $messages = $stmt->fetchAll();
        
        // Get last message ID
        if (count($messages) > 0) {
            $last_message_id = $messages[count($messages)-1]['id'];
        }
        
        // Mark messages as read
        $sql = "UPDATE berichten 
                SET gelezen = 1 
                WHERE afzender_id = :contact_id AND ontvanger_id = :user_id AND gelezen = 0";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':contact_id', $contact_id);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat | Flitz-Events Stageportaal</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="header-container">
            <h1>Welkom bij Flitz-Events Stagiairs Portal</h1>
            <div class="user-info">
                <span id="user-name"><?php echo htmlspecialchars($_SESSION['naam']); ?></span>
                <a href="../auth/logout.php" id="logout-btn">Uitloggen</a>
            </div>
        </div>
    </header>

    <nav>
        <div class="container">
            <div class="menu-toggle" id="mobile-menu">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
            <ul class="nav-list">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="projecten.php">Projecten</a></li>
                <li><a href="chat.php" class="active">Chat</a></li>
                <?php if (isset($_SESSION['rol']) && strtolower($_SESSION['rol']) === 'admin'): ?>
                <li><a href="admin.php">Admin</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <section id="whatsapp-chat">
        <div class="container">
            <div class="whatsapp-container">
                <!-- Linkerpaneel: Chat Lijst -->
                <div class="chat-list-panel" id="chat-list-panel">
                    <div class="panel-header">
                        <h2>Chats</h2>
                        <div class="header-actions">
                            <button type="button" class="icon-button"><i class="fa fa-search"></i></button>
                            <button type="button" class="icon-button"><i class="fa fa-ellipsis-v"></i></button>
                        </div>
                    </div>
                    
                    <div class="search-container">
                        <div class="search-box">
                            <i class="fa fa-search"></i>
                            <input type="text" placeholder="Zoeken of nieuw gesprek starten" id="contact-search">
                        </div>
                    </div>
                    
                    <div class="chats-container" id="contacts-list">
                        <!-- Contacten uit database -->
                        <?php foreach ($contacts as $contact): ?>
                            <div class="chat-item <?php echo (isset($selected_contact) && $selected_contact['id'] == $contact['id']) ? 'active' : ''; ?>" 
                                 data-chat-id="<?php echo $contact['id']; ?>"
                                 onclick="loadChat(<?php echo $contact['id']; ?>)">
                                <div class="chat-avatar">
                                    <?php 
                                    // Toon initialen
                                    $initials = '';
                                    $words = explode(' ', $contact['naam']);
                                    foreach ($words as $word) {
                                        $initials .= substr($word, 0, 1);
                                    }
                                    echo htmlspecialchars(substr($initials, 0, 2));
                                    ?>
                                </div>
                                <div class="chat-details">
                                    <div class="chat-header">
                                        <h4><?php echo htmlspecialchars($contact['naam']); ?></h4>
                                        <span class="chat-time">
                                            <span class="unread-badge" id="unread-<?php echo $contact['id']; ?>" style="display: none;">0</span>
                                        </span>
                                    </div>
                                    <div class="chat-message-preview">
                                        <p><?php echo ucfirst($contact['rol']); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if(count($contacts) == 0): ?>
                            <p class="no-contacts">Geen contacten beschikbaar.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Rechterpaneel: Chat Gesprek -->
                <div class="chat-detail-panel <?php echo $selected_contact ? 'active' : ''; ?>" id="chat-detail-panel">
                    <?php if ($selected_contact): ?>
                        <div class="panel-header">
                            <button type="button" class="back-button" id="back-to-chats" onclick="hideChat()">
                                <i class="fa fa-arrow-left"></i>
                            </button>
                            <div class="chat-contact-info">
                                <div class="contact-avatar">
                                    <?php 
                                    // Toon initialen
                                    $initials = '';
                                    $words = explode(' ', $selected_contact['naam']);
                                    foreach ($words as $word) {
                                        $initials .= substr($word, 0, 1);
                                    }
                                    echo htmlspecialchars(substr($initials, 0, 2));
                                    ?>
                                </div>
                                <div class="contact-details">
                                    <h4><?php echo htmlspecialchars($selected_contact['naam']); ?></h4>
                                    <span class="status">Online</span>
                                </div>
                            </div>
                            <div class="header-actions">
                                <button type="button" class="icon-button"><i class="fa fa-ellipsis-v"></i></button>
                            </div>
                        </div>
                        
                        <div class="messages-container" id="messages-container">
                            <?php if(count($messages) > 0): ?>
                                <?php 
                                $current_date = null;
                                foreach($messages as $message):
                                    // Toon datumscheiding als nodig
                                    $msg_date = date('Y-m-d', strtotime($message['datum_verzonden']));
                                    if ($msg_date != $current_date) {
                                        $current_date = $msg_date;
                                        
                                        // Bepaal juiste datumtekst
                                        $today = date('Y-m-d');
                                        $yesterday = date('Y-m-d', strtotime('-1 day'));
                                        
                                        if ($msg_date == $today) {
                                            $date_text = 'Vandaag';
                                        } elseif ($msg_date == $yesterday) {
                                            $date_text = 'Gisteren';
                                        } else {
                                            $date_text = date('d M Y', strtotime($message['datum_verzonden']));
                                        }
                                        ?>
                                        <div class="chat-date"><?php echo $date_text; ?></div>
                                        <?php
                                    }
                                    
                                    // Bepaal of bericht van huidige gebruiker is
                                    $is_sent = $message['afzender_id'] == $_SESSION['user_id'];
                                    ?>
                                    <div class="message <?php echo $is_sent ? 'sent' : 'received'; ?>" data-message-id="<?php echo $message['id']; ?>">
                                        <div class="message-bubble">
                                            <?php echo nl2br(htmlspecialchars($message['bericht'])); ?>
                                            <span class="message-time"><?php echo date('H:i', strtotime($message['datum_verzonden'])); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-messages">
                                    <p>Start een gesprek met <?php echo htmlspecialchars($selected_contact['naam']); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="input-container">
                            <button type="button" class="icon-button"><i class="fa fa-paperclip"></i></button>
                            <input type="text" placeholder="Typ een bericht" id="message-input" autofocus>
                            <button type="button" class="send-button" id="send-button">
                                <i class="fa fa-paper-plane"></i>
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="no-chat-selected">
                            <div class="no-chat-message">
                                <i class="fa fa-comments fa-4x"></i>
                                <h3>Selecteer een contact om te chatten</h3>
                                <p>Kies een persoon uit de lijst links om een gesprek te starten.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="footer-container">
            <p>&copy; 2025 Flitz-Events Stageportaal | Alle rechten voorbehouden</p>
        </div>
    </footer>

    <script>
        // Sla relevante informatie op voor JavaScript
        const currentUser = <?php echo $_SESSION['user_id']; ?>;
        const currentUserRole = "<?php echo $_SESSION['rol'] ?? ''; ?>";
        const selectedContact = <?php echo $selected_contact ? $selected_contact['id'] : 'null'; ?>;
        let lastMessageId = <?php echo $last_message_id; ?>;
        
        // Functie om een chat te laden
        function loadChat(contactId) {
            // Let op: Dit verandert de URL maar voorkomt page refresh
            const url = new URL(window.location.href);
            url.searchParams.set('contact', contactId);
            window.history.pushState({}, '', url);
            
            // AJAX-verzoek om chatgegevens op te halen
            fetch(`../api/chat_ajax.php?action=get_messages&contact_id=${contactId}&last_id=0`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateChatUI(contactId, data.messages);
                    }
                })
                .catch(error => {
                    console.error('Error loading chat:', error);
                });
        }
        
        // Functie om de chat UI bij te werken zonder page refresh
        function updateChatUI(contactId, messages) {
            // Markeer de actieve contactpersoon
            document.querySelectorAll('.chat-item').forEach(item => {
                item.classList.remove('active');
                if (item.dataset.chatId == contactId) {
                    item.classList.add('active');
                }
            });
            
            // Haal contactgegevens op
            const contactItem = document.querySelector(`.chat-item[data-chat-id="${contactId}"]`);
            if (!contactItem) return;
            
            const contactName = contactItem.querySelector('h4').textContent;
            const contactAvatar = contactItem.querySelector('.chat-avatar').textContent;
            
            // Update de chatdetails
            const detailPanel = document.getElementById('chat-detail-panel');
            detailPanel.classList.add('active');
            
            // Bouw de header
            detailPanel.innerHTML = `
                <div class="panel-header">
                    <button type="button" class="back-button" id="back-to-chats" onclick="hideChat()">
                        <i class="fa fa-arrow-left"></i>
                    </button>
                    <div class="chat-contact-info">
                        <div class="contact-avatar">${contactAvatar}</div>
                        <div class="contact-details">
                            <h4>${contactName}</h4>
                            <span class="status">Online</span>
                        </div>
                    </div>
                    <div class="header-actions">
                        <button type="button" class="icon-button"><i class="fa fa-ellipsis-v"></i></button>
                    </div>
                </div>
                
                <div class="messages-container" id="messages-container">
                </div>
                
                <div class="input-container">
                    <button type="button" class="icon-button"><i class="fa fa-paperclip"></i></button>
                    <input type="text" placeholder="Typ een bericht" id="message-input" autofocus>
                    <button type="button" class="send-button" id="send-button">
                        <i class="fa fa-paper-plane"></i>
                    </button>
                </div>
            `;
            
            // Voeg berichten toe
            const messagesContainer = document.getElementById('messages-container');
            if (messages && messages.length > 0) {
                let currentDate = null;
                
                messages.forEach(message => {
                    // Controleer of we een nieuwe datum moeten tonen
                    const msgDate = new Date(message.datum_verzonden).toISOString().split('T')[0];
                    const today = new Date().toISOString().split('T')[0];
                    const yesterday = new Date(Date.now() - 86400000).toISOString().split('T')[0];
                    
                    let dateText;
                    if (msgDate === today) {
                        dateText = 'Vandaag';
                    } else if (msgDate === yesterday) {
                        dateText = 'Gisteren';
                    } else {
                        const date = new Date(message.datum_verzonden);
                        dateText = date.getDate() + ' ' + 
                                  ['jan', 'feb', 'mrt', 'apr', 'mei', 'jun', 'jul', 'aug', 'sep', 'okt', 'nov', 'dec'][date.getMonth()] + 
                                  ' ' + date.getFullYear();
                    }
                    
                    if (dateText !== currentDate) {
                        currentDate = dateText;
                        const dateElement = document.createElement('div');
                        dateElement.className = 'chat-date';
                        dateElement.textContent = dateText;
                        messagesContainer.appendChild(dateElement);
                    }
                    
                    // Voeg nieuw bericht toe
                    const isSent = parseInt(message.afzender_id) === currentUser;
                    const messageElement = document.createElement('div');
                    messageElement.className = `message ${isSent ? 'sent' : 'received'}`;
                    messageElement.dataset.messageId = message.id;
                    
                    const time = new Date(message.datum_verzonden).toLocaleTimeString('nl-NL', {hour: '2-digit', minute: '2-digit'});
                    
                    messageElement.innerHTML = `
                        <div class="message-bubble">
                            ${message.bericht.replace(/\n/g, '<br>')}
                            <span class="message-time">${time}</span>
                        </div>
                    `;
                    
                    messagesContainer.appendChild(messageElement);
                    lastMessageId = message.id;
                });
                
                // Scroll naar beneden
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            } else {
                // Geen berichten
                messagesContainer.innerHTML = `
                    <div class="no-messages">
                        <p>Start een gesprek met ${contactName}</p>
                    </div>
                `;
            }
            
            // Voeg event listeners toe aan de nieuwe elementen
            document.getElementById('send-button').addEventListener('click', sendMessage);
            document.getElementById('message-input').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    sendMessage();
                }
            });
            
            // Start berichten-check interval
            if (window.messageCheckInterval) {
                clearInterval(window.messageCheckInterval);
            }
            window.messageCheckInterval = setInterval(checkNewMessages, 3000);
        }
        
        // Functie om chat te verbergen op mobiel
        function hideChat() {
            document.getElementById('chat-detail-panel').classList.remove('active');
        }
        
        // Verzend bericht via AJAX - aangepast om admin rol te ondersteunen
        function sendMessage() {
            const messageInput = document.getElementById('message-input');
            if (!messageInput) {
                console.error("Message input not found");
                return;
            }
            
            const message = messageInput.value.trim();
            
            if (!message) {
                console.log("Empty message - not sending");
                return;
            }
            
            if (!selectedContact) {
                console.error("No contact selected");
                return;
            }
            
            console.log(`Sending message to contact ${selectedContact}`);
            
            // Bereid formulierdata voor
            const formData = new FormData();
            formData.append('action', 'send_message');
            formData.append('contact_id', selectedContact);
            formData.append('message', message);
            
            // Toon bericht direct in de UI (optimistische UI)
            const now = new Date();
            const timeString = now.getHours().toString().padStart(2, '0') + ':' + 
                               now.getMinutes().toString().padStart(2, '0');
            
            const messagesContainer = document.getElementById('messages-container');
            if (!messagesContainer) {
                console.error("Messages container not found");
                return;
            }
            
            const messageElement = document.createElement('div');
            messageElement.className = 'message sent';
            messageElement.innerHTML = `
                <div class="message-bubble">
                    ${message.replace(/\n/g, '<br>')}
                    <span class="message-time">${timeString}</span>
                </div>
            `;
            messagesContainer.appendChild(messageElement);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
            
            // Reset input veld
            messageInput.value = '';
            
            // Verzend bericht naar server
            fetch('../api/chat_ajax.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'Cache-Control': 'no-cache, no-store, must-revalidate'
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log("Server response:", data);
                if (data.success) {
                    // Update message-id voor het laatst verzonden bericht
                    messageElement.dataset.messageId = data.data.id;
                    lastMessageId = data.data.id;
                } else {
                    console.error('Bericht verzenden mislukt:', data.message);
                    messageElement.classList.add('error');
                    messageElement.querySelector('.message-bubble').innerHTML += 
                        '<span class="error-icon" title="Verzenden mislukt"><i class="fa fa-exclamation-circle"></i></span>';
                }
            })
            .catch(error => {
                console.error('Fout bij verzenden bericht:', error);
                messageElement.classList.add('error');
                messageElement.querySelector('.message-bubble').innerHTML += 
                    '<span class="error-icon" title="Verzenden mislukt"><i class="fa fa-exclamation-circle"></i></span>';
            });
        }
        
        // Functie om te controleren op nieuwe berichten
        function checkNewMessages() {
            if (!selectedContact) return;
            
            fetch(`../api/chat_ajax.php?action=get_messages&contact_id=${selectedContact}&last_id=${lastMessageId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.messages && data.messages.length > 0) {
                    const messagesContainer = document.getElementById('messages-container');
                    let currentDate = messagesContainer.querySelector('.chat-date:last-child')?.textContent || null;
                    
                    data.messages.forEach(message => {
                        // Controleer of bericht al bestaat
                        if (document.querySelector(`.message[data-message-id="${message.id}"]`)) {
                            return;
                        }
                        
                        // Controleer of we een nieuwe datum moeten tonen
                        const msgDate = new Date(message.datum_verzonden).toISOString().split('T')[0];
                        const today = new Date().toISOString().split('T')[0];
                        const yesterday = new Date(Date.now() - 86400000).toISOString().split('T')[0];
                        
                        let dateText;
                        if (msgDate === today) {
                            dateText = 'Vandaag';
                        } else if (msgDate === yesterday) {
                            dateText = 'Gisteren';
                        } else {
                            const date = new Date(message.datum_verzonden);
                            dateText = date.getDate() + ' ' + 
                                      ['jan', 'feb', 'mrt', 'apr', 'mei', 'jun', 'jul', 'aug', 'sep', 'okt', 'nov', 'dec'][date.getMonth()] + 
                                      ' ' + date.getFullYear();
                        }
                        
                        if (dateText !== currentDate) {
                            currentDate = dateText;
                            const dateElement = document.createElement('div');
                            dateElement.className = 'chat-date';
                            dateElement.textContent = dateText;
                            messagesContainer.appendChild(dateElement);
                        }
                        
                        // Voeg nieuw bericht toe
                        const isSent = parseInt(message.afzender_id) === currentUser;
                        const messageElement = document.createElement('div');
                        messageElement.className = `message ${isSent ? 'sent' : 'received'}`;
                        messageElement.dataset.messageId = message.id;
                        
                        const time = new Date(message.datum_verzonden).toLocaleTimeString('nl-NL', {hour: '2-digit', minute: '2-digit'});
                        
                        messageElement.innerHTML = `
                            <div class="message-bubble">
                                ${message.bericht.replace(/\n/g, '<br>')}
                                <span class="message-time">${time}</span>
                            </div>
                        `;
                        
                        messagesContainer.appendChild(messageElement);
                        lastMessageId = message.id;
                    });
                    
                    // Scroll naar beneden als we nieuwe berichten hebben
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                }
            })
            .catch(error => {
                console.error('Fout bij ophalen nieuwe berichten:', error);
            });
        }
        
        // Controleer op ongelezen berichten voor alle contacten
        function checkUnreadMessages() {
            fetch('../api/chat_ajax.php?action=get_unread_count')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.unread) {
                    // Reset alle badges
                    document.querySelectorAll('.unread-badge').forEach(badge => {
                        badge.style.display = 'none';
                        badge.textContent = '0';
                    });
                    
                    // Update badges met nieuwe aantallen
                    data.unread.forEach(item => {
                        const badge = document.getElementById(`unread-${item.afzender_id}`);
                        if (badge) {
                            badge.textContent = item.count;
                            badge.style.display = 'inline-block';
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Fout bij ophalen ongelezen berichten:', error);
            });
        }
        
        // Filter contacten bij zoeken
        document.getElementById('contact-search')?.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const contactItems = document.querySelectorAll('.chat-item');
            
            contactItems.forEach(item => {
                const contactName = item.querySelector('h4').textContent.toLowerCase();
                if (contactName.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
        
        // Document Ready
        document.addEventListener('DOMContentLoaded', function() {
            // Scroll naar het laatste bericht
            const messagesContainer = document.getElementById('messages-container');
            if (messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
            
            // Start interval voor berichten controle
            if (selectedContact) {
                window.messageCheckInterval = setInterval(checkNewMessages, 3000);
            }
            
            // Controleer ongelezen berichten en herhaal elke 10 seconden
            checkUnreadMessages();
            setInterval(checkUnreadMessages, 10000);
            
            // Event listener voor het verzendknop
            const sendButton = document.getElementById('send-button');
            if (sendButton) {
                sendButton.addEventListener('click', sendMessage);
            }
            
            // Event listener voor Enter-toets in berichtenveld
            const messageInput = document.getElementById('message-input');
            if (messageInput) {
                messageInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault(); // Voorkom dat de vorm wordt verzonden
                        sendMessage();
                    }
                });
            }
        });
    </script>

    <script src="../assets/js/scripts.js"></script>
</body>
</html>
