<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();
include '../../lib/clsFechas.php';
$clsFecha=new Fechas();

$id=$_POST['id'];
$con="SELECT
    presupuestos.*
    , cat_clientes.razon_social
    , cat_clientes.siroc
    , cat_clientes.registro_patronal
    , cat_clientes.expediente
    , cat_clientes.presupuesto
FROM
    presupuestos
    INNER JOIN cat_clientes 
        ON (presupuestos.cliente_id = cat_clientes.id)
WHERE presupuestos.id=".$id;

$rs=$clsConsulta->consultaGeneral($con);
foreach($rs as $v=>$val){
     $id = $val['id'];
  //   $uid = $val['uid'];
     $folio = $val['folio'];
     $razon_social = $val['razon_social'];
     $descripcion = $val['descripcion'];
     $cliente_id = $val['cliente_id'];
     $monto = $val['monto'];
     $siroc = ($val['siroc']=1)?'SI':'NO';
     $presupuesto = ($val['presupuesto']=1)?'SI':'NO';
     $fecha_recepcion = $val['fecha_recepcion'];
     $archivo = $val['archivo'];
     $estado = $val['estado'];
     $created_at = $val['created_at'];
     $updated_at = $val['updated_at'];
}
?>

<form action="abc/modifica-presupuesto" method="post">    
        <div class="modal-body">
          <div class="row">
            <div class="form-group">
                <label for="folio">Folio</label>
                <input type="text" class="form-control" name="folio" id="folio" placeholder="Folio" value="<?php echo $folio; ?>" disabled>
            </div>
            <div class="form-group">
                <label for="cliente">Cliente</label>
                <?php                
                    echo '<input type="text" class="form-control" value='.$razon_social.' disabled />'; 
                    echo '<input type="hidden" class="form-control" name="cliente" value="'.$razon_social.'" />';
             
                ?>
                <!--
                <input type="text" class="form-control" list="listaClientes" name="cliente" value="<?php echo $razon_social; ?>" placeholder="Presiona dos Click para elejir un cliente o escribre la razón social">
                <datalist id="listaClientes">                    
                    <?php
                    /*
                    $con = "SELECT * FROM cat_clientes";
                    $rs = $clsConsulta->consultaGeneral($con);
                    if ($clsConsulta->numrows > 0) {
                        foreach ($rs as $v => $val) {
                            echo '<option value="'.$val["razon_social"].'">';
                        }
                    }
                    */
                    ?>
                </datalist>
            -->
               
            </div>
            <div class="form-group">
              <label for="">Fecha de recepción:</label>                                                             
                <input type="date" name="fecha_recepcion" class="form-control" value="<?php echo $fecha_recepcion; ?>">
            </div>
            <div class="form-group">
                <label>$ Monto</label>
                <input class="form-control" type="text" name="monto" id="maskmoney" placeholder="$0.00" value="<?php echo $monto; ?>" />
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea name="descripcion" rows="5" class="form-control"><?php echo $descripcion; ?></textarea>
            </div> 
        </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>            
            <button type="submit"  class="btn btn-primary">Guardar Cambios</button>
            <input type="hidden" name="url" value="clientes-presupuestos">
            <input type="hidden" name="cliente_id" value="<?php echo $cliente_id; ?>">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <input type="hidden" name="folio" value="<?php echo $folio; ?>">
        </div>
</form>

