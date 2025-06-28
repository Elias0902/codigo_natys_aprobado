<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Pagos</title>
    <link rel="icon" href="../Natys/Assets/img/natys.png" type="image/x-icon">
    <link rel="stylesheet" href="Assets/css/listar.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
</head>
<body>
    <div class="container-fluid py-4">
        <h1 class="mb-4" style="text-align: center;">Gestión de Pagos</h1>
        
        <div class="d-flex justify-content-between mb-3">
            <button type="button" class="btn btn-success" id="btnNuevoPago">
                <i class="fas fa-plus-circle me-2"></i>Nuevo Pago
            </button>
            <button type="button" class="btn btn-warning" id="btnToggleEstado">
                <i class="fas fa-trash-restore me-2"></i>Mostrar No Aprobados
            </button>
        </div>

        <div class="table-responsive" style="text-align:center;">
            <table id="pagos" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Banco</th>
                        <th>Referencia</th>
                        <th>Fecha</th>
                        <th>Monto</th>
                        <th>Método</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Datos cargados por DataTables -->
                </tbody>
            </table>
        </div>
        
        <br>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-home me-2"></i>Menú Principal
        </a>
    </div>

    <!-- Modales -->
    <div class="modal fade" id="modalNuevo" tabindex="-1" aria-labelledby="modalNuevoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalNuevoLabel">
                        <i class="fas fa-money-bill-wave me-2"></i>
                        Nuevo Pago
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
                        Editar Pago
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="contenidoEditar">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Cargando información del pago...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <template id="templateFormulario">
        <div class="container-fluid p-0">
            <form id="formPago" class="needs-validation" novalidate>
                <input type="hidden" name="id_pago" id="id_pago">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="banco" class="form-label">
                            <i class="fas fa-university me-1"></i>Banco *
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="banco" 
                               name="banco" 
                               placeholder="Ingrese el nombre del banco"
                               required>
                        <div class="invalid-feedback">
                            Por favor ingrese el nombre del banco
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="referencia" class="form-label">
                            <i class="fas fa-hashtag me-1"></i>Referencia *
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="referencia" 
                               name="referencia" 
                               placeholder="Ingrese la referencia del pago"
                               required>
                        <div class="invalid-feedback">
                            Por favor ingrese la referencia del pago
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="fecha" class="form-label">
                            <i class="fas fa-calendar-alt me-1"></i>Fecha *
                        </label>
                        <input type="date" 
                               class="form-control" 
                               id="fecha" 
                               name="fecha" 
                               required>
                        <div class="invalid-feedback">
                            Por favor seleccione la fecha del pago
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="monto" class="form-label">
                            <i class="fas fa-money-bill-wave me-1"></i>Monto *
                        </label>
                        <input type="number" 
                               class="form-control" 
                               id="monto" 
                               name="monto" 
                               placeholder="Ingrese el monto"
                               step="0.01"
                               min="0"
                               required>
                        <div class="invalid-feedback">
                            Por favor ingrese un monto válido
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="cod_metodo" class="form-label">
                            <i class="fas fa-credit-card me-1"></i>Método de Pago *
                        </label>
                        <select class="form-select" id="cod_metodo" name="cod_metodo" required>
                            <option value="" selected disabled>Seleccione un método</option>
                            <!-- Opciones se cargarán dinámicamente -->
                        </select>
                        <div class="invalid-feedback">
                            Por favor seleccione un método de pago
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Guardar Pago
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
    <script src="../Natys/Assets/js/pago.js"></script>
</body>
</html>