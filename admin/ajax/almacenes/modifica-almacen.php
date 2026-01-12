<?php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

$id=$_POST["id"];
$almacen=$_POST['almacen'];
$calle=$_POST['calle'];
$num_ext=$_POST['num_ext'];
$num_int=(isset($_POST['num_int']))?$_POST['num_int']:'';
$cp=$_POST['cp'];
$estado_id=$_POST['estado_id'];
$municipio_id=$_POST['municipio_id'];
$id_usuario=$_POST['id_usuario'];
$mapa=$_POST['mapa'];

$con="UPDATE cat_almacenes SET almacen='".$almacen."', calle='".$calle."', num_ext='".$num_ext."', num_int='".$num_int."', cp=".$cp.", estado_id=".$estado_id.", municipio_id=".$municipio_id.",  mapa='".$mapa."', id_usuario=".$id_usuario." WHERE id=".$id;
echo $con;
$clsConsulta->aplicaQuery($con);

?>