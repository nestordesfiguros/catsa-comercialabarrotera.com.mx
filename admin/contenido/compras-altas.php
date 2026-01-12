<?php
// contenido/compras-altas.php
$clientes = [];
$con = "SELECT * FROM cat_proveedores WHERE estatus=1";
$rs = $clsConsulta->consultaGeneral($con);
if ($clsConsulta->numrows > 0) {
    $clientes = [];
    foreach ($rs as $v => $val) {
        $clientes[] = [
            'id' => $val['id'],
            'nombre' => $val['razon_social']
        ];
    }
}
?>

<!-- Imask js -->
<script src="https://unpkg.com/imask"></script>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="compras">Órdenes de Compra</a></li>
            <li class="breadcrumb-item active" aria-current="page"> Alta</li>

        </ol>
    </nav>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row ">
            <div class="d-flex justify-content-center align-items-center w-100">
                <div class="col-10">
                    <div class="card">
                        <form action="" method="post" id="formPedidos">
                            <div class="card-header">
                                <div class="row g-3">
                                    <div class="col-12 col-md-2">
                                        <div class="form-outline">
                                            <input type="date" name="fecha" class="form-control" id="datepicker" value="<?= $fecha_bd ?>">
                                            <label for="datepicker" class="form-label">Fecha</label>
                                        </div>
                                        <div id="errorFecha" class="invalid-feedback mt-2" style="display: none;">
                                            Escribe una Fecha
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-8">
                                        <div class="form-group">
                                            <div class="form-outline">
                                                <input type="text" id="clienteInput" class="form-control" name="cliente_nombre" list="clientes" placeholder="Selecciona un cliente..." autocomplete="off" />
                                                <label for="clienteInput" class="form-label">Selecciona un proveedor</label>
                                            </div>
                                            <!-- Datalist con nombres de clientes -->
                                            <datalist id="clientes">
                                                <?php foreach ($clientes as $cliente): ?>
                                                    <option value="<?= htmlspecialchars($cliente['nombre']) ?>" data-id="<?= $cliente['id'] ?>"></option>
                                                <?php endforeach; ?>
                                            </datalist>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-2">
                                        <p><b> Total:</b> <span id="totalPedido">$0.00</span></p>
                                    </div>

                                </div>
                            </div>
                            <div class="card-body">
                                <div class="col-12">
                                    <div class="row">
                                        <div class="col-6">
                                            <button type="button" class="btn btn-primary btn-sm" onclick="FnAgregarModal();">Agregar Producto</button>
                                        </div>
                                        <div class="col-6">
                                            <button type="submit" class="btn btn-success btn-sm" id="btnGuardar">Guardar</button>
                                        </div>
                                    </div>
                                </div>
                                <!-- ... tabla de productos ... -->
                                <div class="mt-3">
                                    <table id="tablaProductos" class="table table-bordered table-striped">
                                        <thead class="bg-dark text-white" style="height: 20px;">
                                            <tr>
                                                <th class="text-center" style="width: 150px;">Cantidad</th>
                                                <th class="text-center">Producto</th>
                                                <th class="text-center" style="width: 150px;">Precio U.</th>
                                                <th class="text-center" style="width: 150px;">Total</th>
                                                <th class="text-center" style="width: 80px;">Borrar</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbodyProductos">
                                            <!-- Mensaje inicial de tabla vacía -->
                                            <tr id="filaVacia">
                                                <td colspan="3">Ningún producto agregado</td>
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


<!-- Modal -->
<div class="modal fade" id="modalAddProductos" tabindex="-1" aria-labelledby="modalAddProductosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAddProductosLabel">Productos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="FnCerrarModal();"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 p-3">
                    <div class="row d-flex justify-content-end">
                        <div class="form-outline col-6">
                            <input type="text" id="search" class="form-control">
                            <label for="search" class="form-label">Buscar</label>
                        </div>
                    </div>
                </div>
                <table id="TableListaProductos" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>Clave</th>
                            <th>Producto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $con = "SELECT * FROM cat_productos WHERE estatus=1";
                        $rs = $clsConsulta->consultaGeneral($con);
                        foreach ($rs as $v => $val) {
                            $id_producto = $val['id_producto'];
                            echo '<tr data-id="' . $id_producto . '" data-clave="' . $val['clave'] . '" data-nombre="' .
                                $val['nombre'] . '">';
                            echo '<td>' . $val['clave'] . '</td>';
                            echo '<td><b class="text-primary" style="cursor:pointer;">' . $val['nombre'] . '</b></td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // =============================================
    // FUNCIONES PRINCIPALES
    // =============================================

    // Abrir y cerrar modal de productos
    function FnAgregarModal() {
        $('#modalAddProductos').modal('show');
    }

    function FnCerrarModal() {
        $('#modalAddProductos').modal('hide');
    }

    // =============================================
    // MANEJO DE LA TABLA DE PRODUCTOS
    // =============================================

    function actualizarMensajeTablaVacia() {
        const $tbody = $('#tbodyProductos');
        if ($tbody.children().length === 0) {
            $tbody.append('<tr id="filaVacia"><td colspan="3">No hay productos agregados</td></tr>');
        } else {
            $('#filaVacia').remove();
        }
    }

    // =============================================
    // MANEJO DE CLIENTES
    // =============================================

    function mostrarErrorCliente(mensaje) {
        $('#errorCliente').text(mensaje).show();
        $('#clienteInput').addClass('is-invalid');
        $('#clienteInput').focus();
    }

    // =============================================
    // GUARDAR COMPRA (AJAX)
    // =============================================

    function guardarPedido(form) {
        const formData = new FormData(form);
        $('#btnGuardar').prop('disabled', true);

        alertify.confirm('Confirmación', '¿Estás seguro de que deseas guardar la compra?',
            function() {
                $('#modalSpiner').modal('show');
                $.ajax({
                    url: 'ajax/compras/guardar.php',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(respuesta) {
                        $('#modalSpiner').modal('hide');

                        const r = (respuesta || '').trim();
                        if (r === 'success') {
                            location.href = "compras";
                        } else if (r === 'duplicado') {
                            alertify.error('Ya existe una orden de compra con los mismos datos.');
                            $('#btnGuardar').prop('disabled', false);
                        } else {
                            alertify.alert('Aviso', 'No se ha capturado ningún producto.');
                            $('#btnGuardar').prop('disabled', false);
                        }
                    },
                    error: function(xhr) {
                        document.getElementById('spinner').style.display = 'none';
                        $('#btnGuardar').prop('disabled', false);
                        alertify.error('Error de conexión: ' + xhr.statusText);
                    }
                });
            },
            function() {
                $('#btnGuardar').prop('disabled', false);
                document.getElementById('spinner').style.display = 'none';
                alertify.error('Guardar compra cancelada');
            }
        );
    }

    // =============================================
    // AUXILIARES
    // =============================================

    function resetearFormulario() {
        $('#formPedidos')[0].reset();
        $('#clienteId').val('');
        $('#tbodyProductos').empty();
        actualizarMensajeTablaVacia();
        $('.is-valid').removeClass('is-valid');
    }

    // ---- Calcular total por fila ----
    function calcularTotal(fila) {
        const cantidad = parseFloat((fila.find('.cantidad').val() || '0').toString().replace(/,/g, '')) || 0;
        const precioVenta = parseFloat((fila.find('.precio_venta').val() || '0').toString().replace(/,/g, '')) || 0;
        const total = cantidad * precioVenta;

        const totalFormateado = new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN'
        }).format(total);

        fila.find('td:eq(3)').text(totalFormateado);
        actualizarSumaTotal();
    }

    // ---- IMask para precio ----
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

    // ---- Actualiza texto de total por fila sin recalcular suma (utilidad interna) ----
    function actualizarTotalPorFila(fila) {
        const cantidad = parseFloat((fila.find('.cantidad').val() || '0').toString().replace(/,/g, '')) || 0;
        const precioVenta = parseFloat((fila.find('.precio_venta').val() || '0').toString().replace(/,/g, '')) || 0;
        const total = cantidad * precioVenta;
        fila.find('td:eq(3)').text(formatCurrency(total));
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN',
            maximumFractionDigits: 2,
            useGrouping: true
        }).format(amount);
    }

    // ---- Suma total de la tabla ----
    function actualizarSumaTotal() {
        let sumaTotal = 0;
        $('#tbodyProductos tr').each(function() {
            const totalTexto = $(this).find('td:eq(3)').text().replace('$', '').replace(/,/g, '').trim();
            const total = parseFloat(totalTexto) || 0;
            sumaTotal += total;
            $("#inputSumaTotal").val(sumaTotal);
        });
        $('#totalPedido').text(formatCurrency(sumaTotal));
    }

    // =============================================
    // NUEVO: Obtener último precio desde mov_compras
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
                // Log para depurar qué está llegando
                console.log('ultimo-precio response:', res);

                const $fila = $(`#tbodyProductos tr.fila-producto[data-id="${idProducto}"]`);
                const $inputPrecio = $fila.find('input.precio_venta');

                // Normalizamos a número aunque venga como string
                const precioNum = (res && res.success) ? parseFloat(res.precio) : NaN;

                if (!isNaN(precioNum)) {
                    // Seteamos sin formato; IMask se encarga de mostrarlo bonito tras el 'input'
                    $inputPrecio.val(precioNum.toFixed(2)).trigger('input');
                } else {
                    // Sin historial o respuesta inválida: dejemos vacío (o '0.00' si prefieres)
                    $inputPrecio.val('').trigger('input');
                }
            },
            error: function(xhr, status, err) {
                console.warn('No se pudo obtener el último precio del producto', idProducto, status, err, xhr?.responseText);
            }
        });
    }


    // =============================================
    // DOCUMENT READY
    // =============================================
    $(document).ready(function() {

        // DataTable del modal de productos
        $('#TableListaProductos').dataTable({
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
            responsive: true
        });

        var oTable = $('#TableListaProductos').DataTable();
        $('#search').keyup(function() {
            oTable.search($(this).val()).draw();
        });

        // Click en producto del modal -> agregar fila y traer último precio
        $('#TableListaProductos tbody').on('click', 'tr', function() {
            const $fila = $(this);
            const clave = $fila.data('clave');
            const nombre = $fila.data('nombre');
            const id_producto = $fila.data('id');

            // Evitar duplicados por clave
            if ($(`input[name="clave[]"][value="${clave}"]`).length > 0) {
                alertify.warning('¡Este producto ya está en la lista!');
                return;
            }

            // Agregar fila con data-id para localizarla luego
            const nuevaFila = `
                <tr class="fila-producto" data-id="${id_producto}">
                    <td><input type="number" class="form-control cantidad" value="1" min="1" name="cantidad[]" required></td>
                    <td>${nombre}</td>
                    <td class="text-center">
                        <input type="text" name="precio_venta[]" class="precio_venta" required />
                    </td>
                    <td class="text-center">Total</td>
                    <td>
                        <input type="hidden" name="producto_id[]" value="${id_producto}">
                        <input type="hidden" name="clave[]" value="${clave}">
                        <div class="text-center">
                            <i class="fas fa-trash fa-lg btn-eliminar text-danger" style="cursor:pointer;"></i>
                        </div>
                    </td>
                </tr>
            `;
            $('#tbodyProductos').append(nuevaFila);

            aplicarMaskPrecioVenta();
            actualizarMensajeTablaVacia();

            // NUEVO: traer último precio (por proveedor si ya está seleccionado)
            const proveedorId = $('#clienteId').val() || '';
            obtenerUltimoPrecio(id_producto, proveedorId);

            // Calcular total de la nueva fila (recalculará otra vez al llegar el precio)
            const nuevaFilaElement = $('#tbodyProductos tr').last();
            calcularTotal(nuevaFilaElement);

            FnCerrarModal();
        });

        // Cambios en cantidad o precio -> recalcular
        $(document).on('input', '.cantidad, .precio_venta', function() {
            const fila = $(this).closest('tr');
            calcularTotal(fila);
        });

        // Eliminar producto con confirmación
        $('#tbodyProductos').on('click', '.btn-eliminar', function() {
            const $fila = $(this).closest('.fila-producto');
            const nombreProducto = $fila.find('td:eq(1)').text().trim();

            alertify.confirm(
                'Confirmación',
                `¿Estás seguro de que deseas eliminar el producto: ${nombreProducto}?`,
                function() {
                    $fila.remove();
                    actualizarMensajeTablaVacia();
                    actualizarSumaTotal();
                    alertify.success('Producto eliminado');
                },
                function() {
                    alertify.error('Eliminación cancelada');
                }
            );
        });

        // Prevenir submit con Enter
        $('#formPedidos').on('keypress', function(e) {
            if (e.keyCode === 13) {
                e.preventDefault();
            }
        });

        // VALIDACIÓN DEL FORMULARIO
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
                    required: "Selecciona un cliente"
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

        // Inicializar mensaje de tabla vacía
        actualizarMensajeTablaVacia();

        // Input de cliente/datalist -> setear #clienteId
        $('#clienteInput').on('input', function() {
            const opcion = $('#clientes option').filter(function() {
                return $(this).val() === $('#clienteInput').val();
            });

            if (opcion.length > 0) {
                const clienteId = opcion.data('id');
                $('#clienteId').val(clienteId);
                $('#clienteInput').removeClass('is-invalid').addClass('is-valid');
            } else {
                $('#clienteId').val('');
                $('#clienteInput').removeClass('is-valid').addClass('is-invalid');
            }
        });

        // Regla personalizada: cliente debe existir
        $.validator.addMethod("clientExists", function(value, element) {
            const clienteId = $('#clienteId').val();
            if (!clienteId) {
                const clienteInput = $('#clienteInput').val().trim();
                const clienteValido = $('#clientes option').filter(function() {
                    return $(this).val() === clienteInput;
                }).length > 0;

                if (clienteValido) {
                    const clienteId = $('#clientes option').filter(function() {
                        return $(this).val() === clienteInput;
                    }).data('id');
                    // obtenerDatosCliente(clienteId); // si luego quieres cargar info extra
                    return true;
                } else {
                    mostrarErrorCliente("Cliente no encontrado. Escriba un cliente válido.");
                    return false;
                }
            }
            return true;
        }, "Este cliente no existe.");

    });
</script>