<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

$fechaActual = date('Y-m-d');
$id = $_POST['id'];
$comision =  $_POST['comision'];
$nombre = strtoupper($_POST['nombre']);
$apellido1 = strtoupper($_POST['apellido1']);
$apellido2 = (isset($_POST['apellido2']))?strtoupper($_POST['apellido2']):'';
$curp = ''; // $_POST['curp'];
$contacto = $_POST['contacto'];
$calle = $_POST['calle'];
$num_ext = $_POST['num_ext'];
$num_int = (isset($_POST['num_int']))?$_POST['num_int']:'';
$colonia = $_POST['colonia'];
$cp = $_POST['cp'];
$id_municipio = $_POST['id_municipio'];
$id_estado = $_POST['id_estado'];
$localidad = ''; (isset($_POST['localidad']))?$_POST['localidad']:'';
$tel = $_POST['tel'];
$fecha_alta = $_POST['fecha_alta'];
$telcontacto = (isset($_POST['telcontacto']))?$_POST['telcontacto']:'';

$con="UPDATE cat_vendedores SET nombre='$nombre', apellido1='$apellido1', apellido2='$apellido2', curp='$curp', calle='$calle', num_ext='$num_ext', num_int='$num_int', colonia='$colonia', cp='$cp', id_municipio=$id_municipio, id_estado=$id_estado, localidad='$localidad', tel='$tel', telcontacto='$telcontacto', fecha_alta='".$fecha_alta."', comision=".$comision." WHERE id=".$id;

$clsConsulta->aplicaQuery($con);
?>