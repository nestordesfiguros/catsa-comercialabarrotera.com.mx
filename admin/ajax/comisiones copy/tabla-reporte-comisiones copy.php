<?php
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$f1 = $_POST['desde'] ?? '';
$f2 = $_POST['hasta'] ?? '';
$id_vendedor = $_POST['vendedor'] ?? '';
$estatus_filtro = $_POST['estatus'] ?? 'pendiente';

$data_array = [];

// Filtros base
$where = "WHERE 1";
if ($id_vendedor !== '' && $id_vendedor !== '0') {
    $where .= " AND c.id_vendedor = '$id_vendedor'";
}
if ($f1 && $f2) {
    $where .= " AND (
        DATE(c.fecha_inicio) <= '$f2' AND DATE(c.fecha_fin) >= '$f1'
    )";
}
if ($estatus_filtro !== 'todos') {
    $where .= " AND c.estatus = '$estatus_filtro'";
}

// Consulta principal
$sql = "
SELECT 
    c.id,
    c.id_vendedor,
    CONCAT(v.nombre, ' ', v.apellido1, ' ', v.apellido2) AS nombre_vendedor,
    v.comision,
    c.fecha_inicio,
    c.fecha_fin,
    c.estatus,
    c.fecha_pago
FROM comisiones c
INNER JOIN cat_vendedores v ON v.id = c.id_vendedor
$where
ORDER BY c.fecha_inicio DESC
";

$rs = $clsConsulta->consultaGeneral($sql);

if ($clsConsulta->numrows > 0) {
    foreach ($rs as $row) {
        $id_comision = $row['id'];
        $id_vendedor = $row['id_vendedor'];
        $nombre = $row['nombre_vendedor'];
        $porcentaje = $row['comision'] . '%';
        $fecha_inicio = $row['fecha_inicio'];
        $fecha_fin = $row['fecha_fin'];
        $estatus_val = $row['estatus'];

        // Etiqueta visual de estatus
        $estatus = match($estatus_val) {
            'pagado'    => '<span class="badge bg-success">Pagado</span>',
            'cancelado' => '<span class="badge bg-danger">Cancelado</span>',
            default     => '<span class="badge bg-warning text-dark">Pendiente</span>',
        };

        // Fecha de pago
        $fecha_pago = $row['fecha_pago']
            ? date('d/m/Y H:i', strtotime($row['fecha_pago']))
            : '---';

        // Ventas cobradas desde cab_cxc con remisión y estatus pagado
        $sqlVentas = "
            SELECT 
                IFNULL(SUM(m.cantidad * m.precio), 0) AS total_ventas
            FROM cab_cxc cx
            INNER JOIN cab_remisiones r ON r.id = cx.id_remision
            INNER JOIN mov_remisiones m ON r.id = m.id_remision
            WHERE cx.estatus = 'pagado'
              AND r.id_vendedor = $id_vendedor
              AND DATE(cx.fecha) BETWEEN '$fecha_inicio' AND '$fecha_fin'
        ";
        $resVentas = $clsConsulta->consultaGeneral($sqlVentas);
        $ventas = floatval($resVentas[0]['total_ventas']);
        $comision_calc = round($ventas * floatval($row['comision']) / 100, 2);

        $ventas_fmt = '$' . number_format($ventas, 2, '.', ',');
        $comision_fmt = '$' . number_format($comision_calc, 2, '.', ',');

        // Rango real de pedidos asociados a las remisiones cobradas
        $sqlFechasPedidos = "
            SELECT MIN(p.fecha) AS min_fecha, MAX(p.fecha) AS max_fecha
            FROM cab_pedidos p
            INNER JOIN cab_remisiones r ON r.id_pedido = p.id
            INNER JOIN cab_cxc cx ON cx.id_remision = r.id
            WHERE cx.estatus = 'pagado'
              AND r.id_vendedor = $id_vendedor
              AND DATE(cx.fecha) BETWEEN '$fecha_inicio' AND '$fecha_fin'
        ";
        $resPedidos = $clsConsulta->consultaGeneral($sqlFechasPedidos);
        $fecha_pedido = '---';
        if ($clsConsulta->numrows > 0 && isset($resPedidos[0])) {
            $min = $resPedidos[0]['min_fecha'] ?? null;
            $max = $resPedidos[0]['max_fecha'] ?? null;

            if ($min && $max) {
                $fecha_pedido = ($min === $max)
                    ? date('d/m/Y', strtotime($min))
                    : date('d/m/Y', strtotime($min)) . ' al ' . date('d/m/Y', strtotime($max));
            }
        }

        // Botón detalle
        $detalle = '<div class="text-center">
            <button class="btn btn-sm btn-outline-info" onclick="verDetalleComisiones(' . $id_vendedor . ', \'' . $fecha_inicio . '\', \'' . $fecha_fin . '\')">
                <i class="fas fa-list"></i>
            </button>
        </div>';

        // Botón comprobante
        $comprobante = $estatus_val === 'pagado'
            ? '<div class="text-center">
                <button class="btn btn-sm btn-outline-dark" onclick="verComprobante(' . $id_comision . ')">
                    <i class="fas fa-file-pdf"></i> Ver
                </button>
            </div>'
            : '<div class="text-center text-muted">---</div>';

        $data_array[] = [
            $nombre,
            $porcentaje,
            $ventas_fmt,
            $comision_fmt,
            $estatus,
            $fecha_pedido,
            $fecha_pago,
            $detalle,
            $comprobante
        ];
    }
}

echo json_encode(["data" => $data_array]);
