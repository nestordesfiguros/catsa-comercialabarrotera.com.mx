<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();
include '../../lib/clsFechas.php';
$clsFecha=new Fechas();

//var_dump($_POST);
$accion = $_POST['accion']; 
//$accion='ALTA';
$id = (isset($_POST['id']))?$_POST['id']:0;
$id_cliente =(isset($_POST['clienteid']))?$_POST['clienteid']:0 ;
$nombre = strtoupper($_POST['nombre']);
$ciclo_id = $_POST['ciclo_id']; // apertura / cierre
$estatus = (isset($_POST['estatus']))?1:0;
$obligatorio = (isset($_POST['obligatorio']))?1:0;
$solicita_aprobacion = (isset($_POST['solicita_aprobacion']))?1:0;
$solicita_comentario = (isset($_POST['solicita_comentario']))?1:0;
$multiple = (isset($_POST['multiple']))?1:0;
$referencia = (isset($_POST['referencia']))?1:0;
$seguimiento = (isset($_POST['seguimiento']))?1:0;
$aplazamiento = (isset($_POST['aplazamiento']))?1:0;
$suma_monto = (isset($_POST['suma_monto']))?1:0;
$afecta=(isset($_POST['afecta']))?$_POST['afecta']:0;

switch ($accion){
    case 'ALTA':              
        $con="INSERT INTO definicion_documentos (id_cliente, nombre, ciclo_id, obligatorio, solicita_aprobacion, solicita_comentario, multiple, referencia, seguimiento, aplazamiento, created_at, afecta) VALUES (".$id_cliente.", '".$nombre."', ".$ciclo_id.", '".$obligatorio."', ".$solicita_aprobacion.", '".$solicita_comentario."', '".$multiple."', '".$referencia."', '".$seguimiento."', ".$aplazamiento.", NOW(), ".$afecta.")";
    break;
    case 'MODIFICAR':
        $id = $_POST['id'];
        $con="UPDATE definicion_documentos SET nombre='".$nombre."', obligatorio=".$obligatorio.", solicita_aprobacion=".$solicita_aprobacion.", solicita_comentario='".$solicita_comentario."', ciclo_id=".$ciclo_id.", multiple=".$multiple.", referencia=".$referencia.", seguimiento=".$seguimiento.", aplazamiento=".$aplazamiento.", suma_monto=".$suma_monto.", updated_at= NOW(), estatus=".$estatus.", afecta=".$afecta." WHERE id=".$id;
    break;    
    case 'BORRAR':
        $id = $_POST['id'];
        $con="UPDATE definicion_documentos SET estatus=0, deleted_at=NOW() WHERE id=".$id;
    break;
}
// echo $con;
$clsConsulta->guardarGeneral($con); 


 
?>