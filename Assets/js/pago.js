$(document).ready(function() {
    let table;
    let mostrandoEliminados = false;
    
    // Configuración de toastr
    toastr.options = {
        "closeButton": true,
        "progressBar": false,
        "positionClass": "toast-top-right",
        "timeOut": "5000",
        "escapeHtml": true
    };

    // Inicializar DataTable
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
                                ${data == 1 ? 'Aprobado' : 'Inactivo'}
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
            dom: '<"top"lf>rt<"bottom"ip><"clear">'
        });
    }

    // Recargar tabla
    function recargarTabla() {
        if (table) {
            table.ajax.reload(null, false);
        } else {
            inicializarDataTable();
        }
    }

    // Alternar entre pagos activos e inactivos
    function togglePagos() {
        mostrandoEliminados = !mostrandoEliminados;
        const btn = $('#btnToggleEstado');
        btn.html(`<i class="fas ${mostrandoEliminados ? 'fa-eye' : 'fa-trash'} me-2"></i>
                  ${mostrandoEliminados ? 'Mostrar Aprobados' : 'Mostrar Inactivos'}`);
        btn.removeClass('btn-warning btn-info').addClass(mostrandoEliminados ? 'btn-info' : 'btn-warning');
        recargarTabla();
    }

    // Cargar métodos de pago en un select
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

    // Manejar selección de pedido
    $(document).on('click', '.btn-seleccionar-pedido', function() {
        const idPedido = $(this).data('id-pedido');
        const total = $(this).data('total');
        const cliente = $(this).data('cliente');
        const fecha = $(this).data('fecha');
        
        // Cerrar modal de selección primero
        $('#modalSeleccionarPedido').modal('hide').on('hidden.bs.modal', function() {
            // Llenar datos del pedido
            $('#pedido-numero').text('#' + idPedido);
            $('#pedido-cliente').text(cliente);
            $('#pedido-fecha').text(fecha);
            $('#pedido-total').text(parseFloat(total).toFixed(2));
            $('#id_pedido').val(idPedido);
            $('#monto').val(parseFloat(total).toFixed(2));
            
            // Configurar fecha actual
            const today = new Date().toISOString().split('T')[0];
            $('#fecha_pago').val(today);
            
            // Cargar métodos de pago
            cargarMetodosPago($('#cod_metodo'));
            
            // Mostrar modal de pago
            $('#modalNuevoPago').modal('show');
        });
    });

    // Manejar el formulario de pago
    $(document).on('submit', '#formPago', function(e) {
        e.preventDefault();
        
        const form = this;
        const formData = $(form).serialize();
        const submitBtn = $(form).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Validación frontend
        if (!$('#cod_metodo').val()) {
            toastr.error('Por favor seleccione un método de pago válido');
            return;
        }
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Procesando...');
        
        $.ajax({
            url: 'index.php?url=pago&action=guardar',
            type: 'POST',
            data: formData,
            dataType: 'json'
        })
        .done(function(response) {
            if (response.success) {
                toastr.success(response.message);
                $('#modalNuevoPago').modal('hide');
                
                // Limpiar el formulario
                form.reset();
                $(form).removeClass('was-validated');
                
                // Recargar la tabla
                recargarTabla();
            } else {
                toastr.error(response.message || 'Error al procesar el pago');
            }
        })
        .fail(function(xhr) {
            let errorMsg = 'Error al procesar el pago';
            try {
                const response = JSON.parse(xhr.responseText);
                if (response && response.message) {
                    errorMsg = response.message;
                }
            } catch (e) {
                console.error('Error parsing error response:', e);
            }
            toastr.error(errorMsg);
        })
        .always(function() {
            submitBtn.prop('disabled', false).html(originalText);
        });
    });

    // Manejar edición de pago
    $(document).on('click', '.editar', function() {
        const id = $(this).data('id');
        
        $.ajax({
            url: `index.php?url=pago&action=formEditar&id_pago=${id}`,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response && response.success && response.data) {
                    const pago = response.data;
                    
                    // Llenar formulario de edición
                    $('#modalEditar #id_pago').val(pago.id_pago);
                    $('#modalEditar #banco').val(pago.banco);
                    $('#modalEditar #referencia').val(pago.referencia);
                    $('#modalEditar #fecha').val(pago.fecha.split(' ')[0]);
                    $('#modalEditar #monto').val(pago.monto);
                    
                    // Cargar métodos de pago y seleccionar el actual
                    const $selectMetodo = $('#modalEditar #cod_metodo');
                    cargarMetodosPago($selectMetodo, pago.cod_metodo);
                    
                    $('#modalEditar').modal('show');
                } else {
                    toastr.error(response.message || 'Error al cargar datos del pago');
                }
            },
            error: function(xhr) {
                toastr.error('Error al obtener datos del pago');
                console.error('Error:', xhr.responseText);
            }
        });
    });

    // Manejar eliminación de pago
    $(document).on('click', '.eliminar', function() {
        const id = $(this).data('id');
        
        bootbox.confirm({
            title: "Confirmar Eliminación",
            message: `¿Está seguro de eliminar el pago #${id}?`,
            buttons: {
                cancel: {
                    label: '<i class="fa fa-times"></i> Cancelar'
                },
                confirm: {
                    label: '<i class="fa fa-check"></i> Confirmar'
                }
            },
            callback: function(result) {
                if (result) {
                    $.ajax({
                        url: 'index.php?url=pago&action=eliminar',
                        type: 'POST',
                        data: { id_pago: id },
                        dataType: 'json'
                    })
                    .done(function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            recargarTabla();
                        } else {
                            toastr.error(response.message || 'Error al eliminar el pago');
                        }
                    })
                    .fail(function(xhr) {
                        toastr.error('Error al conectar con el servidor');
                        console.error('Error:', xhr.responseText);
                    });
                }
            }
        });
    });

    // Manejar restauración de pago
    $(document).on('click', '.restaurar', function() {
        const id = $(this).data('id');
        
        bootbox.confirm({
            title: "Confirmar Restauración",
            message: `¿Está seguro de restaurar el pago #${id}?`,
            buttons: {
                cancel: {
                    label: '<i class="fa fa-times"></i> Cancelar'
                },
                confirm: {
                    label: '<i class="fa fa-check"></i> Confirmar'
                }
            },
            callback: function(result) {
                if (result) {
                    $.ajax({
                        url: 'index.php?url=pago&action=restaurar',
                        type: 'POST',
                        data: { id_pago: id },
                        dataType: 'json'
                    })
                    .done(function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            recargarTabla();
                        } else {
                            toastr.error(response.message || 'Error al restaurar el pago');
                        }
                    })
                    .fail(function(xhr) {
                        toastr.error('Error al conectar con el servidor');
                        console.error('Error:', xhr.responseText);
                    });
                }
            }
        });
    });

    // Inicialización
    $('#btnToggleEstado').click(togglePagos);
    
    // Inicializar DataTable al cargar la página
    inicializarDataTable();
});