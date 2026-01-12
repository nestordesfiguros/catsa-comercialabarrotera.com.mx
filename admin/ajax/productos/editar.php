<?php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();
$id=$_POST['id'];
$con="SELECT * FROM productos WHERE ID_Producto=".$id;
$rs=$clsConsulta->consultaGeneral($con);
foreach($rs as $v=>$val){
    $ID_Producto=$val['ID_Producto'];
    $Nombre=$val['Nombre'];
    $Descripcion=$val['Descripcion'];
    $Precio_Unitario=$val['Precio_Unitario'];
    $Cantidad_Disponible=$val['Cantidad_Disponible'];
    $Unidad_Medida=$val['Unidad_Medida'];
    $ID_Categoria=$val['ID_Categoria'];
    
}
?>
<div class="modal-body row gy-3">
    <div class="form-group">
        <label>Categoría</label>
        <select class="form-select" name="ID_Categoria">            
            <?php
            $con = "SELECT * FROM cat_categorias";
            $rs = $clsConsulta->consultaGeneral($con);
            foreach ($rs as $v => $val) {
                if($val['id_categoria']==$ID_Categoria){
                    echo '<option value="' . $val['id_categoria'] . '" selected>' . $val['nombre_categoria'] . '</option>';
                }else{
                    echo '<option value="' . $val['id_categoria'] . '">' . $val['nombre_categoria'] . '</option>';
                }
                
            }
            ?>
        </select>
    </div>
    <div class="form-group">
        <label>Unidad de medida</label>
        <select class="form-select" name="Unidad_Medida">
            <option value="">Selecciona una unidad de medida</option>
            <?php
            $con = "SELECT * FROM unidades_de_medida";
            $rs = $clsConsulta->consultaGeneral($con);
            foreach ($rs as $v => $val) {
                if( $val['id_unidad'] == $Unidad_Medida){
                    echo '<option value="' . $val['id_unidad'] . '" selected>' . $val['nombre_unidad'] . '</option>';
                }else{
                    echo '<option value="' . $val['id_unidad'] . '">' . $val['nombre_unidad'] . '</option>';
                }
                
            }
            ?>
        </select>
    </div>
    <div class="form-group">
        <label>Nombre</label>
        <input type="text" class="form-control " name="Nombre" value="<?php echo $Nombre; ?>" >
    </div>
    <div class="form-group">
        <label>Descripcion</label>
        <textarea class="form-control" name="Descripcion"><?php echo $Descripcion; ?></textarea>
    </div>
    <div class="form-group">
        <label>Precio Unitario</label>
        <input type="text" class="form-control " name="Precio_Unitario" value="<?php echo $Precio_Unitario; ?>" >
    </div>
    <div class="form-group">
        <label>Cantidad Disponible</label>
        <input type="text" class="form-control " name="Cantidad_Disponible" value="<?php echo $Cantidad_Disponible; ?>" >
    </div>
</div>