<?php
// ajax/facturas/obtener-numero-factura.php
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$con = "SELECT fact_serie, fact_no FROM parametros WHERE id=1";
//echo $con . '';
$rs = $clsConsulta->consultaGeneral($con);
$resultado = [];

if ($clsConsulta->numrows > 0) {
    foreach ($rs as $v => $val) {
        $fact_serie = $val['fact_serie'];
        $fact_no = $val['fact_no'];

        // Crear un array de los datos y agregarlo al resultado
        $resultado[] = [
            'fact_serie' => $fact_serie,
            'fact_no' => $fact_no
        ];
    }
} else {
    $resultado[] = [
        'fact_serie' => 'A',
        'fact_no' => 1
    ];
}

// Convertir el array en formato JSON
echo json_encode($resultado);
