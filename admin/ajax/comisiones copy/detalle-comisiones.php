<?php
require_once '../../lib/config.php';
require_once '../../lib/clsConsultas.php';
$cls = new Consultas();

$id_vendedor = isset($_POST['id_vendedor']) ? intval($_POST['id_vendedor']) : 0;
$fecha_inicio = isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : '';
$fecha_fin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : '';

if ($id_vendedor <= 0 || !$fecha_inicio || !$fecha_fin) {
    echo "<div class='alert alert-warning'>Faltan datos para mostrar el detalle.</div>";
    exit;
}

// Consulta que recalcula totales desde mov_remisiones
$sql = "
    SELECT 
        r.id AS folio,
        DATE(r.fecha) AS fecha,
        cl.razon_social AS cliente,
        SUM(m.cantidad * m.precio) AS total,
        ROUND(SUM(m.cantidad * m.precio) * v.comision / 100, 2) AS comision
    FROM cab_remisiones r
    INNER JOIN mov_remisiones m ON r.id = m.id_remision
    INNER JOIN cat_clientes cl ON r.id_cliente = cl.id
    INNER JOIN cat_vendedores v ON r.id_vendedor = v.id
    WHERE r.id_vendedor = $id_vendedor
        AND r.estatus = 'procesada'
        AND DATE(r.fecha) BETWEEN '$fecha_inicio' AND '$fecha_fin'
        AND (
            r.tipo_venta = 'contado'
            OR (
                r.tipo_venta = 'credito'
                AND EXISTS (
                    SELECT 1 FROM cab_cxc c WHERE c.id_remision = r.id AND c.estatus = 'Pagada'
                )
            )
        )
    GROUP BY r.id
    ORDER BY r.fecha DESC
";

$datos = $cls->consultaGeneral($sql);

if (!$cls->numrows) {
    echo "<div class='alert alert-info'>No hay remisiones procesadas para este vendedor en el rango seleccionado.</div>";
    exit;
}
?>

<table class="table table-bordered table-hover table-sm">
    <thead class="table-light">
        <tr>
            <th class="text-center">Folio</th>
            <th class="text-center">Fecha</th>
            <th class="text-center">Cliente</th>
            <th class="text-end">Total</th>
            <th class="text-end">Comisión</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $total_ventas = 0;
        $total_comision = 0;
        foreach ($datos as $fila) {
            $total_ventas += floatval($fila['total']);
            $total_comision += floatval($fila['comision']);
        ?>
            <tr>
                <td class="text-center"><?= $fila['folio'] ?></td>
                <td class="text-center"><?= $fila['fecha'] ?></td>
                <td><?= htmlspecialchars($fila['cliente']) ?></td>
                <td class="text-end">$<?= number_format($fila['total'], 2) ?></td>
                <td class="text-end">$<?= number_format($fila['comision'], 2) ?></td>
            </tr>
        <?php } ?>
    </tbody>
    <tfoot class="table-light fw-bold">
        <tr>
            <td colspan="3" class="text-end">Totales</td>
            <td class="text-end">$<?= number_format($total_ventas, 2) ?></td>
            <td class="text-end">$<?= number_format($total_comision, 2) ?></td>
        </tr>
    </tfoot>
</table>
