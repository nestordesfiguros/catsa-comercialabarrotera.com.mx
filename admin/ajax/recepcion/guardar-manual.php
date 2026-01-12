<?php
require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

header('Content-Type: application/json');

$fecha = $_POST['fecha'] ?? null;
$id_proveedor = $_POST['id_proveedor'] ?? null;
$id_almacen = $_POST['id_almacen'] ?? null;
$observaciones = $_POST['observaciones'] ?? '';
$productos = $_POST['productos'] ?? [];

if (!$fecha || !$id_proveedor || !$id_almacen || empty($productos)) {
  echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
  exit;
}

$clsConsulta->guardarGeneral("INSERT INTO cab_entradas_almacen (id_almacen, id_proveedor, fecha, estatus, observaciones) VALUES (
  $id_almacen, $id_proveedor, '$fecha', 'procesada', '{$clsConsulta->escape($observaciones)}')");

$id_entrada = $clsConsulta->ultimoid;

foreach ($productos as $prod) {
  $id_producto = intval($prod['id_producto'] ?? 0);
  $cantidad = intval($prod['cantidad'] ?? 0);
  if ($id_producto <= 0 || $cantidad <= 0) continue;

  $clsConsulta->guardarGeneral("INSERT INTO mov_entradas_almacen (id_orden_compra, id_producto, cantidad) VALUES (
    NULL, $id_producto, $cantidad)");

  // Actualizar inventario
  $inv = $clsConsulta->consultaGeneral("SELECT id, cantidad FROM inventarios WHERE id_almacen = $id_almacen AND id_producto = $id_producto");

  if ($inv && isset($inv[1])) {
    $nuevo = intval($inv[1]['cantidad']) + $cantidad;
    $clsConsulta->guardarGeneral("UPDATE inventarios SET cantidad = $nuevo WHERE id = {$inv[1]['id']}");
  } else {
    $clsConsulta->guardarGeneral("INSERT INTO inventarios (id_almacen, id_producto, cantidad) VALUES (
      $id_almacen, $id_producto, $cantidad)");
  }
}

echo json_encode(['success' => true]);
?>