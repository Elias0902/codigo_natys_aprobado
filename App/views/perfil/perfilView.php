<?php
ob_start();
?>

<!DOCTYPE html>
<html lang="es" data-theme="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Perfiles</title>
    <link rel="icon" href="../Natys/Assets/img/natys.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="/Natys/Assets/css/styles.css">
    <style>
        :root {
            --primary-color: #cc1d1d;
            --primary-dark: #a81818;
            --bg-color: #f8f9fa;
            --card-bg: #ffffff;
            --text-color: #212529;
            --input-bg: #ffffff;
            --input-text: #212529;
            --border-color: #dee2e6;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        [data-theme="dark"] {
            --bg-color: #121212;
            --card-bg: #1e1e1e;
            --text-color: #f8f9fa;
            --input-bg: #2d2d2d;
            --input-text: #f8f9fa;
            --border-color: #444;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }
        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .modal-content {
            background-color: var(--card-bg);
            color: var(--text-color);
            border: 1px solid var(--border-color);
        }
        .user-card {
            border: 1px solid var(--border-color);
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
            padding: 20px;
            margin-bottom: 20px;
        }
        .user-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border: 4px solid var(--border-color);
            border-radius: 50%;
            margin: 0 auto 15px;
            display: block;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
            padding: 2px;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .user-img:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            border-color: var(--primary-color);
        }
        .user-actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            width: 100%;
            flex-wrap: wrap;
        }
        .modal-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white !important;
            border-bottom: 1px solid var(--border-color);
        }
        .text-muted {
            color: var(--input-text) !important;
            opacity: 0.8;
            margin-top: 15px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .btn-close {
            filter: brightness(0) invert(1);
            opacity: 0.8;
        }
        
        .btn-close:hover {
            opacity: 1;
        }
        #modalZoomImagen .modal-content {
            background: transparent;
            border: none;
        }
        #modalZoomImagen .btn-close {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1060;
            filter: invert(1);
            opacity: 0.8;
        }
        #modalZoomImagen .btn-close:hover {
            opacity: 1;
        }
    </style>
</head>
<body class="dark-mode">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0"><i class="fas fa-users-cog me-2"></i>Gestión de Usuarios</h1>
            <div class="d-flex align-items-center">
                <!-- Dropdown de Reportes -->
                <div class="btn-group me-2">
                    <button type="button" id="reportesDropdownUsuarios" class="btn btn-danger dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-pdf me-2"></i> Reportes
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item text-dark" href="index.php?url=user&type=reporte&action=gestion" target="_blank">
                            <i class="fas fa-list me-2 text-dark"></i><span class="text-dark"> Gestión de Usuarios</span>
                        </a></li>
                        <li><a class="dropdown-item text-dark" href="index.php?url=user&type=reporte&action=administradores" target="_blank">
                            <i class="fas fa-user-shield me-2 text-dark"></i><span class="text-dark"> Administradores</span>
                        </a></li>
                        <li><a class="dropdown-item text-dark" href="index.php?url=user&type=reporte&action=empleados" target="_blank">
                            <i class="fas fa-users me-2 text-dark"></i><span class="text-dark"> Empleados</span>
                        </a></li>
                    </ul>
                </div>
                
                <?php if ($_SESSION['usuario']['rol'] === 'superadmin'): ?>
                    <button class="btn btn-danger me-2" id="btnRegistrarUsuario">
                        <i class="fas fa-user-plus me-1"></i>Registrar Usuario
                    </button>
                <?php endif; ?>
                <a href="index.php?url=home" class="btn btn-outline-secondary">
                    <i class="fas fa-home me-1"></i>Inicio
                </a>
            </div>
        </div>

        <div class="row">
            <?php foreach ($perfiles as $perfil): ?>
                <div class="col-md-4">
                    <div class="user-card text-center">
                        <img src="<?php echo $perfil['imagen_perfil'] ?? '/Natys/Assets/img/defaultAvatar.jpg'; ?>" 
                             alt="Foto de perfil" 
                             class="user-img"
                             onerror="this.src='/Natys/Assets/img/defaultAvatar.jpg'; this.onerror=null;">
                        <div class="user-info">
                            <h5><?php echo htmlspecialchars($perfil['usuario']); ?></h5>
                            <p class="text-muted mb-2"><?php echo htmlspecialchars($perfil['correo_usuario']); ?></p>
                            <span class="badge rounded-pill 
                                <?php 
                                echo $perfil['rol'] === 'admin' ? 'bg-danger' : 
                                     ($perfil['rol'] === 'superadmin' ? 'bg-info' : 'bg-warning text-dark'); 
                                ?>">
                                <?php echo htmlspecialchars(ucfirst($perfil['rol'])); ?>
                            </span>
                        </div>
                        <div class="user-actions">
                            <?php if ($_SESSION['usuario']['id'] != $perfil['id']): ?>
                                <button class="btn btn-sm editar btn-actions" data-id="<?php echo $perfil['id']; ?>" style="background-color: #cc1d1d !important; border-color: #cc1d1d !important; color: white !important;">
                                    <i class="fas fa-sync-alt"></i>Actualizar
                                </button>
                            <?php else: ?>
                                <a href="index.php?url=user&type=perfil" class="btn btn-sm btn-info text-white">
                                    <i class="fas fa-user me-1"></i>Mi Perfil
                                </a>
                            <?php endif; ?>
                            <?php if ($_SESSION['usuario']['rol'] === 'superadmin' && $perfil['rol'] !== 'superadmin'): ?>
                                <button class="btn btn-sm btn-danger eliminar btn-actions" data-id="<?php echo $perfil['id']; ?>" data-usuario="<?php echo htmlspecialchars($perfil['usuario']); ?>">
                                    <i class="fas fa-trash me-1"></i>Eliminar
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Modal para registrar/editar usuario -->
    <div class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitulo"></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formUsuario" class="needs-validation" novalidate>
                        <input type="hidden" id="id" name="id">
                        <div class="mb-3">
                            <label for="usuario" class="form-label">Nombre de Usuario *</label>
                            <input type="text" class="form-control" id="usuario" name="usuario" required>
                            <div class="invalid-feedback">Por favor ingrese un nombre de usuario</div>
                        </div>
                        <div class="mb-3">
                            <label for="correo_usuario" class="form-label">Correo Electrónico *</label>
                            <input type="email" class="form-control" id="correo_usuario" name="correo_usuario" required>
                            <div class="invalid-feedback">Por favor ingrese un correo válido</div>
                        </div>
                        <div class="mb-3">
                            <label for="rol" class="form-label">Rol *</label>
                            <select class="form-select" id="rol" name="rol" required>
                                <option value="">Seleccionar rol</option>
                                <option value="vendedor">Vendedor</option>
                                <option value="admin">Administrador</option>
                            </select>
                            <div class="invalid-feedback">Por favor seleccione un rol</div>
                        </div>
                        <div class="mb-3" id="contrasenaGroup">
                            <label for="clave" class="form-label">Contraseña *</label>
                            <input type="password" class="form-control" id="clave" name="clave" required minlength="6">
                            <div class="invalid-feedback">La contraseña debe tener al menos 6 caracteres</div>
                        </div>
                        <div class="mb-3" id="confirmarContrasenaGroup">
                            <label for="confirmar_clave" class="form-label">Confirmar Contraseña *</label>
                            <input type="password" class="form-control" id="confirmar_clave" name="confirmar_clave" required>
                            <div class="invalid-feedback">Las contraseñas no coinciden</div>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i> Cancelar
                            </button>
                            <button type="submit" class="btn" style="background-color: #cc1d1d !important; border-color: #cc1d1d !important; color: white !important;">
                                <i class="fas fa-save me-1"></i> Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación para eliminar -->
    <div class="modal fade" id="modalConfirmarEliminar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirmar Eliminación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de que desea eliminar al usuario <span id="usuarioEliminar" class="fw-bold"></span>?</p>
                    <p class="text-danger"><small>Esta acción no se puede deshacer.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">
                        <i class="fas fa-trash me-1"></i> Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Verificación Super Admin -->
    <div class="modal fade" id="modalVerificacionSuperAdmin" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fas fa-shield-alt me-2"></i>Verificación Requerida</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Para continuar, ingrese la contraseña del Super Administrador:</p>
                    <div class="mb-3">
                        <label for="superadminPassword" class="form-label">Contraseña de Super Administrador</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="superadminPassword" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">La contraseña es requerida</div>
                    </div>
                    <input type="hidden" id="usuarioIdVerificar">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning" id="btnVerificarSuperAdmin">
                        <i class="fas fa-check me-1"></i> Verificar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para zoom de imagen de perfil -->
    <div class="modal fade" id="modalZoomImagen" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-header border-0 bg-transparent">
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <img id="imagen-zoom" src="" alt="Imagen de perfil ampliada" class="img-fluid rounded-circle shadow-lg" style="max-height: 80vh; object-fit: contain;">
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    $(document).ready(function() {
        // Función para abrir el modal de zoom de imagen
        function abrirZoomImagen(src) {
            $('#imagen-zoom').attr('src', src);
            $('#modalZoomImagen').modal('show');
        }

        // Abrir zoom al hacer clic en cualquier imagen de perfil
        $(document).on('click', '.user-img', function() {
            const imagenSrc = $(this).attr('src');
            abrirZoomImagen(imagenSrc);
        });

        // Cerrar modal con tecla Escape
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $('#modalZoomImagen').hasClass('show')) {
                $('#modalZoomImagen').modal('hide');
            }
        });

        // Inicialización específica para el dropdown de reportes de usuarios
        const reportesDropdown = document.getElementById('reportesDropdownUsuarios');
        if (reportesDropdown) {
            const dropdown = new bootstrap.Dropdown(reportesDropdown);
            
            // Manejar clic en el botón del dropdown
            reportesDropdown.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdown.toggle();
            });
            
            // Prevenir que el menú se cierre al hacer clic en él
            document.querySelector('.dropdown-menu', reportesDropdown.parentNode).addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
        
        // Inicializar otros dropdowns excluyendo el de reportes
        const dropdownToggles = [].slice.call(document.querySelectorAll('.dropdown-toggle:not(#reportesDropdownUsuarios)'));
        dropdownToggles.forEach(function(dropdownToggleEl) {
            new bootstrap.Dropdown(dropdownToggleEl);
        });

        // Variable para almacenar la contraseña temporalmente
        let superAdminPassword = '';
        let currentUserId = '';
        
        // Función para alternar visibilidad de contraseña
        function togglePasswordVisibility(input, icon) {
            const type = input.attr('type') === 'password' ? 'text' : 'password';
            input.attr('type', type);
            icon.toggleClass('fa-eye fa-eye-slash');
        }
        
        // Toggle para mostrar/ocultar contraseña en el modal de verificación
        $(document).on('click', '.toggle-password', function() {
            const input = $(this).siblings('input');
            const icon = $(this).find('i');
            togglePasswordVisibility(input, icon);
        });
        
        // Manejar clic en botón de ver clave
        $(document).on('click', '.btn-ver-clave', function() {
            currentUserId = $(this).data('id');
            $('#modalVerificacionSuperAdmin').modal('show');
        });
        
        // Verificar contraseña de super administrador
        $('#btnVerificarSuperAdmin').click(function() {
            const password = $('#superadminPassword').val().trim();
            
            if (!password) {
                $('#superadminPassword').addClass('is-invalid');
                return;
            }
            
            // Mostrar loading
            const btn = $(this);
            const btnOriginalText = btn.html();
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Verificando...');
            
            // Enviar petición para verificar la contraseña
            console.log('Enviando petición de verificación a: index.php?url=user&type=verificarSuperAdmin');
            console.log('Datos enviados:', { password: password });
            
            $.ajax({
                url: 'index.php?url=user&type=verificarSuperAdmin',
                type: 'POST',
                data: { password: password },
                dataType: 'json',
                success: function(response, status, xhr) {
                    console.log('Respuesta del servidor (verificarSuperAdmin):', response);
                    
                    if (response.success) {
                        console.log('Verificación exitosa, obteniendo clave del usuario...');
                        superAdminPassword = password;
                        $('#modalVerificacionSuperAdmin').modal('hide');
                        
                        // Obtener la contraseña del usuario
                        const requestData = { 
                            id: currentUserId,
                            superadmin_password: password 
                        };
                        
                        console.log('Solicitando clave del usuario con datos:', requestData);
                        
                        $.ajax({
                            url: 'index.php?url=user&type=obtenerClave',
                            type: 'POST',
                            data: requestData,
                            dataType: 'json',
                            success: function(claveResponse, status, xhr) {
                                console.log('Respuesta del servidor (obtenerClave):', claveResponse);
                                
                                if (claveResponse && claveResponse.success) {
                                    // Mostrar la contraseña en un alert bonito
                                    Swal.fire({
                                        title: 'Contraseña del Usuario',
                                        html: `<div class="text-center">
                                            <i class="fas fa-key fa-3x text-primary mb-3"></i>
                                            <p class="h4">${claveResponse.clave || 'No disponible'}</p>
                                            <p class="text-muted small">Usuario: ${claveResponse.usuario || 'N/A'}</p>
                                        </div>`,
                                        confirmButtonText: 'Cerrar',
                                        confirmButtonColor: '#cc1d1d',
                                        customClass: {
                                            confirmButton: 'btn btn-primary',
                                        },
                                        buttonsStyling: false
                                    });
                                } else {
                                    const errorMsg = claveResponse ? 
                                        (claveResponse.message || 'Error desconocido al obtener la contraseña') : 
                                        'No se recibió respuesta del servidor';
                                    
                                    console.error('Error al obtener la contraseña:', errorMsg, claveResponse);
                                    toastr.error(errorMsg);
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Error en la petición obtenerClave:', status, error);
                                console.error('Respuesta del servidor:', xhr.responseText);
                                toastr.error('Error al conectar con el servidor para obtener la contraseña');
                            }
                        });
                    } else {
                        $('#superadminPassword').addClass('is-invalid');
                        const errorMsg = response ? 
                            (response.message || 'Error desconocido al verificar la contraseña') : 
                            'No se recibió respuesta del servidor';
                        
                        console.error('Error en verificación:', errorMsg, response);
                        toastr.error(errorMsg);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error en la petición verificarSuperAdmin:', status, error);
                    console.error('Respuesta del servidor:', xhr.responseText);
                    
                    let errorMsg = 'Error al conectar con el servidor';
                    
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response && response.message) {
                            errorMsg = response.message;
                        }
                    } catch (e) {
                        console.error('Error al analizar la respuesta de error:', e);
                    }
                    
                    toastr.error(errorMsg);
                },
                complete: function() {
                    btn.prop('disabled', false).html(btnOriginalText);
                }
            });
        });
        
        // Limpiar modal al cerrar
        $('#modalVerificacionSuperAdmin').on('hidden.bs.modal', function () {
            $('#superadminPassword').val('').removeClass('is-invalid');
            currentUserId = '';
        });
        // Configuración de toastr
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "5000"
        };

        // Mostrar modal para registrar nuevo usuario
        $('#btnRegistrarUsuario').click(function() {
            $('#modalTitulo').html('<i class="fas fa-user-plus me-2"></i>Registrar Nuevo Usuario');
            $('#formUsuario')[0].reset();
            $('#formUsuario').removeClass('was-validated');
            $('#id').val(''); // Asegurar que el ID esté vacío
            $('#contrasenaGroup').show();
            $('#confirmarContrasenaGroup').show();
            $('#clave').prop('required', true);
            $('#confirmar_clave').prop('required', true);
            $('#modalUsuario').modal('show');
        });

        // Editar usuario
        $('.editar').click(function() {
            const id = $(this).data('id');
            
            // Mostrar indicador de carga
            const btn = $(this);
            const btnOriginalHtml = btn.html();
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cargando...');
            
            $.ajax({
                url: 'index.php?url=user&type=perfil&action=formEditar&id=' + id,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response && response.success) {
                        const data = response.data;
                        $('#modalTitulo').html('<i class="fas fa-sync-alt"></i>Editar Usuario');
                        $('#id').val(data.id);
                        $('#usuario').val(data.usuario);
                        $('#correo_usuario').val(data.correo_usuario);
                        $('#rol').val(data.rol);
                        $('#contrasenaGroup').hide();
                        $('#confirmarContrasenaGroup').hide();
                        $('#clave').prop('required', false);
                        $('#confirmar_clave').prop('required', false);
                        $('#modalUsuario').modal('show');
                    } else {
                        const errorMsg = response && response.message ? response.message : 'Error al cargar los datos del usuario';
                        toastr.error(errorMsg);
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error('Error al cargar los datos del usuario');
                },
                complete: function() {
                    btn.prop('disabled', false).html(btnOriginalHtml);
                }
            });
        });

        // Eliminar usuario
        $('.eliminar').click(function() {
            const id = $(this).data('id');
            const usuario = $(this).data('usuario');
            $('#usuarioEliminar').text(usuario);
            $('#btnConfirmarEliminar').data('id', id);
            $('#modalConfirmarEliminar').modal('show');
        });

        // Confirmar eliminación
        $('#btnConfirmarEliminar').click(function() {
            const id = $(this).data('id');
            
            // Mostrar indicador de carga
            const btn = $(this);
            const btnOriginalHtml = btn.html();
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Eliminando...');
            
            $.ajax({
                url: 'index.php?url=user&type=perfil&action=eliminar&id=' + id,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response && response.success) {
                        toastr.success(response.message);
                        // Recargar la página para actualizar la tabla
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        const errorMsg = response && response.message ? response.message : 'Error al eliminar el usuario';
                        toastr.error(errorMsg);
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error('Error al eliminar el usuario');
                },
                complete: function() {
                    btn.prop('disabled', false).html(btnOriginalHtml);
                    $('#modalConfirmarEliminar').modal('hide');
                }
            });
        });

        // Enviar formulario de usuario
        // Validar que las contraseñas coincidan
        function validarContrasenas() {
            const clave = $('#clave').val();
            const confirmarClave = $('#confirmar_clave').val();
            const confirmarClaveInput = document.getElementById('confirmar_clave');
            
            if (clave !== confirmarClave) {
                confirmarClaveInput.setCustomValidity('Las contraseñas no coinciden');
                return false;
            } else {
                confirmarClaveInput.setCustomValidity('');
                return true;
            }
        }
        
        // Validar al cambiar la contraseña
        $('#clave, #confirmar_clave').on('keyup', function() {
            if ($('#confirmar_clave').val()) {
                validarContrasenas();
            }
        });
        
        $('#formUsuario').submit(function(e) {
            e.preventDefault();
            const form = this;
            
            // Validar contraseñas solo si los campos están visibles (al registrar)
            if ($('#contrasenaGroup').is(':visible') && !validarContrasenas()) {
                form.classList.add('was-validated');
                return false;
            }
            
            if (!form.checkValidity()) {
                e.stopPropagation();
                form.classList.add('was-validated');
                return false;
            }
            
            const formData = $(this).serialize();
            const isEditing = $('#id').val() !== '';
            
            // Mostrar indicador de carga
            const btnSubmit = $(this).find('button[type="submit"]');
            const btnOriginalText = btnSubmit.html();
            btnSubmit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...');
            
            // Construir la URL correcta con type=perfil&action=registrar/editar
            const action = isEditing ? 'editar' : 'registrar';
            const url = `index.php?url=user&type=perfil&action=${action}`;
            
            // Mostrar la URL en consola para depuración
            console.log('Enviando a:', url);
            
            $.ajax({
                url: url,
                method: 'POST',
                dataType: 'json',
                data: formData,
                success: function(response) {
                    console.log('Respuesta del servidor:', response);
                    if (response && response.success) {
                        toastr.success(response.message);
                        // Cerrar el modal manualmente sin usar 'hide'
                        const modal = bootstrap.Modal.getInstance(document.getElementById('modalUsuario'));
                        if (modal) {
                            modal.hide();
                        }
                        // Eliminar el backdrop manualmente
                        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                        // Restaurar el scroll del body
                        document.body.style.overflow = 'auto';
                        document.body.style.paddingRight = '0';
                        // Recargar la página para actualizar la tabla
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        const errorMsg = response && response.message ? response.message : 'Error al guardar el usuario';
                        console.error('Error en la respuesta:', errorMsg);
                        toastr.error(errorMsg);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error AJAX:', status, error);
                    console.error('Respuesta del servidor:', xhr.responseText);
                    console.error('Status HTTP:', xhr.status);
                    
                    let errorMsg = 'Error al guardar el usuario';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response && response.message) {
                            errorMsg = response.message;
                        }
                    } catch (e) {
                        console.error('No se pudo parsear la respuesta:', e);
                    }
                    
                    toastr.error(errorMsg);
                },
                complete: function() {
                    btnSubmit.prop('disabled', false).html(btnOriginalText);
                }
            });
        });
        
        // Resetear validación al cerrar modal
        $('#modalUsuario').on('hidden.bs.modal', function () {
            $('#formUsuario')[0].reset();
            $('#formUsuario').removeClass('was-validated');
        });
    });
    </script>
</body>
</html>

<?php
$content = ob_get_clean();
include 'Assets/layouts/base.php';