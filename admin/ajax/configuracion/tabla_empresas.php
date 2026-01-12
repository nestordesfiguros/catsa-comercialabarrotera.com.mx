<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

$data_array = [];
$con="SELECT
cat_empresas.*
, estados.nombre AS nombre_estado
, municipios.nombre AS nombre_municipio
FROM
cat_empresas
INNER JOIN estados 
    ON (cat_empresas.estado_id = estados.id)
INNER JOIN municipios 
    ON (cat_empresas.municipio_id = municipios.id)";
$rs=$clsConsulta->consultaGeneral($con);
if($clsConsulta->numrows>0){
    foreach($rs as $v=>$val){
        $id = $val['id'];        
        $razon_social = $val['razon_social'];
        $direccion = $val['calle'].' '.$val['num_ext'].' '.$val['num_int'];        
        $rfc = $val['rfc'];
        $estado = $val['nombre_municipio'].', '.$val['nombre_estado'];        
        $cp = '<div class="text-center">'.$val['cp'].'</div>';        
        $fecha_alta = $val['fecha_alta'];
        $fecha_update = $val['fecha_update'];                   
        $emp_estatus=$val['estatus'];  
              
        switch ($emp_estatus){
            case 0:
                $estatus='<div class="text-danger text-center" style="cursor:pointer;"  onclick="ModalcambiaEstatus('.$id.',0)"> INACTIVO </div>';
            break;
            case 1:
                $estatus='<div class="text-succes text-center" style="cursor:pointer;" onclick="ModalcambiaEstatus('.$id.',1)"> ACTIVO </div>';
            break;

        }
  
        $data_array[] = array (            
            $razon_social,
            $direccion,
            $cp,
            $estado,
            $rfc,         
            $estatus         
        );
    } 

}
   
    $new_array=["data"=>$data_array];
    echo json_encode($new_array);
    ?>