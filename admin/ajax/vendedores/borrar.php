<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

$fechaActual = date('Y-m-d h:m:s');
$id = $_POST['id'];

$con="UPDATE cat_vendedores SET estatus=0 WHERE id=".$id;
$clsConsulta->aplicaQuery($con);
?>