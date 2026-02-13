<?php
// ajax/compras/tabla-proveedores.php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

header('Content-Type: application/json; charset=utf-8');

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;

$draw   = isset($_POST['draw']) ? (int)$_POST['draw'] : 0;
$start  = isset($_POST['start']) ? (int)$_POST['start'] : 0;
$length = isset($_POST['length']) ? (int)$_POST['length'] : 10;

$search = '';
if (isset($_POST['search']) && isset($_POST['search']['value'])) {
    $search = trim((string)$_POST['search']['value']);
}

$orderCol = 0;
$orderDir = 'ASC';
if (isset($_POST['order'][0]['column'])) $orderCol = (int)$_POST['order'][0]['column'];
if (isset($_POST['order'][0]['dir'])) {
    $tmp = strtoupper((string)$_POST['order'][0]['dir']);
    $orderDir = ($tmp === 'DESC') ? 'DESC' : 'ASC';
}

// Columnas permitidas para ORDER
$cols = [
    0 => 'nombre_Comercial',
    1 => 'razon_social'
];
$orderBy = isset($cols[$orderCol]) ? $cols[$orderCol] : 'nombre_Comercial';

// Empresa inválida
if ($idEmpresa <= 0) {
    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => []
    ]);
    exit;
}

// Total
$sqlTotal = "
    SELECT COUNT(*) AS total
    FROM cat_proveedores
    WHERE estatus=1
      AND id_empresa={$idEmpresa}
";
$rsTotal = $clsConsulta->consultaGeneral($sqlTotal);
$total = 0;
if ($clsConsulta->numrows > 0 && isset($rsTotal[1]['total'])) {
    $total = (int)$rsTotal[1]['total'];
}

// Filtro
$whereSearch = "";
if ($search !== '') {
    $s = $clsConsulta->real_escape_string($search);
    $whereSearch = " AND (
        nombre_Comercial LIKE '%{$s}%'
        OR razon_social LIKE '%{$s}%'
    )";
}

// Filtrado
$sqlFiltered = "
    SELECT COUNT(*) AS total
    FROM cat_proveedores
    WHERE estatus=1
      AND id_empresa={$idEmpresa}
      {$whereSearch}
";
$rsFiltered = $clsConsulta->consultaGeneral($sqlFiltered);
$filtered = $total;
if ($clsConsulta->numrows > 0 && isset($rsFiltered[1]['total'])) {
    $filtered = (int)$rsFiltered[1]['total'];
}

// Datos
$sql = "
    SELECT id, nombre_Comercial, razon_social
    FROM cat_proveedores
    WHERE estatus=1
      AND id_empresa={$idEmpresa}
      {$whereSearch}
    ORDER BY {$orderBy} {$orderDir}
    LIMIT {$start}, {$length}
";
$rs = $clsConsulta->consultaGeneral($sql);

$data = [];
if ($clsConsulta->numrows > 0) {
    foreach ($rs as $i => $val) {
        $nombre = isset($val['nombre_Comercial']) ? trim((string)$val['nombre_Comercial']) : '';
        $razon  = isset($val['razon_social']) ? trim((string)$val['razon_social']) : '';

        $data[] = [
            'id_proveedor' => (int)$val['id'],
            'nombre' => $nombre,
            'razon_social' => $razon
        ];
    }
}

echo json_encode([
    'draw' => $draw,
    'recordsTotal' => $total,
    'recordsFiltered' => $filtered,
    'data' => $data
]);
