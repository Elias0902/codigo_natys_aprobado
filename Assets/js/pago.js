$(document).ready(function() {
    let table;
    let mostrandoEliminados = false;
    
    // Variables para el modal de procesar pago
    let modalProcesarPago = `
    <div class="modal fade" id="modalProcesarPago" tabindex="-1" aria-labelledby="modalProcesarPagoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalProcesarPagoLabel">
                        <i class="fas fa-money-bill-wave me-2"></i> Procesar Pago de Pedido
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="formProcesarPago">
                        <input type="hidden" id="pedido_id" name="id_pedido">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="monto" class="form-label">Monto a Pagar</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="monto" name="monto" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="fecha_pago" class="form-label">Fecha de Pago</label>
                                <input type="date" class="form-control" id="fecha_pago" name="fecha_pago" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="metodo_pago" class="form-label">Método de Pago</label>
                                <select class="form-select" id="metodo_pago" name="metodo_pago" required>
                                    <option value="">Seleccione un método de pago</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="banco" class="form-label">Banco (Opcional)</label>
                                <input type="text" class="form-control" id="banco" name="banco">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="referencia" class="form-label">Número de Referencia o Transacción</label>
                            <input type="text" class="form-control" id="referencia" name="referencia" required>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <span id="info-pedido"></span>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnConfirmarPago">
                        <i class="fas fa-check-circle me-1"></i> Confirmar Pago
                    </button>
                </div>
            </div>
        </div>
    </div>`;
    
    // Agregar el modal al final del body si no existe
    if ($('#modalProcesarPago').length === 0) {
        $('body').append(modalProcesarPago);
    }

    toastr.options = {
        "closeButton": true,
        "progressBar": false,
        "positionClass": "toast-top-right",
        "timeOut": "5000",
        "escapeHtml": true
    };

    function inicializarDataTable() {
        if ($.fn.DataTable.isDataTable('#pagos')) {
            table.destroy();
            $('#pagos').empty();
        }

        table = $('#pagos').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div>'
            },
            processing: true,
            serverSide: false,
            ajax: {
                url: mostrandoEliminados 
                    ? 'index.php?url=pago&action=listarEliminados' 
                    : 'index.php?url=pago&action=listar',
                type: 'GET',
                dataSrc: 'data',
                error: function(xhr, error, thrown) {
                    console.error('Error en AJAX:', error, thrown);
                    toastr.error('Error al cargar los datos de pagos');
                }
            },
            columns: [
                { data: 'id_pago' },
                { data: 'banco' },
                { data: 'referencia' },
                { 
                    data: 'fecha',
                    render: function(data) {
                        return new Date(data).toLocaleDateString('es-ES');
                    }
                },
                { 
                    data: 'monto',
                    render: function(data) {
                        return `$${parseFloat(data).toFixed(2)}`;
                    }
                },
                { data: 'metodo_pago' },
                { 
                    data: 'estado',
                    render: function(data) {
                        return `<span class="badge ${data == 1 ? 'bg-success' : 'bg-secondary'}">
                                ${data == 1 ? 'Aprobado' : 'InAprobado'}
                                </span>`;
                    }
                },
                {
                    data: 'id_pago',
                    render: function(data, type, row) {
                        return `
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-primary editar" data-id="${data}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                ${row.estado == 1 ? 
                                    `<button class="btn btn-sm btn-danger eliminar" data-id="${data}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>` : 
                                    `<button class="btn btn-sm btn-warning restaurar" data-id="${data}">
                                        <i class="fas fa-undo"></i>
                                    </button>`}
                            </div>
                        `;
                    },
                    orderable: false,
                    className: 'text-center'
                }
            ],
            order: [[0, 'asc']],
            responsive: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Todos']],
            dom: '<"top"lf>rt<"bottom"ip><"clear">',
            initComplete: function() {
                console.log('DataTable de pagos inicializado correctamente');
            },
            drawCallback: function() {
                console.log('Tabla redibujada. Mostrando:', mostrandoEliminados ? 'Eliminados' : 'Aprobados');
            }
        });
    }

    function recargarTabla() {
        if (table) {
            table.ajax.url(
                mostrandoEliminados 
                    ? 'index.php?url=pago&action=listarEliminados' 
                    : 'index.php?url=pago&action=listar'
            ).load(function(json) {
                console.log('Datos de pagos cargados:', json);
            }, false);
        } else {
            inicializarDataTable();
        }
    }

    function togglePagos() {
        mostrandoEliminados = !mostrandoEliminados;
        console.log('Alternando a:', mostrandoEliminados ? 'Eliminados' : 'Aprobados');
        const btn = $('#btnToggleEstado');
        btn.html(`<i class="fas ${mostrandoEliminados ? 'fa-money-bill-wave' : 'fa-trash-restore'} me-2"></i>
                  ${mostrandoEliminados ? 'Mostrar Aprobados' : 'Mostrar Eliminados'}`);
        btn.removeClass('btn-warning btn-info').addClass(mostrandoEliminados ? 'btn-info' : 'btn-warning');
        recargarTabla();
    }

 function cargarMetodosPago(selectElement, metodoSeleccionado = null) {
    $.ajax({
        url: 'index.php?url=pago&action=listarMetodos',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response && response.success && response.data) {
                selectElement.empty().append('<option value="" selected disabled>Seleccione un método</option>');
                $.each(response.data, function(index, metodo) {
                    const isSelected = metodoSeleccionado && metodoSeleccionado === metodo.codigo;
                    selectElement.append(`<option value="${metodo.codigo}" ${isSelected ? 'selected' : ''}>${metodo.detalle}</option>`);
                });
            } else {
                toastr.error('No se pudieron cargar los métodos de pago');
                console.error('Respuesta inesperada:', response);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar métodos de pago:', error, xhr.responseText);
            toastr.error('Error al cargar métodos de pago');
        }
    });
}

    const cargarFormulario = (modalId, contenidoId, datos = null) => {
        const template = document.getElementById('templateFormulario');
        const clone = template.content.cloneNode(true);
        const form = clone.querySelector('form');
        const selectMetodo = clone.querySelector('#cod_metodo');
        
        // Cargar métodos de pago
        cargarMetodosPago($(selectMetodo));
        
        if (datos) {
            console.log('Datos recibidos para formulario:', datos);
            form.querySelector('#id_pago').value = datos.id_pago || '';
            form.querySelector('#banco').value = datos.banco || '';
            form.querySelector('#referencia').value = datos.referencia || '';
            form.querySelector('#fecha').value = datos.fecha || '';
            form.querySelector('#monto').value = datos.monto || '';
            
            // Establecer el método seleccionado después de cargar las opciones
            setTimeout(() => {
                if (datos.cod_metodo) {
                    form.querySelector('#cod_metodo').value = datos.cod_metodo;
                }
            }, 300);
        }
        
        $(contenidoId).empty().append(clone);
        $(modalId).modal('show');
        
        // Configurar fecha mínima (hoy) para nuevos pagos
        if (!datos) {
            const today = new Date().toISOString().split('T')[0];
            form.querySelector('#fecha').min = today;
        }
        
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    };

    const confirmarAccion = ({ id, url, method, successMessage }) => {
        $.ajax({
            url: url,
            type: method,
            data: { id_pago: id },
            dataType: 'json'
        })
        .done(response => {
            if(response.success) {
                recargarTabla();
                toastr.success(successMessage);
            } else {
                toastr.error(response.message);
            }
        })
        .fail((xhr, status, error) => {
            toastr.error(`Error en la solicitud: ${error}`);
            console.error('Error en la petición:', error, xhr.responseText);
        });
    };

    const manejarFormulario = () => {
        $(document).on('submit', '#formPago', function(e) {
            e.preventDefault();
            
            const form = this;
            const formData = $(form).serialize();
            const actionUrl = 'index.php?url=pago&action=guardar';
            
            // Mostrar loading en el botón de guardar
            const submitBtn = $(form).find('button[type="submit"]');
            const originalBtnText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Procesando...');
            
            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: formData,
                dataType: 'json'
            })
            .done(response => {
                if(response.success) {
                    $(isNew ? '#modalNuevo' : '#modalEditar').modal('hide');
                    toastr.success(response.message);
                    recargarTabla();
                    
                    if(isNew) {
                        form.reset();
                        form.classList.remove('was-validated');
                    }
                } else {
                    toastr.error(response.message);
                }
            })
            .fail(function(xhr, status, error) {
                console.error('Error en la petición:', error, xhr.responseText);
                let errorMsg = 'Error al procesar la solicitud. Por favor, intente nuevamente.';
                
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response && response.message) {
                        errorMsg = response.message;
                    }
                } catch (e) {
                    console.error('Error al parsear respuesta de error:', e);
                }
                
                toastr.error(errorMsg);
            })
            .always(function() {
                // Restaurar el botón
                submitBtn.prop('disabled', false).html(originalBtnText);
            });
        });
    };

    // Cargar métodos de pago para el modal de procesar pago
    cargarMetodosPago($('#metodo_pago'));
    
    // Configurar fecha actual por defecto
    const today = new Date().toISOString().split('T')[0];
    $('#fecha_pago').val(today);
    
    // Manejar clic en el botón Seleccionar del modal de pedidos pendientes
    $(document).on('click', '.btn-seleccionar-pedido', function() {
        const idPedido = $(this).data('id-pedido');
        const total = $(this).data('total');
        const cliente = $(this).data('cliente');
        const fecha = $(this).data('fecha');
        
        // Llenar la información del pedido en el modal de pago
        $('#pedido-numero').text('#' + idPedido);
        $('#pedido-cliente').text(cliente);
        $('#pedido-fecha').text(fecha);
        $('#pedido-total').text(parseFloat(total).toFixed(2));
        
        // Establecer el ID del pedido en el formulario
        $('#id_pedido').val(idPedido);
        
        // Establecer el monto por defecto
        $('#monto').val(parseFloat(total).toFixed(2));
        
        // Cerrar el modal de selección y abrir el de pago
        const modalSeleccion = bootstrap.Modal.getInstance(document.getElementById('modalSeleccionarPedido'));
        if (modalSeleccion) {
            modalSeleccion.hide();
        }
        
        const modalPago = new bootstrap.Modal(document.getElementById('modalNuevoPago'));
        modalPago.show();
    });
    
    // Inicializar DataTable para la tabla de pedidos pendientes
    const tablaPedidosPendientes = $('#tablaPedidosPendientes').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div>'
        },
        processing: true,
        serverSide: false,
        ajax: {
            url: 'index.php?url=pedido&action=listarPendientes',
            type: 'GET',
            dataSrc: 'data',
            error: function(xhr, error, thrown) {
                console.error('Error al cargar pedidos pendientes:', error, thrown);
                toastr.error('Error al cargar los pedidos pendientes');
            }
        },
        columns: [
            { data: 'id_pedido' },
            { 
                data: 'cliente',
                render: function(data) {
                    return data ? `${data.nombre} ${data.apellido}` : 'Cliente no especificado';
                }
            },
            { 
                data: 'fecha_creacion',
                render: function(data) {
                    return new Date(data).toLocaleDateString();
                }
            },
            { 
                data: 'total',
                render: function(data) {
                    return `$${parseFloat(data).toFixed(2)}`;
                }
            },
            {
                data: 'detalles',
                render: function(data) {
                    if (!data || data.length === 0) return 'Sin productos';
                    return data.map(detalle => 
                        `${detalle.cantidad} x ${detalle.producto.nombre}`
                    ).join(', ');
                }
            },
            {
                data: 'id_pedido',
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-sm btn-primary btn-seleccionar-pedido" 
                                data-id="${data}"
                                data-cliente="${row.cliente ? row.cliente.nombre + ' ' + row.cliente.apellido : 'Cliente'}"
                                data-total="${row.total}">
                            <i class="fas fa-check me-1"></i> Seleccionar
                        </button>
                    `;
                },
                orderable: false,
                searchable: false
            }
        ],
        order: [[2, 'desc']]
    });

    // Manejar clic en el botón de seleccionar pedido
    $(document).on('click', '.btn-seleccionar-pedido', function() {
        const idPedido = $(this).data('id');
        const cliente = $(this).data('cliente');
        const total = parseFloat($(this).data('total'));
        
        // Cerrar el modal de selección
        const modalSeleccionar = bootstrap.Modal.getInstance(document.getElementById('modalSeleccionarPedido'));
        modalSeleccionar.hide();
        
        // Abrir el modal de pago
        const modalPago = new bootstrap.Modal(document.getElementById('modalNuevoPago'));
        
        // Configurar el formulario de pago
        $('#contenidoNuevo').html(`
            <form id="formPago" class="needs-validation" novalidate>
                <input type="hidden" name="id_pedido" value="${idPedido}">
                <div class="alert alert-info mb-4">
                    <h5 class="alert-heading">
                        <i class="fas fa-shopping-cart me-2"></i>Pago del Pedido #${idPedido}
                    </h5>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Cliente:</strong> ${cliente}</p>
                            <p class="mb-1"><strong>Total a Pagar:</strong> $${total.toFixed(2)}</p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="monto" class="form-label">Monto *</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="monto" name="monto" 
                                   step="0.01" min="0.01" max="${total}" value="${total.toFixed(2)}" required>
                        </div>
                        <div class="invalid-feedback">
                            Ingrese un monto válido (máximo $${total.toFixed(2)})
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="fecha" class="form-label">Fecha *</label>
                        <input type="date" class="form-control" id="fecha" name="fecha" required>
                        <div class="invalid-feedback">
                            La fecha es obligatoria
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="cod_metodo" class="form-label">Método de Pago *</label>
                        <select class="form-select" id="cod_metodo" name="cod_metodo" required>
                            <option value="">Seleccione un método</option>
                            <!-- Los métodos de pago se cargarán aquí -->
                        </select>
                        <div class="invalid-feedback">
                            Seleccione un método de pago
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="banco" class="form-label">Banco</label>
                        <input type="text" class="form-control" id="banco" name="banco" 
                               placeholder="Nombre del banco (opcional)">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="referencia" class="form-label">Referencia *</label>
                    <input type="text" class="form-control" id="referencia" name="referencia" 
                           placeholder="Número de referencia o transacción" required>
                    <div class="invalid-feedback">
                        La referencia es obligatoria
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Guardar Pago
                    </button>
                </div>
            </form>
        `);
        
        // Cargar métodos de pago
        cargarMetodosPago($('#cod_metodo'));
        
        // Establecer la fecha actual por defecto
        const today = new Date().toISOString().split('T')[0];
        $('#fecha').val(today);
    });
    
    // Manejar clic en el botón de seleccionar pedido
    $(document).on('click', '.btn-seleccionar-pedido', function() {
        const idPedido = $(this).data('id-pedido');
        const total = $(this).data('total');
        const cliente = $(this).data('cliente');
        const fecha = $(this).data('fecha');
        
        // Llenar los campos del formulario de pago
        $('#id_pedido').val(idPedido);
        $('#pedido-numero').text('#' + idPedido);
        $('#pedido-cliente').text(cliente);
        $('#pedido-fecha').text(fecha);
        $('#pedido-total').text(parseFloat(total).toFixed(2));
        $('#monto').val(parseFloat(total).toFixed(2));
        
        // Obtener instancias de los modales
        const modalSeleccion = bootstrap.Modal.getInstance(document.getElementById('modalSeleccionarPedido'));
        const modalPago = new bootstrap.Modal(document.getElementById('modalNuevoPago'));
        
        // Configurar evento para cuando se oculte el modal de selección
        $('#modalSeleccionarPedido').off('hidden.bs.modal').on('hidden.bs.modal', function () {
            // Mostrar el modal de pago después de que se oculte el de selección
            modalPago.show();
        });
        
        // Ocultar el modal de selección
        if (modalSeleccion) {
            modalSeleccion.hide();
        } else {
            // Si no hay instancia del modal, forzar el cierre y mostrar el de pago
            $('#modalSeleccionarPedido').modal('hide');
            // Pequeño retraso para asegurar que el modal se haya cerrado completamente
            setTimeout(() => {
                modalPago.show();
            }, 300);
        }
    });
    
    // Inicializar el modal de selección de pedido
    $('#modalSeleccionarPedido').on('show.bs.modal', function () {
        // Limpiar cualquier evento previo para evitar duplicados
        $(this).off('hidden.bs.modal');
    });
    
    // Manejar el cierre limpio del modal de pago
    $('#modalNuevoPago').on('hidden.bs.modal', function () {
        // Limpiar el formulario cuando se cierre el modal
        const form = document.getElementById('formPago');
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }
    });
    
    // Manejar clic en el botón de cerrar o cancelar del modal de pago
    $(document).on('click', '#btnCancelarPago, #modalNuevoPago .btn-close', function(e) {
        e.preventDefault();
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevoPago'));
        if (modal) {
            modal.hide();
        } else {
            $('#modalNuevoPago').modal('hide');
        }
        
        // Limpiar el formulario
        const form = document.getElementById('formPago');
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }
    });
    
    // Manejar clic en el botón de ver detalle
    $(document).on('click', '.btn-ver-detalle', function(e) {
        e.preventDefault();
        const idPedido = $(this).data('id-pedido');
        // Abrir en una nueva pestaña
        window.open(`index.php?url=pedido&action=verDetalle&id_pedido=${idPedido}`, '_blank');
    });
    
    // Manejar clic en el botón de ver detalle
    $(document).on('click', '.btn-ver-detalle', function() {
        const idPedido = $(this).data('id-pedido');
        // Aquí puedes implementar la lógica para mostrar el detalle del pedido
        // Por ejemplo, abrir una nueva pestaña o mostrar un modal con la información
        window.open(`index.php?url=pedido&action=verDetalle&id_pedido=${idPedido}`, '_blank');
    });
    
    // Manejar el envío del formulario de procesar pago
    $('#btnConfirmarPago').click(function() {
        const form = $('#formProcesarPago')[0];
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        const formData = {
            id_pedido: $('#pedido_id').val(),
            monto: $('#monto').val(),
            fecha_pago: $('#fecha_pago').val(),
            cod_metodo: $('#metodo_pago').val(),
            banco: $('#banco').val() || null,
            referencia: $('#referencia').val()
        };
        
        // Mostrar loading
        const $btn = $(this);
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...');
        
        // Enviar la solicitud al servidor
        $.ajax({
            url: 'index.php?url=pago&action=procesarPagoPedido',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message || 'Pago procesado correctamente');
                    $('#modalProcesarPago').modal('hide');
                    // Recargar la página para ver los cambios
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toastr.error(response.message || 'Error al procesar el pago');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al procesar el pago:', error);
                toastr.error('Error al conectar con el servidor');
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    manejarFormulario();
    $('#btnNuevoPago').click(() => {
        cargarFormulario('#modalNuevo', '#contenidoNuevo');
    });

    $('#btnToggleEstado').click(togglePagos);

    $(document).on('click', '.editar', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        console.log('Editando pago con ID:', id);
        
        $.ajax({
            url: `index.php?url=pago&action=formEditar&id_pago=${id}`,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log('Respuesta del servidor:', response);
                
                if (response && response.success && response.data) {
                    cargarFormulario('#modalEditar', '#contenidoEditar', response.data);
                } else {
                    toastr.error(response.message || 'Datos del pago no disponibles');
                    console.error('Respuesta inesperada:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al obtener pago:', error, xhr.responseText);
                try {
                    const errorResponse = JSON.parse(xhr.responseText);
                    toastr.error(errorResponse.message || 'Error al cargar datos del pago');
                } catch (e) {
                    toastr.error('Error al cargar datos del pago');
                }
            }
        });
    });

    $(document).on('click', '.eliminar', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        bootbox.confirm({
            title: "Confirmar Eliminación",
            message: `¿Está seguro de eliminar el pago con ID ${id}?`,
            buttons: {
                cancel: {
                    label: '<i class="fa fa-times"></i> Cancelar'
                },
                confirm: {
                    label: '<i class="fa fa-check"></i> Confirmar'
                }
            },
            callback: function(result) {
                if(result) {
                    confirmarAccion({
                        id: id,
                        url: 'index.php?url=pago&action=eliminar',
                        method: 'POST',
                        successMessage: 'Pago eliminado correctamente'
                    });
                }
            }
        });
    });

    $(document).on('click', '.restaurar', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        bootbox.confirm({
            title: "Confirmar Restauración",
            message: `¿Está seguro de restaurar el pago con ID ${id}?`,
            buttons: {
                cancel: {
                    label: '<i class="fa fa-times"></i> Cancelar'
                },
                confirm: {
                    label: '<i class="fa fa-check"></i> Confirmar'
                }
            },
            callback: function(result) {
                if(result) {
                    confirmarAccion({
                        id: id,
                        url: 'index.php?url=pago&action=restaurar',
                        method: 'POST',
                        successMessage: 'Pago restaurado correctamente'
                    });
                }
            }
        });
    });

    $.fn.dataTable.ext.errMode = 'none';
    $('#pagos').on('error.dt', function(e, settings, techNote, message) {
        console.error('Error en DataTables:', message);
        toastr.error('Error al cargar los datos de la tabla');
        inicializarDataTable();
    });

    inicializarDataTable();
});