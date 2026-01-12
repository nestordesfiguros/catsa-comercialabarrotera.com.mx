<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

function s($v)
{
    global $clsConsulta;
    return $clsConsulta->sanitizar($v);
}

// Helpers seguros (funcionan con arrays indexados desde 0 o 1)
function qAll($sql)
{
    global $clsConsulta;
    $rs = $clsConsulta->consultaGeneral($sql);
    return (is_array($rs)) ? $rs : [];
}
function firstRow($rows)
{
    if (!is_array($rows) || empty($rows)) return null;
    $first = reset($rows); // primer elemento independientemente del índice
    return (is_array($first)) ? $first : null;
}
function qScalar($sql, $field, $default = 0)
{
    $rows = qAll($sql);
    $row  = firstRow($rows);
    return (is_array($row) && array_key_exists($field, $row)) ? $row[$field] : $default;
}

$draw   = isset($_POST['draw'])   ? (int)$_POST['draw']   : 1;
$start  = isset($_POST['start'])  ? (int)$_POST['start']  : 0;
$length = isset($_POST['length']) ? (int)$_POST['length'] : 25;

// Valores crudos
$fi_raw = trim($_POST['fecha_ini'] ?? '');
$ff_raw = trim($_POST['fecha_fin'] ?? '');
if ($fi_raw === '') $fi_raw = date('Y-m-01');
if ($ff_raw === '') $ff_raw = date('Y-m-d');

$vendedor     = (int)($_POST['vendedor'] ?? 0);
$cliente      = (int)($_POST['cliente']  ?? 0);
// En UI llamamos "forma", en BD es tipo_venta
$tipo_raw     = trim($_POST['forma']     ?? ''); // '' | 'contado' | 'credito'
$estatus_raw  = trim($_POST['estatus']   ?? '');
$search_raw   = trim($_POST['search']['value'] ?? '');

// Fechas sanitizadas (con comillas)
$fi = s($fi_raw . ' 00:00:00');
$ff = s($ff_raw . ' 23:59:59');

// Orden
$colMap = [
    0 => 'r.fecha',
    1 => 'r.id',
    2 => 'c.razon_social',
    3 => 'v.nombre',
    4 => 'r.total',
    5 => 'r.tipo_venta',
    6 => 'r.estatus'
];
$orderColIdx = isset($_POST['order'][0]['column']) ? (int)$_POST['order'][0]['column'] : 0;
$orderDir    = (isset($_POST['order'][0]['dir']) && strtolower($_POST['order'][0]['dir']) === 'asc') ? 'ASC' : 'DESC';
$orderCol    = $colMap[$orderColIdx] ?? 'r.fecha';

// WHERE base
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
$whereBase = count($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

// Búsqueda global
$whereSearch = $where;
if ($search_raw !== '') {
    $like = s('%' . $search_raw . '%');
    $whereSearch[] = "("
        . "CAST(r.id AS CHAR) LIKE $like OR "
        . "c.razon_social LIKE $like OR "
        . "v.nombre LIKE $like OR "
        . "r.tipo_venta LIKE $like OR "
        . "r.estatus LIKE $like"
        . ")";
}
$whereFinal = count($whereSearch) ? ('WHERE ' . implode(' AND ', $whereSearch)) : '';

// Conteos (sin acceder por [0])
$sqlTotal = "
  SELECT COUNT(*) AS total
  FROM cab_remisiones r
  LEFT JOIN cat_clientes c   ON c.id = r.id_cliente
  LEFT JOIN cat_vendedores v ON v.id = r.id_vendedor
  $whereBase
";
$total = (int) qScalar($sqlTotal, 'total', 0);

$sqlFiltered = "
  SELECT COUNT(*) AS total
  FROM cab_remisiones r
  LEFT JOIN cat_clientes c   ON c.id = r.id_cliente
  LEFT JOIN cat_vendedores v ON v.id = r.id_vendedor
  $whereFinal
";
$filtered = (int) qScalar($sqlFiltered, 'total', 0);

// Datos
$sqlData = "
  SELECT
    DATE_FORMAT(r.fecha, '%Y-%m-%d %H:%i:%s') AS fecha,
    r.id AS folio,
    IFNULL(c.razon_social, '') AS cliente,
    IFNULL(v.nombre, '')       AS vendedor,
    r.total,
    IFNULL(r.tipo_venta, '')   AS tipo_venta,
    IFNULL(r.estatus, '')      AS estatus
  FROM cab_remisiones r
  LEFT JOIN cat_clientes c   ON c.id = r.id_cliente
  LEFT JOIN cat_vendedores v ON v.id = r.id_vendedor
  $whereFinal
  ORDER BY $orderCol $orderDir
  LIMIT $start, $length
";
$rows = qAll($sqlData);

// Fallback si por cualquier motivo COUNT falló pero sí hay filas
if ($filtered === 0 && !empty($rows)) {
    $filtered = $total > 0 ? $total : count($rows);
}
if ($total === 0 && $filtered > 0) {
    $total = $filtered;
}

$data = [];
foreach ($rows as $r) {
    $data[] = [
        'fecha'      => $r['fecha'],
        'folio'      => $r['folio'],
        'cliente'    => $r['cliente'],
        'vendedor'   => $r['vendedor'],
        'total'      => (float)$r['total'],
        'tipo_venta' => $r['tipo_venta'],
        'estatus'    => $r['estatus']
    ];
}

echo json_encode([
    'draw'            => $draw,
    'recordsTotal'    => $total,
    'recordsFiltered' => $filtered,
    'data'            => $data
]);
