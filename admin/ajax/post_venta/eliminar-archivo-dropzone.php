<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

$filename=$_POST['filename'];
$ruta='../../expedientes/post_venta/';

$borrar=$ruta.$filename;
if(file_exists($borrar)){
    echo 'SI existe: '.$borrar;
    unlink($borrar);
}else{
    echo 'NO existe'.$borrar;
}
?>