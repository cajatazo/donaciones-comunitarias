document.addEventListener('DOMContentLoaded', function() {
    // Validación específica para el formulario de registro
    const registerForm = document.querySelector('form[action*="/auth/register"]');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                confirmPassword.classList.add('is-invalid');
                
                // Crear mensaje de error si no existe
                if (!confirmPassword.nextElementSibling || !confirmPassword.nextElementSibling.classList.contains('invalid-feedback')) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback';
                    errorDiv.textContent = 'Las contraseñas no coinciden';
                    confirmPassword.parentNode.insertBefore(errorDiv, confirmPassword.nextSibling);
                }
            } else {
                confirmPassword.classList.remove('is-invalid');
            }
        });
    }
    
    // Validación específica para el formulario de login
    const loginForm = document.querySelector('form[action*="/auth/login"]');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            
            if (!email.value.trim() || !password.value.trim()) {
                e.preventDefault();
                
                if (!email.value.trim()) {
                    email.classList.add('is-invalid');
                }
                
                if (!password.value.trim()) {
                    password.classList.add('is-invalid');
                }
            }
        });
    }
});