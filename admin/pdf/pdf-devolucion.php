<?php
// admin/pdf/pdf-devolucion.php
require '../lib/clsConsultas.php';
$clsConsulta = new Consultas();

require '../vendor/autoload.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
  die('ID de devolución no válido.');
}

$mpdf = new \Mpdf\Mpdf(['default_font' => 'arial']);
$mpdf->SetTopMargin(45);

$html = '<style>
  body{font-family:Arial,sans-serif;font-size:8px;}
  table{width:100%;border-collapse:collapse;margin-top:20px;font-size:8px;}
  th,td{padding:6px 10px;border:1px solid #ccc;text-align:center;}
  th{background-color:#0e0e0e;color:#fff;}
  .text-start{text-align:left;}
  .text-end{text-align:right;}
  .borderNo{border:none;}
</style>';

/* ===== Cabecera ===== */
$sqlCab = "
  SELECT d.*, c.razon_social AS cliente
  FROM cab_devoluciones d
  LEFT JOIN cat_clientes c ON d.id_cliente = c.id
  WHERE d.id_devolucion = {$id}
";
$rsCab = $clsConsulta->consultaGeneral($sqlCab);
$cab   = $rsCab[1] ?? null;
if (!$cab) {
  die('No se encontró la devolución.');
}

$htmlHeader = '<table>
<tr class="borderNo">
  <td class="borderNo text-start"><img src=\"../img/logo-inicio.png" width=\"180\"></td>
  <td class="borderNo text-end">
    <h3>DEVOLUCIÓN #' . intval($cab['id_devolucion']) . '</h3>
    <p><strong>Fecha:</strong> ' . date('d/m/Y H:i', strtotime($cab['fecha'])) . '</p>
  </td>
</tr>
</table>';
$mpdf->SetHTMLHeader($htmlHeader);

$html .= '<p><strong>Cliente:</strong> ' . htmlspecialchars($cab['cliente']) . '</p>';
$html .= '<p><strong>Documento origen:</strong> ' . strtoupper($cab['tipo_documento']) . ' ' . intval($cab['id_documento']) . '</p>';
$html .= '<p><strong>Observaciones:</strong> ' . nl2br(htmlspecialchars($cab['observaciones'] ?? '')) . '</p>';

/* ===== Detalle ===== */
$sqlDet = "
  SELECT 
    m.id_producto, m.cantidad, m.motivo, m.precio_unitario,
    p.clave, p.nombre
  FROM mov_devoluciones m
  LEFT JOIN cat_productos p ON m.id_producto = p.id_producto
  WHERE m.id_devolucion = {$id}
";
$det = $clsConsulta->consultaGeneral($sqlDet);
$rows = $clsConsulta->numrows;

$html .= '<table>
<thead>
<tr>
  <th>Clave</th>
  <th>Producto</th>
  <th>Motivo</th>
  <th>Cantidad</th>
  <th>Precio</th>
  <th>Total</th>
</tr>
</thead><tbody>';

$totalGlobal = 0;

if ($rows > 0) {
  for ($i = 1; $i <= $rows; $i++) {
    $row = $det[$i];

    $cantidad = floatval($row['cantidad']);
    $precio   = floatval($row['precio_unitario']);
    $subtotal = $cantidad * $precio;
    $totalGlobal += $subtotal;

    $clave   = isset($row['clave']) ? $row['clave'] : '';
    $nombre  = isset($row['nombre']) ? $row['nombre'] : ('ID ' . $row['id_producto']);

    $html .= '<tr>';
    $html .= '<td>' . htmlspecialchars($clave) . '</td>';
    $html .= '<td class="text-start">' . htmlspecialchars($nombre) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['motivo']) . '</td>';
    $html .= '<td>' . $cantidad . '</td>';
    $html .= '<td class="text-end">$' . number_format($precio, 2) . '</td>';
    $html .= '<td class="text-end">$' . number_format($subtotal, 2) . '</td>';
    $html .= '</tr>';
  }
} else {
  $html .= '<tr><td colspan="6" class="text-start">Sin detalles registrados para esta devolución.</td></tr>';
}

$html .= '</tbody></table>';

$html .= '<p class="text-end"><strong>Total:</strong> $' . number_format($totalGlobal, 2) . '</p>';

$html .= '<br><br><table>
<tr class="borderNo">
  <td class="borderNo">Autorizado por: ________________________________</td>
  <td class="borderNo">Fecha: _______________________</td>
</tr>
</table>';

$mpdf->WriteHTML($html);
$mpdf->Output('devolucion_' . $id . '.pdf', 'I');
