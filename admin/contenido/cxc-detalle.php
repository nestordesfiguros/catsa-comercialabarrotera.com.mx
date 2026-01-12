<?php

function safeStr($val)
{
    return htmlspecialchars($val ?? '', ENT_QUOTES, 'UTF-8');
}

$id_cxc = (int)$cat;

// 1. Cabecera CxC + datos cliente + remisión
$cab = $clsConsulta->consultaGeneral("
    SELECT c.*, 
        cli.id AS idCliente, cli.razon_social, cli.nombre_comercial, cli.calle, cli.num_ext, cli.num_int, cli.colonia, cli.cp, cli.localidad,
        r.id AS remision_id, r.total AS remision_total, r.direccion_envio AS remision_direccion, r.fecha AS remision_fecha
    FROM cab_cxc c
    LEFT JOIN cat_clientes cli ON c.id_cliente = cli.id
    LEFT JOIN cab_remisiones r ON c.id_remision = r.id
    WHERE c.id_cxc = $id_cxc
    LIMIT 1
");
if (!$cab || !isset($cab[1])) {
    echo '<div class="alert alert-danger">Cuenta por cobrar no encontrada.</div>';
    return;
}
$cxc = $cab[1];
$idCliente = $cab[1]['idCliente'];

// 2. Domicilio armado del cliente
$domicilio = trim(
    ($cxc['calle'] ?? '') . ' ' .
        ($cxc['num_ext'] ?? '') .
        (($cxc['num_int']) ? (' Int. ' . $cxc['num_int']) : '') . ', ' .
        ($cxc['colonia'] ?? '') . ', ' .
        ($cxc['localidad'] ?? '') . ', C.P. ' .
        ($cxc['cp'] ?? '')
);

// 3. Detalle productos de remisión
$detalle = [];
if ($cxc['remision_id']) {
    $detalle = $clsConsulta->consultaGeneral("
        SELECT mr.*, p.clave, p.nombre, u.nombre_unidad
        FROM mov_remisiones mr
        LEFT JOIN cat_productos p ON mr.id_producto = p.id_producto
        LEFT JOIN cat_unidades_de_medida u ON p.unidad_medida = u.id_unidad
        WHERE mr.id_remision = {$cxc['remision_id']}
");
}

// 4. Abonos con usuario
$abonos = $clsConsulta->consultaGeneral("
    SELECT m.*, u.nombre AS usuario_nombre
    FROM mov_cxc m
    LEFT JOIN usuarios u ON m.id_usuario = u.id
    WHERE m.id_cxc = $id_cxc
    ORDER BY m.fecha ASC
");
?>

<!-- BREADCRUMB Y TITULOS -->
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="cxc">Cuentas por Cobrar</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detalle </li>
        </ol>
    </nav>
</div>

<div class="container-fluid px-4">
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">
                    Detalle de Cuenta por Cobrar
                    <span class="badge bg-secondary"><?= safeStr($cxc['estatus']) ?></span>
                </h5>
                <small class="text-muted">
                    Folio: <b><?= 'CXC-' . str_pad($cxc['id_cxc'], 6, '0', STR_PAD_LEFT) ?></b>
                </small>
            </div>
            <div>
                <a href="cxc" class="btn btn-outline-secondary btn-sm">
                    <i class="fa fa-arrow-left"></i> Regresar
                </a>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.print()">
                    <i class="fa fa-print"></i> Imprimir
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4"><strong>Cliente:</strong><br><?= safeStr($cxc['razon_social'] ?: $cxc['nombre_comercial']) ?></div>
                <div class="col-md-4"><strong>Domicilio:</strong><br><?= safeStr($domicilio) ?></div>
                <div class="col-md-4"><strong>Fecha Emisión:</strong><br><?= safeStr($cxc['fecha_emision']) ?></div>
                <div class="col-md-4 mt-3"><strong>Fecha Vencimiento:</strong><br><?= safeStr($cxc['fecha_vencimiento']) ?></div>
                <div class="col-md-4 mt-3"><strong>Remisión Relacionada:</strong><br><?= $cxc['remision_id'] ? 'REM-' . str_pad($cxc['remision_id'], 6, '0', STR_PAD_LEFT) : 'Sin remisión' ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><strong>Monto:</strong> $<?= number_format($cxc['monto'] ?? 0, 2) ?></div>
                <div class="col-md-4"><strong>Saldo:</strong> $<?= number_format($cxc['saldo'] ?? 0, 2) ?></div>
                <div class="col-md-4"><strong>Observaciones:</strong><br><?= nl2br(safeStr($cxc['observaciones'])) ?></div>
            </div>
            <hr>

            <h6 class="mb-3">Detalle de productos</h6>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="table-secondary">
                        <tr>
                            <th class="text-center">Clave</th>
                            <th class="text-center">Producto</th>
                            <th class="text-center">Unidad</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-center">Precio</th>
                            <th class="text-center">Importe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($detalle && count($detalle)) {
                            foreach ($detalle as $prod) {
                                echo '<tr>
                                    <td>' . safeStr($prod['clave']) . '</td>
                                    <td>' . safeStr($prod['nombre']) . '</td>
                                    <td>' . safeStr($prod['nombre_unidad']) . '</td>
                                    <td class="text-end">' . number_format($prod['cantidad'] ?? 0, 2) . '</td>
                                    <td class="text-end">$' . number_format($prod['precio'] ?? 0, 2) . '</td>
                                    <td class="text-end">$' . number_format(($prod['precio'] ?? 0) * ($prod['cantidad'] ?? 0), 2) . '</td>
                                </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="6" class="text-center text-muted">Sin productos relacionados.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <h6 class="mb-3">Pagos / Abonos aplicados
                <span class="ms-5">
                    <button class="btn btn-success btn-info btn-small"
                        data-bs-toggle="modal"
                        data-bs-target="#modalAbonoCxc"
                        <?= ($cxc['saldo'] <= 0 ? 'disabled' : '') ?>>
                        Agregar Pago
                    </button>
                </span>
            </h6>


            <div class="table-responsive mb-2">
                <table class="table table-bordered table-sm table-hover">
                    <thead class="table-secondary">
                        <tr>
                            <th class="text-center">Fecha Pago</th>
                            <th class="text-center">Monto Abono</th>
                            <th class="text-center">Concepto</th>
                            <th class="text-center">Usuario</th>
                            <th class="text-center">Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($abonos && count($abonos)) {
                            foreach ($abonos as $ab) {
                                echo '<tr>
                                    <td class="text-center">' . safeStr($ab['fecha']) . '</td>
                                    <td class="text-end">$' . number_format($ab['abono'] ?? 0, 2) . '</td>
                                    <td>' . safeStr($ab['concepto']) . '</td>
                                    <td>' . safeStr($ab['usuario_nombre']) . '</td>
                                    <td class="text-center">                                        
                                        <i style="cursor:pointer" class="fa fa-trash fa-lg btnEliminarAbono text-danger" data-id="' . $ab['id'] . '"></i>                                        
                                    </td>
                                </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="5" class="text-center text-muted">Sin pagos o abonos registrados.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL REGISTRAR ABONO -->
<div class="modal fade" id="modalAbonoCxc" tabindex="-1" aria-labelledby="modalAbonoCxcLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formAbonoCxc" autocomplete="off">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="modalAbonoCxcLabel">
                        <i class="fas fa-dollar-sign"></i> Registrar abono a <span id="abonoCxcFolio"><?= 'CXC-' . str_pad($cxc['id_cxc'], 6, '0', STR_PAD_LEFT) ?></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="cerrarModalAbono();"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Concepto</label>
                        <input type="text" name="concepto" class="form-control" placeholder="Ej. Pago parcial" maxlength="100" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Monto a abonar</label>
                        <input type="number" name="abono" id="abonoCxc_monto" class="form-control" min=".01" step="0.01" required>
                        <small class="text-muted" id="abonoCxc_saldo">Saldo actual: $<?= number_format($cxc['saldo'], 2) ?></small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Fecha</label>
                        <input type="date" name="fecha" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="id_cxc" value="<?= $id_cxc; ?>">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="cerrarModalAbono();">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Registrar abono</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function cerrarModalAbono() {
        $('#modalAbonoCxc').modal('hide');
    }

    $(document).ready(function() {
        $('#formAbonoCxc').on('submit', function(e) {
            e.preventDefault();

            const form = this;
            const monto = parseFloat($('#abonoCxc_monto').val());
            const saldoText = $('#abonoCxc_saldo').text().replace(/[^\d.,]/g, '').replace(',', '');
            const saldo = parseFloat(saldoText) || 0;

            if (monto > saldo) {
                alertify.error('El abono no puede ser mayor al saldo.');
                return;
            }

            alertify.confirm(
                '¿Registrar abono?',
                '¿Estás seguro de registrar este abono?',
                function() {
                    const formData = $(form).serialize();
                    $.post('ajax/cxc/abonar.php', formData, function(resp) {
                        if (resp.trim() === 'success') {
                            alertify.success('Abono registrado correctamente');
                            location.reload();
                        } else {
                            alertify.error('Error: ' + resp);
                        }
                    });
                },
                function() {
                    alertify.message('Cancelado');
                }
            );
        });

        // Eliminar abono
        $(document).on('click', '.btnEliminarAbono', function() {
            const idAbono = $(this).data('id');
            alertify.confirm(
                '¿Eliminar abono?',
                'Esta acción no se puede deshacer.',
                function() {
                    $.post('ajax/cxc/eliminar-abono.php', {
                        id: idAbono
                    }, function(resp) {
                        if (resp.trim() === 'success') {
                            alertify.success('Abono eliminado');
                            location.reload();
                        } else {
                            alertify.error('Error: ' + resp);
                        }
                    });
                },
                function() {
                    alertify.message('Cancelado');
                }
            );
        });
    });
</script>