document.addEventListener("DOMContentLoaded", () => {

    // ==========================================
    // PASSWORD SHOW / HIDE TOGGLE
    // ==========================================
    const passwordInputs = document.querySelectorAll('input[type="password"]');

    passwordInputs.forEach((input) => {
        const inputGroup = input.closest('.input-group') || input.parentElement;

        let toggleBtn = inputGroup.querySelector('.toggle-password-btn');

        if (!toggleBtn) {
            toggleBtn = document.createElement('button');
            toggleBtn.type = 'button';
            toggleBtn.className = 'btn btn-outline-secondary border-start-0 bg-transparent text-muted toggle-password-btn';
            toggleBtn.style.borderColor = 'var(--ice-border)';
            toggleBtn.style.borderRadius = '0 12px 12px 0';
            toggleBtn.innerHTML = '<i class="fa-regular fa-eye"></i>';

            input.style.borderTopRightRadius = '0';
            input.style.borderBottomRightRadius = '0';

            inputGroup.appendChild(toggleBtn);
        }

        toggleBtn.addEventListener('click', () => {
            const isPassword = input.getAttribute('type') === 'password';
            input.setAttribute('type', isPassword ? 'text' : 'password');

            const icon = toggleBtn.querySelector('i');
            if (icon) {
                icon.className = isPassword ? 'fa-regular fa-eye-slash text-primary' : 'fa-regular fa-eye';
            }
        });
    });

    // ==========================================
    // SIGNUP PASSWORD MATCHING
    // ==========================================
    const signupForm = document.getElementById('signupForm');
    const signupPass = signupForm ? signupForm.querySelectorAll('input[type="password"]')[0] : null;
    const confirmPass = signupForm ? signupForm.querySelectorAll('input[type="password"]')[1] : null;

    const validatePasswordMatch = () => {
        if (!signupPass || !confirmPass) return true;

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
            feedback.textContent = '\u2713 Passwords match';
            feedback.style.color = '#10b981';
            confirmPass.style.borderColor = '#10b981';
            return true;
        } else {
            feedback.textContent = '\u2717 Passwords do not match';
            feedback.style.color = '#ef4444';
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

});
