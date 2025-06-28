$(document).ready(function() {
    let table;
    let mostrandoEliminados = false;

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
            const isNew = !form.querySelector('#id_pago').value;
            const actionUrl = isNew 
                ? 'index.php?url=pago&action=guardar' 
                : 'index.php?url=pago&action=actualizar';
            
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
            .fail((xhr, status, error) => {
                console.error('Error en la petición:', error, xhr.responseText);
                toastr.error(`Error en la solicitud: ${error}`);
            });
        });
    };

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