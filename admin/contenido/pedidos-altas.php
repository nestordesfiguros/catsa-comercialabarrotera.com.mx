<?php // contenido/pedidos-altas.php 
?>
<?php
// Se asume $_SESSION['id_empresa'] ya está disponible y $fecha_bd también
$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
if ($idEmpresa <= 0) {
    header("Location: inicio");
    exit;
}

// Almacenes de la empresa (compat: el sistema espera "nombre")
$conAlm = "
    SELECT 
        id,
        almacen AS nombre
    FROM cat_almacenes
    WHERE id_empresa = {$idEmpresa}
      AND estatus = 1
    ORDER BY id ASC
";
$rsAlm = $clsConsulta->consultaGeneral($conAlm);

$almacenDefaultId = 0;
if ($clsConsulta->numrows > 0 && isset($rsAlm[1]['id'])) {
    $almacenDefaultId = (int)$rsAlm[1]['id'];
}
?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="pedidos">Pedidos</a></li>
            <li class="breadcrumb-item active" aria-current="page">Altas</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row ">
            <div class="d-flex justify-content-center align-items-center w-100">
                <div class="col-12 col-md-10">
                    <div class="card">
                        <form action="" method="post" id="formPedidos">
                            <div class="card-header">
                                <h5 class="mb-3">Nuevo Pedido</h5>

                                <div class="row g-3">

                                    <!-- FECHA -->
                                    <div class="col-12 col-md-3">
                                        <label for="datepicker" class="form-label">Fecha</label>
                                        <input type="date" name="fecha" class="form-control" id="datepicker" value="<?= $fecha_bd ?>">
                                        <div id="errorFecha" class="invalid-feedback mt-2" style="display: none;">
                                            Escribe una Fecha
                                        </div>
                                    </div>

                                    <!-- ALMACÉN -->
                                    <div class="col-12 col-md-3">
                                        <label class="form-label"><span class="text-danger">*</span> Almacén</label>
                                        <select class="form-select" id="almacenSelect" name="id_almacen" required>
                                            <?php
                                            if ($clsConsulta->numrows > 0) {
                                                foreach ($rsAlm as $k => $alm) {
                                                    if (!is_array($alm) || !isset($alm['id'])) continue;
                                                    $idA = (int)$alm['id'];
                                                    $nom = $alm['nombre'] ?? ('Almacén ' . $idA);
                                                    $sel = ($idA === $almacenDefaultId) ? 'selected' : '';
                                                    echo '<option value="' . $idA . '" ' . $sel . '>' . htmlspecialchars($nom) . '</option>';
                                                }
                                            } else {
                                                echo '<option value="" selected disabled>No hay almacenes para esta empresa</option>';
                                            }
                                            ?>
                                        </select>
                                        <div class="form-text">Se usa para existencias y reservas.</div>
                                    </div>

                                    <!-- CLIENTE (MODAL) -->
                                    <div class="col-12 col-md-3">
                                        <label class="form-label"><span class="text-danger">*</span> Cliente</label>
                                        <button type="button" class="btn btn-primary w-100" onclick="abrirModalClientes();">
                                            Seleccionar Cliente
                                        </button>

                                        <div id="clienteSeleccionado" class="mt-2 fw-bold text-success"></div>
                                        <div id="clienteListaPrecio" class="small text-muted"></div>
                                        <div id="errorCliente" class="text-danger mt-1" style="display:none;"></div>
                                    </div>

                                    <!-- VENDEDOR (MODAL) -->
                                    <div class="col-12 col-md-3">
                                        <label class="form-label">Vendedor</label>
                                        <button type="button" class="btn btn-info w-100" onclick="abrirModalVendedores();">
                                            Seleccionar Vendedor
                                        </button>
                                        <div id="vendedorSeleccionado" class="mt-2 fw-bold text-primary"></div>
                                    </div>

                                    <!-- DOMICILIO -->
                                    <div class="col-12">
                                        <label for="domicilio" class="form-label">Domicilio de entrega</label>
                                        <input type="text" name="direccion_envio" class="form-control" id="domicilio" autocomplete="off">
                                    </div>

                                </div>
                            </div>

                            <div class="card-body">
                                <div class="col-12 mb-3">
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <button type="button" class="btn btn-primary btn-sm" onclick="FnAgregarModal();">
                                                Agregar Producto
                                            </button>
                                        </div>
                                        <div class="col-12 col-md-6 text-md-end mt-2 mt-md-0">
                                            <button type="submit" class="btn btn-success btn-sm" id="btnGuardarPedido">
                                                Guardar
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive mt-3">
                                    <table id="tablaProductos" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Cantidad</th>
                                                <th>Producto</th>
                                                <th class="text-end">Precio</th>
                                                <th class="text-center">Borrar</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbodyProductos">
                                            <tr id="filaVacia">
                                                <td colspan="4">Ningún producto agregado</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- HIDDEN FIELDS -->
                            <input type="hidden" name="cliente_id" id="clienteId">
                            <input type="hidden" name="id_vendedor" id="vendedorId">

                            <!-- lista de precios del cliente (1..5) -->
                            <input type="hidden" name="lista_precios" id="listaPrecios" value="1">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========================================================= -->
<!-- MODAL SPINNER / BLOQUEO -->
<!-- ========================================================= -->
<div class="modal fade" id="modalSpiner" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-body text-center p-4">
                <div class="spinner-border" role="status" aria-hidden="true"></div>
                <div class="mt-3 fw-semibold">Procesando…</div>
                <div class="text-muted small">No cierres esta ventana.</div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================= -->
<!-- MODAL SELECCIONAR CLIENTE -->
<!-- ========================================================= -->
<div class="modal fade" id="modalSeleccionCliente" tabindex="-1" aria-labelledby="modalSeleccionClienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Seleccionar Cliente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <div class="row mb-3">
                    <div class="col-12 col-md-4 ms-auto">
                        <label class="form-label" for="buscarCliente">Buscar cliente</label>
                        <input type="text" id="buscarCliente" class="form-control" autocomplete="off">
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="tablaClientesPedido" class="table table-striped table-bordered w-100">
                        <thead>
                            <tr>
                                <th>Nombre Comercial</th>
                                <th>RFC</th>
                                <th>Teléfono</th>
                                <th class="text-center">Seleccionar</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- ========================================================= -->
<!-- MODAL SELECCIONAR VENDEDOR -->
<!-- ========================================================= -->
<div class="modal fade" id="modalSeleccionVendedor" tabindex="-1" aria-labelledby="modalSeleccionVendedorLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Seleccionar Vendedor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <div class="row mb-3">
                    <div class="col-12 col-md-4 ms-auto">
                        <label class="form-label" for="buscarVendedor">Buscar vendedor</label>
                        <input type="text" id="buscarVendedor" class="form-control" autocomplete="off">
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="tablaVendedoresPedido" class="table table-striped table-bordered w-100">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Teléfono</th>
                                <th class="text-center">Seleccionar</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- ========================================================= -->
<!-- MODAL AGREGAR PRODUCTOS -->
<!-- ========================================================= -->
<div class="modal fade" id="modalAddProductos" tabindex="-1" aria-labelledby="modalAddProductosLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="modalAddProductosLabel">Productos</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="FnCerrarModal();"></button>
            </div>
            <div class="modal-body">

                <div class="mb-3 p-3">
                    <div class="row d-flex justify-content-end">
                        <div class="col-12 col-md-6">
                            <label for="buscarProducto" class="form-label">Buscar</label>
                            <input type="text" id="buscarProducto" class="form-control" autocomplete="off">
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="TableListaProductos" class="table table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th>Imagen</th>
                                <th>Clave</th>
                                <th>Producto</th>
                                <th class="text-end" id="thPrecioLista">Precio (Lista 1)</th>
                                <th class="text-end">Disponible</th>
                                <th class="text-center">Agregar</th>
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
    // Evitar submit con ENTER en el formulario, excepto en TEXTAREA
    document.getElementById('formPedidos').addEventListener('keydown', function(event) {
        if (event.key === 'Enter' && event.target.tagName !== 'TEXTAREA') {
            event.preventDefault();
        }
    });

    let isSavingPedido = false;

    function lockUI(locked) {
        const $btn = $('#btnGuardarPedido');
        if (locked) {
            $('#modalSpiner').modal('show');
            $btn.prop('disabled', true);
            $('#formPedidos :input').prop('disabled', true);
        } else {
            $('#modalSpiner').modal('hide');
            $('#formPedidos :input').prop('disabled', false);
            $btn.prop('disabled', false);
        }
    }

    function FnAgregarModal() {
        // exige cliente y almacén antes de abrir productos
        const clienteId = parseInt($('#clienteId').val() || '0', 10);
        const almacenId = parseInt($('#almacenSelect').val() || '0', 10);

        if (almacenId <= 0) {
            $('#errorCliente').text('Selecciona un almacén.').show();
            return;
        }
        if (clienteId <= 0) {
            $('#errorCliente').text('Selecciona un cliente antes de agregar productos.').show();
            return;
        }

        $('#modalAddProductos').modal('show');
        // fuerza recarga para asegurar existencia por almacén + lista
        $('#TableListaProductos').DataTable().ajax.reload(null, true);
    }

    function FnCerrarModal() {
        $('#modalAddProductos').modal('hide');
    }

    function abrirModalClientes() {
        $('#modalSeleccionCliente').modal('show');
    }

    function abrirModalVendedores() {
        $('#modalSeleccionVendedor').modal('show');
    }

    function actualizarMensajeTablaVacia() {
        const $tbody = $('#tbodyProductos');
        if ($tbody.children('tr.fila-producto').length === 0) {
            if ($('#filaVacia').length === 0) {
                $tbody.append('<tr id="filaVacia"><td colspan="4">Ningún producto agregado</td></tr>');
            }
        } else {
            $('#filaVacia').remove();
        }
    }

    // Cliente: obtener domicilio + lista de precios (por empresa)
    function obtenerDatosCliente(clienteId) {
        const almacenId = parseInt($('#almacenSelect').val() || '0', 10);

        $.ajax({
            url: 'ajax/pedidos/busca-cliente.php',
            method: 'GET',
            data: {
                idCliente: clienteId,
                id_almacen: almacenId
            },
            dataType: 'json',
            success: function(r) {
                if (r && r.error === false) {

                    const direccion = `${r.calle || ''} ${r.num_ext || ''} ${r.num_int ? ('Int. ' + r.num_int) : ''}, ${r.colonia || ''}, ${r.cp || ''}, ${r.nmunicipio || ''}, ${r.nestado || ''}`
                        .replace(/\n/g, '')
                        .replace(/  +/g, ' ')
                        .trim();

                    $('#domicilio').val(direccion).removeClass('is-invalid').addClass('is-valid');

                    // lista de precios (1..5)
                    const lista = parseInt(r.lista_precios || '1', 10);
                    const listaNombre = r.lista_precios_nombre || ('Lista ' + lista);

                    $('#listaPrecios').val(lista);
                    $('#clienteListaPrecio').text('Lista de precios: ' + listaNombre);
                    $('#thPrecioLista').text('Precio (' + listaNombre + ')');

                    $('#errorCliente').hide();

                    // recargar productos según almacén + lista
                    $('#TableListaProductos').DataTable().ajax.reload(null, true);
                } else {
                    $('#errorCliente').text('Cliente no encontrado o no pertenece a la empresa actual.').show();
                }
            },
            error: function() {
                $('#errorCliente').text('Error al obtener datos del cliente.').show();
            }
        });
    }

    function seleccionarCliente(id, nombre) {
        $('#clienteId').val(id);
        $('#clienteSeleccionado').text(nombre);
        $('#modalSeleccionCliente').modal('hide');
        $('#errorCliente').hide();

        // reinicia productos del pedido si ya había (evita mezclar listas)
        $('#tbodyProductos').empty();
        actualizarMensajeTablaVacia();

        obtenerDatosCliente(id);
    }

    function seleccionarVendedor(id, nombre) {
        $('#vendedorId').val(id);
        $('#vendedorSeleccionado').text(nombre);
        $('#modalSeleccionVendedor').modal('hide');
    }

    function agregarProducto(id, clave, nombre, precio) {
        if ($("input[name='producto_id[]'][value='" + id + "']").length > 0) {
            alertify.alert('Aviso', 'Este producto ya está en la lista.');
            return;
        }

        const p = parseFloat(precio || 0).toFixed(2);

        const nuevaFila = `
        <tr class="fila-producto">
            <td style="width:120px;">
                <input type="number"
                       class="form-control cantidad"
                       value="1"
                       min="1"
                       name="cantidad[]"
                       required>
            </td>
            <td>${nombre}</td>
            <td style="width:180px;">
                <input type="number"
                       class="form-control precio text-end"
                       value="${p}"
                       min="${p}"
                       step="0.01"
                       name="precio_venta[]"
                       required>
            </td>
            <td class="text-center" style="width:90px;">
                <input type="hidden" name="producto_id[]" value="${id}">
                <button type="button" class="btn btn-danger btn-sm btn-eliminar" aria-label="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>`;

        $('#tbodyProductos').append(nuevaFila);
        actualizarMensajeTablaVacia();
        FnCerrarModal();
    }

    function guardarPedido(form) {
        if (isSavingPedido) return;

        const formData = new FormData(form);

        alertify.confirm('Confirmar', '¿Deseas guardar este movimiento?',
            function() {
                isSavingPedido = true;
                lockUI(true);

                $.ajax({
                    url: 'ajax/pedidos/guardar.php',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(resp) {
                        const r = (resp || '').trim();

                        if (r === 'success') {
                            location.href = "pedidos";
                            return;
                        }

                        isSavingPedido = false;
                        lockUI(false);

                        if (r.indexOf('error:stock_insuficiente:') === 0) {
                            const partes = r.split(':');
                            const nombreProd = partes[2] || 'Producto';
                            const disponible = partes[3] || '0';
                            alertify.alert('Stock insuficiente', 'No hay suficiente existencia para <b>' + nombreProd + '</b>. Disponible: ' + disponible);
                            return;
                        }

                        if (r.indexOf('error:precio_invalido:') === 0) {
                            const partes = r.split(':');
                            const nombreProd = partes[2] || 'Producto';
                            const minimo = partes[3] || '0.00';
                            alertify.alert('Precio inválido', 'El precio de <b>' + nombreProd + '</b> no puede ser menor a ' + minimo);
                            return;
                        }

                        if (r === 'error_cliente') {
                            alertify.alert('Aviso', 'Cliente inválido o no pertenece a la empresa.');
                            return;
                        }

                        if (r === 'error_almacen') {
                            alertify.alert('Aviso', 'Almacén inválido o no pertenece a la empresa.');
                            return;
                        }

                        alertify.alert('Aviso', 'No se pudo guardar. Revisa los datos del pedido.');
                    },
                    error: function(xhr) {
                        isSavingPedido = false;
                        lockUI(false);
                        alertify.alert('Error', 'Error de conexión: ' + (xhr.statusText || ''));
                    }
                });
            },
            function() {}
        );
    }

    $(document).ready(function() {

        // si cambia almacén: refrescar lista de productos y revalidar cliente (nombre de lista)
        $('#almacenSelect').on('change', function() {
            const clienteId = parseInt($('#clienteId').val() || '0', 10);

            // refresca productos del modal
            $('#TableListaProductos').DataTable().ajax.reload(null, true);

            // refresca nombre de lista (si ya hay cliente)
            if (clienteId > 0) {
                obtenerDatosCliente(clienteId);
            }

            // por seguridad, vacía productos del pedido (cambió stock/almacén)
            $('#tbodyProductos').empty();
            actualizarMensajeTablaVacia();
        });

        // DataTable Productos (SERVER-SIDE real)
        const dtProd = $('#TableListaProductos').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: 'ajax/pedidos/tabla-productos-pedido.php',
                type: 'GET',
                data: function(d) {
                    d.id_almacen = $('#almacenSelect').val();
                    d.cliente_id = $('#clienteId').val();
                }
            },
            ordering: true,
            pageLength: 10,
            dom: "<'row'<'col-sm-4'l><'col-sm-1'><'col-sm-7'p>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-4'l><'col-sm-1'><'col-sm-7'p>>",
            language: {
                url: "assets/datatables/Spanish.json",
                sSearch: '<i class="fa fa-search" aria-hidden="true"></i> Buscar'
            },
            responsive: true,
            searchDelay: 350
        });

        $('#buscarProducto').keyup(function() {
            dtProd.search($(this).val()).draw();
        });

        // DataTable Clientes (SERVER-SIDE real)
        const dtCli = $('#tablaClientesPedido').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: 'ajax/pedidos/tabla-clientes-pedido.php',
                type: 'GET'
            },
            ordering: true,
            pageLength: 10,
            dom: "<'row'<'col-sm-4'l><'col-sm-1'><'col-sm-7'p>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-4'l><'col-sm-1'><'col-sm-7'p>>",
            language: {
                url: "assets/datatables/Spanish.json",
                sSearch: '<i class="fa fa-search" aria-hidden="true"></i> Buscar'
            },
            responsive: true,
            searchDelay: 350
        });

        $('#buscarCliente').keyup(function() {
            dtCli.search($(this).val()).draw();
        });

        // DataTable Vendedores (SERVER-SIDE real)
        const dtVend = $('#tablaVendedoresPedido').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: 'ajax/pedidos/tabla-vendedores-pedido.php',
                type: 'GET'
            },
            ordering: true,
            pageLength: 10,
            dom: "<'row'<'col-sm-4'l><'col-sm-1'><'col-sm-7'p>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-4'l><'col-sm-1'><'col-sm-7'p>>",
            language: {
                url: "assets/datatables/Spanish.json",
                sSearch: '<i class="fa fa-search" aria-hidden="true"></i> Buscar'
            },
            responsive: true,
            searchDelay: 350
        });

        $('#buscarVendedor').keyup(function() {
            dtVend.search($(this).val()).draw();
        });

        // Validar precio mínimo (no toast)
        $('#tbodyProductos').on('change', '.precio', function() {
            const $input = $(this);
            const precioMinimo = parseFloat($input.attr('min')) || 0;
            const precioActual = parseFloat($input.val()) || 0;

            if (precioActual < precioMinimo) {
                $input.val(precioMinimo.toFixed(2)).focus();
                alertify.alert('Aviso', 'El precio no puede ser menor a ' + precioMinimo.toFixed(2));
            }
        });

        // Eliminar producto
        $('#tbodyProductos').on('click', '.btn-eliminar', function() {
            const $fila = $(this).closest('.fila-producto');
            const nombreProducto = $fila.find('td:eq(1)').text().trim();

            alertify.confirm(
                'Confirmación',
                '¿Deseas eliminar el producto: <b>' + nombreProducto + '</b>?',
                function() {
                    $fila.remove();
                    actualizarMensajeTablaVacia();
                },
                function() {}
            );
        });

        actualizarMensajeTablaVacia();

        // Validación del form
        $('#formPedidos').validate({
            ignore: [],
            rules: {
                fecha: {
                    required: true
                },
                cliente_id: {
                    required: true
                },
                direccion_envio: {
                    required: true
                },
                id_almacen: {
                    required: true
                }
            },
            messages: {
                fecha: {
                    required: "Escribe una fecha"
                },
                cliente_id: {
                    required: "Selecciona un cliente"
                },
                direccion_envio: {
                    required: "Escribe el domicilio de entrega"
                },
                id_almacen: {
                    required: "Selecciona un almacén"
                }
            },
            errorElement: 'div',
            errorPlacement: function(error, element) {
                if (element.attr('name') === 'cliente_id') {
                    $('#errorCliente').text(error.text()).show();
                } else {
                    error.addClass('invalid-feedback');
                    error.insertAfter(element);
                }
            },
            highlight: function(element) {
                if (element.name !== 'cliente_id') {
                    $(element).addClass('is-invalid').removeClass('is-valid');
                }
            },
            unhighlight: function(element) {
                if (element.name !== 'cliente_id') {
                    $(element).removeClass('is-invalid').addClass('is-valid');
                }
            },
            submitHandler: function(form) {
                guardarPedido(form);
            }
        });
    });
</script>