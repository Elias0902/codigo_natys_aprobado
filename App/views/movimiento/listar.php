<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Movimientos de Entrada</title>
    <link rel="icon" href="../Natys/Assets/img/natys.png" type="image/x-icon">
    <link rel="stylesheet" href="Assets/css/ME.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
</head>
<body>
    <div class="container-fluid py-4">
        <h1 class="mb-4" style="text-align: center;">Gestión de Movimientos de Entrada</h1>

        <div class="d-flex justify-content-between mb-3">
            <button type="button" class="btn btn-success" id="btnNuevoMovimiento">
                <i class="fas fa-plus-circle me-2"></i>Nuevo Movimiento
            </button>
            <button type="button" class="btn btn-warning" id="btnToggleEstado">
                <i class="fas fa-trash-restore me-2"></i>Mostrar Eliminados
            </button>
        </div>

        <div class="table-responsive" style="text-align:center;">
            <table id="movimientos" class="table table-striped" style="margin: 0 auto; text-align: center;">
                <thead>
                    <tr>
                        <th>N° Movimiento</th>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Observaciones</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movimientos as $movimiento): ?>
                        <tr>
                            <td data-num_movimiento="<?php echo htmlspecialchars($movimiento['num_movimiento']); ?>">
                                <?php echo htmlspecialchars($movimiento['num_movimiento']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($movimiento['fecha']); ?></td>
                            <td>
                                <?php 
                                if(isset($movimiento['cod_producto'])) {
                                    echo htmlspecialchars($movimiento['cod_producto'] . ' - ' . ($movimiento['producto_nombre'] ?? ''));
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($movimiento['cant_productos'] ?? '0'); ?></td>
                            <td><?php echo htmlspecialchars($movimiento['observaciones']); ?></td>
                            <td><?php echo $movimiento['estado'] == 1 ? 'Activo' : 'Inactivo'; ?></td>
                            <td>
                                <div class="actions">
                                    <button class="btn btn-sm btn-primary editar" data-id="<?php echo $movimiento['num_movimiento']; ?>" title="Editar movimiento">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($movimiento['estado'] == 1): ?>
                                        <button class="btn btn-sm btn-danger eliminar" data-id="<?php echo $movimiento['num_movimiento']; ?>" title="Eliminar movimiento">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-warning restaurar" data-id="<?php echo $movimiento['num_movimiento']; ?>" title="Restaurar movimiento">
                                            <i class="fas fa-undo"></i>
                                        </button>
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

    <!-- Modal Eliminar -->
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
                    <p class="fs-5 mb-3">¿Está seguro de eliminar este movimiento?</p>
                    <p class="text-muted">
                        Esta acción cambiará el estado del movimiento a inactivo.<br>
                        Podrá restaurarlo posteriormente si es necesario.
                    </p>
                    <input type="hidden" id="idEliminar">
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmarEliminar">
                        <i class="fas fa-trash-alt me-1"></i>Eliminar Movimiento
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Restaurar -->
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
                    <p class="fs-5 mb-3">¿Está seguro de restaurar este movimiento?</p>
                    <p class="text-muted">
                        Esta acción activará nuevamente el movimiento<br>
                        y estará disponible para realizar operaciones.
                    </p>
                    <input type="hidden" id="idRestaurar">
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-warning" id="confirmarRestaurar">
                        <i class="fas fa-undo me-1"></i>Restaurar Movimiento
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar -->
    <div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalEditarLabel">
                        <i class="fas fa-edit me-2"></i>
                        Editar Movimiento
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="contenidoEditar">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Cargando información del movimiento...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo -->
    <div class="modal fade" id="modalNuevo" tabindex="-1" aria-labelledby="modalNuevoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalNuevoLabel">
                        <i class="fas fa-user-plus me-2"></i>
                        Nuevo Movimiento
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

    <!-- Template Formulario -->
    <template id="templateFormulario">
        <div class="container-fluid p-0">
            <form id="formMovimiento" class="needs-validation" novalidate>
                <input type="hidden" name="num_movimiento" id="num_movimiento">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="fecha" class="form-label">
                            <i class="fas fa-calendar me-1"></i>Fecha *
                        </label>
                        <input type="date" 
                               class="form-control" 
                               id="fecha" 
                               name="fecha" 
                               required>
                        <div class="invalid-feedback">
                            Por favor ingrese la fecha del movimiento
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="producto" class="form-label">
                            <i class="fas fa-box me-1"></i>Producto *
                        </label>
                        <select class="form-select" id="producto" name="producto" required>
                            <option value="">Seleccione un producto</option>
                            <!-- Las opciones se llenarán con JavaScript -->
                        </select>
                        <div class="invalid-feedback">
                            Por favor seleccione un producto
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="cantidad" class="form-label">
                            <i class="fas fa-hashtag me-1"></i>Cantidad *
                        </label>
                        <input type="number" 
                               class="form-control" 
                               id="cantidad" 
                               name="cantidad" 
                               min="1"
                               required>
                        <div class="invalid-feedback">
                            Por favor ingrese la cantidad
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="observaciones" class="form-label">
                            <i class="fas fa-comment me-1"></i>Observaciones *
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="observaciones" 
                               name="observaciones" 
                               placeholder="Ingrese las observaciones"
                               required>
                        <div class="invalid-feedback">
                            Por favor ingrese las observaciones
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Guardar Movimiento
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
    <script src="../Natys/Assets/js/movimiento.js"></script>
</body>
</html>