<?php
// ajax/compras/almacenes.php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
if ($idEmpresa <= 0) {
    echo json_encode(['success' => false, 'data' => []]);
    exit;
}

$sql = "SELECT id, almacen
        FROM cat_almacenes
        WHERE id_empresa = {$idEmpresa}
          AND estatus = 1
        ORDER BY almacen ASC";

$rs = $clsConsulta->consultaGeneral($sql);

$data = [];
if ($clsConsulta->numrows > 0 && is_array($rs)) {
    foreach ($rs as $row) {
        $data[] = [
            'id' => (int)$row['id'],
            'almacen' => (string)$row['almacen']
        ];
    }
}

echo json_encode(['success' => true, 'data' => $data]);
