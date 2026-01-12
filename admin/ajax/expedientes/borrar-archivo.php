<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

$ruta='../../expedientes/';
$id_archivo = $_POST['id_archivo'];  // id del archivo

$con="SELECT * FROM expedientes_archivos WHERE id=".$id_archivo;
$rs=$clsConsulta->consultaGeneral($con);
$nombreArchivo=$rs[1]['archivo'];

$existe=$ruta.$nombreArchivo;
if(file_exists($existe)){    
    unset($existe);
}

$con="DELETE FROM expedientes_archivos WHERE id=".$id_archivo;
$clsConsulta->aplicaQuery($con);

?>