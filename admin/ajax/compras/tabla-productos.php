<?php
// ajax/compras/tabla-productos.php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

header('Content-Type: application/json; charset=utf-8');

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;

$draw   = isset($_POST['draw']) ? (int)$_POST['draw'] : 0;
$start  = isset($_POST['start']) ? (int)$_POST['start'] : 0;
$length = isset($_POST['length']) ? (int)$_POST['length'] : 10;

$searchValue = '';
if (isset($_POST['search']) && isset($_POST['search']['value'])) {
    $searchValue = trim($_POST['search']['value']);
}

$orderCol = 0;
$orderDir = 'ASC';
if (isset($_POST['order'][0]['column'])) {
    $orderCol = (int)$_POST['order'][0]['column'];
}
if (isset($_POST['order'][0]['dir']) && in_array(strtoupper($_POST['order'][0]['dir']), ['ASC', 'DESC'], true)) {
    $orderDir = strtoupper($_POST['order'][0]['dir']);
}

$columns = [
    0 => 'clave',
    1 => 'nombre'
];
$orderBy = isset($columns[$orderCol]) ? $columns[$orderCol] : 'nombre';

if ($idEmpresa <= 0) {
    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => []
    ]);
    exit;
}

$where = " WHERE estatus=1 AND id_empresa={$idEmpresa} ";

$excluded = [];
if (!empty($_POST['excluded_ids'])) {
    $parts = explode(',', $_POST['excluded_ids']);
    foreach ($parts as $p) {
        $id = (int)trim($p);
        if ($id > 0) $excluded[] = $id;
    }
}
if (count($excluded) > 0) {
    $where .= " AND id_producto NOT IN (" . implode(',', $excluded) . ") ";
}

if ($searchValue !== '') {
    $s = $clsConsulta->real_escape_string($searchValue);
    $where .= " AND (clave LIKE '%{$s}%' OR nombre LIKE '%{$s}%') ";
}

// Total base (empresa)
$sqlTotal = "SELECT COUNT(*) AS total FROM cat_productos WHERE estatus=1 AND id_empresa={$idEmpresa}";
$rsTotal = $clsConsulta->consultaGeneral($sqlTotal);
$recordsTotal = 0;
if ($clsConsulta->numrows > 0) {
    $recordsTotal = (int)$rsTotal[1]['total'];
}

// Total filtrado (empresa + search + excluded)
$sqlFiltered = "SELECT COUNT(*) AS total FROM cat_productos {$where}";
$rsFiltered = $clsConsulta->consultaGeneral($sqlFiltered);
$recordsFiltered = 0;
if ($clsConsulta->numrows > 0) {
    $recordsFiltered = (int)$rsFiltered[1]['total'];
}

// Data
$sqlData = "
    SELECT id_producto, clave, nombre
    FROM cat_productos
    {$where}
    ORDER BY {$orderBy} {$orderDir}
    LIMIT {$start}, {$length}
";
$rsData = $clsConsulta->consultaGeneral($sqlData);

$data = [];
if ($clsConsulta->numrows > 0) {
    foreach ($rsData as $v => $val) {
        $data[] = [
            'id_producto' => (int)$val['id_producto'],
            'clave' => $val['clave'],
            'nombre' => $val['nombre']
        ];
    }
}

echo json_encode([
    'draw' => $draw,
    'recordsTotal' => $recordsTotal,
    'recordsFiltered' => $recordsFiltered,
    'data' => $data
]);
