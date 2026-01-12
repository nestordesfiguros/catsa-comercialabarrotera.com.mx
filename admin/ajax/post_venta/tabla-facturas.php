<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();
/*
$orden=$_POST['orden'];

if($orden==1){
 ordenxRazonSocial   
}
*/
$ordena='';
    if(isset($_COOKIE['ordenTablaFacturas'])) {    
     $varcookie=$_COOKIE['ordenTablaFacturas'];        
        if($varcookie==0){
            $ordena='WHERE fact_estatus=0';
        }
        if($varcookie==1){
            $ordena='WHERE fact_estatus=1';
        }
        if($varcookie==2){
            $ordena='WHERE fact_estatus=2';
        }
    
   }
   $ordenaRS='';
   if(isset($_COOKIE['ordenxRazonSocial'])) {    
    $varcookie=$_COOKIE['ordenxRazonSocial'];
       
            if($varcookie=='TODOS'){
                $ordenaRS='';
            }else{
                
                $conc="SELECT id FROM cat_clientes WHERE razon_social='".$varcookie."'";                
                $rsc=$clsConsulta->consultaGeneral($conc);
                if($clsConsulta->numrows>0){
                    $id_razon=$rsc[1]['id'];
                }
                
                $ordenaRS=' AND post_venta.fact_cliente ='.$id_razon;                
                
            }        
   
    }

$data_array = [];
$cont="SELECT post_venta.*
        , cat_clientes.razon_social
        FROM
        post_venta
        INNER JOIN cat_clientes 
            ON (post_venta.fact_cliente = cat_clientes.id) ".$ordena.$ordenaRS; // WHERE fact_estatus=1";
$rs=$clsConsulta->consultaGeneral($cont);
if($clsConsulta->numrows>0){
    foreach($rs as $v=>$val){
        $fact_id = $val['fact_id'];
        $fact_folio = $val['fact_folio'];
        $razon_social = $val['razon_social'];
        $fact_monto = '<div class="text-end">$'.number_format($val['fact_monto'],'2','.',',').'</div>';
        $fact_retencion = '<div class="text-end">$'.number_format($val['fact_retencion'],'2','.',',').'</div>';
        $fact_amortizacion_anticipo = '<div class="text-end">$'.number_format($val['fact_amortizacion_anticipo'],'2','.',',').'</div>';        
        $fact_fecha = '<div class="text-center">'.$val['fact_fecha'].'</div>';        
        $fact_serie = $val['fact_serie'];
        $fact_no = '<div class="text-end">'.$val['fact_no'].'</div>';
        $fact_tentativa_cobro = '<div class="text-end">'.$val['fact_tentativa_cobro'].'</div>';
        $fact_semana_envio = '<div class="text-end">'.$val['fact_semana_envio'].'</div>';  
        $fact_semana_cobro = '<div class="text-end">'.$val['fact_semana_cobro'].'</div>';
        $fact_fecha_accion = '<div class="text-center">'.$val['fact_fecha_accion'].'</div>';              
        $fact_estatus=$val['fact_estatus'];        
        switch ($fact_estatus){
            case 0:
                $estatus='<div class="text-info text-center" style="cursor:pointer;" class="text-danger"  onclick="ModalcambiaEstatus('.$fact_id.')"> PENDIENTE </div>';
            break;
            case 1:
                $estatus='<div class="text-success text-center"> COBRADA </div>';
            break;
            case 3:
                $estatus='<div class="text-danger text-center" > CANCELADA </div>';
            break;
            case 2:
                $estatus='<div class="text-warning text-center" > PRE CANCELADA </div>';
            break;

        }
        $fact_estatus_proceso=$val['fact_estatus_proceso'];  // fact_no,fact_serie,fact_folio
        $valoresProceso=$val['fact_no'].",'".$fact_serie."','".$fact_folio."'";
        switch ($fact_estatus_proceso){
            case 0:
                $estatus_proceso='<div data-title="Click para ver el correo" class="text-info text-center" style="cursor:pointer;" onclick="funcModalEstatusProceso('.$valoresProceso.')"> <i class="fa-solid fa-envelopes-bulk fa-lg"></i> PENDIENTE </div>';
            break;
            case 1:
                $estatus_proceso='<div class="text-success text-center" style="cursor:pointer;"  onclick="funcModalEstatusProceso('.$valoresProceso.')"> <i class="fa-solid fa-envelopes-bulk fa-lg"></i> ENVIADO </div>';
            break;
            case 2:
                $estatus_proceso='<div class="text-danger text-center" style="cursor:pointer;" onclick="funcModalEstatusProceso('.$valoresProceso.')"> <i class="fa-solid fa-envelopes-bulk fa-lg"></i> NO ENVIADo </div>';
            break;

        }
  
        $data_array[] = array (
            $fact_folio,
            $razon_social,
            $fact_monto,
            $fact_retencion,
            $fact_amortizacion_anticipo,
            $fact_fecha,
            $fact_serie,
            $fact_no,
            $fact_semana_envio,
            $fact_semana_cobro, 
            $fact_fecha_accion,          
            $estatus,
            $estatus_proceso                   
        );
    } 

}
   
    $new_array=["data"=>$data_array];
    echo json_encode($new_array);
    ?>