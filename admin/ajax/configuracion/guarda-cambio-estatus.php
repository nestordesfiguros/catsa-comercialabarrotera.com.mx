<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

$id=$_POST['id'];
$estatus=$_POST['estatus'];

$con="UPDATE cat_empresas SET estatus=".$estatus." WHERE id=".$id;
$clsConsulta->aplicaQuery($con);

?>