<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DROPZONE - Iniciar Sesión</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700;900&family=Exo+2:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --black: #000000;
            --dark-gray: #1A1A1A;
            --white: #FFFFFF;
            --gold: #C8A032;
            --medium-gray: #333333;
            --accent: #FF5E14;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background-color: var(--black);
            color: var(--white);
            line-height: 1.6;
            overflow-x: hidden;
            font-family: 'Exo 2', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(rgba(0, 0, 0, 0.85), rgba(0, 0, 0, 0.85)), 
                        url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect width="100" height="100" fill="%231A1A1A"/><path d="M0 0L100 100M100 0L0 100" stroke="%23333333" stroke-width="1"/></svg>');
            padding: 20px;
        }
        
        .auth-container {
            width: 100%;
            max-width: 450px;
        }
        
        .auth-card {
            background-color: var(--dark-gray);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            border: 1px solid var(--medium-gray);
            position: relative;
        }
        
        .auth-header {
            background: linear-gradient(135deg, var(--black) 0%, var(--dark-gray) 100%);
            padding: 2.5rem 2rem;
            text-align: center;
            border-bottom: 2px solid var(--gold);
            position: relative;
            overflow: hidden;
            border-radius: 15px 15px 0 0;
        }
        
        .auth-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(200, 160, 50, 0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }
        
        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .logo {
            font-size: 2.5rem;
            font-weight: 900;
            color: var(--white);
            text-transform: uppercase;
            letter-spacing: 2px;
            font-family: 'Orbitron', sans-serif;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 2;
        }
        
        .logo span {
            color: var(--gold);
        }
        
        .auth-header p {
            color: rgba(255, 255, 255, 0.8);
            position: relative;
            z-index: 2;
        }
        
        .auth-body {
            padding: 2.5rem 2rem;
        }
        
        .form-container {
            position: relative;
            min-height: auto;
        }
        
        .auth-form {
            display: none;
            width: 100%;
        }
        
        .auth-form.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--white);
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 0.9rem 1.2rem;
            background-color: var(--black);
            border: 1px solid var(--medium-gray);
            border-radius: 6px;
            color: var(--white);
            font-size: 1rem;
            transition: all 0.3s;
            font-family: 'Exo 2', sans-serif;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 2px rgba(200, 160, 50, 0.2);
        }
        
        .password-container {
            position: relative;
        }
        
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--white);
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.3s;
        }
        
        .toggle-password:hover {
            opacity: 1;
        }
        
        .btn {
            display: block;
            width: 100%;
            background-color: var(--gold);
            color: var(--black);
            padding: 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-family: 'Exo 2', sans-serif;
            margin-bottom: 1rem;
        }
        
        .btn:hover {
            background-color: var(--white);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(200, 160, 50, 0.4);
        }
        
        /* Botones de Social Login */
        .social-login {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin: 1.5rem 0;
        }
        
        .btn-social {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            border: none;
            color: var(--white);
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Exo 2', sans-serif;
            font-weight: 600;
            font-size: 1rem;
            width: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .btn-social::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-social:hover::before {
            left: 100%;
        }
        
        .btn-social:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }
        
        .btn-social i {
            font-size: 1.2rem;
            min-width: 20px;
        }
        
        .btn-discord {
            background: linear-gradient(135deg, #5865F2 0%, #4752c4 100%);
            border: 2px solid #5865F2;
        }
        
        .btn-discord:hover {
            background: linear-gradient(135deg, #4752c4 0%, #3c45a5 100%);
            border-color: #4752c4;
        }
        
        .btn-telegram {
            background: linear-gradient(135deg, #0088cc 0%, #0077b3 100%);
            border: 2px solid #0088cc;
        }
        
        .btn-telegram:hover {
            background: linear-gradient(135deg, #0077b3 0%, #006699 100%);
            border-color: #0077b3;
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--medium-gray);
        }
        
        .divider span {
            padding: 0 1rem;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .auth-footer p {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 0.5rem;
        }
        
        .toggle-form {
            color: var(--gold);
            background: none;
            border: none;
            cursor: pointer;
            font-weight: 600;
            text-decoration: underline;
            font-family: 'Exo 2', sans-serif;
            font-size: 1rem;
            transition: color 0.3s;
        }
        
        .toggle-form:hover {
            color: var(--white);
        }
        
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .remember-me input {
            accent-color: var(--gold);
        }
        
        .forgot-password {
            color: var(--gold);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s;
        }
        
        .forgot-password:hover {
            color: var(--white);
        }
        
        .terms {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.6);
            margin-top: 1rem;
            text-align: center;
        }
        
        .terms a {
            color: var(--gold);
            text-decoration: none;
        }
        
        .terms a:hover {
            text-decoration: underline;
        }
        
        /* Badge de métodos populares */
        .popular-badge {
            position: absolute;
            top: -8px;
            right: 10px;
            background: var(--gold);
            color: var(--black);
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: bold;
            transform: rotate(5deg);
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .auth-container {
                margin: 0;
            }
            
            .auth-header {
                padding: 2rem 1.5rem;
            }
            
            .auth-body {
                padding: 2rem 1.5rem;
            }
            
            .logo {
                font-size: 2rem;
            }
            
            .social-login {
                gap: 0.8rem;
            }
            
            .btn-social {
                padding: 0.9rem 1.2rem;
                font-size: 0.95rem;
            }
        }
        
        /* Animaciones adicionales */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .btn-social:active {
            animation: pulse 0.3s ease;
        }


        /* Mensajes de error */
.error-message {
    background-color: rgba(255, 59, 59, 0.1);
    border: 1px solid rgba(255, 59, 59, 0.3);
    color: #ff3b3b;
    padding: 0.75rem;
    border-radius: 6px;
    margin-bottom: 1rem;
    text-align: center;
    font-size: 0.9rem;
}

.success-message {
    background-color: rgba(72, 187, 120, 0.1);
    border: 1px solid rgba(72, 187, 120, 0.3);
    color: #48bb78;
    padding: 0.75rem;
    border-radius: 6px;
    margin-bottom: 1rem;
    text-align: center;
    font-size: 0.9rem;
}


    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo">DROP<span>ZONE</span></div>
                <p>Tu plataforma de recargas para juegos</p>
            </div>
            
            <div class="auth-body">
                <div class="form-container">
                    <!-- Formulario de Login -->
                    <form class="auth-form login active" id="loginForm">
                        <div class="form-group">
                            <label for="loginEmail">Correo Electrónico o Usuario</label>
                            <input type="text" id="loginEmail" class="form-control" placeholder="tu@email.com o usuario" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="loginPassword">Contraseña</label>
                            <div class="password-container">
                                <input type="password" id="loginPassword" class="form-control" placeholder="Tu contraseña" required>
                                <button type="button" class="toggle-password" id="toggleLoginPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-options">
                            <div class="remember-me">
                                <input type="checkbox" id="rememberMe">
                                <label for="rememberMe">Recordarme</label>
                            </div>
                            <a href="#" class="forgot-password">¿Olvidaste tu contraseña?</a>
                        </div>
                        
                        <button type="submit" class="btn">Iniciar Sesión</button>
                        
                        <div class="divider">
                            <span>O inicia sesión con</span>
                        </div>
                        
                        <div class="social-login">
                            <button type="button" class="btn-social btn-discord" onclick="loginWithDiscord()">
                                <i class="fab fa-discord"></i>
                                Continuar con Discord
                                <span class="popular-badge">POPULAR</span>
                            </button>
                            <button type="button" class="btn-social btn-telegram" id="telegramLogin">
                                <i class="fab fa-telegram"></i>
                                Continuar con Telegram
                            </button>
                        </div>
                        
                        <div class="auth-footer">
                            <p>¿No tienes una cuenta? <button type="button" class="toggle-form" data-form="register">Regístrate</button></p>
                        </div>
                    </form>
                    
                    <!-- Formulario de Registro -->
                    <form class="auth-form register" id="registerForm">
                        <div class="form-group">
                            <label for="registerName">Nombre Completo</label>
                            <input type="text" id="registerName" class="form-control" placeholder="Tu nombre completo" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="registerUsername">Usuario</label>
                            <input type="text" id="registerUsername" class="form-control" placeholder="Nombre de usuario" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="registerEmail">Correo Electrónico</label>
                            <input type="email" id="registerEmail" class="form-control" placeholder="tu@email.com" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="registerPassword">Contraseña</label>
                            <div class="password-container">
                                <input type="password" id="registerPassword" class="form-control" placeholder="Crea una contraseña segura" required>
                                <button type="button" class="toggle-password" id="toggleRegisterPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="registerConfirmPassword">Confirmar Contraseña</label>
                            <div class="password-container">
                                <input type="password" id="registerConfirmPassword" class="form-control" placeholder="Repite tu contraseña" required>
                                <button type="button" class="toggle-password" id="toggleRegisterConfirmPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn">Crear Cuenta</button>
                        
                        <div class="divider">
                            <span>O regístrate con</span>
                        </div>
                        
                        <div class="social-login">
                            <button type="button" class="btn-social btn-discord" onclick="loginWithDiscord()">
                                <i class="fab fa-discord"></i>
                                Registrarse con Discord
                                <span class="popular-badge">POPULAR</span>
                            </button>
                            <button type="button" class="btn-social btn-telegram" id="telegramRegister">
                                <i class="fab fa-telegram"></i>
                                Registrarse con Telegram
                            </button>
                        </div>
                        
                        <div class="terms">
                            Al registrarte, aceptas nuestros <a href="#">Términos de Servicio</a> y <a href="#">Política de Privacidad</a>.
                        </div>
                        
                        <div class="auth-footer">
                            <p>¿Ya tienes una cuenta? <button type="button" class="toggle-form" data-form="login">Inicia Sesión</button></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle entre formularios de Login y Registro
        document.querySelectorAll('.toggle-form').forEach(button => {
            button.addEventListener('click', function() {
                const targetForm = this.getAttribute('data-form');
                
                // Ocultar todos los formularios
                document.querySelectorAll('.auth-form').forEach(form => {
                    form.classList.remove('active');
                });
                
                // Mostrar el formulario objetivo
                document.getElementById(`${targetForm}Form`).classList.add('active');
            });
        });
        
        // Toggle para mostrar/ocultar contraseñas
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.parentElement.querySelector('input');
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
        
      // Manejo del formulario de Login
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    
    // Validación básica
    if (!email || !password) {
        showMessage('Por favor, completa todos los campos', 'error');
        return;
    }
    
    // Mostrar loading
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Iniciando sesión...';
    submitBtn.disabled = true;
    
    // Enviar datos al servidor
    const formData = new FormData();
    formData.append('email', email);
    formData.append('password', password);
    
    fetch('auth/login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (response.redirected) {
            // Redirección exitosa
            window.location.href = response.url;
        } else if (response.ok) {
            return response.text();
        } else {
            throw new Error('Error en el servidor: ' + response.status);
        }
    })
    .then(data => {
        if (data) {
            // Si hay datos (no redirección), mostrar mensaje
            showMessage(data, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error al conectar con el servidor', 'error');
    })
    .finally(() => {
        // Restaurar botón
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
});

// Manejo del formulario de Registro
document.getElementById('registerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const name = document.getElementById('registerName').value;
    const username = document.getElementById('registerUsername').value;
    const email = document.getElementById('registerEmail').value;
    const password = document.getElementById('registerPassword').value;
    const confirmPassword = document.getElementById('registerConfirmPassword').value;
    
    // Validación básica
    if (password !== confirmPassword) {
        showMessage('Las contraseñas no coinciden', 'error');
        return;
    }
    
    if (password.length < 6) {
        showMessage('La contraseña debe tener al menos 6 caracteres', 'error');
        return;
    }
    
    // Mostrar loading
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Creando cuenta...';
    submitBtn.disabled = true;
    
    // Enviar datos al servidor
    const formData = new FormData();
    formData.append('name', name);
    formData.append('username', username);
    formData.append('email', email);
    formData.append('password', password);
    formData.append('confirm_password', confirmPassword);
    
    fetch('auth/register.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (response.redirected) {
            // Redirección exitosa
            window.location.href = response.url;
        } else if (response.ok) {
            return response.text();
        } else {
            throw new Error('Error en el servidor: ' + response.status);
        }
    })
    .then(data => {
        if (data) {
            showMessage(data, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error al conectar con el servidor', 'error');
    })
    .finally(() => {
        // Restaurar botón
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
});

// Función para mostrar mensajes
function showMessage(message, type) {
    // Remover mensajes anteriores
    const existingMessage = document.querySelector('.form-message');
    if (existingMessage) {
        existingMessage.remove();
    }
    
    // Crear nuevo mensaje
    const messageDiv = document.createElement('div');
    messageDiv.className = `form-message ${type === 'error' ? 'error-message' : 'success-message'}`;
    messageDiv.textContent = message;
    
    // Insertar después del header
    const authBody = document.querySelector('.auth-body');
    const formContainer = document.querySelector('.form-container');
    authBody.insertBefore(messageDiv, formContainer);
    
    // Auto-remover después de 5 segundos
    setTimeout(() => {
        if (messageDiv.parentNode) {
            messageDiv.remove();
        }
    }, 30000);
}
        
        // Función para iniciar sesión con Discord
function loginWithDiscord() {
    console.log('Iniciando login con Discord...');
    window.location.href = 'auth/discord.php';
}
        
        // Login con Telegram
        document.getElementById('telegramLogin').addEventListener('click', function() {
            alert('Iniciando autenticación con Telegram...');
        });
        
        // Registro con Telegram
        document.getElementById('telegramRegister').addEventListener('click', function() {
            alert('Iniciando registro con Telegram...');
        });
        
        // Verificar errores al cargar la página
        window.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const error = urlParams.get('error');
            
            if (error === 'auth_failed') {
                alert('❌ Error al autenticar con Discord. Por favor, intenta nuevamente.');
            } else if (error === 'no_code') {
                alert('⚠️ Error en el proceso de autenticación.');
            }
            
            // Limpiar URL después de mostrar error
            if (error) {
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });
        
        // Efecto hover mejorado para botones sociales
        document.querySelectorAll('.btn-social').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-3px)';
            });
            
            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });


        // Verificar errores al cargar la página
window.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    
    const errorMessages = {
        'invalid_credentials': '❌ Email o contraseña incorrectos',
        'password_mismatch': '❌ Las contraseñas no coinciden',
        'password_short': '❌ La contraseña debe tener al menos 6 caracteres',
        'email_exists': '❌ Este email ya está registrado',
        'username_exists': '❌ Este nombre de usuario ya está en uso',
        'registration_failed': '❌ Error en el registro, intenta nuevamente',
        'auth_failed': '❌ Error al autenticar con Discord. Por favor, intenta nuevamente.',
        'no_code': '⚠️ Error en el proceso de autenticación.'
    };
    
    if (error && errorMessages[error]) {
        alert(errorMessages[error]);
    }
    
    // Limpiar URL después de mostrar error
    if (error) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});



    </script>
</body>
</html>