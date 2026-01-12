<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();
$folio=trim($_POST['folio']);
//echo 'folio: '.$folio;
$descripcion='';
$existe=0; 
$siroc='no';        
$descripcion_siroc = '';
$cliente_id = '';
$presupuesto_id = '';
$fecha_firma = '';
$fecha_cierre_siroc = '';
$id_siroc ='';
$razon_social ='';

$pres='no';
$id_presupuesto = '';
$descripcion_pres='';
$monto = '';
$fecha_recepcion = ''; 

$base='';

$con="SELECT folio FROM contratos WHERE folio = '".$folio."'";
//echo $con;
$rs=$clsConsulta->consultaGeneral($con);
if($clsConsulta->numrows>0){
    echo '{"folio_contrato":"1"}';
}else{
    

    $con="SELECT
        clientes_sirocs.*
        , cat_clientes.razon_social        
    FROM
        clientes_sirocs
        INNER JOIN cat_clientes 
            ON (clientes_sirocs.cliente_id = cat_clientes.id) WHERE folio = '".$folio."'";
    //echo $con;
    $rs=$clsConsulta->consultaGeneral($con);
    if($clsConsulta->numrows>0){
        foreach($rs as $v=>$val){
            $id_siroc = $val['id'];
            $razon_social = $val['razon_social'];
            $folio = $val['folio'];
            $descripcion_siroc = trim($val['descripcion']);
            $cliente_id = $val['cliente_id'];
            $presupuesto_id = $val['presupuesto_id'];
            $fecha_firma = $val['fecha_firma'];
            $fecha_cierre_siroc = $val['fecha_cierre_siroc'];
            $siroc='si';
        }
    }
    $con="SELECT
        presupuestos.*
        , cat_clientes.razon_social
    FROM
        presupuestos
        INNER JOIN cat_clientes 
            ON (presupuestos.cliente_id = cat_clientes.id) 
    WHERE folio = '".$folio."'";
    //echo $con;
    $rs=$clsConsulta->consultaGeneral($con);
    if($clsConsulta->numrows>0){
        foreach($rs as $v=>$val){
            $id_presupuesto = $val['id'];
            $cliente_id = $val['cliente_id'];
            $razon_social = $val['razon_social'];
            $folio = $val['folio'];
            $descripcion_pres = trim($val['descripcion']);
            $monto = $val['monto'];
            $fecha_recepcion = $val['fecha_recepcion'];  
            $pres='si';
        }
    }

    if($pres=='no' && $siroc=='no'){    
        $existe=0;    
    }
    if($pres=='si' && $siroc=='si'){    
        $existe=1;    
    }
    if($pres=='no' && $siroc=='si'){    
        $existe=2;    
    }
    if($pres=='si' && $siroc=='no'){    
        $existe=3;    
    }

    $descrip=$descripcion_siroc.' '.$descripcion_pres;
    //$descripcion= preg_replace("/[\r\n|\n|\r]+/", " ", $descripcion);

    //$descripcion = json_decode($descrip);
    $descripcion =json_encode($descrip, JSON_PRETTY_PRINT);
    // ,{"descripcion":"'.$descripcion.'"},{"cliente_id":'.$cliente_id.'}
    echo '{"existe":"'.$existe.'","base":"'.$base.'","folio":"'.$folio.'","id_siroc":"'.$id_siroc.'","id_presupuesto":"'.$id_presupuesto.'","descripcion":'.$descripcion.' ,"razon_social":"'.$razon_social.'","cliente_id":"'.$cliente_id.'","fecha_firma":"'.$fecha_firma.'","fecha_cierre_siroc":"'.$fecha_cierre_siroc.'","fecha_recepcion":"'.$fecha_recepcion.'","monto":"'.$monto.'"}';
};
?>