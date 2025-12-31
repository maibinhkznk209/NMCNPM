// Password toggle functionality
const passwordInput = document.getElementById('password');
const passwordToggle = document.getElementById('passwordToggle');

passwordToggle.addEventListener('click', function() {
    if (passwordInput.type === 'password') {
    passwordInput.type = 'text';
    passwordToggle.textContent = '🙈';
    } else {
    passwordInput.type = 'password';
    passwordToggle.textContent = '👁️';
    }
});

// Show/hide messages
function showMessage(messageId, text) {
    const messageEl = document.getElementById(messageId);
    messageEl.textContent = text;
    messageEl.style.display = 'block';
    setTimeout(() => {
    messageEl.style.display = 'none';
    }, 5000);
}

// Form validation and submission
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const loginButton = document.getElementById('loginButton');

    // Hide previous messages
    document.getElementById('errorMessage').style.display = 'none';
    document.getElementById('successMessage').style.display = 'none';

    // Basic validation
    if (!email || !password) {
    showMessage('errorMessage', 'Vui lòng nhập đầy đủ email và mật khẩu.');
    return;
    }

    // Email format validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
    showMessage('errorMessage', 'Vui lòng nhập email hợp lệ.');
    return;
    }

    // Password length validation
    if (password.length < 6) {
    showMessage('errorMessage', 'Mật khẩu phải có ít nhất 6 ký tự.');
    return;
    }

    // Show loading state
    loginButton.classList.add('loading');
    loginButton.innerHTML = '';

    // Simulate login process
    setTimeout(() => {
    try {
        const username = email.split('@')[0];
        
        // Store user data (in a real app, this would be handled by backend)
        const userData = {
        username: username,
        email: email,
        loginTime: new Date().toISOString()
        };
        
        localStorage.setItem('userData', JSON.stringify(userData));
        localStorage.setItem('isLoggedIn', 'true');
        localStorage.setItem('username', username);

        
        showMessage('successMessage', 'Đăng nhập thành công! Đang chuyển hướng...');
        
        // Redirect after success message
        setTimeout(() => {
        window.location.href = 'index.html';
        }, 1500);
        
    } catch (error) {
        showMessage('errorMessage', 'Có lỗi xảy ra. Vui lòng thử lại.');
        loginButton.classList.remove('loading');
        loginButton.innerHTML = '<span>Đăng nhập</span>';
    }
    }, 1500); // Simulate network delay
});

// Input focus effects
const inputs = document.querySelectorAll('input');
inputs.forEach(input => {
    input.addEventListener('focus', function() {
    this.parentElement.parentElement.classList.add('focused');
    });
    
    input.addEventListener('blur', function() {
    this.parentElement.parentElement.classList.remove('focused');
    });
});

// Keyboard accessibility
document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && document.activeElement.tagName !== 'BUTTON') {
    document.getElementById('loginForm').dispatchEvent(new Event('submit'));
    }
});

// Auto-focus first input
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('email').focus();
});