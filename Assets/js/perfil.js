$(document).ready(function() {
    let table;

    toastr.options = {
        "closeButton": true,
        "progressBar": false,
        "positionClass": "toast-top-right",
        "timeOut": "5000",
        "escapeHtml": true
    };

    function inicializarDataTable() {
        if ($.fn.DataTable.isDataTable('#perfiles')) {
            table.destroy();
            $('#perfiles').empty();
        }

        table = $('#perfiles').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div>'
            },
            processing: true,
            serverSide: false,
            ajax: {
                url: 'index.php?url=perfil&action=listar',
                type: 'GET',
                dataSrc: 'data',
                error: function(xhr, error, thrown) {
                    console.error('Error en AJAX:', error, thrown);
                    toastr.error('Error al cargar los datos de perfiles');
                }
            },
            columns: [
                { data: 'id' },
                { data: 'nombre_usuario' },
                { data: 'correo_usuario' },
                { data: 'usuario' },
                { 
                    data: 'rol',
                    render: function(data, type, row) {
                        let badgeClass = 'bg-primary'; // Admin por defecto
                        if (data === 'superadmin') badgeClass = 'bg-info';
                        if (data === 'vendedor') badgeClass = 'bg-success';
                        
                        return `<span class="badge rounded-pill ${badgeClass}">${data}</span>`;
                    }
                },
                {
                    data: 'id',
                    render: function(data, type, row) {
                        return `
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-primary actualizar" data-id="${data}">
                                    <i class="fas fa-sync-alt"></i> Editar
                                </button>
                                <button class="btn btn-sm btn-danger eliminar" data-id="${data}">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
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
                console.log('DataTable de perfiles inicializado correctamente');
            }
        });
    }

    function recargarTabla() {
        if (table) {
            table.ajax.reload(function(json) {
                console.log('Datos de perfiles cargados:', json);
            }, false);
        } else {
            inicializarDataTable();
        }
    }

    const cargarFormulario = (modalId, contenidoId, datos = null) => {
        const template = document.getElementById('templateFormularioPerfil');
        const clone = template.content.cloneNode(true);
        const form = clone.querySelector('form');
        
        if (datos) {
            console.log('Datos recibidos para formulario:', datos);
            form.querySelector('#id').value = datos.id || '';
            form.querySelector('#nombre_usuario').value = datos.nombre_usuario || '';
            form.querySelector('#correo_usuario').value = datos.correo_usuario || '';
            form.querySelector('#usuario').value = datos.usuario || '';
            form.querySelector('#rol').value = datos.rol || '';
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

    const manejarFormulario = () => {
        $(document).on('submit', '#formPerfil', function(e) {
            e.preventDefault();
            
            const form = this;
            const formData = $(form).serialize();
            
            $.ajax({
                url: 'index.php?url=perfil&action=actualizar',
                type: 'POST',
                data: formData,
                dataType: 'json'
            })
            .done(response => {
                if(response.success) {
                    $('#modalEditar').modal('hide');
                    toastr.success(response.message);
                    recargarTabla();
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

    // Manejar clic en botón registrar
    $(document).on('click', '#btnRegistrarUsuario', function() {
        $('#modalRegistrar').modal('show');
    });

    // Manejar envío del formulario de registro
    $(document).on('submit', '#formRegistrar', function(e) {
        e.preventDefault();
        
        const form = this;
        if (!form.checkValidity()) {
            e.stopPropagation();
            form.classList.add('was-validated');
            return;
        }
        
        $.ajax({
            url: 'index.php?url=perfil&action=registrar',
            type: 'POST',
            data: $(form).serialize(),
            dataType: 'json'
        })
        .done(response => {
            if(response.success) {
                $('#modalRegistrar').modal('hide');
                form.reset();
                form.classList.remove('was-validated');
                toastr.success(response.message);
                recargarTabla();
            } else {
                toastr.error(response.message);
            }
        })
        .fail((xhr, status, error) => {
            console.error('Error en la petición:', error, xhr.responseText);
            toastr.error(`Error en la solicitud: ${error}`);
        });
    });

    // Manejar clic en botón eliminar
    $(document).on('click', '.eliminar', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        
        if (confirm('¿Está seguro que desea eliminar este usuario?')) {
            $.ajax({
                url: `index.php?url=perfil&action=eliminar&id=${id}`,
                type: 'GET',
                dataType: 'json'
            })
            .done(response => {
                if(response.success) {
                    toastr.success(response.message);
                    recargarTabla();
                } else {
                    toastr.error(response.message);
                }
            })
            .fail((xhr, status, error) => {
                console.error('Error en la petición:', error, xhr.responseText);
                toastr.error(`Error en la solicitud: ${error}`);
            });
        }
    });

    manejarFormulario();

    $(document).on('click', '.editar', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        console.log('Editando perfil con ID:', id);
        
        $.ajax({
            url: `index.php?url=perfil&action=formEditar&id=${id}`,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log('Respuesta del servidor:', response);
                
                if (response && response.success && response.data) {
                    cargarFormulario('#modalEditar', '#contenidoEditar', response.data);
                } else {
                    toastr.error(response.message || 'Datos del perfil no disponibles');
                    console.error('Respuesta inesperada:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al obtener perfil:', error, xhr.responseText);
                try {
                    const errorResponse = JSON.parse(xhr.responseText);
                    toastr.error(errorResponse.message || 'Error al cargar datos del perfil');
                } catch (e) {
                    toastr.error('Error al cargar datos del perfil');
                }
            }
        });
    });

    $.fn.dataTable.ext.errMode = 'none';
    $('#perfiles').on('error.dt', function(e, settings, techNote, message) {
        console.error('Error en DataTables:', message);
        toastr.error('Error al cargar los datos de la tabla');
        inicializarDataTable();
    });

    inicializarDataTable();
});