<?php
// admin/ajax/clientes/municipios.php
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id_estado = isset($_POST['id_estado']) ? (int)$_POST['id_estado'] : 0;
?>

<label class="form-label mb-1"><span class="text-danger">*</span> Municipio</label>
<select name="id_municipio" id="id_municipio" class="form-select" onchange="fnCp();">
    <option value="">Selecciona un Municipio</option>
    <?php
    if ($id_estado > 0) {
        $con = "SELECT id, nombre FROM municipios WHERE estado_id = " . $id_estado . " ORDER BY nombre ASC";
        $rs = $clsConsulta->consultaGeneral($con);

        if ($clsConsulta->numrows > 0) {
            foreach ($rs as $val) {
                echo '<option value="' . (int)$val['id'] . '">' . htmlspecialchars($val['nombre']) . '</option>';
            }
        }
    }
    ?>
</select>