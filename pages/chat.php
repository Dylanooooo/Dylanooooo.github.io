<?php
session_start();
include('../includes/config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

// Relatief pad voor navigatie
$root_path = "../";
$pageTitle = "Chat - Flitz Events";
$useIcons = true;

$user_id = $_SESSION['user_id'];

// Get all conversations for the current user
$conversations_query = "SELECT 
                        g.id, 
                        g.naam, 
                        g.type,
                        g.laatste_bericht,
                        CASE 
                            WHEN g.type = 'direct' THEN 
                                (SELECT naam FROM gebruikers WHERE id = 
                                    CASE 
                                        WHEN g.gebruiker1_id = :user_id THEN g.gebruiker2_id 
                                        WHEN g.gebruiker2_id = :user_id THEN g.gebruiker1_id
                                        ELSE g.aangemaakt_door
                                    END
                                ) 
                            ELSE g.naam 
                        END as display_naam,
                        gu.ongelezen_berichten
                      FROM gesprekken g
                      JOIN gesprekken_gebruikers gu ON g.id = gu.gesprek_id
                      WHERE gu.gebruiker_id = :user_id
                      ORDER BY g.laatste_bericht DESC";

try {
    // Check if the current user exists
    $user_check_query = "SELECT id FROM gebruikers WHERE id = :user_id";
    $stmt = $pdo->prepare($user_check_query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    if (!$stmt->fetch()) {
        // User doesn't exist in database
        session_destroy();
        header('Location: ../index.php?error=invalid_user');
        exit;
    }
    
    // Get conversations
    $stmt = $pdo->prepare($conversations_query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $conversations = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}

// Haal alle contacten op die nog geen gesprek hebben met de gebruiker
$sql = "
    SELECT id, naam 
    FROM gebruikers 
    WHERE id != :user_id 
    AND id NOT IN (
        SELECT IF(gebruiker1_id = :user_id, gebruiker2_id, gebruiker1_id)
        FROM gesprekken
        WHERE (gebruiker1_id = :user_id OR gebruiker2_id = :user_id)
        AND type = 'direct'
    )
";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$overige_contacten = $stmt->fetchAll();

// Geselecteerd contact/gesprek
$selected_contact_id = isset($_GET['contact_id']) ? intval($_GET['contact_id']) : null;
$selected_contact = null;
$berichten = [];

// Als een contact is geselecteerd, haal zijn/haar gegevens en berichten op
if ($selected_contact_id) {
    // Haal contactgegevens op
    $sql = "SELECT * FROM gebruikers WHERE id = :contact_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':contact_id', $selected_contact_id);
    $stmt->execute();
    $selected_contact = $stmt->fetch();
    
    if ($selected_contact) {
        // Controleer of er al een gesprek bestaat tussen deze gebruikers
        $sql = "
            SELECT id FROM gesprekken 
            WHERE (gebruiker1_id = :user_id AND gebruiker2_id = :contact_id)
            OR (gebruiker1_id = :contact_id AND gebruiker2_id = :user_id)
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':contact_id', $selected_contact_id);
        $stmt->execute();
        $gesprek = $stmt->fetch();
        
        $gesprek_id = null;
        if ($gesprek) {
            $gesprek_id = $gesprek['id'];
        }
        
        // Haal berichten op
        $sql = "
            SELECT b.*, u.naam as afzender_naam
            FROM berichten b
            JOIN gebruikers u ON b.afzender_id = u.id
            WHERE (b.afzender_id = :user_id AND b.ontvanger_id = :contact_id)
            OR (b.afzender_id = :contact_id AND b.ontvanger_id = :user_id)
            ORDER BY b.datum_verzonden ASC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':contact_id', $selected_contact_id);
        $stmt->execute();
        $berichten = $stmt->fetchAll();
        
        // Markeer ongelezen berichten als gelezen
        $sql = "
            UPDATE berichten
            SET gelezen = 1
            WHERE ontvanger_id = :user_id AND afzender_id = :contact_id AND gelezen = 0
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':contact_id', $selected_contact_id);
        $stmt->execute();
    }
}

// AJAX handler voor het versturen van berichten
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_message') {
    $ontvanger_id = intval($_POST['ontvanger_id']);
    $bericht = trim($_POST['bericht']);
    
    if (empty($bericht)) {
        echo json_encode(['success' => false, 'message' => 'Bericht kan niet leeg zijn']);
        exit;
    }
    
    try {
        // First verify that both users exist
        $verify_users_query = "SELECT id FROM gebruikers WHERE id IN (:user_id, :ontvanger_id)";
        $stmt = $pdo->prepare($verify_users_query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':ontvanger_id', $ontvanger_id);
        $stmt->execute();
        $valid_users = $stmt->fetchAll();
        
        if (count($valid_users) != 2) {
            echo json_encode(['success' => false, 'message' => 'Een of beide gebruikers bestaan niet in het systeem']);
            exit;
        }
        
        $pdo->beginTransaction();
        
        // Voeg het bericht toe aan de database
        $column_name = 'inhoud'; // Default column name
        
        // Check if 'inhoud' column exists
        $check_col = $pdo->query("SHOW COLUMNS FROM berichten LIKE 'inhoud'");
        if ($check_col->rowCount() == 0) {
            // Use 'bericht' if 'inhoud' doesn't exist
            $column_name = 'bericht';
        }
        
        $sql = "INSERT INTO berichten (afzender_id, ontvanger_id, $column_name) VALUES (:afzender_id, :ontvanger_id, :content)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':afzender_id', $user_id);
        $stmt->bindParam(':ontvanger_id', $ontvanger_id);
        $stmt->bindParam(':content', $bericht);
        $stmt->execute();
        
        $bericht_id = $pdo->lastInsertId();
        
        // Controleer of er al een gesprek bestaat
        $sql = "
            SELECT id FROM gesprekken 
            WHERE (gebruiker1_id = :user_id AND gebruiker2_id = :ontvanger_id)
            OR (gebruiker1_id = :ontvanger_id AND gebruiker2_id = :user_id)
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':ontvanger_id', $ontvanger_id);
        $stmt->execute();
        $gesprek = $stmt->fetch();
        
        if ($gesprek) {
            // Update bestaand gesprek
            $sql = "UPDATE gesprekken SET laatste_bericht = NOW() WHERE id = :gesprek_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':gesprek_id', $gesprek['id']);
            $stmt->execute();
            
            // Update aantal ongelezen berichten voor ontvanger
            $sql = "UPDATE gesprekken_gebruikers SET ongelezen_berichten = ongelezen_berichten + 1 
                    WHERE gesprek_id = :gesprek_id AND gebruiker_id = :ontvanger_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':gesprek_id', $gesprek['id']);
            $stmt->bindParam(':ontvanger_id', $ontvanger_id);
            $stmt->execute();
        } else {
            // Maak nieuw gesprek aan
            $sql = "INSERT INTO gesprekken (naam, type, gebruiker1_id, gebruiker2_id, aangemaakt_door, laatste_bericht) 
                    VALUES (NULL, 'direct', :user_id, :ontvanger_id, :user_id, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':ontvanger_id', $ontvanger_id);
            $stmt->execute();
            
            $gesprek_id = $pdo->lastInsertId();
            
            // Voeg gebruikers toe aan het gesprek
            $sql = "INSERT INTO gesprekken_gebruikers (gesprek_id, gebruiker_id, ongelezen_berichten) VALUES (:gesprek_id, :user_id, 0)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':gesprek_id', $gesprek_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            $sql = "INSERT INTO gesprekken_gebruikers (gesprek_id, gebruiker_id, ongelezen_berichten) VALUES (:gesprek_id, :ontvanger_id, 1)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':gesprek_id', $gesprek_id);
            $stmt->bindParam(':ontvanger_id', $ontvanger_id);
            $stmt->execute();
        }
        
        $pdo->commit();
        
        // Haal het nieuwe bericht op met afzendernaam
        $sql = "SELECT b.*, u.naam as afzender_naam FROM berichten b JOIN gebruikers u ON b.afzender_id = u.id WHERE b.id = :bericht_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':bericht_id', $bericht_id);
        $stmt->execute();
        $nieuw_bericht = $stmt->fetch();
        
        echo json_encode(['success' => true, 'message' => 'Bericht verstuurd', 'data' => $nieuw_bericht]);
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Chat error: " . $e->getMessage() . " - User ID: $user_id, Receiver ID: $ontvanger_id");
        echo json_encode(['success' => false, 'message' => 'Er is een fout opgetreden: ' . $e->getMessage()]);
        exit;
    }
}

// Als het een AJAX-verzoek is om berichten op te halen, verwerk dat dan
if (isset($_GET['action']) && $_GET['action'] === 'get_messages' && isset($_GET['contact_id'])) {
    $contact_id = intval($_GET['contact_id']);
    $last_id = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;
    
    $sql = "
        SELECT b.*, u.naam as afzender_naam
        FROM berichten b
        JOIN gebruikers u ON b.afzender_id = u.id
        WHERE ((b.afzender_id = :user_id AND b.ontvanger_id = :contact_id)
        OR (b.afzender_id = :contact_id AND b.ontvanger_id = :user_id))
        AND b.id > :last_id
        ORDER BY b.datum_verzonden ASC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':contact_id', $contact_id);
    $stmt->bindParam(':last_id', $last_id);
    $stmt->execute();
    $nieuwe_berichten = $stmt->fetchAll();
    
    // Markeer ongelezen berichten als gelezen
    $sql = "
        UPDATE berichten
        SET gelezen = 1
        WHERE ontvanger_id = :user_id AND afzender_id = :contact_id AND gelezen = 0
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':contact_id', $contact_id);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'berichten' => $nieuwe_berichten]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Inclusie van de consistente navigatie component -->
    <?php include('../includes/navigation.php'); ?>

    <section id="whatsapp-chat">
        <div class="whatsapp-container">
            <!-- Chat lijst paneel -->
            <div class="chat-list-panel">
                <div class="panel-header">
                    <h2>Chats</h2>
                    <div class="header-actions">
                        <button class="icon-button" id="nieuwe-chat-btn"><i class="fas fa-edit"></i></button>
                        <button class="icon-button"><i class="fas fa-ellipsis-v"></i></button>
                    </div>
                </div>
                
                <div class="search-container">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Zoek chat" id="chat-search">
                    </div>
                </div>
                
                <div class="chats-container">
                    <?php if (empty($conversations) && empty($overige_contacten)): ?>
                        <div class="no-chats">
                            <p>Geen gesprekken gevonden.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($conversations as $gesprek): ?>
                            <div class="chat-item <?php echo ($selected_contact_id == $gesprek['id']) ? 'active' : ''; ?>" 
                                 data-contact-id="<?php echo $gesprek['id']; ?>">
                                <div class="chat-avatar">
                                    <?php
                                    // Maak initialen van de naam
                                    $naam_delen = explode(' ', $gesprek['display_naam']);
                                    $initialen = '';
                                    foreach ($naam_delen as $deel) {
                                        $initialen .= strtoupper(substr($deel, 0, 1));
                                    }
                                    echo $initialen;
                                    ?>
                                </div>
                                <div class="chat-details">
                                    <div class="chat-header">
                                        <h4><?php echo htmlspecialchars($gesprek['display_naam']); ?></h4>
                                        <span class="chat-time"><?php echo $gesprek['laatste_bericht'] ? date('H:i', strtotime($gesprek['laatste_bericht'])) : ''; ?></span>
                                    </div>
                                    <div class="chat-message-preview">
                                        <?php if ($gesprek['laatste_bericht']): ?>
                                            <p<?php echo ($gesprek['ongelezen_berichten'] > 0) ? ' class="unread"' : ''; ?>>
                                                Laatste bericht
                                            </p>
                                        <?php else: ?>
                                            <p>Geen berichten</p>
                                        <?php endif; ?>
                                        <?php if ($gesprek['ongelezen_berichten'] > 0): ?>
                                            <span class="unread-badge"><?php echo $gesprek['ongelezen_berichten']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Chat detail paneel -->
            <div class="chat-detail-panel <?php echo $selected_contact ? 'active' : ''; ?>">
                <?php if ($selected_contact): ?>
                    <div class="panel-header">
                        <button class="back-button"><i class="fas fa-arrow-left"></i></button>
                        <div class="chat-avatar">
                            <?php
                            // Maak initialen van de naam
                            $naam_delen = explode(' ', $selected_contact['naam']);
                            $initialen = '';
                            foreach ($naam_delen as $deel) {
                                $initialen .= strtoupper(substr($deel, 0, 1));
                            }
                            echo $initialen;
                            ?>
                        </div>
                        <div class="chat-info">
                            <h3><?php echo htmlspecialchars($selected_contact['naam']); ?></h3>
                            <span class="status">Online</span>
                        </div>
                        <div class="header-actions">
                            <button class="icon-button"><i class="fas fa-ellipsis-v"></i></button>
                        </div>
                    </div>
                    
                    <div class="messages-container" id="messages-container" data-contact-id="<?php echo $selected_contact_id; ?>">
                        <?php if (empty($berichten)): ?>
                            <div class="chat-empty-state">
                                <div class="empty-state-icon">
                                    <i class="far fa-comments"></i>
                                </div>
                                <p>Begin een gesprek met <?php echo htmlspecialchars($selected_contact['naam']); ?></p>
                            </div>
                        <?php else: ?>
                            <?php 
                            $current_date = null;
                            foreach ($berichten as $bericht): 
                                $bericht_date = date('Y-m-d', strtotime($bericht['datum_verzonden']));
                                
                                // Date display logic has been fixed to show dates only once per day
                                if ($current_date != $bericht_date) {
                                    $current_date = $bericht_date;
                                    $date_label = date('d-m-Y', strtotime($bericht['datum_verzonden']));
                                    if ($bericht_date == date('Y-m-d')) {
                                        $date_label = 'Vandaag';
                                    } elseif ($bericht_date == date('Y-m-d', strtotime('-1 day'))) {
                                        $date_label = 'Gisteren';
                                    }
                                    echo '<div class="chat-date">' . $date_label . '</div>';
                                }
                            ?>
                                <div class="message <?php echo ($bericht['afzender_id'] == $user_id) ? 'sent' : 'received'; ?>" data-message-id="<?php echo $bericht['id']; ?>">
                                    <div class="message-bubble">
                                        <?php 
                                        // Try to use 'inhoud' first, then fall back to 'bericht'
                                        $message_content = isset($bericht['inhoud']) ? $bericht['inhoud'] : (isset($bericht['bericht']) ? $bericht['bericht'] : '');
                                        echo nl2br(htmlspecialchars($message_content)); 
                                        ?>
                                        <span class="message-time"><?php echo date('H:i', strtotime($bericht['datum_verzonden'])); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="input-container">
                        <button class="icon-button"><i class="far fa-smile"></i></button>
                        <textarea id="message-input" placeholder="Typ een bericht"></textarea>
                        <button class="send-button" id="send-button" data-contact-id="<?php echo $selected_contact_id; ?>">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                <?php else: ?>
                    <div class="chat-welcome">
                        <div class="welcome-icon">
                            <i class="far fa-comments"></i>
                        </div>
                        <h2>Welkom bij de chat</h2>
                        <p>Selecteer een contact om een gesprek te starten</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Modal voor nieuwe chat -->
    <div id="nieuwe-chat-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Nieuw gesprek</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="search-container">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Zoek contact" id="contact-search">
                    </div>
                </div>
                <div class="contacts-list">
                    <?php foreach ($overige_contacten as $contact): ?>
                        <div class="contact-item" data-contact-id="<?php echo $contact['id']; ?>">
                            <div class="contact-avatar">
                                <?php
                                // Maak initialen van de naam
                                $naam_delen = explode(' ', $contact['naam']);
                                $initialen = '';
                                foreach ($naam_delen as $deel) {
                                    $initialen .= strtoupper(substr($deel, 0, 1));
                                }
                                echo $initialen;
                                ?>
                            </div>
                            <h4><?php echo htmlspecialchars($contact['naam']); ?></h4>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer-container">
            <p>&copy; <?php echo date("Y"); ?> Flitz-Events | Alle rechten voorbehouden</p>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const messagesContainer = document.getElementById('messages-container');
        const messageInput = document.getElementById('message-input');
        const sendButton = document.getElementById('send-button');
        const chatItems = document.querySelectorAll('.chat-item');
        const backButton = document.querySelector('.back-button');
        const chatDetailPanel = document.querySelector('.chat-detail-panel');
        const nieuweChatBtn = document.getElementById('nieuwe-chat-btn');
        const nieuweChatModal = document.getElementById('nieuwe-chat-modal');
        const closeModal = document.querySelector('.close-modal');
        const contactItems = document.querySelectorAll('.contact-item');
        let lastMessageId = 0;

        // Scroll naar beneden in de berichtencontainer
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
            lastMessageId = 0;
            // Haal het laatste berichtID op
            const messages = messagesContainer.querySelectorAll('.message');
            if (messages.length > 0) {
                const lastMessage = messages[messages.length - 1];
                lastMessageId = lastMessage.dataset.messageId || 0;
            }
        }

        // Stuur bericht versturen
        if (sendButton) {
            sendButton.addEventListener('click', function() {
                sendMessage();
            });
        }

        if (messageInput) {
            messageInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });
        }

        function sendMessage() {
            if (!messageInput || !sendButton) return;
            const message = messageInput.value.trim();
            const contactId = sendButton.dataset.contactId;
            if (message === '') return;
            
            // Show sending indicator
            const tempId = 'temp-' + Date.now();
            const tempMessage = document.createElement('div');
            tempMessage.className = 'message sent temp-message';
            tempMessage.id = tempId;
            tempMessage.innerHTML = `
                <div class="message-bubble">
                    ${message.replace(/\n/g, '<br>')}
                    <span class="message-time">Verzenden...</span>
                </div>
            `;
            messagesContainer.appendChild(tempMessage);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
            
            // Stuur bericht naar de server
            const formData = new FormData();
            formData.append('action', 'send_message');
            formData.append('ontvanger_id', contactId);
            formData.append('bericht', message);
            
            fetch('chat.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Remove temp message
                const tempMsg = document.getElementById(tempId);
                if (tempMsg) tempMsg.remove();
                
                if (data.success) {
                    messageInput.value = '';
                    // Display the sent message with server data
                    const messageData = [{
                        id: data.data.id,
                        afzender_id: <?php echo $user_id; ?>,
                        inhoud: data.data.inhoud || data.data.bericht, // Handle both column names
                        bericht: data.data.bericht || data.data.inhoud, // Handle both column names
                        datum_verzonden: data.data.datum_verzonden,
                        afzender_naam: '<?php echo $_SESSION['naam']; ?>'
                    }];
                    displayNewMessages(messageData);
                    
                    // Update lastMessageId to prevent duplicate display
                    if (parseInt(data.data.id) > lastMessageId) {
                        lastMessageId = parseInt(data.data.id);
                    }
                } else {
                    console.error('Fout bij versturen bericht:', data.message);
                    alert('Fout bij versturen bericht: ' + data.message);
                }
            })
            .catch(error => {
                // Remove temp message
                const tempMsg = document.getElementById(tempId);
                if (tempMsg) tempMsg.remove();
                
                console.error('Fout bij versturen bericht:', error);
                alert('Er is een fout opgetreden bij het versturen van het bericht. Probeer het later opnieuw.');
            });
        }

        // Regelmatig controleren op nieuwe berichten
        let messageCheckInterval;
        function startMessageChecking() {
            if (messagesContainer) {
                const contactId = messagesContainer.dataset.contactId;
                if (contactId) {
                    // Check direct en dan elke 3 seconden
                    checkForNewMessages();
                    messageCheckInterval = setInterval(checkForNewMessages, 3000);
                }
            }
        }

        function stopMessageChecking() {
            clearInterval(messageCheckInterval);
        }

        function checkForNewMessages() {
            if (!messagesContainer) return;
            const contactId = messagesContainer.dataset.contactId;
            
            if (!contactId) return;
            
            // Haal nieuwe berichten op
            fetch(`chat.php?action=get_messages&contact_id=${contactId}&last_id=${lastMessageId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.berichten.length > 0) {
                    displayNewMessages(data.berichten);
                }
            })
            .catch(error => {
                console.error('Fout bij ophalen berichten:', error);
            });
        }

        function displayNewMessages(berichten) {
            let currentDate = null;
            const currentUserId = <?php echo $user_id; ?>;
            
            // Get the last displayed date in the container
            const dateDivs = messagesContainer.querySelectorAll('.chat-date');
            if (dateDivs.length > 0) {
                const lastDateText = dateDivs[dateDivs.length - 1].textContent;
                
                if (lastDateText === 'Vandaag') {
                    currentDate = new Date().toISOString().split('T')[0];
                } else if (lastDateText === 'Gisteren') {
                    currentDate = new Date(Date.now() - 86400000).toISOString().split('T')[0];
                } else {
                    // Parse Dutch date format (dd-mm-yyyy)
                    const dateParts = lastDateText.split('-');
                    if (dateParts.length === 3) {
                        currentDate = `${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`;
                    }
                }
            }
            
            berichten.forEach(bericht => {
                const berichtDate = new Date(bericht.datum_verzonden).toISOString().split('T')[0];
                
                // Voeg datumscheiding toe indien nodig
                if (currentDate !== berichtDate) {
                    currentDate = berichtDate;
                    let dateLabel = new Date(bericht.datum_verzonden).toLocaleDateString('nl-NL');
                    
                    if (berichtDate === new Date().toISOString().split('T')[0]) {
                        dateLabel = 'Vandaag';
                    } else if (berichtDate === new Date(Date.now() - 86400000).toISOString().split('T')[0]) {
                        dateLabel = 'Gisteren';
                    }
                    
                    const dateDiv = document.createElement('div');
                    dateDiv.className = 'chat-date';
                    dateDiv.textContent = dateLabel;
                    messagesContainer.appendChild(dateDiv);
                }
                
                // Maak berichtbel aan
                const messageDiv = document.createElement('div');
                messageDiv.className = `message ${bericht.afzender_id == currentUserId ? 'sent' : 'received'}`;
                messageDiv.dataset.messageId = bericht.id;
                
                const bubbleDiv = document.createElement('div');
                bubbleDiv.className = 'message-bubble';
                
                // Handle both column names
                const messageContent = document.createElement('span');
                const messageText = bericht.inhoud || bericht.bericht;
                messageContent.innerHTML = messageText.replace(/\n/g, '<br>');
                
                const timeSpan = document.createElement('span');
                timeSpan.className = 'message-time';
                timeSpan.textContent = new Date(bericht.datum_verzonden).toLocaleTimeString('nl-NL', {hour: '2-digit', minute:'2-digit'});
                
                bubbleDiv.appendChild(messageContent);
                bubbleDiv.appendChild(timeSpan);
                messageDiv.appendChild(bubbleDiv);
                messagesContainer.appendChild(messageDiv);
                
                // Update laatst ontvangen bericht ID
                if (parseInt(bericht.id) > lastMessageId) {
                    lastMessageId = parseInt(bericht.id);
                }
            });
            
            // Scroll naar beneden
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        // Chat items klikbaar maken
        chatItems.forEach(item => {
            item.addEventListener('click', function() {
                const contactId = this.dataset.contactId;
                window.location.href = `chat.php?contact_id=${contactId}`;
            });
        });
        
        // Terug knop functionaliteit
        if (backButton) {
            backButton.addEventListener('click', function() {
                window.location.href = 'chat.php';
            });
        }
        
        // Modal voor nieuwe chat
        if (nieuweChatBtn) {
            nieuweChatBtn.addEventListener('click', function() {
                nieuweChatModal.style.display = 'block';
            });
        }
        
        if (closeModal) {
            closeModal.addEventListener('click', function() {
                nieuweChatModal.style.display = 'none';
            });
        }
        
        // Sluit modal als er buiten wordt geklikt
        window.addEventListener('click', function(event) {
            if (event.target === nieuweChatModal) {
                nieuweChatModal.style.display = 'none';
            }
        });
        
        // Contact items klikbaar maken
        contactItems.forEach(item => {
            item.addEventListener('click', function() {
                const contactId = this.dataset.contactId;
                window.location.href = `chat.php?contact_id=${contactId}`;
                nieuweChatModal.style.display = 'none';
            });
        });
        
        // Zoekfunctionaliteit
        const chatSearch = document.getElementById('chat-search');
        if (chatSearch) {
            chatSearch.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                
                chatItems.forEach(item => {
                    const contactName = item.querySelector('h4').textContent.toLowerCase();
                    if (contactName.includes(searchTerm)) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        }
        
        const contactSearch = document.getElementById('contact-search');
        if (contactSearch) {
            contactSearch.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                
                contactItems.forEach(item => {
                    const contactName = item.querySelector('h4').textContent.toLowerCase();
                    if (contactName.includes(searchTerm)) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        }
        
        // Start message checking als er een contact is geselecteerd
        if (messagesContainer && messagesContainer.dataset.contactId) {
            startMessageChecking();
        }
        
        // Cleanup bij verlaten van de pagina
        window.addEventListener('beforeunload', function() {
            stopMessageChecking();
        });
    });
    </script>
</body>
</html>
