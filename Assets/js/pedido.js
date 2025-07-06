$(document).ready(function() {
    // Configurar fecha actual
    $('#fecha').val(new Date().toISOString().split('T')[0]);

    // Inicializar DataTable de pedidos pendientes
    const tablaPendientes = $('#tablaPendientes').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
            decimal: ",",
            thousands: "."
        },
        ajax: {
            url: 'index.php?url=pedido&action=listarPendientes',
            dataSrc: 'data'
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
            { data: 'cant_producto' },
            {
                data: null,
                render: function(data) {
                    return `
                        <div class="btn-group btn-group-sm" role="group">
                            <button class="btn btn-info btn-sm ver-detalle" data-id="${data.id_pedido}" title="Ver detalle">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-primary btn-sm editar-pedido" data-id="${data.id_pedido}" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>`;
                }
            }
        ]
    });

    // Inicializar DataTable de pedidos principales
    const tablaPedidos = $('#tablaPedidos').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
            decimal: ",",
            thousands: "."
        },
        ajax: {
            url: 'index.php?url=pedido&action=listar&estado=1', 
            dataSrc: 'data'
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
            { data: 'cant_producto' },
            { 
                data: 'estado',
                render: function(data) {
                    return data == 1 
                        ? '<span class="badge bg-success">Pagado</span>' 
                        : '<span class="badge bg-warning text-dark">Por pagar</span>';
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
                                <i class="fas fa-edit"></i>
                            </button>`;
                    }
                    
                    acciones += `
                            <button class="btn btn-danger btn-sm eliminar-pedido" data-id="${data.id_pedido}" title="Eliminar">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>`;
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

    // Marcar pedido como pagado
    $('#tablaPendientes').on('click', '.marcar-pagado', function() {
        const idPedido = $(this).data('id');
        if (confirm('¿Está seguro de marcar este pedido como pagado?')) {
            $.post('index.php?url=pedido&action=marcarPagado', { id_pedido: idPedido })
                .done(function(response) {
                    const result = typeof response === 'string' ? JSON.parse(response) : response;
                    if (result.success) {
                        toastr.success('Pedido marcado como pagado');
                        tablaPendientes.ajax.reload();
                        tablaPedidos.ajax.reload();
                    } else {
                        toastr.error(result.message || 'Error al actualizar el pedido');
                    }
                })
                .fail(function() {
                    toastr.error('Error al procesar la solicitud');
                });
        }
    });

    // Filtrar por estado
    $('.filter-btn').click(function() {
        const estado = $(this).data('estado');
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        
        let url = 'index.php?url=pedido&action=listar';
        if (estado !== 'all') {
            url += '&estado=' + estado;
        }
        
        tablaPedidos.ajax.url(url).load();
    });

    // Función para ver detalle del pedido (modalDetalle)
    function verDetallePedido(idPedido) {
        $.getJSON(`index.php?url=pedido&action=verDetalle&id_pedido=${idPedido}`)
            .done(function(response) {
                if (response.success) {
                    const data = response.data;
                    
                    // Llenar modal de detalle
                    $('#pedidoId').text(data.pedido.id_pedido);
                    $('#detalleCliente').text(data.cliente.nomcliente + ' (' + data.cliente.ced_cliente + ')');
                    $('#detalleFecha').text(data.pedido.fecha);
                    $('#detalleTelefono').text(data.cliente.telefono);
                    $('#detalleDireccion').text(data.cliente.direccion);
                    $('#detalleTotal').text(data.pedido.total_formatted || parseFloat(data.pedido.total).toFixed(2));
                    $('#detalleTotalFinal').text(data.pedido.total_formatted || parseFloat(data.pedido.total).toFixed(2));
                    
                    // Estado
                    const estadoBadge = data.pedido.estado == 1 
                        ? '<span class="badge bg-success">Pagado</span>' 
                        : '<span class="badge bg-warning text-dark">Pendiente</span>';
                    $('#detalleEstado').html(estadoBadge);
                    
                    // Productos
                    $('#detalleProductos').empty();
                    data.productos.forEach(function(producto) {
                        $('#detalleProductos').append(`
                            <tr>
                                <td>${producto.nombre}</td>
                                <td class="text-end">${producto.precio_unitario_formatted || parseFloat(producto.precio).toFixed(2)}</td>
                                <td class="text-center">${producto.cantidad}</td>
                                <td class="text-end">${producto.subtotal_formatted || parseFloat(producto.subtotal).toFixed(2)}</td>
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

    // Manejador de eventos para ver detalles en ambas tablas
    $('#tablaPedidos, #tablaPendientes').on('click', '.ver-detalle', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const idPedido = $(this).data('id');
        verDetallePedido(idPedido);
    });

    // Función para cargar el formulario de edición (modalFormulario)
    function cargarFormularioPedido(idPedido) {
        // Mostrar loading
        const $modal = $('#modalFormulario');
        $modal.find('.modal-body').html('<div class="text-center my-5"><div class="spinner-border" role="status"><span class="visually-hidden">Cargando...</span></div></div>');
        $modal.modal('show');
        
        // Obtener detalles del pedido para edición
        $.getJSON(`index.php?url=pedido&action=formEditar&id_pedido=${idPedido}`)
            .done(function(response) {
                if (response && response.success) {
                    // Restaurar el formulario original
                    $modal.find('.modal-body').html(`
                        <form id="formPedido">
                            <input type="hidden" id="id_pedido" name="id_pedido">
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="ced_cliente" class="form-label">Cliente *</label>
                                    <select class="form-select" id="ced_cliente" name="ced_cliente" required>
                                        <option value="">Seleccione un cliente</option>
                                        ${$('#ced_cliente').html().split('<option value="">Seleccione un cliente</option>')[1]}
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="fecha" class="form-label">Fecha</label>
                                    <input type="date" class="form-control" id="fecha" name="fecha" required>
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
                                            <!-- Productos se agregarán aquí -->
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                                <td id="total-pedido-form">0.00</td>
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
                                    <i class="fas fa-save me-1"></i>Guardar Cambios
                                </button>
                            </div>
                        </form>
                    `);

                    const pedido = response.data.pedido;
                    const cliente = response.data.cliente;
                    const productos = response.data.productos;
                    
                    // Configurar título
                    $('#modalFormularioTitulo').text('Editar Pedido #' + pedido.id_pedido);
                    
                    // Llenar datos básicos
                    $('#id_pedido').val(pedido.id_pedido);
                    $('#ced_cliente').val(cliente.ced_cliente);
                    $('#fecha').val(pedido.fecha.split('T')[0]);
                    
                    // Limpiar y agregar productos
                    $('#productos-seleccionados').empty();
                    productos.forEach(function(producto) {
                        agregarProductoATabla({
                            cod_producto: producto.cod_producto,
                            nombre: producto.nombre,
                            precio: producto.precio,
                            cantidad: producto.cantidad,
                            subtotal: producto.subtotal
                        });
                    });
                    
                    actualizarTotal();
                    
                    // Reasignar eventos después de reconstruir el formulario
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
    }

    // Función para reasignar eventos después de reconstruir el formulario
    function asignarEventosFormulario() {
        // Asignar evento al botón de agregar producto
        $('#btnAgregarProducto').off('click').on('click', function() {
            $('#formAgregarProducto')[0].reset();
            $('#modalAgregarProducto').modal('show');
        });

        // Asignar evento al formulario principal
        $('#formPedido').off('submit').on('submit', function(e) {
            e.preventDefault();
            enviarFormularioPedido();
        });
    }

    // Función para enviar el formulario (separada para reutilización)
    function enviarFormularioPedido() {
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
        $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...');

        const url = formData.id_pedido 
            ? 'index.php?url=pedido&action=actualizar'
            : 'index.php?url=pedido&action=guardar';

        $.ajax({
            url: url,
            type: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json; charset=utf-8',
            dataType: 'json'
        })
        .done(function(response) {
            if (response && response.success) {
                toastr.success(response.message || 'Operación realizada con éxito');
                $('#modalFormulario').modal('hide');
                $('#tablaPedidos').DataTable().ajax.reload();
            } else {
                toastr.error(response.message || 'Error al procesar la solicitud');
            }
        })
        .fail(function(jqXHR) {
            toastr.error('Error en la comunicación con el servidor');
            console.error(jqXHR.responseText);
        })
        .always(function() {
            $submitBtn.prop('disabled', false).html(originalBtnText);
        });
    }

    // Editar pedido
    $('#tablaPedidos').on('click', '.editar-pedido', function(e) {
        e.preventDefault();
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

    // Eliminar pedido
    $('#tablaPedidos').on('click', '.eliminar-pedido', function() {
        const idPedido = $(this).data('id');
        
        if (confirm('¿Está seguro de eliminar este pedido? Esta acción no se puede deshacer.')) {
            $.post('index.php?url=pedido&action=eliminar', {
                id_pedido: idPedido
            }, function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    tablaPedidos.ajax.reload();
                } else {
                    toastr.error(response.message);
                }
            }, 'json');
        }
    });

    // Agregar producto al formulario
    $('#btnAgregarProducto').click(function() {
        $('#formAgregarProducto')[0].reset();
        $('#modalAgregarProducto').modal('show');
    });

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

    // Enviar formulario de pedido
    $('#formPedido').submit(function(e) {
        e.preventDefault();
        
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

        const total = parseFloat($('#total-pedido-form').text());
        const cant_producto = productos.reduce((sum, prod) => sum + prod.cantidad, 0);

        const formData = {
            id_pedido: $('#id_pedido').val() || null,
            ced_cliente: $('#ced_cliente').val(),
            fecha: $('#fecha').val(),
            productos: productos,
            total: total,
            cant_producto: cant_producto
        };

        const $submitBtn = $(this).find('button[type="submit"]');
        const originalBtnText = $submitBtn.html();
        $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...');

        const url = formData.id_pedido 
            ? 'index.php?url=pedido&action=actualizar'
            : 'index.php?url=pedido&action=guardar';

        $.ajax({
            url: url,
            type: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            processData: false
        })
        .done(function(response) {
            if (response && response.success) {
                toastr.success(response.message || 'Operación realizada con éxito');
                $('#modalFormulario').modal('hide');
                tablaPedidos.ajax.reload();
            } else {
                const errorMsg = response && response.message 
                    ? response.message 
                    : 'Error desconocido al procesar la solicitud';
                toastr.error(errorMsg);
            }
        })
        .fail(function(jqXHR) {
            let errorMsg = 'Error en la comunicación con el servidor';
            
            try {
                const response = jqXHR.responseJSON || JSON.parse(jqXHR.responseText);
                errorMsg = response.message || errorMsg;
            } catch (e) {
                if (jqXHR.responseText) {
                    errorMsg = 'Error: ' + jqXHR.responseText.substring(0, 200);
                }
            }
            
            toastr.error(errorMsg);
        })
        .always(function() {
            $submitBtn.prop('disabled', false).html(originalBtnText);
        });
    });
});