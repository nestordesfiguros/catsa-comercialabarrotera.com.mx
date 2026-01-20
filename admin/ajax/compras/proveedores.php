<?php
// ajax/compras/proveedores.php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

header('Content-Type: application/json; charset=utf-8');

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;

if ($idEmpresa <= 0) {
    echo json_encode(['success' => false, 'data' => []]);
    exit;
}

$sql = "
    SELECT id, nombre_Comercial, razon_social
    FROM cat_proveedores
    WHERE estatus=1
      AND id_empresa={$idEmpresa}
    ORDER BY nombre_Comercial ASC, razon_social ASC
";

$rs = $clsConsulta->consultaGeneral($sql);

$data = [];
if ($clsConsulta->numrows > 0) {
    foreach ($rs as $i => $val) {
        $nombreComercial = isset($val['nombre_Comercial']) ? trim((string)$val['nombre_Comercial']) : '';
        $razon = isset($val['razon_social']) ? trim((string)$val['razon_social']) : '';

        $nombre = trim($nombreComercial . ' / ' . $razon, " /");

        $data[] = [
            'id' => (int)$val['id'],
            'nombre' => $nombre
        ];
    }
}

echo json_encode([
    'success' => true,
    'data' => $data
]);
