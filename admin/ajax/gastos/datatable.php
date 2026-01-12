<?php
// ajax/gastos/datatable.php
session_start();
require_once '../../lib/clsConsultas.php';

$consulta   = new Consultas();
$idEmpresa  = isset($_SESSION['id_empresa']) ? intval($_SESSION['id_empresa']) : 0;

$draw       = intval($_POST['draw']   ?? 1);
$start      = intval($_POST['start']  ?? 0);
$length     = intval($_POST['length'] ?? 10);
$searchText = trim($_POST['search']['value'] ?? '');

// Filtros
$fini   = $_POST['filtroFechaInicio'] ?? '';
$ffin   = $_POST['filtroFechaFin'] ?? '';
$fcat   = trim($_POST['filtroCategoria'] ?? '');
$fmet   = trim($_POST['filtroMetodo'] ?? '');
$fest   = trim($_POST['filtroEstatus'] ?? '');

$where = " WHERE g.id_empresa = {$idEmpresa} ";

if ($fini !== '' && $ffin !== '') {
    $where .= " AND g.fecha_gasto BETWEEN " . $consulta->sanitizar($fini) . " AND " . $consulta->sanitizar($ffin) . " ";
}
if ($fcat !== '') {
    $where .= " AND g.id_categoria = " . intval($fcat) . " ";
}
if ($fmet !== '') {
    $where .= " AND g.id_metodo   = " . intval($fmet) . " ";
}
if ($fest !== '') {
    $where .= " AND g.estatus     = " . $consulta->sanitizar($fest) . " ";
}

if ($searchText !== '') {
    $sv = $consulta->escape($searchText);
    $where .= " AND (g.descripcion LIKE '%{$sv}%' OR g.referencia LIKE '%{$sv}%' OR c.nombre LIKE '%{$sv}%' OR m.descripcion LIKE '%{$sv}%') ";
}

$columnsMap = [
    0 => 'g.fecha_gasto',
    1 => 'c.nombre',
    2 => 'g.descripcion',
    3 => 'm.descripcion',
    4 => 'g.referencia',
    5 => 'g.monto',
    6 => 'g.estatus'
];
$orderColIdx = intval($_POST['order'][0]['column'] ?? 0);
$orderDir    = (($_POST['order'][0]['dir'] ?? 'desc') === 'asc') ? 'ASC' : 'DESC';
$orderCol    = $columnsMap[$orderColIdx] ?? 'g.fecha_gasto';

// Conteo
$sqlCount = "
    SELECT COUNT(*) AS total
    FROM mov_gastos g
    INNER JOIN cat_gastos_categorias c ON c.id_categoria = g.id_categoria
    INNER JOIN cat_metodos_pago m      ON m.id = g.id_metodo
    {$where}
";
$rsCount = $consulta->aplicaQuery($sqlCount);
$totalFiltrado = 0;
if ($rsCount) {
    $row = mysqli_fetch_assoc($rsCount);
    $totalFiltrado = intval($row['total'] ?? 0);
}

// SUMA filtrada
$sqlSum = "
    SELECT COALESCE(SUM(g.monto),0) AS total_monto
    FROM mov_gastos g
    INNER JOIN cat_gastos_categorias c ON c.id_categoria = g.id_categoria
    INNER JOIN cat_metodos_pago m      ON m.id = g.id_metodo
    {$where}
";
$rsSum = $consulta->aplicaQuery($sqlSum);
$sumMonto = 0.0;
if ($rsSum) {
    $rowS = mysqli_fetch_assoc($rsSum);
    $sumMonto = floatval($rowS['total_monto'] ?? 0);
}

// Datos
$sqlData = "
    SELECT
        g.id_gasto, g.fecha_gasto, g.hora_gasto, g.descripcion, g.monto, g.referencia, g.estatus,
        c.nombre AS categoria, m.id AS id_metodo, m.descripcion AS metodo
    FROM mov_gastos g
    INNER JOIN cat_gastos_categorias c ON c.id_categoria = g.id_categoria
    INNER JOIN cat_metodos_pago m      ON m.id = g.id_metodo
    {$where}
    ORDER BY {$orderCol} {$orderDir}
    LIMIT {$start}, {$length}
";
$rsData = $consulta->aplicaQuery($sqlData);

$rows = [];
while ($fila = mysqli_fetch_assoc($rsData)) {
    $tituloAccion = ($fila['estatus'] === 'Activo') ? 'Cancelar' : 'Activar';
    $accionesHtml = '
        <div class="btn-group btn-group-sm" role="group">
            <button class="btn btn-outline-primary btn-editar-gasto" data-id="' . $fila['id_gasto'] . '" title="Editar">
                <i class="fa-solid fa-pen-to-square"></i>
            </button>
            <button class="btn btn-outline-secondary btn-estatus-gasto" data-id="' . $fila['id_gasto'] . '" data-estatus="' . htmlspecialchars($fila['estatus']) . '" title="' . htmlspecialchars($tituloAccion) . '">
                <i class="fa-solid fa-arrows-rotate"></i>
            </button>
        </div>';
    $rows[] = [
        'fecha'       => $fila['fecha_gasto'] . ' ' . $fila['hora_gasto'],
        'categoria'   => $fila['categoria'],
        'descripcion' => $fila['descripcion'],
        'metodo'      => $fila['metodo'],
        'referencia'  => $fila['referencia'],
        'monto'       => number_format((float)$fila['monto'], 2),
        'estatus'     => $fila['estatus'],
        'acciones'    => $accionesHtml
    ];
}

echo json_encode([
    'draw'            => $draw,
    'recordsTotal'    => $totalFiltrado,
    'recordsFiltered' => $totalFiltrado,
    'data'            => $rows,
    'sumMonto'        => $sumMonto
]);
