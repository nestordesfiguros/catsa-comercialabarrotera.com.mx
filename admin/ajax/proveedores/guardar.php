<?php
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();
include '../../lib/clsCadenas.php';
$clsCadenas = new Cadenas();

date_default_timezone_set('America/Mexico_City');
$fechaActual = date('Y-m-d h:m:s');

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

$con = "INSERT INTO cat_proveedores ( razon_social, nombre_Comercial, tel, correo, contacto, tel_contacto, calle, num_ext, num_int, colonia, cp, id_estado, id_municipio, mapa) VALUES ('".$razon_social."', '".$nombre_Comercial."', '".$tel."', '".$correo."', '".$contacto."', '".$tel_contacto."', '".$calle."', '".$num_ext."', '".$num_int."', '".$colonia."', ".$cp.", ".$id_estado.", ".$id_municipio.", '".$mapa."') ";
echo $con;
$clsConsulta->guardarGeneral($con);
