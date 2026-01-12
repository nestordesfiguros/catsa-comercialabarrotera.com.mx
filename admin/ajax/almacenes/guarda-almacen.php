<?php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();
$id_usuario=$_SESSION["id_user"];
$almacen=$_POST['almacen'];
$calle=$_POST['calle'];
$num_ext=$_POST['num_ext'];
$num_int=(isset($_POST['num_int']))?$_POST['num_int']:'';
$cp=$_POST['cp'];
$estado_id=$_POST['estado_id'];
$municipio_id=$_POST['municipio_id'];
$id_usuario=$_POST['id_usuario'];
$mapa=$_POST['mapa'];

$con="INSERT INTO cat_almacenes (almacen, calle, num_ext, num_int, cp, municipio_id, estado_id, fecha_alta, id_usuario, mapa) VALUES ('".$almacen."', '".$calle."', '".$num_ext."', '".$num_int."', '".$cp."', ".$municipio_id.", ".$estado_id.", NOW(), ".$id_usuario.", '".$mapa."' )";
echo $con;
$clsConsulta->aplicaQuery($con);

?>