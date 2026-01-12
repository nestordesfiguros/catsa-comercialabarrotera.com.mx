<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();
include '../../lib/clsCadenas.php';
$clsCadenas=new Cadenas();


$fechaActual = date('Y-m-d h:m:s');

$id_cliente = $_POST['id_cliente'];
$nombre = $clsCadenas->quitarAcentos($_POST['nombre']);
$apellido1 = $clsCadenas->quitarAcentos($_POST['apellido1']);
$apellido2 = (isset($_POST['apellido2']))?$clsCadenas->quitarAcentos($_POST['apellido2']):'';
$calle = $clsCadenas->quitarAcentos($_POST['calle']);
$num_ext = $_POST['num_ext'];
$num_int = (isset($_POST['num_int']))?$_POST['num_int']:'';
$colonia = $clsCadenas->quitarAcentos($_POST['colonia']);
$cp = $_POST['cp'];
$id_municipio = $_POST['id_municipio'];
$id_estado = $_POST['id_estado'];
$localidad = (isset($_POST['localidad']))?$_POST['localidad']:'';
$tel = $_POST['tel'];
$ine = $_POST['ine'];

$con="INSERT INTO referencias (id_cliente, nombre, apellido1, apellido2, calle, num_ext, num_int, colonia, cp, id_municipio, id_estado, localidad, tel, fecha_alta) VALUES ( ".$id_cliente.", '".$nombre."', '".$apellido1."', '".$apellido2."',  '".$calle."', '".$num_ext."', '".$num_int."', '".$colonia."', ".$cp.", ".$id_municipio.", ".$id_estado.", '".$localidad."', '".$tel."', '".$fechaActual."' )";
$clsConsulta->guardarGeneral($con);
$id_cliente = $clsConsulta->ultimoid;

?>