<?php
session_start();
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

header('Content-Type: application/json');

// Validar parámetros
$id = intval($_POST['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['status' => 'error', 'mensaje' => 'ID inválido']);
    exit;
}

// Verificar si ya está pagado
$sqlVerifica = "SELECT estatus FROM comisiones_detalle WHERE id = $id LIMIT 1";
$res = $clsConsulta->consultaGeneral($sqlVerifica);

if ($clsConsulta->numrows === 0) {
    echo json_encode(['status' => 'error', 'mensaje' => 'Comisión no encontrada']);
    exit;
}

if ($res[0]['estatus'] === 'pagado') {
    echo json_encode(['status' => 'error', 'mensaje' => 'Esta comisión ya fue pagada']);
    exit;
}

// Actualizar a pagado
$fecha_pago = date('Y-m-d H:i:s');
$id_usuario = $_SESSION['id_user'] ?? 0;

$sql = "UPDATE comisiones_detalle 
        SET estatus = 'pagado', fecha_pago = '$fecha_pago', id_usuario_pago = $id_usuario 
        WHERE id = $id";

$ok = $clsConsulta->aplicaQuery($sql);

if ($ok) {
    echo json_encode(['status' => 'ok']);
} else {
    echo json_encode(['status' => 'error', 'mensaje' => 'Error al actualizar']);
}
