<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

//var_dump($_POST);
$id = trim($_POST['id_archivos'],",");  // id del archivo
$estatus = ($_POST['estatus']=='rechazar')?2:1;
$id_usuario = $_POST['id_usuario']; 
$folio = $_POST['folio']; 
$comentario= $_POST['comentario']; 
$nombre_documento=$_POST['nombre_documento'];
$documento = ($_POST['estatus']=='rechazar')? 'Archivo del documento '.$nombre_documento.' fueron rechazados '.$comentario: 'Archivo del documento '.$nombre_documento.' furon aporbados'; 
$accion = ($_POST['estatus']=='rechazar')?'RECHAZADO':'APROBADO';

$clsConsulta->bitacora($folio, $id_usuario, $accion, $documento, 'Expedientes'); // Folio, id usuario, accion, comentario, seccion

$idArchivos=explode(",",$id);
foreach($idArchivos as $idA){
    $con="UPDATE expedientes_archivos SET estatus=".$estatus.", comentario='".$comentario."' WHERE id=".$idA;    
    $clsConsulta->aplicaQuery($con);
}

$con="SELECT * FROM expedientes_archivos WHERE id=".$idArchivos[0];
$rs=$clsConsulta->consultaGeneral($con);
foreach($rs as $v=>$val){
    $id_expediente=$val['id_expediente'];
}

$con="UPDATE expedientes SET estatus=".$estatus." WHERE id=".$id_expediente;    
$clsConsulta->aplicaQuery($con); 


?>