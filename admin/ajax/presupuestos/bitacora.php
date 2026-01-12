<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

$folio=$_POST['folio'];  
?>

<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel">Bitacora</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>        
<div class="modal-body">

        <table class="table">
            <thead>
                <tr>
                    <th class="th-sm">Usuario</th>
                    <th class="th-sm">Accion</th>
                    <th class="th-sm">Comentario</th>
                    <th class="th-sm">Fecha y Hora</th>                            
                </tr>
            </thead>
            <tbody>                      
            <?php
            $con = "SELECT
                    mov_bitacora.*
                    , CONCAT(usuarios.nombre    , usuarios.apellido1    , usuarios.apellido2) AS nombre
                FROM
                    mov_bitacora
                    INNER JOIN usuarios 
                ON (mov_bitacora.id_usuario = usuarios.id) 
                WHERE seccion='presupuestos' AND folio='".$folio."'";
            //    echo $con.'<br>';
            $rs = $clsConsulta->consultaGeneral($con);
            if ($clsConsulta->numrows > 0) {
                foreach ($rs as $v => $val) {                            
                    $id_usuario = $val['nombre'];
                    $accion = $val['accion'];
                    $comentario = $val['comentario'];
                //    $seccion = $val['seccion'];
                    $fecha_mov = $val['fecha_mov'];
                    echo'
                    <tr>
                        <td>'.$id_usuario.'</td>
                        <td>'.$accion.'</td>
                        <td>'.$comentario.'</td>
                        <td>'.$fecha_mov.'</td>
                    </tr>';

                }
            }else{
                echo '<tr><td colspan="4" class="text-center">No hay movimientos</tr></td>';
            }
            ?>    
            </tbody>
        </table>

</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>            
    <!--button type="submit" id="guardar" class="btn btn-primary">Guardar</button-->
</div>  