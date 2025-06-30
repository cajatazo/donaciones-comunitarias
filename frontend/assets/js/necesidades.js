document.addEventListener('DOMContentLoaded', function() {
    // Validación para el formulario de necesidades
    const necesidadForms = document.querySelectorAll('form[action*="/organizacion/necesidades"]');
    necesidadForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const cantidadInput = form.querySelector('input[name="cantidad_necesaria"]');
            const fechaInput = form.querySelector('input[name="fecha_limite"]');
            
            if (parseInt(cantidadInput.value) <= 0) {
                e.preventDefault();
                cantidadInput.classList.add('is-invalid');
                
                if (!cantidadInput.nextElementSibling || !cantidadInput.nextElementSibling.classList.contains('invalid-feedback')) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback';
                    errorDiv.textContent = 'La cantidad debe ser mayor a cero';
                    cantidadInput.parentNode.insertBefore(errorDiv, cantidadInput.nextSibling);
                }
            } else {
                cantidadInput.classList.remove('is-invalid');
            }
            
            const today = new Date().toISOString().split('T')[0];
            if (fechaInput.value < today) {
                e.preventDefault();
                fechaInput.classList.add('is-invalid');
                
                if (!fechaInput.nextElementSibling || !fechaInput.nextElementSibling.classList.contains('invalid-feedback')) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback';
                    errorDiv.textContent = 'La fecha no puede ser anterior a hoy';
                    fechaInput.parentNode.insertBefore(errorDiv, fechaInput.nextSibling);
                }
            } else {
                fechaInput.classList.remove('is-invalid');
            }
        });
    });
    
    // Mostrar confirmación antes de eliminar una necesidad
    const deleteButtons = document.querySelectorAll('form[action*="/organizacion/necesidades/eliminar"]');
    deleteButtons.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('¿Estás seguro de eliminar esta necesidad? Esta acción no se puede deshacer.')) {
                e.preventDefault();
            }
        });
    });
});