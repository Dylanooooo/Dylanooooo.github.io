<div class="chat-container">
    <input type="hidden" id="current-user-id" value="<?php echo $_SESSION['user_id']; ?>">
    <div id="chat-toggle" class="chat-toggle">
        <i class="fas fa-comments"></i>
        <span id="chat-notification-badge" class="chat-notification-badge"></span>
    </div>
    <div id="chat-panel" class="chat-panel">
        <div class="chat-header">
            <div class="chat-header-title">Chat</div>
            <div id="chat-close" class="chat-close">&times;</div>
        </div>
        <div class="chat-body">
            <div class="chat-users">
                <div id="chat-users-list"></div>
            </div>
            <div class="chat-content">
                <div id="chat-messages" class="chat-messages"></div>
                <form id="message-form" class="chat-form">
                    <input type="text" id="message-input" placeholder="Type een bericht..." autocomplete="off">
                    <button type="submit"><i class="fas fa-paper-plane"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>
