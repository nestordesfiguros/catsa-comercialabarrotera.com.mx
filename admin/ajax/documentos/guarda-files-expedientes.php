<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();
include '../../lib/clsFechas.php';
$clsFecha=new Fechas();
include '../../lib/clsCadenas.php';
$clsCadenas=new cadenas();

$datetime=$clsFecha->datetime();

//$folio=$_GET['folio'];
$ruta='../../expedientes/';
$nombre=$ruta.basename($_FILES['file']['name']);
$nombrefile=basename($_FILES['file']['name']);

/*
$micarpeta = $ruta;
if (!file_exists($micarpeta)) {
    mkdir($micarpeta, 0777, true);
}
*/

//$uuid=uniqid(16);
//$archivo=basename($_FILES['file']['name']);
/* Extrae la extención del archivo */
//$info = new SplFileInfo($archivo);  
//$ext=($info->getExtension());
/* ****  */


move_uploaded_file($_FILES['file']['tmp_name'], $nombre);
echo '{"archivo":"'.$nombrefile.'"}';
?>