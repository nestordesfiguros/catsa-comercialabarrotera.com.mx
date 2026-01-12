<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

$sql = "SELECT * FROM cat_operadores WHERE id = $id LIMIT 1";
$res = $clsConsulta->consultaGeneral($sql);

if ($clsConsulta->numrows > 0) {
    $data = $res[1];
    echo json_encode(['success' => true, 'data' => $data]);
} else {
    echo json_encode(['success' => false, 'message' => 'Operador no encontrado']);
}
