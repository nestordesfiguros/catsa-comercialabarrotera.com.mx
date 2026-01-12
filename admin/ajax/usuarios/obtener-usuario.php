<?php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

$nombre=$_SESSION['nombre'];
$id=$_SESSION['id_user'];

echo '{"nombre":"'.$nombre.'","id_usuario":"'.$id.'"}';

?>