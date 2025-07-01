<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <link rel="icon" href="../Natys/Assets/img/natys.png" type="image/x-icon">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        :root {
            --primary-color:rgb(243, 60, 60);
            --secondary-color:rgb(243, 60, 60);
        }
        
        body {
            background-color: #f5f7fb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .auth-card {
            border-radius: 12px;
            overflow: hidden;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            padding: 1.5rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }
        
        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
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
        
        .auth-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .auth-logo img {
            height: 60px;
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
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="auth-logo">
                    <img src="../Natys/Assets/img/Natys.png" alt="Logo" class="img-fluid">
                </div>
                
                <div class="auth-card card">
                    <div class="card-header text-white text-center">
                        <h4 class="mb-0"><i class="fas fa-key me-2"></i>Recuperar Contraseña</h4>
                    </div>
                    <div class="card-body p-4">
                        <form id="formRecuperar" method="POST" action="index.php?url=login&action=solicitarRecuperacion">
                            <div class="mb-3">
                                <p class="text-muted">Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.</p>
                            </div>
                            <div class="mb-3">
                                <label for="correo" class="form-label">Correo Electrónico</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="correo" name="correo" placeholder="tucorreo@ejemplo.com" required>
                                </div>
                            </div>
                            <div class="d-grid gap-2 mb-3">
                                <button type="submit" class="btn btn-primary">
                                    <span class="submit-text">Enviar Enlace</span>
                                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                </button>
                            </div>
                            <div class="text-center">
                                <a href="index.php?url=login" class="text-decoration-none"><i class="fas fa-arrow-left me-1"></i> Volver al login</a>
                            </div>
                        </form>
                    </div>
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
    $(document).ready(function() {
        // Manejar el envío del formulario de recuperación
        $('#formRecuperar').submit(function(e) {
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
                        toastr.success(response.message);
                        
                        // Reemplazar el formulario con campos para nueva contraseña
                        $('#formRecuperar').html(`
                            <div class="mb-3">
                                <label for="clave" class="form-label">Nueva Contraseña</label>
                                <div class="input-group password-toggle">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="clave" name="clave" required>
                                    <span class="password-toggle-icon"><i class="fas fa-eye"></i></span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="confirmar_clave" class="form-label">Confirmar Contraseña</label>
                                <div class="input-group password-toggle">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="confirmar_clave" name="confirmar_clave" required>
                                    <span class="password-toggle-icon"><i class="fas fa-eye"></i></span>
                                </div>
                            </div>
                            <input type="hidden" name="correo" value="${$('#correo').val()}">
                            <div class="d-grid gap-2 mb-3">
                                <button type="submit" class="btn btn-primary">
                                    <span class="submit-text">Cambiar Contraseña</span>
                                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                </button>
                                <div class="text-center">
                                <a href="index.php?url=login" class="text-decoration-none"><i class="fas fa-arrow-left me-1"></i> Volver al login</a>
                            </div>
                            </div>
                        `);
                        
                        $('#formRecuperar').attr('action', 'index.php?url=login&action=cambiarClave');
                        
                        // Configurar el toggle de visibilidad para los nuevos campos
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
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error('Error en la solicitud');
                    console.error(error);
                },
                complete: function() {
                    // Restaurar el botón
                    submitText.removeClass('d-none');
                    spinner.addClass('d-none');
                    submitBtn.prop('disabled', false);
                }
            });
        });
        
        // Alternar visibilidad de contraseña (para cuando se cargue el segundo formulario)
        $(document).on('click', '.password-toggle-icon', function() {
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
    });
    </script>
</body>
</html>