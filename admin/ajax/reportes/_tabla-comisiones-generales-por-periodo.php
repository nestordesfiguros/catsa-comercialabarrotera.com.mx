<?php
// ajax/reportes/tabla-comisiones-generales-por-periodo.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$start = (int)($_POST['start'] ?? 0);
$length = (int)($_POST['length'] ?? 25);
$draw = (int)($_POST['draw'] ?? 1);

$f_ini = trim($_POST['f_ini'] ?? '');
$f_fin = trim($_POST['f_fin'] ?? '');

$w = "IFNULL(r.estatus,'') <> 'cancelada' AND IFNULL(r.es_devolucion,0)=0 AND IFNULL(r.es_nota_credito,0)=0";
if ($f_ini !== '') $w .= " AND DATE(r.fecha) >= '" . $clsConsulta->escape($f_ini) . "'";
if ($f_fin !== '') $w .= " AND DATE(r.fecha) <= '" . $clsConsulta->escape($f_fin) . "'";

$sqlBase = "
  SELECT
    CONCAT_WS(' ', v.nombre, v.apellido1, v.apellido2) AS vendedor,
    SUM(m.cantidad*m.precio) AS venta_sin_iva,
    IFNULL(v.comision,0) AS pct
  FROM cab_remisiones r
  JOIN mov_remisiones m ON m.id_remision=r.id
  LEFT JOIN cat_vendedores v ON v.id=r.id_vendedor
  WHERE {$w}
  GROUP BY v.id, vendedor, pct
  ORDER BY vendedor ASC
";

$tot = $clsConsulta->consultaGeneral("SELECT COUNT(1) c FROM ({$sqlBase}) t");
$recordsTotal = (int)($tot[1]['c'] ?? 0);
$recordsFiltered = $recordsTotal;

$sql = $sqlBase . " LIMIT {$start}, {$length}";
$rs = $clsConsulta->consultaGeneral($sql);

$data = [];
if ($rs && $clsConsulta->numrows > 0) {
    foreach ($rs as $r) {
        $venta = (float)$r['venta_sin_iva'];
        $pct = (float)$r['pct'];
        $com = $venta * ($pct / 100);
        $data[] = [
            $r['vendedor'] ?: 'SIN VENDEDOR',
            '$' . number_format($venta, 2, '.', ','),
            number_format($pct, 2, '.', ',') . ' %',
            '$' . number_format($com, 2, '.', ',')
        ];
    }
}

echo json_encode([
    'draw' => $draw,
    'recordsTotal' => $recordsTotal,
    'recordsFiltered' => $recordsFiltered,
    'data' => $data
], JSON_UNESCAPED_UNICODE);
