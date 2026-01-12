<?php
// contenido/almacen-salidas-altas.php

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
$fecha_bd = date('Y-m-d');

// Almacenes por empresa
$almacenes = $clsConsulta->consultaGeneral("SELECT id, almacen FROM cat_almacenes WHERE estatus=1 AND id_empresa={$idEmpresa} ORDER BY almacen ASC");
if (!is_array($almacenes) || $clsConsulta->numrows <= 0) $almacenes = [];

// Clientes por empresa (si tu tabla se llama cat_clientes; ajusta campos si varían)
$clientes = $clsConsulta->consultaGeneral("SELECT id, razon_social FROM cat_clientes WHERE estatus=1 AND id_empresa={$idEmpresa} ORDER BY razon_social ASC");
if (!is_array($clientes) || $clsConsulta->numrows <= 0) $clientes = [];
?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="almacen-salidas">Salidas de almacén</a></li>
            <li class="breadcrumb-item active" aria-current="page">Alta</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="d-flex justify-content-center align-items-center w-100">
                <div class="col-10">
                    <div class="card">

                        <form id="formSalida" method="post" action="">
                            <div class="card-header">
                                <div class="row g-3">

                                    <div class="col-12 col-md-2">
                                        <div class="form-outline">
                                            <input type="date" name="fecha" class="form-control" value="<?= $fecha_bd ?>" required>
                                            <label class="form-label">Fecha</label>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-outline">
                                            <select name="almacen_id" id="almacenSelect" class="form-select" required>
                                                <option value="">Selecciona almacén origen...</option>
                                                <?php foreach ($almacenes as $a): ?>
                                                    <option value="<?= (int)$a['id'] ?>"><?= htmlspecialchars($a['almacen']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label class="form-label">Almacén origen</label>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-3">
                                        <div class="form-outline">
                                            <select name="tipo_salida" class="form-select" required>
                                                <option value="venta">Venta</option>
                                                <option value="traspaso">Traspaso</option>
                                                <option value="ajuste">Ajuste</option>
                                                <option value="consumo_interno">Consumo interno</option>
                                            </select>
                                            <label class="form-label">Tipo</label>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-3">
                                        <div class="form-outline">
                                            <input type="text" id="clienteInput" class="form-control" list="clientes" placeholder="(Opcional) Cliente/destino..." autocomplete="off" />
                                            <label class="form-label">Destino (Cliente)</label>
                                            <datalist id="clientes">
                                                <?php foreach ($clientes as $c): ?>
                                                    <option value="<?= htmlspecialchars($c['razon_social']) ?>" data-id="<?= (int)$c['id'] ?>"></option>
                                                <?php endforeach; ?>
                                            </datalist>
                                            <input type="hidden" name="cliente_id" id="clienteId">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-outline">
                                            <input type="text" name="referencia" class="form-control" placeholder="Ej: Remisión 123 / Ticket 456">
                                            <label class="form-label">Referencia</label>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-8">
                                        <div class="form-outline">
                                            <input type="text" name="observaciones" class="form-control" placeholder="Notas / observaciones">
                                            <label class="form-label">Observaciones</label>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-6">
                                        <button type="button" class="btn btn-primary btn-sm" onclick="FnAbrirModalProductos();">
                                            Agregar Producto
                                        </button>
                                    </div>
                                    <div class="col-6 text-end">
                                        <p class="mb-0"><b>Total piezas:</b> <span id="totalPiezas">0</span></p>
                                    </div>
                                </div>

                                <div class="mt-2">
                                    <table class="table table-bordered table-striped" id="tablaProductosSalida">
                                        <thead class="bg-dark text-white">
                                            <tr>
                                                <th class="text-center" style="width:140px;">Cantidad</th>
                                                <th>Producto</th>
                                                <th class="text-center" style="width:140px;">Existencia</th>
                                                <th class="text-center" style="width:120px;">Unidad</th>
                                                <th class="text-center" style="width:80px;">Borrar</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbodySalida">
                                            <tr id="filaVacia">
                                                <td colspan="5" class="text-center">Ningún producto agregado</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                            </div>

                            <div class="card-footer">
                                <button type="button" class="btn btn-secondary" onclick="history.back()">Cancelar</button>
                                <button type="submit" class="btn btn-success" id="btnGuardar">Guardar salida</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Productos -->
<div class="modal fade" id="modalProductos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Productos (según existencia del almacén)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <div class="form-outline" data-mdb-input-init>
                        <input type="text" id="searchProductos" class="form-control" />
                        <label class="form-label">Buscar producto</label>
                    </div>
                    <small class="text-muted d-block mt-2">
                        Primero selecciona el almacén. Sólo se listan productos con existencia &gt; 0 en ese almacén.
                    </small>
                </div>

                <table id="tablaModalProductos" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>Clave</th>
                            <th>Producto</th>
                            <th class="text-end">Existencia</th>
                            <th>Unidad</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<script>
    let dtProductos = null;

    function FnAbrirModalProductos() {
        const almacenId = $('#almacenSelect').val();
        if (!almacenId) {
            alertify.error('Selecciona primero el almacén origen.');
            return;
        }

        $('#modalProductos').modal('show');

        // Inicializa o recarga DataTable del modal
        if (!dtProductos) {
            dtProductos = $('#tablaModalProductos').DataTable({
                ajax: {
                    url: 'ajax/almacen-salidas/productos-por-almacen.php',
                    type: 'POST',
                    data: function(d) {
                        d.id_almacen = $('#almacenSelect').val();
                    }
                },
                ordering: true,
                pageLength: 10,
                language: {
                    url: "assets/datatables/Spanish.json"
                },
                responsive: true,
                columns: [{
                        data: 'clave'
                    },
                    {
                        data: 'nombre'
                    },
                    {
                        data: 'existencia',
                        className: 'text-end'
                    },
                    {
                        data: 'unidad'
                    }
                ]
            });

            // custom search
            $('#searchProductos').on('input', function() {
                dtProductos.search($(this).val()).draw();
            });

            // click row -> agregar
            $('#tablaModalProductos tbody').on('click', 'tr', function() {
                const data = dtProductos.row(this).data();
                if (!data) return;

                agregarProductoSalida(data);
                $('#modalProductos').modal('hide');
            });
        } else {
            dtProductos.ajax.reload();
            $('#searchProductos').val('');
            dtProductos.search('').draw();
        }
    }

    function agregarProductoSalida(prod) {
        // Evitar duplicados
        if ($(`#tbodySalida tr.fila-prod[data-id="${prod.id_producto}"]`).length > 0) {
            alertify.warning('Este producto ya está agregado.');
            return;
        }

        const fila = `
            <tr class="fila-prod" data-id="${prod.id_producto}">
                <td>
                    <input type="number"
                           class="form-control cantidad"
                           name="cantidad[]"
                           value="1"
                           min="1"
                           max="${prod.existencia}"
                           required>
                    <input type="hidden" name="producto_id[]" value="${prod.id_producto}">
                </td>
                <td>
                    <b>${prod.nombre}</b><br>
                    <small class="text-muted">Clave: ${prod.clave}</small>
                </td>
                <td class="text-end existencia">${prod.existencia}</td>
                <td class="text-center unidad">${prod.unidad || 'PZA'}</td>
                <td class="text-center">
                    <i class="fas fa-trash fa-lg text-danger btn-eliminar" style="cursor:pointer;"></i>
                </td>
            </tr>
        `;

        $('#filaVacia').remove();
        $('#tbodySalida').append(fila);
        recalcularTotalPiezas();
    }

    function recalcularTotalPiezas() {
        let total = 0;
        $('#tbodySalida tr.fila-prod').each(function() {
            const qty = parseFloat($(this).find('input.cantidad').val()) || 0;
            total += qty;
        });
        $('#totalPiezas').text(total);
    }

    // Cambios en cantidad
    $(document).on('input', 'input.cantidad', function() {
        const $fila = $(this).closest('tr');
        const existencia = parseFloat($fila.find('td.existencia').text()) || 0;
        const qty = parseFloat($(this).val()) || 0;

        if (qty > existencia) {
            $(this).val(existencia);
            alertify.warning('La cantidad no puede ser mayor a la existencia del almacén.');
        }
        if (qty < 1) $(this).val(1);

        recalcularTotalPiezas();
    });

    // Eliminar fila
    $('#tbodySalida').on('click', '.btn-eliminar', function() {
        const $fila = $(this).closest('tr');
        alertify.confirm('Confirmación', '¿Eliminar este producto?', function() {
            $fila.remove();

            if ($('#tbodySalida tr.fila-prod').length === 0) {
                $('#tbodySalida').html('<tr id="filaVacia"><td colspan="5" class="text-center">Ningún producto agregado</td></tr>');
            }
            recalcularTotalPiezas();
        }, function() {});
    });

    // Cliente datalist -> hidden id
    $('#clienteInput').on('input', function() {
        const opcion = $('#clientes option').filter(function() {
            return $(this).val() === $('#clienteInput').val();
        });

        if (opcion.length > 0) {
            $('#clienteId').val(opcion.data('id'));
        } else {
            $('#clienteId').val('');
        }
    });

    // Si cambian almacén, limpiar productos (para evitar inconsistencias)
    $('#almacenSelect').on('change', function() {
        $('#tbodySalida').html('<tr id="filaVacia"><td colspan="5" class="text-center">Ningún producto agregado</td></tr>');
        $('#totalPiezas').text('0');
        if (dtProductos) dtProductos.ajax.reload();
    });

    // Guardar salida
    $('#formSalida').on('submit', function(e) {
        e.preventDefault();

        const almacenId = $('#almacenSelect').val();
        if (!almacenId) {
            alertify.error('Selecciona el almacén origen.');
            return;
        }

        if ($('#tbodySalida tr.fila-prod').length === 0) {
            alertify.error('Agrega al menos un producto.');
            return;
        }

        // validación final cantidades <= existencia
        let ok = true;
        $('#tbodySalida tr.fila-prod').each(function() {
            const existencia = parseFloat($(this).find('td.existencia').text()) || 0;
            const qty = parseFloat($(this).find('input.cantidad').val()) || 0;
            if (qty <= 0 || qty > existencia) ok = false;
        });
        if (!ok) {
            alertify.error('Revisa cantidades: no pueden exceder existencia.');
            return;
        }

        const formData = new FormData(this);
        $('#btnGuardar').prop('disabled', true);

        alertify.confirm('Confirmación', '¿Deseas guardar esta salida de almacén?', function() {
            $('#modalSpiner').modal('show');

            $.ajax({
                url: 'ajax/almacen-salidas/guardar.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(resp) {
                    $('#modalSpiner').modal('hide');
                    $('#btnGuardar').prop('disabled', false);

                    const r = (resp || '').trim();
                    if (r === 'success') {
                        alertify.success('Salida guardada correctamente.');
                        location.href = 'almacen-salidas';
                    } else {
                        alertify.error(r || 'No se pudo guardar.');
                    }
                },
                error: function(xhr) {
                    $('#modalSpiner').modal('hide');
                    $('#btnGuardar').prop('disabled', false);
                    alertify.error('Error de conexión: ' + xhr.statusText);
                }
            });
        }, function() {
            $('#btnGuardar').prop('disabled', false);
        });
    });
</script>