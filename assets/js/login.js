document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    const loginMessage = document.getElementById('login-message');

    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            // Clear previous messages
            if (loginMessage) {
                loginMessage.textContent = '';
                loginMessage.style.color = '';
            }
            
            // Show loading state
            const submitButton = loginForm.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            submitButton.textContent = 'Inloggen...';
            submitButton.disabled = true;
            
            // Create form data
            const formData = new FormData();
            formData.append('email', email);
            formData.append('password', password);
            
            // Send login request to PHP backend
            fetch('../api/login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Successful login
                    if (loginMessage) {
                        loginMessage.textContent = 'Inloggen succesvol!';
                        loginMessage.style.color = 'green';
                    }
                    
                    // Store user info in localStorage for client-side use
                    localStorage.setItem('authToken', data.token || 'authenticated');
                    localStorage.setItem('userRole', data.user.rol);
                    localStorage.setItem('userName', data.user.naam);
                    localStorage.setItem('userId', data.user.id);
                    
                    // Redirect based on user role
                    setTimeout(() => {
                        if (data.user.rol === 'admin') {
                            window.location.href = '../pages/admin.php';
                        } else {
                            window.location.href = '../pages/dashboard.php';
                        }
                    }, 1000);
                } else {
                    // Failed login
                    if (loginMessage) {
                        loginMessage.textContent = data.message || 'Ongeldige inloggegevens!';
                        loginMessage.style.color = 'red';
                    }
                }
            })
            .catch(error => {
                console.error('Login error:', error);
                if (loginMessage) {
                    loginMessage.textContent = 'Er is een fout opgetreden. Probeer het later opnieuw.';
                    loginMessage.style.color = 'red';
                }
            })
            .finally(() => {
                // Reset button state
                submitButton.textContent = originalText;
                submitButton.disabled = false;
            });
        });
    }
    
    // Check if user is already logged in
    const authToken = localStorage.getItem('authToken');
    const userRole = localStorage.getItem('userRole');
    
    if (authToken && userRole) {
        // Verify token with server
        fetch('../api/verify-session.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect to appropriate dashboard if already logged in
                    if (userRole === 'admin') {
                        window.location.href = '../pages/admin.php';
                    } else {
                        window.location.href = '../pages/dashboard.php';
                    }
                } else {
                    // Clear invalid tokens
                    localStorage.removeItem('authToken');
                    localStorage.removeItem('userRole');
                    localStorage.removeItem('userName');
                    localStorage.removeItem('userId');
                }
            })
            .catch(error => {
                console.error('Session verification error:', error);
                // Clear tokens on error
                localStorage.removeItem('authToken');
                localStorage.removeItem('userRole');
                localStorage.removeItem('userName');
                localStorage.removeItem('userId');
            });
    }
    
    // Password visibility toggle (if implemented in HTML)
    const passwordToggle = document.getElementById('password-toggle');
    const passwordField = document.getElementById('password');
    
    if (passwordToggle && passwordField) {
        passwordToggle.addEventListener('click', function() {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            
            // Toggle icon
            const icon = this.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            }
        });
    }
    
    // Auto-focus on email field
    const emailField = document.getElementById('email');
    if (emailField) {
        emailField.focus();
    }
});

// Utility function to check if user is authenticated
function isAuthenticated() {
    return localStorage.getItem('authToken') !== null;
}

// Utility function to get current user role
function getUserRole() {
    return localStorage.getItem('userRole');
}

// Utility function to get current user name
function getUserName() {
    return localStorage.getItem('userName');
}

// Utility function to logout user
function logout() {
    // Clear local storage
    localStorage.removeItem('authToken');
    localStorage.removeItem('userRole');
    localStorage.removeItem('userName');
    localStorage.removeItem('userId');
    
    // Notify server of logout
    fetch('../api/logout.php', {
        method: 'POST'
    })
    .then(() => {
        // Redirect to login page
        window.location.href = '../index.php';
    })
    .catch(error => {
        console.error('Logout error:', error);
        // Still redirect even if server logout fails
        window.location.href = '../index.php';
    });
}