// cliente.js - Script separado para gestión de clientes
$(document).ready(function() {
    let table;
    let mostrandoEliminados = false;

    // Configuración de toastr
    toastr.options = {
        "closeButton": true,
        "progressBar": false,
        "positionClass": "toast-top-right",
        "timeOut": "5000",
        "escapeHtml": false
    };

    // Función segura para mostrar alertas
    function mostrarAlerta(titulo, mensaje, tipo = 'error') {
        // Crear el contenedor de la tostada
        const toast = document.createElement('div');
        toast.className = `toast ${tipo === 'error' ? 'toast-error' : 'toast-success'}`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        // Contenedor del mensaje
        const toastMessage = document.createElement('div');
        toastMessage.className = 'toast-message';
        toastMessage.style.cssText = `
            color: white;
            font-weight: 500;
            padding: 15px;
            display: flex;
            align-items: center;
        `;
        
        // Agregar el ícono de error si es necesario
        if (tipo === 'error') {
            const icon = document.createElement('i');
            icon.className = 'fa fa-exclamation-triangle';
            icon.style.cssText = `
                margin-right: 10px;
                font-size: 20px;
                color: white;
            `;
            toastMessage.appendChild(icon);
        }
        
        // Agregar el texto del mensaje
        const messageText = document.createElement('span');
        messageText.textContent = mensaje;
        toastMessage.appendChild(messageText);
        
        // Botón de cierre con ícono de X
        const closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.className = 'btn-close';
        closeButton.setAttribute('aria-label', 'Cerrar');
        closeButton.style.cssText = `
            position: relative;
            width: 16px;
            height: 16px;
            margin-left: 1rem;
            border: none;
            background: transparent;
            cursor: pointer;
            padding: 0;
            opacity: 0.75;
            transition: opacity 0.15s ease-in-out;
        `;
        
        // Crear el ícono X usando estilos CSS
        closeButton.innerHTML = `
            <span style="
                position: absolute;
                top: 50%;
                left: 50%;
                width: 100%;
                height: 2px;
                background-color: ${tipo === 'error' ? '#fff' : '#fff'};
                transform: translate(-50%, -50%) rotate(45deg);
                border-radius: 1px;
            "></span>
            <span style="
                position: absolute;
                top: 50%;
                left: 50%;
                width: 100%;
                height: 2px;
                background-color: ${tipo === 'error' ? '#fff' : '#fff'};
                transform: translate(-50%, -50%) rotate(-45deg);
                border-radius: 1px;
            "></span>
        `;
        
        // Efecto hover
        closeButton.onmouseover = () => closeButton.style.opacity = '1';
        closeButton.onmouseout = () => closeButton.style.opacity = '0.75';
        
        // Evento para cerrar la tostada
        closeButton.addEventListener('click', function() {
            toast.style.opacity = '0';
            setTimeout(() => {
                if (toast.parentNode) {
                    document.body.removeChild(toast);
                }
            }, 300);
        });
        
        // Añadir el mensaje a la tostada
        toast.appendChild(toastMessage);
        
        // Estilos para la tostada
        Object.assign(toast.style, {
            'position': 'fixed',
            'top': '20px',
            'right': '20px',
            'opacity': '0',
            'transition': 'opacity 0.3s ease-in-out',
            'display': 'block',
            'z-index': '1090',
            'min-width': '250px',
            'max-width': '350px',
            'background-color': tipo === 'error' ? '#d31111' : '#198754',
            'border-left': tipo === 'error' ? '5px solid #b02a37' : '5px solid #146c43',
            'color': 'white',
            'opacity': '1',
            'border-radius': '4px',
            'box-shadow': '0 0.5rem 1rem rgba(0,0,0,0.15)'
        });
        
        // Añadir la tostada al body
        document.body.appendChild(toast);
        
        // Forzar el reflow para la animación
        void toast.offsetHeight;
        
        // Mostrar la tostada con animación
        toast.style.opacity = '1';
        
        // Ocultar y eliminar la tostada después de 5 segundos
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 5000);
    }

    // Inicialización específica para el dropdown de reportes de clientes
    function inicializarDropdownReportesClientes() {
        const reportesDropdown = document.getElementById('reportesDropdownClientes');
        if (reportesDropdown) {
            const dropdown = new bootstrap.Dropdown(reportesDropdown);
            
            reportesDropdown.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdown.toggle();
            });
            
            const dropdownMenu = reportesDropdown.parentNode.querySelector('.dropdown-menu');
            if (dropdownMenu) {
                dropdownMenu.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
        }
    }

    // Función para inicializar DataTable
    function inicializarDataTable() {
        if ($.fn.DataTable.isDataTable('#clientes')) {
            table.destroy();
        }

        // Obtener el estado actual de la URL
        const urlParams = new URLSearchParams(window.location.search);
        mostrandoEliminados = urlParams.get('mostrarEliminados') === 'true';

        // Actualizar el botón según el estado actual
        actualizarBotonToggle();

        table = $('#clientes').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            order: [[0, 'asc']],
            responsive: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Todos']],
            dom: '<"top"lf>rt<"bottom"ip><"clear">',
            processing: true,
            serverSide: false,
            ajax: {
                url: 'index.php?url=cliente&action=listar',
                type: 'GET',
                data: function(d) {
                    d.filtro = mostrandoEliminados ? 'inactivos' : 'activos';
                },
                dataSrc: function(response) {
                    if (response.success) {
                        return response.data;
                    } else {
                        console.error('Error en la respuesta:', response);
                        return [];
                    }
                }
            },
            columns: [
                { data: 'ced_cliente' },
                { data: 'nomcliente' },
                { 
                    data: 'correo',
                    render: function(data, type, row) {
                        return data || '';
                    }
                },
                { 
                    data: 'telefono',
                    render: function(data, type, row) {
                        return data || '';
                    }
                },
                { data: 'direccion' },
                { 
                    data: 'estado',
                    render: function(data, type, row) {
                        return '<span class="badge bg-' + (data == 1 ? 'success' : 'secondary') + '">' + 
                               (data == 1 ? 'Activo' : 'Inactivo') + '</span>';
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        let buttons = `<div class="btn-group" role="group">
                            <button class="btn btn-sm btn-info detalles" data-cedula="${row.ced_cliente}" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </button>`;

                        if (row.estado == 1) {
                            buttons += `<button class="btn btn-sm btn-primary actualizar" data-cedula="${row.ced_cliente}" title="Actualizar cliente">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <button class="btn btn-sm btn-danger eliminar" data-cedula="${row.ced_cliente}" title="Eliminar cliente">
                                <i class="fas fa-trash-alt"></i>
                            </button></div>`;
                        } else {
                            buttons += `<button class="btn btn-sm btn-warning restaurar" data-cedula="${row.ced_cliente}" title="Restaurar cliente">
                                <i class="fas fa-undo"></i>
                            </button></div>`;
                        }

                        return buttons;
                    },
                    orderable: false,
                    searchable: false
                }
            ],
            drawCallback: function(settings) {
                // Actualizar también la vista móvil
                // Obtener los datos actuales de la tabla (con filtros aplicados)
                const currentData = table.rows({ search: 'applied' }).data().toArray();
                actualizarVistaMovil(currentData);
            },
            error: function(xhr, error, thrown) {
                console.error('Error loading data:', xhr, error, thrown);
                toastr.error('Error al cargar los datos de la tabla');
            }
        });
    }

    // Función para actualizar la vista móvil
    function actualizarVistaMovil(clientes) {
        // Usar un contenedor específico para la lista de clientes en móvil
        // que no afecte al menú de navegación
        const $sidebar = $('.sidebar');
        let $mobileView = $('.mobile-clientes-container');
        
        // Si no existe el contenedor, lo creamos
        if ($mobileView.length === 0) {
            // Asegurarse de no incluir el menú de hamburguesa
            $('main.container-fluid').append('<div class="mobile-clientes-container d-md-none"></div>');
            $mobileView = $('.mobile-clientes-container');
        } else {
            $mobileView.empty();
        }

        if (!clientes || clientes.length === 0) {
            const mensaje = mostrandoEliminados ? 
                'No hay clientes eliminados' : 
                'No hay clientes activos';
            
            $mobileView.append(`
                <div class="card mb-3">
                    <div class="card-body text-center py-4 text-muted">
                        <i class="fas fa-inbox fa-2x mb-2"></i><br>
                        ${mensaje}
                    </div>
                </div>
            `);
            return;
        }

        clientes.forEach(cliente => {
            // En DataTables, los datos vienen como un array, no como objeto
            // Si es un array, convertirlo a objeto
            if (Array.isArray(cliente)) {
                cliente = {
                    ced_cliente: cliente[0],
                    nomcliente: cliente[1],
                    correo: cliente[2],
                    telefono: cliente[3],
                    direccion: cliente[4],
                    estado: cliente[5]
                };
            }

            const estadoBadge = cliente.estado == 1 ? 
                'bg-success' : 'bg-secondary';
            const estadoTexto = cliente.estado == 1 ? 'Activo' : 'Inactivo';

            let acciones = '';
            if (cliente.estado == 1) {
                acciones = `
                    <button class="btn btn-sm btn-info detalles" data-cedula="${cliente.ced_cliente}" title="Ver detalles">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-primary actualizar" data-cedula="${cliente.ced_cliente}" title="Actualizar cliente">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <button class="btn btn-sm btn-danger eliminar" data-cedula="${cliente.ced_cliente}" title="Eliminar cliente">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                `;
            } else {
                acciones = `
                    <button class="btn btn-sm btn-info detalles" data-cedula="${cliente.ced_cliente}" title="Ver detalles">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning restaurar" data-cedula="${cliente.ced_cliente}" title="Restaurar cliente">
                        <i class="fas fa-undo"></i>
                    </button>
                `;
            }

            $mobileView.append(`
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0">${cliente.nomcliente}</h5>
                            <span class="badge ${estadoBadge}">${estadoTexto}</span>
                        </div>
                        
                        <div class="mb-2">
                            <div class="text-muted small">Cédula</div>
                            <div>${cliente.ced_cliente}</div>
                        </div>
                        
                        <div class="mb-2">
                            <div class="text-muted small">Correo</div>
                            <a href="mailto:${cliente.correo}" class="text-decoration-none">
                                ${cliente.correo || 'No especificado'}
                            </a>
                        </div>
                        
                        <div class="mb-2">
                            <div class="text-muted small">Teléfono</div>
                            <a href="tel:${cliente.telefono}" class="text-decoration-none">
                                ${cliente.telefono || 'No especificado'}
                            </a>
                        </div>
                        
                        <div class="mb-3">
                            <div class="text-muted small">Dirección</div>
                            <div>${cliente.direccion || 'No especificada'}</div>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2">
                            ${acciones}
                        </div>
                    </div>
                </div>
            `);
        });
    }

    // Función para actualizar el botón de toggle
    function actualizarBotonToggle() {
        const btn = $('#btnToggleEstado');
        btn.html(`<i class="fas ${mostrandoEliminados ? 'fa-user-check' : 'fa-trash-restore'} me-2"></i>
                  ${mostrandoEliminados ? 'Mostrar Activos' : 'Mostrar Eliminados'}`);
        btn.removeClass('btn-warning btn-info').addClass(mostrandoEliminados ? 'btn-info' : 'btn-warning');
    }

    // Función para alternar entre mostrar activos e inactivos
    function toggleClientes() {
        mostrandoEliminados = !mostrandoEliminados;
        
        console.log('Cambiando estado. Mostrar eliminados:', mostrandoEliminados);
        
        // Actualizar URL sin recargar la página
        const url = new URL(window.location.href);
        if (mostrandoEliminados) {
            url.searchParams.set('mostrarEliminados', 'true');
        } else {
            url.searchParams.delete('mostrarEliminados');
        }
        
        // Cambiar la URL sin recargar
        window.history.pushState({}, '', url.toString());
        
        // Actualizar el botón
        actualizarBotonToggle();
        
        // Recargar los datos de la DataTable
        if (table) {
            table.ajax.reload();
        }
    }

    // Función para cargar formulario nuevo
    function cargarFormularioNuevo() {
        $('#contenidoNuevo').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-2 text-muted">Preparando formulario...</p>
            </div>
        `);
        
        $('#modalNuevo').modal('show');
        
        $.ajax({
            url: 'index.php?url=cliente&action=formNuevo',
            type: 'GET',
            success: function(data) {
                $('#contenidoNuevo').html(data);
                configurarValidacionFormularioNuevo();
            },
            error: function() {
                $('#contenidoNuevo').html('<div class="alert alert-danger">Error al cargar el formulario</div>');
            }
        });
    }

    // Función para cargar formulario de actualización
    function cargarFormularioActualizar(cedula) {
        $('#contenidoActualizar').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-2 text-muted">Cargando información del cliente...</p>
            </div>
        `);
        
        $('#modalActualizar').modal('show');
        
        $.ajax({
            url: `index.php?url=cliente&action=formActualizar&ced_cliente=${cedula}`,
            type: 'GET',
            success: function(data) {
                $('#contenidoActualizar').html(data);
                configurarValidacionFormularioActualizar();
            },
            error: function() {
                $('#contenidoActualizar').html('<div class="alert alert-danger">Error al cargar el formulario</div>');
            }
        });
    }

    // =============================================
    // VALIDACIÓN PARA FORMULARIO NUEVO CLIENTE
    // =============================================
    function validarFormularioNuevo(form) {
        let valido = true;
        let requiredMissing = [];
        let formatErrors = [];

        // Limpiar validaciones previas
        $(form).find('.is-invalid').removeClass('is-invalid');
        $(form).find('.is-valid').removeClass('is-valid');

        // Validar cédula
        const cedula = $('#ced_cliente').val();
        if (!cedula || cedula.trim() === '') {
            requiredMissing.push('cédula');
            $('#ced_cliente').addClass('is-invalid');
            valido = false;
        } else if (!/^\d{7,9}$/.test(cedula)) {
            formatErrors.push('La cédula debe contener entre 7 y 9 dígitos');
            $('#ced_cliente').addClass('is-invalid');
            valido = false;
        } else {
            $('#ced_cliente').removeClass('is-invalid').addClass('is-valid');
        }

        // Validar nombre
        const nombre = $('#nomcliente').val();
        if (!nombre || nombre.trim() === '') {
            requiredMissing.push('nombre');
            $('#nomcliente').addClass('is-invalid');
            valido = false;
        } else if (nombre.length < 3) {
            formatErrors.push('El nombre debe tener al menos 3 caracteres');
            $('#nomcliente').addClass('is-invalid');
            valido = false;
        } else {
            $('#nomcliente').removeClass('is-invalid').addClass('is-valid');
        }

        // Validar correo
        const correo = $('#correo').val();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!correo || correo.trim() === '') {
            requiredMissing.push('correo');
            $('#correo').addClass('is-invalid');
            valido = false;
        } else if (!emailRegex.test(correo)) {
            formatErrors.push('El formato del correo no es válido');
            $('#correo').addClass('is-invalid');
            valido = false;
        } else {
            $('#correo').removeClass('is-invalid').addClass('is-valid');
        }

        // Validar teléfono
        const telefono = $('#telefono').val();
        if (!telefono || telefono.trim() === '') {
            requiredMissing.push('teléfono');
            $('#telefono').addClass('is-invalid');
            valido = false;
        } else if (!/^04(12|14|16|24|26|22)\d{7}$/.test(telefono)) {
            formatErrors.push('Formato de teléfono inválido. Debe comenzar con 0412, 0414, 0416, 0422, 0424 o 0426 seguido de 7 dígitos');
            $('#telefono').addClass('is-invalid');
            valido = false;
        } else {
            $('#telefono').removeClass('is-invalid').addClass('is-valid');
        }

        // Validar dirección
        const direccion = $('#direccion').val();
        if (!direccion || direccion.trim() === '') {
            requiredMissing.push('dirección');
            $('#direccion').addClass('is-invalid');
            valido = false;
        } else if (direccion.length < 5) {
            formatErrors.push('La dirección debe tener al menos 5 caracteres');
            $('#direccion').addClass('is-invalid');
            valido = false;
        } else {
            $('#direccion').removeClass('is-invalid').addClass('is-valid');
        }

        // Construir mensajes de error
        let mensajes = [];
        if (requiredMissing.length > 0) {
            mensajes.push('Los siguientes campos son obligatorios: ' + requiredMissing.join(', '));
        }
        mensajes = mensajes.concat(formatErrors);

        // Mostrar mensajes de error
        if (mensajes.length > 0) {
            mostrarAlerta('Error de validación', mensajes.join('\n'), 'error');

            // Hacer scroll al primer campo con error
            const firstError = $(form).find('.is-invalid').first();
            if (firstError.length && firstError.offset()) {
                $('html, body').animate({
                    scrollTop: firstError.offset().top - 100
                }, 500);
            }
        }

        return valido;
    }

    // =============================================
    // VALIDACIÓN PARA FORMULARIO ACTUALIZAR CLIENTE
    // =============================================
    function validarFormularioActualizar(form) {
        let valido = true;
        let requiredMissing = [];
        let formatErrors = [];

        // Limpiar validaciones previas
        $(form).find('.is-invalid').removeClass('is-invalid');
        $(form).find('.is-valid').removeClass('is-valid');

        // En actualización, la cédula es de solo lectura y siempre tiene valor
        // No necesitamos validar que esté presente

        // Validar nombre
        const nombre = $('#nomcliente').val();
        if (!nombre || nombre.trim() === '') {
            requiredMissing.push('nombre');
            $('#nomcliente').addClass('is-invalid');
            valido = false;
        } else if (nombre.length < 3) {
            formatErrors.push('El nombre debe tener al menos 3 caracteres');
            $('#nomcliente').addClass('is-invalid');
            valido = false;
        } else {
            $('#nomcliente').removeClass('is-invalid').addClass('is-valid');
        }

        // Validar correo
        const correo = $('#correo').val();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!correo || correo.trim() === '') {
            requiredMissing.push('correo');
            $('#correo').addClass('is-invalid');
            valido = false;
        } else if (!emailRegex.test(correo)) {
            formatErrors.push('El formato del correo no es válido');
            $('#correo').addClass('is-invalid');
            valido = false;
        } else {
            $('#correo').removeClass('is-invalid').addClass('is-valid');
        }

        // Validar teléfono
        const telefono = $('#telefono').val();
        if (!telefono || telefono.trim() === '') {
            requiredMissing.push('teléfono');
            $('#telefono').addClass('is-invalid');
            valido = false;
        } else if (!/^04(12|14|16|24|26|22)\d{7}$/.test(telefono)) {
            formatErrors.push('Formato de teléfono inválido. Debe comenzar con 0412, 0414, 0416, 0422, 0424 o 0426 seguido de 7 dígitos');
            $('#telefono').addClass('is-invalid');
            valido = false;
        } else {
            $('#telefono').removeClass('is-invalid').addClass('is-valid');
        }

        // Validar dirección
        const direccion = $('#direccion').val();
        if (!direccion || direccion.trim() === '') {
            requiredMissing.push('dirección');
            $('#direccion').addClass('is-invalid');
            valido = false;
        } else if (direccion.length < 5) {
            formatErrors.push('La dirección debe tener al menos 5 caracteres');
            $('#direccion').addClass('is-invalid');
            valido = false;
        } else {
            $('#direccion').removeClass('is-invalid').addClass('is-valid');
        }

        // Construir mensajes de error
        let mensajes = [];
        if (requiredMissing.length > 0) {
            mensajes.push('Los siguientes campos son obligatorios: ' + requiredMissing.join(', '));
        }
        mensajes = mensajes.concat(formatErrors);

        // Mostrar mensajes de error
        if (mensajes.length > 0) {
            mostrarAlerta('Error de validación', mensajes.join('\n'), 'error');

            // Hacer scroll al primer campo con error
            const firstError = $(form).find('.is-invalid').first();
            if (firstError.length && firstError.offset()) {
                $('html, body').animate({
                    scrollTop: firstError.offset().top - 100
                }, 500);
            }
        }

        return valido;
    }

    // =============================================
    // CONFIGURACIÓN DE VALIDACIÓN PARA FORMULARIO NUEVO
    // =============================================
    function configurarValidacionFormularioNuevo() {
        $('#formCliente').off('submit').on('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const formData = $(form).serialize();
            const actionUrl = 'index.php?url=cliente&action=guardar';
            
            // Validar formulario NUEVO
            if (!validarFormularioNuevo(form)) {
                return false;
            }
            
            // Mostrar loading
            const submitBtn = $(form).find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.html('<i class="fas fa-spinner fa-spin me-1"></i>Procesando...').prop('disabled', true);
            
            // Enviar formulario
            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        $('#modalNuevo').modal('hide');
                        toastr.success(response.message);
                        
                        // Recargar la DataTable
                        if (table) {
                            table.ajax.reload();
                        }
                    } else {
                        const errorMsg = response.message || 'Error al procesar la solicitud';
                        toastr.error(errorMsg);
                        console.error('Error del servidor:', response);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error completo en la petición:', {
                        status: status,
                        error: error,
                        responseText: xhr.responseText,
                        statusText: xhr.statusText,
                        statusCode: xhr.status
                    });
                    
                    let errorMessage = 'Error en la solicitud: ';
                    
                    if (xhr.status === 0) {
                        errorMessage += 'No hay conexión a internet o el servidor no responde';
                    } else if (xhr.status === 500) {
                        errorMessage += 'Error interno del servidor (500)';
                    } else if (xhr.status === 404) {
                        errorMessage += 'Recurso no encontrado (404)';
                    } else {
                        errorMessage += xhr.statusText || error;
                    }
                    
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response && response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        if (xhr.responseText) {
                            errorMessage += '<br>Detalles: ' + xhr.responseText.substring(0, 200);
                        }
                    }
                    
                    toastr.error(errorMessage);
                },
                complete: function() {
                    // Restaurar botón
                    submitBtn.html(originalText).prop('disabled', false);
                }
            });
        });

        // Validación en tiempo real para formulario NUEVO
        $('#ced_cliente').off('input').on('input', function() {
            const cedula = $(this).val().trim();
            if (cedula && /^\d{7,9}$/.test(cedula)) {
                $(this).removeClass('is-invalid').addClass('is-valid');
            } else if (cedula) {
                $(this).removeClass('is-valid').addClass('is-invalid');
            } else {
                $(this).removeClass('is-valid is-invalid');
            }
        });

        $('#nomcliente').off('input').on('input', function() {
            const nombre = $(this).val().trim();
            if (nombre && nombre.length >= 3) {
                $(this).removeClass('is-invalid').addClass('is-valid');
            } else if (nombre) {
                $(this).removeClass('is-valid').addClass('is-invalid');
            } else {
                $(this).removeClass('is-valid is-invalid');
            }
        });

        $('#correo').off('input').on('input', function() {
            const correo = $(this).val().trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (correo && emailRegex.test(correo)) {
                $(this).removeClass('is-invalid').addClass('is-valid');
            } else if (correo) {
                $(this).removeClass('is-valid').addClass('is-invalid');
            } else {
                $(this).removeClass('is-valid is-invalid');
            }
        });

        $('#telefono').off('input').on('input', function() {
            const telefono = $(this).val().trim();
            if (telefono && /^04(12|14|16|24|26|22)\d{7}$/.test(telefono)) {
                $(this).removeClass('is-invalid').addClass('is-valid');
            } else if (telefono) {
                $(this).removeClass('is-valid').addClass('is-invalid');
            } else {
                $(this).removeClass('is-valid is-invalid');
            }
        });

        $('#direccion').off('input').on('input', function() {
            const direccion = $(this).val().trim();
            if (direccion && direccion.length >= 5) {
                $(this).removeClass('is-invalid').addClass('is-valid');
            } else if (direccion) {
                $(this).removeClass('is-valid').addClass('is-invalid');
            } else {
                $(this).removeClass('is-valid is-invalid');
            }
        });
    }

    // =============================================
    // CONFIGURACIÓN DE VALIDACIÓN PARA FORMULARIO ACTUALIZAR
    // =============================================
    function configurarValidacionFormularioActualizar() {
        $('#formCliente').off('submit').on('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const formData = $(form).serialize();
            const actionUrl = 'index.php?url=cliente&action=actualizar';
            
            // Validar formulario ACTUALIZAR
            if (!validarFormularioActualizar(form)) {
                return false;
            }
            
            // Mostrar loading
            const submitBtn = $(form).find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.html('<i class="fas fa-spinner fa-spin me-1"></i>Procesando...').prop('disabled', true);
            
            // Enviar formulario
            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        $('#modalActualizar').modal('hide');
                        toastr.success(response.message);
                        
                        // Recargar la DataTable
                        if (table) {
                            table.ajax.reload();
                        }
                    } else {
                        const errorMsg = response.message || 'Error al procesar la solicitud';
                        toastr.error(errorMsg);
                        console.error('Error del servidor:', response);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error completo en la petición:', {
                        status: status,
                        error: error,
                        responseText: xhr.responseText,
                        statusText: xhr.statusText,
                        statusCode: xhr.status
                    });
                    
                    let errorMessage = 'Error en la solicitud: ';
                    
                    if (xhr.status === 0) {
                        errorMessage += 'No hay conexión a internet o el servidor no responde';
                    } else if (xhr.status === 500) {
                        errorMessage += 'Error interno del servidor (500)';
                    } else if (xhr.status === 404) {
                        errorMessage += 'Recurso no encontrado (404)';
                    } else {
                        errorMessage += xhr.statusText || error;
                    }
                    
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response && response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        if (xhr.responseText) {
                            errorMessage += '<br>Detalles: ' + xhr.responseText.substring(0, 200);
                        }
                    }
                    
                    toastr.error(errorMessage);
                },
                complete: function() {
                    // Restaurar botón
                    submitBtn.html(originalText).prop('disabled', false);
                }
            });
        });

        // Validación en tiempo real para formulario ACTUALIZAR
        $('#nomcliente').off('input').on('input', function() {
            const nombre = $(this).val().trim();
            if (nombre && nombre.length >= 3) {
                $(this).removeClass('is-invalid').addClass('is-valid');
            } else if (nombre) {
                $(this).removeClass('is-valid').addClass('is-invalid');
            } else {
                $(this).removeClass('is-valid is-invalid');
            }
        });

        $('#correo').off('input').on('input', function() {
            const correo = $(this).val().trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (correo && emailRegex.test(correo)) {
                $(this).removeClass('is-invalid').addClass('is-valid');
            } else if (correo) {
                $(this).removeClass('is-valid').addClass('is-invalid');
            } else {
                $(this).removeClass('is-valid is-invalid');
            }
        });

        $('#telefono').off('input').on('input', function() {
            const telefono = $(this).val().trim();
            if (telefono && /^04(12|14|16|24|26|22)\d{7}$/.test(telefono)) {
                $(this).removeClass('is-invalid').addClass('is-valid');
            } else if (telefono) {
                $(this).removeClass('is-valid').addClass('is-invalid');
            } else {
                $(this).removeClass('is-valid is-invalid');
            }
        });

        $('#direccion').off('input').on('input', function() {
            const direccion = $(this).val().trim();
            if (direccion && direccion.length >= 5) {
                $(this).removeClass('is-invalid').addClass('is-valid');
            } else if (direccion) {
                $(this).removeClass('is-valid').addClass('is-invalid');
            } else {
                $(this).removeClass('is-valid is-invalid');
            }
        });
    }

    // Función para confirmar acciones (eliminar/restaurar)
    function confirmarAccion(cedula, url, successMessage) {
        $.ajax({
            url: url,
            type: 'POST',
            data: { cedula: cedula },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    toastr.success(successMessage);
                    // Recargar la DataTable
                    if (table) {
                        table.ajax.reload();
                    }
                } else {
                    toastr.error(response.message || 'Ocurrió un error al procesar la solicitud');
                }
            },
            error: function(xhr, status, error) {
                toastr.error('Error en la solicitud: ' + error);
                console.error('Error en la petición:', error, xhr.responseText);
            }
        });
    }

    // Función para mostrar detalles del cliente
    function mostrarDetallesCliente(cedula) {
        console.log('Solicitando detalles del cliente con cédula:', cedula);
        
        // Mostrar un indicador de carga
        const $tablaPedidos = $('#detalle-pedidos');
        const $sinPedidos = $('#sin-pedidos');
        const $tablaContainer = $tablaPedidos.parents('.table-responsive');
        
        $tablaPedidos.html('<tr><td colspan="5" class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div></td></tr>');
        $tablaContainer.show();
        $sinPedidos.addClass('d-none');
        
        // Mostrar el modal inmediatamente
        const modal = new bootstrap.Modal(document.getElementById('modalDetallesCliente'));
        modal.show();
        
        $.ajax({
            url: `index.php?url=cliente&action=detalles&ced_cliente=${cedula}`,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log('Respuesta del servidor:', response);
                
                if (response.success) {
                    const cliente = response.data.cliente;
                    const pedidos = response.data.pedidos || [];
                    
                    console.log('Datos del cliente:', cliente);
                    console.log('Pedidos recibidos:', pedidos);

                    // Actualizar información del cliente
                    $('#detalle-cedula').text(cliente.ced_cliente || 'No especificado');
                    $('#detalle-nombre').text(cliente.nomcliente || 'No especificado');
                    $('#detalle-correo').text(cliente.correo || 'No especificado');
                    $('#detalle-telefono').text(cliente.telefono || 'No especificado');
                    $('#detalle-direccion').text(cliente.direccion || 'No especificada');

                    // Actualizar estado del cliente
                    const estadoBadge = $('#detalle-estado');
                    if (parseInt(cliente.estado) === 1) {
                        estadoBadge.removeClass('bg-secondary').addClass('bg-success').text('Activo');
                    } else {
                        estadoBadge.removeClass('bg-success').addClass('bg-secondary').text('Inactivo');
                    }

                    // Actualizar historial de pedidos
                    $tablaPedidos.empty();
                    
                    if (pedidos && pedidos.length > 0) {
                        console.log('Mostrando', pedidos.length, 'pedidos');
                        
                        pedidos.forEach((pedido, index) => {
                            console.log(`Procesando pedido ${index + 1}:`, pedido);
                            
                            // Determinar clase de estado
                            let estadoClase = '';
                            const estadoCodigo = parseInt(pedido.estado_codigo);
                            
                            switch(estadoCodigo) {
                                case 0: // Pendiente
                                    estadoClase = 'bg-warning text-dark';
                                    break;
                                case 1: // Completado
                                    estadoClase = 'bg-success';
                                    break;
                                case 2: // Cancelado
                                    estadoClase = 'bg-danger';
                                    break;
                                default:
                                    estadoClase = 'bg-secondary';
                            }
                            
                            const fila = `
                                <tr>
                                    <td>${pedido.id_pedido || 'N/A'}</td>
                                    <td>${pedido.fecha || 'Fecha no disponible'}</td>
                                    <td>${pedido.cant_productos || 0} producto(s)</td>
                                    <td>${pedido.total || '0,00'} $.</td>
                                    <td><span class="badge ${estadoClase}">${pedido.estado || 'Desconocido'}</span></td>
                                </tr>
                            `;
                            $tablaPedidos.append(fila);
                        });
                        
                        $tablaContainer.show();
                        $sinPedidos.addClass('d-none');
                    } else {
                        console.log('No se encontraron pedidos para este cliente');
                        $tablaContainer.hide();
                        $sinPedidos.removeClass('d-none');
                    }
                } else {
                    console.error('Error en la respuesta del servidor:', response.message || 'Error desconocido');
                    $tablaContainer.hide();
                    $sinPedidos.html('<i class="fas fa-exclamation-triangle fa-3x mb-3 text-warning"></i><p class="mb-0">Error al cargar los pedidos</p>');
                    $sinPedidos.removeClass('d-none');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la petición AJAX:', error);
                $tablaContainer.hide();
                $sinPedidos.html('<i class="fas fa-exclamation-triangle fa-3x mb-3 text-danger"></i><p class="mb-0">Error al conectar con el servidor</p>');
                $sinPedidos.removeClass('d-none');
                toastr.error('Error al cargar los detalles del cliente');
            }
        });
    }

    // Evento para botón nuevo cliente
    $('#btnNuevoCliente').click(cargarFormularioNuevo);

    // Evento para botón toggle estado
    $('#btnToggleEstado').click(toggleClientes);

    // Evento para botones de detalles
    $(document).on('click', '.detalles', function(e) {
        e.preventDefault();
        const cedula = $(this).data('cedula');
        mostrarDetallesCliente(cedula);
    });

    // Evento para botones de actualizar
    $(document).on('click', '.actualizar', function(e) {
        e.preventDefault();
        const cedula = $(this).data('cedula');
        cargarFormularioActualizar(cedula);
    });

    // Evento para botones de eliminar
    $(document).on('click', '.eliminar', function(e) {
        e.preventDefault();
        const cedula = $(this).data('cedula');
        $('#cedulaEliminar').val(cedula);
        $('#modalEliminar').modal('show');
    });

    // Evento para botones de restaurar
    $(document).on('click', '.restaurar', function(e) {
        e.preventDefault();
        const cedula = $(this).data('cedula');
        $('#cedulaRestaurar').val(cedula);
        $('#modalRestaurar').modal('show');
    });

    // Confirmar eliminación
    $('#confirmarEliminar').click(function() {
        const cedula = $('#cedulaEliminar').val();
        $('#modalEliminar').modal('hide');
        confirmarAccion(cedula, 'index.php?url=cliente&action=eliminar', 'Cliente eliminado correctamente');
    });

    // Confirmar restauración
    $('#confirmarRestaurar').click(function() {
        const cedula = $('#cedulaRestaurar').val();
        $('#modalRestaurar').modal('hide');
        confirmarAccion(cedula, 'index.php?url=cliente&action=restaurar', 'Cliente restaurado correctamente');
    });

    // Manejar el evento popstate para cuando el usuario navega con el historial
    $(window).on('popstate', function() {
        if (table) {
            table.ajax.reload();
        }
    });

    // Inicialización al cargar la página
    function inicializar() {
        // Inicializar DataTable
        inicializarDataTable();
        
        // Inicializar dropdown de reportes
        inicializarDropdownReportesClientes();
    }

    // Iniciar la aplicación
    inicializar();
});