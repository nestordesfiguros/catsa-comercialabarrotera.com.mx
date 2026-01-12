<?php
// ajax/liberar-reserva.php
header('Content-Type: application/json; charset=utf-8');
require '../../admin/lib/clsConsultas.php';
session_start();
$clsConsulta = new Consultas();

$ids = $_POST['ids'] ?? []; // array de id_reserva
if (!is_array($ids) || empty($ids)) {
    echo json_encode(['ok' => false, 'msg' => 'Sin reservas']);
    exit;
}

$idsInt = array_map('intval', $ids);
$idsStr = implode(',', $idsInt);

try {
    $clsConsulta->aplicaquery("START TRANSACTION");
    $sql = "UPDATE inventarios_reservas SET estatus='liberado' WHERE id IN ($idsStr) AND estatus='reservado'";
    $clsConsulta->aplicaquery($sql);
    $clsConsulta->aplicaquery("COMMIT");
    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    $clsConsulta->aplicaquery("ROLLBACK");
    echo json_encode(['ok' => false, 'msg' => 'Error al liberar']);
}
