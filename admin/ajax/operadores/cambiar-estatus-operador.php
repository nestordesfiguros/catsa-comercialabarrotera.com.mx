<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id = intval($_POST['id'] ?? 0);
$valor = intval($_POST['valor'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

$sql = "UPDATE cat_operadores SET activo = $valor WHERE id = $id LIMIT 1";
$res = $clsConsulta->aplicaquery($sql);

echo json_encode(['success' => $res]);
