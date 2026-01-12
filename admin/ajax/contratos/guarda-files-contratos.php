<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();
include '../../lib/clsFechas.php';
$clsFecha=new Fechas();
include '../../lib/clsCadenas.php';
$clsCadenas=new cadenas();

$datetime=$clsFecha->datetime();

/*
$id=$_GET['id'];
$folio=$_GET['folio'];
$razon_social=strtolower($_GET['razonsocial']);
$razon_social=$clsCadenas->normaliza($razon_social);
*/
//$ruta='../../archivos-siroc/'.$razon_social;
$ruta='../../documentos/contratos/';
$nombre=$ruta.basename($_FILES['file']['name']);
$nombrefile=basename($_FILES['file']['name']);
//$rutabd='archivos-siroc/'.$razon_social;
/*
$micarpeta = $ruta;
if (!file_exists($micarpeta)) {
    mkdir($micarpeta, 0777, true);
}
*/
/*
$uuid=uniqid(16);
$archivo=basename($_FILES['file']['name']);
/* Extrae la extención del archivo */
/*
$info = new SplFileInfo($archivo);  
$ext=($info->getExtension());
*/
/* ****  */
/*
$nombre=$ruta.'/'.$datetime.'-'.$uuid.'.'.$ext;
$nombrefile=$datetime.'-'.$uuid.'.'.$ext;
*/
move_uploaded_file($_FILES['file']['tmp_name'], $nombre);
/*
$nombrebd=$rutabd.'/'.$id.'-'.$folio.'-'.$uuid.'.'.$ext;
$con="UPDATE clientes_srirocs SET archivo='".$nombrebd."'";
$clsConsulta->aplicaQuery($con);
*/
echo '{"archivo":"'.$nombrefile.'"}';
?>