<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id_vendedor = intval($_POST['id_vendedor']);
$fecha_inicio = $_POST['fecha_inicio'] ?? '';
$fecha_fin = $_POST['fecha_fin'] ?? '';
$id_usuario = $_SESSION['id_user'] ?? 0;

// Validación básica
if (!$id_vendedor || !$fecha_inicio || !$fecha_fin || !$id_usuario) {
  echo json_encode(['status' => 'error', 'message' => 'Faltan datos necesarios']);
  exit;
}

// Formateamos fecha de pago actual
$fecha_pago = date('Y-m-d H:i:s');

// Actualiza comisiones en el rango para el vendedor
$sql = "
  UPDATE comisiones
  SET estatus = 'pagado',
      fecha_pago = '$fecha_pago',
      id_usuario_pago = $id_usuario
  WHERE id_vendedor = $id_vendedor
    AND fecha_inicio >= '$fecha_inicio'
    AND fecha_fin <= '$fecha_fin'
    AND estatus = 'pendiente'
";

if ($clsConsulta->aplicaquery($sql)) {
  echo json_encode(['status' => 'success']);
} else {
  echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar el estatus']);
}
