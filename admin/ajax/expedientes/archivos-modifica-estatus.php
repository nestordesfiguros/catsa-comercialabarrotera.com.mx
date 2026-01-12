<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

//var_dump($_POST);
$id = $_POST['id'];  // id del archivo
$estatus = ($_POST['estatus']=='rechazar')?2:1;
$id_usuario = $_POST['id_usuario']; 
$folio = $_POST['folio']; 
$comentario= $_POST['comentario']; 
$nombre_documento=$_POST['nombre_documento'];
$documento = ($_POST['estatus']=='rechazar')? 'Archivo del documento '.$nombre_documento.' fué rechazado '.$comentario: 'Archivo del documento '.$nombre_documento.' fué aporbado'; 
$accion = ($_POST['estatus']=='rechazar')?'RECHAZADO':'APROBADO';

$clsConsulta->bitacora($folio, $id_usuario, $accion, $documento, 'Expedientes'); // Folio, id usuario, accion, comentario, seccion

$con="UPDATE expedientes_archivos SET estatus=".$estatus.", comentario='".$comentario."' WHERE id=".$id;
$clsConsulta->aplicaQuery($con);

$con="SELECT * FROM expedientes_archivos WHERE id=".$id;
echo $con.'  /  ';
$rs=$clsConsulta->consultaGeneral($con);
foreach($rs as $v=>$val){
    $id_expediente=$val['id_expediente'];
}

$aprobados=0;
$rechazados=0;
$pendientes=0;
$con="SELECT * FROM expedientes_archivos WHERE id_expediente=".$id_expediente;
echo $con.'  /  ';
$rs=$clsConsulta->consultaGeneral($con);
$cantidad=$clsConsulta->numrows;
foreach($rs as $v=>$val){
    if($val['estatus']==0){
        $pendientes++;
    }
    if($val['estatus']==1){
        $aprobados++;
    }
    if($val['estatus']==2){
        $rechazados++;
    }
}
if($aprobados==$cantidad){
    $con="UPDATE expedientes SET estatus=1 WHERE id=".$id_expediente;
    echo $con.'  /  ';
    $clsConsulta->aplicaQuery($con);    
}

if($rechazados==$cantidad){
    $con="UPDATE expedientes SET estatus=2 WHERE id=".$id_expediente;
    echo $con.'  /  ';
    $clsConsulta->aplicaQuery($con);    
}


/*
if($estatus==2){
    echo '<i class="fa fa-ban fa-lg text-red"></i> <br><small>Rechazado</small>';
}else{
    echo '<i class="fa fa-check fa-lg text-success"></i> <br><small>Aprobado</small>';
}
*/
?>