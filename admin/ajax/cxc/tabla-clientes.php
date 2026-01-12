<?php
require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$start  = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$draw   = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
$search = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';

// WHERE dinámico
$where = "1";
if ($search != '') {
    $like = '%' . addslashes($search) . '%';
    $where .= " AND (
        razon_social LIKE '$like'
        OR nombre_comercial LIKE '$like'
    )";
}

// Total de registros sin filtro
$sql_total = "SELECT COUNT(*) AS total FROM cat_clientes WHERE 1";
$res_total = $clsConsulta->consultaGeneral($sql_total);
$recordsTotal = $res_total && isset($res_total[0]['total']) ? intval($res_total[0]['total']) : 0;

// Total filtrado
$sql_filtrado = "SELECT COUNT(*) AS total FROM cat_clientes WHERE $where";
$res_filtrado = $clsConsulta->consultaGeneral($sql_filtrado);
$recordsFiltered = $res_filtrado && isset($res_filtrado[0]['total']) ? intval($res_filtrado[0]['total']) : 0;

// Consulta paginada
$sql = "
    SELECT id, razon_social, nombre_comercial
    FROM cat_clientes
    WHERE $where
    ORDER BY razon_social ASC
    LIMIT $start, $length
";
$rows = $clsConsulta->consultaGeneral($sql);

$data = [];
if ($rows && is_array($rows)) {
    foreach ($rows as $row) {
        $data[] = [
            "id" => $row["id"],
            "razon_social" => trim(strip_tags($row["razon_social"])),
            "nombre_comercial" => trim(strip_tags($row["nombre_comercial"]))
        ];
    }
}

echo json_encode([
    "draw" => $draw,
    "recordsTotal" => $recordsTotal,
    "recordsFiltered" => $recordsFiltered,
    "data" => $data
]);
