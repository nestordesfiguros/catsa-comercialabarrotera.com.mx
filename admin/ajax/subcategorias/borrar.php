<?php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

///var_dump($_POST);
$id=$_POST['id'];
$clsConsulta->borrarDetalle('cat_subcategorias',$id,'id');
//$clsConsulta->guardar('cat_categorias',$_POST);
?>