// Mobile menu toggle
const mobileMenu = document.getElementById('mobile-menu');
const navList = document.querySelector('.nav-list');
if (mobileMenu) {
    mobileMenu.addEventListener('click', () => {
        navList.classList.toggle('active');
    });
}

// Logout functionality
const logoutBtn = document.getElementById('logout-btn');
if (logoutBtn) {
    logoutBtn.addEventListener('click', () => {
        // Clear any auth tokens/session data
        localStorage.removeItem('authToken');
        localStorage.removeItem('userRole');
        localStorage.removeItem('userName');

        // Redirect to login page (PHP version)
        window.location.href = '../index.php';
    });
}

// Display user name from PHP session
const userNameElement = document.getElementById('user-name');
if (userNameElement && !userNameElement.textContent.trim()) {
    const userName = localStorage.getItem('userName') || 'Gebruiker';
    userNameElement.textContent = userName;
}

// SVG Meter initialisatie - de enige versie die we nodig hebben
function initializeSvgMeter() {
    console.log("Initialisatie van SVG meter...");

    const meter = document.querySelector('.svg-meter');
    if (!meter) {
        console.log("Geen SVG meter element gevonden");
        return;
    }

    // Haal het percentage op
    const percentage = parseInt(meter.getAttribute('data-percentage')) || 0;
    console.log(`Percentage: ${percentage}%`);

    // Update de voortgangsboog
    const progress = meter.querySelector('.meter-fg');
    if (progress) {
        // Totale lengte van de boog (ongeveer 283 eenheden voor een halve cirkel)
        const totalLength = 283;

        // Bereken hoeveel van de boog zichtbaar moet zijn
        // 0% = volledig verborgen (offset = 283), 100% = volledig zichtbaar (offset = 0)
        const dashOffset = totalLength * (1 - percentage / 100);

        // Pas het dashoffset toe met een korte vertraging voor een animatie-effect
        setTimeout(() => {
            progress.style.strokeDasharray = totalLength;
            progress.style.strokeDashoffset = dashOffset;
        }, 100);

        console.log(`SVG boog ingesteld met offset ${dashOffset}`);
    }

    // Update de naald
    const needle = meter.querySelector('.meter-needle');
    if (needle) {
        // Bereken de rotatie: 0% = -90deg (links), 100% = 90deg (rechts)
        const rotation = (percentage / 100 * 180) - 90;

        setTimeout(() => {
            needle.style.transform = `translateX(-50%) rotate(${rotation}deg)`;
            console.log(`Naald geroteerd naar ${rotation}deg`);
        }, 300);
    }

    // Update het percentage tekst
    const center = meter.querySelector('.meter-center');
    if (center) {
        center.textContent = `${percentage}%`;
    }
}

// Chat functionality
function initChatInterface() {
    console.log('Initialiseren van chat interface...');
    const chatToggle = document.getElementById('chat-toggle');
    const chatPanel = document.getElementById('chat-panel');
    const chatClose = document.getElementById('chat-close');
    const usersList = document.getElementById('chat-users-list');
    const chatMessages = document.getElementById('chat-messages');
    const messageForm = document.getElementById('message-form');
    const messageInput = document.getElementById('message-input');

    // Get the current user ID from a hidden element or localStorage
    const currentUserIdElement = document.getElementById('current-user-id');
    const currentUserId = currentUserIdElement ? currentUserIdElement.value :
        localStorage.getItem('userId');

    console.log('Current user ID:', currentUserId);

    if (!currentUserId) {
        console.error('Current user ID not found. Chat functionality may not work correctly.');
    }

    if (chatToggle) {
        chatToggle.addEventListener('click', function() {
            fetchChatUsers();
            chatPanel.classList.toggle('active');
        });
    }

    if (chatClose) {
        chatClose.addEventListener('click', function() {
            chatPanel.classList.remove('active');
        });
    }

    function fetchChatUsers() {
        fetch('../api/chat.php?action=get_users')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderUsersList(data.users);
                    updateUnreadCounts(data.unread_counts);
                } else {
                    console.error('Failed to fetch users:', data.message);
                }
            })
            .catch(error => console.error('Error fetching users:', error));
    }
    
    function renderUsersList(users) {
        if (!usersList) return;

        usersList.innerHTML = ''; // Clear existing content

        if (users.length === 0) {
            usersList.innerHTML = '<div class="no-users">Geen gebruikers beschikbaar</div>';
            return;
        }

        users.forEach(user => {
            const userItem = document.createElement('div');
            userItem.className = 'chat-user';
            userItem.dataset.userId = user.id;
            userItem.dataset.userName = user.naam; // Store the user name for reference

            const unreadBadge = user.unread_count > 0 ?
                `<span class="unread-badge">${user.unread_count}</span>` : '';

            userItem.innerHTML = `
                <div class="user-name">${user.naam}</div>
                <div class="user-role">${user.rol}</div>
                ${unreadBadge}
            `;

            userItem.addEventListener('click', function() {
                document.querySelectorAll('.chat-user').forEach(el => el.classList.remove('active'));
                this.classList.add('active');

                // Update chat header to show who you're talking to
                const chatHeader = document.querySelector('.chat-header-title');
                if (chatHeader) {
                    chatHeader.textContent = `Chat met ${this.dataset.userName}`;
                }

                loadChatMessages(this.dataset.userId);
            });

            usersList.appendChild(userItem);
        });
    }
    
    function updateUnreadCounts(unreadCounts) {
        // Update the main notification badge
        const notificationBadge = document.getElementById('chat-notification-badge');
        const totalUnread = Object.values(unreadCounts).reduce((sum, count) => sum + count, 0);

        if (notificationBadge) {
            if (totalUnread > 0) {
                notificationBadge.textContent = totalUnread;
                notificationBadge.style.display = 'flex';
            } else {
                notificationBadge.style.display = 'none';
            }
        }

        // Update individual user badges
        for (const userId in unreadCounts) {
            const userItem = document.querySelector(`.chat-user[data-user-id="${userId}"]`);
            if (userItem) {
                let badge = userItem.querySelector('.unread-badge');
                if (unreadCounts[userId] > 0) {
                    if (!badge) {
                        badge = document.createElement('span');
                        badge.className = 'unread-badge';
                        userItem.appendChild(badge);
                    }
                    badge.textContent = unreadCounts[userId];
                } else if (badge) {
                    badge.remove();
                }
            }
        }
    }

    function loadChatMessages(userId) {
        if (!chatMessages || !messageForm) return;

        // Store the selected user ID for reference
        messageForm.dataset.receiverId = userId;

        chatMessages.innerHTML = '<div class="loading-messages">Berichten laden...</div>';

        fetch(`../api/chat.php?action=get_messages&user_id=${userId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderChatMessages(data.messages, userId);

                    // Mark messages as read
                    fetch(`../api/chat.php?action=mark_read&user_id=${userId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Update unread counts
                                const userBadge =
                                    document.querySelector(`.chat-user[data-user-id="${userId}"] .unread-badge`);
                                if (userBadge) userBadge.remove();

                                // Also update total count
                                fetchChatUsers();
                            }
                        })
                        .catch(error => {
                            console.error('Error marking messages as read:', error);
                        });
                } else {
                    chatMessages.innerHTML = `<div class="error-message">${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error loading messages:', error);
                chatMessages.innerHTML = '<div class="error-message">Fout bij het laden van berichten</div>';
            });
    }

    function renderChatMessages(messages, userId) {
        if (!chatMessages) return;

        chatMessages.innerHTML = '';

        if (messages.length === 0) {
            chatMessages.innerHTML = '<div class="no-messages">Geen berichten. Stuur een bericht om de conversatie te starten!</div>';
            return;
        }

        let currentDate = '';

        messages.forEach(message => {
            // Debug output
            console.log("Message:", message);
            console.log(`Message from ${message.afzender_id}, to ${message.ontvanger_id}, current user viewing: ${userId}`);

            // Add date separator if date changes
            const messageDate = new Date(message.timestamp).toLocaleDateString('nl-NL');
            if (messageDate !== currentDate) {
                currentDate = messageDate;
                const dateSeparator = document.createElement('div');
                dateSeparator.className = 'date-separator';
                dateSeparator.textContent = currentDate;
                chatMessages.appendChild(dateSeparator);
            }

            // Create the message element
            const messageItem = document.createElement('div');

            // Check if message was sent by current user
            const isFromMe = parseInt(message.afzender_id) === parseInt(currentUserId);
            messageItem.className = `message ${isFromMe ? 'sent' : 'received'}`;

            console.log(`Message direction: ${isFromMe ? 'sent by me' : 'received from other user'}`);

            const time = new Date(message.timestamp).toLocaleTimeString('nl-NL', {
                hour: '2-digit',
                minute: '2-digit'
            });

            messageItem.innerHTML = `
                <div class="message-content">${message.bericht}</div>
                <div class="message-time">${time}</div>
            `;

            chatMessages.appendChild(messageItem);
        });

        // Scroll to bottom
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Send message
    if (messageForm) {
        messageForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const receiverId = this.dataset.receiverId;
            const message = messageInput.value.trim();

            if (!receiverId || !message) return;

            const formData = new FormData();
            formData.append('action', 'send_message');
            formData.append('receiver_id', receiverId);
            formData.append('message', message);

            fetch('../api/chat.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        messageInput.value = '';
                        loadChatMessages(receiverId);
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error sending message:', error);
                    alert('Fout bij het versturen van het bericht');
                });
        });
    }

    // Initial load of users with auto-open first unread chat
    function initialLoad() {
        fetch('../api/chat.php?action=get_users')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderUsersList(data.users);
                    updateUnreadCounts(data.unread_counts);

                    // Find the first user with unread messages
                    const firstUserWithUnread = data.users.find(user => user.unread_count > 0);

                    if (firstUserWithUnread) {
                        console.log('Auto-opening chat with unread messages:', firstUserWithUnread.naam);

                        // Find and click the user's chat element
                        setTimeout(() => {
                            const userElement = document.querySelector(`.chat-user[data-user-id="${firstUserWithUnread.id}"]`);
                            if (userElement) {
                                userElement.click();
                            }
                        }, 100);
                    }
                } else {
                    console.error('Failed to fetch users:', data.message);
                }
            })
            .catch(error => console.error('Error fetching users:', error));
    }

    // Replace fetchChatUsers() with initialLoad() for first load
    initialLoad();

    // Periodic reload of chat data
    setInterval(function() {
        if (chatPanel && chatPanel.classList.contains('active')) {
            fetchChatUsers();

            // If a user is selected, reload their messages
            const activeUser = document.querySelector('.chat-user.active');
            if (activeUser) {
                loadChatMessages(activeUser.dataset.userId);
            }
        }
    }, 10000); // Check every 10 seconds
}

// DOMContentLoaded event
document.addEventListener('DOMContentLoaded', function() {
    // Higlichten van het huidige navigatie-item op basis van de URL
    function setActiveNavItem() {
        const currentPage = window.location.pathname.split('/').pop();
        const navLinks = document.querySelectorAll('.nav-list a');

        // Verwijder active class van alle items
        navLinks.forEach(link => link.classList.remove('active'));

        // Voeg active class toe aan het juiste item
        navLinks.forEach(link => {
            const linkPage = link.getAttribute('href');
            if (currentPage === linkPage ||
                (currentPage === '' && linkPage === 'index.php') ||
                (currentPage === 'dashboard.php' && linkPage === 'dashboard.php')) {
                link.classList.add('active');
            }
        });

        console.log('Active nav item set based on current page:', currentPage);
    }

    // Voer de functie direct uit
    setActiveNavItem();

    // Initialiseer de SVG meter
    setTimeout(initializeSvgMeter, 100);

    // Initialiseer de chat interface
    initChatInterface();

    // Voeg dit toe aan het einde van je bestaande DOMContentLoaded handler
    initializeStickyNavbar();

    // Initialiseer WhatsApp chat als we op de chat pagina zijn
    if (document.getElementById('whatsapp-chat')) {
        initializeWhatsappChat();
    }
    
    // Project edit form handling
    const editProjectForm = document.getElementById('edit-project-form');
    if (editProjectForm) {
        editProjectForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.href = 'project-detail.php?id=' + formData.get('id');
                    } else {
                        alert(data.message || 'Er is een fout opgetreden bij het bijwerken van het project.');
                    }
                })
                .catch(error => {
                    console.error('Fout:', error);
                    alert('Er is een fout opgetreden bij het bijwerken van het project. Probeer het later opnieuw.');
                });
        });
    }
});

// Verbeterde Sticky navbar functionaliteit
function initializeStickyNavbar() {
    console.log("Initialiseren van sticky navbar...");
    const navbar = document.querySelector('nav');
    const bannerWrapper = document.querySelector('.intro-banner-wrapper');
    const header = document.querySelector('header');

    if (!navbar || !header) {
        console.log("Ontbrekende elementen voor sticky navbar:", {
            navbar: !!navbar,
            bannerWrapper: !!bannerWrapper,
            header: !!header
        });
        return;
    }

    // Create or get nav spacer
    let navSpacer = document.querySelector('.nav-spacer');
    if (!navSpacer) {
        navSpacer = document.createElement('div');
        navSpacer.className = 'nav-spacer';
        navbar.after(navSpacer);
        console.log("Nav spacer dynamisch toegevoegd");
    }

    const navHeight = navbar.offsetHeight;
    navSpacer.style.height = navHeight + 'px';

    // Calculate trigger position
    const headerHeight = header.offsetHeight;
    const bannerHeight = bannerWrapper ? bannerWrapper.offsetHeight : 0;
    const triggerPosition = headerHeight + bannerHeight - 10; // 10px eerder triggeren

    function checkNavbarPosition() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        if (scrollTop >= triggerPosition) {
            navbar.classList.add('sticky');
            navSpacer.classList.add('active');
        } else {
            navbar.classList.remove('sticky');
            navSpacer.classList.remove('active');
        }
    }

    // Event listeners
    window.addEventListener('scroll', checkNavbarPosition);
    window.addEventListener('resize', () => {
        // Recalculate on resize
        setTimeout(initializeStickyNavbar, 100);
    });

    // Initial check
    checkNavbarPosition();
}

// WhatsApp-stijl chat functionaliteit
function initializeWhatsappChat() {
    const chatItems = document.querySelectorAll('.chat-item');
    const chatDetailPanel = document.getElementById('chat-detail-panel');
    const backButton = document.getElementById('back-to-chats');
    const sendButton = document.getElementById('send-button');
    const messageInput = document.getElementById('message-input');

    // Get contact elements
    const contactAvatar = document.getElementById('contact-avatar');
    const contactName = document.getElementById('contact-name');

    // Open chat detail bij klikken op een chat
    if (chatItems) {
        chatItems.forEach(item => {
            item.addEventListener('click', function() {
                // Markeer geselecteerde chat
                chatItems.forEach(chat => chat.classList.remove('active'));
                this.classList.add('active');

                // Haal chat-id op (zou je kunnen gebruiken om de juiste berichten te laden)
                const chatId = this.getAttribute('data-chat-id');
                console.log(`Opening chat: ${chatId}`);

                // Update avatar en naam in de header (in een echte app zou je dit dynamisch laden)
                const avatar = this.querySelector('.chat-avatar').textContent;
                const name = this.querySelector('h4').textContent;

                if (contactAvatar && contactName) {
                    contactAvatar.textContent = avatar;
                    contactName.textContent = name;
                }

                // Activeer het detail paneel (vooral belangrijk op mobiel)
                if (chatDetailPanel) {
                    chatDetailPanel.classList.add('active');
                }
            });
        });
    }

    // Terug naar chatlijst bij klikken op terug knop
    if (backButton) {
        backButton.addEventListener('click', function() {
            if (chatDetailPanel) {
                chatDetailPanel.classList.remove('active');
            }
            if (messageInput) {
                messageInput.value = '';
            }
        });
    }

    // Bericht versturen
    if (sendButton && messageInput) {
        sendButton.addEventListener('click', sendMessage);
        messageInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    }

    function sendMessage() {
        const messageText = messageInput ? messageInput.value.trim() : '';
        if (messageText) {
            // Huidige tijd
            const now = new Date();
            const hours = now.getHours().toString().padStart(2, '0');
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const timeString = `${hours}:${minutes}`;

            // Nieuw bericht element maken
            const messagesContainer = document.querySelector('.messages-container');
            const newMessage = document.createElement('div');
            newMessage.className = 'message sent';
            newMessage.innerHTML = `
                <div class="message-bubble">
                    ${messageText}
                    <span class="message-time">${timeString}</span>
                </div>
            `;

            // Toevoegen aan chat
            if (messagesContainer) {
                messagesContainer.appendChild(newMessage);
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }

            // Input leegmaken
            messageInput.value = '';

            // In een echte app zou je hier een API call maken om het bericht op te slaan
            console.log(`Message sent: ${messageText}`);
        }
    }

    // Check op mobiel of desktop voor initiële weergave
    function checkMobileView() {
        const isMobile = window.innerWidth <= 768;
        if (chatDetailPanel) {
            // Op desktop standaard het eerste gesprek tonen
            if (!isMobile && chatItems && chatItems.length > 0) {
                chatItems[0].click();
            } else if (isMobile) {
                // Op mobiel standaard de chatlijst tonen
                chatDetailPanel.classList.remove('active');
            }
        }
    }

    checkMobileView();
    window.addEventListener('resize', checkMobileView);
}