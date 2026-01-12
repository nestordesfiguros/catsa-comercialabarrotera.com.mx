<?php
// ajax/reportes/tabla-utilidades-por-producto.php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$f_ini = isset($_GET['f_ini']) ? trim($_GET['f_ini']) : '';
$f_fin = isset($_GET['f_fin']) ? trim($_GET['f_fin']) : '';

$w = "IFNULL(r.estatus,'') <> 'cancelada'";
if ($f_ini !== '') $w .= " AND DATE(r.fecha) >= '" . $clsConsulta->escape($f_ini) . "'";
if ($f_fin !== '') $w .= " AND DATE(r.fecha) <= '" . $clsConsulta->escape($f_fin) . "'";

$sqlCount = "
SELECT COUNT(1) c FROM (
  SELECT p.id_producto
  FROM cab_remisiones r
  JOIN mov_remisiones m ON m.id_remision=r.id
  JOIN cat_productos p ON p.id_producto=m.id_producto
  LEFT JOIN cat_vendedores v ON v.id=r.id_vendedor
  WHERE {$w}
  GROUP BY p.id_producto
) t";
$cRes = $clsConsulta->consultaGeneral($sqlCount);
$total = $cRes && isset($cRes[1]['c']) ? (int)$cRes[1]['c'] : 0;

$sql = "
  SELECT 
    p.id_producto, p.clave, p.nombre,
    SUM(m.cantidad) AS unidades,
    SUM(m.cantidad*m.precio) AS venta,
    SUM(m.cantidad * p.precio_compra) AS costo,
    AVG(IFNULL(v.comision,0)) AS pct_comision
  FROM cab_remisiones r
  JOIN mov_remisiones m ON m.id_remision=r.id
  JOIN cat_productos p ON p.id_producto=m.id_producto
  LEFT JOIN cat_vendedores v ON v.id=r.id_vendedor
  WHERE {$w}
  GROUP BY p.id_producto, p.clave, p.nombre
  ORDER BY venta DESC
";
$rs = $clsConsulta->consultaGeneral($sql);

$data = [];
if ($rs) {
  foreach ($rs as $row) {
    $venta = (float)$row['venta'];
    $costo = (float)$row['costo'];
    $bruta = $venta - $costo;
    $comis = $venta * ((float)$row['pct_comision'] / 100);
    $neta  = $bruta - $comis;
    $data[] = [
      htmlspecialchars($row['clave']),
      htmlspecialchars($row['nombre']),
      number_format((float)$row['unidades'], 2, '.', ','),
      '$' . number_format($venta, 2, '.', ','),
      '$' . number_format($costo, 2, '.', ','),
      '$' . number_format($bruta, 2, '.', ','),
      '$' . number_format($neta, 2, '.', ',')
    ];
  }
}

echo json_encode([
  'recordsTotal' => $total,
  'recordsFiltered' => $total,
  'data' => $data
], JSON_UNESCAPED_UNICODE);
