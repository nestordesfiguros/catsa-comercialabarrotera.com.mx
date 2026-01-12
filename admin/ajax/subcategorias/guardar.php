<?php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

//var_dump($_POST);
$Nombre_Categoria=$_POST['Nombre_Categoria'];
$clsConsulta->guardar('cat_subcategorias',$_POST);
?>