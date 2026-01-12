<?php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

///var_dump($_POST);
$id=$_POST['id'];
$con="UPDATE cat_productos SET estatus=0 WHERE id_producto=".$id;
$clsConsulta->aplicaQuery($con);

?>