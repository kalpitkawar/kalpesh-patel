document.getElementById('login-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    fetch('../backend/admin_login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'admin_dashboard.html';
        } else {
            document.getElementById('login-error').textContent = data.error || 'Login failed.';
        }
    })
    .catch(() => {
        document.getElementById('login-error').textContent = 'Server error.';
    });
});
