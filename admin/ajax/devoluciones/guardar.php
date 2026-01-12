<?php
// ajax/devoluciones/guardar.php
session_start();
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

header('Content-Type: application/json');

$cabecera = $_POST['cabecera'] ?? null;
$detalles = $_POST['detalles'] ?? [];

if (!$cabecera || !is_array($cabecera)) {
  echo json_encode(['success' => false, 'message' => 'Faltan datos de cabecera.']);
  exit;
}

$tipo_documento        = $clsConsulta->escape($cabecera['tipo_documento'] ?? '');
$documento_numero      = intval($cabecera['documento_numero'] ?? 0);
$id_cliente            = intval($cabecera['id_cliente'] ?? 0);
$observaciones         = $clsConsulta->escape($cabecera['observaciones'] ?? '');
$id_almacen            = intval($cabecera['id_almacen'] ?? 0);
$generar_nota_credito  = intval($cabecera['generar_nota_credito'] ?? 0);

if (!$tipo_documento || !$documento_numero || !$id_cliente || empty($detalles) || $id_almacen <= 0) {
  echo json_encode(['success' => false, 'message' => 'Datos incompletos para registrar la devolución.']);
  exit;
}

// Iniciar transacción
$clsConsulta->aplicaquery("START TRANSACTION");

try {
  // Calcular monto total
  $monto_total = 0;
  foreach ($detalles as $item) {
    $cantidad = floatval($item['cantidad']);
    $precio = floatval($item['precio_unitario']);
    $monto_total += $cantidad * $precio;
  }
  $monto_total = round($monto_total, 2);

  // Insertar cabecera
  $sqlCab = "
        INSERT INTO cab_devoluciones
        (tipo_documento, id_documento, id_cliente, observaciones, monto_total, fecha, estatus, id_usuario)
        VALUES
        ('$tipo_documento', $documento_numero, $id_cliente, '$observaciones', $monto_total, NOW(), 'activo', {$_SESSION['id_user']})
    ";
  $clsConsulta->guardarGeneral($sqlCab);
  $id_devolucion = $clsConsulta->ultimoid;

  if (!$id_devolucion) {
    throw new Exception('Error al guardar la cabecera de devolución.');
  }

  // Insertar movimientos y actualizar inventario
  foreach ($detalles as $item) {
    $id_producto = intval($item['id_producto']);
    $cantidad = floatval($item['cantidad']);
    $motivo = $clsConsulta->escape($item['motivo'] ?? '');
    $precio_unitario = floatval($item['precio_unitario']);

    if ($cantidad <= 0) continue;

    // Insertar detalle
    $sqlMov = "
            INSERT INTO mov_devoluciones
            (id_devolucion, id_producto, cantidad, motivo, precio_unitario)
            VALUES
            ($id_devolucion, $id_producto, $cantidad, '$motivo', $precio_unitario)
        ";
    if (!$clsConsulta->aplicaquery($sqlMov)) {
      throw new Exception('Error al registrar detalle de devolución.');
    }

    // Revisar inventario
    $sqlCheck = "
            SELECT id, cantidad 
            FROM inventarios 
            WHERE id_almacen = $id_almacen AND id_producto = $id_producto
            LIMIT 1
        ";
    $rsInv = $clsConsulta->consultaGeneral($sqlCheck);

    if ($clsConsulta->numrows > 0) {
      // Actualizar cantidad existente
      $cantidadActual = floatval($rsInv[1]['cantidad']);
      $nuevaCantidad = $cantidadActual + $cantidad;

      $sqlUpdate = "
                UPDATE inventarios 
                SET cantidad = $nuevaCantidad
                WHERE id = {$rsInv[1]['id']}
                LIMIT 1
            ";
      if (!$clsConsulta->aplicaquery($sqlUpdate)) {
        throw new Exception('Error al actualizar inventario.');
      }
    } else {
      // Insertar nuevo registro de inventario
      $sqlInsertInv = "
                INSERT INTO inventarios (id_almacen, id_producto, cantidad)
                VALUES ($id_almacen, $id_producto, $cantidad)
            ";
      if (!$clsConsulta->aplicaquery($sqlInsertInv)) {
        throw new Exception('Error al insertar inventario.');
      }
    }
  }

  // Si el usuario solicitó generar nota de crédito
  if ($generar_nota_credito === 1) {
    // Generar folio automático (ejemplo: NC + id_devolucion)
    $folioNC = 'NC' . str_pad($id_devolucion, 5, '0', STR_PAD_LEFT);

    $motivoNC = "Nota de crédito por devolución del documento $tipo_documento #$documento_numero";

    $sqlNC = "
            INSERT INTO notas_credito
            (id_devolucion, id_cliente, folio, fecha, monto_total, motivo, estatus, id_usuario)
            VALUES
            ($id_devolucion, $id_cliente, '$folioNC', NOW(), $monto_total, '$motivoNC', 'emitida', {$_SESSION['id_user']})
        ";
    if (!$clsConsulta->aplicaquery($sqlNC)) {
      throw new Exception('Error al generar la nota de crédito.');
    }
  }

  // Confirmar transacción
  $clsConsulta->aplicaquery("COMMIT");
  echo json_encode(['success' => true, 'message' => 'Devolución registrada correctamente' . ($generar_nota_credito ? ' con nota de crédito.' : '.')]);
} catch (Exception $e) {
  // Si ocurre error, revertir cambios
  $clsConsulta->aplicaquery("ROLLBACK");
  echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
