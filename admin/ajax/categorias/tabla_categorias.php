<?php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();
$data_array = [];
$con = "SELECT c.id_categoria, 
       c.nombre_categoria, 
       COUNT(s.id) AS total_subcategorias
FROM cat_categorias c
LEFT JOIN cat_subcategorias s ON c.id_categoria = s.id_categoria
GROUP BY c.id_categoria, c.nombre_categoria";
$rs=$clsConsulta->consultaGeneral($con);
if($clsConsulta->numrows>0){
    foreach($rs as $v=>$val){
        $id_Categoria=$val['id_categoria'];
        $total=$val['total_subcategorias'];
        $editarValores=$id_Categoria.",'".$val['nombre_categoria']."'";
        //$editarValores='';
        $categoria='<div>'.$val['nombre_categoria'].'</div>';                      
        $editar='<div class="text-center" ><i class="fas fa-edit fa-lg text-info" style="cursor:pointer;" onclick="editar('.$editarValores.');"></i></div>';
        $subcategoria='<div class="text-center " ><a href="subcategorias/'.$id_Categoria.'" class="text-info"><i class="fas fa-list fa-lg"></i> </a> <small>('.$total.')</small></div>';       
        $borrar='<div class="text-center"><i class="fas fa-trash-alt fa-lg text-danger" style="cursor:pointer;" onclick="borrar('.$id_Categoria.');"></i></div>';

        $data_array[] = array(
            $categoria,    
            $editar,
          //  $subcategoria,
            $borrar            
        );
    }
}

$new_array = ["data" => $data_array];
echo json_encode($new_array);
?>