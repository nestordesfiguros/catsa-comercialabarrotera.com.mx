<?php
// ajax/cxp/tabla-cxp.php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
if ($idEmpresa <= 0) {
    echo json_encode([
        'draw' => 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => []
    ]);
    exit;
}

$draw = isset($_POST['draw']) ? (int)$_POST['draw'] : 1;
$start = isset($_POST['start']) ? (int)$_POST['start'] : 0;
$length = isset($_POST['length']) ? (int)$_POST['length'] : 25;

if ($start < 0) $start = 0;
if ($length < 1) $length = 10;
if ($length > 100) $length = 100;

$proveedor = isset($_POST['proveedor']) ? (int)$_POST['proveedor'] : 0;
$estatus = isset($_POST['estatus']) && $_POST['estatus'] !== '' ? $_POST['estatus'] : 'pendiente';
$search_global = isset($_POST['search_global']) ? trim($_POST['search_global']) : '';

$where = "cxp.id_empresa = {$idEmpresa} AND prov.id_empresa = {$idEmpresa}";

if ($proveedor > 0) {
    $where .= " AND cxp.id_proveedor = {$proveedor}";
}

if ($estatus !== '') {
    $estatusEsc = $clsConsulta->escape($estatus);
    $where .= " AND cxp.estatus = '{$estatusEsc}'";
}

if ($search_global !== '') {
    $search = $clsConsulta->escape($search_global);
    $where .= " AND (prov.razon_social LIKE '%{$search}%' OR cxp.id LIKE '%{$search}%')";
}

$sql_total = "SELECT COUNT(*) as total
              FROM cuentas_por_pagar cxp
              INNER JOIN cat_proveedores prov ON prov.id = cxp.id_proveedor
              WHERE cxp.id_empresa = {$idEmpresa} AND prov.id_empresa = {$idEmpresa}";
$res_total = $clsConsulta->consultaGeneral($sql_total);
$totalRows = ($clsConsulta->numrows > 0 && isset($res_total[1]['total'])) ? (int)$res_total[1]['total'] : 0;

$sql_filtered = "SELECT COUNT(*) as total
                 FROM cuentas_por_pagar cxp
                 INNER JOIN cat_proveedores prov ON prov.id = cxp.id_proveedor
                 WHERE {$where}";
$res_filtered = $clsConsulta->consultaGeneral($sql_filtered);
$totalFiltered = ($clsConsulta->numrows > 0 && isset($res_filtered[1]['total'])) ? (int)$res_filtered[1]['total'] : 0;

$sql_data = "
    SELECT
        cxp.id,
        prov.razon_social AS proveedor,
        cxp.fecha,
        cxp.monto_total,
        cxp.monto_pagado,
        (cxp.monto_total - cxp.monto_pagado) AS saldo,
        cxp.estatus
    FROM cuentas_por_pagar cxp
    INNER JOIN cat_proveedores prov ON prov.id = cxp.id_proveedor
    WHERE {$where}
    ORDER BY cxp.fecha DESC
    LIMIT {$start}, {$length}
";

$datos = $clsConsulta->consultaGeneral($sql_data);

$data = [];
if ($clsConsulta->numrows > 0 && is_array($datos)) {
    foreach ($datos as $row) {
        $data[] = [
            'id'           => $row['id'],
            'proveedor'    => $row['proveedor'],
            'fecha'        => $row['fecha'],
            'monto_total'  => $row['monto_total'],
            'monto_pagado' => $row['monto_pagado'],
            'saldo'        => $row['saldo'],
            'estatus'      => $row['estatus']
        ];
    }
}

echo json_encode([
    'draw' => $draw,
    'recordsTotal' => $totalRows,
    'recordsFiltered' => $totalFiltered,
    'data' => $data
]);
