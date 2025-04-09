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
$sql = "SELECT * FROM gebruikers WHERE id != :user_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$contacten = $stmt->fetchAll();

$pageTitle = "Chat - Flitz Events";
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
                        <button class="icon-button"><i class="fas fa-ellipsis-v"></i></button>
                    </div>
                </div>
                
                <div class="search-container">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Zoek of begin nieuwe chat" id="chat-search">
                    </div>
                </div>
                
                <div class="chats-container">
                    <?php foreach ($contacten as $contact): ?>
                        <div class="chat-item" data-contact-id="<?php echo $contact['id']; ?>">
                            <div class="chat-avatar">
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
                            <div class="chat-details">
                                <div class="chat-header">
                                    <h4><?php echo htmlspecialchars($contact['naam']); ?></h4>
                                    <span class="chat-time">12:34</span>
                                </div>
                                <div class="chat-message-preview">
                                    <p>Hallo! Hoe gaat het met jouw taken?</p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Chat detail paneel -->
            <div class="chat-detail-panel">
                <div class="panel-header">
                    <button class="back-button"><i class="fas fa-arrow-left"></i></button>
                    <div class="chat-avatar">JD</div>
                    <div class="chat-info">
                        <h3>Jan de Vries</h3>
                        <span class="status">Online</span>
                    </div>
                    <div class="header-actions">
                        <button class="icon-button"><i class="fas fa-ellipsis-v"></i></button>
                    </div>
                </div>
                
                <div class="messages-container">
                    <div class="chat-date">Vandaag</div>
                    
                    <div class="message received">
                        <div class="message-bubble">
                            Hallo! Hoe gaat het met jouw taken voor het Noordwijk project?
                            <span class="message-time">10:30</span>
                        </div>
                    </div>
                    
                    <div class="message sent">
                        <div class="message-bubble">
                            Hey Jan! Het gaat goed, ik ben bezig met de voorbereidingen voor het event.
                            <span class="message-time">10:32</span>
                        </div>
                    </div>
                    
                    <div class="message sent">
                        <div class="message-bubble">
                            Ik heb de locatie al bezocht en foto's gemaakt.
                            <span class="message-time">10:32</span>
                        </div>
                    </div>
                    
                    <div class="message received">
                        <div class="message-bubble">
                            Perfect! Kun je die foto's delen in het projectkanaal?
                            <span class="message-time">10:35</span>
                        </div>
                    </div>
                    
                    <div class="message sent">
                        <div class="message-bubble">
                            Zeker, ik zal ze vanmiddag uploaden!
                            <span class="message-time">10:36</span>
                        </div>
                    </div>
                    
                    <div class="message received">
                        <div class="message-bubble">
                            Super, bedankt! Heb je nog vragen over je taken?
                            <span class="message-time">10:38</span>
                        </div>
                    </div>
                </div>
                
                <div class="input-container">
                    <button class="icon-button"><i class="far fa-smile"></i></button>
                    <input type="text" placeholder="Typ een bericht">
                    <button class="send-button"><i class="fas fa-paper-plane"></i></button>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="footer-container">
            <p>&copy; <?php echo date("Y"); ?> Flitz-Events | Alle rechten voorbehouden</p>
        </div>
    </footer>

    <script src="../assets/js/scripts.js"></script>
</body>
</html>
