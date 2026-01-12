<?php
header('Content-Type: application/json'); // Indica que devolverás un JSON

include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$idCliente = trim($_POST['idCliente']);
$idVendedor = trim($_POST['idVendedor']);

$con="SELECT * FROM vendedores_clientes WHERE id_vendedor=$idVendedor AND id_cliente=$idCliente";
//echo $con;
$rs = $clsConsulta->consultaGeneral($con);



if ($clsConsulta->numrows > 0) {
    // Ya existe, no insertes
    echo json_encode(["existe" => false]);
} else {
    $con = "INSERT INTO vendedores_clientes (id_vendedor, id_cliente) VALUES ($idVendedor, $idCliente)";
    $clsConsulta->aplicaQuery($con);

    // Devuelve éxito
    echo json_encode(["existe" => true]);
}

?>
