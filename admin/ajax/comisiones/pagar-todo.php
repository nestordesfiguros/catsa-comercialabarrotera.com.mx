<?php
session_start();
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

header('Content-Type: application/json');

try {
    // Obtener todos los IDs de comisiones pendientes directamente
    $sql = "SELECT id FROM comisiones_detalle WHERE pagado = 0";
    $result = $clsConsulta->consultaGeneral($sql);

    if (!$result || count($result) === 0) {
        echo json_encode(['status' => 'ok', 'pagadas' => 0]);
        exit;
    }

    $ids = array_column($result, 'id');
    $total = 0;

    foreach ($ids as $id) {
        $id = intval($id);
        $upd = "UPDATE comisiones_detalle SET pagado = 1 WHERE id = $id";
        if ($clsConsulta->aplicaQuery($upd)) {
            $total++;
        }
    }

    echo json_encode(['status' => 'ok', 'pagadas' => $total]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error interno: ' . $e->getMessage()]);
}
