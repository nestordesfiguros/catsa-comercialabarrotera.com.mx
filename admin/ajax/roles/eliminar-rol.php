<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id = intval($_POST['id'] ?? 0);

// Validación de ID
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

// Verificar si el rol está en uso
$sqlCheck = "SELECT COUNT(*) AS total FROM usuarios WHERE rol = $id";
$res = $clsConsulta->consultaGeneral($sqlCheck);

$total = 0;
if (is_array($res) && isset($res[0]['total'])) {
    $total = intval($res[0]['total']);
}

if ($total > 0) {
    echo json_encode(['success' => false, 'message' => 'No se puede eliminar: el rol está asignado a usuarios']);
    exit;
}

// Eliminar rol
$sqlDelete = "DELETE FROM roles WHERE id = $id";
$ok = $clsConsulta->aplicaquery($sqlDelete);

echo json_encode(['success' => $ok]);
