<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

$estado_id=trim($_POST['estado']);      
?>
<select name="municipio_id" id="municipio_id" class="form-select">
    <option value="">Seleccione un Municipio</option>
    <?php
    $con="SELECT * FROM municipios WHERE estado_id=".$estado_id." ORDER BY nombre ASC";
    $rs=$clsConsulta->consultaGeneral($con);
    foreach($rs as $v=>$val){
        echo '<option value="'.$val['id'].'">'.$val['nombre'].'</option>';
    }
    ?>
</select>

<?php echo $con; ?>