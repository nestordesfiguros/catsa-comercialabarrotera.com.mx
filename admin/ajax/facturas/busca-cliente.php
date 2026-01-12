<?php
// ajax/facturas/busca-cliente.php
session_start();
include '../../lib/clsConsultas.php';

$clsConsulta = new Consultas();
$data = [];  // Inicializar array de respuesta

$razon_social = $_POST['cliente'] ?? '';

if (!empty($razon_social)) {
    $con = "SELECT id FROM cat_clientes WHERE razon_social='" . addslashes($razon_social) . "'";
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
