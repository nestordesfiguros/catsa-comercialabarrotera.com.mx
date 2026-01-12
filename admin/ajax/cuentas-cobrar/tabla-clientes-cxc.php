<?php
session_start();
include '../../lib/clsConsultas.php';

if (!isset($_SESSION['id_puesto'])) {
    echo json_encode(['data' => []]);
    exit;
}

$clsConsulta = new Consultas();

// Obtener filtro de estatus
$estatus_filter = isset($_POST['estatus']) ? $clsConsulta->escape($_POST['estatus']) : '';
$where = "WHERE f.estatus IN ('PENDIENTE', 'PAGADA')";
if ($estatus_filter !== '') {
    $where .= " AND f.estatus = '$estatus_filter'";
}

// Consulta de facturas
$query = "SELECT f.id AS id_factura, f.folio, f.fecha, COALESCE(c.razon_social, 'Sin cliente') AS razon_social, f.total, f.estatus
          FROM cab_facturas f
          LEFT JOIN cat_clientes c ON f.id_receptor = c.id
          $where
          ORDER BY f.fecha DESC";
$facturas = $clsConsulta->consultaGeneral($query) ?? [];

// Calcular saldo para cada factura
$data = [];
foreach ($facturas as $factura) {
    // Calcular saldo usando mov_facturas.importe
    $pagos = $clsConsulta->consultaGeneral("SELECT SUM(importe) AS total_pagado FROM mov_facturas WHERE id_factura = {$factura['id_factura']}");
    $total_pagado = $pagos[0]['total_pagado'] ?? 0;
    $saldo = $factura['total'] - $total_pagado;

    // Mapear estatus
    $estatus_map = [
        'PENDIENTE' => 'Pendiente',
        'PAGADA' => 'Pagada',
        'CANCELADA' => 'Cancelada'
    ];
    $estatus = $estatus_map[$factura['estatus']] ?? $factura['estatus'];

    // Acciones
    $acciones = '';
    if ($factura['estatus'] !== 'PAGADA' && $factura['estatus'] !== 'CANCELADA') {
        $acciones .= '<a href="cuentas-cobrar-pago/' . $factura['id_factura'] . '" class="btn btn-sm btn-primary"><i class="fas fa-money-bill"></i> Registrar Pago</a> ';
    }
    $acciones .= '<a href="cuentas-cobrar-historial/' . $factura['id_factura'] . '" class="btn btn-sm btn-info"><i class="fas fa-history"></i> Historial</a>';

    $data[] = [
        'folio' => htmlspecialchars($factura['folio']),
        'fecha' => htmlspecialchars($factura['fecha']),
        'razon_social' => htmlspecialchars($factura['razon_social']),
        'total' => '$' . number_format($factura['total'], 2),
        'saldo' => '$' . number_format($saldo, 2),
        'estatus' => $estatus,
        'acciones' => $acciones
    ];
}

// Devolver datos en formato JSON para DataTables
echo json_encode(['data' => $data]);
?>