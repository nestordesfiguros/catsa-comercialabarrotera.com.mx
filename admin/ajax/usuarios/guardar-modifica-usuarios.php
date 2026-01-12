<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();
// var_dump($_POST);

$nombre=$_POST['nombre'];
$apellido1=$_POST['apellido1'];
$apellido2=$_POST['apellido2'];
$id=$_POST['id'];   
$usr=$_POST['usr'];
$id_puesto=$_POST['id_puesto'];
 
$con="UPDATE usuarios SET nombre='".$nombre."', apellido1='".$apellido1."', apellido2='".$apellido2."', usr='".$usr."', id_puesto='".$id_puesto."',  fecha_modifica=NOW() WHERE id=".$id;
 //echo $con;

$clsConsulta->guardarGeneral($con); 

?>