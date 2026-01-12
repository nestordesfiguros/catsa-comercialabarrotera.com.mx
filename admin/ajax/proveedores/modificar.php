<?php
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();
include '../../lib/clsCadenas.php';
$clsCadenas = new Cadenas();

date_default_timezone_set('America/Mexico_City');
$fechaActual = date('Y-m-d h:m:s');

$id = $_POST['id'];
$razon_social = $_POST['razonSocial'];
$nombre_Comercial = $_POST['nombreComercial'];
$tel = $_POST['telefono'];
$correo = $_POST['correo'];
$contacto = $_POST['contacto'];
$tel_contacto = $_POST['telContacto'];
$calle = $_POST['calle'];
$num_ext = $_POST['numExt'];
$num_int = $_POST['numInt'];
$colonia = $_POST['colonia'];
$cp = $_POST['cp'];
$id_estado = $_POST['id_estado'];
$id_municipio = $_POST['id_municipio'];
$mapa = $_POST['mapa'];
//$estatus = $_POST['estatus'];

$con = "UPDATE cat_proveedores SET razon_social='".$razon_social."', nombre_Comercial='".$nombre_Comercial."', tel='".$tel."', correo='".$correo."', contacto='".$contacto."', tel_contacto='".$tel_contacto."', calle='".$calle."', num_ext='".$num_ext."', num_int='".$num_int."', colonia='".$colonia."', cp=".$cp.", id_estado=".$id_estado.", id_municipio=".$id_municipio.", mapa='".$mapa."'  WHERE id= ".$id;
echo $con;
$clsConsulta->guardarGeneral($con);
