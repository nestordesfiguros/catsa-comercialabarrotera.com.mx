<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

function s($v)
{
    global $clsConsulta;
    return $clsConsulta->sanitizar($v);
}

// Helpers compatibles con arrays 1-based/0-based
function qAll($sql)
{
    global $clsConsulta;
    $rs = $clsConsulta->consultaGeneral($sql);
    return (is_array($rs)) ? $rs : [];
}
function firstRow($rows)
{
    if (!is_array($rows) || empty($rows)) return null;
    $first = reset($rows);
    return (is_array($first)) ? $first : null;
}

$fi_raw = trim($_POST['fecha_ini'] ?? '');
$ff_raw = trim($_POST['fecha_fin'] ?? '');
if ($fi_raw === '') $fi_raw = date('Y-m-01');
if ($ff_raw === '') $ff_raw = date('Y-m-d');

$vendedor    = (int)($_POST['vendedor'] ?? 0);
$cliente     = (int)($_POST['cliente']  ?? 0);
$tipo_raw    = trim($_POST['forma']     ?? ''); // UI "forma" -> BD tipo_venta
$estatus_raw = trim($_POST['estatus']   ?? '');

$fi = s($fi_raw . ' 00:00:00');
$ff = s($ff_raw . ' 23:59:59');

$where = [];
$where[] = "r.fecha BETWEEN $fi AND $ff";
if ($vendedor > 0) {
    $where[] = "r.id_vendedor = $vendedor";
}
if ($cliente  > 0) {
    $where[] = "r.id_cliente  = $cliente";
}
if ($tipo_raw   !== '') {
    $where[] = "r.tipo_venta = " . s($tipo_raw);
}
if ($estatus_raw !== '') {
    $where[] = "r.estatus    = " . s($estatus_raw);
}
$whereSql = count($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

$sql = "
  SELECT
    COUNT(*) AS tickets,
    IFNULL(SUM(r.total),0) AS total_ventas
  FROM cab_remisiones r
  $whereSql
";
$rows = qAll($sql);
$row  = firstRow($rows);

$tickets = 0;
$total   = 0.0;
if ($row && isset($row['tickets'])) {
    $tickets = (int)$row['tickets'];
    $total   = (float)$row['total_ventas'];
}
$prom = ($tickets > 0) ? ($total / $tickets) : 0.0;

echo json_encode([
    'tickets'         => $tickets,
    'total_ventas'    => $total,
    'ticket_promedio' => $prom
]);
