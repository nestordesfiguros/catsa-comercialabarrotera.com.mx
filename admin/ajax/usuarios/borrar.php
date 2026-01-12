<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

$id=$_POST['id'];
$con="DELETE FROM usuarios WHERE id=".$id;
$rs=$clsConsulta->aplicaQuery($con);

?>
