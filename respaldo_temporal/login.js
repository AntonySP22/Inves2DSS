document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const loginError = document.getElementById('loginError');
    
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
        
        // Validar el campo de correo
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email.value)) {
            email.classList.add('is-invalid');
            isValid = false;
            event.preventDefault();
        } else {
            email.classList.remove('is-invalid');
        }
        
        // Validar el campo de contraseña
        if (password.value.trim() === '') {
            password.classList.add('is-invalid');
            isValid = false;
            event.preventDefault();
        } else {
            password.classList.remove('is-invalid');
        }
        
        // Si no es válido, mostrar mensaje de error
        if (!isValid) {
            loginError.style.display = 'block';
        } else {
            loginError.style.display = 'none';
        }
    });
    
    // Limpiar mensaje de error al escribir en el campo de correo
    email.addEventListener('input', function() {
        this.classList.remove('is-invalid');
        loginError.style.display = 'none';
    });
    
    // Manejar campo de contraseña de forma diferente (para no ocultar el ícono)
    password.addEventListener('input', function() {
        this.classList.remove('is-invalid');
        loginError.style.display = 'none';
        // No manipulamos el nextElementSibling aquí para evitar ocultar el ícono
    });
});