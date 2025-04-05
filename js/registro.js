document.addEventListener('DOMContentLoaded', function() {
    // Obtener los elementos del formulario
    const form = document.getElementById('registrationForm');
    const nombre = document.getElementById('nombre');
    const correo = document.getElementById('correo');
    const contrasena = document.getElementById('contrasena');
    const confirmarContrasena = document.getElementById('confirmar_contrasena');
    const toggleButtons = document.querySelectorAll('.toggle-password');
    
    // Función para mostrar/ocultar la contraseña
    document.querySelectorAll('.toggle-password').forEach(icon => {
        icon.addEventListener('click', () => {
            const targetId = icon.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        });
    });
    
    // Validar el formulario al enviarlo
    form.addEventListener('submit', function(event) {
        let isValid = true;
        
        // Validar el campo de nombre
        if (nombre.value.trim() === '') {
            nombre.classList.add('is-invalid');
            nombre.nextElementSibling.style.display = 'block';
            isValid = false;
            event.preventDefault();
        } else {
            nombre.classList.remove('is-invalid');
            nombre.nextElementSibling.style.display = 'none';
        }
        
        // Validar el campo de correo
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(correo.value)) {
            correo.classList.add('is-invalid');
            correo.nextElementSibling.style.display = 'block';
            isValid = false;
            event.preventDefault();
        } else {
            correo.classList.remove('is-invalid');
            correo.nextElementSibling.style.display = 'none';
        }
        
        // Validar el campo de contraseña
        if (contrasena.value.length < 8) {
            contrasena.classList.add('is-invalid');
            contrasena.parentElement.nextElementSibling.style.display = 'block';
            isValid = false;
            event.preventDefault();
        } else {
            contrasena.classList.remove('is-invalid');
            contrasena.parentElement.nextElementSibling.style.display = 'none';
        }
        
        // Validar que las contraseñas coincidan
        if (contrasena.value !== confirmarContrasena.value) {
            confirmarContrasena.classList.add('is-invalid');
            confirmarContrasena.parentElement.nextElementSibling.style.display = 'block';
            isValid = false;
            event.preventDefault();
        } else {
            confirmarContrasena.classList.remove('is-invalid');
            confirmarContrasena.parentElement.nextElementSibling.style.display = 'none';
        }
    });
    
    // Limpiar los mensajes de error al escribir
    [nombre, correo].forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid');
            if (this.nextElementSibling) {
                this.nextElementSibling.style.display = 'none';
            }
        });
    });

    // Manejar campos de contraseña de forma diferente
    [contrasena, confirmarContrasena].forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid');
            // Accedemos al div padre y luego al siguiente elemento (mensaje de error)
            const feedbackElement = this.parentElement.nextElementSibling;
            if (feedbackElement && feedbackElement.classList.contains('invalid-feedback')) {
                feedbackElement.style.display = 'none';
            }
        });
    });
});