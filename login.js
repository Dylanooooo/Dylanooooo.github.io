document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    const loginMessage = document.getElementById('login-message');

    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            // In a real application, this would make an API call to authenticate
            // For this demo, we'll use hardcoded credentials
            
            if (username === 'stagiair' && password === 'password123') {
                // Successful login as intern
                loginMessage.textContent = 'Inloggen succesvol!';
                loginMessage.style.color = 'green';
                
                // Store user info
                localStorage.setItem('authToken', 'demo-token-stagiair');
                localStorage.setItem('userRole', 'stagiair');
                localStorage.setItem('userName', 'Demo Stagiair');
                
                // Redirect to appropriate dashboard
                setTimeout(() => {
                    window.location.href = 'dashboard-stagiair.html';
                }, 1000);
            } 
            else if (username === 'medewerker' && password === 'password123') {
                // Successful login as staff
                loginMessage.textContent = 'Inloggen succesvol!';
                loginMessage.style.color = 'green';
                
                // Store user info
                localStorage.setItem('authToken', 'demo-token-medewerker');
                localStorage.setItem('userRole', 'medewerker');
                localStorage.setItem('userName', 'Demo Medewerker');
                
                // Redirect to appropriate dashboard
                setTimeout(() => {
                    window.location.href = 'dashboard-medewerker.html';
                }, 1000);
            } 
            else {
                // Failed login
                loginMessage.textContent = 'Ongeldige gebruikersnaam of wachtwoord!';
                loginMessage.style.color = 'red';
            }
        });
    }
    
    // Check if user is already logged in
    const authToken = localStorage.getItem('authToken');
    const userRole = localStorage.getItem('userRole');
    
    if (authToken && userRole) {
        // Redirect to appropriate dashboard if already logged in
        if (userRole === 'stagiair') {
            window.location.href = 'dashboard-stagiair.html';
        } else if (userRole === 'medewerker') {
            window.location.href = 'dashboard-medewerker.html';
        }
    }
});