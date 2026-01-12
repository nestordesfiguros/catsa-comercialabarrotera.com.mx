<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

//var_dump($_POST);
$id = $_POST['id'];
$activo =($_POST['activo']==0)?1:0;


$con="UPDATE procesos_dominios SET activo='".$activo."' WHERE id=".$id;
//echo $con;
$clsConsulta->aplicaQuery($con);


?>