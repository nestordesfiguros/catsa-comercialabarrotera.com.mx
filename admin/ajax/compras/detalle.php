<?php
// ajax/compras/detalle.php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($idEmpresa <= 0 || $id <= 0) {
  echo '<div class="alert alert-danger">Solicitud inválida.</div>';
  exit;
}

// Validar compra de empresa
$chk = $clsConsulta->consultaGeneral("SELECT id FROM cab_compras WHERE id={$id} AND id_empresa={$idEmpresa} LIMIT 1");
if ($clsConsulta->numrows < 1) {
  echo '<div class="alert alert-danger">Compra no encontrada.</div>';
  exit;
}

echo '<style>
#tablaDetalle td, #tablaDetalle th { padding: 5px 10px; }
</style>';

$con = "SELECT
           mc.cantidad,
           mc.precio,
           p.clave,
           p.nombre
        FROM mov_compras mc
        INNER JOIN cat_productos p ON mc.id_producto = p.id_producto
        WHERE mc.id_orden_compra = {$id}";

$rs = $clsConsulta->consultaGeneral($con);

$granTotal = 0;
$tbodyHtml = '';

if ($clsConsulta->numrows > 0 && is_array($rs)) {
  foreach ($rs as $val) {
    $importe = (float)$val['cantidad'] * (float)$val['precio'];
    $granTotal += $importe;

    $tbodyHtml .= '<tr>';
    $tbodyHtml .= '<td class="text-end">' . number_format((float)$val['cantidad'], 0, ".", ",") . '</td>';
    $tbodyHtml .= '<td>' . htmlspecialchars($val['clave']) . '</td>';
    $tbodyHtml .= '<td>' . htmlspecialchars($val['nombre']) . '</td>';
    $tbodyHtml .= '<td class="text-end">$' . number_format((float)$val['precio'], 2, ".", ",") . '</td>';
    $tbodyHtml .= '<td class="text-end">$' . number_format($importe, 2, ".", ",") . '</td>';
    $tbodyHtml .= '</tr>';
  }
}

$totalFmt = number_format($granTotal, 2, ".", ",");

echo '<table id="tablaDetalle" class="table table-bordered table-striped" data-total="' . $totalFmt . '">
<thead class="bg-info">
<tr>
    <th class="text-center text-white">Cantidad</th>
    <th class="text-center text-white">Clave</th>
    <th class="text-center text-white">Nombre</th>
    <th class="text-center text-white">Precio</th>
    <th class="text-center text-white">Total</th>
</tr>
</thead>
<tbody>';

echo $tbodyHtml;

echo '</tbody>
<tfoot>
  <tr>
    <th colspan="4" class="text-end">TOTAL</th>
    <th class="text-end">$' . $totalFmt . '</th>
  </tr>
</tfoot>
</table>';
