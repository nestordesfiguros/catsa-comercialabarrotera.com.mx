<!-- Tags Input -->
<link rel="stylesheet" href="../admin/dist/tagsinput/bootstrap-tagsinput.css">
<script src="../admin/dist/tagsinput/bootstrap-tagsinput.min.js"></script>
<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

$id=$_POST['id'];
$sucursal=($_POST['sucursal']=='' || $_POST['sucursal']==0)? '':"AND clientes_correos.sucursal='".$_POST['sucursal']."'";
$sucursal=trim($sucursal);
$id=($_POST['id']==0)?'':$_POST['id'];

if(!is_numeric($id)){
    $con="SELECT id FROM cat_clientes WHERE razon_social='".$id."'";    
    $rs=$clsConsulta->consultaGeneral($con);
    if($clsConsulta->numrows>0){
        foreach($rs as $v=>$val){
            $id=$val['id'];
        }
    }else{
        $id=0;
    }
}

    /* Destinatario */
    $correos='';
    $con="SELECT
            clientes_correos_procesos.id_correo
            , clientes_correos_procesos.id_proceso
            , clientes_correos.nombre
            , clientes_correos.correo
            , clientes_correos.sucursal
            , clientes_correos.cliente_id
        FROM
            clientes_correos_procesos
            INNER JOIN clientes_correos 
                ON (clientes_correos_procesos.id_correo = clientes_correos.id)
        WHERE clientes_correos.cliente_id=".$id." 
        AND clientes_correos_procesos.id_proceso=1
        ".$sucursal."
        AND clientes_correos_procesos.tipo_correo=1";    
        
    $rs=$clsConsulta->consultaGeneral($con);    
    if($clsConsulta->numrows>0){
        foreach($rs as $v=>$val){
            $correos .= $val['correo'].',';
        }
        $correos=trim($correos,",");
                            
        echo '<div>
            <div>Destinatario</div>                     
            <input type="text" name="correosDesstinatario" value="'.$correos.'" data-role="tagsinput" />
        </div>';
        /* CC */
        $correos='';
        $con="SELECT
                clientes_correos_procesos.id_correo
                , clientes_correos_procesos.id_proceso
                , clientes_correos.nombre
                , clientes_correos.correo
                , clientes_correos.cliente_id
            FROM
                clientes_correos_procesos
                INNER JOIN clientes_correos 
                    ON (clientes_correos_procesos.id_correo = clientes_correos.id)
                    WHERE clientes_correos.cliente_id=".$id." 
                AND clientes_correos_procesos.id_proceso=1
                ".$sucursal." 
                AND clientes_correos_procesos.tipo_correo=2";
                
        $rs=$clsConsulta->consultaGeneral($con);
        if($clsConsulta->numrows>0){
            foreach($rs as $v=>$val){
                $correos .= $val['correo'].',';
            }
            $correos=trim($correos,",");
            echo '<div class="mt-3">
                <div>CC</div>                    
                <input type="text" style="width:100%;" name="correosCC" value="'.$correos.'" data-role="tagsinput" />
            </div>';
        }else{
            echo '<div class="mt-3"> CC  </div>
            <div class="correosinput">  Sin correos asignados </div>';
        }
        /* CCO */
        $correos='';
        $con="SELECT
                clientes_correos_procesos.id_correo
                , clientes_correos_procesos.id_proceso
                , clientes_correos.nombre
                , clientes_correos.correo
                , clientes_correos.cliente_id
            FROM
                clientes_correos_procesos
                INNER JOIN clientes_correos 
                    ON (clientes_correos_procesos.id_correo = clientes_correos.id)
                    WHERE clientes_correos.cliente_id=".$id." 
        AND clientes_correos_procesos.id_proceso=1
        ".$sucursal." 
        AND clientes_correos_procesos.tipo_correo=3";
        $rs=$clsConsulta->consultaGeneral($con);
        if($clsConsulta->numrows>0){
            foreach($rs as $v=>$val){
                $correos .= $val['correo'].',';
            }
            $correos=trim($correos,",");
            echo '<div class="mt-3">
                <div>CCO</div>                     
                <input type="text" name="correosCCO" value="'.$correos.'" data-role="tagsinput" />
            </div>';
        }else{
            echo '<div class="mt-3"> CCO  </div>
            <div class="correosinput">  Sin correos asignados </div>';
        }
        
    }else{
                
        echo '<div> Destinatario  </div>
            <div class="correosinput">  Sin correos asignados </div>';
        echo '<div class="mt-3"> CC  </div>
            <div class="correosinput">  Sin correos asignados </div>';
        echo '<div class="mt-3"> CCO  </div>
            <div class="correosinput">  Sin correos asignados </div>';
            echo '</div>';
    /*        
    }
    
}else{
        
    echo '<div> Destinatario  </div>
        <div class="correosinput">  Sin correos asignados </div>';
    echo '<div class="mt-3"> CC  </div>
        <div class="correosinput">  Sin correos asignados </div>';
    echo '<div class="mt-3"> CCO  </div>
        <div class="correosinput">  Sin correos asignados </div>';
}
*/
}
?>