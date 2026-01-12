<?php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

//var_dump($_POST);
$id=$_POST['id'];
$nombre=$_POST['nombre_categoria'];
$con="UPDATE cat_categorias SET nombre_categoria='".$nombre."' WHERE id_categoria=".$id;
echo $con;
$clsConsulta->aplicaQuery($con);
?>