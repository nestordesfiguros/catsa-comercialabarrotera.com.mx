<?php
session_start();
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

header('Content-Type: application/json');

$id_usuario = $_SESSION['id_user'] ?? 0;

// Recibir y decodificar JSON del frontend
$remisiones = isset($_POST['ids']) ? json_decode($_POST['ids'], true) : [];

if (!is_array($remisiones) || empty($remisiones)) {
    echo json_encode(['status' => 'error', 'mensaje' => 'No se recibieron remisiones.']);
    exit;
}

$pagadas = 0;

foreach ($remisiones as $id_detalle) {
    $id = intval($id_detalle);

    // Validar existencia y estatus
    $check = $clsConsulta->consultaGeneral("SELECT pagado FROM comisiones_detalle WHERE id = $id LIMIT 1");
    if ($clsConsulta->numrows == 0) continue;
    if (isset($check[0]['pagado']) && $check[0]['pagado'] == 1) continue;

    // ⚠️ SOLO SE ACTUALIZA `pagado = 1`, ya que los demás campos no existen
    $sql = "UPDATE comisiones_detalle SET pagado = 1 WHERE id = $id";
    $clsConsulta->aplicaQuery($sql);
    $pagadas++;
}

echo json_encode(['status' => 'ok', 'pagadas' => $pagadas]);
exit;
