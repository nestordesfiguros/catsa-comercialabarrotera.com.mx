<?php
// ajax/reportes/tabla-utilidades-por-venta-general.php
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
  SELECT DATE(r.fecha) d
  FROM cab_remisiones r
  JOIN mov_remisiones m ON m.id_remision=r.id
  JOIN cat_productos p ON p.id_producto=m.id_producto
  LEFT JOIN cat_vendedores v ON v.id=r.id_vendedor
  WHERE {$w}
  GROUP BY DATE(r.fecha)
) t";
$cRes = $clsConsulta->consultaGeneral($sqlCount);
$total = $cRes && isset($cRes[1]['c']) ? (int)$cRes[1]['c'] : 0;

$sql = "
  SELECT DATE(r.fecha) AS dia,
         SUM(m.cantidad*m.precio) AS venta,
         SUM(m.cantidad*p.precio_compra) AS costo,
         AVG(IFNULL(v.comision,0)) AS pct
  FROM cab_remisiones r
  JOIN mov_remisiones m ON m.id_remision=r.id
  JOIN cat_productos p ON p.id_producto=m.id_producto
  LEFT JOIN cat_vendedores v ON v.id=r.id_vendedor
  WHERE {$w}
  GROUP BY DATE(r.fecha)
  ORDER BY dia DESC
";
$rs = $clsConsulta->consultaGeneral($sql);

$data = [];
if ($rs) {
    foreach ($rs as $row) {
        $venta = (float)$row['venta'];
        $costo = (float)$row['costo'];
        $bruta = $venta - $costo;                 // utilidad bruta
        $comis = $venta * ((float)$row['pct'] / 100);
        $neta  = $bruta - $comis;                 // utilidad neta
        $data[] = [
            htmlspecialchars($row['dia']),
            '$' . number_format($venta, 2, '.', ','),
            '$' . number_format($costo, 2, '.', ','),
            '$' . number_format($bruta, 2, '.', ','),
            '$' . number_format($comis, 2, '.', ','),
            '$' . number_format($neta, 2, '.', ',')
        ];
    }
}

echo json_encode([
    'recordsTotal' => $total,
    'recordsFiltered' => $total,
    'data' => $data
], JSON_UNESCAPED_UNICODE);
