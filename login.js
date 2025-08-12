document.getElementById('login-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const loginField = document.getElementById('login');
    const passwordField = document.getElementById('password');
    const login = loginField.value.trim();
    const password = passwordField.value;
    const msg = document.getElementById('login-msg');
    const submitBtn = e.target.querySelector('button[type="submit"]');
    
    // Basic client-side validation
    if (!login) {
        showMessage(msg, 'Please enter your username, email, or mobile number.', 'error');
        loginField.focus();
        return;
    }
    
    if (!password) {
        showMessage(msg, 'Please enter your password.', 'error');
        passwordField.focus();
        return;
    }
    
    if (password.length < 6) {
        showMessage(msg, 'Password must be at least 6 characters.', 'error');
        passwordField.focus();
        return;
    }
    
    // Disable form during submission
    submitBtn.disabled = true;
    submitBtn.textContent = 'Logging in...';
    showMessage(msg, 'Logging in...', 'info');
    
    // Updated API path (removing ../backend/)
    fetch('user_login.php', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ login, password })
    })
    .then(res => {
        if (!res.ok) {
            throw new Error(`HTTP error! status: ${res.status}`);
        }
        return res.json();
    })
    .then(data => {
        if (data.success) {
            showMessage(msg, 'Login successful! Redirecting...', 'success');
            setTimeout(() => {
                window.location.href = 'index.html';
            }, 1000);
        } else {
            showMessage(msg, data.error || 'Login failed. Please check your credentials.', 'error');
            passwordField.focus();
        }
    })
    .catch(error => {
        console.error('Login error:', error);
        showMessage(msg, 'Unable to connect to server. Please try again.', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Login';
    });
});

function showMessage(element, message, type) {
    element.textContent = message;
    element.className = `message ${type}`;
    element.style.color = type === 'error' ? '#f44336' : 
                         type === 'success' ? '#4caf50' : '#666';
}
