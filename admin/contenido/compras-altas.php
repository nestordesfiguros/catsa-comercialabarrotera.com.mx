<?php
// contenido/compras-altas.php
?>

<!-- Imask js -->
<script src="https://unpkg.com/imask"></script>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="compras">Órdenes de Compra</a></li>
            <li class="breadcrumb-item active" aria-current="page">Alta</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="d-flex justify-content-center align-items-center w-100">
                <div class="col-12 col-lg-10">
                    <div class="card">
                        <form action="" id="formPedidos" autocomplete="off">
                            <div class="card-header">
                                <div class="row g-3">
                                    <div class="col-12 col-md-2">
                                        <div class="form-floating">
                                            <input type="date" name="fecha" class="form-control" id="datepicker" value="<?= $fecha_bd ?>">
                                            <label for="datepicker">Fecha</label>
                                        </div>
                                        <div id="errorFecha" class="invalid-feedback mt-2" style="display:none;">
                                            Escribe una Fecha
                                        </div>
                                    </div>

                                    <!-- PROVEEDOR (SOLO MODAL) -->
                                    <div class="col-12 col-md-8">
                                        <div class="input-group">
                                            <div class="form-floating flex-grow-1">
                                                <input type="text"
                                                    id="clienteInput"
                                                    class="form-control"
                                                    name="cliente_nombre"
                                                    placeholder="Selecciona un proveedor..."
                                                    autocomplete="off"
                                                    readonly />
                                                <label for="clienteInput">Selecciona un proveedor</label>
                                            </div>
                                            <button type="button"
                                                class="btn btn-outline-primary"
                                                id="btnOpenProveedor"
                                                title="Seleccionar proveedor"
                                                onclick="FnAbrirModalProveedor();">
                                                <i class="fa-solid fa-truck-field"></i>
                                            </button>
                                        </div>

                                        <div id="errorCliente" class="invalid-feedback mt-2" style="display:none;"></div>
                                    </div>

                                    <div class="col-12 col-md-2 d-flex align-items-center justify-content-md-end">
                                        <p class="mb-0"><b>Total:</b> <span id="totalPedido">$0.00</span></p>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="col-12">
                                    <div class="row g-2">
                                        <div class="col-12 col-md-6">
                                            <button type="button" class="btn btn-primary btn-sm" onclick="FnAgregarModal();">
                                                Agregar Producto
                                            </button>
                                        </div>
                                        <div class="col-12 col-md-6 text-md-end">
                                            <button type="submit" class="btn btn-success btn-sm" id="btnGuardar">Guardar</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3 table-responsive">
                                    <table id="tablaProductos" class="table table-bordered table-striped">
                                        <thead class="table-dark">
                                            <tr>
                                                <th class="text-center" style="width:150px;">Cantidad</th>
                                                <th class="text-center">Producto</th>
                                                <th class="text-center" style="width:150px;">Precio U.</th>
                                                <th class="text-center" style="width:150px;">Total</th>
                                                <th class="text-center" style="width:80px;">Borrar</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbodyProductos">
                                            <tr id="filaVacia">
                                                <td colspan="5">Ningún producto agregado</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <input type="hidden" name="proveedor_id" id="clienteId">
                            <input type="hidden" name="total" id="inputSumaTotal">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Productos -->
<div class="modal fade" id="modalAddProductos" tabindex="-1" aria-labelledby="modalAddProductosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAddProductosLabel">Productos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="FnCerrarModal();"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <div class="row justify-content-end g-2">
                        <div class="col-12 col-md-6">
                            <div class="form-floating">
                                <input type="text" id="search" class="form-control" placeholder="Buscar">
                                <label for="search">Buscar</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="TableListaProductos" class="table table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th style="width:160px;">Clave</th>
                                <th>Producto</th>
                                <th class="text-end" style="width:170px;">Último precio</th>
                                <th class="text-center" style="width:120px;">Acción</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal Proveedores -->
<div class="modal fade" id="modalProveedores" tabindex="-1" aria-labelledby="modalProveedoresLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalProveedoresLabel">Proveedores</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="FnCerrarModalProveedor();"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <div class="row justify-content-end g-2">
                        <div class="col-12 col-md-6">
                            <div class="form-floating">
                                <input type="text" id="searchProveedor" class="form-control" placeholder="Buscar">
                                <label for="searchProveedor">Buscar</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="TableListaProveedores" class="table table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th>Proveedor</th>
                                <th>Razón social</th>
                                <th class="text-center" style="width:120px;">Acción</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    // =============================================
    // MODAL PRODUCTOS
    // =============================================
    function FnAgregarModal() {
        $('#modalAddProductos').modal('show');
        if (window.tablaProductosModal) {
            window.tablaProductosModal.ajax.reload();
        }
    }

    function FnCerrarModal() {
        $('#modalAddProductos').modal('hide');
    }

    // =============================================
    // MODAL PROVEEDORES
    // =============================================
    function FnAbrirModalProveedor() {
        $('#modalProveedores').modal('show');
        if (window.tablaProveedoresModal) {
            window.tablaProveedoresModal.ajax.reload();
        }
        setTimeout(function() {
            $('#searchProveedor').trigger('focus');
        }, 200);
    }

    function FnCerrarModalProveedor() {
        $('#modalProveedores').modal('hide');
    }

    // =============================================
    // TABLA VACÍA
    // =============================================
    function actualizarMensajeTablaVacia() {
        const $tbody = $('#tbodyProductos');
        const filas = $tbody.find('tr.fila-producto').length;

        if (filas === 0) {
            if ($('#filaVacia').length === 0) {
                $tbody.append('<tr id="filaVacia"><td colspan="5">Ningún producto agregado</td></tr>');
            }
        } else {
            $('#filaVacia').remove();
        }
    }

    // =============================================
    // UI BLOCK (usa tu modal #modalSpiner)
    // =============================================
    function uiBlock(on) {
        if (on) {
            if ($('#modalSpiner').length) {
                $('#modalSpiner').modal({
                    backdrop: 'static',
                    keyboard: false
                });
                $('#modalSpiner').modal('show');
            }
            $('body').addClass('pe-none');
            $('#modalSpiner').removeClass('pe-none');
        } else {
            if ($('#modalSpiner').length) $('#modalSpiner').modal('hide');
            $('body').removeClass('pe-none');
        }
    }

    function btnLock($btn, on, texto) {
        if (!$btn || !$btn.length) return;
        if (on) {
            $btn.data('txt', $btn.html());
            $btn.prop('disabled', true);
            if (texto) $btn.html(texto);
        } else {
            $btn.prop('disabled', false);
            var old = $btn.data('txt');
            if (old) $btn.html(old);
        }
    }


    // =============================================
    // GUARDAR (AJAX)
    // =============================================
    function guardarPedido(form) {
        var $btn = $('#btnGuardar');
        var formData = new FormData(form);

        alertify.confirm(
            'Confirmación',
            '¿Estás seguro de que deseas guardar la compra?',
            function() {
                btnLock($btn, true, 'Guardando...');
                uiBlock(true);

                $.ajax({
                    url: 'ajax/compras/guardar.php',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(respuesta) {
                        var r = (respuesta || '').trim();

                        if (r === 'success') {
                            alertify.success('Compra guardada');
                            location.href = "compras";
                            return;
                        }

                        uiBlock(false);
                        btnLock($btn, false);

                        if (r === 'duplicado') {
                            alertify.error('Ya existe una orden de compra con los mismos datos.');
                        } else {
                            alertify.alert('Aviso', 'No se ha capturado ningún producto.');
                        }
                    },
                    error: function(xhr) {
                        uiBlock(false);
                        btnLock($btn, false);
                        alertify.error('Error de conexión: ' + xhr.statusText);
                    }
                });
            },
            function() {
                alertify.error('Guardar compra cancelada');
            }
        );
    }

    // =============================================
    // TOTALES
    // =============================================
    function formatCurrency(amount) {
        return new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN',
            maximumFractionDigits: 2,
            useGrouping: true
        }).format(amount);
    }

    function calcularTotal(fila) {
        const cantidad = parseFloat((fila.find('.cantidad').val() || '0').toString().replace(/,/g, '')) || 0;
        const precioVenta = parseFloat((fila.find('.precio_venta').val() || '0').toString().replace(/,/g, '')) || 0;
        const total = cantidad * precioVenta;

        fila.find('td:eq(3)').text(formatCurrency(total));
        actualizarSumaTotal();
    }

    function actualizarSumaTotal() {
        let sumaTotal = 0;

        $('#tbodyProductos tr.fila-producto').each(function() {
            const totalTexto = $(this).find('td:eq(3)').text()
                .replace('$', '').replace(/,/g, '').trim();
            const total = parseFloat(totalTexto) || 0;
            sumaTotal += total;
        });

        $("#inputSumaTotal").val(sumaTotal);
        $('#totalPedido').text(formatCurrency(sumaTotal));
    }

    // =============================================
    // IMASK
    // =============================================
    function aplicarMaskPrecioVenta() {
        const precioVentaInputs = document.querySelectorAll('.precio_venta');
        precioVentaInputs.forEach(function(input) {
            if (!input.hasAttribute('data-masked')) {
                IMask(input, {
                    mask: Number,
                    min: 0,
                    max: 10000000.99,
                    thousandsSeparator: ',',
                    radix: '.',
                    scale: 2,
                });
                input.setAttribute('data-masked', 'true');
            }
        });
    }

    // =============================================
    // ÚLTIMO PRECIO (ENDPOINT)
    // =============================================
    function obtenerUltimoPrecio(idProducto, proveedorId = '') {
        $.ajax({
            url: 'ajax/compras/ultimo-precio.php',
            method: 'POST',
            dataType: 'json',
            data: {
                id_producto: idProducto,
                id_proveedor: proveedorId
            },
            success: function(res) {
                const $fila = $(`#tbodyProductos tr.fila-producto[data-id="${idProducto}"]`);
                const $inputPrecio = $fila.find('input.precio_venta');

                const precioNum = (res && res.success) ? parseFloat(res.precio) : NaN;

                if (!isNaN(precioNum)) {
                    $inputPrecio.val(precioNum.toFixed(2)).trigger('input');
                } else {
                    $inputPrecio.val('').trigger('input');
                }
            }
        });
    }

    function refrescarPreciosPorProveedor() {
        const proveedorId = $('#clienteId').val() || '';
        $('#tbodyProductos tr.fila-producto').each(function() {
            const idProducto = $(this).data('id');
            obtenerUltimoPrecio(idProducto, proveedorId);
        });
    }

    // =============================================
    // EXCLUIDOS: productos ya agregados
    // =============================================
    function getProductosExcluidos() {
        const ids = [];
        $('#tbodyProductos tr.fila-producto').each(function() {
            const idp = parseInt($(this).data('id'), 10);
            if (idp > 0) ids.push(idp);
        });
        return ids.join(',');
    }

    // =============================================
    // HELPERS
    // =============================================
    function escapeHtml(text) {
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // =============================================
    // READY
    // =============================================
    $(document).ready(function() {

        // ---------------------------------------------
        // DataTable modal PRODUCTOS (SERVER-SIDE)
        // ---------------------------------------------
        $('#TableListaProductos').dataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: 'ajax/compras/tabla-productos.php',
                type: 'POST',
                data: function(d) {
                    d.excluded_ids = getProductosExcluidos();
                    d.id_proveedor = $('#clienteId').val() || ''; // <- para último precio por proveedor
                }
            },
            ordering: true,
            pageLength: 10,
            dom: "<'row'<'col-sm-6'l><'col-sm-6'p>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-6'l><'col-sm-6'p>>",
            initComplete: function() {
                $('#custom_length').appendTo('body');
            },
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json",
                sSearch: '<i class="fa fa-search" aria-hidden="true"></i> Buscar'
            },
            responsive: true,
            columns: [{
                    data: 'clave'
                },
                {
                    data: 'nombre'
                },
                {
                    data: 'ultimo_precio',
                    className: 'text-end',
                    render: function(data, type, row) {
                        const p = parseFloat(data);
                        if (!isNaN(p)) return formatCurrency(p);
                        return '-';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function(data, type, row) {
                        const idp = row.id_producto || '';
                        const clave = row.clave || '';
                        const nombre = row.nombre || '';
                        const ultimo = row.ultimo_precio || '';
                        return `
                            <button type="button"
                                class="btn btn-sm btn-primary btn-agregar-producto"
                                data-id="${idp}"
                                data-clave="${escapeHtml(clave)}"
                                data-nombre="${escapeHtml(nombre)}"
                                data-precio="${ultimo}">
                                Agregar
                            </button>
                        `;
                    }
                }
            ]
        });

        window.tablaProductosModal = $('#TableListaProductos').DataTable();

        $('#search').on('keyup', function() {
            window.tablaProductosModal.search($(this).val()).draw();
        });

        // AGREGAR producto (por botón)
        $('#TableListaProductos tbody').on('click', '.btn-agregar-producto', function() {
            const id_producto = parseInt($(this).data('id'), 10) || 0;
            const clave = String($(this).data('clave') || '');
            const nombre = String($(this).data('nombre') || '');

            if (id_producto <= 0) return;

            const nuevaFila = `
                <tr class="fila-producto" data-id="${id_producto}">
                    <td><input type="number" class="form-control cantidad" value="1" min="1" name="cantidad[]" required></td>
                    <td>${escapeHtml(nombre)}</td>
                    <td class="text-center">
                        <input type="text" name="precio_venta[]" class="form-control precio_venta" required />
                    </td>
                    <td class="text-center">Total</td>
                    <td>
                        <input type="hidden" name="producto_id[]" value="${id_producto}">
                        <input type="hidden" name="clave[]" value="${escapeHtml(clave)}">
                        <div class="text-center">
                            <i class="fas fa-trash fa-lg btn-eliminar text-danger" style="cursor:pointer;"></i>
                        </div>
                    </td>
                </tr>
            `;

            $('#tbodyProductos').append(nuevaFila);
            aplicarMaskPrecioVenta();
            actualizarMensajeTablaVacia();

            // setear precio desde la tabla (último precio mostrado)
            const proveedorId = $('#clienteId').val() || '';
            const precioUltimo = parseFloat($(this).data('precio'));

            const $nuevaFilaElement = $('#tbodyProductos tr').last();
            const $inputPrecio = $nuevaFilaElement.find('input.precio_venta');

            if (!isNaN(precioUltimo)) {
                $inputPrecio.val(precioUltimo.toFixed(2)).trigger('input');
            } else {
                // fallback si no vino precio (mantiene tu endpoint existente)
                obtenerUltimoPrecio(id_producto, proveedorId);
            }

            // total inicial
            calcularTotal($nuevaFilaElement);

            // refrescar lista para que ya no aparezca
            if (window.tablaProductosModal) window.tablaProductosModal.ajax.reload();

            FnCerrarModal();
        });

        // Cambios en cantidad o precio -> recalcular
        $(document).on('input', '.cantidad, .precio_venta', function() {
            const fila = $(this).closest('tr');
            calcularTotal(fila);
        });

        // Eliminar producto
        $('#tbodyProductos').on('click', '.btn-eliminar', function() {
            const $fila = $(this).closest('.fila-producto');
            const nombreProducto = $fila.find('td:eq(1)').text().trim();

            alertify.confirm(
                'Confirmación',
                `¿Estás seguro de que deseas eliminar el producto: ${escapeHtml(nombreProducto)}?`,
                function() {
                    $fila.remove();
                    actualizarMensajeTablaVacia();
                    actualizarSumaTotal();

                    if (window.tablaProductosModal) window.tablaProductosModal.ajax.reload();
                    alertify.success('Producto eliminado');
                },
                function() {
                    alertify.error('Eliminación cancelada');
                }
            );
        });

        // ---------------------------------------------
        // DataTable modal PROVEEDORES (SERVER-SIDE)
        // ---------------------------------------------
        $('#TableListaProveedores').dataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: 'ajax/compras/tabla-proveedores.php',
                type: 'POST'
            },
            ordering: true,
            pageLength: 10,
            dom: "<'row'<'col-sm-6'l><'col-sm-6'p>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-6'l><'col-sm-6'p>>",
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json",
                sSearch: '<i class="fa fa-search" aria-hidden="true"></i> Buscar'
            },
            responsive: true,
            columns: [{
                    data: 'nombre'
                },
                {
                    data: 'razon_social'
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function(data, type, row) {
                        const idp = row.id_proveedor || '';
                        const nombre = row.nombre || '';
                        const razon = row.razon_social || '';
                        return `
                            <button type="button"
                                class="btn btn-sm btn-primary btn-select-proveedor"
                                data-id="${idp}"
                                data-nombre="${escapeHtml(nombre)}"
                                data-razon="${escapeHtml(razon)}">
                                Seleccionar
                            </button>
                        `;
                    }
                }
            ]
        });

        window.tablaProveedoresModal = $('#TableListaProveedores').DataTable();

        $('#searchProveedor').on('keyup', function() {
            window.tablaProveedoresModal.search($(this).val()).draw();
        });

        // Seleccionar proveedor
        $('#TableListaProveedores tbody').on('click', '.btn-select-proveedor', function() {
            const id_proveedor = parseInt($(this).data('id'), 10) || 0;
            const nombre = String($(this).data('nombre') || '');
            const razon = String($(this).data('razon') || '');

            if (id_proveedor <= 0) return;

            const texto = (nombre && razon) ? (nombre + ' / ' + razon) : (nombre || razon);

            $('#clienteId').val(id_proveedor);
            $('#clienteInput').val(texto);

            $('#clienteInput').removeClass('is-invalid').addClass('is-valid');
            $('#errorCliente').hide();

            FnCerrarModalProveedor();

            // refrescar precios de productos ya agregados
            refrescarPreciosPorProveedor();
        });

        // Prevenir submit con Enter
        $('#formPedidos').on('keypress', function(e) {
            if (e.keyCode === 13) e.preventDefault();
        });

        function mostrarErrorCliente(mensaje) {
            $('#errorCliente').text(mensaje).show();
            $('#clienteInput').addClass('is-invalid');
            $('#clienteInput').focus();
        }

        $.validator.addMethod("clientExists", function(value, element) {
            const id = $('#clienteId').val();
            if (!id) {
                mostrarErrorCliente("Selecciona un proveedor válido desde el ícono.");
                return false;
            }
            return true;
        }, "Este proveedor no existe.");

        $('#formPedidos').validate({
            rules: {
                fecha: {
                    required: true
                },
                cliente_nombre: {
                    required: true,
                    clientExists: true
                }
            },
            messages: {
                fecha: {
                    required: "Escribe una fecha"
                },
                cliente_nombre: {
                    required: "Selecciona un proveedor"
                }
            },
            errorElement: 'div',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                error.insertAfter(element);
            },
            highlight: function(element) {
                $(element).addClass('is-invalid').removeClass('is-valid');
            },
            unhighlight: function(element) {
                $(element).removeClass('is-invalid').addClass('is-valid');
            },
            submitHandler: function(form) {
                guardarPedido(form);
            }
        });

        actualizarMensajeTablaVacia();

    });
</script>