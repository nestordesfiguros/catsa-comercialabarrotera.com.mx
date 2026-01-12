<?php
// ajax/reportes/tabla-comisiones-por-vendedor.php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$f_ini = isset($_GET['f_ini']) ? trim($_GET['f_ini']) : '';
$f_fin = isset($_GET['f_fin']) ? trim($_GET['f_fin']) : '';
$vendedor = isset($_GET['vendedor']) ? trim($_GET['vendedor']) : '';

$w = "IFNULL(r.estatus,'') <> 'cancelada'";
if ($f_ini !== '') $w .= " AND DATE(r.fecha) >= '" . $clsConsulta->escape($f_ini) . "'";
if ($f_fin !== '') $w .= " AND DATE(r.fecha) <= '" . $clsConsulta->escape($f_fin) . "'";
if ($vendedor !== '') {
  $esc = $clsConsulta->escape($vendedor);
  $w .= " AND CONCAT_WS(' ', v.nombre, v.apellido1, v.apellido2) LIKE '%{$esc}%'";
}

$sqlCount = "
SELECT COUNT(1) c FROM (
  SELECT r.id
  FROM cab_remisiones r
  JOIN mov_remisiones m ON m.id_remision=r.id
  LEFT JOIN cat_vendedores v ON v.id=r.id_vendedor
  WHERE {$w}
  GROUP BY r.id
) t";
$cRes = $clsConsulta->consultaGeneral($sqlCount);
$total = $cRes && isset($cRes[1]['c']) ? (int)$cRes[1]['c'] : 0;

$sql = "
  SELECT
    DATE(r.fecha) AS fecha,
    CONCAT('REM-', r.id) AS folio,
    c.razon_social AS cliente,
    CONCAT_WS(' ', v.nombre, v.apellido1, v.apellido2) AS vendedor,
    SUM(m.cantidad*m.precio) AS venta_sin_iva,
    IFNULL(v.comision,0) AS pct
  FROM cab_remisiones r
  JOIN mov_remisiones m ON m.id_remision=r.id
  LEFT JOIN cat_clientes c ON c.id=r.id_cliente
  LEFT JOIN cat_vendedores v ON v.id=r.id_vendedor
  WHERE {$w}
  GROUP BY r.id, fecha, cliente, vendedor, pct
  ORDER BY r.fecha DESC, r.id DESC
";
$rs = $clsConsulta->consultaGeneral($sql);

$data = [];
if ($rs) {
  foreach ($rs as $row) {
    $venta = (float)$row['venta_sin_iva'];
    $pct   = (float)$row['pct'];
    $comis = $venta * ($pct / 100);
    $data[] = [
      htmlspecialchars($row['fecha']),
      htmlspecialchars($row['folio']),
      htmlspecialchars($row['cliente'] ?? ''),
      htmlspecialchars($row['vendedor'] ?? ''),
      '$' . number_format($venta, 2, '.', ','),
      number_format($pct, 2, '.', ',') . ' %',
      '$' . number_format($comis, 2, '.', ',')
    ];
  }
}

echo json_encode([
  'recordsTotal' => $total,
  'recordsFiltered' => $total,
  'data' => $data
], JSON_UNESCAPED_UNICODE);
