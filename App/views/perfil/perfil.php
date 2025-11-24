<?php
ob_start();
$imagenDefault = '/Natys/Assets/img/defaultAvatar.jpg';
$usuarioId = $_SESSION['usuario']['id'] ?? 0;
$imagenPerfil = $_SESSION['usuario']['imagen_perfil'] ?? $imagenDefault;


$esAdmin = in_array($_SESSION['usuario']['rol'], ['admin', 'superadmin']);
$esSuperAdmin = $_SESSION['usuario']['rol'] === 'superadmin';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil - <?php echo htmlspecialchars($_SESSION['usuario']['usuario']); ?></title>
    <link rel="icon" href="../Natys/Assets/img/natys.png" type="image/x-icon">
    <link rel="stylesheet" href="/Natys/Assets/css/styles.css">
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
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .profile-img:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }
        .info-label {
            font-weight: 600;
            color: #495057;
        }
        .info-value {
            color: #6c757d;
        }
        .btn-change-password {
            background-color: #996f28;
            border-color: #996f28;
        }
        .btn-change-password:hover {
            background-color: #e0a800;
            border-color: #d39e00;
        }
        .password-field {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            z-index: 10;
            background: transparent;
            border: none;
            color: #6c757d;
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
                                <div class="position-relative d-inline-block">
                                    <img id="imagen-perfil" 
                                         src="<?php echo $imagenPerfil; ?>" 
                                         data-original-src="<?php echo $imagenPerfil; ?>"
                                         alt="Foto de perfil" 
                                         class="profile-img rounded-circle mb-3" 
                                         onerror="this.src='<?php echo $imagenDefault; ?>'; this.onerror=null;">
                                    <div class="position-absolute bottom-0 end-0 d-flex flex-column" style="gap: 5px;">
                                        <label for="subir-imagen" class="btn btn-primary btn-sm rounded-circle" style="width: 40px; height: 40px; line-height: 25px;" title="Cambiar foto de perfil">
                                            <i class="fas fa-camera"></i>
                                        </label>
                                        <button type="button" id="eliminar-imagen" class="btn btn-outline-danger btn-sm rounded-circle" style="width: 40px; height: 40px; line-height: 25px; padding: 0; display: flex; align-items: center; justify-content: center;" title="Eliminar foto de perfil" <?php echo ($imagenPerfil === $imagenDefault) ? 'disabled' : ''; ?>>
                                            <i class="fas fa-trash" style="font-size: 1rem;"></i>
                                        </button>
                                        <input type="file" id="subir-imagen" accept="image/*" style="display: none;">
                                    </div>
                                </div>
                                <h5 class="mb-1 mt-3"><?php echo htmlspecialchars($_SESSION['usuario']['usuario']); ?></h5>
                                <p class="text-muted mb-3">
                                    <span class="badge bg-<?php 
                                        echo $_SESSION['usuario']['rol'] === 'admin' ? 'primary' : 
                                             ($_SESSION['usuario']['rol'] === 'superadmin' ? 'info' : 'success'); 
                                    ?>">
                                        <?php echo htmlspecialchars(ucfirst($_SESSION['usuario']['rol'])); ?>
                                    </span>
                                </p>
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
                                        <span class="badge bg-<?php 
                                            echo $_SESSION['usuario']['rol'] === 'admin' ? 'primary' : 
                                                 ($_SESSION['usuario']['rol'] === 'superadmin' ? 'info' : 'success'); 
                                        ?>">
                                            <?php echo htmlspecialchars(ucfirst($_SESSION['usuario']['rol'])); ?>
                                        </span>
                                    </div>
                                </div>
                                <hr>
                                <div class="row mb-3">
                                    <div class="col-sm-4 info-label">Último acceso:</div>
                                    <div class="col-sm-8 info-value" id="ultimo-acceso">
                                        Cargando hora local...
                                    </div>
                                </div>
                                <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const serverDate = new Date(<?php 
                                        if(isset($_SESSION['usuario']['ultimo_acceso'])) {
                                            $fecha = new DateTime($_SESSION['usuario']['ultimo_acceso'], new DateTimeZone('UTC'));
                                            echo strtotime($fecha->format('Y-m-d H:i:s')) * 1000;
                                        } elseif(isset($_SESSION['last_activity'])) {
                                            echo $_SESSION['last_activity'] * 1000;
                                        } else {
                                            echo 'null';
                                        }
                                    ?>);
                                    
                                    function formatDate(date) {
                                        if (!date) return 'No disponible';
                                        
                                        const options = {
                                            day: '2-digit',
                                            month: '2-digit',
                                            year: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit',
                                            second: '2-digit',
                                            hour12: true
                                        };
                                        
                                        return date.toLocaleString('es-VE', options);
                                    }
                                    
                                    const ultimoAccesoElement = document.getElementById('ultimo-acceso');
                                    ultimoAccesoElement.textContent = serverDate ? formatDate(serverDate) : 'No disponible';
                                });
                                </script>
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
                    <form id="formCambiarClave" class="needs-validation" novalidate>
                        <div class="mb-3 position-relative">
                            <label for="clave_actual" class="form-label">Contraseña Actual *</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="clave_actual" name="clave_actual" required>
                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="clave_actual">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">Por favor ingrese su contraseña actual</div>
                        </div>
                        <div class="mb-3 position-relative">
                            <label for="nueva_clave" class="form-label">Nueva Contraseña *</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="nueva_clave" name="nueva_clave" required minlength="6">
                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="nueva_clave">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">La contraseña debe tener al menos 6 caracteres</div>
                        </div>
                        <div class="mb-3 position-relative">
                            <label for="confirmar_clave" class="form-label">Confirmar Nueva Contraseña *</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirmar_clave" name="confirmar_clave" required minlength="6">
                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="confirmar_clave">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>
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

    <!-- Dependencias JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "5000",
        "preventDuplicates": true
    };
    
    let isUploading = false;
    const defaultImage = '<?php echo $imagenDefault; ?>';
    const userId = <?php echo $_SESSION['usuario']['id'] ?? 0; ?>;

    $(document).ready(function() {
        // Función para actualizar la imagen en el perfil y header
        function updateSessionImage(newImageUrl) {
            const timestamp = new Date().getTime();
            
            // CORRECCIÓN: Remover cualquier parámetro existente y luego añadir el timestamp
            const baseUrl = newImageUrl.split('?')[0];
            const newImageUrlWithTimestamp = baseUrl + '?t=' + timestamp;
            
            // Actualizar imagen perfil
            $('#imagen-perfil').attr('src', newImageUrlWithTimestamp).data('original-src', newImageUrl);

            // Actualizar imagen header si existe
            const $headerImg = $('#header-profile-img');
            if ($headerImg.length) {
                $headerImg.attr('src', newImageUrlWithTimestamp);
            }

            // Actualizar botón eliminar
            updateDeleteButton();
        }

        function updateDeleteButton() {
            const currentImage = $('#imagen-perfil').attr('src');
            const isDefaultImage = currentImage.includes('defaultAvatar.jpg');
            $('#eliminar-imagen').prop('disabled', isDefaultImage);
        }
        updateDeleteButton();

        function showImagePreview(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#imagen-perfil').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }

        // Función para abrir el modal de zoom de imagen
        function abrirZoomImagen(src) {
            $('#imagen-zoom').attr('src', src);
            $('#modalZoomImagen').modal('show');
        }

        // Abrir zoom al hacer clic en la imagen de perfil
        $('#imagen-perfil').on('click', function() {
            const imagenSrc = $(this).attr('src');
            abrirZoomImagen(imagenSrc);
        });

        // Cerrar modal con tecla Escape
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $('#modalZoomImagen').hasClass('show')) {
                $('#modalZoomImagen').modal('hide');
            }
        });

        // Subir imagen de perfil
        $('#subir-imagen').on('change', function(e) {
            const file = this.files[0];
            if (!file) return;

            const maxSize = 3 * 1024 * 1024;
            if (file.size > maxSize) {
                toastr.error('La imagen es demasiado grande. El tamaño máximo permitido es de 3MB.');
                $(this).val('');
                return;
            }

            const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                toastr.error('Formato de archivo no permitido. Solo se permiten imágenes JPG, PNG o GIF.');
                $(this).val('');
                return;
            }

            showImagePreview(file);

            if (isUploading) {
                toastr.warning('Ya se está subiendo una imagen. Por favor espera...');
                return;
            }

            const loadingToast = toastr.info('Subiendo imagen...', '', {timeOut: 0, closeButton: false});
            isUploading = true;

            const formData = new FormData();
            formData.append('imagen_perfil', file);

            $.ajax({
                url: 'index.php?url=user&type=login&action=subirImagen',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    toastr.clear(loadingToast);
                    isUploading = false;

                    if (response.success) {
                        updateSessionImage(response.imagen_url);
                        toastr.success(response.message || 'Imagen actualizada correctamente');
                    } else {
                        toastr.error(response.message || 'Error al subir la imagen');
                        const originalSrc = $('#imagen-perfil').data('original-src') || defaultImage;
                        $('#imagen-perfil').attr('src', originalSrc);
                        updateDeleteButton();
                    }
                },
                error: function(xhr) {
                    toastr.clear(loadingToast);
                    isUploading = false;
                    let errorMessage = 'Error al procesar la imagen. Inténtalo de nuevo.';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response && response.message) errorMessage = response.message;
                    } catch (e) {}
                    toastr.error(errorMessage);
                    const originalSrc = $('#imagen-perfil').data('original-src') || defaultImage;
                    $('#imagen-perfil').attr('src', originalSrc);
                    updateDeleteButton();
                }
            });
        });

        // Eliminar imagen de perfil
        $('#eliminar-imagen').on('click', function() {
            if ($(this).prop('disabled')) return;
            
            Swal.fire({
                title: '¿Estás seguro?',
                text: '¿Deseas eliminar tu foto de perfil? Se restaurará la imagen por defecto.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const loadingToast = toastr.info('Eliminando imagen de perfil...', '', {timeOut: 0, closeButton: false});
                    
                    $.ajax({
                        url: 'index.php?url=user&type=login&action=eliminarImagen',
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            toastr.clear(loadingToast);
                            if (response.success) {
                                updateSessionImage(response.imagen_url);
                                toastr.success(response.message);
                            } else {
                                toastr.error(response.message || 'Error al eliminar la imagen de perfil');
                            }
                        },
                        error: function(xhr) {
                            toastr.clear(loadingToast);
                            let errorMessage = 'Error al procesar la solicitud. Inténtalo de nuevo.';
                            try {
                                const response = JSON.parse(xhr.responseText);
                                if (response && response.message) errorMessage = response.message;
                            } catch (e) {}
                            toastr.error(errorMessage);
                        }
                    });
                }
            });
        });

        // Mostrar modal cambiar contraseña
        $('#btnCambiarClave').click(function() {
            $('#modalCambiarClave').modal('show');
        });

        // Alternar visibilidad de contraseña
        $('.toggle-password').click(function() {
            const targetId = $(this).data('target');
            const input = $('#' + targetId);
            const icon = $(this).find('i');
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        // Validación y envío del formulario de cambio de contraseña
        $('#formCambiarClave').submit(function(e) {
            e.preventDefault();
            const form = this;
            if (!form.checkValidity()) {
                e.stopPropagation();
                form.classList.add('was-validated');
                return false;
            }
            const claveActual = $('#clave_actual').val();
            const nuevaClave = $('#nueva_clave').val();
            const confirmarClave = $('#confirmar_clave').val();
            if (nuevaClave !== confirmarClave) {
                $('#confirmar_clave').addClass('is-invalid');
                toastr.error('Las contraseñas no coinciden');
                return false;
            } else {
                $('#confirmar_clave').removeClass('is-invalid');
            }
            const btnSubmit = $(this).find('button[type="submit"]');
            const btnOriginalText = btnSubmit.html();
            btnSubmit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...');
            $.ajax({
                url: 'index.php?url=user&type=perfil&action=cambiarClave',
                method: 'POST',
                dataType: 'json',
                data: {
                    clave_actual: claveActual,
                    nueva_clave: nuevaClave,
                    confirmar_clave: confirmarClave
                },
                success: function(response) {
                    if (response && response.success) {
                        toastr.success(response.message);
                        $('#modalCambiarClave').modal('hide');
                        $('#formCambiarClave')[0].reset();
                        $('#formCambiarClave').removeClass('was-validated');
                    } else {
                        const errorMsg = response && response.message ? response.message : 'Error desconocido al cambiar la contraseña';
                        toastr.error(errorMsg);
                        $('#clave_actual').addClass('is-invalid');
                    }
                },
                error: function() {
                    toastr.error('Error al procesar la solicitud. Por favor, inténtalo de nuevo.');
                },
                complete: function() {
                    btnSubmit.prop('disabled', false).html(btnOriginalText);
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