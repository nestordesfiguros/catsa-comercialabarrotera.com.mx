<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id = intval($_POST['id'] ?? 0);
$valor = intval($_POST['valor'] ?? -1); // <- asegúrate de usar 'valor', NO 'activo'

if ($id <= 0 || ($valor !== 0 && $valor !== 1)) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

$sql = "UPDATE cat_vehiculos SET activo = $valor WHERE id = $id LIMIT 1";
$res = $clsConsulta->aplicaquery($sql);

echo json_encode(['success' => $res]);
