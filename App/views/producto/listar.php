<?php
// Al inicio del archivo listar.php
ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Productos</title>
    <link rel="icon" href="../Natys/Assets/img/natys.png" type="image/x-icon">
    <link rel="stylesheet" href="Assets/css/listar.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
</head>
<body>
    <div class="container-fluid py-4">
        <h1 class="mb-4" style="text-align: center;">Gestión de Productos</h1>
        
        <div class="d-flex justify-content-between mb-3">
            <button type="button" class="btn btn-success" id="btnNuevoProducto">
                <i class="fas fa-plus-circle me-2"></i>Nuevo Producto
            </button>
            <button type="button" class="btn btn-warning" id="btnToggleEstado">
                <i class="fas fa-trash-restore me-2"></i>Mostrar Eliminados
            </button>
        </div>

        <div class="table-responsive" style="text-align:center;">
            <table id="productos" class="table table-striped" style="width:100%">
    <thead>
        <tr>
            <th>Código</th>
            <th>Nombre</th>
            <th>Precio</th>
            <th>Unidad</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
                <tbody>
                    <?php foreach ($productos as $producto): ?>
                        <tr>
                            <td data-codigo="<?php echo htmlspecialchars($producto['cod_producto']); ?>">
                                <?php echo htmlspecialchars($producto['cod_producto']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($producto['precio']); ?></td>
                            <td><?php echo htmlspecialchars($producto['unidad']); ?></td>
                            <td><?php echo $producto['estado'] == 1 ? 'Activo' : 'Inactivo'; ?></td>
                            <td>
                                <div class="actions">
                                    <a href="#" class="editar" 
                                       data-codigo="<?php echo $producto['cod_producto']; ?>" 
                                       title="Editar producto">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($producto['estado'] == 1): ?>
                                        <a href="#" class="eliminar" 
                                           data-codigo="<?php echo $producto['cod_producto']; ?>" 
                                           title="Eliminar producto">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="#" class="restaurar" 
                                           data-codigo="<?php echo $producto['cod_producto']; ?>" 
                                           title="Restaurar producto">
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
        <a href="index.php?url=home" class="btn btn-secondary">
            <i class="fas fa-home me-2"></i>Menú Principal
        </a>
    </div>

    <!-- Modales -->
    <div class="modal fade" id="modalNuevo" tabindex="-1" aria-labelledby="modalNuevoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalNuevoLabel">
                        <i class="fas fa-box me-2"></i>
                        Nuevo Producto
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

    <div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalEditarLabel">
                        <i class="fas fa-edit me-2"></i>
                        Editar Producto
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="contenidoEditar">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Cargando información del producto...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <template id="templateFormulario">
        <div class="container-fluid p-0">
            <form id="formProducto" class="needs-validation" novalidate>
                <input type="hidden" name="original_codigo" id="original_codigo">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="cod_producto" class="form-label">
                            <i class="fas fa-barcode me-1"></i>Código del Producto *
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="cod_producto" 
                               name="cod_producto" 
                               placeholder="Ingrese el código del producto"
                               required>
                        <div class="invalid-feedback">
                            Por favor ingrese el código del producto
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="nombre" class="form-label">
                            <i class="fas fa-box me-1"></i>Nombre del Producto *
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="nombre" 
                               name="nombre" 
                               placeholder="Ingrese el nombre del producto"
                               required>
                        <div class="invalid-feedback">
                            Por favor ingrese el nombre del producto
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="precio" class="form-label">
                            <i class="fas fa-tag me-1"></i>Precio *
                        </label>
                        <input type="number" 
                               class="form-control" 
                               id="precio" 
                               name="precio" 
                               placeholder="Ingrese el precio"
                               step="0.01"
                               min="0"
                               required>
                        <div class="invalid-feedback">
                            Por favor ingrese un precio válido
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="unidad" class="form-label">
                            <i class="fas fa-balance-scale me-1"></i>Unidad de Medida *
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="unidad" 
                               name="unidad" 
                               placeholder="Ej: kg, litros, unidades"
                               required>
                        <div class="invalid-feedback">
                            Por favor ingrese la unidad de medida
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Guardar Producto
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
    <script src="../Natys/Assets/js/producto.js"></script>
</body>
</html>
<?php
// Al final del archivo listar.php
$content = ob_get_clean();

include 'Assets/layouts/base.php';