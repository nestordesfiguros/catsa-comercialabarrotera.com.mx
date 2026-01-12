<?php
// contenido/cxp-detalle.php

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;

$id_cxp = intval($cat);
$cxp = $clsConsulta->consultaGeneral("
    SELECT
        cxp.*,
        prov.razon_social AS proveedor
    FROM cuentas_por_pagar cxp
    INNER JOIN cat_proveedores prov ON prov.id = cxp.id_proveedor
    WHERE cxp.id = {$id_cxp}
      AND cxp.id_empresa = {$idEmpresa}
      AND prov.id_empresa = {$idEmpresa}
    LIMIT 1
");

if ($clsConsulta->numrows < 1) {
    echo '<div class="alert alert-danger">Cuenta por pagar no encontrada.</div>';
    return;
}
$row = $cxp[1];
?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="cxp">Cuentas por Pagar</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detalle CxP #<?= (int)$row['id'] ?></li>
        </ol>
    </nav>
</div>

<div class="container-fluid px-4">
    <div class="card p-4 mb-4">
        <h5 class="mb-3">Detalle de la Cuenta por Pagar</h5>
        <div class="row">
            <div class="col-md-6 mb-2"><b>Proveedor:</b> <?= htmlspecialchars($row['proveedor']) ?></div>
            <div class="col-md-3 mb-2"><b>Fecha:</b> <?= htmlspecialchars($row['fecha']) ?></div>
            <div class="col-md-3 mb-2">
                <b>Estatus:</b>
                <span class="badge bg-<?=
                                        ($row['estatus'] == 'pagada' ? 'success' : ($row['estatus'] == 'pendiente' ? 'warning' : ($row['estatus'] == 'vencida' ? 'danger' : 'info')))
                                        ?>"><?= htmlspecialchars($row['estatus']) ?></span>
            </div>

            <div class="col-md-3 mb-2"><b>Monto Total:</b> $<?= number_format((float)$row['monto_total'], 2) ?></div>
            <div class="col-md-3 mb-2"><b>Monto Pagado:</b> $<?= number_format((float)$row['monto_pagado'], 2) ?></div>
            <div class="col-md-3 mb-2"><b>Saldo:</b> $<?= number_format((float)$row['monto_total'] - (float)$row['monto_pagado'], 2) ?></div>
            <div class="col-md-12 mb-2"><b>Observaciones:</b> <?= htmlspecialchars($row['observaciones']) ?></div>
        </div>

        <div class="mt-3">
            <?php if ($row['estatus'] != 'pagada'): ?>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalPago">
                    <i class="fa fa-plus"></i> Registrar pago/abono
                </button>
            <?php else: ?>
                <button class="btn btn-secondary" disabled><i class="fa fa-lock"></i> Cuenta pagada</button>
            <?php endif; ?>
            <a href="cxp" class="btn btn-secondary ms-2"><i class="fa fa-arrow-left"></i> Regresar</a>
        </div>
    </div>

    <div class="card p-4">
        <h6>Pagos/Abonos realizados</h6>
        <div class="table-responsive">
            <table id="tablaPagosCxp" class="table table-bordered table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Monto</th>
                        <th>Método de pago</th>
                        <th>Referencia</th>
                        <th>Observaciones</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPago" tabindex="-1" aria-labelledby="modalPagoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formPagoCxp">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalPagoLabel">Registrar pago/abono</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Monto a abonar</label>
                        <input type="number" step="0.01" min="0.01"
                            max="<?= (float)$row['monto_total'] - (float)$row['monto_pagado'] ?>"
                            class="form-control" name="monto" required>
                    </div>
                    <div class="mb-3">
                        <label>Fecha de pago</label>
                        <input type="date" class="form-control" name="fecha" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Método de pago</label>
                        <select class="form-select" name="metodo_pago" required>
                            <option value="">Selecciona...</option>
                            <?php
                            $metodos = $clsConsulta->consultaGeneral("SELECT id, descripcion FROM metodo_pago ORDER BY descripcion");
                            if ($clsConsulta->numrows > 0 && is_array($metodos)) {
                                foreach ($metodos as $m) {
                                    echo '<option value="' . (int)$m['id'] . '">' . htmlspecialchars($m['descripcion']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Referencia</label>
                        <input type="text" class="form-control" name="referencia">
                    </div>
                    <div class="mb-3">
                        <label>Observaciones</label>
                        <textarea class="form-control" name="observaciones"></textarea>
                    </div>
                    <input type="hidden" name="id_cxp" value="<?= (int)$row['id'] ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar pago</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#tablaPagosCxp').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: 'ajax/cxp/tabla-pagos-cxp.php',
                type: 'POST',
                data: {
                    id_cxp: <?= (int)$row['id'] ?>
                }
            },
            paging: false,
            searching: false,
            info: false,
            ordering: false,
            columns: [{
                    data: 'fecha'
                },
                {
                    data: 'monto',
                    render: $.fn.dataTable.render.number(',', '.', 2, '$')
                },
                {
                    data: 'metodo_pago'
                },
                {
                    data: 'referencia'
                },
                {
                    data: 'observaciones'
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        return `<button class="btn btn-danger btn-sm btnEliminarPago" data-id="${row.id}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>`;
                    }
                }
            ],
            language: {
                url: "assets/datatables/Spanish.json"
            }
        });

        $('#formPagoCxp').on('submit', function(e) {
            e.preventDefault();

            var monto = parseFloat($('[name="monto"]').val());
            var metodo = $('[name="metodo_pago"]').val();
            var saldo = parseFloat('<?= (float)$row["monto_total"] - (float)$row["monto_pagado"] ?>');

            if (!metodo) {
                alertify.error('Selecciona el método de pago.');
                $('[name="metodo_pago"]').focus();
                return false;
            }
            if (!monto || monto <= 0) {
                alertify.error('El monto debe ser mayor a cero.');
                $('[name="monto"]').focus();
                return false;
            }
            if (monto > saldo) {
                alertify.error('El monto no puede ser mayor al saldo pendiente.');
                $('[name="monto"]').focus();
                return false;
            }

            alertify.confirm(
                'Confirmar abono',
                '¿Estás seguro que deseas registrar este pago/abono?',
                function() {
                    $.ajax({
                        url: 'ajax/cxp/guardar-pago-cxp.php',
                        type: 'POST',
                        data: $('#formPagoCxp').serialize(),
                        success: function(resp) {
                            const r = (resp || '').trim();
                            if (r === 'success') {
                                $('#modalPago').modal('hide');
                                $('#tablaPagosCxp').DataTable().ajax.reload();
                                location.reload();
                            } else if (r === 'error_cxp_not_found') {
                                alertify.error('Cuenta por pagar no encontrada.');
                            } else if (r === 'error_monto_excede') {
                                alertify.error('El monto a abonar excede el saldo pendiente.');
                            } else {
                                alertify.error('No se pudo guardar el pago. Revisa los datos.');
                            }
                        }
                    });
                },
                function() {
                    alertify.message('Operación cancelada.');
                }
            ).set('labels', {
                ok: 'Sí, registrar',
                cancel: 'No, cancelar'
            });
        });

        $('#tablaPagosCxp').on('click', '.btnEliminarPago', function() {
            var idPago = $(this).data('id');

            alertify.confirm('Eliminar pago', '¿Seguro que deseas eliminar este pago?', function() {
                $.ajax({
                    url: 'ajax/cxp/eliminar-pago-cxp.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        id: idPago
                    },
                    success: function(json) {
                        if (json && json.status === 'success') {
                            alertify.success('Pago eliminado.');
                            $('#tablaPagosCxp').DataTable().ajax.reload();

                            if (json.nuevo_estatus === 'pendiente') {
                                alertify.warning('La cuenta ha regresado al estatus "pendiente".');
                            }

                            setTimeout(() => location.reload(), 800);
                        } else {
                            alertify.error('No se pudo eliminar el pago.');
                        }
                    },
                    error: function() {
                        alertify.error('Error al procesar respuesta del servidor.');
                    }
                });
            }, function() {
                alertify.message('Cancelado');
            }).set('labels', {
                ok: 'Sí, eliminar',
                cancel: 'No, cancelar'
            });
        });
    });
</script>