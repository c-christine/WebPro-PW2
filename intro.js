// Form toggle functions
function showRegister() {
  document.getElementById('login-form').style.display = 'none';
  document.getElementById('register-form').style.display = 'block';
}

function showLogin() {
  document.getElementById('register-form').style.display = 'none';
  document.getElementById('login-form').style.display = 'block';
}

// Form handlers
document.addEventListener('DOMContentLoaded', () => {
  // Handle URL parameters for errors and success messages
  const urlParams = new URLSearchParams(window.location.search);
  
  // Check for login error
  if (urlParams.has('error')) {
    const error = urlParams.get('error');
    const errorMessages = {
      '1': 'Invalid username or password',
      'login_required': 'Please login to access that page'
    };
    
    if (errorMessages[error]) {
      alert(errorMessages[error]);
    }
    // Clear error from URL
    window.history.replaceState({}, document.title, window.location.pathname);
  }
  
  // Check for registration errors
  if (urlParams.has('errors')) {
    const errors = urlParams.get('errors').split(',');
    const errorMessages = {
      'password_mismatch': "Passwords don't match!",
      'password_length': "Password must be at least 8 characters!",
      'user_exists': "Username already exists!",
      'registration_failed': "Registration failed. Please try again."
    };
    
    errors.forEach(error => {
      if (errorMessages[error]) {
        alert(errorMessages[error]);
      }
    });
    
    // Show register form if there were errors
    showRegister();
    // Clear errors from URL
    window.history.replaceState({}, document.title, window.location.pathname);
  }
  
  // Show success message after registration
  if (urlParams.has('success')) {
    alert('Registration successful! Please login.');
    showLogin();
    window.history.replaceState({}, document.title, window.location.pathname);
  }

  // Login form validation
  document.getElementById('loginForm')?.addEventListener('submit', function(e) {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();
    
    if (!username || !password) {
      e.preventDefault();
      alert('Please enter both username and password');
    }
    // Else go to login.php
  });

  // Register form validation (client-side only)
  document.getElementById('registerForm')?.addEventListener('submit', function(e) {
    const password = document.getElementById('reg_password').value;
    const confirmPassword = document.getElementById('reg_confirm').value;
    
    if (password !== confirmPassword) {
      e.preventDefault();
      alert("Passwords don't match!");
      return;
    }
    
    if (password.length < 8) {
      e.preventDefault();
      alert("Password must be at least 8 characters!");
      return;
    }
    // Else go to register.php
  });
});