<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();
include '../../lib/clsFechas.php';
$clsFecha=new Fechas();
$resultado='{
              "data": [';
?>

        <?php 
        $con="SELECT
            definicion_documentos.*
            , ciclo_proyecto.nombre as requeridoen
        FROM
            definicion_documentos
            INNER JOIN ciclo_proyecto 
                ON (definicion_documentos.ciclo_id = ciclo_proyecto.id)
        WHERE estatus=1";
        $rs=$clsConsulta->consultaGeneral($con);
        foreach($rs as $v=>$val){
            $id = $val['id'];
            $uuid = $val['uuid'];
            $nombre = $val['nombre'];
            $funcion=strtolower($nombre);
            $slug = $val['slug'];
            $obligatorio = ($val['obligatorio']==1)?'<b class="text-info">SI</b>':'<b class="text-secondary">NO</b>';
            $solicita_aprobacion = ($val['solicita_aprobacion']==1)?'<b class="text-info">SI</b>':'<b class="text-secondary">NO</b>';
            $solicita_comentario = ($val['solicita_comentario']==1)?'<b class="text-info">SI</b>':'<b class="text-secondary">NO</b>';
            $requeridoen = $val['requeridoen'];
            $multiple = ($val['multiple']==1)?'<b class="text-info">SI</b>':'<b class="text-secondary">NO</b>';
            $referencia = ($val['referencia']==1)?'<b class="text-info">SI</b>':'<b class="text-secondary">NO</b>';
            $seguimiento = ($val['seguimiento']==1)?'<b class="text-info">SI</b>':'<b class="text-secondary">NO</b>';
            $aplazamiento = ($val['aplazamiento']==1)?'<b class="text-info">SI</b>':'<b class="text-secondary">NO</b>';
            $created_at = $val['created_at'];
            $updated_at = $val['updated_at'];
            $deleted_at = $val['deleted_at'];
            $estatus = ($val['estatus']==1)?'ACTIVO':'DESHABILITADO';
            /*
            echo '<tr>';
            echo '<td><b>'.$nombre,'</b></td>';
            echo '<td>'.$requeridoen,'</td>';
            echo '<td class="text-center">'.$obligatorio.'</td>';
            echo '<td class="text-center">'.$solicita_aprobacion,'</td>';
            echo '<td class="text-center" class="text-center">'.$solicita_comentario,'</td>';
            echo '<td class="text-center">'.$multiple,'</td>';
            echo '<td class="text-center">'.$referencia,'</td>';
            echo '<td class="text-center">'.$seguimiento,'</td>';
            echo '<td class="text-center">'.$aplazamiento,'</td>';
            echo '<td>'.$estatus,'</td>';
            echo '<td class="text-center"><a title="Editar documento" href="clientes-documentos-editar/'.$id.'" class="text-info"><i class="fas fa-edit fa-lg"></i></a></td>';
            echo '<td class="text-center"><a style="cursor:pointer" title="Deshabilitar" data-toggle="modal" data-target="#borrar" class="text-danger" onclick="funborrar('.$id.');"><i class="fas fa-trash fa-lg"></i></a></td>';
            echo '</tr>';
            */
            
            
            $resultado.='[
                  $nombre,
                  $requeridoen,
                  $obligatorio,
                  $solicita_aprobacion,
                  $solicita_comentario,
                  $multiple,
                  $referencia,
                  $seguimiento,
                  $aplazamiento,
                  $estatus
                ],';    
        $resultado='}';
        }
return $resultado;
        ?>
    