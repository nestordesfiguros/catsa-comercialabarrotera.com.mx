<?php
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();


$id_estado = $_POST['id_estado'];

?>
<div class="form-group">
    <!--label form="id_municipio">Municipio</label-->
    <select name="id_municipio" id="id_municipio" class="form-select" onchange="fnCp();">
        <option value="">Selecciona un Municipio</option>
        <?php
        $con = "SELECT * FROM municipios WHERE estado_id=" . $id_estado. " ORDER BY nombre ASC";
        $rs = $clsConsulta->consultaGeneral($con);
        foreach ($rs as $v => $val) {
            echo '<option value="' . $val['id'] . '">' . $val['nombre'] . '</option>';
        }
        ?>
    </select>
</div>
