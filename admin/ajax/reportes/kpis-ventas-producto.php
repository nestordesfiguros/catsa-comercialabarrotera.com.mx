<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

function s($v)
{
    global $clsConsulta;
    return $clsConsulta->sanitizar($v);
}
function qAll($sql)
{
    global $clsConsulta;
    $rs = $clsConsulta->consultaGeneral($sql);
    return is_array($rs) ? $rs : [];
}
function firstRow($rows)
{
    if (!is_array($rows) || empty($rows)) return null;
    $first = reset($rows);
    return is_array($first) ? $first : null;
}
function qScalar($sql, $field, $default = 0)
{
    $row = firstRow(qAll($sql));
    return (is_array($row) && array_key_exists($field, $row)) ? $row[$field] : $default;
}

// ====== Mapeo ==========================
const T_MOV        = 'mov_remisiones';
const COL_M_ID_REM = 'id_remision';
const COL_M_ID_PROD = 'id_producto';
const COL_M_CANT   = 'cantidad';
const COL_M_PRECIO = 'precio_unitario';

const T_PROD       = 'cat_productos';
const COL_P_ID     = 'id_producto';
const COL_P_NOMBRE = 'nombre';
// ======================================

$fi_raw = trim($_POST['fecha_ini'] ?? '');
if ($fi_raw === '') $fi_raw = date('Y-m-01');
$ff_raw = trim($_POST['fecha_fin'] ?? '');
if ($ff_raw === '') $ff_raw = date('Y-m-d');

$vendedor = (int)($_POST['vendedor'] ?? 0);
$cliente  = (int)($_POST['cliente']  ?? 0);
$producto = (int)($_POST['producto'] ?? 0);
$tipo_raw = trim($_POST['forma']     ?? '');
$estatus  = trim($_POST['estatus']   ?? '');

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
if ($producto > 0) {
    $where[] = "m." . COL_M_ID_PROD . " = $producto";
}
if ($tipo_raw !== '') {
    $where[] = "r.tipo_venta = " . s($tipo_raw);
}
if ($estatus  !== '') {
    $where[] = "r.estatus    = " . s($estatus);
}
$whereSql = count($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

$sqlKPIs = "
  SELECT
    IFNULL(SUM(m." . COL_M_CANT . "),0)                       AS unidades,
    IFNULL(SUM(m." . COL_M_CANT . " * m." . COL_M_PRECIO . "),0)  AS total_ventas
  FROM " . T_MOV . " m
  JOIN cab_remisiones r ON r.id = m." . COL_M_ID_REM . "
  $whereSql
";
$row = firstRow(qAll($sqlKPIs));
$unidades = $row ? (float)$row['unidades'] : 0.0;
$total    = $row ? (float)$row['total_ventas'] : 0.0;

// Top producto por ventas
$sqlTop = "
  SELECT p." . COL_P_NOMBRE . " AS nombre, SUM(m." . COL_M_CANT . " * m." . COL_M_PRECIO . ") AS ventas
  FROM " . T_MOV . " m
  JOIN cab_remisiones r ON r.id = m." . COL_M_ID_REM . "
  JOIN " . T_PROD . " p      ON p." . COL_P_ID . " = m." . COL_M_ID_PROD . "
  $whereSql
  GROUP BY p." . COL_P_ID . ", p." . COL_P_NOMBRE . "
  ORDER BY ventas DESC
  LIMIT 1
";
$topRow = firstRow(qAll($sqlTop));
$topProducto = $topRow ? $topRow['nombre'] : null;

echo json_encode([
    'unidades'      => $unidades,
    'total_ventas'  => $total,
    'top_producto'  => $topProducto
]);
