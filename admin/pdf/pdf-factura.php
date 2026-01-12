<?php
// pdf/pdf-factura.php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../lib/clsConsultas.php';

use Mpdf\Mpdf;

$clsConsulta = new Consultas();
$raiz        = dirname(__DIR__);                   // raíz del proyecto
$dirTimbrado = $raiz . '/ajax/timbrados';          // donde timbrar.php guarda factura_{id}.xml|pdf

/* =========================
   Parámetro
   ========================= */
$id_factura = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_factura <= 0) {
    echo 'Factura no encontrada.';
    exit;
}

/* =========================
   Cabecera + Receptor + Empresa (Emisor)
   ========================= */
$sqlCab = "
    SELECT
        cf.*,
        cli.razon_social       AS rec_razon,
        CAST(cli.rfc AS CHAR)  AS rec_rfc,
        IFNULL(cli.regimen_fiscal,'') AS rec_regimen,
        IFNULL(cli.cp,'')      AS rec_cp,
        CONCAT(IFNULL(cli.calle,''),' ',IFNULL(cli.num_ext,''),' ',IFNULL(cli.num_int,''),' ',
               IFNULL(cli.colonia,''),' CP ',IFNULL(cli.cp,'')) AS rec_domicilio
    FROM cab_facturas cf
    JOIN cat_clientes   cli ON cli.id = cf.id_receptor
    WHERE cf.id = {$id_factura}
    LIMIT 1
";
$cab = $clsConsulta->consultaGeneral($sqlCab);
$cab = $cab[1] ?? null;
if (!$cab) {
    echo 'Factura no encontrada o datos incorrectos.';
    exit;
}

/* Empresa (emisor) */
$idEmpresa = (int)($cab['id_empresa'] ?? 0);
if ($idEmpresa <= 0) $idEmpresa = 1;

$emp = $clsConsulta->consultaGeneral("SELECT * FROM cat_empresas WHERE id={$idEmpresa} LIMIT 1");
$emp = $emp[1] ?? null;
if (!$emp) {
    echo 'No se encontró la empresa emisora.';
    exit;
}

/* =========================
   Detalle (conceptos)
   ========================= */
$sqlDet = "
    SELECT
        m.id_producto, m.cantidad, m.precio,
        p.nombre, p.clave, p.unidad_medida, p.excento
    FROM mov_facturas m
    JOIN cat_productos p ON p.id_producto = m.id_producto
    WHERE m.id_factura = {$id_factura}
";
$detalles = $clsConsulta->consultaGeneral($sqlDet);
if (!$detalles || $clsConsulta->numrows <= 0) {
    echo 'La factura no tiene conceptos.';
    exit;
}

/* =========================
   Datos derivados / timbre desde XML
   ========================= */
$xmlPath = $dirTimbrado . "/factura_{$id_factura}.xml";
$uuid = trim((string)($cab['uuid'] ?? ''));
$noCertCFDI = $noCertSAT = $fechaTimbrado = '';
$selloCFD = $selloSAT = '';

if (is_file($xmlPath)) {
    libxml_use_internal_errors(true);
    $sx = @simplexml_load_file($xmlPath);
    if ($sx) {
        $ns = $sx->getNamespaces(true);
        // NoCertificado CFDI
        if (isset($sx['NoCertificado'])) {
            $noCertCFDI = (string)$sx['NoCertificado'];
        } elseif (isset($ns['cfdi'])) {
            $n = $sx->children($ns['cfdi']);
            if (isset($n['NoCertificado'])) $noCertCFDI = (string)$n['NoCertificado'];
        }
        // Timbre
        $tfd = $sx->xpath('//tfd:TimbreFiscalDigital');
        if (!$tfd && isset($ns['cfdi'])) {
            $tfd = $sx->xpath('//cfdi:Complemento/tfd:TimbreFiscalDigital');
        }
        if ($tfd && isset($tfd[0])) {
            $t = $tfd[0];
            if ($uuid === '' && isset($t['UUID']))           $uuid          = (string)$t['UUID'];
            if (isset($t['NoCertificadoSAT']))               $noCertSAT     = (string)$t['NoCertificadoSAT'];
            if (isset($t['FechaTimbrado']))                  $fechaTimbrado = (string)$t['FechaTimbrado'];
            if (isset($t['SelloCFD']))                       $selloCFD      = (string)$t['SelloCFD'];
            if (isset($t['SelloSAT']))                       $selloSAT      = (string)$t['SelloSAT'];
        }
    }
}
if ($fechaTimbrado === '' && !empty($cab['fecha_timbrado'])) {
    $fechaTimbrado = date('c', strtotime($cab['fecha_timbrado']));
}

/* =========================
   Totales
   ========================= */
$subtotal = (float)$cab['subtotal'];
$total    = (float)$cab['total'];
$iva      = max($total - $subtotal, 0.00);

/* =========================
   Logo
   ========================= */
$logoPath = $raiz . '/img/logo.png';
$logoB64  = is_file($logoPath) ? base64_encode(file_get_contents($logoPath)) : '';

/* =========================
   MPDF
   ========================= */
$pdf = new Mpdf([
    'mode'          => 'utf-8',
    'format'        => 'A4',
    'margin_left'   => 12,
    'margin_right'  => 12,
    'margin_top'    => 12,
    'margin_bottom' => 14,
]);

$css = <<<CSS
*{ font-family: DejaVu Sans, Arial, Helvetica, sans-serif; }
h1,h2,h3{ margin:0; }
.header{
  border:1px solid #004B87; border-radius:8px; padding:10px; margin-bottom:10px;
}
.header-table{ width:100%; }
.header-left{ width:60%; vertical-align:top; }
.header-right{ width:40%; vertical-align:top; text-align:right; }
.brand{ color:#004B87; }
.box{
  border:1px solid #ddd; border-radius:8px; padding:10px; margin-top:8px;
}
.grid{
  width:100%; border-collapse:collapse; margin-top:6px;
}
.grid th{
  background:#f5f7fb; color:#333; padding:8px; border-bottom:1px solid #e0e0e0; font-size:12px;
}
.grid td{
  padding:7px; border-bottom:1px solid #efefef; font-size:12px;
}
.text-right{ text-align:right; }
.text-center{ text-align:center; }
.badge{
  display:inline-block; padding:2px 8px; border-radius:10px; background:#004B87; color:#fff; font-size:11px;
}
.footer-note{ font-size:10px; color:#666; margin-top:10px; }
.small{ font-size:11px; color:#333; }
.code{ font-family: monospace; font-size:10px; word-wrap:anywhere; }
.totals{ width:45%; float:right; margin-top:10px; }
.totals td{ font-size:12px; padding:6px 4px; }
.hr{ height:1px; background:#e0e3eb; border:0; margin:8px 0; }
.qr-box{ text-align:center; margin-top:10px; }
CSS;

$pdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);

/* =========================
   Encabezado
   ========================= */
$serie   = htmlspecialchars((string)$cab['serie']);
$folio   = htmlspecialchars((string)$cab['folio']);
$emRfc   = htmlspecialchars((string)$emp['rfc']);
$emNom   = htmlspecialchars((string)$emp['razon_social']);
$emReg   = htmlspecialchars((string)($emp['regimen_fiscal'] ?? ''));
$emCP    = htmlspecialchars((string)($emp['cp'] ?? ''));
$emDom   = htmlspecialchars(trim(($emp['calle'] ?? '') . ' ' . ($emp['num_ext'] ?? '') . ' ' . ($emp['num_int'] ?? '') . ' ' . ($emp['colonia'] ?? '') . ' CP ' . ($emp['cp'] ?? '')));

$recRfc  = htmlspecialchars((string)$cab['rec_rfc']);
$recRaz  = htmlspecialchars((string)$cab['rec_razon']);
$recReg  = htmlspecialchars((string)$cab['rec_regimen']);
$recCP   = htmlspecialchars((string)$cab['rec_cp']);
$recDom  = htmlspecialchars((string)$cab['rec_domicilio']);

$fechaEmi = date('d/m/Y H:i', strtotime($cab['fecha']));
$fechaTim = $fechaTimbrado ? date('d/m/Y H:i', strtotime($fechaTimbrado)) : ($cab['fecha_timbrado'] ? date('d/m/Y H:i', strtotime($cab['fecha_timbrado'])) : '');

$header = '
<div class="header">
  <table class="header-table">
    <tr>
      <td class="header-left">
        ' . ($logoB64 ? '<img src="data:image/png;base64,' . $logoB64 . '" style="height:60px;"/>' : '') . '
        <div class="brand">
          <h2>Factura</h2>
          <div class="small">Serie: <b>' . $serie . '</b> &nbsp; | &nbsp; Folio: <b>' . $folio . '</b></div>
        </div>
      </td>
      <td class="header-right">
        <div><span class="badge">CFDI 4.0</span></div>
        <div class="small"><b>Fecha de emisión:</b> ' . $fechaEmi . '</div>
        ' . ($fechaTim ? '<div class="small"><b>Fecha de timbrado:</b> ' . $fechaTim . '</div>' : '') . '
        ' . ($uuid !== '' ? '<div class="small"><b>UUID:</b> ' . $uuid . '</div>' : '') . '
      </td>
    </tr>
  </table>
</div>';

$pdf->WriteHTML($header, \Mpdf\HTMLParserMode::HTML_BODY);

/* =========================
   Emisor / Receptor
   ========================= */
$emisorReceptor = '
<table width="100%" cellspacing="0" cellpadding="0">
  <tr>
    <td width="50%" style="padding-right:6px;">
      <div class="box">
        <h3 style="color:#004B87;margin-bottom:6px;">Emisor</h3>
        <div class="small"><b>RFC:</b> ' . $emRfc . '</div>
        <div class="small"><b>Razón Social:</b> ' . $emNom . '</div>
        <div class="small"><b>Régimen Fiscal:</b> ' . $emReg . '</div>
        <div class="small"><b>Domicilio:</b> ' . $emDom . '</div>
        <div class="small"><b>C.P.:</b> ' . $emCP . '</div>
      </div>
    </td>
    <td width="50%" style="padding-left:6px;">
      <div class="box">
        <h3 style="color:#004B87;margin-bottom:6px;">Receptor</h3>
        <div class="small"><b>RFC:</b> ' . $recRfc . '</div>
        <div class="small"><b>Razón Social:</b> ' . $recRaz . '</div>
        <div class="small"><b>Régimen Fiscal:</b> ' . $recReg . '</div>
        <div class="small"><b>Domicilio:</b> ' . $recDom . '</div>
        <div class="small"><b>C.P.:</b> ' . $recCP . '</div>
      </div>
    </td>
  </tr>
</table>';
$pdf->WriteHTML($emisorReceptor, \Mpdf\HTMLParserMode::HTML_BODY);

/* =========================
   Tabla de Conceptos
   ========================= */
$tbody = '';
foreach ($detalles as $r) {
    if (!is_array($r) || !isset($r['id_producto'])) continue;
    $clave   = htmlspecialchars((string)($r['clave'] ?? ''));
    $nombre  = htmlspecialchars((string)$r['nombre']);
    $cant    = (float)$r['cantidad'];
    $precio  = (float)$r['precio'];
    $importe = $cant * $precio;

    $tbody .= '<tr>
      <td class="text-center">' . ($clave !== '' ? $clave : '&nbsp;') . '</td>
      <td>' . $nombre . '</td>
      <td class="text-center">' . number_format($cant, 2, '.', ',') . '</td>
      <td class="text-right">$ ' . number_format($precio, 2, '.', ',') . '</td>
      <td class="text-right">$ ' . number_format($importe, 2, '.', ',') . '</td>
    </tr>';
}

$tabla = '
<div class="box">
  <h3 style="color:#004B87;margin-bottom:6px;">Conceptos</h3>
  <table class="grid">
    <thead>
      <tr>
        <th class="text-center" style="width:16%;">Clave</th>
        <th>Descripción</th>
        <th class="text-center" style="width:12%;">Cantidad</th>
        <th class="text-right" style="width:16%;">Precio U.</th>
        <th class="text-right" style="width:16%;">Importe</th>
      </tr>
    </thead>
    <tbody>' . $tbody . '</tbody>
  </table>

  <table class="totals" cellspacing="0" cellpadding="0" align="right">
    <tr><td class="text-right" style="width:60%;">Subtotal:</td><td class="text-right" style="width:40%;">$ ' . number_format($subtotal, 2, '.', ',') . '</td></tr>
    <tr><td class="text-right">IVA 16%:</td><td class="text-right">$ ' . number_format($iva, 2, '.', ',') . '</td></tr>
    <tr><td class="text-right"><b>Total:</b></td><td class="text-right"><b>$ ' . number_format($total, 2, '.', ',') . '</b></td></tr>
  </table>
  <div style="clear:both;"></div>
</div>';
$pdf->WriteHTML($tabla, \Mpdf\HTMLParserMode::HTML_BODY);

/* =========================
   Timbrado / Sellos / QR
   ========================= */
$qrPath = $dirTimbrado . "/factura_{$id_factura}.png";
$qrImg  = (is_file($qrPath) ? base64_encode(file_get_contents($qrPath)) : '');

$timbrado = '
<div class="box">
  <h3 style="color:#004B87;margin-bottom:6px;">Timbre fiscal</h3>
  <table width="100%">
    <tr>
      <td width="70%" style="vertical-align:top;">
        <div class="small"><b>UUID:</b> ' . htmlspecialchars($uuid) . '</div>
        <div class="small"><b>No. Certificado CFDI:</b> ' . htmlspecialchars($noCertCFDI) . '</div>
        <div class="small"><b>No. Certificado SAT:</b> ' . htmlspecialchars($noCertSAT) . '</div>
        ' . ($fechaTim ? '<div class="small"><b>Fecha de certificación:</b> ' . htmlspecialchars($fechaTim) . '</div>' : '') . '
        <div class="hr"></div>
        <div class="small"><b>Sello Digital del CFDI:</b></div>
        <div class="code">' . htmlspecialchars($selloCFD) . '</div>
        <div class="small" style="margin-top:6px;"><b>Sello Digital del SAT:</b></div>
        <div class="code">' . htmlspecialchars($selloSAT) . '</div>
      </td>
      <td width="30%" style="vertical-align:top;">
        ' . ($qrImg ? '<div class="qr-box"><img src="data:image/png;base64,' . $qrImg . '" style="width:120px;"/><div class="small" style="margin-top:6px;">Código QR</div></div>' : '') . '
      </td>
    </tr>
  </table>
  <div class="footer-note">Este documento es una representación impresa de un CFDI.</div>
</div>';
$pdf->WriteHTML($timbrado, \Mpdf\HTMLParserMode::HTML_BODY);

/* =========================
   Salida
   ========================= */
$pdf->SetTitle("Factura {$serie}{$folio}");
$pdf->Output("factura_{$id_factura}.pdf", 'I');
exit;
