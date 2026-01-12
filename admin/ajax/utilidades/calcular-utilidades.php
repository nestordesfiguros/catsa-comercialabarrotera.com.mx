<?php
require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

if (empty($_POST['fecha_inicio']) || empty($_POST['fecha_fin'])) {
    die(json_encode(['error' => 'Debe especificar ambas fechas']));
}

$fecha_inicio = date('Y-m-d', strtotime($_POST['fecha_inicio']));
$fecha_fin = date('Y-m-d', strtotime($_POST['fecha_fin']));
$id_vendedor = !empty($_POST['id_vendedor']) ? (int)$_POST['id_vendedor'] : null;

// Consulta remisiones procesadas
$sql = "SELECT 
            r.id,
            DATE_FORMAT(r.fecha, '%d/%m/%Y') as fecha_formateada,
            c.razon_social as cliente,
            v.id as id_vendedor,
            CONCAT(v.nombre, ' ', v.apellido1) as vendedor,
            v.comision as porcentaje_comision
        FROM cab_remisiones r
        INNER JOIN cat_clientes c ON r.id_cliente = c.id
        INNER JOIN cat_vendedores v ON r.id_vendedor = v.id
        WHERE r.estatus = 'procesada'
        AND DATE(r.fecha) BETWEEN '$fecha_inicio' AND '$fecha_fin'";

if ($id_vendedor) {
    $sql .= " AND r.id_vendedor = $id_vendedor";
}

$remisiones = $clsConsulta->consultaGeneral($sql);
$resultados = [];

if ($clsConsulta->numrows > 0) {
    foreach ($remisiones as $remision) {
        $sqlDetalle = "SELECT 
                            m.id_producto, 
                            m.cantidad,
                            m.precio as precio_venta,
                            COALESCE(
                                (SELECT precio FROM mov_compras 
                                 WHERE id_producto = m.id_producto 
                                 ORDER BY id DESC LIMIT 1),
                                p.precio_compra
                            ) as costo_unitario
                        FROM mov_remisiones m
                        INNER JOIN cat_productos p ON m.id_producto = p.id_producto
                        WHERE m.id_remision = {$remision['id']}";

        $detalles = $clsConsulta->consultaGeneral($sqlDetalle);

        $totalVenta = 0;
        $costoTotal = 0;

        foreach ($detalles as $detalle) {
            $totalVenta += ($detalle['precio_venta'] * $detalle['cantidad']);
            $costoTotal += ($detalle['costo_unitario'] * $detalle['cantidad']);
        }

        $comision = $totalVenta > 0 ? round(($remision['porcentaje_comision'] / 100) * $totalVenta, 2) : 0;
        $utilidadBruta = $totalVenta - $costoTotal;
        $utilidadNeta = $utilidadBruta - $comision;

        $resultados[] = [
            'folio' => 'RM-' . str_pad($remision['id'], 6, '0', STR_PAD_LEFT),
            'fecha' => $remision['fecha_formateada'],
            'cliente' => $remision['cliente'],
            'vendedor' => $remision['vendedor'],
            'total_venta' => round($totalVenta, 2),
            'costo_total' => round($costoTotal, 2),
            'utilidad_bruta' => round($utilidadBruta, 2),
            'comision' => round($comision, 2),
            'utilidad_neta' => round($utilidadNeta, 2)
        ];
    }
}

// Totales
$totales = [
    'total_ventas' => array_sum(array_column($resultados, 'total_venta')),
    'total_costos' => array_sum(array_column($resultados, 'costo_total')),
    'total_comisiones' => array_sum(array_column($resultados, 'comision'))
];

$totales['total_utilidad_bruta'] = $totales['total_ventas'] - $totales['total_costos'];
$totales['total_utilidad_neta'] = $totales['total_utilidad_bruta'] - $totales['total_comisiones'];

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'data' => $resultados,
    'totales' => $totales
]);
