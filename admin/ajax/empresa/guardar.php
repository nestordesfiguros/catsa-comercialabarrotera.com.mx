<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();


date_default_timezone_set('America/Mexico_City');
$fechaActual = date('Y-m-d h:m:s');

$razon_social = $_POST['razon_social'];
$rfc = $_POST['rfc'];
$correo = $_POST['correo'];
$calle = $_POST['calle'];
$num_ext = $_POST['num_ext'];
$num_int = (isset($_POST['num_int']))?$_POST['num_int']:'';
$colonia = $_POST['colonia'];
$cp = $_POST['cp'];
$id_municipio = $_POST['id_municipio'];
$id_estado = $_POST['id_estado'];
$localidad = (isset($_POST['localidad']))?$_POST['localidad']:'';

$con="INSERT INTO cat_empresas (razon_social,  rfc, correo, calle, num_ext, num_int, colonia, cp, id_municipio, id_estado, localidad, fecha_alta ) VALUES ( '".$razon_social."',  '".$rfc."', '".$correo."', '".$calle."', '".$num_ext."', '".$num_int."', '".$colonia."', ".$cp.", ".$id_municipio.", ".$id_estado.", '".$localidad."', '".$fechaActual."' )";
$clsConsulta->guardarGeneral($con);

?>