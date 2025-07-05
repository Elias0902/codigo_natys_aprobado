$(document).ready(function() {
    let table;
    let mostrandoEliminados = false;

    // Configuración de Toastr
    toastr.options = {
        "closeButton": true,
        "progressBar": false,
        "positionClass": "toast-top-right",
        "timeOut": "5000",
        "escapeHtml": true
    };

function inicializarDataTable() {
    if ($.fn.DataTable.isDataTable('#productos')) {
        table.destroy();
    }

    table = $('#productos').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        processing: true,
        serverSide: false,
        ajax: {
            url: mostrandoEliminados 
                ? 'index.php?url=producto&action=listarEliminados' 
                : 'index.php?url=producto&action=listar',
            type: 'GET',
            dataSrc: 'data'
        },
        columns: [
            { data: 'cod_producto' },
            { data: 'nombre' },
            { data: 'precio' },
            { data: 'unidad' },
            { 
                data: 'estado',
                render: function(data) {
                    return `<span class="badge ${data == 1 ? 'bg-success' : 'bg-secondary'}">
                            ${data == 1 ? 'Activo' : 'Inactivo'}
                          </span>`;
                }
            },
            {
                data: 'cod_producto',
                render: function(data, type, row) {
                    return `
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-primary editar" data-codigo="${data}">
                                <i class="fas fa-edit"></i>
                            </button>
                            ${row.estado == 1 ? 
                                `<button class="btn btn-sm btn-danger eliminar" data-codigo="${data}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>` : 
                                `<button class="btn btn-sm btn-warning restaurar" data-codigo="${data}">
                                    <i class="fas fa-undo"></i>
                                </button>`}
                        </div>
                    `;
                },
                orderable: false
            }
        ],
        responsive: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Todos']]
    });
}
    // Recargar la tabla
    function recargarTabla() {
        $.ajax({
            url: mostrandoEliminados 
                ? 'index.php?url=producto&action=listarEliminados' 
                : 'index.php?url=producto&action=listar',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response && response.data) {
                    table.clear().rows.add(response.data).draw();
                }
            },
            error: function(xhr, error, thrown) {
                console.error('Error al cargar productos:', error, thrown);
                toastr.error('Error al cargar los productos');
            }
        });
    }

    // Alternar entre productos activos e eliminados
    function toggleProductos() {
        mostrandoEliminados = !mostrandoEliminados;
        const btn = $('#btnToggleEstado');
        btn.html(`<i class="fas ${mostrandoEliminados ? 'fa-box-open' : 'fa-trash-restore'} me-2"></i>
                  ${mostrandoEliminados ? 'Mostrar Activos' : 'Mostrar Eliminados'}`);
        btn.removeClass('btn-warning btn-info').addClass(mostrandoEliminados ? 'btn-info' : 'btn-warning');
        recargarTabla();
    }

    // Mostrar formulario para nuevo producto
    function mostrarFormularioNuevo() {
        const template = document.getElementById('templateFormulario');
        const clone = template.content.cloneNode(true);
        const form = clone.querySelector('form');
        
        // Configurar validación
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            } else {
                e.preventDefault();
                guardarProducto(form);
            }
            form.classList.add('was-validated');
        }, false);
        
        $('#contenidoNuevo').empty().append(clone);
        $('#modalNuevo').modal('show');
    }

    // Mostrar formulario para editar producto
    function mostrarFormularioEditar(codigo) {
        $.ajax({
            url: `index.php?url=producto&action=formEditar&cod_producto=${codigo}`,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response && response.success && response.data) {
                    const template = document.getElementById('templateFormulario');
                    const clone = template.content.cloneNode(true);
                    const form = clone.querySelector('form');
                    
                    // Llenar datos del producto
                    form.querySelector('#original_codigo').value = response.data.cod_producto;
                    form.querySelector('#cod_producto').value = response.data.cod_producto;
                    form.querySelector('#nombre').value = response.data.nombre;
                    form.querySelector('#precio').value = response.data.precio;
                    form.querySelector('#unidad').value = response.data.unidad;
                    
                    // Configurar validación
                    form.addEventListener('submit', function(e) {
                        if (!form.checkValidity()) {
                            e.preventDefault();
                            e.stopPropagation();
                        } else {
                            e.preventDefault();
                            actualizarProducto(form);
                        }
                        form.classList.add('was-validated');
                    }, false);
                    
                    $('#contenidoEditar').empty().append(clone);
                    $('#modalEditar').modal('show');
                } else {
                    toastr.error(response.message || 'Error al cargar datos del producto');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al obtener producto:', error, xhr.responseText);
                toastr.error('Error al cargar datos del producto');
            }
        });
    }

    // Guardar nuevo producto
    function guardarProducto(form) {
        const formData = $(form).serialize();
        
        $.ajax({
            url: 'index.php?url=producto&action=guardar',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#modalNuevo').modal('hide');
                    toastr.success(response.message);
                    recargarTabla();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al guardar producto:', error, xhr.responseText);
                toastr.error('Error al guardar el producto');
            }
        });
    }

    // Actualizar producto existente
    function actualizarProducto(form) {
        const formData = $(form).serialize();
        
        $.ajax({
            url: 'index.php?url=producto&action=actualizar',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#modalEditar').modal('hide');
                    toastr.success(response.message);
                    recargarTabla();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al actualizar producto:', error, xhr.responseText);
                toastr.error('Error al actualizar el producto');
            }
        });
    }

    // Confirmar eliminación de producto
    function confirmarEliminar(codigo) {
        bootbox.confirm({
            title: "Confirmar Eliminación",
            message: `¿Está seguro de eliminar el producto ${codigo}?`,
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
                        url: 'index.php?url=producto&action=eliminar',
                        type: 'POST',
                        data: { codigo: codigo },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.message);
                                recargarTabla();
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error al eliminar producto:', error, xhr.responseText);
                            toastr.error('Error al eliminar el producto');
                        }
                    });
                }
            }
        });
    }

    // Confirmar restauración de producto
    function confirmarRestaurar(codigo) {
        bootbox.confirm({
            title: "Confirmar Restauración",
            message: `¿Está seguro de restaurar el producto ${codigo}?`,
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
                        url: 'index.php?url=producto&action=restaurar',
                        type: 'POST',
                        data: { codigo: codigo },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.message);
                                recargarTabla();
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error al restaurar producto:', error, xhr.responseText);
                            toastr.error('Error al restaurar el producto');
                        }
                    });
                }
            }
        });
    }

    // Eventos
    $(document).on('click', '#btnNuevoProducto', mostrarFormularioNuevo);
    $(document).on('click', '#btnToggleEstado', toggleProductos);
    
    $(document).on('click', '.editar', function(e) {
        e.preventDefault();
        const codigo = $(this).data('codigo');
        mostrarFormularioEditar(codigo);
    });
    
    $(document).on('click', '.eliminar', function(e) {
        e.preventDefault();
        const codigo = $(this).data('codigo');
        confirmarEliminar(codigo);
    });
    
    $(document).on('click', '.restaurar', function(e) {
        e.preventDefault();
        const codigo = $(this).data('codigo');
        confirmarRestaurar(codigo);
    });

    // Inicialización
    inicializarDataTable();
});