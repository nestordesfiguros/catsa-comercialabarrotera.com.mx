<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

//var_dump($_POST);
$id_expediente = $_POST['id_expediente'];  // id del archivo
$archivos = explode("|",$_POST['archivos']);
$folio   = $_POST['folio'];
$id_usuario = $_POST['id_usuario']; 
$comentario = strtoupper($_POST['comentario']); 
$accion     ="AGREGAR ARCHIVOS";
$documento  = $_POST['archivos'];

$clsConsulta->bitacora($folio, $id_usuario, $accion, $documento, 'Expedientes'); // Folio, id usuario, accion, comentario, seccion


if($archivos!=''){
    foreach($archivos as $arch){
        if($arch!=''){
            $con="INSERT INTO expedientes_archivos (id_expediente, archivo, comentario, fecha_alta) VALUES (".$id_expediente.", '".$arch."', '".$comentario."', NOW() )";                       
            $clsConsulta->aplicaQuery($con);   
        }            
    }
}



?>