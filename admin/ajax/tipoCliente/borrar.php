<?php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

$id=$_POST['id'];
$clsConsulta->borrarDetalle('cat_tipo_cliente',$id,'id');
?>