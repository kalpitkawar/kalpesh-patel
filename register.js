document.getElementById('register-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const username = document.getElementById('username').value;
    const email = document.getElementById('email').value;
    const mobile = document.getElementById('mobile').value;
    const password = document.getElementById('password').value;
    const msg = document.getElementById('register-msg');
    msg.textContent = 'Registering...';
    fetch('../backend/user_register.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, email, mobile, password })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            msg.style.color = 'green';
            msg.textContent = 'Registration successful! You can now login.';
        } else {
            msg.style.color = 'red';
            msg.textContent = data.error || 'Registration failed.';
        }
    })
    .catch(() => {
        msg.style.color = 'red';
        msg.textContent = 'Server error.';
    });
});
