<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="icon" href="../Natys/Assets/img/natys.png" type="image/x-icon">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        :root {
            --primary-color: rgb(204, 29, 29);
            --secondary-color: rgb(204, 29, 29);
            --bg-color: #121212;
            --card-bg: #1e1e1e;
            --text-color: #f8f9fa;
            --input-bg: #ffffff;
            --input-text: #212529;
            --link-color: #000000;
        }
        
        /* Estilos para el modo claro */
        [data-theme="light"] {
            --bg-color: #f8f9fa;
            --card-bg: #ffffff;
            --text-color: #212529;
            --input-bg: #ffffff;
            --input-text: #212529;
            --link-color: #000000;
        }
        
        /* Estilos para el modo oscuro */
        :root:not([data-theme="light"]) .card-body a {
            --link-color: #ffffff !important;
        }
        
        body {
            background-color: var(--bg-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            color: var(--text-color);
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        .auth-card {
            border-radius: 12px;
            overflow: hidden;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            background-color: var(--card-bg);
        }
        
        .card-header {
            padding: 1.5rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }
        
        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 1px solid #ced4da;
            background-color: var(--input-bg);
            color: var(--input-text);
        }
        
        .form-control:focus {
            background-color: var(--input-bg);
            color: var(--input-text);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(204, 29, 29, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 0.75rem;
            border-radius: 8px;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
        }
        
        .password-toggle {
            position: relative;
        }
        
        .password-toggle-icon {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }

        .auth-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .auth-logo img {
            height: 60px;
        }
        
        /* Estilos para el modal de timeout */
        #timeoutModal {
            z-index: 99999;
        }
        
        #timeoutModal .modal-body {
            text-align: center;
            font-size: 1.2rem;
        }
        
        #countdown {
            font-weight: bold;
            color: var(--primary-color);
            font-size: 1.5rem;
        }
        
        .form-check-label {
            color: var(--text-color);
        }
        
        .card-body a {
            color: var(--link-color) !important;
            text-decoration: none;
        }
        
        .card-body a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="auth-logo mb-4">
                    <img src="../Natys/Assets/img/Natys.png" alt="Logo" class="img-fluid">
                </div>
                
                <div class="text-end mb-3">
                    <button id="themeToggle" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-moon"></i> Modo Oscuro
                    </button>
                </div>
                
                <div class="auth-card card">
                    <div class="card-header text-white text-center">
                        <h4 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión</h4>
                    </div>
                    <div class="card-body p-4">
                        <form id="formLogin" method="POST" action="index.php?url=login&action=autenticar">
                            <div class="mb-3">
                                <label for="usuario" class="form-label">Usuario</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Ingresa tu usuario" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="clave" class="form-label">Contraseña</label>
                                <div class="input-group password-toggle">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="clave" name="clave" placeholder="Ingresa tu contraseña" required>
                                    <span class="password-toggle-icon"><i class="fas fa-eye"></i></span>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="rememberMe">
                                    <label class="form-check-label" for="rememberMe">Recordarme</label>
                                </div>
                                <a href="index.php?url=login&action=mostrarRecuperar" class="text-decoration-none">¿Olvidaste tu contraseña?</a>
                            </div>
                            <div class="d-grid gap-2 mb-3">
                                <button type="submit" class="btn btn-primary">
                                    <span class="submit-text">Ingresar</span>
                                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para advertencia de timeout -->
    <div class="modal fade" id="timeoutModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Advertencia de Inactividad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Tu sesión está a punto de expirar por inactividad.</p>
                    <p>Serás redirigido al login en <span id="countdown">60</span> segundos.</p>
                    <p>Mueve el mouse o presiona una tecla para continuar.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="stayLoggedIn">Continuar sesión</button>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <script>
    // Función para cambiar el tema
    function setTheme(theme) {
        if (theme === 'light') {
            document.documentElement.setAttribute('data-theme', 'light');
            document.querySelector('#themeToggle').innerHTML = '<i class="fas fa-moon"></i> Modo Oscuro';
            localStorage.setItem('theme', 'light');
        } else {
            document.documentElement.removeAttribute('data-theme');
            document.querySelector('#themeToggle').innerHTML = '<i class="fas fa-sun"></i> Modo Claro';
            localStorage.setItem('theme', 'dark');
        }
    }
    
    // Verificar tema guardado o preferencia del sistema
    const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark');
    setTheme(savedTheme);
    
    // Alternar tema al hacer clic en el botón
    document.getElementById('themeToggle').addEventListener('click', function() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        setTheme(currentTheme === 'light' ? 'dark' : 'light');
    });
    
    $(document).ready(function() {
        // Mostrar mensaje de error si existe
        <?php if (isset($_SESSION['error_login'])): ?>
            toastr.error('<?php echo $_SESSION['error_login']; ?>');
            <?php unset($_SESSION['error_login']); ?>
        <?php endif; ?>
        
        // Alternar visibilidad de contraseña
        $('.password-toggle-icon').click(function() {
            const input = $(this).siblings('input');
            const icon = $(this).find('i');
            
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });
        
        // Manejar el envío del formulario
        $('#formLogin').submit(function(e) {
            e.preventDefault();
            
            // Mostrar spinner
            const submitBtn = $(this).find('button[type="submit"]');
            const submitText = submitBtn.find('.submit-text');
            const spinner = submitBtn.find('.spinner-border');
            
            submitText.addClass('d-none');
            spinner.removeClass('d-none');
            submitBtn.prop('disabled', true);
            
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastr.success('Autenticación exitosa, redirigiendo...');
                        setTimeout(function() {
                            window.location.href = response.redirect;
                        }, 1500);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error('Error en la solicitud: ' + error);
                    console.error('Error:', error, xhr.responseText);
                },
                complete: function() {
                    // Restaurar el botón
                    submitText.removeClass('d-none');
                    spinner.addClass('d-none');
                    submitBtn.prop('disabled', false);
                }
            });
        });

        // =============================================
        // Sistema de Timeout por Inactividad
        // =============================================
        const inactiveTimeout = 300000; // 5 minutos en milisegundos
        const warningTimeout = 240000; // 4 minutos en milisegundos (muestra advertencia 1 minuto antes)
        
        let inactivityTimer;
        let warningTimer;
        let countdownInterval;
        const timeoutModal = new bootstrap.Modal(document.getElementById('timeoutModal'));
        
        // Función para reiniciar los timers
        function resetInactivityTimers() {
            // Limpiar timers existentes
            clearTimeout(inactivityTimer);
            clearTimeout(warningTimer);
            clearInterval(countdownInterval);
            
            // Ocultar modal si está visible
            timeoutModal.hide();
            
            // Configurar nuevo timer de advertencia
            warningTimer = setTimeout(showTimeoutWarning, warningTimeout);
            
            // Configurar nuevo timer de logout
            inactivityTimer = setTimeout(logoutDueToInactivity, inactiveTimeout);
        }
        
        // Mostrar advertencia de timeout
        function showTimeoutWarning() {
            // Mostrar modal
            timeoutModal.show();
            
            // Configurar cuenta regresiva
            let secondsLeft = 60;
            $('#countdown').text(secondsLeft);
            
            countdownInterval = setInterval(function() {
                secondsLeft--;
                $('#countdown').text(secondsLeft);
                
                if (secondsLeft <= 0) {
                    clearInterval(countdownInterval);
                }
            }, 1000);
        }
        
        // Cerrar sesión por inactividad
        function logoutDueToInactivity() {
            clearInterval(countdownInterval);
            timeoutModal.hide();
            
            toastr.error('Tu sesión ha expirado por inactividad', 'Sesión cerrada', {
                timeOut: 5000,
                onHidden: function() {
                    window.location.href = 'index.php?url=login';
                }
            });
        }
        
        // Eventos que reinician el contador de inactividad
        $(document).on('mousemove keydown click scroll', function() {
            resetInactivityTimers();
        });
        
        // Botón para continuar sesión
        $('#stayLoggedIn').click(function() {
            resetInactivityTimers();
            timeoutModal.hide();
        });
        
        // Iniciar timers al cargar la página
        resetInactivityTimers();
        
        // Manejar respuesta AJAX para warnings de timeout del servidor
        $(document).ajaxSuccess(function(event, xhr, settings) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.timeout_warning) {
                    toastr.warning(response.message, 'Advertencia', {
                        timeOut: response.remaining * 1000,
                        closeButton: true
                    });
                } else if (response.timeout) {
                    toastr.error(response.message, 'Sesión expirada', {
                        timeOut: 5000,
                        onHidden: function() {
                            window.location.href = response.redirect;
                        }
                    });
                }
            } catch (e) {
                // No es una respuesta JSON o no es relevante
            }
        });
    });
    </script>
</body>
</html>