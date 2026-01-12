<?php
// ajax/almacen-salidas/detalle.php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($idEmpresa <= 0 || $id <= 0) {
    echo '<div class="alert alert-danger">Solicitud inválida.</div>';
    exit;
}

// Validar que la salida pertenece a la empresa
$chk = $clsConsulta->consultaGeneral("SELECT id FROM cab_salidas_almacen WHERE id={$id} AND id_empresa={$idEmpresa} LIMIT 1");
if ($clsConsulta->numrows < 1) {
    echo '<div class="alert alert-danger">Salida no encontrada.</div>';
    exit;
}

echo '<style>
#tablaDetalle td, #tablaDetalle th { padding: 6px 10px; }
</style>';

$sql = "
    SELECT
        ms.cantidad,
        p.clave,
        p.nombre,
        u.nombre_unidad AS unidad
    FROM mov_salidas_almacen ms
    INNER JOIN cat_productos p ON p.id_producto = ms.id_producto
    LEFT JOIN cat_unidades_de_medida u ON u.id_unidad = p.unidad_medida
    WHERE ms.id_salida = {$id}
    ORDER BY p.nombre ASC
";

$rs = $clsConsulta->consultaGeneral($sql);

echo '<table id="tablaDetalle" class="table table-bordered table-striped">
<thead class="bg-info">
<tr>
    <th class="text-center text-white">Cantidad</th>
    <th class="text-center text-white">Clave</th>
    <th class="text-center text-white">Nombre</th>
    <th class="text-center text-white">Unidad</th>
</tr>
</thead>
<tbody>';

if ($clsConsulta->numrows > 0 && is_array($rs)) {
    foreach ($rs as $val) {
        echo '<tr>';
        echo '<td class="text-end">' . number_format((float)$val['cantidad'], 2, ".", ",") . '</td>';
        echo '<td>' . htmlspecialchars($val['clave']) . '</td>';
        echo '<td>' . htmlspecialchars($val['nombre']) . '</td>';
        echo '<td class="text-center">' . htmlspecialchars($val['unidad'] ?? 'PZA') . '</td>';
        echo '</tr>';
    }
}

echo '</tbody></table>';
