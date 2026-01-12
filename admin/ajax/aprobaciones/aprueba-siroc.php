<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();
include '../../lib/clsFechas.php';
$clsFecha=new Fechas();
//var_dump($_POST);

$folio=trim($_POST['folio']);
$comentario=trim($_POST['comentario']);
$accion=trim($_POST['accion']);

$con="UPDATE clientes_sirocs SET estado='".$accion."', comentario='".$comentario."' WHERE folio='".$folio."'";
echo $con;
$clsConsulta->aplicaQuery($con);

?>