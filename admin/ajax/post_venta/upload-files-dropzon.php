<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();
include '../../lib/clsFechas.php';
$clsFecha=new Fechas();
$datetime=$clsFecha->datetime();
$ruta='../../expedientes/post_venta/';

$uuid=uniqid(16);
$archivo=basename($_FILES['file']['name']);

$nombre=$ruta.basename($_FILES['file']['name']);
$nombrefile=basename($_FILES['file']['name']);
move_uploaded_file($_FILES['file']['tmp_name'], $nombre);

echo '{"archivo":"'.$nombrefile.'"}';
?>