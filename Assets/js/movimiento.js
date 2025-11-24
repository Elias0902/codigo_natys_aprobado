$(document).ready(function() {
    let dataTable;
    let currentFilter = 'activos';

    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "5000",
        "extendedTimeOut": "2000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };

    function initializeDropdown() {
        $('#dropdownReportesButton').on('click', function(e) {
            e.stopPropagation();
            const $menu = $(this).next('.dropdown-menu');
            const isVisible = $menu.hasClass('show');

            $('.dropdown-menu').removeClass('show');

            if (!isVisible) {
                $menu.addClass('show');
                $(this).attr('aria-expanded', 'true');
            } else {
                $(this).attr('aria-expanded', 'false');
            }
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('.dropdown').length) {
                $('.dropdown-menu').removeClass('show');
                $('.dropdown-toggle').attr('aria-expanded', 'false');
            }
        });

        $('.dropdown-menu a').on('click', function() {
            $('.dropdown-menu').removeClass('show');
            $('.dropdown-toggle').attr('aria-expanded', 'false');
        });
    }

    initializeDropdown();

    // Función para inicializar DataTable
    function inicializarDataTable() {
        if (dataTable) {
            dataTable.destroy();
            $('#movimientos tbody').empty();
        }
        
        // Intentar cargar datos del almacenamiento local primero
        const datosLocales = JSON.parse(localStorage.getItem('ultimosMovimientos') || '[]');
        
        dataTable = $('#movimientos').DataTable({
            // Mostrar datos locales iniciales si existen
            data: datosLocales,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            processing: true,
            serverSide: true,
            ajax: {
                url: '/Natys/index.php?url=movimiento&action=datatables',
                type: 'POST',
                data: function(d) {
                    d.mostrarHistorial = (currentFilter === 'historial') ? 1 : 0;
                },
                dataSrc: function(json) {
                    // Verificar si la respuesta es válida
                    if (!json || !Array.isArray(json.data)) {
                        console.error('Respuesta del servidor inválida:', json);
                        toastr.error('Error al procesar la respuesta del servidor');
                        return [];
                    }
                    cargarVistaMobile(json.data);
                    return json.data;
                },
                error: function(xhr, error, thrown) {
                    console.error('Error en DataTables:', error, thrown);
                    
                    // Mostrar mensaje más descriptivo según el tipo de error
                    let errorMessage = 'Error al cargar los datos de movimientos';
                    
                    if (error === 'timeout') {
                        errorMessage = 'Tiempo de espera agotado. Verifica tu conexión a Internet.';
                    } else if (error === 'error') {
                        if (xhr.status === 0) {
                            errorMessage = 'No hay conexión a Internet. Verifica tu conexión e intenta de nuevo.';
                        } else if (xhr.status === 500) {
                            errorMessage = 'Error interno del servidor. Por favor, intente más tarde.';
                        }
                    }
                    
                    // Mostrar mensaje de error en la interfaz
                    toastr.error(errorMessage, 'Error', {timeOut: 5000});
                    
                    // Mostrar datos locales si están disponibles (útil para modo offline)
                    const datosLocales = JSON.parse(localStorage.getItem('ultimosMovimientos') || '[]');
                    if (datosLocales.length > 0) {
                        toastr.info('Mostrando datos almacenados localmente', 'Modo sin conexión');
                        cargarVistaMobile(datosLocales);
                        return datosLocales;
                    }
                    
                    return [];
                },
                // Agregar timeout para evitar que la petición se quede colgada
                timeout: 10000 // 10 segundos
            },
            columns: [
                { 
                    data: 'num_movimiento',
                    className: 'text-center fw-bold'
                },
                { 
                    data: 'fecha',
                    render: function(data) {
                        return new Date(data).toLocaleDateString('es-ES');
                    },
                    className: 'text-center'
                },
                { 
                    data: null,
                    render: function(data) {
                        let html = '<div class="d-flex align-items-center">';
                        html += '<i class="fas fa-box me-2 text-muted"></i>';
                        html += '<div>';
                        html += '<div class="fw-semibold">' + (data.producto_nombre || 'N/A') + '</div>';
                        html += '<small class="text-muted">Código: ' + (data.cod_producto || 'N/A') + '</small>';
                        if (data.producto_estado === 0) {
                            html += ' <span class="badge bg-secondary ms-1">Inactivo</span>';
                        }
                        html += '</div></div>';
                        return html;
                    }
                },
                { 
                    data: null,
                    render: function(data) {
                        const cantidad = parseFloat(data.cant_productos);
                        const stockActual = parseFloat(data.stock_actual || 0);
                        
                        let html = '<div class="text-center">';
                        
                        // Mostrar cantidad del movimiento
                        const badgeClass = cantidad > 0 ? 'bg-success' : 'bg-danger';
                        const icon = cantidad > 0 ? 'fa-arrow-down' : 'fa-arrow-up';
                        const tipoMovimiento = cantidad > 0 ? 'Entrada' : 'Salida';
                        
                        html += `<div class="mb-1">
                                <span class="badge ${badgeClass}">
                                    <i class="fas ${icon} me-1"></i>
                                    ${Math.abs(cantidad)} (${tipoMovimiento})
                                </span>
                                </div>`;
                        
                        // Mostrar stock actual
                        html += `<div>
                                <small class="text-muted">Stock:</small>
                                <span class="badge bg-primary">
                                    <i class="fas fa-boxes me-1"></i>
                                    ${stockActual}
                                </span>
                                </div>`;
                        
                        html += '</div>';
                        return html;
                    },
                    className: 'text-center'
                },
                { 
                    data: 'estado',
                    render: function(data) {
                        return data == 1 
                            ? '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Activo</span>'
                            : '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Finalizado</span>';
                    },
                    className: 'text-center'
                },
                {
                    data: null,
                    render: function(data) {
                        let html = '<div class="btn-group btn-group-sm" role="group">';
                        html += '<button class="btn btn-info btn-actions btn-detalles" data-id="' + data.num_movimiento + '" title="Ver Detalle">';
                        html += '<i class="fas fa-eye"></i></button>';

                        if (data.cod_producto) {
                            html += '<button class="btn btn-danger btn-actions btn-kardex" data-cod="' + data.cod_producto + '" title="Ver Kardex">';
                            html += '<i class="fas fa-list"></i></button>';
                        }

                        if (currentFilter === 'activos') {
                            html += '<button class="btn btn-primary btn-actions btn-editar" data-id="' + data.num_movimiento + '" title="Actualizar">';
                            html += '<i class="fas fa-sync-alt"></i></button>';
                            html += '<button class="btn btn-danger btn-actions btn-eliminar" data-id="' + data.num_movimiento + '" title="Eliminar">';
                            html += '<i class="fas fa-trash"></i></button>';
                        } else {
                            // No mostrar botón de restaurar
                        }

                        html += '</div>';
                        return html;
                    },
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }
            ],
            order: [[0, 'desc']],
            responsive: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Todos']],
            dom: '<"row"<"col-md-6"l><"col-md-6"f>>rt<"row"<"col-md-6"i><"col-md-6"p>>',
            drawCallback: function(settings) {
                // Actualizar contadores
                const total = settings.json.recordsTotal;
                const filtered = settings.json.recordsFiltered;
                $('#totalMovimientos').text(total);
                $('#movimientosFiltrados').text(filtered);
            }
        });
    }

    // Función para cargar vista móvil
    function cargarVistaMobile(data) {
        // Guardar datos en almacenamiento local para uso sin conexión
        if (data && data.length > 0) {
            try {
                localStorage.setItem('ultimosMovimientos', JSON.stringify(data));
            } catch (e) {
                console.warn('No se pudieron guardar los datos localmente:', e);
            }
        }
        let html = '';
        
        if (data.length === 0) {
            html = '<div class="alert alert-info text-center">';
            html += '<i class="fas fa-info-circle me-2"></i>';
            html += 'No hay movimientos para mostrar';
            html += '</div>';
        } else {
            data.forEach(function(movimiento) {
                const fecha = new Date(movimiento.fecha).toLocaleDateString('es-ES');
                const estadoBadge = movimiento.estado == 1 
                    ? '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Activo</span>'
                    : '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Finalizado</span>';
                
                const cantidad = parseFloat(movimiento.cant_productos);
                const stockActual = parseFloat(movimiento.stock_actual || 0);
                const esSalida = cantidad < 0;
                const tipoMovimiento = cantidad > 0 ? 'Entrada' : 'Salida';
                
                // Clase adicional para resaltar filas de salida en el historial
                const filaClase = (currentFilter === 'historial' && esSalida) ? 'movimiento-salida' : '';
                
                let productoHtml = movimiento.producto_nombre || 'N/A';
                if (movimiento.producto_estado === 0) {
                    productoHtml += ' <span class="badge bg-secondary">Inactivo</span>';
                }
                
                html += `
                    <div class="card mb-3 shadow-sm ${filaClase}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0 text-primary">
                                    <i class="fas fa-exchange-alt me-2"></i>
                                    Movimiento #${movimiento.num_movimiento}
                                </h5>
                                ${estadoBadge}
                            </div>
                            
                            <div class="row mb-2">
                                <div class="col-6">
                                    <div class="text-muted small">
                                        <i class="fas fa-calendar me-1"></i>Fecha
                                    </div>
                                    <div class="fw-semibold">${fecha}</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small">
                                        <i class="fas fa-hashtag me-1"></i>Cantidad
                                    </div>
                                    <div>
                                        <span class="badge ${esSalida ? 'bg-danger' : 'bg-success'}">
                                            <i class="fas ${esSalida ? 'fa-arrow-up' : 'fa-arrow-down'} me-1"></i>
                                            ${Math.abs(cantidad)} (${tipoMovimiento})
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="text-muted small">
                                        <i class="fas fa-boxes me-1"></i>Stock Actual
                                    </div>
                                    <div>
                                        <span class="badge bg-primary">
                                            <i class="fas fa-boxes me-1"></i>
                                            ${stockActual}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="text-muted small">
                                    <i class="fas fa-box me-1"></i>Producto
                                </div>
                                <div class="fw-semibold">${productoHtml}</div>
                                <small class="text-muted">Código: ${movimiento.cod_producto || 'N/A'}</small>
                            </div>
                            
                            <div class="d-flex justify-content-end gap-2">
                                <button class="btn btn-sm btn-info btn-action btn-detalles" data-id="${movimiento.num_movimiento}" title="Ver Detalle">
                                    <i class="fas fa-eye"></i>
                                </button>`;

                if (movimiento.cod_producto) {
                    html += `
                                <button class="btn btn-sm btn-danger btn-action btn-kardex" data-cod="${movimiento.cod_producto}" title="Ver Kardex">
                                    <i class="fas fa-list"></i>
                                </button>`;
                }

                if (currentFilter === 'activos') {
                    html += `
                            <button class="btn btn-sm btn-primary btn-action btn-editar" data-id="${movimiento.num_movimiento}" title="Actualizar">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <button class="btn btn-sm btn-danger btn-action btn-eliminar" data-id="${movimiento.num_movimiento}" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>`;
                } else {
                    // No mostrar botón de restaurar
                }

                html += `
                            </div>
                        </div>
                    </div>`;
            });
        }
        
        $('#movimientosMobile').html(html);
    }

    // Verificar conectividad antes de inicializar
    const checkConnectivity = () => {
        return new Promise((resolve) => {
            // Intentar hacer una petición HEAD a un recurso pequeño
            fetch('/Natys/favicon.ico', { method: 'HEAD', cache: 'no-cache' })
                .then(() => resolve(true))
                .catch(() => resolve(false));
        });
    };

    // Inicializar DataTable al cargar la página
    checkConnectivity().then(online => {
        if (!online) {
            toastr.warning('Estás trabajando sin conexión. Algunas funciones pueden estar limitadas.', 'Modo sin conexión', {timeOut: 10000});
        }
        inicializarDataTable();
    });

    // Event Listeners para filtros
    $('#btnActivos').on('click', function() {
        currentFilter = 'activos';
        $(this).addClass('active').removeClass('btn-outline-primary');
        $('#btnHistorial').removeClass('active').addClass('btn-outline-primary');
        inicializarDataTable();
    });

    $('#btnHistorial').on('click', function() {
        currentFilter = 'historial';
        $(this).addClass('active').removeClass('btn-outline-primary');
        $('#btnActivos').removeClass('active').addClass('btn-outline-primary');
        inicializarDataTable();
    });

    // Event Listeners para botones de acción (delegación)
    $(document).on('click', '.btn-detalles', function() {
        const id = $(this).data('id');
        cargarDetallesMovimiento(id);
    });

    $(document).on('click', '.btn-kardex', function() {
        const codProducto = $(this).data('cod');
        cargarKardexProducto(codProducto);
    });

    $(document).on('click', '.btn-editar', function() {
        const id = $(this).data('id');
        cargarFormularioEdicion(id);
    });

    $(document).on('click', '.btn-eliminar', function() {
        const id = $(this).data('id');
        $('#idEliminar').val(id);
        $('#modalEliminar').modal('show');
    });

    // Botón nuevo movimiento
    $('#btnNuevoMovimiento').on('click', function() {
        $('#modalNuevo').modal('show');
    });

    // Confirmar eliminación
    $('#confirmarEliminar').on('click', function() {
        const id = $('#idEliminar').val();
        eliminarMovimiento(id);
    });

    // Guardar nuevo movimiento
    $('#guardarMovimiento').on('click', function() {
        if (validarFormularioMovimiento()) {
            guardarMovimiento();
        }
    });

    // Actualizar movimiento editado
    $(document).on('click', '#actualizarMovimiento', function() {
        if (validarFormularioEditar()) {
            actualizarMovimiento();
        }
    });

    // Función auxiliar para actualizar el estado de validación
    function updateValidationState(element, isValid, errorMessage) {
        const $input = $(element);
        const $container = $input.closest('.position-relative') || $input.closest('.mb-3');
        const $invalidFeedback = $container.find('.invalid-feedback');
        const $validFeedback = $container.find('.valid-feedback');

        // Actualizar mensaje de error si es personalizado
        if (errorMessage && $invalidFeedback.length) {
            $invalidFeedback.html('<i class="fas fa-exclamation-circle me-2"></i>' + errorMessage);
        }

        // Actualizar clases de validación
        if (isValid) {
            $input.removeClass('is-invalid').addClass('is-valid');
            $invalidFeedback.hide();
            $validFeedback.html('<i class="fas fa-check-circle me-2"></i>Campo válido').show();
        } else if ($input.val() !== '') {
            $input.removeClass('is-valid').addClass('is-invalid');
            $validFeedback.hide();
            $invalidFeedback.show();
        } else {
            $input.removeClass('is-valid is-invalid');
            $validFeedback.hide();
            $invalidFeedback.hide();
        }

        // Validar el formulario completo
        const $form = $input.closest('form');
        if ($form.length) {
            const isValidForm = $form[0].checkValidity();
            $form.find('button[type="submit"]').prop('disabled', !isValidForm);
        }
    }

    // Validación en tiempo real para fecha
    $(document).on('input change', '#fecha', function() {
        const fecha = $(this).val().trim();

        // Si el campo está vacío, muestra error de campo requerido
        if (fecha === '') {
            updateValidationState(this, false, 'Por favor seleccione una fecha');
            return;
        }

        // Validar que sea una fecha válida y no futura
        const fechaSeleccionada = new Date(fecha);
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);

        const isValid = fechaSeleccionada <= hoy && !isNaN(fechaSeleccionada.getTime());
        updateValidationState(this, isValid, 'La fecha debe ser válida y no puede ser futura');
    });

    // Validación en tiempo real para producto
    $(document).on('change', '#producto', function() {
        const producto = $(this).val();

        // Si no se ha seleccionado producto, muestra error
        if (!producto || producto === '') {
            updateValidationState(this, false, 'Por favor seleccione un producto');
            return;
        }

        updateValidationState(this, true, '');
    });

    // Validación en tiempo real para cantidad
    $(document).on('input', '#cantidad', function() {
        const cantidad = $(this).val().trim();

        // Si el campo está vacío, muestra error de campo requerido
        if (cantidad === '') {
            updateValidationState(this, false, 'Por favor ingrese la cantidad');
            return;
        }

        // Validar que sea un número positivo
        const numCantidad = parseFloat(cantidad);
        const isValid = !isNaN(numCantidad) && numCantidad > 0 && numCantidad <= 999999.99;
        updateValidationState(this, isValid, 'La cantidad debe ser un número positivo (máximo 999,999.99)');
    });

    // Validación en tiempo real para precio
    $(document).on('input', '#precio', function() {
        const precio = $(this).val().trim();

        // Si el campo está vacío, muestra error de campo requerido
        if (precio === '') {
            updateValidationState(this, false, 'Por favor ingrese el precio');
            return;
        }

        // Validar que sea un número positivo
        const numPrecio = parseFloat(precio);
        const isValid = !isNaN(numPrecio) && numPrecio >= 0 && numPrecio <= 999999999.99;
        updateValidationState(this, isValid, 'El precio debe ser un número válido (máximo 999,999,999.99)');
    });

    // Validación en tiempo real para observaciones
    $(document).on('input', '#observaciones', function() {
        const observaciones = $(this).val().trim();

        // Campo opcional, pero si se escribe algo, validar longitud mínima
        if (observaciones !== '' && observaciones.length < 3) {
            updateValidationState(this, false, 'Las observaciones deben tener al menos 3 caracteres');
            return;
        }

        // Si está vacío o tiene contenido válido, mostrar como válido
        updateValidationState(this, true, '');
    });

    // Validación en tiempo real para formulario de nuevo movimiento (campos requeridos)
    $('#formNuevoMovimiento input[required], #formNuevoMovimiento select[required]').on('blur', function() {
        validarCampo($(this));
    });

    // Cargar precio cuando se selecciona un producto
    $('#producto').on('change', function() {
        const codProducto = $(this).val();
        if (codProducto) {
            cargarPrecioProducto(codProducto);
        } else {
            $('#precio').val('');
        }
    });

    // Eventos para reportes por fechas
    $('#modalReportesFechas').on('show.bs.modal', function(e) {
        // Establecer fecha actual como fecha fin (readonly)
        const hoy = new Date().toISOString().split('T')[0];
        $('#fecha_fin_reporte').val(hoy);
    });

    $('#btnGenerarReporteFechas').on('click', function() {
        const tipo = $('#tipo_reporte').val();
        const fechaInicio = $('#fecha_inicio_reporte').val();
        const fechaFin = $('#fecha_fin_reporte').val();

        if (!tipo) {
            toastr.error('Por favor seleccione un tipo de reporte');
            $('#tipo_reporte').addClass('is-invalid');
            return;
        }

        if (!fechaInicio) {
            toastr.error('Por favor seleccione la fecha de inicio');
            $('#fecha_inicio_reporte').addClass('is-invalid');
            return;
        }

        $('#tipo_reporte').removeClass('is-invalid');
        $('#fecha_inicio_reporte').removeClass('is-invalid');

        let action = '';
        switch(tipo) {
            case 'reporte_por_fechas':
                action = 'reporte_por_fechas';
                break;
            case 'entradas_por_fechas':
                action = 'entradas_por_fechas';
                break;
            case 'salidas_por_fechas':
                action = 'salidas_por_fechas';
                break;
            default:
                toastr.error('Tipo de reporte no válido');
                return;
        }

        const url = `/Natys/index.php?url=movimiento&action=${action}&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;
        window.open(url, '_blank');
        $('#modalReportesFechas').modal('hide');
        toastr.success('Generando reporte...');
    });

    // Eventos para Kardex
    $('#btnGenerarKardex').on('click', function() {
        const codProducto = $('#cod_producto_kardex').val().trim();

        if (!codProducto) {
            toastr.error('Por favor ingrese un código de producto');
            $('#cod_producto_kardex').addClass('is-invalid');
            return;
        }

        $('#cod_producto_kardex').removeClass('is-invalid');

        const url = `/Natys/index.php?url=movimiento&action=reporte_kardex&cod_producto=${codProducto}`;

        // Abrir en nueva pestaña con manejo de errores
        try {
            const newWindow = window.open(url, '_blank');
            if (newWindow) {
                $('#modalKardexProducto').modal('hide');
                $('#cod_producto_kardex').val('');
                toastr.success('Generando kardex del producto...');
            } else {
                toastr.error('No se pudo abrir la nueva pestaña. Verifique que no tenga bloqueadores de pop-ups.');
            }
        } catch (error) {
            console.error('Error al abrir nueva pestaña:', error);
            toastr.error('Error al generar el kardex. Intente nuevamente.');
        }
    });

    // Funciones de validación
    function validarCampo(campo) {
        const valor = campo.val().trim();
        const esRequerido = campo.prop('required');
        
        campo.removeClass('is-valid is-invalid');
        
        if (esRequerido && !valor) {
            campo.addClass('is-invalid');
            return false;
        }
        
        // Validaciones específicas por tipo de campo
        if (campo.attr('type') === 'number' && valor) {
            const min = campo.attr('min');
            const max = campo.attr('max');
            
            if (min && parseFloat(valor) < parseFloat(min)) {
                campo.addClass('is-invalid');
                return false;
            }
            
            if (max && parseFloat(valor) > parseFloat(max)) {
                campo.addClass('is-invalid');
                return false;
            }
        }
        
        if (campo.attr('type') === 'email' && valor && !esEmailValido(valor)) {
            campo.addClass('is-invalid');
            return false;
        }
        
        campo.addClass('is-valid');
        return true;
    }

    function esEmailValido(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }

    function validarFormularioMovimiento() {
        let esValido = true;

        $('#formNuevoMovimiento [required]').each(function() {
            if (!validarCampo($(this))) {
                esValido = false;
            }
        });

        if (!esValido) {
            toastr.error('Por favor complete todos los campos obligatorios correctamente');
            $('.is-invalid').first().focus();
        }

        return esValido;
    }

    function validarFormularioEditar() {
        let esValido = true;

        $('#formEditarMovimiento [required]').each(function() {
            if (!validarCampo($(this))) {
                esValido = false;
            }
        });

        if (!esValido) {
            toastr.error('Por favor complete todos los campos obligatorios correctamente');
            $('.is-invalid').first().focus();
        }

        return esValido;
    }

    // Funciones AJAX
    function cargarPrecioProducto(codProducto) {
        $.ajax({
            url: '/Natys/index.php?url=movimiento&action=obtenerPrecio',
            type: 'POST',
            data: { cod_producto: codProducto },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data && response.data.precio) {
                    $('#precio').val(response.data.precio);
                } else {
                    $('#precio').val('');
                    toastr.warning('No se pudo obtener el precio del producto');
                }
            },
            error: function() {
                $('#precio').val('');
                toastr.error('Error al obtener el precio del producto');
            }
        });
    }

    function cargarPrecioProductoEditar(codProducto) {
        $.ajax({
            url: '/Natys/index.php?url=movimiento&action=obtenerPrecio',
            type: 'POST',
            data: { cod_producto: codProducto },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data && response.data.precio) {
                    $('#edit_precio').val(response.data.precio);
                } else {
                    $('#edit_precio').val('');
                    toastr.warning('No se pudo obtener el precio del producto');
                }
            },
            error: function() {
                $('#edit_precio').val('');
                toastr.error('Error al obtener el precio del producto');
            }
        });
    }

    function cargarDetallesMovimiento(id) {
        $.ajax({
            url: '/Natys/index.php?url=movimiento&action=detalles&id=' + id,
            type: 'GET',
            success: function(response) {
                $('#contenidoDetalles').html(response);
                $('#modalDetalles').modal('show');
            },
            error: function() {
                toastr.error('Error al cargar los detalles del movimiento');
            }
        });
    }

    function cargarKardexProducto(codProducto) {
        $.ajax({
            url: '/Natys/index.php?url=movimiento&action=obtenerKardex',
            type: 'GET',
            data: { cod_producto: codProducto },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    mostrarKardexModal(response.data, codProducto);
                } else {
                    toastr.error(response.message || 'Error al cargar el kardex del producto');
                }
            },
            error: function() {
                toastr.error('Error al cargar el kardex del producto');
            }
        });
    }

    function mostrarKardexModal(kardexData, codProducto) {
        let html = '<div class="modal fade" id="modalKardex" tabindex="-1" aria-labelledby="modalKardexLabel" aria-hidden="true">';
        html += '<div class="modal-dialog modal-xl">';
        html += '<div class="modal-content">';
        html += '<div class="modal-header bg-primary text-white">';
        html += '<h5 class="modal-title" id="modalKardexLabel"><i class="fas fa-list me-2"></i>Kardex del Producto: ' + codProducto + '</h5>';
        html += '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>';
        html += '</div>';
        html += '<div class="modal-body">';

        if (kardexData && kardexData.length > 0) {
            // Calcular stock final
            const stockFinal = kardexData[kardexData.length - 1]?.saldo_actual || 0;
            
            html += '<div class="alert alert-info mb-3">';
            html += '<i class="fas fa-info-circle me-2"></i>';
            html += '<strong>Stock Final:</strong> ' + stockFinal + ' unidades';
            html += '</div>';
            
            html += '<div class="table-responsive">';
            html += '<table class="table table-striped table-hover">';
            html += '<thead class="table-dark">';
            html += '<tr>';
            html += '<th>Fecha</th>';
            html += '<th>Observaciones</th>';
            html += '<th>Cantidad</th>';
            html += '<th>Tipo</th>';
            html += '<th>Precio</th>';
            html += '<th>Saldo</th>';
            html += '<th>Producto</th>';
            html += '</tr>';
            html += '</thead>';
            html += '<tbody>';

            kardexData.forEach(function(item) {
                const fecha = new Date(item.fecha).toLocaleDateString('es-ES');
                const cantidad = parseFloat(item.cant_productos);
                const saldo = parseFloat(item.saldo_actual || 0);
                const tipo = cantidad > 0 ? 'Entrada' : 'Salida';
                const cantidadClass = cantidad > 0 ? 'text-success' : 'text-danger';
                const cantidadIcon = cantidad > 0 ? '<i class="fas fa-arrow-down me-1"></i>' : '<i class="fas fa-arrow-up me-1"></i>';
                const tipoBadge = cantidad > 0 ? 'bg-success' : 'bg-danger';

                html += '<tr>';
                html += '<td>' + fecha + '</td>';
                html += '<td>' + (item.observaciones || 'N/A') + '</td>';
                html += '<td class="' + cantidadClass + '">' + cantidadIcon + Math.abs(cantidad) + '</td>';
                html += '<td><span class="badge ' + tipoBadge + '">' + tipo + '</span></td>';
                html += '<td>$' + parseFloat(item.precio_venta).toFixed(2) + '</td>';
                html += '<td><strong>' + saldo + '</strong></td>';
                html += '<td>' + (item.producto_nombre || 'N/A') + '</td>';
                html += '</tr>';
            });

            html += '</tbody>';
            html += '</table>';
            html += '</div>';
        } else {
            html += '<div class="alert alert-info text-center">';
            html += '<i class="fas fa-info-circle me-2"></i>';
            html += 'No hay movimientos registrados para este producto.';
            html += '</div>';
        }

        html += '</div>';
        html += '<div class="modal-footer">';
        html += '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        html += '</div>';

        // Remover modal anterior si existe
        $('#modalKardex').remove();

        // Agregar el nuevo modal al body
        $('body').append(html);

        // Mostrar el modal
        $('#modalKardex').modal('show');
    }

    function cargarFormularioEdicion(id) {
        $.ajax({
            url: '/Natys/index.php?url=movimiento&action=formularioEditar&id=' + id,
            type: 'GET',
            success: function(response) {
                $('#contenidoEditar').html(response);
                $('#modalEditar').modal('show');

                // Inicializar validaciones para el formulario de edición
                inicializarValidacionesEdicion();
            },
            error: function() {
                toastr.error('Error al cargar el formulario de edición');
            }
        });
    }

    function inicializarValidacionesEdicion() {
        $('#formEditarMovimiento [required]').on('blur', function() {
            validarCampo($(this));
        });

        // Cargar precio cuando se selecciona un producto en edición
        $('#edit_producto').on('change', function() {
            const codProducto = $(this).val();
            if (codProducto) {
                cargarPrecioProductoEditar(codProducto);
            } else {
                $('#edit_precio').val('');
            }
        });

        // Validaciones en tiempo real para el formulario de edición
        $(document).on('input change', '#edit_fecha', function() {
            const fecha = $(this).val().trim();
            if (fecha === '') {
                updateValidationState(this, false, 'Por favor seleccione una fecha');
                return;
            }
            const fechaSeleccionada = new Date(fecha);
            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);
            const isValid = fechaSeleccionada <= hoy && !isNaN(fechaSeleccionada.getTime());
            updateValidationState(this, isValid, 'La fecha debe ser válida y no puede ser futura');
        });

        $(document).on('change', '#edit_producto', function() {
            const producto = $(this).val();
            if (!producto || producto === '') {
                updateValidationState(this, false, 'Por favor seleccione un producto');
                return;
            }
            updateValidationState(this, true, '');
        });

        $(document).on('input', '#edit_cantidad', function() {
            const cantidad = $(this).val().trim();
            if (cantidad === '') {
                updateValidationState(this, false, 'Por favor ingrese la cantidad');
                return;
            }
            const numCantidad = parseFloat(cantidad);
            const isValid = !isNaN(numCantidad) && numCantidad > 0 && numCantidad <= 999999.99;
            updateValidationState(this, isValid, 'La cantidad debe ser un número positivo (máximo 999,999.99)');
        });

        $(document).on('input', '#edit_precio', function() {
            const precio = $(this).val().trim();
            if (precio === '') {
                updateValidationState(this, false, 'Por favor ingrese el precio');
                return;
            }
            const numPrecio = parseFloat(precio);
            const isValid = !isNaN(numPrecio) && numPrecio >= 0 && numPrecio <= 999999999.99;
            updateValidationState(this, isValid, 'El precio debe ser un número válido (máximo 999,999,999.99)');
        });

        $(document).on('input', '#edit_observaciones', function() {
            const observaciones = $(this).val().trim();
            if (observaciones !== '' && observaciones.length < 3) {
                updateValidationState(this, false, 'Las observaciones deben tener al menos 3 caracteres');
                return;
            }
            updateValidationState(this, true, '');
        });
    }

    function guardarMovimiento() {
        const formData = new FormData();
        formData.append('fecha', $('#fecha').val());
        formData.append('producto', $('#producto').val());
        formData.append('cantidad', $('#cantidad').val());
        formData.append('observaciones', $('#observaciones').val());
        formData.append('precio', $('#precio').val());

        $.ajax({
            url: '/Natys/index.php?url=movimiento&action=guardar',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message || 'Movimiento guardado exitosamente');
                    $('#modalNuevo').modal('hide');
                    $('#formNuevoMovimiento')[0].reset();
                    $('#formNuevoMovimiento').removeClass('was-validated');
                    $('#formNuevoMovimiento .form-control').removeClass('is-valid is-invalid');
                    dataTable.ajax.reload();
                } else {
                    toastr.error(response.message || 'Error al guardar el movimiento');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                toastr.error('Error al guardar el movimiento');
            }
        });
    }

    function eliminarMovimiento(id) {
        $.ajax({
            url: '/Natys/index.php?url=movimiento&action=eliminar',
            type: 'POST',
            data: { num_movimiento: id },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message || 'Movimiento eliminado exitosamente');
                    $('#modalEliminar').modal('hide');
                    dataTable.ajax.reload();
                } else {
                    toastr.error(response.message || 'Error al eliminar el movimiento');
                    $('#modalEliminar').modal('hide');
                    dataTable.ajax.reload();
                    toastr.info('La tabla se ha actualizado');
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                    return;
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                toastr.error('Error al eliminar el movimiento');
            }
        });
    }

    function actualizarMovimiento() {
        const formData = new FormData();
        formData.append('num_movimiento', $('#formEditarMovimiento input[name="num_movimiento"]').val());
        formData.append('fecha', $('#edit_fecha').val());
        formData.append('producto', $('#edit_producto').val());
        formData.append('cantidad', $('#edit_cantidad').val());
        formData.append('observaciones', $('#edit_observaciones').val());
        formData.append('precio', $('#edit_precio').val());

        $.ajax({
            url: '/Natys/index.php?url=movimiento&action=editar',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message || 'Movimiento actualizado exitosamente');
                    $('#modalEditar').modal('hide');
                    dataTable.ajax.reload();
                } else {
                    toastr.error(response.message || 'Error al actualizar el movimiento');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                toastr.error('Error al actualizar el movimiento');
            }
        });
    }

    // Inicialización de tooltips
    $(function () {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });

    // Limpiar formulario cuando se cierre el modal
    $('#modalNuevo').on('hidden.bs.modal', function() {
        $('#formNuevoMovimiento')[0].reset();
        $('#formNuevoMovimiento').removeClass('was-validated');
        $('#formNuevoMovimiento .form-control').removeClass('is-valid is-invalid');
    });
});