<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

$base=trim($_POST['base']);
//echo'BASE: '. $base;

switch ($base){
    case 1:        
?>
<table id="clientes" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Monto</th>
            <th>Fecha Recepción</th>
            <th>Seleccionar</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $con="SELECT * FROM post_venta WHERE estado='Aprobado'";
        // echo $con;
        $rs=$clsConsulta->consultaGeneral($con);
        if($clsConsulta->numrows>0){
            foreach($rs as $v=>$val){
                $pasavalor=$val['id'].",'".$val['nombre']."'";
                echo '<tr>';
                echo '<td>'.$val['nombre'].'</td>';                                        
                echo '<td>'.$val['fecha_rececpión'].'</td>';
                echo '<td class="text-end">$'.number_format($val['monto'],'2','.',',').'</td>';                
                echo '<td class="text-center"><button type="button" class="btn btn-primary" onclick="pasaidbase('.$pasavalor.')" >Seleccionar</button></td>';                                         
                echo '</tr>';
            }
        }else{
            echo '<tr>';
            echo '<td colspan=4 class="text-center">No se encontraron resultados</td>';                                                                                       
            echo '</tr>';
        }
        ?>
    </tbody>
</table>
<?php
        
    break;
    case 2:      
?>
<table id="clientes" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Folio</th>
            <th>Descripcion</th>
            <th>Monto</th>
            <th>Seleccionar</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $con="SELECT * FROM presupuestos WHERE estado='Aprobado'";
        // echo $con;
        $rs=$clsConsulta->consultaGeneral($con);
        if($clsConsulta->numrows>0){
            foreach($rs as $v=>$val){

                $id_cliente=$val['cliente_id'];
                $conc="SELECT razon_social FROM cat_clientes WHERE id=".$id_cliente;
                $rsc=$clsConsulta->consultaGeneral($conc);
                foreach($rsc as $vc=>$valc){
                    $razon_social=$valc['razon_social'];
                }
                
                $pasavalor=$val['id'].",'".$val['folio']."','".$razon_social."', ".$id_cliente;
                $id_cliente=$val['cliente_id'];
                echo '<tr>';
                echo '<td>'.$val['folio'].'</td>';                                        
                echo '<td>'.$val['descripcion'].'</td>';
                echo '<td class="text-end">$'.number_format($val['monto'],'2','.',',').'</td>';                
                echo '<td class="text-center"><button type="button" class="btn btn-primary" onclick="pasaidbase('.$pasavalor.')" >Seleccionar</button></td>';                                       
                echo '</tr>';
            }
        }else{
            echo '<tr>';
            echo '<td colspan=4 class="text-center">No se encontraron resultados</td>';                                                                                       
            echo '</tr>';
        }
        
        ?>
    </tbody>
</table>
<?php 
    break;
    case 3:
        ?>
<table id="clientes" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Folio</th>
            <th>Descripcion</th>
            <th>Fecha_firma</th>
            <th>Seleccionar</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $con="SELECT * FROM clientes_sirocs WHERE estado='Aprobado'";
        // echo $con;
        $rs=$clsConsulta->consultaGeneral($con);
        if($clsConsulta->numrows>0){
            foreach($rs as $v=>$val){

                $id_cliente=$val['cliente_id'];                
                $conc="SELECT razon_social FROM cat_clientes WHERE id=".$id_cliente;
                $rsc=$clsConsulta->consultaGeneral($conc);
                foreach($rsc as $vc=>$valc){
                    $razon_social=$valc['razon_social'];
                }
                
                $pasavalor=$val['id'].",'".$val['folio']."','".$razon_social."', ".$id_cliente;
                echo '<tr>';
                echo '<td>'.$val['folio'].'</td>';                                        
                echo '<td>'.$val['descripcion'].'</td>';
                echo '<td class="text-end">'.$val['fecha_firma'].'</td>';                
                echo '<td class="text-center"><button type="button" class="btn btn-primary" onclick="pasaidbase('.$pasavalor.')" >Seleccionar</button></td>';                                        
                echo '</tr>';
            }
        }else{
            echo '<tr>';
            echo '<td colspan=4 class="text-center">No se encontraron resultados</td>';                                                                                       
            echo '</tr>';
        }
        ?>
    </tbody>
</table>
<?php         
    break;
}  // End Switch


?>
<script>
    function pasaidbase(id, folio, razon_social, id_cliente){
         //   console.log(id+' - '+id_cliente+' - '.razon_social);
            $("#folio").val(folio);
            $("#foliobase").val(folio);
            $("#exampleModal").modal('hide');
            $("#nombre_cliente").val(razon_social);
            $("#id_cliente").val(id_cliente);
        };
        
</script>