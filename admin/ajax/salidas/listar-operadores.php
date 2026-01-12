<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$sql = "SELECT id, nombre, licencia FROM cat_operadores WHERE activo = 1 ORDER BY nombre ASC";
$operadores = $clsConsulta->consultaGeneral($sql);

$data = [];

if ($clsConsulta->numrows > 0) {
    foreach ($operadores as $i => $o) {
        if ($i === 0) continue;
        $data[] = [
            'id' => $o['id'],
            'text' => $o['nombre'] . ' - Lic. ' . $o['licencia']
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($data);
