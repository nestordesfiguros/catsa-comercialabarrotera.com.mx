<?php
// ajax/almacen-entradas/detalle.php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($idEmpresa <= 0 || $id <= 0) {
    echo '<div class="alert alert-danger">Solicitud inválida.</div>';
    exit;
}

echo '<style>
#tablaDetalle td, #tablaDetalle th { padding: 5px 10px; }
</style>';

// Valida que la entrada pertenezca a la empresa
$chk = $clsConsulta->consultaGeneral("SELECT id FROM cab_entradas_almacen WHERE id={$id} AND id_empresa={$idEmpresa} LIMIT 1");
if ($clsConsulta->numrows < 1) {
    echo '<div class="alert alert-danger">Entrada no encontrada.</div>';
    exit;
}

$con = "SELECT
    m.cantidad,
    p.clave,
    p.nombre
FROM mov_entradas_almacen m
INNER JOIN cat_productos p ON m.id_producto = p.id_producto
WHERE m.id_orden_compra = {$id}";

$rs = $clsConsulta->consultaGeneral($con);

echo '<table id="tablaDetalle" class="table table-bordered table-striped">
<thead class="bg-info">
<tr>
    <th class="text-center text-white">Cantidad</th>
    <th class="text-center text-white">Clave</th>
    <th class="text-center text-white">Nombre</th>
</tr>
</thead>
<tbody>';

if ($clsConsulta->numrows > 0 && is_array($rs)) {
    foreach ($rs as $val) {
        echo '<tr>';
        echo '<td class="text-end">' . number_format((float)$val['cantidad'], 2, ".", ",") . '</td>';
        echo '<td>' . htmlspecialchars($val['clave']) . '</td>';
        echo '<td>' . htmlspecialchars($val['nombre']) . '</td>';
        echo '</tr>';
    }
}

echo '</tbody></table>';
