<?php
session_start();
include('../includes/config.php');

// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

// Relatief pad voor navigatie
$root_path = "../";

// Gebruikersgegevens voor de chat
$user_id = $_SESSION['user_id'];

// Haal alle gesprekken op voor deze gebruiker
$sql = "
    SELECT 
        g.id as gesprek_id,
        IF(g.gebruiker1_id = :user_id, g.gebruiker2_id, g.gebruiker1_id) as contact_id,
        u.naam as contact_naam,
        b.bericht as laatste_bericht,
        b.afzender_id as laatste_afzender_id,
        b.datum_verzonden,
        (SELECT COUNT(*) FROM berichten WHERE ontvanger_id = :user_id AND afzender_id = contact_id AND gelezen = 0) as ongelezen_aantal
    FROM 
        gesprekken g
    JOIN 
        gebruikers u ON (u.id = IF(g.gebruiker1_id = :user_id, g.gebruiker2_id, g.gebruiker1_id))
    LEFT JOIN 
        berichten b ON g.laatste_bericht_id = b.id
    WHERE 
        g.gebruiker1_id = :user_id OR g.gebruiker2_id = :user_id
    ORDER BY 
        g.laatste_activiteit DESC
";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$gesprekken = $stmt->fetchAll();

// Haal alle contacten op die nog geen gesprek hebben met de gebruiker
$sql = "
    SELECT id, naam 
    FROM gebruikers 
    WHERE id != :user_id 
    AND id NOT IN (
        SELECT IF(gebruiker1_id = :user_id, gebruiker2_id, gebruiker1_id)
        FROM gesprekken
        WHERE gebruiker1_id = :user_id OR gebruiker2_id = :user_id
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

$pageTitle = "Chat - Flitz Events";

// AJAX handler voor het versturen van berichten
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_message') {
    $ontvanger_id = intval($_POST['ontvanger_id']);
    $bericht = trim($_POST['bericht']);
    
    if (empty($bericht)) {
        echo json_encode(['success' => false, 'message' => 'Bericht kan niet leeg zijn']);
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Voeg het bericht toe aan de database
        $sql = "INSERT INTO berichten (afzender_id, ontvanger_id, bericht) VALUES (:afzender_id, :ontvanger_id, :bericht)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':afzender_id', $user_id);
        $stmt->bindParam(':ontvanger_id', $ontvanger_id);
        $stmt->bindParam(':bericht', $bericht);
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
            $sql = "UPDATE gesprekken SET laatste_bericht_id = :bericht_id, laatste_activiteit = CURRENT_TIMESTAMP WHERE id = :gesprek_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':bericht_id', $bericht_id);
            $stmt->bindParam(':gesprek_id', $gesprek['id']);
            $stmt->execute();
        } else {
            // Maak nieuw gesprek aan
            $sql = "INSERT INTO gesprekken (gebruiker1_id, gebruiker2_id, laatste_bericht_id) VALUES (:user_id, :ontvanger_id, :bericht_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':ontvanger_id', $ontvanger_id);
            $stmt->bindParam(':bericht_id', $bericht_id);
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
                    <?php if (empty($gesprekken) && empty($overige_contacten)): ?>
                        <div class="no-chats">
                            <p>Geen gesprekken gevonden.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($gesprekken as $gesprek): ?>
                            <div class="chat-item <?php echo ($selected_contact_id == $gesprek['contact_id']) ? 'active' : ''; ?>" 
                                 data-contact-id="<?php echo $gesprek['contact_id']; ?>">
                                <div class="chat-avatar">
                                    <?php
                                    // Maak initialen van de naam
                                    $naam_delen = explode(' ', $gesprek['contact_naam']);
                                    $initialen = '';
                                    foreach ($naam_delen as $deel) {
                                        $initialen .= strtoupper(substr($deel, 0, 1));
                                    }
                                    echo $initialen;
                                    ?>
                                </div>
                                <div class="chat-details">
                                    <div class="chat-header">
                                        <h4><?php echo htmlspecialchars($gesprek['contact_naam']); ?></h4>
                                        <span class="chat-time"><?php echo date('H:i', strtotime($gesprek['datum_verzonden'])); ?></span>
                                    </div>
                                    <div class="chat-message-preview">
                                        <?php if ($gesprek['laatste_bericht']): ?>
                                            <p<?php echo ($gesprek['ongelezen_aantal'] > 0 && $gesprek['laatste_afzender_id'] != $user_id) ? ' class="unread"' : ''; ?>>
                                                <?php echo ($gesprek['laatste_afzender_id'] == $user_id ? 'Jij: ' : ''); ?>
                                                <?php echo htmlspecialchars(substr($gesprek['laatste_bericht'], 0, 30)) . (strlen($gesprek['laatste_bericht']) > 30 ? '...' : ''); ?>
                                            </p>
                                        <?php else: ?>
                                            <p>Geen berichten</p>
                                        <?php endif; ?>
                                        <?php if ($gesprek['ongelezen_aantal'] > 0): ?>
                                            <span class="unread-badge"><?php echo $gesprek['ongelezen_aantal']; ?></span>
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
                                
                                // Alleen een datum weergeven als deze anders is dan de vorige
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
                                        <?php echo nl2br(htmlspecialchars($bericht['bericht'])); ?>
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
                if (data.success) {
                    messageInput.value = '';
                    checkForNewMessages();
                } else {
                    console.error('Fout bij versturen bericht:', data.message);
                }
            })
            .catch(error => {
                console.error('Fout bij versturen bericht:', error);
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
                
                const messageContent = document.createElement('span');
                messageContent.innerHTML = bericht.bericht.replace(/\n/g, '<br>');
                
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
        if (selected_contact_id) {
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
