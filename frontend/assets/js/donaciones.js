document.addEventListener('DOMContentLoaded', function() {
    // Manejar el formulario de donación
    const donacionForms = document.querySelectorAll('form[action*="/donante/donar"]');
    donacionForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const cantidadInput = form.querySelector('input[name="cantidad"]');
            const max = parseInt(cantidadInput.max);
            const value = parseInt(cantidadInput.value);
            
            if (value > max) {
                e.preventDefault();
                cantidadInput.classList.add('is-invalid');
                
                // Crear mensaje de error si no existe
                if (!cantidadInput.nextElementSibling || !cantidadInput.nextElementSibling.classList.contains('invalid-feedback')) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback';
                    errorDiv.textContent = `La cantidad máxima disponible es ${max}`;
                    cantidadInput.parentNode.insertBefore(errorDiv, cantidadInput.nextSibling);
                }
            } else {
                cantidadInput.classList.remove('is-invalid');
            }
        });
    });
    
    // Mostrar/ocultar campos adicionales según el estado de la donación
    const estadoSelects = document.querySelectorAll('select[name="estado"]');
    estadoSelects.forEach(select => {
        const modalId = select.closest('.modal').id;
        const fechaContainer = document.querySelector(`#${modalId} [id^="fechaEntregaContainer"]`);
        
        select.addEventListener('change', function() {
            if (this.value === 'entregada') {
                fechaContainer.style.display = 'block';
            } else {
                fechaContainer.style.display = 'none';
            }
        });
    });
});