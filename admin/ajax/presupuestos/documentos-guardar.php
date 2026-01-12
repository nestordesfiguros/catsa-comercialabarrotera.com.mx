<?php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();
include '../../lib/clsFechas.php';
$clsFecha=new Fechas();
include '../../lib/clsCadenas.php';
$clsCadenas=new cadenas();

//var_dump($_POST);

$id=$_POST['id'];
$folio=$_POST['folio'];
$subcat=$_POST['subcat'];
//$razon_social=$_POST['razonsocial'];

$ruta='../../documentos/';

$archivos = explode("|",$_POST['archivos']);

if($archivos!=''){
    foreach($archivos as $file){    
        if($file!=''){
            $conf="INSERT INTO presupuestos_archivos (id_presupuesto, archivo) VALUES (".$id.", '".$file."')";
        //    echo $conf.'<br>';
            $clsConsulta->aplicaQuery($conf);      
        }        
    }            
}


$comentario ='';            
$id_usuario=$_SESSION['id_user'];
$accion='Agrega documentos';
$seccion='presupuestos';

$clsConsulta->bitacora($folio, $id_usuario, $accion, $comentario, $seccion);

$res= '{"subcat":"'.$subcat.'"}';
echo $res;
?>
