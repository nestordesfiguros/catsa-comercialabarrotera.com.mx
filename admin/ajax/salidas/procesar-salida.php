<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

// Se puede validar más lógica si se requiere (por ejemplo: que tenga productos)

$sql = "UPDATE cab_salida_almacen SET estatus = 'procesado' WHERE id = $id";
$resultado = $clsConsulta->aplicaquery($sql);

if ($resultado) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'No se pudo procesar']);
}
