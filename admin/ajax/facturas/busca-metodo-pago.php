<?php
session_start();
include '../../lib/clsConsultas.php';

$clsConsulta = new Consultas();
$data = [];  // Inicializar array de respuesta

$metodo_pago = $_POST['metodo_pago'] ?? '';

if (!empty($metodo_pago)) {
    $con = "SELECT id FROM cat_metodos_pago WHERE descripcion='" . addslashes($metodo_pago) . "'";
    $rs = $clsConsulta->consultaGeneral($con);

    if ($clsConsulta->numrows > 0) {  
       // $fila = $rs->fetch_assoc();  // Obtener el ID del cliente
        $data = [
            "existe" => true
          //  "id" => $fila['id']  // Enviar el ID del cliente si existe
        ];
    } else {
        $data = [
            "existe" => false
        ];
    }
} else {
    $data = [
        "error" => "Falta el nombre del cliente"
    ];
}

header('Content-Type: application/json');
echo json_encode($data);
?>
