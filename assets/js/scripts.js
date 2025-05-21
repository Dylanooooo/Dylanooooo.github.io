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
        
        // Redirect to login page
        window.location.href = 'index.html';
    });
}

// Display user name
const userNameElement = document.getElementById('user-name');

if (userNameElement) {
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
    const currentUserId = currentUserIdElement ? currentUserIdElement.value : localStorage.getItem('userId');
    
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
    }orEach(user => {
        const userItem = document.createElement('div');
    function renderUsersList(users) {t-user';
        if (!usersList) return;
        naam; // Store the user name for reference
        usersList.innerHTML = '';unt > 0 ? 
        
        if (users.length === 0) {
            usersList.innerHTML = '<div class="no-users">Geen gebruikers beschikbaar</div>';
            return;
        }
        
        users.forEach(user => { {
            const userItem = document.createElement('div');ach(el => el.classList.remove('active'));
            userItem.className = 'chat-user';
            userItem.dataset.userId = user.id;ng to
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
                this.classList.add('active');ementById('chat-notification-badge');
                readCounts).reduce((sum, count) => sum + count, 0);
                // Update chat header to show who you're talking to
                const chatHeader = document.querySelector('.chat-header-title');
                if (chatHeader) {textContent = totalUnread;
                    chatHeader.textContent = `Chat met ${this.dataset.userName}`;
                }
                
                loadChatMessages(this.dataset.userId);
            });
            
            usersList.appendChild(userItem);
        });
    }t-user[data-user-id="${userId}"]`);
    
    function updateUnreadCounts(unreadCounts) {ad-badge');
        // Update the main notification badge
        const notificationBadge = document.getElementById('chat-notification-badge');
        const totalUnread = Object.values(unreadCounts).reduce((sum, count) => sum + count, 0);
        = 'unread-badge';
        if (notificationBadge) {(badge);
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
                <div class="loading-messages">Berichten laden...</div>';
                if (unreadCounts[userId] > 0) {user_id=${userId}`)
                    if (!badge) {e.json())
                        badge = document.createElement('span');
                        badge.className = 'unread-badge';
                        userItem.appendChild(badge);, userId);
                    }es as read
                    badge.textContent = unreadCounts[userId];
                } else if (badge) {json())
                    badge.remove();
                }
            }
        }ser-id="${userId}"] .unread-badge`);
    }
     total count
    function loadChatMessages(userId) {
        if (!chatMessages) return;
        
        // Store the selected user ID for reference
        messageForm.dataset.receiverId = userId;iv class="error-message">${data.message}</div>`;
        
        chatMessages.innerHTML = '<div class="loading-messages">Berichten laden...</div>';
        
        fetch(`../api/chat.php?action=get_messages&user_id=${userId}`)
            .then(response => response.json())rror-message">Fout bij het laden van berichten</div>';
            .then(data => {
                if (data.success) {
                    renderChatMessages(data.messages, userId);
                    
                    // Mark messages as read
                    fetch(`../api/chat.php?action=mark_read&user_id=${userId}`)
                        .then(response => response.json())
                        .then(data => { class="no-messages">Geen berichten. Stuur een bericht om de conversatie te starten!</div>';
                            if (data.success) {
                                // Update unread counts
                                const userBadge = document.querySelector(`.chat-user[data-user-id="${userId}"] .unread-badge`);
                                if (userBadge) userBadge.remove();
                                
                                // Also update total count
                                fetchChatUsers();
                            }sage);
                        });sage from ${message.afzender_id}, to ${message.ontvanger_id}, current user viewing: ${userId}`);
                } else {
                    chatMessages.innerHTML = `<div class="error-message">${data.message}</div>`;
                }) {
            })eDate;
            .catch(error => {ent('div');
                console.error('Error loading messages:', error);
                chatMessages.innerHTML = '<div class="error-message">Fout bij het laden van berichten</div>';
            });
    }
        
    function renderChatMessages(messages, userId) {
        if (!chatMessages) return;v');
        Date(message.timestamp).toLocaleTimeString('nl-NL', {
        chatMessages.innerHTML = '';
        digit'
        if (messages.length === 0) {
            chatMessages.innerHTML = '<div class="no-messages">Geen berichten. Stuur een bericht om de conversatie te starten!</div>';
            return;
        }FromCurrentUser ? 'sent' : 'received'}`;
        
        console.log("Rendering messages:", messages);
        let currentDate = '';${time}</div>
        
        messages.forEach(message => {
            // Debug output
            console.log("Message:", message);
            console.log(`Message from ${message.afzender_id}, to ${message.ontvanger_id}, current user viewing: ${userId}`);
            Messages.scrollTop = chatMessages.scrollHeight;
            // Add date separator if date changes
            const messageDate = new Date(message.timestamp).toLocaleDateString('nl-NL');
            if (messageDate !== currentDate) {
                currentDate = messageDate;ener('submit', function(e) {
                const dateSeparator = document.createElement('div');
                dateSeparator.className = 'date-separator';st receiverId = this.dataset.receiverId;
                dateSeparator.textContent = currentDate;
                chatMessages.appendChild(dateSeparator);
            }
            
            // Create the message element
            const messageItem = document.createElement('div');
            
            // Check if message was sent by current user
            const isFromMe = parseInt(message.afzender_id) === parseInt(currentUserId);rmData.append('message', message);
            messageItem.className = `message ${isFromMe ? 'sent' : 'received'}`;
            
            console.log(`Message direction: ${isFromMe ? 'sent by me' : 'received from other user'}`);
            
            const time = new Date(message.timestamp).toLocaleTimeString('nl-NL', {
                hour: '2-digit',
                minute: '2-digit'
            });) {
            ssageInput.value = '';
            messageItem.innerHTML = `
                <div class="message-content">${message.bericht}</div>
                <div class="message-time">${time}</div>
            `;
            })
            chatMessages.appendChild(messageItem); {
        });sage:', error);
            alert('Fout bij het versturen van het bericht');
        // Scroll to bottom
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // Send message users with auto-open first unread chat
    if (messageForm) {
        messageForm.addEventListener('submit', function(e) {action=get_users')
            e.preventDefault();response.json())
            
            const receiverId = this.dataset.receiverId;
            const message = messageInput.value.trim();.users);
            d_counts);
            if (!receiverId || !message) return;
            nread messages
            const formData = new FormData();firstUserWithUnread = data.users.find(user => user.unread_count > 0);
            formData.append('action', 'send_message');ead) {
            formData.append('receiver_id', receiverId);       console.log('Auto-opening chat with unread messages:', firstUserWithUnread.naam);
            formData.append('message', message);          // Find and click the user's chat element
            er[data-user-id="${firstUserWithUnread.id}"]`);
            fetch('../api/chat.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())           } else {
            .then(data => {                console.error('Failed to fetch users:', data.message);
                if (data.success) {
                    messageInput.value = '';
                    loadChatMessages(receiverId);
                } else {
                    alert(data.message);
                }h initialLoad() for first load
            })
            .catch(error => {
                console.error('Error sending message:', error);tion() {
                alert('Fout bij het versturen van het bericht');
            });
        });
    }heir messages
    
    // Initial load of users with auto-open first unread chat
    function initialLoad() {
        fetch('../api/chat.php?action=get_users')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderUsersList(data.users);
                    updateUnreadCounts(data.unread_counts);t
                    
                    // Find the first user with unread messages van het huidige navigatie-item op basis van de URL
                    const firstUserWithUnread = data.users.find(user => user.unread_count > 0);ctiveNavItem() {
                    
                    if (firstUserWithUnread) {   const navLinks = document.querySelectorAll('.nav-list a');
                        console.log('Auto-opening chat with unread messages:', firstUserWithUnread.naam);    
                        
                        // Find and click the user's chat elementorEach(link => link.classList.remove('active'));
                        const userElement = document.querySelector(`.chat-user[data-user-id="${firstUserWithUnread.id}"]`);
                        if (userElement) {iste item
                            userElement.click();k => {
                        });
                    }
                } else {    (currentPage === '' && linkPage === 'index.html') ||
                    console.error('Failed to fetch users:', data.message);ard-stagiair.html')) {
                }
            })
            .catch(error => console.error('Error fetching users:', error));
    }
    sole.log('Active nav item set based on current page:', currentPage);
    // Replace fetchChatUsers() with initialLoad() for first load
    initialLoad();
        // Voer de functie direct uit
    // Periodic reload of chat data
    setInterval(function() {
        if (chatPanel.classList.contains('active')) {
            fetchChatUsers();, 100);
            
            // If a user is selected, reload their messages
            const activeUser = document.querySelector('.chat-user.active');ChatInterface();
            if (activeUser) {
                loadChatMessages(activeUser.dataset.userId);
            }document.getElementById('whatsapp-chat')) {
        }
    }, 10000); // Check every 10 seconds
}

// DOMContentLoaded event
document.addEventListener('DOMContentLoaded', function() {
    // Higlichten van het huidige navigatie-item op basis van de URL
    function setActiveNavItem() {
        const currentPage = window.location.pathname.split('/').pop();avSpacer = document.querySelector('.nav-spacer');
        const navLinks = document.querySelectorAll('.nav-list a');t bannerWrapper = document.querySelector('.intro-banner-wrapper');
        
        // Verwijder active class van alle items
        navLinks.forEach(link => link.classList.remove('active'));if (!navbar || !bannerWrapper || !header) {
        elementen voor sticky navbar:", {
        // Voeg active class toe aan het juiste itemavbar,
        navLinks.forEach(link => {        bannerWrapper: !!bannerWrapper,
            const linkPage = link.getAttribute('href');
            if (currentPage === linkPage || 
                (currentPage === '' && linkPage === 'index.html') ||    return;
                (currentPage === '' && linkPage === 'dashboard-stagiair.html')) {
                link.classList.add('active');
            }// Maak de spacer indien niet aanwezig
        });
        
        console.log('Active nav item set based on current page:', currentPage);e = 'nav-spacer';
    }
    d");
    // Voer de functie direct uit
    setActiveNavItem();
     const updatedNavSpacer = document.querySelector('.nav-spacer');
    // Initialiseer de SVG meter    
    setTimeout(initializeSvgMeter, 100);er
    
    // Initialiseer de chat interface
    initChatInterface(); + 'px';
    
    // Voeg dit toe aan het einde van je bestaande DOMContentLoaded handler
    initializeStickyNavbar();
    // Controleer de positie bij het laden
    // Initialiseer WhatsApp chat als we op de chat pagina zijn
    if (document.getElementById('whatsapp-chat')) {
        initializeWhatsappChat();tling
    }
});'scroll', function() {
(!scrollTimeout) {
// Verbeterde Sticky navbar functionaliteitt(function() {
function initializeStickyNavbar() {           checkNavbarPosition();
    console.log("Initialiseren van sticky navbar...");;
    const navbar = document.querySelector('nav');
    const navSpacer = document.querySelector('.nav-spacer');
    const bannerWrapper = document.querySelector('.intro-banner-wrapper');
    const header = document.querySelector('header');
    size
    if (!navbar || !bannerWrapper || !header) {
        console.log("Ontbrekende elementen voor sticky navbar:", {
            navbar: !!navbar,function checkNavbarPosition() {
            bannerWrapper: !!bannerWrapper,
            header: !!header    const bannerHeight = bannerWrapper.offsetHeight;
        });ght;
        return;
    }on = headerHeight + bannerHeight - 10; // 10px eerder triggeren
    
    // Maak de spacer indien niet aanwezig
    if (!navSpacer) {   console.log(`Scroll positie: ${scrollPosition}, Trigger positie: ${triggerPosition}`);
        const newSpacer = document.createElement('div');    
        newSpacer.className = 'nav-spacer';e banner is
        navbar.after(newSpacer);
        console.log("Nav spacer dynamisch toegevoegd");
    }
    g("Sticky navbar geactiveerd");
    const updatedNavSpacer = document.querySelector('.nav-spacer');
    
    // Bereken correcte hoogte voor de spacerive');
    const navHeight = navbar.offsetHeight; gedeactiveerd");
    if (updatedNavSpacer) {
        updatedNavSpacer.style.height = navHeight + 'px';
        console.log(`Nav spacer hoogte ingesteld op ${navHeight}px`);
    }
    hatsApp-stijl chat functionaliteit
    // Controleer de positie bij het laden
    checkNavbarPosition();
    atDetailPanel = document.getElementById('chat-detail-panel');
    // Controleer de positie bij scrollen met throttling
    let scrollTimeout;end-button');
    window.addEventListener('scroll', function() {e-input');
        if (!scrollTimeout) {
            scrollTimeout = setTimeout(function() {t
                checkNavbarPosition();
                scrollTimeout = null;Items.forEach(item => {
            }, 10);tener('click', function() {
        }
    });        chatItems.forEach(chat => chat.classList.remove('active'));
    
    // Controleer positie bij resize
    window.addEventListener('resize', checkNavbarPosition);kunnen gebruiken om de juiste berichten te laden)
    -chat-id');
    function checkNavbarPosition() {);
        // Bereken wanneer de banner uit beeld is
        const bannerHeight = bannerWrapper.offsetHeight;
        const headerHeight = header.offsetHeight;avatar').textContent;
        const scrollPosition = window.scrollY;extContent;
        const triggerPosition = headerHeight + bannerHeight - 10; // 10px eerder triggeren       
        
        // Debug info               const contactName = chatDetailPanel.querySelector('.contact-details h4');
        console.log(`Scroll positie: ${scrollPosition}, Trigger positie: ${triggerPosition}`);                
        ontactName) {
        // Als de scroll positie voorbij de banner is
        if (scrollPosition >= triggerPosition) {
            navbar.classList.add('sticky');
            updatedNavSpacer?.classList.add('active');
            console.log("Sticky navbar geactiveerd");
        } else {
            navbar.classList.remove('sticky');                chatDetailPanel.classList.add('active');
            updatedNavSpacer?.classList.remove('active');
            console.log("Sticky navbar gedeactiveerd");
        }
    }
}

// WhatsApp-stijl chat functionaliteit
function initializeWhatsappChat() {on.addEventListener('click', function() {
    const chatItems = document.querySelectorAll('.chat-item');
    const chatDetailPanel = document.getElementById('chat-detail-panel');
    const backButton = document.getElementById('back-to-chats');
    const sendButton = document.getElementById('send-button');
    const messageInput = document.getElementById('message-input');
    
    // Open chat detail bij klikken op een chat
    if (chatItems) {on && messageInput) {
        chatItems.forEach(item => {
            item.addEventListener('click', function() {
                // Markeer geselecteerde chat
                chatItems.forEach(chat => chat.classList.remove('active'));
                this.classList.add('active');
                
                // Haal chat-id op (zou je kunnen gebruiken om de juiste berichten te laden)
                const chatId = this.getAttribute('data-chat-id');
                console.log(`Opening chat: ${chatId}`);
                
                // Update avatar en naam in de header (in een echte app zou je dit dynamisch laden)
                const avatar = this.querySelector('.chat-avatar').textContent;idige tijd
                const name = this.querySelector('h4').textContent;
                nst hours = now.getHours().toString().padStart(2, '0');
                const contactAvatar = chatDetailPanel.querySelector('.contact-avatar');       const minutes = now.getMinutes().toString().padStart(2, '0');
                const contactName = chatDetailPanel.querySelector('.contact-details h4');        const timeString = `${hours}:${minutes}`;
                
                if (contactAvatar && contactName) {bericht element maken
                    contactAvatar.textContent = avatar;tor('.messages-container');
                    contactName.textContent = name;ument.createElement('div');
                }
                age.innerHTML = `
                // Activeer het detail paneel (vooral belangrijk op mobiel)
                if (chatDetailPanel) {               ${messageText}
                    chatDetailPanel.classList.add('active');                <span class="message-time">${timeString}</span>
                }
            });
        });
    }
    
    // Terug naar chatlijst bij klikken op terug knopild(newMessage);
    if (backButton) {rollHeight;
        backButton.addEventListener('click', function() {
            if (chatDetailPanel) {       
                chatDetailPanel.classList.remove('active');        // Input leegmaken
            }ue = '';
        });
    }ericht op te slaan
    
    // Bericht versturen
    if (sendButton && messageInput) {
        sendButton.addEventListener('click', sendMessage);
        messageInput.addEventListener('keypress', function(e) {ave
            if (e.key === 'Enter') { checkMobileView() {
                sendMessage();
            }
        });
    }s.length > 0) {
    ;
    function sendMessage() {
        const messageText = messageInput.value.trim();
        if (messageText) {
            // Huidige tijd
            const now = new Date();
            const hours = now.getHours().toString().padStart(2, '0');
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const timeString = `${hours}:${minutes}`;
            
            // Nieuw bericht element maken
            const messagesContainer = document.querySelector('.messages-container');            const newMessage = document.createElement('div');            newMessage.className = 'message sent';            newMessage.innerHTML = `                <div class="message-bubble">                    ${messageText}                    <span class="message-time">${timeString}</span>                </div>            `;                        // Toevoegen aan chat            if (messagesContainer) {                messagesContainer.appendChild(newMessage);                messagesContainer.scrollTop = messagesContainer.scrollHeight;            }                        // Input leegmaken            messageInput.value = '';                        // In een echte app zou je hier een API call maken om het bericht op te slaan            console.log(`Message sent: ${messageText}`);        }    }
    
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