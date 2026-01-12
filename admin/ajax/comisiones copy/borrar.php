<?php
require_once '../../lib/clsConsultas.php';
session_start();

$clsConsulta = new Consultas();
$id = intval($_POST['id'] ?? 0);

if (!$id) {
    echo json_encode(['success' => false, 'msg' => 'ID inválido']);
    exit;
}

// Validar que la comisión no esté marcada como pagada
$check = $clsConsulta->consultaGeneral("SELECT estatus FROM comisiones WHERE id = $id LIMIT 1");

if (!$clsConsulta->numrows) {
    echo json_encode(['success' => false, 'msg' => 'No se encontró el registro']);
    exit;
}

if ($check[0]['estatus'] === 'pagado') {
    echo json_encode(['success' => false, 'msg' => 'No se puede eliminar una comisión ya pagada']);
    exit;
}

$sql = "DELETE FROM comisiones WHERE id = $id";
$res = $clsConsulta->aplicaQuery($sql);
echo json_encode(['success' => $res]);

?>