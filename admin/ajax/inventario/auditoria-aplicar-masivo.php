<?php
// ajax/inventario/auditoria-aplicar-masivo.php
require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();
$db = $clsConsulta->getConexion();

$id_empresa = intval($_SESSION['id_empresa'] ?? 1);
$id_usuario = intval($_SESSION['id_usuario'] ?? 0);

$almacen  = trim($_POST['almacen'] ?? '');
$producto = trim($_POST['producto'] ?? '');
$desde    = trim($_POST['desde'] ?? '');
$hasta    = trim($_POST['hasta'] ?? '');
$limit    = max(1, intval($_POST['limit'] ?? 500));
$dry_run  = intval($_POST['dry_run'] ?? 1); // 1=simula, 0=aplica

// 1) Construir universo de pares producto/almacén como en auditoria-lista
$w = [];
if ($almacen !== '') $w[] = " t.id_almacen = " . intval($almacen);
if ($producto !== '') {
    $safe = $db->real_escape_string($producto);
    $w[] = " (t.sku LIKE '%$safe%' OR t.producto LIKE '%$safe%') ";
}
if ($desde !== '') $w[] = " (t.fecha_conteo IS NULL OR DATE(t.fecha_conteo) >= '" . $db->real_escape_string($desde) . "')";
if ($hasta !== '') $w[] = " (t.fecha_conteo IS NULL OR DATE(t.fecha_conteo) <= '" . $db->real_escape_string($hasta) . "')";
$where = count($w) ? ('WHERE ' . implode(' AND ', $w)) : '';

$sqlBase = "
  SELECT
    par.id_producto,
    par.id_almacen,
    cp.clave AS sku,
    COALESCE(cp.nombre, cp.descripcion, CONCAT('Producto ', cp.id_producto)) AS producto,
    ca.almacen AS nom_almacen,
    COALESCE(inv.cantidad,0) AS teorico,
    c.cantidad               AS contado,
    c.aplicado               AS conteo_aplicado
  FROM
  (
    SELECT id_producto, id_almacen FROM inventarios
    UNION
    SELECT id_producto, id_almacen FROM inv_conteos
    UNION
    SELECT me.id_producto, ce.id_almacen FROM mov_entradas_almacen me JOIN cab_entradas_almacen ce ON ce.id=me.id_orden_compra WHERE ce.estatus='procesada'
    UNION
    SELECT ms.id_producto, cs.id_almacen FROM mov_salidas_almacen  ms JOIN cab_salidas_almacen  cs ON cs.id=ms.id_salida       WHERE cs.estatus='procesada'
    UNION
    SELECT mt.id_producto, ct.id_almacen_origen  FROM mov_traspasos_almacen mt JOIN cab_traspasos_almacen ct ON ct.id_traspaso=mt.id_traspaso WHERE ct.estatus='activo'
    UNION
    SELECT mt.id_producto, ct.id_almacen_destino FROM mov_traspasos_almacen mt JOIN cab_traspasos_almacen ct ON ct.id_traspaso=mt.id_traspaso WHERE ct.estatus='activo'
  ) par
  JOIN cat_productos cp ON cp.id_producto = par.id_producto
  JOIN cat_almacenes ca ON ca.id = par.id_almacen
  LEFT JOIN inventarios inv 
         ON inv.id_producto = par.id_producto AND inv.id_almacen = par.id_almacen
  LEFT JOIN (
    SELECT x.*
    FROM inv_conteos x
    JOIN (
      SELECT id_producto, id_almacen, MAX(fecha_conteo) max_fc
      FROM inv_conteos
      GROUP BY id_producto, id_almacen
    ) u ON u.id_producto=x.id_producto AND u.id_almacen=x.id_almacen AND u.max_fc=x.fecha_conteo
  ) c ON c.id_producto=par.id_producto AND c.id_almacen=par.id_almacen
";

$sqlData = "SELECT * FROM ($sqlBase) t $where LIMIT $limit";
$rows = $clsConsulta->consultaGeneral($sqlData);
$N = $clsConsulta->numrows;

$summary = [
    'processed' => 0,
    'applied_conteo' => 0,
    'applied_kardex' => 0,
    'salidas_creadas' => 0,
    'entradas_creadas' => 0,
    'sin_cambios' => 0,
    'errores' => 0,
    'items' => []
];

function float0($v)
{
    return is_null($v) ? 0.0 : floatval($v);
}

for ($i = 1; $i <= $N; $i++) {
    $r = $rows[$i];
    $id_producto = intval($r['id_producto']);
    $id_almacen  = intval($r['id_almacen']);
    $teorico     = float0($r['teorico']);
    $contado     = isset($r['contado']) ? floatval($r['contado']) : null;
    $aplicado    = intval($r['conteo_aplicado'] ?? 0);
    $sku         = $r['sku'] ?? '';
    $nomProd     = $r['producto'] ?? '';

    // 2) Sumas para kardex/diagnóstico (SIN remisiones en kardex)
    $qS = "
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
  ";
    $rs = $clsConsulta->consultaGeneral($qS);
    $ent  = float0($rs[1]['ent']);
    $sal  = float0($rs[1]['sal']);
    $tent = float0($rs[1]['tent']);
    $tsal = float0($rs[1]['tsal']);
    $rem  = float0($rs[1]['rem']);
    $oc   = float0($rs[1]['oc']);

    $kardex = $ent + $tent - $sal - $tsal;
    $salidas_falt  = max(0, $rem - $sal);
    $entradas_falt = max(0, $oc  - $ent);

    // 3) Selección automática
    $modo = 'kardex';
    if (!is_null($contado) && !$aplicado) $modo = 'conteo';
    else if ($salidas_falt > 0)          $modo = 'generar_salida';
    else if ($entradas_falt > 0)         $modo = 'generar_entrada';

    $summary['processed']++;

    if ($dry_run) {
        $summary['items'][] = [
            'id_producto' => $id_producto,
            'id_almacen' => $id_almacen,
            'sku' => $sku,
            'producto' => $nomProd,
            'teorico' => $teorico,
            'kardex' => $kardex,
            'contado' => $contado,
            'modo' => $modo
        ];
        continue;
    }

    // 4) Ejecutar acción (transacción por producto)
    try {
        $db->begin_transaction();

        // Inventario actual (refrescar para este ciclo)
        $rInv = $clsConsulta->consultaGeneral("SELECT cantidad FROM inventarios WHERE id_producto=$id_producto AND id_almacen=$id_almacen");
        $teo  = isset($rInv[1]['cantidad']) ? floatval($rInv[1]['cantidad']) : 0;

        if ($modo === 'conteo') {
            $objetivo = floatval($contado);
            if (!isset($rInv[1]['cantidad'])) {
                $clsConsulta->aplicaquery("INSERT INTO inventarios (id_almacen,id_producto,cantidad) VALUES ($id_almacen,$id_producto,$objetivo)");
            } else {
                $clsConsulta->aplicaquery("UPDATE inventarios SET cantidad=$objetivo WHERE id_producto=$id_producto AND id_almacen=$id_almacen");
            }
            // marcar último conteo como aplicado
            $clsConsulta->aplicaquery("
        UPDATE inv_conteos ic
        JOIN (
          SELECT id FROM inv_conteos
          WHERE id_producto=$id_producto AND id_almacen=$id_almacen
          ORDER BY fecha_conteo DESC LIMIT 1
        ) u ON u.id=ic.id
        SET ic.aplicado=1
      ");
            $diff = $objetivo - $teo;
            $tipo = ($diff > 0) ? 'AJUSTE_POS' : 'AJUSTE_NEG';
            $clsConsulta->aplicaquery("
        INSERT INTO inv_ajustes (id_empresa,id_almacen,id_producto,diferencia,tipo_ajuste,id_movimiento_ajuste,usuario,notas)
        VALUES ($id_empresa,$id_almacen,$id_producto,$diff,'$tipo',NULL,'auditoria','MASIVO: ajuste por CONTEO')
      ");
            $summary['applied_conteo']++;
        } elseif ($modo === 'kardex') {
            $objetivo = $kardex;
            if (!isset($rInv[1]['cantidad'])) {
                $clsConsulta->aplicaquery("INSERT INTO inventarios (id_almacen,id_producto,cantidad) VALUES ($id_almacen,$id_producto,$objetivo)");
            } else {
                $clsConsulta->aplicaquery("UPDATE inventarios SET cantidad=$objetivo WHERE id_producto=$id_producto AND id_almacen=$id_almacen");
            }
            $diff = $objetivo - $teo;
            if (abs($diff) != 0) {
                $tipo = ($diff > 0) ? 'AJUSTE_POS' : 'AJUSTE_NEG';
                $clsConsulta->aplicaquery("
          INSERT INTO inv_ajustes (id_empresa,id_almacen,id_producto,diferencia,tipo_ajuste,id_movimiento_ajuste,usuario,notas)
          VALUES ($id_empresa,$id_almacen,$id_producto,$diff,'$tipo',NULL,'auditoria','MASIVO: ajuste por KARDEX')
        ");
            } else {
                $summary['sin_cambios']++;
            }
            $summary['applied_kardex']++;
        } elseif ($modo === 'generar_salida') {
            $cant = $salidas_falt;
            if ($cant <= 0) {
                $summary['sin_cambios']++;
                $db->commit();
                continue;
            }

            $clsConsulta->aplicaquery("
        INSERT INTO cab_salidas_almacen (id_almacen, fecha, id_usuario, estatus, total)
        VALUES ($id_almacen, CURDATE(), $id_usuario, 'procesada', 0.00)
      ");
            $id_salida = $db->insert_id;

            $clsConsulta->aplicaquery("
        INSERT INTO mov_salidas_almacen (id_salida, id_producto, cantidad, precio)
        VALUES ($id_salida, $id_producto, $cant, 0.00)
      ");

            $nuevo = max(0, $teo - $cant);
            if (!isset($rInv[1]['cantidad'])) {
                $clsConsulta->aplicaquery("INSERT INTO inventarios (id_almacen,id_producto,cantidad) VALUES ($id_almacen,$id_producto,$nuevo)");
            } else {
                $clsConsulta->aplicaquery("UPDATE inventarios SET cantidad=$nuevo WHERE id_producto=$id_producto AND id_almacen=$id_almacen");
            }

            $clsConsulta->aplicaquery("
        INSERT INTO inv_ajustes (id_empresa,id_almacen,id_producto,diferencia,tipo_ajuste,id_movimiento_ajuste,usuario,notas)
        VALUES ($id_empresa,$id_almacen,$id_producto,-$cant,'AJUSTE_NEG',$id_salida,'auditoria','MASIVO: salida por remisiones pendientes')
      ");
            $summary['salidas_creadas']++;
        } elseif ($modo === 'generar_entrada') {
            $cant = $entradas_falt;
            if ($cant <= 0) {
                $summary['sin_cambios']++;
                $db->commit();
                continue;
            }

            $clsConsulta->aplicaquery("
        INSERT INTO cab_entradas_almacen (id_almacen, id_proveedor, fecha, id_usuario, estatus, total, referencia, tipo_referencia)
        VALUES ($id_almacen, NULL, CURDATE(), $id_usuario, 'procesada', 0.00, 'AJUSTE AUDITORIA MASIVO', 'OTRO')
      ");
            $id_ent = $db->insert_id;

            $clsConsulta->aplicaquery("
        INSERT INTO mov_entradas_almacen (id_orden_compra, id_producto, cantidad, precio)
        VALUES ($id_ent, $id_producto, $cant, 0.00)
      ");

            $nuevo = $teo + $cant;
            if (!isset($rInv[1]['cantidad'])) {
                $clsConsulta->aplicaquery("INSERT INTO inventarios (id_almacen,id_producto,cantidad) VALUES ($id_almacen,$id_producto,$nuevo)");
            } else {
                $clsConsulta->aplicaquery("UPDATE inventarios SET cantidad=$nuevo WHERE id_producto=$id_producto AND id_almacen=$id_almacen");
            }

            $clsConsulta->aplicaquery("
        INSERT INTO inv_ajustes (id_empresa,id_almacen,id_producto,diferencia,tipo_ajuste,id_movimiento_ajuste,usuario,notas)
        VALUES ($id_empresa,$id_almacen,$id_producto,$cant,'AJUSTE_POS',$id_ent,'auditoria','MASIVO: entrada por compras/OC pendientes')
      ");
            $summary['entradas_creadas']++;
        }

        $db->commit();
    } catch (Throwable $e) {
        $db->rollback();
        $summary['errores']++;
        $summary['items'][] = ['id_producto' => $id_producto, 'id_almacen' => $id_almacen, 'error' => $e->getMessage()];
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['success' => true] + $summary);
