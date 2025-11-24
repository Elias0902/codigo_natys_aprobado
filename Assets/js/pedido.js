$(document).ready(function() {
    // Configurar fecha actual
    $('#fecha').val(new Date().toISOString().split('T')[0]);

    // Inicializar dropdowns de Bootstrap manualmente
    function inicializarDropdowns() {
        // Inicializar el dropdown de reportes
        const dropdownReportes = document.getElementById('dropdownReportes');
        if (dropdownReportes) {
            new bootstrap.Dropdown(dropdownReportes);
        }
        
        // Inicializar todos los dropdowns en la página
        var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
        var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
            return new bootstrap.Dropdown(dropdownToggleEl);
        });
    }

    // Llamar a la inicialización de dropdowns
    inicializarDropdowns();

    // Manejar clic en opción de reporte por fechas
    $(document).on('click', '.reporte-option', function(e) {
        e.preventDefault();
        const tipo = $(this).data('tipo');
        if (tipo === 'fechas') {
            $('#modalReporteFechas').modal('show');
        }
    });

    // Configurar fechas por defecto para el modal de reporte por fechas
    const fechaFin = new Date();
    const fechaInicio = new Date();
    fechaInicio.setDate(fechaInicio.getDate() - 30);
    
    $('#fechaInicio').val(fechaInicio.toISOString().split('T')[0]);
    $('#fechaFin').val(fechaFin.toISOString().split('T')[0]);

    // Manejar clic en el botón de generar reporte
    $('#btnGenerarReporte').click(function() {
        const fechaInicio = $('#fechaInicio').val();
        const fechaFin = $('#fechaFin').val();
        const estado = $('#tipoEstado').val();
        
        if (!fechaInicio || !fechaFin) {
            toastr.error('Por favor seleccione ambas fechas');
            return;
        }
        
        if (new Date(fechaInicio) > new Date(fechaFin)) {
            toastr.error('La fecha de inicio no puede ser mayor a la fecha fin');
            return;
        }
        
        // Construir la URL del reporte
        let url = `index.php?url=pedido&action=reporte_lista&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;
        
        if (estado !== 'todos') {
            url += `&estado=${estado}`;
        }
        
        // Abrir el reporte en una nueva pestaña
        window.open(url, '_blank');
        $('#modalReporteFechas').modal('hide');
    });

    // Asignar evento al botón de agregar producto
    $(document).on('click', '#btnAgregarProducto', function() {
        $('#formAgregarProducto')[0].reset();
        $('#modalAgregarProducto').modal('show');
    });

    // Función para generar cards de pedidos pendientes en móvil
    function generarCardsPendientes(pedidos) {
        const container = $('#pendientesMovil');
        container.empty();
        
        if (pedidos.length === 0) {
            container.html('<div class="alert alert-info text-center">No hay pedidos pendientes</div>');
            return;
        }
        
        pedidos.forEach(function(pedido) {
            const card = `
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0">Pedido #${pedido.id_pedido}</h5>
                            <span class="badge badge-estado-pendiente">Pendiente</span>
                        </div>
                        
                        <div class="mb-2">
                            <div class="text-muted small">Fecha</div>
                            <div>${pedido.fecha}</div>
                        </div>
                        
                        <div class="mb-2">
                            <div class="text-muted small">Cliente</div>
                            <div><strong>${pedido.nomcliente}</strong></div>
                            <div class="small">${pedido.ced_cliente}</div>
                        </div>
                        
                        <div class="mb-2">
                            <div class="text-muted small">Total</div>
                            <div class="fw-bold text-success">$${parseFloat(pedido.total).toFixed(2)}</div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="text-muted small">Productos</div>
                            <div>${pedido.nombre_producto || 'Producto'} (${pedido.cant_producto})</div>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2">
                            <button class="btn btn-sm btn-info ver-detalle-pendiente" data-id="${pedido.id_pedido}" title="Ver detalle">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-primary editar-pedido" data-id="${pedido.id_pedido}" title="Editar">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            container.append(card);
        });
    }

    // Inicializar DataTable de pedidos pendientes
    const tablaPendientes = $('#tablaPendientes').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
            decimal: ",",
            thousands: "."
        },
        ajax: {
            url: 'index.php?url=pedido&action=listarPendientes&ajax=1',
            dataSrc: function(json) {
                generarCardsPendientes(json.data);
                return json.data;
            }
        },
        columns: [
            { data: 'id_pedido' },
            { 
                data: null,
                render: function(data) {
                    return `<strong>${data.nomcliente}</strong><br><small>${data.ced_cliente}</small>`;
                }
            },
            { data: 'fecha' },
            { 
                data: 'total',
                render: function(data) {
                    return `$${parseFloat(data).toFixed(2)}`;
                }
            },
            { 
                data: null,
                render: function(data) {
                    const nombreProducto = data.nombre_producto || 'Producto';
                    return `${nombreProducto} (${data.cant_producto})`;
                }
            },
            {
                data: null,
                render: function(data) {
                    return `
                        <div class="btn-group btn-group-sm" role="group">
                            <button class="btn btn-info btn-sm ver-detalle-pendiente" data-id="${data.id_pedido}" title="Ver detalle">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-primary btn-sm editar-pedido" data-id="${data.id_pedido}" title="Editar">
                                <i class="fas fa-sync-alt"></i>
                            </button>

                        </div>`;
                }
            }
        ]
    });

    // Función para generar cards móviles de pedidos generales
    function generarCardsPedidos(pedidos) {
        const container = $('#pedidosMovil');
        container.empty();
        
        pedidos.forEach(function(pedido) {
            let estadoBadge, acciones;
            
            if (pedido.estado == 0) {
                estadoBadge = '<span class="badge badge-estado-pendiente">Pendiente</span>';
                acciones = `
                    <button class="btn btn-sm btn-info ver-detalle" data-id="${pedido.id_pedido}" title="Ver detalle">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-primary editar-pedido" data-id="${pedido.id_pedido}" title="Editar">
                        <i class="fas fa-sync-alt"></i>
                    </button>`;
            } else if (pedido.estado == 1) {
                estadoBadge = '<span class="badge badge-estado-aprobado">Aprobado</span>';
                acciones = `
                    <button class="btn btn-sm btn-info ver-detalle" data-id="${pedido.id_pedido}" title="Ver detalle">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-danger eliminar-pedido" data-id="${pedido.id_pedido}" title="Eliminar">
                        <i class="fas fa-trash-alt"></i>
                    </button>`;
            } else {
                estadoBadge = '<span class="badge badge-estado-cancelado">Cancelado</span>';
                acciones = `
                    <button class="btn btn-sm btn-info ver-detalle" data-id="${pedido.id_pedido}" title="Ver detalle">
                        <i class="fas fa-eye"></i>
                    </button>`;
            }
            
            const nombreProducto = pedido.nombre_producto || 'Producto';
            
            const card = `
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0">Pedido #${pedido.id_pedido}</h5>
                            ${estadoBadge}
                        </div>
                        
                        <div class="mb-2">
                            <div class="text-muted small">Fecha</div>
                            <div>${pedido.fecha}</div>
                        </div>
                        
                        <div class="mb-2">
                            <div class="text-muted small">Cliente</div>
                            <div><strong>${pedido.nomcliente}</strong></div>
                            <div class="small">${pedido.ced_cliente}</div>
                        </div>
                        
                        <div class="mb-2">
                            <div class="text-muted small">Total</div>
                            <div class="fw-bold text-success">$${parseFloat(pedido.total).toFixed(2)}</div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="text-muted small">Productos</div>
                            <div>${nombreProducto} (${pedido.cant_producto})</div>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2">
                            ${acciones}
                        </div>
                    </div>
                </div>
            `;
            
            container.append(card);
        });
    }

    // Inicializar DataTable de pedidos principales
    const tablaPedidos = $('#tablaPedidos').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
            decimal: ",",
            thousands: "."
        },
        ajax: {
            url: 'index.php?url=pedido&action=listar&ajax=1',
            dataSrc: function(json) {
                // Filtrar solo pedidos aprobados (estado = 1) para la vista principal
                const pedidosAprobados = json.data.filter(pedido => pedido.estado == 1);
                generarCardsPedidos(pedidosAprobados);
                return pedidosAprobados;
            }
        },
        columns: [
            { data: 'id_pedido' },
            { data: 'fecha' },
            { 
                data: null,
                render: function(data) {
                    return `<strong>${data.nomcliente}</strong><br><small>${data.ced_cliente}</small>`;
                }
            },
            { 
                data: 'total',
                render: function(data) {
                    return `$${parseFloat(data).toFixed(2)}`;
                }
            },
            { 
                data: null,
                render: function(data) {
                    const nombreProducto = data.nombre_producto || 'Producto';
                    return `${nombreProducto} (${data.cant_producto})`;
                }
            },
            { 
                data: 'estado',
                render: function(data) {
                    if (data == 0) {
                        return '<span class="badge badge-estado-pendiente">Pendiente</span>';
                    } else if (data == 1) {
                        return '<span class="badge badge-estado-aprobado">Aprobado</span>';
                    } else {
                        return '<span class="badge badge-estado-cancelado">Cancelado</span>';
                    }
                }
            },
            {
                data: null,
                render: function(data) {
                    let acciones = `
                        <div class="btn-group btn-group-sm" role="group">
                            <button class="btn btn-info btn-sm ver-detalle" data-id="${data.id_pedido}" title="Ver detalle">
                                <i class="fas fa-eye"></i>
                            </button>`;

                    if (data.estado == 0) {
                        acciones += `
                            <button class="btn btn-primary btn-sm editar-pedido" data-id="${data.id_pedido}" title="Editar">
                                <i class="fas fa-sync-alt"></i>
                            </button>`;
                    } else if (data.estado == 1) {
                        acciones += `
                            <button class="btn btn-danger btn-sm eliminar-pedido" data-id="${data.id_pedido}" title="Eliminar">
                                <i class="fas fa-trash-alt"></i>
                            </button>`;
                    }

                    acciones += `</div>`;
                    return acciones;
                }
            }
        ]
    });

    // Mostrar modal de pedidos pendientes
    $('#btnVerPendientes').click(function() {
        tablaPendientes.ajax.reload();
        $('#modalPendientes').modal('show');
    });

    // Función para ver detalle del pedido
    function verDetallePedido(idPedido) {
        $.getJSON(`index.php?url=pedido&action=obtenerDetalle&id=${idPedido}`)
            .done(function(response) {
                if (response.success) {
                    const data = response.data;
                    
                    $('#pedidoId').text(data.pedido.id_pedido);
                    $('#detalleCliente').text(data.cliente.nombre_completo + ' (' + data.cliente.cedula + ')');
                    $('#detalleFecha').text(data.pedido.fecha_creacion_formatted);
                    $('#detalleTelefono').text(data.cliente.telefono);
                    $('#detalleDireccion').text(data.cliente.direccion);
                    $('#detalleTotal').text(data.pedido.total_formatted);
                    $('#detalleTotalFinal').text(data.pedido.total_formatted);
                    
                    let estadoBadge;
                    if (data.pedido.estado == 0) {
                        estadoBadge = '<span class="badge badge-estado-pendiente">Pendiente</span>';
                    } else if (data.pedido.estado == 1) {
                        estadoBadge = '<span class="badge badge-estado-aprobado">Aprobado</span>';
                    } else {
                        estadoBadge = '<span class="badge badge-estado-cancelado">Cancelado</span>';
                    }
                    $('#detalleEstado').html(estadoBadge);
                    
                    $('#detalleProductos').empty();
                    data.detalles.forEach(function(producto) {
                        $('#detalleProductos').append(`
                            <tr>
                                <td>${producto.nombre_producto}</td>
                                <td class="text-end">${producto.precio_unitario_formatted || producto.precio_formatted || '$0.00'}</td>
                                <td class="text-center">${producto.cantidad}</td>
                                <td class="text-end">${producto.subtotal_formatted}</td>
                            </tr>
                        `);
                    });
                    
                    $('#modalDetalle').modal('show');
                } else {
                    toastr.error(response.message || 'Error al cargar el detalle');
                }
            })
            .fail(function() {
                toastr.error('Error al cargar los detalles del pedido');
            });
    }

    // Manejador de eventos para ver detalles
    $(document).on('click', '.ver-detalle, .ver-detalle-pendiente', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const idPedido = $(this).data('id');
        verDetallePedido(idPedido);
    });



    // Eliminar pedido aprobado
    $(document).on('click', '.eliminar-pedido', function() {
        const idPedido = $(this).data('id');

        // Crear modal de confirmación personalizado
        const confirmModal = `
            <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="confirmDeleteModalLabel">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Confirmar Eliminación
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="text-center mb-4">
                                <div class="mb-3">
                                    <i class="fas fa-trash-alt fa-3x text-danger"></i>
                                </div>
                                <h5 class="fw-bold text-danger">¿Eliminar Pedido #${idPedido}?</h5>
                                <p class="text-muted mb-0">Esta acción no se puede deshacer.</p>
                            </div>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <strong>Advertencia:</strong> Se eliminará permanentemente el pedido aprobado y toda su información asociada.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>
                                Cancelar
                            </button>
                            <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                                <i class="fas fa-trash-alt me-1"></i>
                                Eliminar Pedido
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('body').append(confirmModal);
        $('#confirmDeleteModal').modal('show');

        // Manejar confirmación de eliminación
        $('#confirmDeleteBtn').off('click').on('click', function() {
            $('#confirmDeleteBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status"></span>Eliminando...');

            $.post('index.php?url=pedido&action=eliminar', {
                id_pedido: idPedido
            }, function(response) {
                const result = typeof response === 'string' ? JSON.parse(response) : response;
                if (result.success) {
                    toastr.success('Pedido eliminado exitosamente', 'Éxito', {
                        timeOut: 3000,
                        progressBar: true,
                        closeButton: true
                    });
                    tablaPedidos.ajax.reload();
                    $('#confirmDeleteModal').modal('hide');
                } else {
                    toastr.error(result.message, 'Error', {
                        timeOut: 4000,
                        progressBar: true,
                        closeButton: true
                    });
                }
            }, 'json').fail(function() {
                toastr.error('Error al procesar la solicitud', 'Error de Conexión', {
                    timeOut: 4000,
                    progressBar: true,
                    closeButton: true
                });
            }).always(function() {
                $('#confirmDeleteModal').modal('hide');
                $('#confirmDeleteModal').remove();
            });
        });

        // Limpiar modal cuando se cierre
        $('#confirmDeleteModal').on('hidden.bs.modal', function() {
            $(this).remove();
        });
    });

    // Función para cargar clientes via AJAX
    function cargarClientes(callback) {
        $.getJSON('index.php?url=cliente&action=listar')
            .done(function(response) {
                if (response && response.success) {
                    let options = '<option value="">Seleccione un cliente</option>';
                    response.data.forEach(function(cliente) {
                        options += `<option value="${cliente.ced_cliente}">${cliente.nomcliente} - ${cliente.ced_cliente}</option>`;
                    });
                    callback(options);
                } else {
                    toastr.error('Error al cargar la lista de clientes');
                    callback('<option value="">Error al cargar clientes</option>');
                }
            })
            .fail(function() {
                toastr.error('Error al conectar con el servidor para cargar clientes');
                callback('<option value="">Error al cargar clientes</option>');
            });
    }

    // Función para cargar el formulario de edición - CORREGIDA
    function cargarFormularioPedido(idPedido) {
        // Cerrar cualquier modal abierto primero
        $('.modal').modal('hide');

        // Esperar un momento para que se cierre el modal anterior
        setTimeout(function() {
            const $modal = $('#modalFormulario');
            $modal.find('.modal-body').html('<div class="text-center my-5"><div class="spinner-border" role="status"><span class="visually-hidden">Cargando...</span></div><div class="mt-2">Cargando datos del pedido...</div></div>');
            $modal.modal('show');

            $.getJSON(`index.php?url=pedido&action=obtenerDetalle&id=${idPedido}`)
                .done(function(response) {
                    if (response && response.success) {
                        const pedido = response.data.pedido;
                        const cliente = response.data.cliente;
                        const productos = response.data.detalles || [];

                        // Construir el formulario de edición
                        const formularioHTML = `
                            <form id="formPedido">
                                <input type="hidden" id="id_pedido" name="id_pedido" value="${pedido.id_pedido}">

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="ced_cliente" class="form-label">Cliente *</label>
                                        <select class="form-select" id="ced_cliente" name="ced_cliente" required>
                                            <option value="">Cargando clientes...</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="fecha" class="form-label">Fecha</label>
                                        <input type="date" class="form-control" id="fecha" name="fecha" required value="${pedido.fecha_creacion}">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Productos *</label>
                                    <div class="table-responsive">
                                        <table class="table table-sm" id="tablaProductos">
                                            <thead>
                                                <tr>
                                                    <th>Producto</th>
                                                    <th>Precio</th>
                                                    <th>Cantidad</th>
                                                    <th>Subtotal</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody id="productos-seleccionados">
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                                    <td id="total-pedido-form">${pedido.total_formatted}</td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-primary" id="btnAgregarProducto">
                                        <i class="fas fa-plus me-1"></i>Agregar Producto
                                    </button>
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        <i class="fas fa-times me-1"></i>Cancelar
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Actualizar Pedido
                                    </button>
                                </div>
                            </form>
                        `;

                        $modal.find('.modal-body').html(formularioHTML);
                        $('#modalFormularioTitulo').text('Actualizar Pedido #' + pedido.id_pedido);

                        // Cargar clientes y seleccionar el actual
                        cargarClientes(function(options) {
                            $('#ced_cliente').html(options);
                            if (cliente.cedula) {
                                $('#ced_cliente').val(cliente.cedula);
                            }
                        });

                        // Cargar productos en la tabla
                        $('#productos-seleccionados').empty();
                        productos.forEach(function(producto) {
                            agregarProductoATabla({
                                cod_producto: producto.cod_producto,
                                nombre: producto.nombre_producto,
                                precio: producto.precio_unitario,
                                cantidad: producto.cantidad,
                                subtotal: producto.subtotal
                            });
                        });

                        actualizarTotal();
                        asignarEventosFormulario();
                    } else {
                        const errorMsg = (response && response.message) || 'Error al cargar el pedido';
                        toastr.error(errorMsg);
                        $modal.modal('hide');
                    }
                })
                .fail(function() {
                    toastr.error('Error al cargar el pedido para edición');
                    $modal.modal('hide');
                });
        }, 300);
    }



    // Función para reasignar eventos después de reconstruir el formulario
    function asignarEventosFormulario() {
        $('#btnAgregarProducto').off('click').on('click', function() {
            $('#formAgregarProducto')[0].reset();
            $('#modalAgregarProducto').modal('show');
        });

        $('#formPedido').off('submit').on('submit', function(e) {
            e.preventDefault();
            enviarFormularioPedido();
        });
    }

    // Función para enviar el formulario
    function enviarFormularioPedido() {
        if (!$('#ced_cliente').val()) {
            toastr.warning('Por favor seleccione un cliente para el pedido');
            return;
        }
        
        if ($('#productos-seleccionados tr').length === 0) {
            toastr.error('Debe agregar al menos un producto al pedido');
            return;
        }
        
        const productos = [];
        $('#productos-seleccionados tr').each(function() {
            const $row = $(this);
            productos.push({
                cod_producto: $row.data('cod'),
                cantidad: parseInt($row.find('td:eq(2)').text()),
                precio: parseFloat($row.find('td:eq(1)').text()),
                subtotal: parseFloat($row.find('.subtotal').text())
            });
        });

        const formData = {
            id_pedido: $('#id_pedido').val() || null,
            ced_cliente: $('#ced_cliente').val(),
            fecha: $('#fecha').val(),
            productos: productos,
            total: parseFloat($('#total-pedido-form').text()),
            cant_producto: productos.reduce((sum, prod) => sum + prod.cantidad, 0)
        };

        const $submitBtn = $('#formPedido').find('button[type="submit"]');
        const originalBtnText = $submitBtn.html();
        $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Actualizando...');

        const url = formData.id_pedido 
            ? 'index.php?url=pedido&action=editar'
            : 'index.php?url=pedido&action=guardar';

        // Mostrar modal de carga
        const loadingModal = `
            <div class="modal fade show" id="loadingModal" tabindex="-1" style="display: block; background: rgba(0,0,0,0.8);" aria-modal="true" role="dialog">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body text-center py-5">
                            <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <h5 class="mb-2">Actualizando pedido...</h5>
                            <p class="text-muted mb-0">Por favor espere mientras se guarda la información</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('body').append(loadingModal);

        // Deshabilitar interacciones
        $('button').prop('disabled', true);
        $('input').prop('disabled', true);
        $('select').prop('disabled', true);
        $('textarea').prop('disabled', true);

        $.ajax({
            url: url,
            type: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json; charset=utf-8',
            dataType: 'json'
        })
        .done(function(response) {
            if (response && response.success) {
                toastr.success(response.message || 'Pedido actualizado con éxito');
                $('#modalFormulario').modal('hide');
                // Recargar después de un breve delay para mostrar el mensaje
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            } else {
                toastr.error(response.message || 'Error al procesar la solicitud');
            }
        })
        .fail(function(jqXHR) {
            toastr.error('Error en la comunicación con el servidor');
            console.error(jqXHR.responseText);
        })
        .always(function() {
            // Ocultar modal de carga y restaurar interacciones
            $('#loadingModal').remove();
            $('button').prop('disabled', false);
            $('input').prop('disabled', false);
            $('select').prop('disabled', false);
            $('textarea').prop('disabled', false);
            $submitBtn.prop('disabled', false).html(originalBtnText);
        });
    }

    // Editar pedido
    $(document).on('click', '.editar-pedido', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const idPedido = $(this).data('id');
        cargarFormularioPedido(idPedido);
    });

    // Nuevo pedido
    $('#btnNuevoPedido').click(function() {
        $('#modalFormularioTitulo').text('Nuevo Pedido');
        $('#modalFormulario').find('form')[0].reset();
        $('#id_pedido').val('');
        $('#productos-seleccionados').empty();
        $('#total-pedido-form').text('0.00');
        $('#fecha').val(new Date().toISOString().split('T')[0]);
        $('#modalFormulario').modal('show');
    });

    // Función para agregar producto a la tabla
    function agregarProductoATabla(producto) {
        const subtotal = producto.subtotal || (producto.precio * producto.cantidad);
        
        const row = `
            <tr data-cod="${producto.cod_producto}">
                <td>${producto.nombre}</td>
                <td>${parseFloat(producto.precio).toFixed(2)}</td>
                <td>${producto.cantidad}</td>
                <td class="subtotal">${parseFloat(subtotal).toFixed(2)}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger eliminar-producto">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#productos-seleccionados').append(row);
        
        actualizarTotal();
    }

    // Eliminar producto del pedido
    $('#productos-seleccionados').on('click', '.eliminar-producto', function() {
        $(this).closest('tr').remove();
        actualizarTotal();
    });

    // Actualizar total del pedido
    function actualizarTotal() {
        let total = 0;
        
        $('.subtotal').each(function() {
            total += parseFloat($(this).text());
        });
        
        $('#total-pedido-form').text(total.toFixed(2));
    }

    // Calcular subtotal cuando cambia cantidad o precio
    $('#cantidad, #precio').on('input', function() {
        const cantidad = parseFloat($('#cantidad').val()) || 0;
        const precio = parseFloat($('#precio').val()) || 0;
        const subtotal = cantidad * precio;
        $('#subtotal').val(subtotal.toFixed(2));
    });

    // Cuando se selecciona un producto, cargar su precio
    $('#cod_producto').change(function() {
        const selectedOption = $(this).find('option:selected');
        const precio = parseFloat(selectedOption.data('precio'));
        $('#precio').val(precio ? precio.toFixed(2) : '0.00');
        
        const cantidad = parseFloat($('#cantidad').val()) || 1;
        const subtotal = cantidad * (isNaN(precio) ? 0 : precio);
        $('#subtotal').val(subtotal.toFixed(2));
    });

    // Confirmar agregar producto
    $('#btnConfirmarProducto').click(function() {
        const form = $('#formAgregarProducto')[0];
        if (form.checkValidity()) {
            const codProducto = $('#cod_producto').val();
            const productoText = $('#cod_producto option:selected').text().split(' - ')[0];
            const unidad = $('#cod_producto option:selected').data('unidad');
            const precio = parseFloat($('#precio').val()) || 0;
            const cantidad = parseInt($('#cantidad').val()) || 1;
            const subtotal = parseFloat($('#subtotal').val()) || 0;
            
            if ($(`#productos-seleccionados tr[data-cod="${codProducto}"]`).length > 0) {
                toastr.warning('Este producto ya fue agregado al pedido');
                return;
            }
            
            const row = `
                <tr data-cod="${codProducto}">
                    <td>${productoText} (${unidad})</td>
                    <td>${precio.toFixed(2)}</td>
                    <td>${cantidad}</td>
                    <td class="subtotal">${subtotal.toFixed(2)}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger eliminar-producto">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#productos-seleccionados').append(row);
            
            actualizarTotal();
            $('#modalAgregarProducto').modal('hide');
            form.reset();
        } else {
            form.reportValidity();
        }
    });

    // Asignar eventos iniciales
    asignarEventosFormulario();
});