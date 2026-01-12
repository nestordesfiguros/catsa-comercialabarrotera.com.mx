<?php
// ajax/gastos/export-pdf.php
session_start();
require_once '../../lib/clsConsultas.php';

$cls       = new Consultas();
$idEmpresa = intval($_SESSION['id_empresa'] ?? 0);

$fini = $_POST['filtroFechaInicio'] ?? '';
$ffin = $_POST['filtroFechaFin'] ?? '';
$fcat = trim($_POST['filtroCategoria'] ?? '');
$fmet = trim($_POST['filtroMetodo'] ?? '');
$fest = trim($_POST['filtroEstatus'] ?? '');
$search = trim($_POST['search'] ?? '');

$where = " WHERE g.id_empresa = {$idEmpresa} ";
if ($fini !== '' && $ffin !== '') {
    $where .= " AND g.fecha_gasto BETWEEN " . $cls->sanitizar($fini) . " AND " . $cls->sanitizar($ffin) . " ";
}
if ($fcat !== '') {
    $where .= " AND g.id_categoria = " . intval($fcat) . " ";
}
if ($fmet !== '') {
    $where .= " AND g.id_metodo   = " . intval($fmet) . " ";
}
if ($fest !== '') {
    $where .= " AND g.estatus     = " . $cls->sanitizar($fest) . " ";
}
if ($search !== '') {
    $sv = $cls->escape($search);
    $where .= " AND (g.descripcion LIKE '%{$sv}%' OR g.referencia LIKE '%{$sv}%' OR c.nombre LIKE '%{$sv}%' OR m.descripcion LIKE '%{$sv}%') ";
}

$sql = "
  SELECT g.fecha_gasto, g.hora_gasto, c.nombre AS categoria, g.descripcion, m.descripcion AS metodo, g.referencia, g.monto, g.estatus
  FROM mov_gastos g
  INNER JOIN cat_gastos_categorias c ON c.id_categoria = g.id_categoria
  INNER JOIN cat_metodos_pago m      ON m.id = g.id_metodo
  {$where}
  ORDER BY g.fecha_gasto DESC, g.hora_gasto DESC
";

$rs = $cls->aplicaQuery($sql);
$total = 0.0;

$rowsHtml = '';
while ($row = mysqli_fetch_assoc($rs)) {
    $total += floatval($row['monto']);
    $rowsHtml .= '<tr>
    <td>' . htmlspecialchars($row['fecha_gasto']) . '</td>
    <td>' . htmlspecialchars($row['hora_gasto']) . '</td>
    <td>' . htmlspecialchars($row['categoria']) . '</td>
    <td>' . htmlspecialchars($row['descripcion']) . '</td>
    <td>' . htmlspecialchars($row['metodo']) . '</td>
    <td>' . htmlspecialchars($row['referencia']) . '</td>
    <td style="text-align:right;">' . number_format((float)$row['monto'], 2) . '</td>
    <td>' . htmlspecialchars($row['estatus']) . '</td>
  </tr>';
}

$html = '
<html><head><meta charset="utf-8">
<style>
  body{ font-family: DejaVu Sans, Arial, sans-serif; font-size:12px; }
  h2{ margin:0 0 10px 0; }
  table{ width:100%; border-collapse: collapse; }
  th, td{ border:1px solid #ccc; padding:6px; }
  th{ background:#f5f5f5; }
  tfoot td{ font-weight:bold; }
</style></head><body>
  <h2>Gastos</h2>
  <table>
    <thead>
      <tr>
        <th>Fecha</th><th>Hora</th><th>Categoría</th><th>Descripción</th><th>Método</th><th>Referencia</th><th>Monto</th><th>Estatus</th>
      </tr>
    </thead>
    <tbody>' . $rowsHtml . '</tbody>
    <tfoot>
      <tr><td colspan="6" style="text-align:right;">TOTAL</td><td style="text-align:right;">' . number_format($total, 2) . '</td><td></td></tr>
    </tfoot>
  </table>
</body></html>';

// Si Dompdf está disponible, generar PDF; si no, mostrar HTML imprimible
if (class_exists('\\Dompdf\\Dompdf')) {
    $dompdf = new \Dompdf\Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    $dompdf->stream('gastos_' . date('Ymd_His') . '.pdf', ['Attachment' => true]);
} else {
    header('Content-Type: text/html; charset=utf-8');
    echo $html;
}
