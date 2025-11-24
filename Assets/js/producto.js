$(document).ready(function() {
    // Configuración de Toastr
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut",
        "escapeHtml": true,
        "target": 'body',
        "containerId": 'toast-container',
        "closeHtml": '<button type="button" class="toast-close-button" role="button"><i class="fas fa-times"></i></button>',
        "tapToDismiss": true
    };
    
    // Forzar posición en caso de que algún otro estilo la esté sobrescribiendo
    const style = document.createElement('style');
    style.textContent = `
        #toast-container {
            position: fixed !important;
            top: 20px !important;
            right: 20px !important;
            left: auto !important;
            bottom: auto !important;
            z-index: 99999 !important;
        }
        .toast {
            margin-bottom: 15px !important;
        }
    `;
    document.head.appendChild(style);

    let currentView = 'active'; // 'active' or 'inactive'
    let searchTimeout;

    // Unidades de medida predefinidas
    const unidadesMedida = [
        { value: 'unidad', text: 'Unidad' },
        { value: 'kg', text: 'Kilogramo (kg)' },
        { value: 'g', text: 'Gramo (g)' },
        { value: 'lb', text: 'Libra (lb)' },
        { value: 'caja', text: 'Caja' },
        { value: 'paquete', text: 'Paquete' },
        { value: 'bolsa', text: 'Bolsa' },
        { value: 'par', text: 'Par' },
        { value: 'docena', text: 'Docena' },
    ];

    // Función para mostrar loading
    function showLoading() {
        $('.loading-spinner').show();
        $('#product-grid').hide();
    }

    // Función para ocultar loading
    function hideLoading() {
        $('.loading-spinner').hide();
        $('#product-grid').show();
    }

    // Función para cargar productos con filtros
    function loadProducts(view = null, searchTerm = '') {
        showLoading();
        
        // Si se proporciona una vista, actualizamos currentView
        if (view !== null) {
            currentView = view;
        }
        
        // Obtener valores de los filtros de precio
        const minPrice = $('#minPrice').val();
        const maxPrice = $('#maxPrice').val();
        
        // Determinar la URL correcta según la vista
        const url = (currentView === 'active') 
            ? 'index.php?url=producto&action=listar'
            : 'index.php?url=producto&action=listarEliminados';
        
        // Actualizar el botón según la vista actual
        if (currentView === 'active') {
            $('#btnVerEliminados').html('<i class="fas fa-trash-restore me-2"></i> Ver Inactivos')
                .removeClass('btn-info').addClass('btn-warning');
        } else {
            $('#btnVerEliminados').html('<i class="fas fa-box me-2"></i> Ver Activos')
                .removeClass('btn-warning').addClass('btn-info');
        }
        
        $.ajax({
            url: url,
            type: 'GET',
            data: { 
                search: searchTerm,
                min_price: minPrice,
                max_price: maxPrice
            },
            dataType: 'json',
            success: function(response) {
                try {
                    // Manejar la respuesta dependiendo de si es de listar o listarEliminados
                    let products = [];
                    
                    // Si es la respuesta de listarEliminados, la respuesta directa es el array
                    // Si es de listar, está en response.data
                    if (currentView === 'active' && response && response.data) {
                        products = response.data;
                    } else if (Array.isArray(response)) {
                        products = response;
                    } else if (response && response.data) {
                        products = response.data;
                    }
                    
                    if (!Array.isArray(products)) {
                        console.error('Formato de respuesta inesperado:', response);
                        throw new Error('Formato de respuesta inesperado del servidor');
                    }
                    
                    renderProducts(products);
                } catch (error) {
                    console.error('Error procesando la respuesta:', error, response);
                    toastr.error('Error al procesar los productos');
                    renderProducts([]);
                }
                hideLoading();
            },
            error: function(xhr, status, error) {
                console.error('Error loading products:', error, xhr.responseText);
                toastr.error('Error al cargar los productos');
                hideLoading();
                renderProducts([]);
            }
        });
    }

    // Función para renderizar productos
    function renderProducts(products) {
        const productGrid = $('#product-grid');
        productGrid.empty();
        
        if (!Array.isArray(products) || products.length === 0) {
            productGrid.html(`
                <div class="col-12 text-center py-5">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No hay productos ${currentView === 'active' ? 'activos' : 'sin stock'}</h4>
                    <p>${currentView === 'active' ? 'Comience agregando un nuevo producto.' : 'No hay productos sin stock.'}</p>
                </div>
            `);
            return;
        }
        
        products.forEach(product => {
            const imageUrl = product.imagen_url || 'http://localhost/Natys/Assets/img/crash.png';
            const stock = parseFloat(product.stock) || 0;
            const stockClass = stock <= 0 ? 'stock-out' : (stock <= 10 ? 'stock-low' : 'stock-ok');
            const stockText = stock <= 0 ? 'Sin stock' : (stock <= 10 ? `Stock bajo: ${stock}` : `Stock: ${stock}`);
            
            const productCard = `
                <div class="col-md-6 col-lg-4">
                    <div class="card product-card">
                        <span class="product-badge">
                            <span class="badge ${product.estado == 1 ? 'bg-success' : 'bg-secondary'}">
                                ${product.estado == 1 ? 'Activo' : 'Inactivo'}
                            </span>
                        </span>
                        <img src="${imageUrl}" 
                             class="product-image" 
                             alt="${product.nombre}"
                             onerror="this.onerror=null; this.src='http://localhost/Natys/Assets/img/crash.png'"
                             loading="lazy">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0">${product.nombre}</h5>
                                <span class="badge bg-primary">${product.cod_producto}</span>
                            </div>
                            <p class="card-text product-description">${product.descripcion || 'Sin descripción'}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="product-price">$${parseFloat(product.precio).toFixed(2)}</span>
                                <span class="text-muted">${product.unidad}</span>
                            </div>
                            <div class="stock-info ${stockClass}">
                                <i class="fas fa-boxes me-1"></i>${stockText}
                            </div>
                            <div class="action-buttons mt-3">
                                <button class="btn btn-sm btn-info view-details" data-product-id="${product.cod_producto}">
                                    <i class="fas fa-eye me-1"></i> Detalles
                                </button>
                                ${currentView === 'active' ? `
                                <button class="btn btn-sm btn-primary edit-product" data-product-id="${product.cod_producto}">
                                   <i class="fas fa-sync-alt"></i> Actualizar
                                </button>
                                <button class="btn btn-sm btn-danger delete-product" data-product-id="${product.cod_producto}" data-product-name="${product.nombre}">
                                    <i class="fas fa-trash-alt me-1"></i> Eliminar
                                </button>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            productGrid.append(productCard);
        });
    }

    // Función para mostrar detalles del producto en modal
    function showProductDetails(productId) {
        $.ajax({
            url: `index.php?url=producto&action=formActualizar&cod_producto=${productId}`,
            type: 'GET',
            success: function(response) {
                // Extraer datos del formulario para mostrar en el modal de detalles
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = response;
                
                const codProducto = tempDiv.querySelector('#cod_producto').value;
                const nombre = tempDiv.querySelector('#nombre').value;
                const precio = tempDiv.querySelector('#precio').value;
                const unidad = tempDiv.querySelector('#unidad').value;
                const descripcion = tempDiv.querySelector('#descripcion').value;
                
                // Buscar la imagen
                let imagenUrl = 'http://localhost/Natys/Assets/img/crash.png';
                const imgElement = tempDiv.querySelector('img');
                if (imgElement && imgElement.src) {
                    imagenUrl = imgElement.src;
                }
                
                // Obtener stock del producto
                $.ajax({
                    url: `index.php?url=producto&action=listar`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        const productos = response.data || [];
                        const producto = productos.find(p => p.cod_producto === productId);
                        const stock = producto ? producto.stock || 0 : 0;
                        
                        $('#modal-product-image').attr('src', imagenUrl);
                        $('#modal-product-name').text(nombre);
                        $('#modal-product-price').text('$' + parseFloat(precio).toFixed(2));
                        $('#modal-product-status').text('Activo');
                        $('#modal-product-code').text('Código: ' + codProducto);
                        $('#modal-product-unit').text('Unidad: ' + unidad);
                        $('#modal-product-stock').html(`<i class="fas fa-boxes me-1"></i>Stock disponible: <strong>${stock}</strong>`);
                        $('#modal-product-description').html(descripcion || '<em>Sin descripción</em>');
                        
                        const productModal = new bootstrap.Modal(document.getElementById('productDetailModal'));
                        productModal.show();
                    }
                });
            },
            error: function(xhr, status, error) {
                console.error('Error loading product details:', error);
                toastr.error('Error al cargar los detalles del producto');
            }
        });
    }

    // Función para cargar formulario de producto
    function loadProductForm(action, productId = '') {
        const url = productId 
            ? `index.php?url=producto&action=${action}&cod_producto=${productId}`
            : `index.php?url=producto&action=${action}`;
        
        $('#form-container').html('<div class="text-center p-4"><div class="spinner-border text-primary" role="status"></div></div>');
        
        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                $('#form-container').html(response);
                
                // Transformar unidad de medida a select
                transformarUnidadSelect();
                
                // Configurar validaciones en tiempo real
                configurarValidaciones();
                
                // Configurar el envío del formulario
                $('#formProducto').off('submit').on('submit', function(e) {
                    e.preventDefault();
                    
                    if (this.checkValidity()) {
                        const formData = new FormData(this);
                        const actionType = action === 'formNuevo' ? 'guardar' : 'actualizar';
                        
                        $.ajax({
                            url: `index.php?url=producto&action=${actionType}`,
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    toastr.success(response.message);
                                    $('#productFormModal').modal('hide');
                                    loadProducts();
                                } else {
                                    toastr.error(response.message);
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Error saving product:', error);
                                toastr.error('Error al guardar el producto');
                            }
                        });
                    } else {
                        this.classList.add('was-validated');
                    }
                });
            },
            error: function(xhr, status, error) {
                console.error('Error loading form:', error);
                toastr.error('Error al cargar el formulario');
            }
        });
    }

    // Función para transformar unidad de medida a select
    function transformarUnidadSelect() {
        const unidadInput = $('#unidad');
        if (unidadInput.length && unidadInput.prop('type') === 'text') {
            const currentValue = unidadInput.val();
            
            // Crear select
            const selectHtml = `
                <select class="form-select" id="unidad" name="unidad" required>
                    <option value="">Seleccione una unidad...</option>
                    ${unidadesMedida.map(unidad => 
                        `<option value="${unidad.value}" ${unidad.value === currentValue ? 'selected' : ''}>${unidad.text}</option>`
                    ).join('')}
                    <option value="otro">Otra unidad...</option>
                </select>
                <input type="text" class="form-control mt-2 d-none" id="otraUnidad" name="otra_unidad" placeholder="Especifique otra unidad">
            `;
            
            unidadInput.replaceWith(selectHtml);
            
            // Manejar selección de "otro"
            $('#unidad').on('change', function() {
                if ($(this).val() === 'otro') {
                    $('#otraUnidad').removeClass('d-none').attr('required', true);
                } else {
                    $('#otraUnidad').addClass('d-none').removeAttr('required');
                }
            });
            
            // Si el valor actual no está en las opciones, mostrar campo "otro"
            const unidadExists = unidadesMedida.some(u => u.value === currentValue);
            if (currentValue && !unidadExists) {
                $('#unidad').val('otro');
                $('#otraUnidad').val(currentValue).removeClass('d-none').attr('required', true);
            }
        }
    }

    // Función para verificar si hay campos vacíos
    function tieneCamposVacios(form) {
        let tieneVacios = false;
        let camposFaltantes = [];

        $(form).find('input[required], textarea[required], select[required]').each(function() {
            const $field = $(this);
            const fieldName = $field.attr('name') || 'campo';
            const fieldLabel = $('label[for="' + $field.attr('id') + '"]').text().replace('*', '').trim() || fieldName;

            if ($field.val() === '' || $field.val() === null) {
                // Marcar como inválido
                $field.addClass('is-invalid');
                $field.siblings('.invalid-feedback').html('<i class="fas fa-exclamation-circle me-2"></i>Este campo es obligatorio');

                // Asegurarse de que el mensaje de error sea visible
                $field.siblings('.invalid-feedback').css({
                    'display': 'flex',
                    'align-items': 'center'
                });

                // Agregar a la lista de campos faltantes
                camposFaltantes.push(fieldLabel);

                // Desplazarse al primer campo con error
                if (!tieneVacios) {
                    $('html, body').animate({
                        scrollTop: $field.offset().top - 100
                    }, 500);
                }

                tieneVacios = true;
            }
        });

        // Mostrar notificación si hay campos vacíos
        if (tieneVacios) {
            const mensaje = camposFaltantes.length > 1
                ? 'Por favor complete los siguientes campos obligatorios: ' + camposFaltantes.join(', ')
                : 'El campo ' + camposFaltantes[0] + ' es obligatorio';

            // Mostrar notificación con SweetAlert2
            Swal.fire({
                icon: 'warning',
                title: 'Campos obligatorios',
                text: mensaje,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });

            // También mostrar notificación de toastr para compatibilidad
            toastr.warning(mensaje, 'Campos obligatorios');
        }

        return tieneVacios;
    }

    // Función para configurar validación de formularios
    function configurarValidaciones() {
        // Configurar validación al enviar el formulario
        $('form').each(function() {
            $(this).on('submit', function(e) {
                // Primero, quitar clases de validación previas
                $(this).find('.is-invalid').removeClass('is-invalid');

                // Verificar campos vacíos
                if (tieneCamposVacios(this)) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }

                // Si llegamos aquí, el formulario es válido
                return true;
            });

            // Validación en tiempo real para los campos
            $(this).find('input, textarea, select').each(function() {
                // Validar cuando cambia el valor
                $(this).on('change', function() {
                    if (this.checkValidity()) {
                        $(this).removeClass('is-invalid').addClass('is-valid');
                        $(this).siblings('.invalid-feedback').hide();
                        $(this).siblings('.valid-feedback').remove();
                        $(this).after('<div class="valid-feedback"><i class="fas fa-check-circle me-2"></i>¡Campo válido!</div>');
                    } else {
                        $(this).removeClass('is-valid').addClass('is-invalid');
                        $(this).siblings('.valid-feedback').remove();
                    }
                });

                // También validar cuando el campo pierde el foco
                $(this).on('blur', function() {
                    if (this.checkValidity()) {
                        $(this).removeClass('is-invalid').addClass('is-valid');
                        $(this).siblings('.invalid-feedback').hide();
                        $(this).siblings('.valid-feedback').remove();
                        $(this).after('<div class="valid-feedback"><i class="fas fa-check-circle me-2"></i>¡Campo válido!</div>');
                    } else if ($(this).val() !== '') {
                        $(this).removeClass('is-valid').addClass('is-invalid');
                        $(this).siblings('.valid-feedback').remove();
                    }
                });
            });
        });

        // Validación en tiempo real para código de producto (letras y números, mínimo 5 caracteres)
        $(document).on('input', '#cod_producto', function() {
            const codigo = $(this).val();
            const regex = /^[a-zA-Z0-9]{5,20}$/;
            const isValid = regex.test(codigo);

            updateValidationState(this, isValid, 'El código debe contener solo letras y números (mínimo 5 caracteres)');
        });

        // Validación en tiempo real para nombre (solo letras y espacios, mínimo 3 caracteres)
        $(document).on('input', '#nombre', function() {
            const nombre = $(this).val().trim();
            const regex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,100}$/;
            const isValid = regex.test(nombre);

            updateValidationState(this, isValid, 'El nombre debe contener solo letras y espacios (mínimo 3 caracteres)');
        });

        // Validación en tiempo real para precio (número positivo)
        $(document).on('input', '#precio', function() {
            const precio = parseFloat($(this).val());
            const isValid = !isNaN(precio) && precio > 0;

            updateValidationState(this, isValid, 'Por favor ingrese un precio válido mayor a cero');
        });

        // Validación en tiempo real para unidad de medida
        $(document).on('change', '#unidad', function() {
            const unidad = $(this).val();
            const isValid = unidad !== '';

            updateValidationState(this, isValid, 'Por favor ingrese la unidad de medida');
        });

        // Validación en tiempo real para imagen (tipo y tamaño, opcional)
        $('#imagen').on('change', function() {
            const file = this.files[0];
            const maxSize = 3 * 1024 * 1024; // 3MB
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

            if (!file) {
                $(this).removeClass('is-valid is-invalid');
                $(this).siblings('.valid-feedback, .invalid-feedback').hide();
                return;
            }

            const isValid = allowedTypes.includes(file.type) && file.size <= maxSize;
            let errorMsg = 'Formato de archivo no válido. ';
            if (!allowedTypes.includes(file.type)) {
                errorMsg += 'Solo se permiten imágenes JPG, PNG o GIF. ';
            }
            if (file.size > maxSize) {
                errorMsg += 'El tamaño máximo es 3MB.';
            }

            updateValidationState(this, isValid, errorMsg);
        });

        // Validación en tiempo real para descripción (opcional, pero si se ingresa debe tener al menos 5 caracteres)
        $(document).on('input', '#descripcion', function() {
            const descripcion = $(this).val().trim();

            if (descripcion === '') {
                $(this).removeClass('is-valid is-invalid');
                $(this).siblings('.valid-feedback, .invalid-feedback').hide();
                return;
            }

            const isValid = descripcion.length >= 5;
            updateValidationState(this, isValid, 'La descripción debe tener al menos 5 caracteres');
        });
    }

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

    // Función para mostrar confirmación de eliminación
    function confirmDelete(productId, productName) {
        $('#delete-product-id').val(productId);
        $('#delete-product-name').text(productName);
        
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        deleteModal.show();
    }

    // Función para aplicar todos los filtros
    function applyFilters() {
        const searchTerm = $('#searchProduct').val().trim();
        loadProducts(currentView, searchTerm);
    }

    // Validar que el precio mínimo no sea mayor al máximo
    function validatePriceRange() {
        const min = parseFloat($('#minPrice').val()) || 0;
        const max = parseFloat($('#maxPrice').val()) || Infinity;
        
        if (min > max) {
            toastr.warning('El precio mínimo no puede ser mayor al precio máximo');
            return false;
        }
        return true;
    }

    // Función para generar reportes
    function generarReporte(action) {
        const url = `index.php?url=producto&action=${action}`;
        
        // Mostrar mensaje de generación
        toastr.info('Generando reporte, por favor espere...', 'Generando PDF', {
            timeOut: 3000,
            extendedTimeOut: 1000
        });
        
        // Abrir en nueva pestaña después de un breve delay para que toastr se muestre
        setTimeout(() => {
            window.open(url, '_blank');
        }, 500);
    }

    // Función para cargar modal con todos los reportes
    function cargarModalReportes() {
        $.ajax({
            url: 'index.php?url=producto&action=reportes',
            type: 'GET',
            success: function(response) {
                // Crear modal dinámicamente si no existe
                if ($('#reportesModal').length === 0) {
                    $('body').append(`
                        <div class="modal fade" id="reportesModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title">
                                            <i class="fas fa-file-pdf me-2"></i>Generar Reportes
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body" id="reportesModalBody">
                                        ${response}
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            <i class="fas fa-times me-2"></i>Cerrar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);
                } else {
                    $('#reportesModalBody').html(response);
                }
                
                const reportesModal = new bootstrap.Modal(document.getElementById('reportesModal'));
                reportesModal.show();
            },
            error: function(xhr, status, error) {
                console.error('Error loading reports modal:', error);
                toastr.error('Error al cargar los reportes');
            }
        });
    }

    // Event handlers para reportes
    $(document).on('click', '.reporte-item', function(e) {
        e.preventDefault();
        const action = $(this).data('action');
        generarReporte(action);
        
        // Cerrar el dropdown
        const dropdown = bootstrap.Dropdown.getInstance($('#dropdownReportes')[0]);
        if (dropdown) {
            dropdown.hide();
        }
    });

    $('#btnTodosReportes').on('click', function(e) {
        e.preventDefault();
        cargarModalReportes();
        
        // Cerrar el dropdown
        const dropdown = bootstrap.Dropdown.getInstance($('#dropdownReportes')[0]);
        if (dropdown) {
            dropdown.hide();
        }
    });

    // Inicializar tooltips para los items del dropdown de reportes
    $(document).on('mouseenter', '.reporte-item', function() {
        const action = $(this).data('action');
        let title = '';
        
        switch(action) {
            case 'inventario':
                title = 'Reporte completo de inventario con valoración';
                break;
            case 'productos':
                title = 'Catálogo completo de productos activos';
                break;
            case 'bajo_stock':
                title = 'Productos que requieren reabastecimiento';
                break;
            case 'fuera_stock':
                title = 'Productos inactivos sin stock disponible';
                break;
        }
        
        $(this).attr('data-bs-toggle', 'tooltip');
        $(this).attr('data-bs-placement', 'left');
        $(this).attr('title', title);
        
        // Inicializar tooltip
        new bootstrap.Tooltip(this);
    });

    // Event handlers existentes
    $(document).on('click', '.view-details', function() {
        const productId = $(this).data('product-id');
        showProductDetails(productId);
    });

    $(document).on('click', '.edit-product', function() {
        const productId = $(this).data('product-id');
        $('#productFormModalLabel').html('<i class="fas fa-sync-alt"></i> Editar Producto');
        loadProductForm('formActualizar', productId);
        $('#productFormModal').modal('show');
    });

    $(document).on('click', '.delete-product', function() {
        const productId = $(this).data('product-id');
        const productName = $(this).data('product-name');
        confirmDelete(productId, productName);
    });

    $('#btnNuevoProducto').click(function() {
        $('#productFormModalLabel').html('<i class="fas fa-plus-circle me-2"></i> Registrar Producto');
        loadProductForm('formNuevo');
        $('#productFormModal').modal('show');
    });

    $('#btnVerEliminados').click(function() {
        // Cambiar la vista actual y cargar los productos correspondientes
        const newView = currentView === 'active' ? 'inactive' : 'active';
        loadProducts(newView);
    });

    $('#btnConfirmDelete').click(function() {
        const productId = $('#delete-product-id').val();
        
        $.ajax({
            url: 'index.php?url=producto&action=eliminar',
            type: 'POST',
            data: { codigo: productId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#deleteConfirmModal').modal('hide');
                    loadProducts();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error deleting product:', error);
                toastr.error('Error al eliminar el producto');
            }
        });
    });

    // Búsqueda de productos
    $('#searchProduct').on('input', function() {
        const searchTerm = $(this).val().trim();
        
        // Mostrar/ocultar botón de limpiar búsqueda
        if (searchTerm.length > 0) {
            $('#btnClearSearch').show();
        } else {
            $('#btnClearSearch').hide();
        }
        
        // Usar debounce para evitar múltiples llamadas durante la escritura
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            applyFilters();
        }, 500);
    });
    
    // Limpiar búsqueda
    $('#btnClearSearch').on('click', function() {
        $('#searchProduct').val('');
        $(this).hide();
        applyFilters();
    });
    
    // Limpiar filtros de precio
    $('#btnClearPriceFilter').on('click', function() {
        $('#minPrice').val('');
        $('#maxPrice').val('');
        applyFilters();
    });
    
    // Aplicar filtros al hacer clic en el botón
    $('#btnApplyFilters').on('click', function() {
        if (validatePriceRange()) {
            applyFilters();
        }
    });
    
    // Aplicar filtros al presionar Enter en los campos de precio
    $('#minPrice, #maxPrice').on('keyup', function(e) {
        if (e.key === 'Enter') {
            if (validatePriceRange()) {
                applyFilters();
            }
        }
    });

    // Cargar productos al iniciar
    loadProducts();
});