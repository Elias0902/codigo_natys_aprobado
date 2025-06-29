<?php
ob_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Perfiles</title>
    <link rel="icon" href="../Natys/Assets/img/natys.png" type="image/x-icon">
    <link rel="stylesheet" href="Assets/css/listar.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
</head>
<body>
    <div class="container-fluid py-4">
        <h1 class="mb-4" style="text-align: center;">Gestión de Perfiles</h1>

        <div class="table-responsive" style="text-align:center;">
            <table id="perfiles" class="table table-striped" style="margin: 0 auto; text-align: center;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($perfiles as $perfil): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($perfil['id']); ?></td>
                            <td><?php echo htmlspecialchars($perfil['nombre_usuario']); ?></td>
                            <td><?php echo htmlspecialchars($perfil['correo_usuario']); ?></td>
                            <td><?php echo htmlspecialchars($perfil['usuario']); ?></td>
                            <td><?php echo htmlspecialchars($perfil['rol']); ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary editar" data-id="<?php echo $perfil['id']; ?>">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <br>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-home me-2"></i>Menú Principal
        </a>
    </div>

    <div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalEditarLabel">
                        <i class="fas fa-user-edit me-2"></i>
                        Editar Perfil
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="contenidoEditar">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Cargando información del perfil...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <template id="templateFormularioPerfil">
        <div class="container-fluid p-0">
            <form id="formPerfil" class="needs-validation" novalidate>
                <input type="hidden" name="id" id="id">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nombre_usuario" class="form-label">
                            <i class="fas fa-user me-1"></i>Nombre Completo *
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="nombre_usuario" 
                               name="nombre_usuario" 
                               placeholder="Ingrese el nombre completo"
                               required>
                        <div class="invalid-feedback">
                            Por favor ingrese el nombre completo
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="correo_usuario" class="form-label">
                            <i class="fas fa-envelope me-1"></i>Correo Electrónico *
                        </label>
                        <input type="email" 
                               class="form-control" 
                               id="correo_usuario" 
                               name="correo_usuario" 
                               placeholder="ejemplo@correo.com"
                               required>
                        <div class="invalid-feedback">
                            Por favor ingrese un correo electrónico válido
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="usuario" class="form-label">
                            <i class="fas fa-user-tag me-1"></i>Nombre de Usuario *
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="usuario" 
                               name="usuario" 
                               placeholder="Ingrese el nombre de usuario"
                               required>
                        <div class="invalid-feedback">
                            Por favor ingrese el nombre de usuario
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="rol" class="form-label">
                            <i class="fas fa-user-shield me-1"></i>Rol *
                        </label>
                        <select class="form-select" id="rol" name="rol" required>
                            <option value="">Seleccione un rol</option>
                            <option value="admin">Administrador</option>
                            <option value="vendedor">Vendedor</option>
                        </select>
                        <div class="invalid-feedback">
                            Por favor seleccione un rol
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="clave" class="form-label">
                        <i class="fas fa-key me-1"></i>Nueva Contraseña (opcional)
                    </label>
                    <input type="password" 
                           class="form-control" 
                           id="clave" 
                           name="clave" 
                           placeholder="Dejar en blanco para no cambiar">
                    <div class="form-text">
                        Solo complete si desea cambiar la contraseña
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </template>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="../Natys/Assets/js/perfil.js"></script>
</body>
</html>

<?php
$content = ob_get_clean();
include 'Assets/layouts/base.php';