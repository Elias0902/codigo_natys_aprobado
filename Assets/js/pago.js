/**
 * Gestión de Pagos - Natys
 * JavaScript separado para funcionalidades de pago
 */

class PagoManager {
    constructor() {
        this.table = null;
        this.init();
    }

    init() {
        this.initDataTable();
        this.initEventListeners();
        this.initValidation();
        this.setDefaultDates();
        this.initToastr();
        this.initMobileFilters();
        this.cargarMetodosParaReporte();
    }

    initToastr() {
        // Configuración CORREGIDA de Toastr
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": true,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "4000", // 4 segundos
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut",
            "tapToDismiss": true
        };

        // Limpiar toasts antiguos al inicializar
        toastr.clear();

        // Override para asegurar que los toasts se cierren
        $(document).on('DOMNodeInserted', function(e) {
            if ($(e.target).hasClass('toast')) {
                const $toast = $(e.target);
                
                // Configurar timeout para auto-eliminación
                setTimeout(() => {
                    if ($toast.is(':visible')) {
                        $toast.fadeOut(500, function() {
                            $(this).remove();
                        });
                    }
                }, 4000);
            }
        });
    }

    initDataTable() {
        this.table = $('#pagos').DataTable({
            "responsive": true,
            "pageLength": 15,
            "lengthChange": false,
            "dom": '<"d-flex justify-content-between align-items-center mb-2"<"d-flex"f><"ms-2"l>>rt<"d-flex justify-content-between align-items-center mt-2"<"d-flex"i><"d-flex"p>>',
            "language": {
                "search": "Buscar:",
                "lengthMenu": "Mostrar _MENU_ registros por página",
                "zeroRecords": "No se encontraron registros",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                "infoFiltered": "(filtrado de _MAX_ registros totales)",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                },
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "emptyTable": "No hay datos disponibles"
            },
            "ajax": {
                "url": "index.php?url=pago&action=listar",
                "dataSrc": "data",
                "error": (xhr, error, thrown) => {
                    console.error("Error cargando datos:", error, thrown);
                    this.showToast("Error al cargar los datos de pagos", "error");
                }
            },
            "columns": [
                {
                    "data": "id_pago",
                    "className": 'fw-bold'
                },
                {
                    "data": "banco"
                },
                {
                    "data": "referencia"
                },
                {
                    "data": "fecha",
                    "render": (data) => data ? new Date(data).toLocaleDateString('es-ES') : ''
                },
                {
                    "data": "monto",
                    "render": (data) => '$' + parseFloat(data).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')
                },
                {
                    "data": "metodo_pago",
                    "render": (data) => {
                        if (!data) return '';
                        const icon = data.toLowerCase().includes('efectivo') ? 'money-bill-wave' : 'credit-card';
                        return `<i class="fas fa-${icon} me-1"></i>${data}`;
                    }
                },
                {
                    "data": "estado",
                    "render": (data) => data == 1
                        ? '<span class="badge bg-success">Activo</span>'
                        : '<span class="badge bg-danger">Inactivo</span>'
                },
                {
                    "data": null,
                    "orderable": false,
                    "render": (data, type, row) => this.renderActionButtons(row)
                }
            ],
            "order": [[0, "desc"]]
        });
    }

    renderActionButtons(row) {
        let buttons = '';
        
        buttons += `<button class="btn btn-sm btn-danger btn-actions btn-detalles" data-id="${row.id_pago}" title="Ver Detalles">`;
        buttons += `<i class="fas fa-eye"></i></button>`;
        
        if (row.estado == 1) {
            buttons += `<button class="btn btn-sm btn-danger btn-actions btn-editar" data-id="${row.id_pago}" title="Actualizar">`;
            buttons += `<i class="fas fa-sync-alt"></i></button>`;
            buttons += `<button class="btn btn-sm btn-danger btn-actions btn-eliminar" data-id="${row.id_pago}" title="Eliminar">`;
            buttons += `<i class="fas fa-trash"></i></button>`;
        } else {
            buttons += `<button class="btn btn-sm btn-actions btn-restaurar" data-id="${row.id_pago}" title="Restaurar" style="background: linear-gradient(135deg, #d31111 0%, #c0392b 100%) !important; border: none !important; box-shadow: none !important; color: white !important;">`;
            buttons += `<i class="fas fa-undo"></i></button>`;
        }
        return buttons;
    }

    initMobileFilters() {
        // Inicializar filtros para vista móvil
        $('.filtro-btn-mobile').on('click', (e) => {
            const filtro = $(e.target).data('filtro');
            this.aplicarFiltroMobile(filtro);
        });
    }

    aplicarFiltroMobile(filtro) {
        // Actualizar botones activos
        $('.filtro-btn-mobile').removeClass('active');
        $(`.filtro-btn-mobile[data-filtro="${filtro}"]`).addClass('active');
        
        // Recargar datos con el filtro
        this.cargarPagosMobile(filtro);
        this.showToast(`Filtro aplicado: ${this.getFiltroNombre(filtro)}`, "info");
    }

    cargarPagosMobile(filtro = 'todos') {
        $.ajax({
            url: `index.php?url=pago&action=listar&filtro=${filtro}`,
            type: 'GET',
            dataType: 'json',
            success: (response) => {
                if (response.data) {
                    this.renderPagosMobile(response.data);
                }
            },
            error: () => {
                this.showToast('Error al cargar los pagos', 'error');
            }
        });
    }

    renderPagosMobile(pagos) {
        const $container = $('.pagos-mobile-list');
        $container.empty();

        if (pagos.length === 0) {
            $container.html('<div class="alert alert-info text-center">No hay pagos para mostrar</div>');
            return;
        }

        pagos.forEach(pago => {
            const pagoCard = `
                <div class="pago-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold">#${pago.id_pago}</span>
                        <span class="badge bg-${pago.estado == 1 ? 'success' : 'danger'}">
                            ${pago.estado == 1 ? 'Activo' : 'Inactivo'}
                        </span>
                    </div>
                    <div><strong>Banco:</strong> ${pago.banco || 'N/A'}</div>
                    <div><strong>Referencia:</strong> ${pago.referencia || 'N/A'}</div>
                    <div><strong>Fecha:</strong> ${pago.fecha ? new Date(pago.fecha).toLocaleDateString('es-ES') : ''}</div>
                    <div><strong>Monto:</strong> $${parseFloat(pago.monto).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')}</div>
                    <div><strong>Método:</strong> ${pago.metodo_pago}</div>
                    <div class="btn-group w-100 mt-2">
                        <button class="btn btn-sm btn-danger btn-detalles" data-id="${pago.id_pago}">
                            <i class="fas fa-eye"></i>
                        </button>
                        ${pago.estado == 1 ? `
                            <button class="btn btn-sm btn-danger btn-editar" data-id="${pago.id_pago}">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <button class="btn btn-sm btn-danger btn-eliminar" data-id="${pago.id_pago}">
                                <i class="fas fa-trash"></i>
                            </button>
                        ` : `
                            <button class="btn btn-sm btn-restaurar" data-id="${pago.id_pago}" style="background: linear-gradient(135deg, #d31111 0%, #c0392b 100%) !important; border: none !important; box-shadow: none !important; color: white !important;">
                                <i class="fas fa-undo"></i>
                            </button>
                        `}
                    </div>
                </div>
            `;
            $container.append(pagoCard);
        });
    }

    initEventListeners() {
        // Filtros desktop
        $('.filtro-btn').on('click', (e) => {
            const filtro = $(e.target).data('filtro');
            $('.filtro-btn').removeClass('active');
            $(e.target).addClass('active');
            this.table.ajax.url(`index.php?url=pago&action=listar&filtro=${filtro}`).load();
            this.showToast(`Filtro aplicado: ${this.getFiltroNombre(filtro)}`, "info");
        });

        // Filtros mobile
        $('.filtro-btn-mobile').on('click', (e) => {
            const filtro = $(e.target).data('filtro');
            this.aplicarFiltroMobile(filtro);
        });

        // Nuevo pago
        $('#btnNuevoPago').on('click', () => {
            $('#tbody-pedidos-pendientes').html('<tr><td colspan="6" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando pedidos pendientes...</td></tr>');
            $('#modalSeleccionarPedido').modal('show');
            this.cargarPedidosPendientes();
        });

        // Seleccionar pedido
        $(document).on('click', '.btn-seleccionar-pedido', (e) => {
            this.seleccionarPedido(e.target);
        });

        // Método de pago change - CORREGIDO
        $(document).on('change', '#cod_metodo, #editar_cod_metodo', (e) => {
            this.actualizarCamposPorMetodo(e.target);
        });

        // Form submit
        $('#formPago').on('submit', (e) => this.guardarPago(e));
        $('#formEditarPago').on('submit', (e) => this.actualizarPago(e));

        // Cerrar modal al cancelar
        $('#cancelarPago').on('click', () => {
            $('#modalNuevoPago').modal('hide');
            this.showToast("Operación cancelada", "warning");
        });

        $('#modalNuevoPago .btn-close').on('click', () => {
            $('#modalNuevoPago').modal('hide');
            this.showToast("Operación cancelada", "warning");
        });

        // Acciones
        $(document).on('click', '.btn-detalles', (e) => this.mostrarDetalles(e));
        $(document).on('click', '.btn-editar', (e) => this.editarPago(e));
        $(document).on('click', '.btn-eliminar', (e) => this.eliminarPago(e));
        $(document).on('click', '.btn-restaurar', (e) => this.restaurarPago(e));

        // Manejar clic en elementos del menú desplegable de reportes
        $(document).on('click', '.dropdown-item[data-action]', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const action = $(e.currentTarget).data('action');
            if (action) {
                this.generarReporteDropdown(action);
            }
            const dropdown = bootstrap.Dropdown.getInstance(document.getElementById('dropdownReportes'));
            if (dropdown) dropdown.hide();
        });

        $('#btnGenerarReporteFechas').on('click', () => this.generarReporteFechas());
        $('#btnGenerarComprobante').on('click', () => this.generarComprobante());

        // Evento para abrir el modal de reporte de pagos
        $('#btnReportePagos').on('click', function() {
            $('#modalReportePagos').modal('show');
        });

        // Evento para generar el reporte filtrado
        $('#btnGenerarReportePagos').on('click', function(e) {
            e.preventDefault();
            const id = ($('#filtroIdPago').val() || '').trim() || 'all';
            const banco = ($('#filtroBanco').val() || '').trim() || 'all';
            const referencia = ($('#filtroReferencia').val() || '').trim() || 'all';
            const fecha = ($('#filtroFecha').val() || '').trim() || 'all';
            const monto = ($('#filtroMonto').val() || '').trim() || 'all';
            const estado = ($('#filtroEstado').val() || '').trim() || 'all';

            const url = `index.php?url=pago&action=reporte_html&id=${id}&banco=${banco}&referencia=${referencia}&fecha=${fecha}&monto=${monto}&estado=${estado}`;

            window.open(url, '_blank');
            $('#modalReportePagos').modal('hide');
        });

        // Validación en tiempo real
        this.initRealTimeValidation();
        
        // Evento cuando se abre el modal de edición - NUEVO: Forzar actualización de campos
        $('#modalEditarPago').on('show.bs.modal', () => {
            // Pequeño delay para asegurar que los campos estén cargados
            setTimeout(() => {
                this.actualizarCamposPorMetodo($('#editar_cod_metodo')[0]);
            }, 100);
        });

        // Evento cuando se abre el modal de comprobante - limpiar formulario
        $('#modalComprobante').on('show.bs.modal', () => {
            $('#formComprobante')[0].reset();
            $('#id_pago_comprobante').removeClass('is-invalid');
        });
    }

    getFiltroNombre(filtro) {
        const filtros = {
            'todos': 'Todos los Pagos',
            'efectivo': 'Pagos en Efectivo',
            'otros': 'Otros Métodos'
        };
        return filtros[filtro] || filtro;
    }

    initValidation() {
        const forms = document.querySelectorAll('.needs-validation');
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                    this.showToast("Por favor complete todos los campos requeridos correctamente", "warning");
                }
                form.classList.add('was-validated');
            }, false);
        });
    }

    initRealTimeValidation() {
        // Validación en tiempo real para formulario de pago
        $('#referencia').on('input', () => this.validarReferencia());
        $('#monto').on('input', () => this.validarMonto());
        $('#fecha_pago').on('change', () => this.validarFecha());
        $('#banco').on('change', () => this.validarBanco());

        // Validación en tiempo real para formulario de edición
        $('#editar_referencia').on('input', () => this.validarEditarReferencia());
        $('#editar_monto').on('input', () => this.validarEditarMonto());
        $('#editar_fecha').on('change', () => this.validarEditarFecha());
        $('#editar_banco').on('change', () => this.validarEditarBanco());
    }

    // Validaciones en tiempo real para nuevo pago
    validarReferencia() {
        const referencia = $('#referencia').val();
        const metodo = $('#cod_metodo').val();
        const esEfectivo = this.esMetodoEfectivo(metodo);

        if (!esEfectivo && (!referencia || referencia.trim() === '')) {
            this.mostrarError('#referencia', 'La referencia es requerida para este método de pago');
            return false;
        } else if (!esEfectivo && referencia.length < 3) {
            this.mostrarError('#referencia', 'La referencia debe tener al menos 3 caracteres');
            return false;
        } else {
            this.mostrarExito('#referencia');
            return true;
        }
    }

    validarMonto() {
        const monto = parseFloat($('#monto').val());
        if (isNaN(monto) || monto <= 0) {
            this.mostrarError('#monto', 'El monto debe ser mayor a cero');
            return false;
        } else {
            this.mostrarExito('#monto');
            return true;
        }
    }

    validarFecha() {
        const fecha = $('#fecha_pago').val();
        if (!fecha) {
            this.mostrarError('#fecha_pago', 'La fecha del pago es requerida');
            return false;
        } else if (new Date(fecha) > new Date()) {
            this.mostrarError('#fecha_pago', 'La fecha no puede ser futura');
            return false;
        } else {
            this.mostrarExito('#fecha_pago');
            return true;
        }
    }

    validarBanco() {
        const banco = $('#banco').val();
        const metodo = $('#cod_metodo').val();
        const esEfectivo = this.esMetodoEfectivo(metodo);

        if (!esEfectivo && (!banco || banco.trim() === '')) {
            this.mostrarError('#banco', 'El banco es requerido para este método de pago');
            return false;
        } else {
            this.mostrarExito('#banco');
            return true;
        }
    }

    // Validaciones en tiempo real para edición
    validarEditarReferencia() {
        const referencia = $('#editar_referencia').val();
        const metodo = $('#editar_cod_metodo').val();
        const esEfectivo = this.esMetodoEfectivo(metodo);
        
        if (!esEfectivo && (!referencia || referencia.trim() === '')) {
            this.mostrarError('#editar_referencia', 'La referencia es requerida para este método de pago');
            return false;
        } else {
            this.mostrarExito('#editar_referencia');
            return true;
        }
    }

    validarEditarMonto() {
        const monto = parseFloat($('#editar_monto').val());
        if (isNaN(monto) || monto <= 0) {
            this.mostrarError('#editar_monto', 'El monto debe ser mayor a cero');
            return false;
        } else {
            this.mostrarExito('#editar_monto');
            return true;
        }
    }

    validarEditarFecha() {
        const fecha = $('#editar_fecha').val();
        if (!fecha) {
            this.mostrarError('#editar_fecha', 'La fecha del pago es requerida');
            return false;
        } else {
            this.mostrarExito('#editar_fecha');
            return true;
        }
    }

    validarEditarBanco() {
        const banco = $('#editar_banco').val();
        const metodo = $('#editar_cod_metodo').val();
        const esEfectivo = this.esMetodoEfectivo(metodo);
        
        if (!esEfectivo && (!banco || banco.trim() === '')) {
            this.mostrarError('#editar_banco', 'El banco es requerido para este método de pago');
            return false;
        } else {
            this.mostrarExito('#editar_banco');
            return true;
        }
    }

    mostrarError(selector, mensaje) {
        const element = $(selector);
        element.removeClass('is-valid').addClass('is-invalid');
        element.next('.invalid-feedback').html('<i class="fas fa-exclamation-circle me-2"></i>' + mensaje).css({
            'display': 'flex',
            'align-items': 'center',
            'border': '1px solid #dc3545',
            'border-radius': '0.375rem',
            'padding': '0.5rem 0.75rem',
            'background-color': '#f8d7da',
            'color': '#721c24',
            'font-size': '0.875rem',
            'margin-top': '0.25rem'
        }).show();
        element.nextAll('.valid-feedback').hide();
    }

    mostrarExito(selector) {
        const element = $(selector);
        element.removeClass('is-invalid').addClass('is-valid');
        element.next('.invalid-feedback').hide();
        element.nextAll('.valid-feedback').html('<i class="fas fa-check-circle me-2"></i>¡Campo válido!').show();
    }

    esMetodoEfectivo(metodo) {
        return metodo === 'EFECTIVO' || metodo === 'ZELLE';
    }

    seleccionarPedido(button) {
        const $btn = $(button);
        const idPedido = $btn.data('id-pedido');
        const total = parseFloat($btn.data('total')) || 0;
        const cliente = $btn.data('cliente');
        const fecha = $btn.data('fecha');

        $('#id_pedido').val(idPedido);
        $('#pedido-numero').text(idPedido);
        $('#pedido-cliente').text(cliente);
        $('#pedido-fecha').text(fecha);
        $('#pedido-total').text(total.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
        $('#monto').val(total);

        this.cargarMetodosPago('cod_metodo');

        $('#modalSeleccionarPedido').one('hidden.bs.modal', function() {
            $('#modalNuevoPago').modal('show');
        });
        $('#modalSeleccionarPedido').modal('hide');
        
        this.showToast(`Pedido #${idPedido} seleccionado`, "success");
    }

    cargarMetodosPago(selectId, metodoSeleccionado = null) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: 'index.php?url=pago&action=listarMetodos',
                type: 'GET',
                dataType: 'json',
                success: (response) => {
                    if (response.success) {
                        const $select = $(`#${selectId}`);
                        $select.empty().append('<option value="" disabled selected>Seleccione un método de pago</option>');

                        response.data.forEach((metodo) => {
                            const esSeleccionado = metodoSeleccionado && metodoSeleccionado === metodo.codigo;

                            $select.append(
                                $('<option>', {
                                    value: metodo.codigo,
                                    text: metodo.detalle,
                                    selected: esSeleccionado
                                })
                            );
                        });
                        
                        // Resolver la promesa cuando termine
                        resolve(response);
                    } else {
                        this.showToast('Error al cargar métodos de pago', 'error');
                        reject(response);
                    }
                },
                error: () => {
                    this.showToast('Error de conexión al cargar métodos de pago', 'error');
                    reject();
                }
            });
        });
    }

    actualizarCamposPorMetodo(selectElement) {
        const $select = $(selectElement);
        const metodo = $select.val();
        const prefix = $select.attr('id').startsWith('editar_') ? 'editar_' : '';
        const $bancoField = $(`.${prefix}banco-field`);
        const $referenciaField = $(`.${prefix}referencia-field`);
        const $bancoInput = $(`#${prefix}banco`);
        const $referenciaInput = $(`#${prefix}referencia`);

        console.log('Actualizando campos para método:', metodo, 'Prefix:', prefix); // Debug

        const esEfectivo = this.esMetodoEfectivo(metodo);

        if (esEfectivo) {
            $bancoField.hide();
            $referenciaField.hide();
            $bancoInput.prop('required', false);
            $referenciaInput.prop('required', false);
            
            // Establecer valores fijos para efectivo
            if (prefix === 'editar_') {
                $bancoInput.val('N/A');
                $referenciaInput.val('Efectivo');
            } else {
                $bancoInput.val('Divisa');
                $referenciaInput.val('');
            }

            // Limpiar validaciones
            $bancoInput.removeClass('is-invalid is-valid');
            $referenciaInput.removeClass('is-invalid is-valid');
            $bancoInput.next('.invalid-feedback').hide();
            $referenciaInput.next('.invalid-feedback').hide();

        } else {
            $bancoField.show();
            $referenciaField.show();
            $bancoInput.prop('required', true);
            $referenciaInput.prop('required', true);

            // Si están vacíos o tienen valores por defecto, limpiarlos
            if ($bancoInput.val() === 'Divisa' || $bancoInput.val() === 'N/A') {
                $bancoInput.val('');
            }
            if ($referenciaInput.val() === 'Efectivo') {
                $referenciaInput.val('');
            }

            // Limpiar validaciones previas
            $bancoInput.removeClass('is-invalid is-valid');
            $referenciaInput.removeClass('is-invalid is-valid');
        }
    }

    cargarMetodosParaReporte() {
        $.ajax({
            url: 'index.php?url=pago&action=listarMetodos',
            type: 'GET',
            dataType: 'json',
            success: (response) => {
                if (response.success) {
                    const $select = $('#filtroMetodo');
                    $select.empty().append('<option value="todos">Todos los métodos</option>');

                    response.data.forEach((metodo) => {
                        $select.append(
                            $('<option>', {
                                value: metodo.codigo,
                                text: metodo.detalle
                            })
                        );
                    });
                } else {
                    this.showToast('Error al cargar métodos de pago', 'error');
                }
            },
            error: () => {
                this.showToast('Error de conexión al cargar métodos de pago', 'error');
            }
        });
    }

    generarReporteFechas() {
        const fechaInicio = $('#fechaInicio').val();
        const fechaFin = $('#fechaFin').val();
        const metodo = $('#filtroMetodo').val() || 'todos';
        
        if (!fechaInicio || !fechaFin) {
            this.showToast('Por favor seleccione ambas fechas', 'error');
            return;
        }
        
        if (new Date(fechaInicio) > new Date(fechaFin)) {
            this.showToast('La fecha de inicio no puede ser mayor que la fecha de fin', 'error');
            return;
        }
        
        const url = `index.php?url=pago&action=reporte_lista&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&metodo=${metodo}`;
        window.open(url, '_blank');
        $('#modalReporteFechas').modal('hide');
        this.showToast('Generando reporte...', 'info');
    }

    cargarPedidosPendientes() {
        $.ajax({
            url: 'index.php?url=pago&action=obtenerPedidosPendientes',
            type: 'GET',
            dataType: 'json',
            success: (response) => {
                if (response.success) {
                    const tbody = $('#tbody-pedidos-pendientes');
                    tbody.empty();

                    if (response.data.length > 0) {
                        response.data.forEach((pedido) => {
                            const row = `
                                <tr>
                                    <td>#${pedido.id_pedido}</td>
                                    <td>${pedido.nomcliente} (${pedido.ced_cliente})</td>
                                    <td>${new Date(pedido.fecha).toLocaleDateString('es-ES')}</td>
                                    <td>$${parseFloat(pedido.total).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')}</td>
                                    <td>${pedido.cant_producto} producto(s)</td>
                                    <td>
                                        <button class="btn btn-sm btn-danger btn-seleccionar-pedido"
                                                data-id-pedido="${pedido.id_pedido}"
                                                data-total="${pedido.total}"
                                                data-cliente="${pedido.nomcliente} (${pedido.ced_cliente})"
                                                data-fecha="${new Date(pedido.fecha).toLocaleDateString('es-ES')}">
                                            <i class="fas fa-check me-1"></i> Seleccionar
                                        </button>
                                    </td>
                                </tr>
                            `;
                            tbody.append(row);
                        });
                    } else {
                        tbody.html('<tr><td colspan="6" class="text-center">No hay pedidos pendientes de pago</td></tr>');
                    }
                } else {
                    this.showToast('Error al cargar los pedidos pendientes', 'error');
                }
            },
            error: () => {
                this.showToast('Error de conexión al cargar los pedidos pendientes', 'error');
            }
        });
    }

    guardarPago(e) {
        e.preventDefault();

        const form = $('#formPago')[0];
        if (!form.checkValidity()) {
            e.stopPropagation();
            form.classList.add('was-validated');
            this.showToast("Por favor complete todos los campos requeridos", "warning");
            return;
        }

        // Verificar si hay campos con errores de validación
        if ($('#formPago .is-invalid').length > 0) {
            this.showToast('Por favor corrija los errores en el formulario antes de guardar', 'warning');
            return;
        }

        // Validaciones adicionales
        if (!this.validarFormularioPago()) {
            return;
        }

        // Si la fecha está vacía, poner la fecha actual
        if (!$('#fecha_pago').val()) {
            $('#fecha_pago').val(new Date().toISOString().split('T')[0]);
        }
        
        const formData = $('#formPago').serialize();
        const $submitBtn = $('button[type="submit"]', '#formPago');
        
        $.ajax({
            url: 'index.php?url=pago&action=guardar',
            type: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: () => {
                $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Procesando...');
                this.showToast('Procesando pago...', 'info');
            },
            success: (response) => {
                if (response.success) {
                    this.showToast(response.message, 'success', () => {
                        window.location.reload();
                    });

                    $('#modalNuevoPago').modal('hide');
                } else {
                    this.showToast(response.message, 'error');
                }
            },
            error: (xhr, status, error) => {
                console.error('Error:', error);
                let errorMsg = 'Error al procesar el pago';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                this.showToast(errorMsg, 'error');
            },
            complete: () => {
                $submitBtn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Guardar Pago');
            }
        });
    }

    validarFormularioPago() {
        const metodo = $('#cod_metodo').val();
        const esEfectivo = this.esMetodoEfectivo(metodo);

        let valido = true;
        let errores = [];

        if (!this.validarMonto()) {
            valido = false;
            errores.push('El monto debe ser mayor a cero');
        }
        if (!this.validarFecha()) {
            valido = false;
            errores.push('La fecha del pago es requerida');
        }

        if (!esEfectivo) {
            if (!this.validarBanco()) {
                valido = false;
                errores.push('El banco es requerido para este método de pago');
            }
            if (!this.validarReferencia()) {
                valido = false;
                errores.push('La referencia es requerida para este método de pago');
            }
        }

        if (!valido && errores.length > 0) {
            this.showToast('Por favor complete todos los campos requeridos', 'error');
        }

        return valido;
    }

    mostrarDetalles(e) {
        const idPago = $(e.target).closest('.btn-detalles').data('id');
        
        $.ajax({
            url: `index.php?url=pago&action=obtenerDetalles&id=${idPago}`,
            type: 'GET',
            dataType: 'json',
            beforeSend: () => {
                $('#detalle-productos').html('<tr><td colspan="4" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>');
            },
            success: (response) => {
                if (response.success) {
                    this.mostrarDetallesEnModal(response.data);
                    $('#modalDetallesPago').modal('show');
                    this.showToast('Detalles del pago cargados correctamente', 'success');
                } else {
                    this.showToast(response.message, 'error');
                }
            },
            error: () => {
                this.showToast('Error al cargar los detalles del pago', 'error');
            }
        });
    }

    mostrarDetallesEnModal(data) {
        $('#detalle-id-pago').text(data.pago.id_pago);
        $('#detalle-fecha').text(new Date(data.pago.fecha).toLocaleDateString('es-ES'));
        $('#detalle-monto').text(parseFloat(data.pago.monto).toFixed(2));
        $('#detalle-metodo').text(data.pago.metodo_pago || data.pago.cod_metodo);
        $('#detalle-banco').text(data.pago.banco);
        $('#detalle-referencia').text(data.pago.referencia);
        
        const usuarioAccion = data.usuario_accion || 'Sistema';
        const fechaRegistro = data.fecha_registro ? new Date(data.fecha_registro) : new Date();
        const historialHtml = `
            <tr>
                <td>Pago registrado</td>
                <td>${fechaRegistro.toLocaleString('es-ES')}</td>
                <td>${usuarioAccion}</td>
            </tr>
        `;
        $('#detalle-historial').html(historialHtml);
        
        if (data.pedido) {
            $('#detalle-id-pedido').text(data.pedido.id_pedido);
            $('#detalle-cliente').text(data.cliente ? `${data.cliente.nombre} (${data.cliente.cedula})` : 'N/A');
            $('#detalle-fecha-pedido').text(new Date(data.pedido.fecha).toLocaleDateString('es-ES'));
            $('#detalle-total-pedido').text(parseFloat(data.pedido.total).toFixed(2));
        } else {
            $('#detalle-id-pedido').text('N/A');
            $('#detalle-cliente').text('N/A');
            $('#detalle-fecha-pedido').text('N/A');
            $('#detalle-total-pedido').text('N/A');
        }
        
        if (data.productos && data.productos.length > 0) {
            let productosHtml = '';
            data.productos.forEach(producto => {
            productosHtml += `
                    <tr>
                        <td>${producto.nombre || 'Producto ' + producto.cod_producto}</td>
                        <td>${producto.cantidad}</td>
                        <td>$${parseFloat(producto.precio_unitario || producto.precio || producto.precio_venta || 0).toFixed(2)}</td>
                        <td>$${parseFloat(producto.subtotal).toFixed(2)}</td>
                    </tr>
                `;
            });
            $('#detalle-productos').html(productosHtml);
        } else {
            $('#detalle-productos').html('<tr><td colspan="4" class="text-center">No hay productos</td></tr>');
        }
    }

    editarPago(e) {
        const idPago = $(e.target).closest('.btn-editar').data('id');

        $.ajax({
            url: `index.php?url=pago&action=formEditar&id_pago=${idPago}`,
            type: 'GET',
            dataType: 'json',
            success: (response) => {
                if (response.success) {
                    this.cargarDatosEdicion(response.data);
                    $('#modalEditarPago').modal('show');
                } else {
                    this.showToast(response.message, 'error');
                }
            },
            error: (xhr, status, error) => {
                console.error('Error cargando datos para editar:', error);
                this.showToast('Error al cargar datos para editar', 'error');
            }
        });
    }

    cargarDatosEdicion(pago) {
        $('#editar_id_pago').val(pago.id_pago);
        $('#editar_banco').val(pago.banco);
        $('#editar_referencia').val(pago.referencia);
        $('#editar_monto').val(pago.monto);
        $('#editar_fecha').val(pago.fecha);
        
        this.cargarMetodosPago('editar_cod_metodo', pago.cod_metodo).then(() => {
            // Forzar la actualización de campos después de cargar métodos
            setTimeout(() => {
                this.actualizarCamposPorMetodo($('#editar_cod_metodo')[0]);
            }, 100);
        });
    }

    actualizarPago(e) {
        e.preventDefault();
        
        const form = $('#formEditarPago')[0];
        if (!form.checkValidity()) {
            e.stopPropagation();
            form.classList.add('was-validated');
            this.showToast("Por favor complete todos los campos requeridos", "warning");
            return;
        }

        if (!this.validarFormularioEdicion()) {
            return;
        }
        
        const formData = $('#formEditarPago').serialize();
        
        $.ajax({
            url: 'index.php?url=pago&action=actualizar',
            type: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: () => {
                $('button[type="submit"]', '#formEditarPago').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Actualizando...');
                this.showToast('Actualizando pago...', 'info');
            },
            success: (response) => {
                if (response.success) {
                    this.showToast(response.message, 'success');
                    $('#modalEditarPago').modal('hide');
                    this.table.ajax.reload();
                    // También recargar vista móvil si está activa
                    this.cargarPagosMobile($('.filtro-btn-mobile.active').data('filtro') || 'todos');
                } else {
                    this.showToast(response.message, 'error');
                }
            },
            error: (xhr, status, error) => {
                console.error('Error:', error);
                this.showToast('Error al actualizar el pago', 'error');
            },
            complete: () => {
                $('button[type="submit"]', '#formEditarPago').prop('disabled', false).html('<i class="fas fa-save me-1"></i> Actualizar Pago');
            }
        });
    }

    validarFormularioEdicion() {
        const metodo = $('#editar_cod_metodo').val();
        const esEfectivo = this.esMetodoEfectivo(metodo);
        
        let valido = true;

        if (!this.validarEditarMonto()) valido = false;
        if (!this.validarEditarFecha()) valido = false;
        
        if (!esEfectivo) {
            if (!this.validarEditarBanco()) valido = false;
            if (!this.validarEditarReferencia()) valido = false;
        }

        return valido;
    }

    eliminarPago(e) {
        const idPago = $(e.target).closest('.btn-eliminar').data('id');
        const $btn = $(e.target).closest('.btn-eliminar');
        
        Swal.fire({
            title: '¿Está seguro?',
            text: "Esta acción marcará el pago como eliminado. ¿Desea continuar?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'index.php?url=pago&action=eliminar',
                    type: 'POST',
                    data: { id_pago: idPago },
                    dataType: 'json',
                    beforeSend: () => {
                        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
                    },
                    success: (response) => {
                        if (response.success) {
                            this.showToast(response.message, 'success');
                            this.table.ajax.reload();
                            // También recargar vista móvil si está activa
                            this.cargarPagosMobile($('.filtro-btn-mobile.active').data('filtro') || 'todos');
                        } else {
                            this.showToast(response.message, 'error');
                            $btn.prop('disabled', false).html('<i class="fas fa-trash"></i>');
                        }
                    },
                    error: () => {
                        this.showToast('Error al eliminar el pago', 'error');
                        $btn.prop('disabled', false).html('<i class="fas fa-trash"></i>');
                    }
                });
            }
        });
    }

    restaurarPago(e) {
        const idPago = $(e.target).closest('.btn-restaurar').data('id');
        const $btn = $(e.target).closest('.btn-restaurar');
        
        Swal.fire({
            title: '¿Restaurar pago?',
            text: "Esta acción restaurará el pago eliminado. ¿Desea continuar?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, restaurar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            customClass: {
                confirmButton: 'btn btn-success',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'index.php?url=pago&action=restaurar',
                    type: 'POST',
                    data: { id_pago: idPago },
                    dataType: 'json',
                    beforeSend: () => {
                        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
                        this.showToast('Restaurando pago...', 'info');
                    },
                    success: (response) => {
                        if (response.success) {
                            this.showToast(response.message, 'success');
                            this.table.ajax.reload();
                            // También recargar vista móvil si está activa
                            this.cargarPagosMobile($('.filtro-btn-mobile.active').data('filtro') || 'todos');
                        } else {
                            this.showToast(response.message, 'error');
                            $btn.prop('disabled', false).html('<i class="fas fa-undo"></i>');
                        }
                    },
                    error: () => {
                        this.showToast('Error al restaurar el pago', 'error');
                        $btn.prop('disabled', false).html('<i class="fas fa-undo"></i>');
                    }
                });
            }
        });
    }

    generarComprobante() {
        const idPago = $('#id_pago_comprobante').val();
        
        if (!idPago || isNaN(idPago) || idPago <= 0) {
            $('#id_pago_comprobante').addClass('is-invalid');
            this.showToast('Por favor ingrese un número de pago válido', 'error');
            return false;
        }
        
        // Obtener la instancia del modal usando Bootstrap
        const modalElement = document.getElementById('modalComprobante');
        const modal = bootstrap.Modal.getInstance(modalElement);
        
        // Cerrar el modal correctamente
        if (modal) {
            modal.hide();
        }
        
        // Limpiar el formulario
        $('#formComprobante')[0].reset();
        $('#id_pago_comprobante').removeClass('is-invalid');
        
        // Abrir el comprobante en una nueva ventana
        window.open(`index.php?url=pago&action=reporte_comprobante&id=${idPago}`, '_blank');
        
        this.showToast('Generando comprobante...', 'info');
        
        return true;
    }

    generarReporteDropdown(action) {
        switch(action) {
            case 'reporte_lista':
                window.open('index.php?url=pago&action=reporte_lista', '_blank');
                this.showToast('Generando listado general...', 'info');
                break;
            case 'reporte_efectivo':
                window.open('index.php?url=pago&action=reporte_efectivo', '_blank');
                this.showToast('Generando reporte de efectivo...', 'info');
                break;
            case 'reporte_transferencias':
                window.open('index.php?url=pago&action=reporte_transferencias', '_blank');
                this.showToast('Generando reporte de transferencias...', 'info');
                break;
        }
    }

    setDefaultDates() {
        const hoy = new Date().toISOString().split('T')[0];
        const hace30Dias = new Date();
        hace30Dias.setDate(hace30Dias.getDate() - 30);
        const fechaHace30Dias = hace30Dias.toISOString().split('T')[0];
        
        $('#fechaInicio').val(fechaHace30Dias);
        $('#fechaFin').val(hoy);
    }

    showToast(message, type = 'info', callback = null) {
        const toastMethods = {
            'success': toastr.success,
            'error': toastr.error,
            'warning': toastr.warning,
            'info': toastr.info
        };

        const toastMethod = toastMethods[type] || toastr.info;

        if (callback) {
            toastMethod(message, '', {
                onHidden: callback
            });
        } else {
            toastMethod(message);
        }
    }
}

// Inicializar cuando el documento esté listo
$(document).ready(() => {
    new PagoManager();
    
    // Eventos adicionales para el comprobante
    $('#id_pago_comprobante').on('keypress', (e) => {
        if (e.which === 13) {
            e.preventDefault();
            $('#btnGenerarComprobante').click();
        }
    });
    
    // Limpiar formularios al cerrar modales
    $('.modal').on('hidden.bs.modal', function() {
        const form = $(this).find('form');
        if (form.length) {
            form.removeClass('was-validated');
            const formElement = form[0];
            if (formElement && typeof formElement.reset === 'function') {
                formElement.reset();
            }
        }
        
        // Limpiar cualquier backdrop residual
        if ($('.modal.show').length === 0) {
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
        }
    });

    // Manejar correctamente el cierre de modales
    $(document).on('hide.bs.modal', '.modal', function() {
        // Asegurarse de que el body se mantenga correcto cuando hay múltiples modales
        const $body = $('body');
        const $openModals = $('.modal.show');
        
        if ($openModals.length <= 1) {
            // Si este es el último modal, limpiar el backdrop
            setTimeout(() => {
                if ($('.modal.show').length === 0) {
                    $body.removeClass('modal-open');
                    $('.modal-backdrop').remove();
                }
            }, 150);
        }
    });
});