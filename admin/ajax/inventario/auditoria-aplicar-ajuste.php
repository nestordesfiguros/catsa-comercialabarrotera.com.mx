<?php
// ajax/inventario/auditoria-aplicar-ajuste.php
require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();
$db = $clsConsulta->getConexion();

$id_producto = intval($_POST['id_producto'] ?? 0);
$id_almacen  = intval($_POST['id_almacen'] ?? 0);
$modo        = trim($_POST['modo'] ?? 'auto'); // conteo | kardex | generar_salida | generar_entrada | auto
$id_usuario  = intval($_SESSION['id_usuario'] ?? 0);

if (!$id_producto || !$id_almacen) {
    echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
    exit;
}

/* Inventario actual */
$rInv = $clsConsulta->consultaGeneral("SELECT cantidad FROM inventarios WHERE id_producto=$id_producto AND id_almacen=$id_almacen");
$teorico = isset($rInv[1]['cantidad']) ? (float)$rInv[1]['cantidad'] : 0;

/* Último conteo (si existe y no aplicado) */
$rC = $clsConsulta->consultaGeneral("
  SELECT id, cantidad, aplicado 
  FROM inv_conteos
  WHERE id_producto=$id_producto AND id_almacen=$id_almacen
  ORDER BY fecha_conteo DESC LIMIT 1
");
$tieneConteo = ($clsConsulta->numrows > 0);
$id_conteo   = $tieneConteo ? intval($rC[1]['id']) : null;
$contado     = $tieneConteo ? (float)$rC[1]['cantidad'] : null;
$aplicado    = $tieneConteo ? intval($rC[1]['aplicado']) : 0;

/* Sumas para kardex y diagnóstico */
$rS = $clsConsulta->consultaGeneral("
  SELECT
    (SELECT COALESCE(SUM(me.cantidad),0)
       FROM mov_entradas_almacen me JOIN cab_entradas_almacen ce ON ce.id=me.id_orden_compra
      WHERE me.id_producto=$id_producto AND ce.id_almacen=$id_almacen AND ce.estatus='procesada') AS ent,
    (SELECT COALESCE(SUM(ms.cantidad),0)
       FROM mov_salidas_almacen ms  JOIN cab_salidas_almacen cs ON cs.id=ms.id_salida
      WHERE ms.id_producto=$id_producto AND cs.id_almacen=$id_almacen AND cs.estatus='procesada') AS sal,
    (SELECT COALESCE(SUM(mt.cantidad),0)
       FROM mov_traspasos_almacen mt JOIN cab_traspasos_almacen ct ON ct.id_traspaso=mt.id_traspaso
      WHERE mt.id_producto=$id_producto AND ct.id_almacen_destino=$id_almacen AND ct.estatus='activo') AS tent,
    (SELECT COALESCE(SUM(mt2.cantidad),0)
       FROM mov_traspasos_almacen mt2 JOIN cab_traspasos_almacen ct2 ON ct2.id_traspaso=mt2.id_traspaso
      WHERE mt2.id_producto=$id_producto AND ct2.id_almacen_origen=$id_almacen AND ct2.estatus='activo') AS tsal,
    (SELECT COALESCE(SUM(mr.cantidad),0)
       FROM mov_remisiones mr JOIN cab_remisiones cr ON cr.id=mr.id_remision
      WHERE mr.id_producto=$id_producto AND cr.id_almacen=$id_almacen AND cr.estatus='procesada') AS rem,
    (SELECT COALESCE(SUM(me2.cantidad),0)
       FROM mov_entradas_almacen me2 JOIN cab_entradas_almacen ce2 ON ce2.id=me2.id_orden_compra
      WHERE me2.id_producto=$id_producto AND ce2.id_almacen=$id_almacen) AS oc
");
$ent  = (float)$rS[1]['ent'];
$sal  = (float)$rS[1]['sal'];
$tent = (float)$rS[1]['tent'];
$tsal = (float)$rS[1]['tsal'];
$rem  = (float)$rS[1]['rem'];
$oc   = (float)$rS[1]['oc'];

$kardex = $ent + $tent - $sal - $tsal;
$salidas_falt  = max(0, $rem - $sal);
$entradas_falt = max(0, $oc - $ent);

/* Selección automática si procede */
if ($modo === 'auto') {
    if ($tieneConteo && !$aplicado) $modo = 'conteo';
    else if ($salidas_falt > 0)     $modo = 'generar_salida';
    else if ($entradas_falt > 0)    $modo = 'generar_entrada';
    else                            $modo = 'kardex';
}

$db->begin_transaction();
try {
    if ($modo === 'conteo') {
        if (!$tieneConteo || $aplicado) throw new Exception('No hay conteo pendiente.');
        $objetivo = $contado;
        if (abs($objetivo - $teorico) == 0) throw new Exception('No hay diferencia por ajustar (conteo).');

        if (!isset($rInv[1]['cantidad'])) {
            $clsConsulta->aplicaquery("INSERT INTO inventarios (id_almacen,id_producto,cantidad) VALUES ($id_almacen,$id_producto,$objetivo)");
        } else {
            $clsConsulta->aplicaquery("UPDATE inventarios SET cantidad=$objetivo WHERE id_producto=$id_producto AND id_almacen=$id_almacen");
        }
        $clsConsulta->aplicaquery("UPDATE inv_conteos SET aplicado=1 WHERE id=$id_conteo");

        $diff = $objetivo - $teorico;
        $tipo = ($diff > 0) ? 'AJUSTE_POS' : 'AJUSTE_NEG';
        $clsConsulta->aplicaquery(
            "INSERT INTO inv_ajustes (id_empresa,id_almacen,id_producto,diferencia,tipo_ajuste,id_movimiento_ajuste,usuario,notas)
       VALUES (1,$id_almacen,$id_producto,$diff,'$tipo',NULL,'auditoria','Ajuste por CONTEO')"
        );
    } elseif ($modo === 'kardex') {
        $objetivo = $kardex;
        if (abs($objetivo - $teorico) == 0) throw new Exception('No hay diferencia por ajustar (kardex).');

        if (!isset($rInv[1]['cantidad'])) {
            $clsConsulta->aplicaquery("INSERT INTO inventarios (id_almacen,id_producto,cantidad) VALUES ($id_almacen,$id_producto,$objetivo)");
        } else {
            $clsConsulta->aplicaquery("UPDATE inventarios SET cantidad=$objetivo WHERE id_producto=$id_producto AND id_almacen=$id_almacen");
        }

        $diff = $objetivo - $teorico;
        $tipo = ($diff > 0) ? 'AJUSTE_POS' : 'AJUSTE_NEG';
        $clsConsulta->aplicaquery(
            "INSERT INTO inv_ajustes (id_empresa,id_almacen,id_producto,diferencia,tipo_ajuste,id_movimiento_ajuste,usuario,notas)
       VALUES (1,$id_almacen,$id_producto,$diff,'$tipo',NULL,'auditoria','Ajuste por MOVIMIENTOS (kardex)')"
        );
    } elseif ($modo === 'generar_salida') {
        if ($salidas_falt <= 0) throw new Exception('No hay salidas pendientes por remisiones.');

        // Crear salida procesada
        $clsConsulta->aplicaquery(
            "INSERT INTO cab_salidas_almacen (id_almacen, fecha, id_usuario, estatus, total)
       VALUES ($id_almacen, CURDATE(), $id_usuario, 'procesada', 0.00)"
        );
        $id_salida = $db->insert_id;

        $clsConsulta->aplicaquery(
            "INSERT INTO mov_salidas_almacen (id_salida, id_producto, cantidad, precio)
       VALUES ($id_salida, $id_producto, $salidas_falt, 0.00)"
        );

        $nuevo = max(0, $teorico - $salidas_falt);
        if (!isset($rInv[1]['cantidad'])) {
            $clsConsulta->aplicaquery("INSERT INTO inventarios (id_almacen,id_producto,cantidad) VALUES ($id_almacen,$id_producto,$nuevo)");
        } else {
            $clsConsulta->aplicaquery("UPDATE inventarios SET cantidad=$nuevo WHERE id_producto=$id_producto AND id_almacen=$id_almacen");
        }

        $clsConsulta->aplicaquery(
            "INSERT INTO inv_ajustes (id_empresa,id_almacen,id_producto,diferencia,tipo_ajuste,id_movimiento_ajuste,usuario,notas)
       VALUES (1,$id_almacen,$id_producto,-$salidas_falt,'AJUSTE_NEG',$id_salida,'auditoria','Generada SALIDA por remisiones pendientes')"
        );
    } elseif ($modo === 'generar_entrada') {
        if ($entradas_falt <= 0) throw new Exception('No hay entradas pendientes por compras/OC.');

        // Crear entrada procesada
        $clsConsulta->aplicaquery(
            "INSERT INTO cab_entradas_almacen (id_almacen, id_proveedor, fecha, id_usuario, estatus, total, referencia, tipo_referencia)
       VALUES ($id_almacen, NULL, CURDATE(), $id_usuario, 'procesada', 0.00, 'AJUSTE AUDITORIA', 'OTRO')"
        );
        $id_ent = $db->insert_id;

        $clsConsulta->aplicaquery(
            "INSERT INTO mov_entradas_almacen (id_orden_compra, id_producto, cantidad, precio)
       VALUES ($id_ent, $id_producto, $entradas_falt, 0.00)"
        );

        $nuevo = $teorico + $entradas_falt;
        if (!isset($rInv[1]['cantidad'])) {
            $clsConsulta->aplicaquery("INSERT INTO inventarios (id_almacen,id_producto,cantidad) VALUES ($id_almacen,$id_producto,$nuevo)");
        } else {
            $clsConsulta->aplicaquery("UPDATE inventarios SET cantidad=$nuevo WHERE id_producto=$id_producto AND id_almacen=$id_almacen");
        }

        $clsConsulta->aplicaquery(
            "INSERT INTO inv_ajustes (id_empresa,id_almacen,id_producto,diferencia,tipo_ajuste,id_movimiento_ajuste,usuario,notas)
       VALUES (1,$id_almacen,$id_producto,$entradas_falt,'AJUSTE_POS',$id_ent,'auditoria','Generada ENTRADA por compras/OC pendientes')"
        );
    } else {
        throw new Exception('Modo no soportado');
    }

    $db->commit();
    echo json_encode(['success' => true, 'message' => 'Operación realizada correctamente.']);
} catch (Throwable $e) {
    $db->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
