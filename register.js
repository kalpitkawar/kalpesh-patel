document.getElementById('register-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = e.target;
    const usernameField = document.getElementById('username');
    const emailField = document.getElementById('email');
    const mobileField = document.getElementById('mobile');
    const passwordField = document.getElementById('password');
    const msg = document.getElementById('register-msg');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Get trimmed values
    const username = usernameField.value.trim();
    const email = emailField.value.trim();
    const mobile = mobileField.value.trim();
    const password = passwordField.value;
    
    // Client-side validation
    const errors = validateRegistrationForm(username, email, mobile, password);
    if (errors.length > 0) {
        showMessage(msg, errors.join(' '), 'error');
        focusFirstErrorField([usernameField, emailField, mobileField, passwordField], errors);
        return;
    }
    
    // Disable form during submission
    submitBtn.disabled = true;
    submitBtn.textContent = 'Creating Account...';
    showMessage(msg, 'Creating your account...', 'info');
    
    // Updated API path
    fetch('user_register.php', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ username, email, mobile, password })
    })
    .then(res => {
        if (!res.ok) {
            throw new Error(`HTTP error! status: ${res.status}`);
        }
        return res.json();
    })
    .then(data => {
        if (data.success) {
            showMessage(msg, 'Registration successful! You can now login with your credentials.', 'success');
            form.reset();
            
            // Redirect to login page after 2 seconds
            setTimeout(() => {
                window.location.href = 'login.html';
            }, 2000);
        } else {
            showMessage(msg, data.error || 'Registration failed. Please try again.', 'error');
            passwordField.focus();
        }
    })
    .catch(error => {
        console.error('Registration error:', error);
        showMessage(msg, 'Unable to connect to server. Please check your connection and try again.', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Register';
    });
});

function validateRegistrationForm(username, email, mobile, password) {
    const errors = [];
    
    // Username validation
    if (!username) {
        errors.push('Username is required.');
    } else if (username.length < 3) {
        errors.push('Username must be at least 3 characters.');
    } else if (username.length > 20) {
        errors.push('Username must be less than 20 characters.');
    } else if (!/^[a-zA-Z0-9]+$/.test(username)) {
        errors.push('Username can only contain letters and numbers.');
    }
    
    // Email validation
    if (!email) {
        errors.push('Email is required.');
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        errors.push('Please enter a valid email address.');
    }
    
    // Mobile validation
    if (!mobile) {
        errors.push('Mobile number is required.');
    } else if (!/^[0-9]{10,15}$/.test(mobile)) {
        errors.push('Mobile number must be 10-15 digits only.');
    }
    
    // Password validation
    if (!password) {
        errors.push('Password is required.');
    } else if (password.length < 8) {
        errors.push('Password must be at least 8 characters.');
    } else if (password.length > 100) {
        errors.push('Password is too long.');
    }
    
    return errors;
}

function showMessage(element, message, type) {
    element.textContent = message;
    element.className = `message ${type}`;
    element.style.color = type === 'error' ? '#f44336' : 
                         type === 'success' ? '#4caf50' : '#666';
}

function focusFirstErrorField(fields, errors) {
    // Focus on the first field that likely has an error
    if (errors.some(e => e.includes('Username'))) {
        fields[0].focus();
    } else if (errors.some(e => e.includes('Email'))) {
        fields[1].focus();
    } else if (errors.some(e => e.includes('Mobile'))) {
        fields[2].focus();
    } else if (errors.some(e => e.includes('Password'))) {
        fields[3].focus();
    }
}

// Real-time validation feedback
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('register-form');
    
    form.addEventListener('input', function(e) {
        const field = e.target;
        
        // Clear previous custom validity
        field.setCustomValidity('');
        
        if (field.name === 'username') {
            const value = field.value.trim();
            if (value && !/^[a-zA-Z0-9]+$/.test(value)) {
                field.setCustomValidity('Username can only contain letters and numbers');
            } else if (value && (value.length < 3 || value.length > 20)) {
                field.setCustomValidity('Username must be 3-20 characters');
            }
        }
        
        if (field.name === 'mobile') {
            const value = field.value.trim();
            if (value && !/^[0-9]{10,15}$/.test(value)) {
                field.setCustomValidity('Mobile number must be 10-15 digits only');
            }
        }
        
        if (field.name === 'password') {
            const value = field.value;
            if (value && value.length < 8) {
                field.setCustomValidity('Password must be at least 8 characters');
            }
        }
    });
});
