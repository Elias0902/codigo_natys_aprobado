$(document).ready(function() {
    let table;

    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "5000",
        "escapeHtml": true
    };

    function inicializarDataTable() {
        table = $('#notificaciones').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div>'
            },
            processing: true,
            serverSide: false,
            ajax: {
                url: 'index.php?url=notificacion&action=listar',
                type: 'GET',
                dataSrc: 'data',
                error: function(xhr, error, thrown) {
                    console.error('Error en AJAX:', error, thrown);
                    toastr.error('Error al cargar las notificaciones');
                }
            },
            columns: [
                { 
                    data: 'mensaje',
                    render: function(data, type, row) {
                        const enlace = row.enlace ? `<a href="${row.enlace}" class="text-decoration-none">${data}</a>` : data;
                        return `<div class="${!row.vista ? 'fw-bold' : ''}">${enlace}</div>`;
                    }
                },
                { 
                    data: 'tipo',
                    render: function(data) {
                        const tipos = {
                            'info': '<span class="badge bg-info">Información</span>',
                            'alerta': '<span class="badge bg-warning text-dark">Alerta</span>',
                            'urgente': '<span class="badge bg-danger">Urgente</span>'
                        };
                        return tipos[data] || data;
                    }
                },
                { 
                    data: 'fecha_creacion',
                    render: function(data) {
                        return new Date(data).toLocaleString();
                    }
                },
                { 
                    data: 'vista',
                    render: function(data) {
                        return data ? 
                            '<span class="badge bg-secondary">Leída</span>' : 
                            '<span class="badge bg-success">Nueva</span>';
                    }
                },
                {
                    data: 'id_notificacion',
                    render: function(data, type, row) {
                        return `
                            <div class="btn-group" role="group">
                                ${!row.vista ? `
                                <button class="btn btn-sm btn-success marcar-leida" data-id="${data}" title="Marcar como leída">
                                    <i class="fas fa-check"></i>
                                </button>
                                ` : ''}
                                <button class="btn btn-sm btn-danger eliminar" data-id="${data}" title="Eliminar">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        `;
                    },
                    orderable: false,
                    className: 'text-center'
                }
            ],
            order: [[2, 'desc']],
            responsive: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Todos']],
            createdRow: function(row, data, dataIndex) {
                if (!data.vista) {
                    $(row).addClass('notificacion-no-leida');
                }
                if (data.tipo === 'urgente') {
                    $(row).addClass('notificacion-urgente');
                } else if (data.tipo === 'alerta') {
                    $(row).addClass('notificacion-alerta');
                }
            }
        });
    }

    function actualizarContador() {
        $.get('index.php?url=notificacion&action=contarNoLeidas', function(response) {
            if (response && response.success && response.count) {
                const count = response.count.unread || 0;
                const $badge = $('#badgeNoti');
                if ($badge.length) {
                    if (count > 0) {
                        $badge.text(count);
                        $badge.show();
                    } else {
                        $badge.text('0');
                        $badge.hide();
                    }
                }
                
                // Actualizar badge del botón "Marcar todas como leídas" si existe
                const $badgeMarcarTodas = $('#badge-total-no-leidas');
                if ($badgeMarcarTodas.length) {
                    $badgeMarcarTodas.text(count);
                }
            }
        }).fail(function(xhr, status, error) {
            console.error('Error al actualizar contador:', error);
        });
    }

    function marcarComoLeida(id) {
        console.log('Iniciando marcarComoLeida para ID:', id);
        
        // Mostrar indicador de carga
        const $btn = $(`.marcar-leida[data-id="${id}"]`);
        const originalHtml = $btn.html();
        $btn.html('<i class="fas fa-spinner fa-spin"></i>');
        $btn.prop('disabled', true);

        console.log('Enviando solicitud a servidor...');
        
        // Verificar si la tabla está inicializada
        if (!table) {
            console.error('Error: La tabla de notificaciones no está inicializada');
            toastr.error('Error: No se pudo cargar la tabla de notificaciones');
            $btn.html(originalHtml);
            $btn.prop('disabled', false);
            return;
        }
        
        // Crear un objeto FormData para asegurar el envío correcto de los datos
        const formData = new FormData();
        formData.append('id_notificacion', id);
        
        // Agregar token CSRF si está disponible
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        if (csrfToken) {
            formData.append('csrf_token', csrfToken);
        }
        
        // Configuración de la petición AJAX
        $.ajax({
            url: 'index.php?url=notificacion&action=marcarLeida',
            type: 'POST',
            dataType: 'json',
            data: formData,
            processData: false,  // Importante para FormData
            contentType: false,  // Importante para FormData
            cache: false,
            success: function(response) {
                console.log('Respuesta del servidor (marcarLeida):', response);
                
                if (response && response.success) {
                    console.log('Actualizando interfaz para notificación ID:', id);
                    
                    // Actualizar la fila específica
                    const row = table.row(`[data-id="${id}"]`).data();
                    if (row) {
                        row.vista = true;
                        table.row(`[data-id="${id}"]`).data(row).invalidate();
                        console.log('Fila actualizada en DataTables');
                    } else {
                        console.log('No se encontró la fila en DataTables, recargando tabla...');
                        table.ajax.reload(null, false);
                    }
                    
                    // Actualizar contadores
                    actualizarContador();
                    
                    // Mostrar mensaje de éxito
                    const message = response.message || 'Notificación marcada como leída';
                    console.log('Éxito:', message);
                    toastr.success(message);
                } else {
                    const errorMsg = response && response.message ? response.message : 'Error desconocido del servidor';
                    console.error('Error en la respuesta del servidor:', errorMsg);
                    toastr.error('Error: ' + errorMsg);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la petición AJAX (marcarLeida):', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText,
                    statusCode: xhr.status,
                    responseJSON: xhr.responseJSON
                });
                
                let errorMsg = 'Error al marcar como leída';
                try {
                    if (xhr.responseText) {
                        const errorResponse = JSON.parse(xhr.responseText);
                        errorMsg += ': ' + (errorResponse.message || xhr.statusText || 'Error desconocido');
                    } else {
                        errorMsg += ': ' + (xhr.statusText || 'Error de conexión');
                    }
                } catch (e) {
                    errorMsg += ': ' + (xhr.statusText || 'Error de conexión');
                }
                
                toastr.error(errorMsg);
            },
            complete: function() {
                console.log('Petición completada');
                // Restaurar el botón
                $btn.html(originalHtml);
                $btn.prop('disabled', false);
            }
        });
    }

    function eliminarNotificacion(id) {
        // Usar el modal personalizado en lugar de bootbox
        if (typeof showCustomModal === 'function') {
            showCustomModal("Confirmar Eliminación", "¿Está seguro de eliminar esta notificación?", function(result) {
                if (result) {
                    realizarEliminacion(id);
                }
            });
        } else {
            // Fallback si no existe el modal personalizado
            if (confirm("¿Está seguro de eliminar esta notificación?")) {
                realizarEliminacion(id);
            }
        }
    }

    function realizarEliminacion(id) {
        $.post('index.php?url=notificacion&action=eliminar', { id_notificacion: id })
            .done(function(response) {
                if (response.success) {
                    table.ajax.reload(null, false);
                    actualizarContador();
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            })
            .fail(function(xhr, status, error) {
                console.error('Error al eliminar:', error, xhr.responseText);
                toastr.error('Error al eliminar: ' + error);
            });
    }

    function marcarTodasComoLeidas() {
        // Usar el modal personalizado en lugar de bootbox
        if (typeof showCustomModal === 'function') {
            showCustomModal("Confirmar Acción", "¿Marcar todas las notificaciones como leídas?", function(result) {
                if (result) {
                    realizarMarcarTodasLeidas();
                }
            });
        } else {
            // Fallback si no existe el modal personalizado
            if (confirm("¿Marcar todas las notificaciones como leídas?")) {
                realizarMarcarTodasLeidas();
            }
        }
    }

    function realizarMarcarTodasLeidas() {
        console.log('Iniciando realizarMarcarTodasLeidas');
        
        // Mostrar indicador de carga en el botón
        const $btn = $('#btnMarcarTodasLeidas, .marcar-todas-btn');
        const originalHtml = $btn.html();
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Procesando...');
        $btn.prop('disabled', true);

        console.log('Enviando solicitud para marcar todas como leídas...');
        
        // Verificar si la tabla está inicializada
        if (!table) {
            console.error('Error: La tabla de notificaciones no está inicializada');
            toastr.error('Error: No se pudo cargar la tabla de notificaciones');
            $btn.html(originalHtml);
            $btn.prop('disabled', false);
            return;
        }
        
        // Crear un objeto FormData para asegurar el envío correcto de los datos
        const formData = new FormData();
        
        // Agregar token CSRF si está disponible
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        if (csrfToken) {
            formData.append('csrf_token', csrfToken);
        }
        
        // Configuración de la petición AJAX
        $.ajax({
            url: 'index.php?url=notificacion&action=marcarTodasLeidas',
            type: 'POST',
            dataType: 'json',
            data: formData,
            processData: false,  // Importante para FormData
            contentType: false,  // Importante para FormData
            cache: false,
            success: function(response) {
                console.log('Respuesta del servidor (marcarTodasLeidas):', response);
                
                if (response && response.success) {
                    console.log('Actualizando interfaz para todas las notificaciones');
                    
                    // Recargar la tabla para asegurar que todo esté actualizado
                    table.ajax.reload(null, false);
                    
                    // Actualizar contadores
                    actualizarContador();
                    
                    // Actualizar badge del contador
                    $('.badge-notification, .badge.bg-danger').text('0');
                    
                    // Mostrar mensaje de éxito
                    const message = response.message || 'Todas las notificaciones han sido marcadas como leídas';
                    console.log('Éxito:', message);
                    toastr.success(message);
                } else {
                    const errorMsg = response && response.message ? response.message : 'Error desconocido del servidor';
                    console.error('Error en la respuesta del servidor:', errorMsg);
                    toastr.error('Error: ' + errorMsg);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la petición AJAX (marcarTodasLeidas):', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText,
                    statusCode: xhr.status,
                    responseJSON: xhr.responseJSON
                });
                
                let errorMsg = 'Error al marcar todas como leídas';
                try {
                    if (xhr.responseText) {
                        const errorResponse = JSON.parse(xhr.responseText);
                        errorMsg += ': ' + (errorResponse.message || xhr.statusText || 'Error desconocido');
                    } else {
                        errorMsg += ': ' + (xhr.statusText || 'Error de conexión');
                    }
                } catch (e) {
                    errorMsg += ': ' + (xhr.statusText || 'Error de conexión');
                }
                
                toastr.error(errorMsg);
            },
            complete: function() {
                console.log('Petición de marcar todas completada');
                // Restaurar el botón
                $btn.html(originalHtml);
                $btn.prop('disabled', false);
            }
        });
    }

    function eliminarLeidas() {
        // Usar el modal personalizado en lugar de bootbox
        if (typeof showCustomModal === 'function') {
            showCustomModal("Confirmar Eliminación", "¿Eliminar todas las notificaciones leídas?", function(result) {
                if (result) {
                    realizarEliminarLeidas();
                }
            });
        } else {
            // Fallback si no existe el modal personalizado
            if (confirm("¿Eliminar todas las notificaciones leídas?")) {
                realizarEliminarLeidas();
            }
        }
    }

    function realizarEliminarLeidas() {
        $.post('index.php?url=notificacion&action=eliminarLeidas')
            .done(function(response) {
                if (response.success) {
                    table.ajax.reload();
                    actualizarContador();
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            })
            .fail(function(xhr, status, error) {
                console.error('Error al eliminar leídas:', error, xhr.responseText);
                toastr.error('Error: ' + error);
            });
    }

    $(document).on('click', '.marcar-leida', function() {
        const id = $(this).data('id');
        marcarComoLeida(id);
    });

    $(document).on('click', '.eliminar', function() {
        const id = $(this).data('id');
        eliminarNotificacion(id);
    });

    $('#btnMarcarTodasLeidas').click(function() {
        marcarTodasComoLeidas();
    });

    $('#btnEliminarLeidas').click(function() {
        eliminarLeidas();
    });

    // Actualizar contador cada 30 segundos
    setInterval(actualizarContador, 30000);

    inicializarDataTable();
    actualizarContador();
});