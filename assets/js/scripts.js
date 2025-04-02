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

// Chat interface initialisatie
function initializeChat() {
    console.log("Initialiseren van chat interface...");
    
    const chatContainer = document.querySelector('.chat-container');
    if (!chatContainer) return; // Alleen uitvoeren op de chatpagina
    
    // Contacten ophalen
    const contacts = document.querySelectorAll('.chat-contact');
    if (contacts.length === 0) return; // Geen contacten gevonden
    
    // Eerste contact actief maken als er nog geen actief contact is
    const hasActiveContact = Array.from(contacts).some(contact => contact.classList.contains('active'));
    
    if (!hasActiveContact && contacts.length > 0) {
        const firstContact = contacts[0];
        firstContact.classList.add('active');
        
        // Update de header met de gegevens van het eerste contact
        updateChatHeader(firstContact);
        
        console.log("Eerste contact automatisch actief gemaakt:", firstContact.querySelector('.contact-name').textContent);
    }
    
    // Functie om de chatheader bij te werken met contactgegevens
    function updateChatHeader(contact) {
        const avatar = contact.querySelector('.contact-avatar').textContent;
        const name = contact.querySelector('.contact-name').textContent;
        const status = contact.querySelector('.contact-status').textContent;
        
        const headerInfo = document.querySelector('.chat-contact-info');
        if (headerInfo) {
            headerInfo.innerHTML = `
                <div class="contact-avatar">${avatar}</div>
                <div class="contact-info">
                    <div class="contact-name">${name}</div>
                    <div class="contact-status">${status}</div>
                </div>
            `;
        }
    }
    
    // Klikfunctionaliteit voor contacten
    contacts.forEach(contact => {
        contact.addEventListener('click', function() {
            // Verwijder active class van alle contacten
            contacts.forEach(c => c.classList.remove('active'));
            // Maak dit contact actief
            this.classList.add('active');
            
            // Update header info
            updateChatHeader(this);
        });
    });
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
                (currentPage === '' && linkPage === 'index.html') ||
                (currentPage === '' && linkPage === 'dashboard-stagiair.html')) {
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
    initializeChat();
    
    // Initialiseer alleen de SVG meter - de enige die we nodig hebben
    setTimeout(initializeSvgMeter, 100);
    
    // Chat-specifieke functionaliteit - alleen toevoegen als er een chat-interface is
    const chatInput = document.querySelector('.chat-input input');
    const chatButton = document.querySelector('.chat-input button');
    const chatMessages = document.querySelector('.chat-messages');
    
    if (chatInput && chatButton && chatMessages) {
        // Chat versturen met Enter-toets
        chatInput.addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                sendChatMessage();
            }
        });
        
        // Chat versturen met knop
        chatButton.addEventListener('click', sendChatMessage);
        
        function sendChatMessage() {
            const message = chatInput.value.trim();
            if (message) {
                // Eenvoudige tijdsweergave
                const now = new Date();
                const time = now.getHours() + ':' + (now.getMinutes() < 10 ? '0' : '') + now.getMinutes();
                
                // Voeg bericht toe aan chat
                const messageDiv = document.createElement('div');
                messageDiv.className = 'message sent';
                messageDiv.innerHTML = `
                    <div class="message-content">
                        ${message}
                        <span class="message-time">${time}</span>
                    </div>
                `;
                
                chatMessages.appendChild(messageDiv);
                
                // Scroll naar beneden
                chatMessages.scrollTop = chatMessages.scrollHeight;
                
                // Maak input leeg
                chatInput.value = '';
            }
        }
        
        // Contact selectie functionaliteit
        const contacts = document.querySelectorAll('.chat-contact');
        contacts.forEach(contact => {
            contact.addEventListener('click', function() {
                // Verwijder active class van alle contacten
                contacts.forEach(c => c.classList.remove('active'));
                // Maak dit contact actief
                this.classList.add('active');
                
                // Update header info met contactgegevens
                const avatar = this.querySelector('.contact-avatar').textContent;
                const name = this.querySelector('.contact-name').textContent;
                const status = this.querySelector('.contact-status').textContent;
                
                const headerInfo = document.querySelector('.chat-contact-info');
                if (headerInfo) {
                    headerInfo.innerHTML = `
                        <div class="contact-avatar">${avatar}</div>
                        <div class="contact-info">
                            <div class="contact-name">${name}</div>
                            <div class="contact-status">${status}</div>
                        </div>
                    `;
                }
            });
        });
    }
    
    // Initialiseer de chat interface
    initializeChat();

    // Voeg dit toe aan het einde van je bestaande DOMContentLoaded handler
    // en verwijder de oude sticky navbar code
    initializeStickyNavbar();

    // Initialiseer WhatsApp chat als we op de chat pagina zijn
    if (document.getElementById('whatsapp-chat')) {
        initializeWhatsappChat();
    }
});

// Verbeterde Sticky navbar functionaliteit
function initializeStickyNavbar() {
    console.log("Initialiseren van sticky navbar...");
    
    const navbar = document.querySelector('nav');
    const navSpacer = document.querySelector('.nav-spacer');
    const bannerWrapper = document.querySelector('.intro-banner-wrapper');
    const header = document.querySelector('header');
    
    if (!navbar || !bannerWrapper || !header) {
        console.log("Ontbrekende elementen voor sticky navbar:", {
            navbar: !!navbar,
            bannerWrapper: !!bannerWrapper,
            header: !!header
        });
        return;
    }
    
    // Maak de spacer indien niet aanwezig
    if (!navSpacer) {
        const newSpacer = document.createElement('div');
        newSpacer.className = 'nav-spacer';
        navbar.after(newSpacer);
        console.log("Nav spacer dynamisch toegevoegd");
    }
    
    const updatedNavSpacer = document.querySelector('.nav-spacer');
    
    // Bereken correcte hoogte voor de spacer
    const navHeight = navbar.offsetHeight;
    if (updatedNavSpacer) {
        updatedNavSpacer.style.height = navHeight + 'px';
        console.log(`Nav spacer hoogte ingesteld op ${navHeight}px`);
    }
    
    // Controleer de positie bij het laden
    checkNavbarPosition();
    
    // Controleer de positie bij scrollen met throttling
    let scrollTimeout;
    window.addEventListener('scroll', function() {
        if (!scrollTimeout) {
            scrollTimeout = setTimeout(function() {
                checkNavbarPosition();
                scrollTimeout = null;
            }, 10);
        }
    });
    
    // Controleer positie bij resize
    window.addEventListener('resize', checkNavbarPosition);
    
    function checkNavbarPosition() {
        // Bereken wanneer de banner uit beeld is
        const bannerHeight = bannerWrapper.offsetHeight;
        const headerHeight = header.offsetHeight;
        const scrollPosition = window.scrollY;
        const triggerPosition = headerHeight + bannerHeight - 10; // 10px eerder triggeren
        
        // Debug info
        console.log(`Scroll positie: ${scrollPosition}, Trigger positie: ${triggerPosition}`);
        
        // Als de scroll positie voorbij de banner is
        if (scrollPosition >= triggerPosition) {
            navbar.classList.add('sticky');
            updatedNavSpacer?.classList.add('active');
            console.log("Sticky navbar geactiveerd");
        } else {
            navbar.classList.remove('sticky');
            updatedNavSpacer?.classList.remove('active');
            console.log("Sticky navbar gedeactiveerd");
        }
    }
}

// WhatsApp-stijl chat functionaliteit
function initializeWhatsappChat() {
    const chatItems = document.querySelectorAll('.chat-item');
    const chatDetailPanel = document.getElementById('chat-detail-panel');
    const backButton = document.getElementById('back-to-chats');
    const sendButton = document.getElementById('send-button');
    const messageInput = document.getElementById('message-input');
    
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
                
                const contactAvatar = chatDetailPanel.querySelector('.contact-avatar');
                const contactName = chatDetailPanel.querySelector('.contact-details h4');
                
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
        const messageText = messageInput.value.trim();
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
    
    // Voer direct uit en bij resize
    checkMobileView();
    window.addEventListener('resize', checkMobileView);
}