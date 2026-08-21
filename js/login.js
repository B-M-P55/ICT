document.addEventListener("DOMContentLoaded", () => {
    // ==========================================
    // 1. PASSWORD SHOW / HIDE TOGGLE
    // ==========================================
    const setupPasswordToggles = () => {
        // Select all password input containers
        const passwordInputs = document.querySelectorAll('input[type="password"]');

        passwordInputs.forEach((input) => {
            const inputGroup = input.closest('.input-group') || input.parentElement;
            
            // Create toggle button if not present
            let toggleBtn = inputGroup.querySelector('.toggle-password-btn');
            
            if (!toggleBtn) {
                toggleBtn = document.createElement('button');
                toggleBtn.type = 'button';
                toggleBtn.className = 'btn btn-outline-secondary border-start-0 bg-transparent text-muted toggle-password-btn';
                toggleBtn.style.borderColor = 'var(--ice-border)';
                toggleBtn.style.borderRadius = '0 12px 12px 0';
                toggleBtn.innerHTML = '<i class="fa-regular fa-eye"></i>';
                
                // Adjust input styling to fit button nicely
                input.style.borderTopRightRadius = '0';
                input.style.borderBottomRightRadius = '0';
                
                inputGroup.appendChild(toggleBtn);
            }

            // Click listener to toggle password visibility
            toggleBtn.addEventListener('click', () => {
                const isPassword = input.getAttribute('type') === 'password';
                input.setAttribute('type', isPassword ? 'text' : 'password');
                
                const icon = toggleBtn.querySelector('i');
                if (icon) {
                    icon.className = isPassword ? 'fa-regular fa-eye-slash text-primary' : 'fa-regular fa-eye';
                }
            });
        });
    };

    // ==========================================
    // 2. SIGNUP PASSWORD MATCHING & VALIDATION
    // ==========================================
    const signupForm = document.getElementById('signupForm');
    const signupPass = signupForm ? signupForm.querySelectorAll('input[type="password"]')[0] : null;
    const confirmPass = signupForm ? signupForm.querySelectorAll('input[type="password"]')[1] : null;

    const validatePasswordMatch = () => {
        if (!signupPass || !confirmPass) return true;

        // Create feedback element if it doesn't exist
        let feedback = confirmPass.parentElement.querySelector('.password-match-feedback');
        if (!feedback) {
            feedback = document.createElement('small');
            feedback.className = 'password-match-feedback d-block mt-1 font-monospace';
            confirmPass.parentElement.appendChild(feedback);
        }

        if (confirmPass.value.length === 0) {
            feedback.textContent = '';
            confirmPass.style.borderColor = 'var(--ice-border)';
            return false;
        }

        if (signupPass.value === confirmPass.value) {
            feedback.textContent = '✓ Passwords match';
            feedback.style.color = '#10b981'; // Success Green
            confirmPass.style.borderColor = '#10b981';
            return true;
        } else {
            feedback.textContent = '✕ Passwords do not match';
            feedback.style.color = '#ef4444'; // Error Red
            confirmPass.style.borderColor = '#ef4444';
            return false;
        }
    };

    if (signupPass && confirmPass) {
        confirmPass.addEventListener('input', validatePasswordMatch);
        signupPass.addEventListener('input', () => {
            if (confirmPass.value.length > 0) validatePasswordMatch();
        });
    }

    // ==========================================
    // 3. FORM SUBMISSION HANDLERS
    // ==========================================
    const loginForm = document.getElementById('loginForm');

    // Login Form Submit
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const submitBtn = loginForm.querySelector('button[type="submit"]');
            
            // Show loading indicator
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status"></span> Logging in...`;

            // Simulate API call delay (Replace with real backend authentication)
            setTimeout(() => {
                alert('Success: Logged in successfully!');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                // window.location.href = 'index.html'; // Redirect user after login
            }, 1200);
        });
    }

    // Signup Form Submit
    if (signupForm) {
        signupForm.addEventListener('submit', (e) => {
            e.preventDefault();

            // Check if passwords match
            if (!validatePasswordMatch()) {
                alert('Please ensure passwords match before submitting.');
                return;
            }

            const submitBtn = signupForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status"></span> Creating Account...`;

            // Simulate API call delay
            setTimeout(() => {
                alert('Success: Account created successfully! Please log in.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                
                // Switch tab back to Login
                const loginTabBtn = document.getElementById('login-tab');
                if (loginTabBtn) {
                    const tab = new bootstrap.Tab(loginTabBtn);
                    tab.show();
                }
            }, 1200);
        });
    }

    // Initialize password toggles
    setupPasswordToggles();
});