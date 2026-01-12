<?php
include '../lib/clsConsultas.php';
$clsConsulta=new Consultas();
include '../lib/clsFechas.php';
$clsFecha=new Fechas();

$nombre=$_POST['nombre'];
$apellido1=$_POST['apellido1'];
$apellido2=$_POST['apellido2'];
//$usuarioID=$_POST['id'];   
$correo=strtolower($_POST['usr']);
$rol=$_POST['rol'];
$con="INSERT INTO usuarios (nombre, apellido1, apellido2, usr, rol) VALUES ('".$nombre."', '".$apellido1."', '".$apellido2."', '".$correo."', ".$rol.")";	
$clsConsulta->guardarGeneral($con); 

?>