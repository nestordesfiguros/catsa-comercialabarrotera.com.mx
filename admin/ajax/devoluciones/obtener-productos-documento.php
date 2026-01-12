<?php
// ajax/devoluciones/obtener-productos-documento.php

require_once __DIR__ . '/../../lib/clsConsultas.php';
header('Content-Type: application/json');

$tipo   = $_POST['tipo_documento']   ?? '';
$numero = $_POST['documento_numero'] ?? '';

if (!$tipo || !$numero) {
  echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
  exit;
}

$clsConsulta = new Consultas();
$numero = $clsConsulta->escape($numero);

if ($tipo === 'factura') {
  $cab = $clsConsulta->consultaGeneral("SELECT id, id_receptor FROM cab_facturas WHERE folio = '$numero'");
  if (!$cab) {
    echo json_encode(['success' => false, 'message' => 'Factura no encontrada']);
    exit;
  }
  $id = $cab[1]['id'];
  $id_cliente = $cab[1]['id_receptor'];

  $cliente = $clsConsulta->consultaGeneral("SELECT razon_social, nombre_comercial FROM cat_clientes WHERE id = $id_cliente");
  $nombre_cliente = $cliente ? $cliente[1]['razon_social'] .
    ($cliente[1]['nombre_comercial'] ? ' / ' . $cliente[1]['nombre_comercial'] : '') : 'Desconocido';

  // En facturas seguimos usando el precio de la partida de factura
  $productos = $clsConsulta->consultaGeneral("
    SELECT mf.id_producto, p.nombre, IFNULL(mf.cantidad, 0) AS cantidad, mf.precio AS precio_unitario
    FROM mov_facturas mf
    INNER JOIN cat_productos p ON p.id_producto = mf.id_producto
    WHERE mf.id_factura = $id
  ");
} elseif ($tipo === 'remision') {
  $cab = $clsConsulta->consultaGeneral("SELECT id, id_cliente FROM cab_remisiones WHERE id = '$numero'");
  if (!$cab) {
    echo json_encode(['success' => false, 'message' => 'Remisión no encontrada']);
    exit;
  }
  $id = $cab[1]['id'];
  $id_cliente = $cab[1]['id_cliente'];

  $cliente = $clsConsulta->consultaGeneral("SELECT razon_social, nombre_comercial FROM cat_clientes WHERE id = $id_cliente");
  $nombre_cliente = $cliente ? $cliente[1]['razon_social'] .
    ($cliente[1]['nombre_comercial'] ? ' / ' . $cliente[1]['nombre_comercial'] : '') : 'Desconocido';

  // ⬅️ AQUÍ el cambio: usar SIEMPRE mov_remisiones.precio_unitario
  $productos = $clsConsulta->consultaGeneral("
    SELECT mr.id_producto, p.nombre, IFNULL(mr.cantidad, 0) AS cantidad, mr.precio_unitario AS precio_unitario
    FROM mov_remisiones mr
    INNER JOIN cat_productos p ON p.id_producto = mr.id_producto
    WHERE mr.id_remision = $id
  ");
} else {
  echo json_encode(['success' => false, 'message' => 'Tipo de documento inválido']);
  exit;
}

$productos_normalizados = [];
if ($productos) {
  foreach ($productos as $p) {
    $precio_unit = isset($p['precio_unitario']) ? (float)$p['precio_unitario'] : 0.0;
    $productos_normalizados[] = [
      'id_producto'     => $p['id_producto'],
      'nombre'          => $p['nombre'],
      'cantidad'        => max((int)$p['cantidad'], 1),
      'precio_unitario' => $precio_unit // devolver número; el front lo formatea
    ];
  }
}

echo json_encode([
  'success'        => true,
  'id_cliente'     => $id_cliente,
  'cliente_nombre' => $nombre_cliente,
  'productos'      => $productos_normalizados
]);
