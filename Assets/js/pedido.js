$(document).ready(function() {
    let table;
    let mostrandoEliminados = false;
    let detalles = [];

    toastr.options = {
        "closeButton": true,
        "progressBar": false,
        "positionClass": "toast-top-right",
        "timeOut": "5000",
        "escapeHtml": true
    };

    function inicializarDataTable() {
        if ($.fn.DataTable.isDataTable('#pedidos')) {
            table.destroy();
            $('#pedidos').empty();
        }

        table = $('#pedidos').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div>'
            },
            processing: true,
            serverSide: false,
            ajax: {
                url: mostrandoEliminados 
                    ? 'index.php?url=pedido&action=listarEliminados' 
                    : 'index.php?url=pedido&action=listar',
                type: 'GET',
                dataSrc: 'data',
                error: function(xhr, error, thrown) {
                    console.error('Error en AJAX:', error, thrown);
                    toastr.error('Error al cargar los datos de pedidos');
                }
            },
            columns: [
                { data: 'id_pedido' },
                { 
                    data: 'fecha',
                    render: function(data) {
                        return new Date(data).toLocaleDateString('es-ES');
                    }
                },
                { data: 'nomcliente' },
                { 
                    data: 'total',
                    render: function(data) {
                        return `$${parseFloat(data).toFixed(2)}`;
                    }
                },
                { data: 'cant_producto' },
                { data: 'metodo_pago' },
                { 
                    data: 'estado',
                    render: function(data) {
                        return `<span class="badge ${data == 1 ? 'bg-success' : 'bg-secondary'}">
                                ${data == 1 ? 'Activo' : 'Inactivo'}
                                </span>`;
                    }
                },
                {
                    data: 'id_pedido',
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
                                <button class="btn btn-sm btn-info detalle" data-id="${data}" title="Ver detalles">
                                    <i class="fas fa-list"></i>
                                </button>
                            </div>
                        `;
                    },
                    orderable: false,
                    className: 'text-center'
                }
            ],
            order: [[0, 'desc']],
            responsive: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Todos']],
            dom: '<"top"lf>rt<"bottom"ip><"clear">',
            initComplete: function() {
                console.log('DataTable de pedidos inicializado correctamente');
            }
        });
    }

    function recargarTabla() {
        if (table) {
            table.ajax.url(
                mostrandoEliminados 
                    ? 'index.php?url=pedido&action=listarEliminados' 
                    : 'index.php?url=pedido&action=listar'
            ).load(function(json) {
                console.log('Datos de pedidos cargados:', json);
            }, false);
        } else {
            inicializarDataTable();
        }
    }

    function togglePedidos() {
        mostrandoEliminados = !mostrandoEliminados;
        console.log('Alternando a:', mostrandoEliminados ? 'Eliminados' : 'Activos');
        const btn = $('#btnToggleEstado');
        btn.html(`<i class="fas ${mostrandoEliminados ? 'fa-user-check' : 'fa-trash-restore'} me-2"></i>
                  ${mostrandoEliminados ? 'Mostrar Activos' : 'Mostrar Eliminados'}`);
        btn.removeClass('btn-warning btn-info').addClass(mostrandoEliminados ? 'btn-info' : 'btn-warning');
        recargarTabla();
    }

    function actualizarTablaProductos() {
        const tbody = $('#detallesProductos');
        tbody.empty();
        
        let total = 0;
        
        detalles.forEach((detalle, index) => {
            // Conversión segura a números
            const precio = parseFloat(detalle.precio) || 0;
            const cantidad = parseInt(detalle.cantidad) || 0;
            const subtotal = parseFloat(detalle.subtotal) || 0;
            
            total += subtotal;
            
            tbody.append(`
                <tr>
                    <td>${detalle.cod_producto} - ${detalle.producto || $('#productoSelect option[value="' + detalle.cod_producto + '"]').data('nombre') || 'Producto'}</td>
                    <td>$${precio.toFixed(2)}</td>
                    <td>${cantidad}</td>
                    <td>$${subtotal.toFixed(2)}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger btnEliminarProducto" data-index="${index}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `);
        });
        
        $('#total').val(total.toFixed(2));
        $('#detalles').val(JSON.stringify(detalles));
    }

    function manejarAgregarProducto() {
        $('#btnAgregarProducto').off('click').on('click', function() {
            const productoSelect = $('#productoSelect');
            const productoOption = productoSelect.find('option:selected');
            
            if (!productoOption.val()) {
                toastr.error('Seleccione un producto válido');
                return;
            }

            // Conversión segura de cantidad
            const cantidad = parseInt($('#cantidadProducto').val());
            if (isNaN(cantidad) || cantidad < 1) {
                toastr.error('Ingrese una cantidad válida (mínimo 1)');
                return;
            }

            // Conversión segura de precio
            const precio = parseFloat(productoOption.data('precio'));
            if (isNaN(precio)) {
                toastr.error('El producto no tiene un precio válido');
                return;
            }

            const subtotal = precio * cantidad;

            // Agregar a la lista de detalles
            detalles.push({
                cod_producto: productoOption.val(),
                producto: productoOption.data('nombre'),
                precio: precio,
                cantidad: cantidad,
                subtotal: subtotal
            });
            
            // Actualizar tabla
            actualizarTablaProductos();
            
            // Limpiar selección
            productoSelect.val('').trigger('change');
            $('#cantidadProducto').val(1);
        });
    }

    function manejarEliminarProducto() {
        $(document).on('click', '.btnEliminarProducto', function() {
            const index = $(this).data('index');
            detalles.splice(index, 1);
            actualizarTablaProductos();
        });
    }

    function inicializarComponentes() {
        // Inicializar select2
        if ($('#clienteSelect').length) {
            $('#clienteSelect').select2({
                placeholder: 'Seleccione un cliente',
                width: '100%'
            });
        }
        
        if ($('#metodoPagoSelect').length) {
            $('#metodoPagoSelect').select2({
                placeholder: 'Seleccione un método de pago',
                width: '100%'
            });
        }
        
        // Configurar fecha actual por defecto
        if ($('#fechaPedido').length) {
            $('#fechaPedido').val(new Date().toISOString().substr(0, 10));
        }

        // Mostrar/ocultar campos según método de pago
        $('#metodoPagoSelect').change(function() {
            const metodo = $(this).val();
            const esTransferencia = metodo === 'TRANSF';
            
            $('#bancoContainer, #referenciaContainer').toggle(esTransferencia);
            
            if (!esTransferencia) {
                $('#banco, #referencia').val('');
            }
        }).trigger('change');
    }

    const cargarFormulario = (modalId, contenidoId, datos = null) => {
        if (datos) {
            // Para edición, cargar datos del pedido
            $.ajax({
                url: `index.php?url=pedido&action=formEditar&id_pedido=${datos.id_pedido}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response && response.success && response.data) {
                        $.ajax({
                            url: 'index.php?url=pedido&action=formNuevo',
                            type: 'GET',
                            success: function(formHtml) {
                                $(contenidoId).html(formHtml);
                                $(modalId).modal('show');
                                
                                // Llenar datos del formulario
                                const pedido = response.data.pedido;
                                $('#id_pedido').val(pedido.id_pedido);
                                $('#fechaPedido').val(pedido.fecha);
                                $('#clienteSelect').val(pedido.ced_cliente).trigger('change');
                                $('#metodoPagoSelect').val(pedido.cod_metodo).trigger('change');
                                $('#banco').val(pedido.banco || '');
                                $('#referencia').val(pedido.referencia || '');
                                
                                // Llenar detalles de productos con conversión segura
                                detalles = (response.data.detalles || []).map(detalle => ({
                                    cod_producto: detalle.cod_producto || '',
                                    producto: detalle.producto || 'Producto desconocido',
                                    precio: parseFloat(detalle.precio) || 0,
                                    cantidad: parseInt(detalle.cantidad) || 0,
                                    subtotal: parseFloat(detalle.subtotal) || 0
                                }));
                                
                                // Actualizar tabla de productos
                                actualizarTablaProductos();
                                inicializarComponentes();
                            },
                            error: function(xhr, status, error) {
                                toastr.error('Error al cargar el formulario');
                                console.error('Error:', error);
                            }
                        });
                    } else {
                        toastr.error(response.message || 'Datos del pedido no disponibles');
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error('Error al cargar datos del pedido');
                    console.error('Error:', error);
                }
            });
        } else {
            // Para nuevo pedido
            detalles = [];
            $.ajax({
                url: 'index.php?url=pedido&action=formNuevo',
                type: 'GET',
                success: function(response) {
                    $(contenidoId).html(response);
                    $(modalId).modal('show');
                    inicializarComponentes();
                },
                error: function(xhr, status, error) {
                    toastr.error('Error al cargar el formulario');
                    console.error('Error:', error);
                }
            });
        }
    };

    const confirmarAccion = ({ id, url, method, successMessage }) => {
        $.ajax({
            url: url,
            type: method,
            data: { id_pedido: id },
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
        $(document).on('submit', '#formPedido', function(e) {
            e.preventDefault();
            
            const form = this;
            const formData = $(form).serialize();
            const isNew = !form.querySelector('#id_pedido').value;
            const actionUrl = isNew 
                ? 'index.php?url=pedido&action=guardar' 
                : 'index.php?url=pedido&action=actualizar';
            
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
                    }
                } else {
                    toastr.error(response.message);
                }
            })
            .fail((xhr, status, error) => {
                console.error('Error en la petición:', error, xhr.responseText);
                toastr.error(`Error en la solicitud: ${error}`);
            });
        });
    };

    // Manejar clic en botón de detalles
    $(document).on('click', '.detalle', function(e) {
        e.preventDefault();
        const idPedido = $(this).data('id');
        
        $.ajax({
            url: `index.php?url=pedido&action=detalle&id_pedido=${idPedido}`,
            type: 'GET',
            success: function(response) {
                const modal = $('#modalDetalle');
                modal.find('.modal-body').html(response);
                modal.modal('show');
            },
            error: function(xhr, status, error) {
                toastr.error('Error al cargar los detalles del pedido');
                console.error('Error:', error);
            }
        });
    });

    // Inicialización
    inicializarDataTable();
    manejarFormulario();
    manejarAgregarProducto();
    manejarEliminarProducto();
    
    $('#btnNuevoPedido').click(() => {
        cargarFormulario('#modalNuevo', '#contenidoNuevo');
    });

    $('#btnToggleEstado').click(togglePedidos);

    $(document).on('click', '.editar', function(e) {
        e.preventDefault();
        const idPedido = $(this).data('id');
        
        $.ajax({
            url: `index.php?url=pedido&action=obtenerPedido&id_pedido=${idPedido}`,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response && response.success && response.data) {
                    cargarFormulario('#modalEditar', '#contenidoEditar', response.data);
                } else {
                    toastr.error(response.message || 'Datos del pedido no disponibles');
                }
            },
            error: function(xhr, status, error) {
                toastr.error('Error al cargar datos del pedido');
                console.error('Error:', error);
            }
        });
    });

    $(document).on('click', '.eliminar', function(e) {
        e.preventDefault();
        const idPedido = $(this).data('id');
        bootbox.confirm({
            title: "Confirmar Eliminación",
            message: `¿Está seguro de eliminar el pedido #${idPedido}?`,
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
                        id: idPedido,
                        url: 'index.php?url=pedido&action=eliminar',
                        method: 'POST',
                        successMessage: 'Pedido eliminado correctamente'
                    });
                }
            }
        });
    });

    $(document).on('click', '.restaurar', function(e) {
        e.preventDefault();
        const idPedido = $(this).data('id');
        bootbox.confirm({
            title: "Confirmar Restauración",
            message: `¿Está seguro de restaurar el pedido #${idPedido}?`,
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
                        id: idPedido,
                        url: 'index.php?url=pedido&action=restaurar',
                        method: 'POST',
                        successMessage: 'Pedido restaurado correctamente'
                    });
                }
            }
        });
    });

    // Manejar errores de DataTables
    $.fn.dataTable.ext.errMode = 'none';
    $('#pedidos').on('error.dt', function(e, settings, techNote, message) {
        console.error('Error en DataTables:', message);
        toastr.error('Error al cargar los datos de la tabla');
        inicializarDataTable();
    });
});