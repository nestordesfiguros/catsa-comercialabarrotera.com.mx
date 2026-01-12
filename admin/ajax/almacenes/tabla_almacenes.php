<?php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();
$data_array = [];
$con = "SELECT * FROM cat_almacenes";
$rs=$clsConsulta->consultaGeneral($con);
if($clsConsulta->numrows>0){
    foreach($rs as $v=>$val){
        
        $id=$val['id'];
        $almacen=$val['almacen'];
        $num_ext=(isset($val['num_ext']))?$val['num_ext']:'';
        $domicilio='<div>'.$val['calle'].' '.$val['num_int'].$num_ext.'</div>';                    
        $editarValores=$id.",'".$almacen."'";                 
        $editar='<div class="text-center" ><a href="almacen-modificar/'.$id.'"><i class="fas fa-edit fa-lg text-info"></i></a></div>';
        $valEstatus=$val['estatus'];
        $editarValoresEstatus=$id.",'".$almacen."',$valEstatus";  
        if($valEstatus==1){
            $estatus='<div class="text-center"><i class="fas fa-check-circle fa-lg text-success" style="cursor:pointer;"  onclick="ModalcambiaEstatus('.$editarValoresEstatus.');"></i></div>';
        }else{
            $estatus='<div class="text-center"><i class="fas fa-ban fa-lg text-danger" style="cursor:pointer;" onclick="ModalcambiaEstatus('.$editarValoresEstatus.');"></i></div>';
        }
        if ($val['mapa'] != '') {
            $mapa = '<div class="text-center"><a href="' . $val['mapa'] . '" target="blank" class="text-info"><i class="fas fa-map-marked-alt fa-lg"></i></a></div>';
        } else {
            $mapa = '<div class="text-center"><i class="fas fa-map-marked-alt fa-lg"></i> </div>';
        }
                
        $data_array[] = array(
            $almacen,    
            $domicilio,
            $mapa,
            $editar,            
            $estatus            
        );
    }
}

$new_array = ["data" => $data_array];
echo json_encode($new_array);
?>