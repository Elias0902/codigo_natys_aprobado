<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Clientes</title>
    <link rel="icon" href="../Natys/Assets/img/natys.png" type="image/x-icon">
    <link rel="stylesheet" href="Assets/css/listar.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
</head>
<body>
    <div class="container-fluid py-4">
        <h1 class="mb-4" style="text-align: center;">Gestión de Clientes</h1>
        

    <div class="d-flex justify-content-between mb-3">
    <button type="button" class="btn btn-success" id="btnNuevoCliente">
        <i class="fas fa-plus-circle me-2"></i>Nuevo Cliente
    </button>
    <button type="button" class="btn btn-warning" id="btnToggleEstado">
    <i class="fas fa-trash-restore me-2"></i>Mostrar Eliminados
    </button>
    </div>
        
        <div class="table-responsive">
            <table id="clientes" class="table table-striped">
                <thead>
                    <tr>
                        <th>Cédula</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Dirección</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cliente): ?>
                        <tr>
                            <td data-cedula="<?php echo htmlspecialchars($cliente['ced_cliente']); ?>">
                                <?php echo htmlspecialchars($cliente['ced_cliente']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($cliente['nomcliente']); ?></td>
                            <td><?php echo htmlspecialchars($cliente['correo']); ?></td>
                            <td><?php echo htmlspecialchars($cliente['telefono']); ?></td>
                            <td><?php echo htmlspecialchars($cliente['direccion']); ?></td>
                            <td><?php echo $cliente['estado'] == 1 ? 'Activo' : 'Inactivo'; ?></td>
                            <td>
                                <div class="actions">
                                    <a href="index.php?url=cliente&action=editar&ced_cliente=<?php echo $cliente['ced_cliente']; ?>" 
                                       class="editar" title="Editar cliente">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($cliente['estado'] == 1): ?>
                                        <a href="index.php?url=cliente&action=eliminar&ced_cliente=<?php echo $cliente['ced_cliente']; ?>" 
                                           class="eliminar" 
                                           onclick="return confirm('¿Está seguro de eliminar este cliente?');"
                                           title="Eliminar cliente">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="index.php?url=cliente&action=restaurar&ced_cliente=<?php echo $cliente['ced_cliente']; ?>" 
                                           class="restaurar" 
                                           onclick="return confirm('¿Está seguro de restaurar este cliente?');"
                                           title="Restaurar cliente">
                                            <i class="fas fa-undo"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
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

    <div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalEliminarLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-user-times fa-3x text-danger mb-3"></i>
                    </div>
                    <p class="fs-5 mb-3">¿Está seguro de eliminar este cliente?</p>
                    <p class="text-muted">
                        Esta acción cambiará el estado del cliente a inactivo.<br>
                        Podrá restaurarlo posteriormente si es necesario.
                    </p>
                    <input type="hidden" id="cedulaEliminar">
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmarEliminar">
                        <i class="fas fa-trash-alt me-1"></i>Eliminar Cliente
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalRestaurar" tabindex="-1" aria-labelledby="modalRestaurarLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="modalRestaurarLabel">
                        <i class="fas fa-undo me-2"></i>
                        Confirmar Restauración
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-user-check fa-3x text-warning mb-3"></i>
                    </div>
                    <p class="fs-5 mb-3">¿Está seguro de restaurar este cliente?</p>
                    <p class="text-muted">
                        Esta acción activará nuevamente el cliente<br>
                        y estará disponible para realizar operaciones.
                    </p>
                    <input type="hidden" id="cedulaRestaurar">
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-warning" id="confirmarRestaurar">
                        <i class="fas fa-undo me-1"></i>Restaurar Cliente
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalEditarLabel">
                        <i class="fas fa-edit me-2"></i>
                        Editar Cliente
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="contenidoEditar">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Cargando información del cliente...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalNuevo" tabindex="-1" aria-labelledby="modalNuevoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalNuevoLabel">
                        <i class="fas fa-user-plus me-2"></i>
                        Nuevo Cliente
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="contenidoNuevo">
                    <div class="text-center py-4">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Preparando formulario...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <template id="templateFormulario">
        <div class="container-fluid p-0">
            <form id="formCliente" class="needs-validation" novalidate>
                <input type="hidden" name="original_cedula" id="original_cedula">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="ced_cliente" class="form-label">
                            <i class="fas fa-id-card me-1"></i>Cédula *
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="ced_cliente" 
                               name="ced_cliente" 
                               placeholder="Ingrese la cédula"
                               required>
                        <div class="invalid-feedback">
                            Por favor ingrese la cédula del cliente
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="nomcliente" class="form-label">
                            <i class="fas fa-user me-1"></i>Nombre Completo *
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="nomcliente" 
                               name="nomcliente" 
                               placeholder="Ingrese el nombre completo"
                               required>
                        <div class="invalid-feedback">
                            Por favor ingrese el nombre del cliente
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="correo" class="form-label">
                            <i class="fas fa-envelope me-1"></i>Correo Electrónico *
                        </label>
                        <input type="email" 
                               class="form-control" 
                               id="correo" 
                               name="correo" 
                               placeholder="ejemplo@correo.com"
                               required>
                        <div class="invalid-feedback">
                            Por favor ingrese un correo electrónico válido
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="telefono" class="form-label">
                            <i class="fas fa-phone me-1"></i>Teléfono *
                        </label>
                        <input type="tel" 
                               class="form-control" 
                               id="telefono" 
                               name="telefono" 
                               placeholder="Ingrese el número de teléfono"
                               required>
                        <div class="invalid-feedback">
                            Por favor ingrese el número de teléfono
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="direccion" class="form-label">
                        <i class="fas fa-map-marker-alt me-1"></i>Dirección *
                    </label>
                    <textarea class="form-control" 
                              id="direccion" 
                              name="direccion" 
                              rows="3" 
                              placeholder="Ingrese la dirección completa"
                              required></textarea>
                    <div class="invalid-feedback">
                        Por favor ingrese la dirección del cliente
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Guardar Cliente
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.5.2/bootbox.min.js"></script>
    <script src="../Natys/Assets/js/cliente.js"></script>
</body>
</html>