<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

function s($v)
{
    global $clsConsulta;
    return $clsConsulta->sanitizar($v);
}

// ====== Mapeo de tablas/columnas ===============================
const T_MOV            = 'mov_remisiones';   // tu detalle de remisiones
const COL_M_ID_REM     = 'id_remision';
const COL_M_ID_PROD    = 'id_producto';
const COL_M_CANT       = 'cantidad';
const COL_M_PRECIO     = 'precio_unitario';

const T_PROD           = 'cat_productos';
const COL_P_ID         = 'id_producto';
const COL_P_NOMBRE     = 'nombre';
// ===============================================================

// Helpers (arrays 1-based/0-based)
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

$draw   = (int)($_POST['draw'] ?? 1);
$start  = (int)($_POST['start'] ?? 0);
$length = (int)($_POST['length'] ?? 25);

// Filtros
$fi_raw = trim($_POST['fecha_ini'] ?? '');
if ($fi_raw === '') $fi_raw = date('Y-m-01');
$ff_raw = trim($_POST['fecha_fin'] ?? '');
if ($ff_raw === '') $ff_raw = date('Y-m-d');

$vendedor   = (int)($_POST['vendedor'] ?? 0);
$cliente    = (int)($_POST['cliente']  ?? 0);
$producto   = (int)($_POST['producto'] ?? 0);
$tipo_raw   = trim($_POST['forma']     ?? '');  // contado|credito
$estatus_raw = trim($_POST['estatus']   ?? '');
$search_raw = trim($_POST['search']['value'] ?? '');

// Fechas sanitizadas
$fi = s($fi_raw . ' 00:00:00');
$ff = s($ff_raw . ' 23:59:59');

// Orden
$colMap = [
    0 => 'p.' . COL_P_NOMBRE,
    1 => 'unidades',
    2 => 'ventas',
    3 => 'precio_promedio',
    4 => 'participacion'
];
$orderColIdx = isset($_POST['order'][0]['column']) ? (int)$_POST['order'][0]['column'] : 2;
$orderDir    = (isset($_POST['order'][0]['dir']) && strtolower($_POST['order'][0]['dir']) === 'asc') ? 'ASC' : 'DESC';
$orderCol    = $colMap[$orderColIdx] ?? 'ventas';

// WHERE base
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
if ($tipo_raw   !== '') {
    $where[] = "r.tipo_venta = " . s($tipo_raw);
}
if ($estatus_raw !== '') {
    $where[] = "r.estatus    = " . s($estatus_raw);
}
$whereBase = count($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

// Search
$whereSearch = $where;
if ($search_raw !== '') {
    $like = s('%' . $search_raw . '%');
    $whereSearch[] = "(p." . COL_P_NOMBRE . " LIKE $like OR CAST(p." . COL_P_ID . " AS CHAR) LIKE $like)";
}
$whereFinal = count($whereSearch) ? ('WHERE ' . implode(' AND ', $whereSearch)) : '';

// Total ventas global para % participación
$sqlTotalVentas = "
  SELECT IFNULL(SUM(m." . COL_M_CANT . "*m." . COL_M_PRECIO . "),0) AS total_vtas
  FROM " . T_MOV . " m
  JOIN cab_remisiones r ON r.id = m." . COL_M_ID_REM . "
  JOIN " . T_PROD . " p      ON p." . COL_P_ID . " = m." . COL_M_ID_PROD . "
  $whereBase
";
$totalVentasGlobal = (float) qScalar($sqlTotalVentas, 'total_vtas', 0);

// Conteos (productos distintos)
$sqlCountBase = "
  SELECT COUNT(DISTINCT p." . COL_P_ID . ") AS n
  FROM " . T_MOV . " m
  JOIN cab_remisiones r ON r.id = m." . COL_M_ID_REM . "
  JOIN " . T_PROD . " p      ON p." . COL_P_ID . " = m." . COL_M_ID_PROD . "
  $whereBase
";
$recordsTotal = (int) qScalar($sqlCountBase, 'n', 0);

$sqlCountFiltered = "
  SELECT COUNT(DISTINCT p." . COL_P_ID . ") AS n
  FROM " . T_MOV . " m
  JOIN cab_remisiones r ON r.id = m." . COL_M_ID_REM . "
  JOIN " . T_PROD . " p      ON p." . COL_P_ID . " = m." . COL_M_ID_PROD . "
  $whereFinal
";
$recordsFiltered = (int) qScalar($sqlCountFiltered, 'n', 0);

// Datos agrupados por producto
$sqlData = "
  SELECT
    p." . COL_P_NOMBRE . "                                         AS producto,
    SUM(m." . COL_M_CANT . ")                                     AS unidades,
    SUM(m." . COL_M_CANT . " * m." . COL_M_PRECIO . ")                AS ventas,
    AVG(m." . COL_M_PRECIO . ")                                   AS precio_promedio
  FROM " . T_MOV . " m
  JOIN cab_remisiones r ON r.id = m." . COL_M_ID_REM . "
  JOIN " . T_PROD . " p      ON p." . COL_P_ID . " = m." . COL_M_ID_PROD . "
  $whereFinal
  GROUP BY p." . COL_P_ID . ", p." . COL_P_NOMBRE . "
  ORDER BY $orderCol $orderDir
  LIMIT $start, $length
";
$rows = qAll($sqlData);

$data = [];
foreach ($rows as $r) {
    $ventas = (float)$r['ventas'];
    $part   = ($totalVentasGlobal > 0) ? round(($ventas / $totalVentasGlobal) * 100, 4) : null;
    $data[] = [
        'producto'        => $r['producto'],
        'unidades'        => (float)$r['unidades'],
        'ventas'          => $ventas,
        'precio_promedio' => (float)$r['precio_promedio'],
        'participacion'   => $part
    ];
}

echo json_encode([
    'draw'            => $draw,
    'recordsTotal'    => $recordsTotal,
    'recordsFiltered' => $recordsFiltered,
    'data'            => $data
]);
