<?php
ob_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil - <?php echo htmlspecialchars($_SESSION['usuario']['usuario']); ?></title>
    <link rel="icon" href="../Natys/Assets/img/natys.png" type="image/x-icon">
    <link rel="stylesheet" href="Assets/css/perfil.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .profile-card {
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        .profile-card:hover {
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.15);
        }
        .profile-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 5px solid #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .info-label {
            font-weight: 600;
            color: #495057;
        }
        .info-value {
            color: #6c757d;
        }
        .btn-change-password {
            background-color: #ffc107;
            border-color: #ffc107;
        }
        .btn-change-password:hover {
            background-color: #e0a800;
            border-color: #d39e00;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card profile-card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-user-circle me-2"></i>Mi Perfil</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 text-center mb-4 mb-md-0">
                                <img src="/Natys/Assets/img/john_doe.png" alt="Foto de perfil" class="profile-img rounded-circle mb-3" onerror="this.src='/Natys/Assets/img/avatar.png'">
                                <h5 class="mb-1"><?php echo htmlspecialchars($_SESSION['usuario']['usuario']); ?></h5>
                                <p class="text-muted mb-3"><?php echo htmlspecialchars(ucfirst($_SESSION['usuario']['rol'])); ?></p>
                                
                                <?php if(in_array($_SESSION['usuario']['rol'], ['admin', 'superadmin'])): ?>
                                    <a href="index.php?url=perfil&action=listar" class="btn btn-primary btn-sm">
                                        <i class="fas fa-users-cog me-1"></i> Gestionar Usuarios
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-8">
                                <div class="row mb-3">
                                    <div class="col-sm-4 info-label">Nombre de Usuario:</div>
                                    <div class="col-sm-8 info-value"><?php echo htmlspecialchars($_SESSION['usuario']['usuario']); ?></div>
                                </div>
                                <hr>
                                <div class="row mb-3">
                                    <div class="col-sm-4 info-label">Correo Electrónico:</div>
                                    <div class="col-sm-8 info-value"><?php echo htmlspecialchars($_SESSION['usuario']['correo'] ?? 'No especificado'); ?></div>
                                </div>
                                <hr>
                                <div class="row mb-3">
                                    <div class="col-sm-4 info-label">Rol:</div>
                                    <div class="col-sm-8 info-value">
                                        <span class="badge bg-<?php echo $_SESSION['usuario']['rol'] === 'admin' ? 'success' : 'info'; ?>">
                                            <?php echo htmlspecialchars(ucfirst($_SESSION['usuario']['rol'])); ?>
                                        </span>
                                    </div>
                                </div>
                                <hr>
                                <div class="row mb-3">
                                    <div class="col-sm-4 info-label">Último acceso:</div>
                                    <div class="col-sm-8 info-value">
                                        <?php 
                                            if(isset($_SESSION['last_activity'])) {
                                                echo date('d/m/Y H:i:s', $_SESSION['last_activity']);
                                            } else {
                                                echo 'No disponible';
                                            }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between">
                            <a href="index.php?url=home" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Volver al Inicio
                            </a>
                            <button type="button" id="btnCambiarClave" class="btn btn-change-password">
                                <i class="fas fa-key me-1"></i> Cambiar Contraseña
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para cambiar contraseña -->
    <div class="modal fade" id="modalCambiarClave" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-key me-2"></i>Cambiar Contraseña</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formCambiarClave">
                        <div class="mb-3">
                            <label for="clave_actual" class="form-label">Contraseña Actual *</label>
                            <input type="password" class="form-control" id="clave_actual" required>
                            <div class="invalid-feedback">Por favor ingrese su contraseña actual</div>
                        </div>
                        <div class="mb-3">
                            <label for="nueva_clave" class="form-label">Nueva Contraseña *</label>
                            <input type="password" class="form-control" id="nueva_clave" required minlength="6">
                            <div class="invalid-feedback">La contraseña debe tener al menos 6 caracteres</div>
                        </div>
                        <div class="mb-3">
                            <label for="confirmar_clave" class="form-label">Confirmar Nueva Contraseña *</label>
                            <input type="password" class="form-control" id="confirmar_clave" required minlength="6">
                            <div class="invalid-feedback">Las contraseñas deben coincidir</div>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i> Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
    $(document).ready(function() {
        // Mostrar modal para cambiar contraseña
        $('#btnCambiarClave').click(function() {
            $('#modalCambiarClave').modal('show');
        });
        
        // Validación del formulario
        $('#formCambiarClave').submit(function(e) {
            e.preventDefault();
            
            const form = this;
            if (!form.checkValidity()) {
                e.stopPropagation();
                form.classList.add('was-validated');
                return;
            }
            
            const claveActual = $('#clave_actual').val();
            const nuevaClave = $('#nueva_clave').val();
            const confirmarClave = $('#confirmar_clave').val();
            
            if (nuevaClave !== confirmarClave) {
                $('#confirmar_clave').addClass('is-invalid');
                toastr.error('Las contraseñas no coinciden');
                return;
            } else {
                $('#confirmar_clave').removeClass('is-invalid');
            }
            
            $.ajax({
                url: 'index.php?url=perfil&action=cambiarClave',
                method: 'POST',
                data: {
                    clave_actual: claveActual,
                    nueva_clave: nuevaClave,
                    confirmar_clave: confirmarClave
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#modalCambiarClave').modal('hide');
                        $('#formCambiarClave')[0].reset();
                        $('#formCambiarClave').removeClass('was-validated');
                    } else {
                        toastr.error(response.message);
                        $('#clave_actual').addClass('is-invalid');
                    }
                },
                error: function() {
                    toastr.error('Error al procesar la solicitud');
                }
            });
        });
        
        // Resetear validación al cerrar modal
        $('#modalCambiarClave').on('hidden.bs.modal', function () {
            $('#formCambiarClave')[0].reset();
            $('#formCambiarClave').removeClass('was-validated');
            $('#formCambiarClave input').removeClass('is-invalid');
        });
    });
    </script>
</body>
</html>

<?php
$content = ob_get_clean();
include 'Assets/layouts/base.php';