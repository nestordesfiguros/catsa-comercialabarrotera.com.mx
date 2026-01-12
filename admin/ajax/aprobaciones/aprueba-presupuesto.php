<?php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();
include '../../lib/clsFechas.php';
$clsFecha=new Fechas();
require '../../vendor/autoload.php';
//var_dump($_POST);

$folio=trim($_POST['folio']);
$comentario=trim($_POST['comentario']);
$accion=$_POST['accion'];
$clienteid=trim($_POST['clienteid']);
$id_usuario=$_SESSION['id_user'];

$con="SELECT * FROM presupuestos WHERE folio='".$folio."'";
$rs=$clsConsulta->consultaGeneral($con);
foreach($rs as $v=>$val){
    $descripcion=$val['descripcion'];
    $id_presupuesto=$val['id'];
    $fecha_recepcion=$clsFecha->fecha_letra($val['fecha_recepcion']);
}

$con="UPDATE presupuestos SET estado='".$accion."', comentario='".$comentario."' WHERE folio='".$folio."'";
$clsConsulta->aplicaQuery($con);

/* Modifica el estatus de los archivos */

$con="SELECT * FROM presupuestos_archivos WHERE id_presupuesto=".$id_presupuesto;
$rs=$clsConsulta->consultaGeneral($con);
foreach($rs as $v=>$val){
    $idp=$val['id'];
    $confile="UPDATE presupuestos_archivos SET estatus = 1, id_usuario = ".$id_usuario.", fecha_modifica = NOW() WHERE id=".$idp; // Estatus 1=Aprobado 2=Rechazado
    echo $confile;
    $clsConsulta->aplicaQuery($confile);
}


/* Bitacora */
//$comentario = (isset($_POST['comentario']))?$_POST['comentario']:'';      
$comentario="Presupuesto Aprobado el día ".$clsFecha->fecha_Hoy();
$seccion='presupuestos';
$clsConsulta->bitacora($folio, $id_usuario, $accion, $comentario, $seccion);
/* Termina Bitacora */

/* Enviar correo si está seleccionado */
$con="SELECT * FROM cliente_correos WHERE cliente_id='".$clienteid."' AND tipo_proceso=2 AND tipo_correo='Destinatario'";      
$nombreDestinatario='';
$rs=$clsConsulta->consultaGeneral($con);
if($clsConsulta->numrows>0){   
    foreach($rs as $v=>$val){              
    //    $tipo_correo = $val['tipo_correo'];         
        $nombreDestinatario .= ' '.$val['titulo'].' '.$val['nombre'].',';
    //    $tituloDestinatario = $val['titulo'];
        $correoDestinatario = $val['correo'].',';
    }    
    $nombreDestinatario=trim($nombreDestinatario, ',');
    $correoDestinatario=trim($correoDestinatario, ',');


    $con="SELECT * FROM cliente_correos WHERE cliente_id='".$clienteid."' AND tipo_proceso=2 AND tipo_correo='CC'";      
    //echo $con;
    $rs=$clsConsulta->consultaGeneral($con);
    if($clsConsulta->numrows>0){   
        foreach($rs as $v=>$val){              
        //    $tipo_correo = $val['tipo_correo'];         
            $nombrecc .= ' '.$val['titulo'].' '.$val['nombre'].',';
        //    $tituloDestinatario = $val['titulo'];
            $correocc = $val['correo'].',';
        }    
        $nombrecc=trim($nombrecc, ',');
        $correocc=trim($correocc, ',');
    }else{
        $correocc='';
        $nombrecc='';
    }

    $con="SELECT * FROM cliente_correos WHERE cliente_id='".$clienteid."' AND tipo_proceso=2 AND tipo_correo='CCO'";      
    //echo $con;
    $rs=$clsConsulta->consultaGeneral($con);
    if($clsConsulta->numrows>0){   
        foreach($rs as $v=>$val){              
        //    $tipo_correo = $val['tipo_correo'];         
            $nombrecco .= ' '.$val['titulo'].' '.$val['nombre'].',';
        //    $tituloDestinatario = $val['titulo'];
            $correocco = $val['correo'].',';
        }    
        $nombrecco=trim($nombrecco, ',');
        $correocco=trim($correocco, ',');
    }else{
        $correocco='';
        $nombrecco='';
    }
    include '../../plantillas/aprueba-presupuesto.php';
}

?>