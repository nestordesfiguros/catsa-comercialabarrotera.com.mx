<?php
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();


$id_estado = $_POST['id_estado'];

?>
<label form="laboral_municipio">Municipio</label>
<select name="laboral_municipio" id="laboral_municipio"  class="form-select" onchange="fnCplaboral();">
    <option value="">Selecciona un Municipio</option>
    <?php
    $con = "SELECT * FROM municipios WHERE estado_id=".$id_estado;
    $rs = $clsConsulta->consultaGeneral($con);
    foreach ($rs as $v => $val) {
        echo '<option value="' . $val['id'] . '">' . $val['nombre'] . '</option>';
    }
    ?>
</select>