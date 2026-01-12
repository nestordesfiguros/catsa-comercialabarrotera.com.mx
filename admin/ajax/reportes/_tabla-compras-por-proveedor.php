<?php
// ajax/reportes/tabla-compras-por-proveedor.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

/* Tablas asumidas:
   cab_compras (id, id_proveedor, fecha, estatus, total)
   mov_compras (id_orden_compra -> id, id_producto, cantidad, precio)
   cat_proveedores (id, razon_social)  <-- si tu tabla se llama distinto, solo cambia aquí.
*/
$start = (int)($_POST['start'] ?? 0);
$length = (int)($_POST['length'] ?? 25);
$draw = (int)($_POST['draw'] ?? 1);

$f_ini = trim($_POST['f_ini'] ?? '');
$f_fin = trim($_POST['f_fin'] ?? '');

$w = "IFNULL(c.estatus,'') <> 'cancelada'";
if ($f_ini !== '') $w .= " AND DATE(c.fecha) >= '" . $clsConsulta->real_escape_string($f_ini) . "'";
if ($f_fin !== '') $w .= " AND DATE(c.fecha) <= '" . $clsConsulta->real_escape_string($f_fin) . "'";

$sqlBase = "
  SELECT 
    pr.razon_social AS proveedor,
    COUNT(DISTINCT c.id) AS compras,
    SUM(m.cantidad) AS unidades,
    SUM(m.cantidad*m.precio) AS importe_sin_iva
  FROM cab_compras c
  JOIN mov_compras m ON m.id_orden_compra = c.id
  LEFT JOIN cat_proveedores pr ON pr.id = c.id_proveedor
  WHERE {$w}
  GROUP BY pr.id, pr.razon_social
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
            $r['proveedor'] ?: 'SIN PROVEEDOR',
            number_format((int)$r['compras'], 0, '.', ','),
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
