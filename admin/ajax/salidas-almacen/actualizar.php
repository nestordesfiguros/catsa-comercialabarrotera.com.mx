<?php
// ajax/salidas-almacen/actualizar.php
require_once '../../lib/clsConsultas.php';
header('Content-Type: application/json');
session_start();
$clsConsulta = new Consultas();

$id_salida = intval($_POST['id_salida'] ?? 0);
$cabecera  = $_POST['cabecera'] ?? null;
$detalles  = $_POST['detalles'] ?? [];
$remisiones = $_POST['remisiones'] ?? [];

if ($id_salida <= 0 || !$cabecera) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

$obs        = $clsConsulta->escape($cabecera['observaciones'] ?? '');
$id_almacen = intval($cabecera['id_almacen'] ?? 0);
if ($id_almacen <= 0) {
    echo json_encode(['success' => false, 'message' => 'Almacén inválido']);
    exit;
}

/* No permitir editar canceladas */
$st = $clsConsulta->consultaGeneral("SELECT estatus FROM cab_salidas_almacen WHERE id_salida=$id_salida");
if (!$st || $st[1]['estatus'] !== 'activo') {
    echo json_encode(['success' => false, 'message' => 'No editable']);
    exit;
}

/* Reversa inventario del detalle anterior */
$ant = $clsConsulta->consultaGeneral("SELECT id_producto, cantidad FROM mov_salidas_almacen WHERE id_salida=$id_salida");
if ($ant) {
    foreach ($ant as $k => $row) {
        if ($k === 0) continue;
        $idp = intval($row['id_producto']);
        $cant = (float)$row['cantidad'];
        $clsConsulta->aplicaquery("
      UPDATE inventarios SET cantidad = IFNULL(cantidad,0) + $cant
      WHERE id_almacen=$id_almacen AND id_producto=$idp
    ");
    }
}
/* Borrar detalle anterior */
$clsConsulta->aplicaquery("DELETE FROM mov_salidas_almacen WHERE id_salida=$id_salida");

/* Insertar nuevo detalle y descontar inventario */
$total = 0.0;
foreach ($detalles as $d) {
    $idp  = intval($d['id_producto']);
    $cant = (float)$d['cantidad'];
    $pu = (float)$d['precio_unitario'];
    $id_rem = isset($d['id_remision']) ? intval($d['id_remision']) : 'NULL';
    $mot  = $clsConsulta->escape($d['motivo'] ?? '');

    if ($idp <= 0 || $cant <= 0) {
        echo json_encode(['success' => false, 'message' => 'Partidas inválidas']);
        exit;
    }

    // validación de existencia
    $ex = $clsConsulta->consultaGeneral("SELECT cantidad FROM inventarios WHERE id_almacen=$id_almacen AND id_producto=$idp LIMIT 1");
    $disp = (float)($ex[1]['cantidad'] ?? 0);
    if ($disp < $cant) {
        echo json_encode(['success' => false, 'message' => "Inventario insuficiente para $idp"]);
        exit;
    }

    $clsConsulta->aplicaquery("
    INSERT INTO mov_salidas_almacen (id_salida, id_producto, cantidad, precio_unitario, id_remision, motivo)
    VALUES ($id_salida, $idp, $cant, $pu, " . ($id_rem ?: 'NULL') . ", '$mot')
  ");
    $clsConsulta->aplicaquery("
    UPDATE inventarios SET cantidad = IFNULL(cantidad,0) - $cant
    WHERE id_almacen=$id_almacen AND id_producto=$idp
  ");
    $total += $cant * $pu;
}

/* Actualizar cabecera y relación de remisiones */
$clsConsulta->aplicaquery("UPDATE cab_salidas_almacen SET observaciones='$obs', total=$total WHERE id_salida=$id_salida");
$clsConsulta->aplicaquery("DELETE FROM rel_salidas_remisiones WHERE id_salida=$id_salida");
if (is_array($remisiones) && count($remisiones) > 0) {
    foreach ($remisiones as $id_rem) {
        $id_rem = intval($id_rem);
        if ($id_rem > 0) $clsConsulta->aplicaquery("INSERT IGNORE INTO rel_salidas_remisiones (id_salida, id_remision) VALUES ($id_salida, $id_rem)");
    }
}

echo json_encode(['success' => true, 'message' => 'Salida actualizada']);
