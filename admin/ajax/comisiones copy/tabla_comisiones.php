<?php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();
$data_array = [];
$con = "SELECT * FROM cat_comisiones";
$rs=$clsConsulta->consultaGeneral($con);
if($clsConsulta->numrows>0){
    foreach($rs as $v=>$val){
        $id=$val['id'];
        $nombre_comision=$val['nombre_comision'];
        $porcentaje=$val['porcentaje'];
        $editarValores=$id.",'".$nombre_comision."',".$porcentaje;
        
        //$editarValores='';
        $nombre_comision='<div>'.$nombre_comision.'</div>';  
        $porciento='<div class="text-end">'.$porcentaje.'%</div>';                      
        $editar='<div class="text-center" ><i class="fas fa-edit fa-lg text-info" style="cursor:pointer;" onclick="editar('.$editarValores.');"></i></div>';           
        $borrar='<div class="text-center"><i class="fas fa-trash-alt fa-lg text-danger" style="cursor:pointer;" onclick="borrar('.$editarValores.');"></i></div>';

        $data_array[] = array(
            $nombre_comision, 
            $porciento,   
            $editar,          
            $borrar            
        );
    }
}

$new_array = ["data" => $data_array];
echo json_encode($new_array);
?>