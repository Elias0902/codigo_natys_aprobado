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
        if ($.fn.DataTable.isDataTable('#movimientos')) {
            table.destroy();
            $('#movimientos').empty();
        }

        table = $('#movimientos').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div>'
            },
            processing: true,
            serverSide: false,
            ajax: {
                url: mostrandoEliminados 
                    ? 'index.php?url=movimiento&action=listarEliminados' 
                    : 'index.php?url=movimiento&action=listar',
                type: 'GET',
                dataSrc: 'data',
                error: function(xhr, error, thrown) {
                    console.error('Error en AJAX:', error, thrown);
                    toastr.error('Error al cargar los datos de movimientos');
                }
            },
            columns: [
                { data: 'num_movimiento' },
                { data: 'fecha' },
                { data: 'observaciones' },
                { 
                    data: 'estado',
                    render: function(data) {
                        return `<span class="badge ${data == 1 ? 'bg-success' : 'bg-secondary'}">
                                ${data == 1 ? 'Activo' : 'Inactivo'}
                                </span>`;
                    }
                },
                {
                    data: 'num_movimiento',
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

    function recargarTabla() {
        if (table) {
            table.ajax.url(
                mostrandoEliminados 
                    ? 'index.php?url=movimiento&action=listarEliminados' 
                    : 'index.php?url=movimiento&action=listar'
            ).load();
        } else {
            inicializarDataTable();
        }
    }

    function toggleMovimientos() {
        mostrandoEliminados = !mostrandoEliminados;
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
            form.querySelector('#num_movimiento').value = datos.num_movimiento || '';
            form.querySelector('#fecha').value = datos.fecha || '';
            form.querySelector('#observaciones').value = datos.observaciones || '';
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

    const confirmarAccion = ({ id, url, method, successMessage }) => {
        $.ajax({
            url: url,
            type: method,
            data: { num_movimiento: id },
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
        $(document).on('submit', '#formMovimiento', function(e) {
            e.preventDefault();
            
            const form = this;
            const formData = $(form).serialize();
            const isNew = !form.querySelector('#num_movimiento').value;
            const actionUrl = isNew 
                ? 'index.php?url=movimiento&action=guardar' 
                : 'index.php?url=movimiento&action=actualizar';
            
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
    $('#btnNuevoMovimiento').click(() => {
        cargarFormulario('#modalNuevo', '#contenidoNuevo');
    });

    $('#btnToggleEstado').click(toggleMovimientos);

    $(document).on('click', '.editar', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        
        $.ajax({
            url: `index.php?url=movimiento&action=formEditar&num_movimiento=${id}`,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response && response.success && response.data) {
                    cargarFormulario('#modalEditar', '#contenidoEditar', response.data);
                } else {
                    toastr.error(response.message || 'Datos del movimiento no disponibles');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al obtener movimiento:', error, xhr.responseText);
                toastr.error('Error al cargar datos del movimiento');
            }
        });
    });

    $(document).on('click', '.eliminar', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        bootbox.confirm({
            title: "Confirmar Eliminación",
            message: `¿Está seguro de eliminar el movimiento ${id}?`,
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
                        url: 'index.php?url=movimiento&action=eliminar',
                        method: 'POST',
                        successMessage: 'Movimiento eliminado correctamente'
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
            message: `¿Está seguro de restaurar el movimiento ${id}?`,
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
                        url: 'index.php?url=movimiento&action=restaurar',
                        method: 'POST',
                        successMessage: 'Movimiento restaurado correctamente'
                    });
                }
            }
        });
    });

    $.fn.dataTable.ext.errMode = 'none';
    $('#movimientos').on('error.dt', function(e, settings, techNote, message) {
        console.error('Error en DataTables:', message);
        toastr.error('Error al cargar los datos de la tabla');
        inicializarDataTable();
    });

    inicializarDataTable();
});