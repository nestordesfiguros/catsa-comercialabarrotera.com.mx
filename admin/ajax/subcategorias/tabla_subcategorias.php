<?php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();
$idCategoria=$_GET['idCategoria'];
$data_array = [];
$con = "SELECT * FROM cat_subcategorias WHERE id_categoria=".$idCategoria;
$rs=$clsConsulta->consultaGeneral($con);
if($clsConsulta->numrows>0){
    foreach($rs as $v=>$val){
        $ID_Categoria=$val['id'];
        $editarValores=$ID_Categoria.",'".$val['nombre_categoria']."'";
        //$editarValores='';
        $categoria='<div>'.$val['nombre_categoria'].'</div>';                      
        $editar='<div class="text-center" ><i class="fas fa-edit fa-lg text-info" style="cursor:pointer;" onclick="editar('.$editarValores.');"></i></div>';
        
        $borrar='<div class="text-center"><i class="fas fa-trash-alt fa-lg text-danger" style="cursor:pointer;" onclick="borrar('.$ID_Categoria.');"></i></div>';

        $data_array[] = array(
            $categoria,    
            $editar,            
            $borrar            
        );
    }
}

$new_array = ["data" => $data_array];
echo json_encode($new_array);
?>