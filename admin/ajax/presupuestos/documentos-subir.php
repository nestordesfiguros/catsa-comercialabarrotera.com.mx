<?php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();
include '../../lib/clsFechas.php';
$clsFecha=new Fechas();
include '../../lib/clsCadenas.php';
$clsCadenas=new cadenas();

$ruta='../../documentos/';
$nombre=$ruta.basename($_FILES['file']['name']);
$nombrefile=basename($_FILES['file']['name']);
move_uploaded_file($_FILES['file']['tmp_name'], $nombre);

echo '{"archivo":"'.$nombrefile.'"}';
?>
