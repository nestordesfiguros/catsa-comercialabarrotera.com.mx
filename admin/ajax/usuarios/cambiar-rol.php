<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id = intval($_POST['id'] ?? 0);
$rolNuevo = intval($_POST['rolNuevo'] ?? 0);

if ($id <= 0 || $rolNuevo <= 0) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

$sql = "UPDATE usuarios SET rol = $rolNuevo WHERE id = $id";
$res = $clsConsulta->aplicaquery($sql);

if ($res) {
    echo json_encode(['success' => true, 'message' => 'Rol actualizado correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el rol']);
}
