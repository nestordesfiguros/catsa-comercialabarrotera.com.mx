<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

$fechaActual = date('Y-m-d h:m:s');
$id = $_POST['id'];

$con="DELETE FROM vendedores_clientes WHERE id=".$id;
$clsConsulta->aplicaQuery($con);

echo json_encode(["existe" => true]);
?>