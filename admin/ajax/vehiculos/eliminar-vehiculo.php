<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

// Verificar existencia
$check = $clsConsulta->consultaGeneral("SELECT id FROM cat_vehiculos WHERE id = $id");
if ($clsConsulta->numrows === 0) {
    echo json_encode(['success' => false, 'message' => 'Vehículo no encontrado']);
    exit;
}

$sql = "DELETE FROM cat_vehiculos WHERE id = $id";
$res = $clsConsulta->aplicaquery($sql);

echo json_encode(['success' => $res]);
