<?php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

//var_dump($_POST);
$id=$_POST['id'];
$nombre=$_POST['nombre_categoria'];
$con="UPDATE cat_subcategorias SET nombre_categoria='".$nombre."' WHERE id=".$id;
echo $con;
$clsConsulta->aplicaQuery($con);
?>