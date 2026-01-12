<?php
// ajax/inventario/auditoria-lista.php
require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();
$db = $clsConsulta->getConexion();

$draw   = intval($_POST['draw'] ?? 1);
$start  = intval($_POST['start'] ?? 0);
$length = intval($_POST['length'] ?? 10);

$almacen  = trim($_POST['almacen'] ?? '');
$producto = trim($_POST['producto'] ?? '');
$desde    = trim($_POST['desde'] ?? '');
$hasta    = trim($_POST['hasta'] ?? '');

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
    ca.almacen AS almacen,
    COALESCE(inv.cantidad,0) AS teorico,
    COALESCE(k.kardex,0)     AS kardex,
    c.cantidad               AS contado,
    c.fecha_conteo
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
  /* Kardex SIN remisiones */
  LEFT JOIN (
    SELECT base.id_producto, base.id_almacen,
           COALESCE(ent.ent,0) + COALESCE(tent.tent,0)
           - COALESCE(sal.sal,0) - COALESCE(tsal.tsal,0) AS kardex
    FROM (
      SELECT id_producto, id_almacen FROM inventarios
      UNION SELECT id_producto, id_almacen FROM inv_conteos
      UNION SELECT me.id_producto, ce.id_almacen FROM mov_entradas_almacen me JOIN cab_entradas_almacen ce ON ce.id=me.id_orden_compra WHERE ce.estatus='procesada'
      UNION SELECT ms.id_producto, cs.id_almacen FROM mov_salidas_almacen  ms JOIN cab_salidas_almacen  cs ON cs.id=ms.id_salida       WHERE cs.estatus='procesada'
      UNION SELECT mt.id_producto, ct.id_almacen_origen  FROM mov_traspasos_almacen mt JOIN cab_traspasos_almacen ct ON ct.id_traspaso=mt.id_traspaso WHERE ct.estatus='activo'
      UNION SELECT mt.id_producto, ct.id_almacen_destino FROM mov_traspasos_almacen mt JOIN cab_traspasos_almacen ct ON ct.id_traspaso=mt.id_traspaso WHERE ct.estatus='activo'
    ) base
    LEFT JOIN (
      SELECT me.id_producto, ce.id_almacen, SUM(me.cantidad) ent
      FROM mov_entradas_almacen me JOIN cab_entradas_almacen ce ON ce.id=me.id_orden_compra
      WHERE ce.estatus='procesada'
      GROUP BY me.id_producto, ce.id_almacen
    ) ent  ON ent.id_producto=base.id_producto  AND ent.id_almacen=base.id_almacen
    LEFT JOIN (
      SELECT mt.id_producto, ct.id_almacen_destino id_almacen, SUM(mt.cantidad) tent
      FROM mov_traspasos_almacen mt JOIN cab_traspasos_almacen ct ON ct.id_traspaso=mt.id_traspaso
      WHERE ct.estatus='activo'
      GROUP BY mt.id_producto, ct.id_almacen_destino
    ) tent ON tent.id_producto=base.id_producto AND tent.id_almacen=base.id_almacen
    LEFT JOIN (
      SELECT ms.id_producto, cs.id_almacen, SUM(ms.cantidad) sal
      FROM mov_salidas_almacen ms JOIN cab_salidas_almacen cs ON cs.id=ms.id_salida
      WHERE cs.estatus='procesada'
      GROUP BY ms.id_producto, cs.id_almacen
    ) sal  ON sal.id_producto=base.id_producto  AND sal.id_almacen=base.id_almacen
    LEFT JOIN (
      SELECT mt.id_producto, ct.id_almacen_origen id_almacen, SUM(mt.cantidad) tsal
      FROM mov_traspasos_almacen mt JOIN cab_traspasos_almacen ct ON ct.id_traspaso=mt.id_traspaso
      WHERE ct.estatus='activo'
      GROUP BY mt.id_producto, ct.id_almacen_origen
    ) tsal ON tsal.id_producto=base.id_producto AND tsal.id_almacen=base.id_almacen
  ) k ON k.id_producto=par.id_producto AND k.id_almacen=par.id_almacen
  /* Último conteo (si existe) */
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

$sqlCount = "SELECT COUNT(1) total FROM ($sqlBase) t $where";
$rCount   = $clsConsulta->consultaGeneral($sqlCount);
$total    = intval($rCount[1]['total'] ?? 0);

$sqlData  = "SELECT * FROM ($sqlBase) t $where LIMIT $start, $length";
$rows     = $clsConsulta->consultaGeneral($sqlData);

$data = [];
if ($clsConsulta->numrows > 0) {
  for ($i = 1; $i <= $clsConsulta->numrows; $i++) {
    $r = $rows[$i];
    $teorico = (float)$r['teorico'];
    $kardex  = (float)$r['kardex'];
    $contado = isset($r['contado']) ? (float)$r['contado'] : null;

    $usa_conteo = ($contado !== null);
    $diff = $usa_conteo ? ($contado - $teorico) : ($kardex - $teorico);
    $estatus = $usa_conteo
      ? (abs($diff) == 0 ? 'Conciliado (conteo)' : 'Pendiente de ajuste (conteo)')
      : (abs($diff) == 0 ? 'Conciliado (movimientos)' : 'Pendiente de ajuste (movimientos)');

    $data[] = [
      'id_producto' => $r['id_producto'],
      'id_almacen'  => $r['id_almacen'],
      'sku'         => $r['sku'],
      'producto'    => $r['producto'],
      'almacen'     => $r['almacen'],
      'teorico'     => number_format($teorico, 4, '.', ''),
      'contado'     => $usa_conteo ? number_format($contado, 4, '.', '') : '',
      'diferencia'  => number_format($diff, 4, '.', ''),
      'estatus'     => $estatus,
      'ultimo_mov'  => '', // opcional
      'aplicable'   => (abs($diff) == 0 ? 0 : 1)
    ];
  }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
  'draw' => $draw,
  'recordsTotal' => $total,
  'recordsFiltered' => $total,
  'data' => $data
]);
