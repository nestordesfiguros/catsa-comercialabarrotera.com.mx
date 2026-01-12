<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

$razon_social=strtoupper($_POST['razon_social']);
$calle=strtoupper($_POST['calle']);
$num_ext=strtoupper($_POST['num_ext']);
$num_int=(isset($_POST['num_int']))?strtoupper($_POST['num_int']):'';
$rfc=strtoupper($_POST['rfc']);
$cp=$_POST['cp'];
$estado_id=$_POST['estado_id'];
$municipio_id=$_POST['municipio_id'];
$id_usuario=$_POST['id_usuario'];

$con="INSERT INTO cat_empresas (razon_social, calle, num_ext, num_int, rfc, cp, municipio_id, estado_id, fecha_alta, id_usuario) VALUES ('".$razon_social."', '".$calle."', '".$num_ext."', '".$num_int."', '".$rfc."', '".$cp."', ".$municipio_id.", ".$estado_id.", NOW(), ".$id_usuario." )";
$clsConsulta->aplicaQuery($con);

?>