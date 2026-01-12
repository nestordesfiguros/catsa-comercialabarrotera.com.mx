<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();
include '../../lib/clsFechas.php';
$clsFecha=new Fechas();
// var_dump($_POST);

$nombre=$_POST['nombre'];
$apellido1=$_POST['apellido1'];
$apellido2=$_POST['apellido2'];
//$usuarioID=$_POST['id'];   
$correo=$_POST['usr'];
$id_puesto=$_POST['id_puesto'];

 
$con="INSERT INTO usuarios ( usr, nombre, apellido1, apellido2, id_puesto, estatus, fecha_alta) VALUES ( '".$correo."', '".$nombre."', '".$apellido1."', '".$apellido2."', ".$id_puesto.", 1,  NOW())  ";	 
 // echo $con;
$clsConsulta->guardarGeneral($con); 



?>