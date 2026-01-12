<?php
session_start();
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

header('Content-Type: application/json');

$id_remision = intval($_GET['id_remision'] ?? 0);

if ($id_remision <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de remisión inválido']);
    exit;
}

// =========================
// 1. Consultar cabecera de la remisión
// =========================
$sqlCab = "
    SELECT 
        cr.id,
        cr.fecha,
        cr.total,
        COALESCE(cc.razon_social, cc.nombre_comercial, 'SIN CLIENTE') AS cliente
    FROM cab_remisiones cr
    LEFT JOIN cat_clientes cc ON cr.id_cliente = cc.id
    WHERE cr.id = $id_remision
    LIMIT 1
";
$cab = $clsConsulta->consultaGeneral($sqlCab);

if ($clsConsulta->numrows <= 0 || !isset($cab[1])) {
    echo json_encode([
        'success' => true,
        'cabecera' => [
            'folio'   => 'R-' . str_pad((string)$id_remision, 6, '0', STR_PAD_LEFT),
            'cliente' => 'SIN CLIENTE',
            'fecha'   => 'N/D',
            'total'   => 0
        ],
        'productos' => []
    ]);
    exit;
}

$cabecera = $cab[1]; // <- índices inician en 1
$folio   = 'R-' . str_pad((string)$cabecera['id'], 6, '0', STR_PAD_LEFT);
$cliente = $cabecera['cliente'];
$fecha   = $cabecera['fecha'] ?? 'N/D';
$total   = floatval($cabecera['total'] ?? 0);

// =========================
// 2. Consultar productos de la remisión
// =========================
$sqlProd = "
    SELECT 
        mr.id_producto,
        cp.nombre AS nombre,
        mr.cantidad,
        mr.precio
    FROM mov_remisiones mr
    INNER JOIN cat_productos cp ON mr.id_producto = cp.id_producto
    WHERE mr.id_remision = $id_remision
    ORDER BY cp.nombre ASC
";
$rs = $clsConsulta->consultaGeneral($sqlProd);

$productos = [];
$total_calculado = 0;

if ($clsConsulta->numrows > 0) {
    foreach ($rs as $i => $row) {
        if (!is_array($row)) continue;

        $cantidad = floatval($row['cantidad']);
        $precio   = floatval($row['precio']);
        $total_calculado += $cantidad * $precio;

        $productos[] = [
            'id_producto'     => $row['id_producto'],
            'nombre'          => $row['nombre'],
            'cantidad'        => $cantidad,
            'precio_unitario' => $precio
        ];
    }
}

// Si el total de la cabecera es 0, usamos el total calculado
if ($total <= 0) {
    $total = $total_calculado;
}

// =========================
// 3. Retornar JSON final
// =========================
echo json_encode([
    'success' => true,
    'cabecera' => [
        'folio'   => $folio,
        'cliente' => $cliente,
        'fecha'   => $fecha,
        'total'   => $total
    ],
    'productos' => $productos
]);
