document.addEventListener('DOMContentLoaded', function() {
    // Validación para el formulario de perfil de organización
    const orgProfileForms = document.querySelectorAll('form[action*="/organizacion/perfil"]');
    orgProfileForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const rucInput = form.querySelector('input[name="ruc"]');
            
            if (rucInput && rucInput.value.length > 0 && rucInput.value.length !== 11) {
                e.preventDefault();
                rucInput.classList.add('is-invalid');
                
                if (!rucInput.nextElementSibling || !rucInput.nextElementSibling.classList.contains('invalid-feedback')) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback';
                    errorDiv.textContent = 'El RUC debe tener 11 dígitos';
                    rucInput.parentNode.insertBefore(errorDiv, rucInput.nextSibling);
                }
            } else if (rucInput) {
                rucInput.classList.remove('is-invalid');
            }
        });
    });
});