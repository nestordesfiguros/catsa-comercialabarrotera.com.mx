<?php
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$data = [];
$nombre = trim($_POST['nombre']);
$nombre = strtoupper($nombre);


if ($nombre != '') {
    $con = "SELECT * FROM cat_productos WHERE nombre='" . $nombre . "'";
    // echo $con;
    $rs = $clsConsulta->consultaGeneral($con);
    if ($clsConsulta->numrows > 0) {        
        $data = [
            "existe" => true,
            "nombre" => $nombre
        ];
    } else {
        $data = [
            "existe" => false,
            "nombre" => ""
        ];
    }
}

header('Content-Type: application/json'); // Asegúrate de establecer el tipo de contenido
echo json_encode($data);

?>