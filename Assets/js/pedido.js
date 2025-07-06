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
                                <button class="btn btn-success btn-sm marcar-pagado" data-id="${data.id_pedido}" title="Marcar como pagado">
                                    <i class="fas fa-check"></i>
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
                url: 'index.php?url=pedido&action=listar&estado=1', // Por defecto carga los por pagar
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
            
            // Actualizar la URL de ajax con el estado seleccionado
            let url = 'index.php?url=pedido&action=listar';
            if (estado !== 'all') {
                url += '&estado=' + estado;
            }
            
            tablaPedidos.ajax.url(url).load();
        });

        // Ver detalle del pedido
        function verDetallePedido(idPedido) {
            cargarFormularioPedido(idPedido, true);
        }

        // Manejador de eventos para ver detalles en ambas tablas
        $('#tablaPedidos, #tablaPendientes').on('click', '.ver-detalle', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const idPedido = $(this).data('id');
            verDetallePedido(idPedido);
        });

        // Nuevo pedido
        $('#btnNuevoPedido').click(function() {
            $('#modalFormularioTitulo').text('Nuevo Pedido');
            $('#formPedido')[0].reset();
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
                    <td class="text-end">${parseFloat(subtotal).toFixed(2)}</td>
                </tr>
            `;
            $('#productos-seleccionados').append(row);
            
            // Actualizar el total después de agregar un producto
            actualizarTotal();
        }

        // Función para cargar el formulario de pedido
        function cargarFormularioPedido(idPedido, modoLectura = false) {
            // Mostrar loading
            const $modal = $('#modalFormulario');
            $modal.find('.modal-body').html('<div class="text-center my-5"><div class="spinner-border" role="status"><span class="visually-hidden">Cargando...</span></div></div>');
            $modal.modal('show');
            
            // Determinar la acción según el modo
            const accion = modoLectura ? 'verDetalle' : 'formEditar';
            
            // Obtener detalles del pedido
            $.getJSON(`index.php?url=pedido&action=${accion}&id_pedido=${idPedido}`)
            .done(function(response) {
                console.log('Respuesta del servidor:', response);
                
                if (response && response.success) {
                    const pedido = response.data;
                    const esModoLectura = modoLectura || pedido.modo_lectura || false;
                    
                    // Crear formulario
                    const formHtml = `
                        <form id="formPedido" class="needs-validation" novalidate>
                            <input type="hidden" id="id_pedido" name="id_pedido" value="">
                            <div class="mb-3">
                                <label for="ced_cliente" class="form-label">Cliente</label>
                                <select class="form-select" id="ced_cliente" name="ced_cliente" ${esModoLectura ? 'disabled' : 'required'}>
                                    <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?= $cliente['ced_cliente'] ?>">
                                        <?= htmlspecialchars($cliente['nomcliente'] . ' - ' . $cliente['ced_cliente']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="fecha" class="form-label">Fecha</label>
                                <input type="date" class="form-control" id="fecha" name="fecha" ${esModoLectura ? 'disabled' : 'required'}>
                            </div>
                            
                            ${!esModoLectura ? `
                            <div class="mb-3">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarProducto">
                                    <i class="fas fa-plus"></i> Agregar Producto
                                </button>
                            </div>` : ''}
                            
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Producto</th>
                                            <th>Precio</th>
                                            <th>Cantidad</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody id="productos-seleccionados">
                                        <!-- Productos se agregarán aquí -->
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3" class="text-end">Total:</th>
                                            <th class="text-end" id="total-pedido-form">0.00</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-3">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    ${esModoLectura ? 'Cerrar' : 'Cancelar'}
                                </button>
                                ${!esModoLectura ? `
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> Guardar Cambios
                                </button>` : ''}
                            </div>
                        </form>
                    `;
                    
                    // Reemplazar el contenido del modal
                    $modal.find('.modal-body').html(formHtml);
                    $modal.find('.modal-title').text(`${esModoLectura ? 'Detalles del Pedido' : 'Editar Pedido'} #${pedido.id_pedido}`);
                    
                    // Llenar datos básicos
                    $('#id_pedido').val(pedido.id_pedido);
                    $('#ced_cliente').val(pedido.ced_cliente);
                    $('#fecha').val(pedido.fecha);
                    
                    // Agregar productos a la tabla
                    if (pedido.productos && pedido.productos.length > 0) {
                        pedido.productos.forEach(function(producto) {
                            agregarProductoATabla({
                                cod_producto: producto.cod_producto,
                                nombre: producto.nombre,
                                precio: producto.precio,
                                cantidad: producto.cantidad,
                                subtotal: producto.subtotal,
                                modoLectura: esModoLectura
                            });
                        });
                        
                        actualizarTotal();
                    }
                    
                    // Si es modo lectura, ocultar botones de eliminar producto
                    if (esModoLectura) {
                        $('.eliminar-producto').hide();
                    }
                    
                    // Inicializar el validador del formulario si no es modo lectura
                    if (!esModoLectura) {
                        initFormValidation();
                    }
                    
                } else {
                    const errorMsg = (response && response.message) || 'Error al cargar el pedido';
                    console.error('Error en la respuesta:', errorMsg);
                    toastr.error(errorMsg);
                    $modal.modal('hide');
                }
            })
            .fail(function(xhr, status, error) {
                console.error('Error en la petición AJAX:', status, error);
                console.error('Respuesta del servidor:', xhr.responseText);
                toastr.error('Error al cargar el pedido. Por favor, intente nuevamente.');
                $modal.modal('hide');
            });
        }

        // Editar pedido
        $('#tablaPedidos').on('click', '.editar-pedido', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const idPedido = $(this).data('id');
            cargarFormularioPedido(idPedido, false);
        });

        // Nuevo pedido
        $('#btnNuevoPedido').click(function() {
            $('#modalFormularioTitulo').text('Nuevo Pedido');
            $('#formPedido')[0].reset();
            $('#id_pedido').val('');
            $('#productos-seleccionados').empty();
            $('#total-pedido-form').text('0.00');
            $('#modalFormulario').modal('show');
        });

        // Función para agregar producto a la tabla
        function agregarProductoATabla(producto) {
            const row = `
                <tr data-cod="${producto.cod_producto}">
                    <td>${producto.nombre}</td>
                    <td>${parseFloat(producto.precio).toFixed(2)}</td>
                    <td>${producto.cantidad}</td>
                    <td class="subtotal">${parseFloat(producto.subtotal).toFixed(2)}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger eliminar-producto">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#productos-seleccionados').append(row);
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
            
            // Calcular subtotal
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
                
                // Verificar si el producto ya fue agregado
                if ($(`#productos-seleccionados tr[data-cod="${codProducto}"]`).length > 0) {
                    toastr.warning('Este producto ya fue agregado al pedido');
                    return;
                }
                
                // Agregar fila a la tabla
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
                
                // Actualizar total
                actualizarTotal();
                
                // Cerrar modal y limpiar formulario
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
            
            // Validar que hay al menos un producto
            if ($('#productos-seleccionados tr').length === 0) {
                toastr.error('Debe agregar al menos un producto al pedido');
                return;
            }
            
            // Obtener datos de los productos
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

            // Calcular total y cantidad de productos
            const total = parseFloat($('#total-pedido-form').text());
            const cant_producto = productos.reduce((sum, prod) => sum + prod.cantidad, 0);

            // Crear objeto con los datos del formulario
            const formData = {
                id_pedido: $('#id_pedido').val() || null,
                ced_cliente: $('#ced_cliente').val(),
                fecha: $('#fecha').val(),
                productos: productos,
                total: total,
                cant_producto: cant_producto
            };

            // Mostrar loading
            const $submitBtn = $(this).find('button[type="submit"]');
            const originalBtnText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...');

            // Determinar la URL de la acción
            const url = formData.id_pedido 
                ? 'index.php?url=pedido&action=actualizar'
                : 'index.php?url=pedido&action=guardar';

            // Enviar datos al servidor
            $.ajax({
                url: url,
                type: 'POST',
                data: JSON.stringify(formData),
                contentType: 'application/json; charset=utf-8',
                dataType: 'json',
                processData: false,
                cache: false,
                timeout: 30000 // 30 segundos de timeout
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
            .fail(function(jqXHR, textStatus, errorThrown) {
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

        // Función para agregar un producto a la tabla
        function agregarProductoATabla(producto) {
            const fila = `
                <tr data-codigo="${producto.cod_producto}">
                    <td>${producto.nombre}</td>
                    <td class="precio">${parseFloat(producto.precio).toFixed(2)}</td>
                    <td>${producto.cantidad}</td>
                    <td class="subtotal">${parseFloat(producto.subtotal).toFixed(2)}</td>
p                </tr>
            `;
            
            $('#productos-seleccionados').append(fila);
        }
    });