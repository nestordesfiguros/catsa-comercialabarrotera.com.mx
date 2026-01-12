<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$termino = isset($_POST['search']) ? trim($_POST['search']) : '';

$sql = "SELECT id, almacen FROM cat_almacenes WHERE estatus = 1";

if ($termino !== '') {
    $termino = $clsConsulta->escape($termino);
    $sql .= " AND almacen LIKE '%$termino%'";
}

$sql .= " ORDER BY almacen ASC LIMIT 20";

$resultado = $clsConsulta->consultaGeneral($sql);
$data = [];

if ($clsConsulta->numrows > 0) {
    foreach ($resultado as $i => $r) {
        if ($i === 0) continue;
        $data[] = [
            'id' => $r['id'],
            'text' => $r['almacen']
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($data);
