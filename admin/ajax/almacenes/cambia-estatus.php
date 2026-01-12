<?php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

///var_dump($_POST);
$id=$_POST['id'];
$estatus=$_POST['estatus'];
$con="UPDATE cat_almacenes SET estatus=$estatus WHERE id=".$id;
$clsConsulta->aplicaQuery($con);
?>