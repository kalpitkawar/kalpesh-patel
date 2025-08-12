document.getElementById('login-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const msg = document.getElementById('login-msg');
    msg.textContent = 'Logging in...';
    fetch('../backend/user_login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            msg.style.color = 'green';
            msg.textContent = 'Login successful! Redirecting...';
            setTimeout(() => window.location.href = 'index.html', 1000);
        } else {
            msg.style.color = 'red';
            msg.textContent = data.error || 'Login failed.';
        }
    })
    .catch(() => {
        msg.style.color = 'red';
        msg.textContent = 'Server error.';
    });
});
