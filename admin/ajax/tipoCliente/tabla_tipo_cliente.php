<?php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();
$data_array = [];
$con = "SELECT * FROM cat_tipo_cliente";
$rs=$clsConsulta->consultaGeneral($con);
if($clsConsulta->numrows>0){
    foreach($rs as $v=>$val){
        $id=$val['id'];
        $tipo=$val['tipo'];
        $descuento=$val['descuento'];
        $editarValores=$id.",'".$tipo."',".$descuento;
        
        //$editarValores='';
        $tipocliente='<div>'.$tipo.'</div>';  
        $desc='<div class="text-end">'.$descuento.'%</div>';                      
        $editar='<div class="text-center" ><i class="fas fa-edit fa-lg text-info" style="cursor:pointer;" onclick="editar('.$editarValores.');"></i></div>';           
        $borrar='<div class="text-center"><i class="fas fa-trash-alt fa-lg text-danger" style="cursor:pointer;" onclick="borrar('.$editarValores.');"></i></div>';

        $data_array[] = array(
            $tipocliente, 
            $desc,   
            $editar,          
            $borrar            
        );
    }
}

$new_array = ["data" => $data_array];
echo json_encode($new_array);
?>