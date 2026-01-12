<?php
// ajax/reportes/tabla-compras-por-producto.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$start = (int)($_POST['start'] ?? 0);
$length = (int)($_POST['length'] ?? 25);
$draw = (int)($_POST['draw'] ?? 1);

$f_ini = trim($_POST['f_ini'] ?? '');
$f_fin = trim($_POST['f_fin'] ?? '');

$w = "IFNULL(c.estatus,'') <> 'cancelada'";
if ($f_ini !== '') $w .= " AND DATE(c.fecha) >= '" . $clsConsulta->escape($f_ini) . "'";
if ($f_fin !== '') $w .= " AND DATE(c.fecha) <= '" . $clsConsulta->escape($f_fin) . "'";

$sqlBase = "
  SELECT 
    p.clave, p.nombre,
    SUM(m.cantidad) AS unidades,
    SUM(m.cantidad*m.precio) AS importe_sin_iva
  FROM cab_compras c
  JOIN mov_compras m ON m.id_orden_compra = c.id
  JOIN cat_productos p ON p.id_producto = m.id_producto
  WHERE {$w}
  GROUP BY p.id_producto, p.clave, p.nombre
  ORDER BY importe_sin_iva DESC
";

$tot = $clsConsulta->consultaGeneral("SELECT COUNT(1) c FROM ({$sqlBase}) t");
$recordsTotal = (int)($tot[1]['c'] ?? 0);
$recordsFiltered = $recordsTotal;

$sql = $sqlBase . " LIMIT {$start}, {$length}";
$rs = $clsConsulta->consultaGeneral($sql);

$data = [];
if ($rs && $clsConsulta->numrows > 0) {
  foreach ($rs as $r) {
    $data[] = [
      $r['clave'],
      $r['nombre'],
      number_format((float)$r['unidades'], 2, '.', ','),
      '$' . number_format((float)$r['importe_sin_iva'], 2, '.', ',')
    ];
  }
}

echo json_encode([
  'draw' => $draw,
  'recordsTotal' => $recordsTotal,
  'recordsFiltered' => $recordsFiltered,
  'data' => $data
], JSON_UNESCAPED_UNICODE);
