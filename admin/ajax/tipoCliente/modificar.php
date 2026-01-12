<?php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

//var_dump($_POST);
$id=$_POST['id'];
$tipo=strtoupper($_POST['tipo']);
$descuento=$_POST['descuento'];
$con="UPDATE cat_tipo_cliente SET tipo='".$tipo."', descuento=".$descuento." WHERE id=".$id;
$clsConsulta->aplicaQuery($con);
?>