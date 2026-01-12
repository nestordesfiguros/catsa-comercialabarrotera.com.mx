<?php
// ajax/reportes/tabla-ventas-generales-por-periodo.php
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
  SELECT DATE(r.fecha) AS d
  FROM cab_remisiones r
  JOIN mov_remisiones m ON m.id_remision = r.id
  WHERE {$w}
  GROUP BY DATE(r.fecha)
) t";
$cRes = $clsConsulta->consultaGeneral($sqlCount);
$total = $cRes && isset($cRes[1]['c']) ? (int)$cRes[1]['c'] : 0;

$sql = "
  SELECT DATE(r.fecha) AS dia,
         COUNT(DISTINCT r.id) AS remisiones,
         SUM(m.cantidad) AS unidades,
         SUM(m.cantidad*m.precio) AS venta_sin_iva
  FROM cab_remisiones r
  JOIN mov_remisiones m ON m.id_remision = r.id
  WHERE {$w}
  GROUP BY DATE(r.fecha)
  ORDER BY dia DESC
";
$rs = $clsConsulta->consultaGeneral($sql);

$data = [];
if ($rs) {
  foreach ($rs as $row) {
    $data[] = [
      htmlspecialchars($row['dia']),
      number_format((int)$row['remisiones'], 0, '.', ','),
      number_format((float)$row['unidades'], 2, '.', ','),
      '$' . number_format((float)$row['venta_sin_iva'], 2, '.', ',')
    ];
  }
}

echo json_encode([
  'recordsTotal' => $total,
  'recordsFiltered' => $total,
  'data' => $data
], JSON_UNESCAPED_UNICODE);
