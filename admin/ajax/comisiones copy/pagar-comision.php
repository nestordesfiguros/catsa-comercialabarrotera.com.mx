<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id_comision = intval($_POST['id_comision'] ?? 0);

if ($id_comision <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
    exit;
}

// Verificar que exista y esté pendiente
$check = $clsConsulta->consultaGeneral("
    SELECT id FROM comisiones
    WHERE id = $id_comision AND estatus = 'pendiente'
    LIMIT 1
");

if ($clsConsulta->numrows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'No se encontró la comisión pendiente']);
    exit;
}

// Actualizar estatus
$sql = "UPDATE comisiones SET estatus = 'pagado' WHERE id = $id_comision";
$ok = $clsConsulta->aplicaquery($sql);

if ($ok) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar']);
}
