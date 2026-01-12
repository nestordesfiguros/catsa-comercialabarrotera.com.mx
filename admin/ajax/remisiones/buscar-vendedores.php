<?php
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$termino = isset($_POST['search']) ? trim($_POST['search']) : '';
$termino = addslashes($termino);

$sql = "
    SELECT id, CONCAT_WS(' ', nombre, apellido1, apellido2) AS nombre
    FROM cat_vendedores
    WHERE estatus = 1
";

if ($termino !== '') {
    $sql .= " AND (nombre LIKE '%$termino%' OR apellido1 LIKE '%$termino%' OR apellido2 LIKE '%$termino%')";
}

$sql .= " ORDER BY nombre LIMIT 20";

$rs = $clsConsulta->consultaGeneral($sql);

$resultado = [];
if ($clsConsulta->numrows > 0) {
    foreach ($rs as $row) {
        $resultado[] = [
            'id' => $row['id'],
            'text' => $row['nombre']
        ];
    }
}

echo json_encode($resultado);
