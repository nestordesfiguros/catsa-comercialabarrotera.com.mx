<?php
// ajax/cxp/tabla-pagos-cxp.php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
$id_cxp = isset($_POST['id_cxp']) ? (int)$_POST['id_cxp'] : 0;
$draw = isset($_POST['draw']) ? (int)$_POST['draw'] : 1;

if ($idEmpresa <= 0 || $id_cxp <= 0) {
    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => []
    ]);
    exit;
}

// Validar CxP pertenece a empresa
$chk = $clsConsulta->consultaGeneral("SELECT id FROM cuentas_por_pagar WHERE id={$id_cxp} AND id_empresa={$idEmpresa} LIMIT 1");
if ($clsConsulta->numrows < 1) {
    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => []
    ]);
    exit;
}

$pagos = $clsConsulta->consultaGeneral("
    SELECT p.id, p.fecha, p.monto, m.descripcion AS metodo_pago, p.referencia, p.observaciones
    FROM pagos_cxp p
    LEFT JOIN metodo_pago m ON m.id = p.id_metodo_pago
    WHERE p.id_cxp = {$id_cxp}
    ORDER BY p.fecha ASC
");

if (!is_array($pagos) || $clsConsulta->numrows <= 0) $pagos = [];

$data = [];
foreach ($pagos as $pago) {
    $data[] = [
        'id' => $pago['id'],
        'fecha' => $pago['fecha'],
        'monto' => $pago['monto'],
        'metodo_pago' => $pago['metodo_pago'],
        'referencia' => $pago['referencia'],
        'observaciones' => $pago['observaciones']
    ];
}

echo json_encode([
    'draw' => $draw,
    'recordsTotal' => count($data),
    'recordsFiltered' => count($data),
    'data' => $data
]);
