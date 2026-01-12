<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

$ruta='../../img/';
$nombre=basename($_FILES['file']['name']);
$archivo=$ruta.$nombre;

move_uploaded_file($_FILES['file']['tmp_name'], $archivo);

echo '{"archivo":"'.$nombre.'"}';
?>