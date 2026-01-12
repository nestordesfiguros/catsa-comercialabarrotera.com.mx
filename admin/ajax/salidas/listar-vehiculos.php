<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$sql = "SELECT id, placas, marca, modelo FROM cat_vehiculos WHERE activo = 1 ORDER BY placas ASC";
$vehiculos = $clsConsulta->consultaGeneral($sql);

$data = [];

if ($clsConsulta->numrows > 0) {
    foreach ($vehiculos as $i => $v) {
        if ($i === 0) continue;
        $data[] = [
            'id' => $v['id'],
            'text' => $v['placas'] . ' - ' . $v['marca'] . ' ' . $v['modelo']
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($data);
