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
        if ($.fn.DataTable.isDataTable('#clientes')) {
            table.destroy();
            $('#clientes').empty();
        }

        table = $('#clientes').DataTable({
            language: {
    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
    processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div>'
    },
            processing: true,
            serverSide: false,
            ajax: {
                url: mostrandoEliminados 
                    ? 'index.php?url=cliente&action=listarEliminados' 
                    : 'index.php?url=cliente&action=listar',
                type: 'GET',
                dataSrc: 'data',
                error: function(xhr, error, thrown) {
                    console.error('Error en AJAX:', error, thrown);
                    toastr.error('Error al cargar los datos de clientes');
                }
            },
            columns: [
                { data: 'ced_cliente' },
                { data: 'nomcliente' },
                { data: 'correo' },
                { data: 'telefono' },
                { data: 'direccion' },
                { 
                    data: 'estado',
                    render: function(data) {
                        return `<span class="badge ${data == 1 ? 'bg-success' : 'bg-secondary'}">
                                ${data == 1 ? 'Activo' : 'Inactivo'}
                                </span>`;
                    }
                },
                {
                    data: 'ced_cliente',
                    render: function(data, type, row) {
                        return `
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-primary editar" data-cedula="${data}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                ${row.estado == 1 ? 
                                    `<button class="btn btn-sm btn-danger eliminar" data-cedula="${data}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>` : 
                                    `<button class="btn btn-sm btn-warning restaurar" data-cedula="${data}">
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
                console.log('DataTable inicializado correctamente');
            },
            drawCallback: function() {
                console.log('Tabla redibujada. Mostrando:', mostrandoEliminados ? 'Eliminados' : 'Activos');
            }
        });
    }

    function recargarTabla() {
        if (table) {
            table.ajax.url(
                mostrandoEliminados 
                    ? 'index.php?url=cliente&action=listarEliminados' 
                    : 'index.php?url=cliente&action=listar'
            ).load(function(json) {
                console.log('Datos cargados:', json);
            }, false);
        } else {
            inicializarDataTable();
        }
    }

    function toggleClientes() {
        mostrandoEliminados = !mostrandoEliminados;
        console.log('Alternando a:', mostrandoEliminados ? 'Eliminados' : 'Activos');
        const btn = $('#btnToggleEstado');
        btn.html(`<i class="fas ${mostrandoEliminados ? 'fa-user-check' : 'fa-trash-restore'} me-2"></i>
                  ${mostrandoEliminados ? 'Mostrar Activos' : 'Mostrar Eliminados'}`);
        btn.removeClass('btn-warning btn-info').addClass(mostrandoEliminados ? 'btn-info' : 'btn-warning');
        recargarTabla();
    }

    const cargarFormulario = (modalId, contenidoId, datos = null) => {
    const template = document.getElementById('templateFormulario');
    const clone = template.content.cloneNode(true);
    const form = clone.querySelector('form');
    if (datos) {
        console.log('Datos recibidos para formulario:', datos);
        form.querySelector('#original_cedula').value = datos.ced_cliente || '';
        form.querySelector('#ced_cliente').value = datos.ced_cliente || '';
        form.querySelector('#nomcliente').value = datos.nomcliente || '';
        form.querySelector('#correo').value = datos.correo || '';
        form.querySelector('#telefono').value = datos.telefono || '';
        form.querySelector('#direccion').value = datos.direccion || '';
        form.querySelector('#ced_cliente').readOnly = true;
    } else {
        form.querySelector('#ced_cliente').readOnly = false;
    }
    
    $(contenidoId).empty().append(clone);
    $(modalId).modal('show');
    
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    }, false);
};

    const confirmarAccion = ({ cedula, url, method, successMessage }) => {
        $.ajax({
            url: url,
            type: method,
            data: { cedula },
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
        $(document).on('submit', '#formCliente', function(e) {
            e.preventDefault();
            
            const form = this;
            const formData = $(form).serialize();
            const isNew = !form.querySelector('#original_cedula').value;
            const actionUrl = isNew 
                ? 'index.php?url=cliente&action=guardar' 
                : 'index.php?url=cliente&action=actualizar';
            
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
    $('#btnNuevoCliente').click(() => {
        cargarFormulario('#modalNuevo', '#contenidoNuevo');
    });

    $('#btnToggleEstado').click(toggleClientes);
   $(document).on('click', '.editar', function(e) {
    e.preventDefault();
    const cedula = $(this).data('cedula');
    console.log('Editando cliente con cédula:', cedula);
    
    $.ajax({
        url: `index.php?url=cliente&action=formEditar&ced_cliente=${cedula}`,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('Respuesta del servidor:', response); // Para depuración
            
            if (response && response.success && response.data) {
                cargarFormulario('#modalEditar', '#contenidoEditar', response.data);
            } else {
                toastr.error(response.message || 'Datos del cliente no disponibles');
                console.error('Respuesta inesperada:', response);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al obtener cliente:', error, xhr.responseText);
            try {
                const errorResponse = JSON.parse(xhr.responseText);
                toastr.error(errorResponse.message || 'Error al cargar datos del cliente');
            } catch (e) {
                toastr.error('Error al cargar datos del cliente');
            }
        }
    });
});

    $(document).on('click', '.eliminar', function(e) {
        e.preventDefault();
        const cedula = $(this).data('cedula');
        bootbox.confirm({
            title: "Confirmar Eliminación",
            message: `¿Está seguro de eliminar al cliente con cédula ${cedula}?`,
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
                        cedula: cedula,
                        url: 'index.php?url=cliente&action=eliminar',
                        method: 'POST',
                        successMessage: 'Cliente eliminado correctamente'
                    });
                }
            }
        });
    });

    $(document).on('click', '.restaurar', function(e) {
        e.preventDefault();
        const cedula = $(this).data('cedula');
        bootbox.confirm({
            title: "Confirmar Restauración",
            message: `¿Está seguro de restaurar al cliente con cédula ${cedula}?`,
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
                        cedula: cedula,
                        url: 'index.php?url=cliente&action=restaurar',
                        method: 'POST',
                        successMessage: 'Cliente restaurado correctamente'
                    });
                }
            }
        });
    });

    $.fn.dataTable.ext.errMode = 'none';
    $('#clientes').on('error.dt', function(e, settings, techNote, message) {
        console.error('Error en DataTables:', message);
        toastr.error('Error al cargar los datos de la tabla');
        inicializarDataTable();
    });

    inicializarDataTable();
});