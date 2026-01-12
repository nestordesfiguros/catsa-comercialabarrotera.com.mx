<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

$sql = "UPDATE cab_salida_almacen SET estatus = 'cancelado' WHERE id = $id";
$resultado = $clsConsulta->aplicaquery($sql);

if ($resultado) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'No se pudo cancelar']);
}
