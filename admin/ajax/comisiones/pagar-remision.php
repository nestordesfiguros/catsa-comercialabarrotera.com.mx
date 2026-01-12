<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id = intval($_POST['id'] ?? 0);
if ($id <= 0) {
  echo json_encode(['status' => 'error', 'mensaje' => 'ID inválido']);
  exit;
}

$sql = "UPDATE comisiones_detalle SET pagado = 1 WHERE id = $id";
$ok = $clsConsulta->aplicaquery($sql);

if ($ok) {
  echo json_encode(['status' => 'ok']);
} else {
  echo json_encode(['status' => 'error', 'mensaje' => 'Error al actualizar']);
}
exit;
