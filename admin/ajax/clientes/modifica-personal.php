<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

$fechaActual = date('Y-m-d h:m:s');
$id = $_POST['id'];
$id_puesto = $_POST['puesto'];
$nombre = $_POST['nombre'];
$apellido1 = $_POST['apellido1'];
$apellido2 = (isset($_POST['apellido2']))?$_POST['apellido2']:'';
$curp = $_POST['curp'];
$correo = $_POST['correo'];
$calle = $_POST['calle'];
$num_ext = $_POST['num_ext'];
$num_int = (isset($_POST['num_int']))?$_POST['num_int']:'';
$colonia = $_POST['colonia'];
$cp = $_POST['cp'];
$id_municipio = $_POST['id_municipio'];
$id_estado = $_POST['id_estado'];
$localidad = (isset($_POST['localidad']))?$_POST['localidad']:'';
$tel1 = $_POST['tel1'];
$tel2 = (isset($_POST['tel2']))?$_POST['tel2']:'';

$con="UPDATE cat_personal SET nombre='$nombre', apellido1='$apellido1', apellido2='$apellido2', curp='$curp', correo='$correo', calle='$calle', num_ext='$num_ext', num_int='$num_int', colonia='$colonia', cp='$cp', id_municipio=$id_municipio, id_estado=$id_estado, localidad='$localidad', tel1='$tel1', tel2='$tel2', id_puesto=$id_puesto WHERE id=".$id;

$clsConsulta->aplicaQuery($con);
?>