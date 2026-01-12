<?php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

///var_dump($_POST);
$id=$_POST['id'];
$clsConsulta->borrarDetalle('cat_categorias',$id,'id_categoria');
//$clsConsulta->guardar('cat_categorias',$_POST);
?>