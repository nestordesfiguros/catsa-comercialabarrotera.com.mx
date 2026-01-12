<?php
$clientes = [];
$con = "SELECT * FROM cat_clientes WHERE estatus=1";
$rs = $clsConsulta->consultaGeneral($con);
if ($clsConsulta->numrows > 0) {
    foreach ($rs as $val) {
        $clientes[] = [
            'id' => $val['id'],
            'nombre' => $val['razon_social']
        ];
    }
}

$id_pedido = $cat;

$con = "SELECT cab_pedidos.*, cab_pedidos.id as idPedido, cat_clientes.razon_social
        FROM cab_pedidos
        INNER JOIN cat_clientes ON cab_pedidos.id_cliente = cat_clientes.id
        WHERE cab_pedidos.id = $id_pedido";
$rs = $clsConsulta->consultaGeneral($con);
foreach ($rs as $val) {
    $id = $val['id'];
    $razon_social = $val['razon_social'];
    $num_pedido = $val['num_pedido'];
    $id_cliente = $val['id_cliente'];
    $id_vendedor = $val['id_vendedor'];
    $fecha = $val['fecha'];
    $estatus = $val['estatus'];
    $total = $val['total'];
    $direccion_envio = $val['direccion_envio'];
    $id_usuario = $val['id_usuario'];
}

$con = "SELECT mov_pedidos.*, cat_productos.clave, cat_productos.nombre
        FROM mov_pedidos
        INNER JOIN cat_productos ON mov_pedidos.id_producto = cat_productos.id_producto
        WHERE mov_pedidos.id_pedido = $id_pedido";
$rs = $clsConsulta->consultaGeneral($con);

$productos_pedido = [];
if ($rs) {
    foreach ($rs as $val) {
        $productos_pedido[] = [
            'id' => $val['id'],
            'id_producto' => $val['id_producto'],
            'clave' => $val['clave'],
            'nombre' => $val['nombre'],
            'precio' => $val['precio'],
            'cantidad' => $val['cantidad']
        ];
    }
}
?>
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="pedidos">Pedidos</a></li>
            <li class="breadcrumb-item active" aria-current="page"> Modificar</li>

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
                                    <div class="col-2">
                                        <div class="form-outline">
                                            <input type="text" class="form-control text-end" value="<?= $num_pedido; ?>" disabled>
                                            <label class="form-label">No pedido</label>
                                        </div>
                                    </div>
                                    <div class="col-2">
                                        <div class="form-outline">
                                            <input type="text" class="form-control text-end" value="<?= $fecha ?>" disabled>
                                            <label class="form-label">Fecha</label>
                                        </div>
                                    </div>
                                    <div class="col-8">
                                        <div class="form-group">
                                            <div class="form-outline">
                                                <input type="text" id="clienteInput" class="form-control" name="cliente_nombre" list="clientes" placeholder="Selecciona un cliente..." autocomplete="off" value="<?= $razon_social; ?>" disabled />
                                                <label class="form-label">Cliente</label>
                                            </div>
                                            <datalist id="clientes">
                                                <?php foreach ($clientes as $cliente): ?>
                                                    <option value="<?= htmlspecialchars($cliente['nombre']) ?>" data-id="<?= $cliente['id'] ?>"></option>
                                                <?php endforeach; ?>
                                            </datalist>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-outline">
                                            <input type="text" name="direccion_envio" class="form-control" value="<?= $direccion_envio; ?>">
                                            <label class="form-label">Domicilio de entrega</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="col-12">
                                    <div class="row">
                                        <div class="col-6">
                                            <button type="button" class="btn btn-primary btn-sm" onclick="FnAgregarModal();">Agregar Producto</button>
                                        </div>
                                        <div class="col-6 text-end">
                                            <button type="submit" class="btn btn-success btn-sm">Guardar</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <table id="tablaProductos" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Cantidad</th>
                                                <th>Producto</th>
                                                <th>Precio</th>
                                                <th>Total</th>
                                                <th>Borrar</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbodyProductos">
                                            <?php if (empty($productos_pedido)): ?>
                                                <tr id="filaVacia">
                                                    <td colspan="5" class="text-center">Ningún producto agregado</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($productos_pedido as $producto): ?>
                                                    <tr class="fila-producto" data-id="<?= $producto['id'] ?>">
                                                        <td>
                                                            <input type="number" class="form-control cantidad"
                                                                value="<?= $producto['cantidad'] ?>" min="1" name="cantidad[]" required>
                                                        </td>
                                                        <td><?= htmlspecialchars($producto['nombre']) ?></td>
                                                        <td>
                                                            <input type="number" step="0.01" class="form-control precio"
                                                                name="precio_venta[]" value="<?= $producto['precio'] ?>" required>
                                                        </td>
                                                        <td class="totalFila text-end">
                                                            <?= '$' . number_format((float)($producto['precio'] ?? 0) * (float)($producto['cantidad'] ?? 0), 2) ?>

                                                        </td>
                                                        <td>
                                                            <input type="hidden" name="producto_id[]" value="<?= $producto['id_producto'] ?>">
                                                            <input type="hidden" name="clave[]" value="<?= $producto['clave'] ?>">
                                                            <button type="button" class="btn btn-danger btn-sm btn-eliminar">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>

                                        <tfoot>
                                            <tr>
                                                <th colspan="3" class="text-end">Total general:</th>
                                                <th id="totalGeneral" class="text-end">$0.00</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <input type="hidden" name="id_pedido" value="<?= $cat ?>">
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
                            <th>Precio Venta</th>
                            <th>Inventario</th>
                            <th>Almacén</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Consulta que trae inventario por producto y almacén
                        $con = "
                            SELECT 
                                p.id_producto,
                                p.clave,
                                p.nombre,
                                IFNULL(p.precio_sugerido,0) AS precio_venta,
                                IFNULL(i.cantidad,0) AS existencia,
                                a.almacen
                            FROM cat_productos p
                            LEFT JOIN inventarios i 
                                ON i.id_producto = p.id_producto
                            LEFT JOIN cat_almacenes a
                                ON i.id_almacen = a.id
                            WHERE p.estatus = 1
                            ORDER BY p.nombre
                        ";
                        $rs = $clsConsulta->consultaGeneral($con);

                        foreach ($rs as $v => $val) {
                            $id_producto = $val['id_producto'];
                            $clave = $val['clave'];
                            $nombre = htmlspecialchars($val['nombre']);
                            $precio_venta = $val['precio_venta'];
                            $existencia = $val['existencia'];
                            $almacen = htmlspecialchars($val['almacen'] ?? 'Sin almacén');

                            echo "<tr 
                                data-id='{$id_producto}' 
                                data-clave='{$clave}' 
                                data-nombre='{$nombre}' 
                                data-precio_venta='{$precio_venta}'
                                data-existencia='{$existencia}'
                                data-almacen='{$almacen}'>
                                <td>{$clave}</td>
                                <td><span class='text-primary' style='cursor:pointer;'>{$nombre}</span></td>
                                <td>$" . number_format($precio_venta, 2) . "</td>
                                <td class='text-center'>{$existencia}</td>
                                <td>{$almacen}</td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>


    <script>
        // =============================================
        // FUNCIONES PRINCIPALES
        // =============================================

        function FnAgregarModal() {
            $('#modalAddProductos').modal('show');
        }

        function FnCerrarModal() {
            $('#modalAddProductos').modal('hide');
        }

        function actualizarMensajeTablaVacia() {
            const $tbody = $('#tbodyProductos');
            if ($tbody.find('tr.fila-producto').length === 0) {
                $tbody.html('<tr id="filaVacia"><td colspan="5" class="text-center">No hay productos agregados</td></tr>');
            } else {
                $('#filaVacia').remove();
            }
        }

        function actualizarTotalGeneral() {
            let total = 0;
            $('#tbodyProductos tr.fila-producto').each(function() {
                const cantidad = parseFloat($(this).find('.cantidad').val()) || 0;
                const precio = parseFloat($(this).find('.precio').val()) || 0;
                total += cantidad * precio;
            });
            $('#totalGeneral').text(total.toLocaleString('es-MX', {
                style: 'currency',
                currency: 'MXN'
            }));
        }

        // =============================================
        // DOCUMENT READY
        // =============================================
        $(document).ready(function() {
            actualizarMensajeTablaVacia();
            actualizarTotalGeneral();

            // Recalcular totales al cambiar cantidad o precio
            $('#tbodyProductos').on('input', '.cantidad, .precio', function() {
                const $fila = $(this).closest('tr');
                const cantidad = parseFloat($fila.find('.cantidad').val()) || 0;
                const precio = parseFloat($fila.find('.precio').val()) || 0;
                const total = cantidad * precio;
                $fila.find('.totalFila').text(`$${total.toFixed(2)}`);
                actualizarTotalGeneral();
            });

            // Eliminar producto
            $('#tbodyProductos').on('click', '.btn-eliminar', function() {
                const $fila = $(this).closest('tr');
                const nombre = $fila.find('td:eq(1)').text().trim();
                alertify.confirm(
                    'Confirmación',
                    `¿Deseas eliminar el producto: <b>${nombre}</b>?`,
                    function() {
                        $fila.remove();
                        actualizarMensajeTablaVacia();
                        actualizarTotalGeneral();
                        alertify.success('Producto eliminado');
                    },
                    function() {
                        alertify.error('Eliminación cancelada');
                    }
                );
            });

            // Inicializar DataTable del modal
            var oTable = $('#TableListaProductos').DataTable({
                ordering: true,
                pageLength: 10,
                dom: "<'row'<'col-sm-6'l><'col-sm-6'p>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-6'l><'col-sm-6'p>>",
                language: {
                    url: "assets/datatables/Spanish.json",
                    sSearch: '<i class="fa fa-search" aria-hidden="true"></i> Buscar'
                },
                responsive: true
            });

            $('#search').keyup(function() {
                oTable.search($(this).val()).draw();
            });

            // Al hacer clic sobre un producto del modal
            // Al hacer clic sobre un producto del modal
            $('#TableListaProductos tbody').on('click', 'tr', function() {
                const $fila = $(this);
                const clave = $fila.data('clave');
                const nombre = $fila.data('nombre');
                const id_producto = $fila.data('id');
                const precio_venta = parseFloat($fila.data('precio_venta')) || 0;

                // Evitar duplicados
                if ($(`#tbodyProductos input[name="producto_id[]"][value="${id_producto}"]`).length > 0) {
                    alertify.warning('¡Este producto ya está en la lista!');
                    return;
                }

                // Crear fila con inputs que SÍ se envían
                const nuevaFila = `
                <tr class="fila-producto">
                    <td>
                        <input type="number" class="form-control cantidad" value="1" min="1" name="cantidad[]" required>
                    </td>
                    <td>${nombre}</td>
                    <td>
                        <input type="number" class="form-control precio" name="precio_venta[]" step="0.01" value="${precio_venta.toFixed(2)}" required>
                    </td>
                    <td class="totalFila">${precio_venta.toLocaleString('es-MX',{style:'currency',currency:'MXN'})}</td>
                    <td>
                        <input type="hidden" name="producto_id[]" value="${id_producto}">
                        <input type="hidden" name="clave[]" value="${clave}">
                        <button type="button" class="btn btn-danger btn-sm btn-eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;

                $('#tbodyProductos').append(nuevaFila);
                actualizarMensajeTablaVacia();
                actualizarTotalGeneral();
                FnCerrarModal();
            });

            // Recalcular total por fila al cambiar cantidad o precio
            $('#tbodyProductos').on('input', '.cantidad, .precio', function() {
                const $fila = $(this).closest('tr');
                const cantidad = parseFloat($fila.find('.cantidad').val()) || 0;
                const precio = parseFloat($fila.find('.precio').val()) || 0;

                const total = cantidad * precio;
                $fila.find('.totalFila').text(
                    total.toLocaleString('es-MX', {
                        style: 'currency',
                        currency: 'MXN'
                    })
                );

                actualizarTotalGeneral();
            });



            // =============================================
            // GUARDAR PEDIDO CON CONFIRMACIÓN
            // =============================================
            $('#formPedidos').validate({
                rules: {
                    direccion_envio: {
                        required: true
                    }
                },
                messages: {
                    direccion_envio: {
                        required: "Escribe el domicilio de entrega"
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
                    // Confirmación antes de guardar
                    alertify.confirm(
                        'Confirmación',
                        '¿Deseas guardar los cambios en este pedido?',
                        function() {
                            const formData = new FormData(form);

                            $.ajax({
                                url: 'ajax/pedidos/modificar.php',
                                method: 'POST',
                                data: formData,
                                processData: false,
                                contentType: false,
                                dataType: 'json',
                                success: function(response) {
                                    if (response === 'success' || (response.success && response.success === true)) {
                                        alertify.success('Pedido modificado correctamente.');
                                        // setTimeout(() => {
                                        //     window.location.href = 'pedidos';
                                        // }, 800);
                                    } else {
                                        // Muestra mensaje de error detallado
                                        let msg = response.message ? response.message : 'No se pudo guardar el pedido';
                                        alertify.alert('<b class="text-danger">Error</b>', msg);
                                    }
                                },
                                error: function(xhr) {
                                    alertify.alert('<b class="text-danger">Error</b>', 'Error de conexión: ' + xhr.statusText);
                                }
                            });
                        },
                        function() {
                            alertify.error('Operación cancelada');
                        }
                    ).set('labels', {
                        ok: 'Sí',
                        cancel: 'No'
                    });
                }
            });

        });
    </script>