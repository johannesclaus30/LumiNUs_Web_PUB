document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.querySelector('.toggle-password');
    const passwordInput = document.getElementById('password-field');
    
    if (toggleBtn && passwordInput) {
        const openEye = toggleBtn.querySelector('.eye-open');
        const closedEye = toggleBtn.querySelector('.eye-closed');
        
        // ✅ Force initial state so the eye icon appears on page load
        if (openEye) openEye.style.display = 'block';
        if (closedEye) closedEye.style.display = 'none';
        
        // Toggle on click
        toggleBtn.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            if (type === 'text') {
                if (openEye) openEye.style.display = 'none';
                if (closedEye) closedEye.style.display = 'block';
                this.setAttribute('aria-label', 'Hide password');
            } else {
                if (openEye) openEye.style.display = 'block';
                if (closedEye) closedEye.style.display = 'none';
                this.setAttribute('aria-label', 'Show password');
            }
        });
    }

    const dismissBtn = document.querySelector('[data-action="dismiss-alert"]');
    if (dismissBtn) {
        dismissBtn.addEventListener('click', function() {
            const alert = document.getElementById('errorAlert');
            if (alert) {
                alert.classList.add('dismissing');
                setTimeout(() => {
                    alert.remove();
                }, 400);
            }
        });
    }
});

// This is the public/js/admin-login.js file, which handles the password visibility toggle and alert dismissal functionality for the admin login page. It ensures that the eye icon reflects the current state of the password input field and provides a smooth user experience when dismissing error alerts.