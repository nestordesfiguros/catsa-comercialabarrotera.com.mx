<?php
// admin/pdf/reportes-ventas-vendedor.php
// Genera PDF: Reporte Ventas por Vendedor (mPDF)

session_start();
require __DIR__ . '/../../lib/clsConsultas.php';
require __DIR__ . '/../../vendor/autoload.php';

$clsConsulta = new Consultas();

// Obtener filtros desde GET (seguro con sanitizar)
$fecha_inicio = trim($_GET['fecha_inicio'] ?? '');
$fecha_fin    = trim($_GET['fecha_fin'] ?? '');
$vendedor_id  = intval($_GET['vendedor'] ?? 0);

// Empresa actual (filtro multiempresa)
$idEmpresa = intval($_SESSION['id_empresa'] ?? 0);

// Construir WHERE seguro usando sanitizar
$where = "IFNULL(r.estatus, '') <> 'cancelada'";

if ($idEmpresa > 0) {
    $where .= " AND r.id_empresa = " . $clsConsulta->sanitizar($idEmpresa, true, false);
}
if ($vendedor_id > 0) {
    $where .= " AND r.id_vendedor = " . $clsConsulta->sanitizar($vendedor_id, true, false);
}
if ($fecha_inicio !== '') {
    $where .= " AND DATE(r.fecha) >= " . $clsConsulta->sanitizar($fecha_inicio, false, false);
}
if ($fecha_fin !== '') {
    $where .= " AND DATE(r.fecha) <= " . $clsConsulta->sanitizar($fecha_fin, false, false);
}

// Query principal: remisiones/ventas
$sql = "
  SELECT
    r.id AS remision_id,
    r.fecha,
    r.total AS total_remision,
    r.id_vendedor,
    CONCAT_WS(' ', v.nombre, v.apellido1, v.apellido2) AS vendedor,
    c.razon_social AS cliente
  FROM cab_remisiones r
  LEFT JOIN cat_vendedores v ON v.id = r.id_vendedor
  LEFT JOIN cat_clientes c ON c.id = r.id_cliente
  WHERE {$where}
  ORDER BY r.fecha DESC
  LIMIT 5000
";

$rows = $clsConsulta->consultaGeneral($sql);
$numRows = $clsConsulta->numrows;

// Preparar mPDF
$mpdf = new \Mpdf\Mpdf([
    'default_font' => 'arial',
    'mode' => 'utf-8'
]);
$mpdf->SetTopMargin(40);

// Cabecera del PDF (logo + título)
$logoPath = __DIR__ . '/../../img/logo-inicio.png';
$logoHtml = '';
if (file_exists($logoPath)) {
    // mPDF acepta rutas de archivo
    $logoHtml = '<img src="' . $logoPath . '" style="width:140px;" alt="Logo" />';
}

$titulo = 'Reporte: Ventas por Vendedor';
$periodo = ($fecha_inicio ?: '---') . ' a ' . ($fecha_fin ?: '---');

$html = '<style>
    body{font-family: Arial, Helvetica, sans-serif; font-size:10px;}
    table { width:100%; border-collapse: collapse; font-size:10px; }
    th, td { padding:6px; border:1px solid #ddd; }
    th { background:#f2f2f2; font-weight:700; }
    .text-end{ text-align:right; }
    .text-center{ text-align:center; }
    .small{ font-size:9px; color:#555; }
</style>';

$header = '
<table style="border:none;">
  <tr style="border:none;">
    <td style="border:none; width:40%;">' . $logoHtml . '</td>
    <td style="border:none; width:60%; text-align:right;">
      <h3 style="margin:0;padding:0;">' . htmlspecialchars($titulo) . '</h3>
      <div class="small">Periodo: ' . htmlspecialchars($periodo) . '</div>
    </td>
  </tr>
</table>
<hr style="margin-top:10px;margin-bottom:8px;" />
';

$mpdf->SetHTMLHeader($header);

// Contenido principal: tabla
$html .= '<table><thead><tr>
    <th class="text-center">Remisión</th>
    <th class="text-center">Fecha</th>
    <th>Vendedor</th>
    <th>Cliente</th>
    <th class="text-end">Total</th>
</tr></thead><tbody>';

$totalGlobal = 0.0;

if ($numRows > 0 && is_array($rows)) {
    // la clase puede devolver índice desde 1; recorrer con protección
    foreach ($rows as $k => $r) {
        if (!is_array($r) || !isset($r['remision_id'])) continue;
        $remId = htmlspecialchars($r['remision_id']);
        $fecha = htmlspecialchars($r['fecha']);
        $vendedor = htmlspecialchars($r['vendedor'] ?? 'SIN VENDEDOR');
        $cliente = htmlspecialchars($r['cliente'] ?? '');
        $total = floatval($r['total_remision'] ?? 0);
        $totalGlobal += $total;

        $html .= '<tr>';
        $html .= '<td class="text-center">' . $remId . '</td>';
        $html .= '<td class="text-center">' . $fecha . '</td>';
        $html .= '<td>' . $vendedor . '</td>';
        $html .= '<td>' . $cliente . '</td>';
        $html .= '<td class="text-end">$' . number_format($total, 2, '.', ',') . '</td>';
        $html .= '</tr>';
    }
} else {
    $html .= '<tr><td colspan="5" class="text-center small">No se encontraron registros para los filtros seleccionados.</td></tr>';
}

$html .= '</tbody></table>';

$html .= '<br/><table style="width:100%;border:none;"><tr style="border:none;">
    <td style="border:none;"></td>
    <td style="border:none;text-align:right;"><strong>Total General: $' . number_format($totalGlobal, 2, '.', ',') . '</strong></td>
</tr></table>';

// Footer (opcional)
$footer = '<div style="font-size:9px;color:#666;text-align:center;">Generado: ' . date('d/m/Y H:i') . ' - Sistema</div>';
$mpdf->SetHTMLFooter($footer);

// Escribir y enviar al navegador (inline)
$mpdf->WriteHTML($html);

// Limpiar buffer accidental y enviar
if (ob_get_length()) {
    ob_end_clean();
}
$mpdf->Output('reporte_ventas_vendedor_' . date('Ymd_His') . '.pdf', 'I');
exit;
