
<?php
require_once __DIR__ . '/../lib/clsConsultas.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Mpdf\Mpdf;
use NumberFormatter;

$clsConsulta = new Consultas();
$idFactura = $_GET['id'] ?? null;

if (!$idFactura) {
    die("Folio de factura no proporcionado.");
}

// Obtener datos de la factura
$conCab = "SELECT * FROM cab_facturas WHERE id = " . (int)$idFactura;
$cab = $clsConsulta->consultaGeneral($conCab);
if ($clsConsulta->numrows === 0) {
    die("Factura no encontrada.");
}
$factura = $cab[1];

// Obtener datos del emisor
$idEmisor = $factura['id_emisor'];
$conEmisor = "SELECT 
    empresa.razon_social,
    empresa.rfc,
    empresa.calle,
    empresa.num_ext,
    empresa.num_int,
    empresa.colonia,
    empresa.cp,
    empresa.tel1,
    estados.nombre AS nestado,
    municipios.nombre AS nmunicipio
FROM empresa
LEFT JOIN estados ON empresa.id_estado = estados.id
LEFT JOIN municipios ON empresa.id_municipio = municipios.id
WHERE empresa.id = " . (int)$idEmisor;
$datosEmisor = $clsConsulta->consultaGeneral($conEmisor);
$emisor = $datosEmisor[1];

// Obtener datos del receptor (cliente)
$idCliente = $factura['id_receptor'];
$conCliente = "SELECT
    estados.nombre AS nestado,
    municipios.nombre AS nmunicipio,
    cat_clientes.razon_social,
    cat_clientes.nombre_comercial,
    cat_clientes.calle,
    cat_clientes.num_ext,
    cat_clientes.num_int,
    cat_clientes.colonia,
    cat_clientes.cp,
    cat_clientes.tel1,
    cat_clientes.rfc
FROM cat_clientes
INNER JOIN estados ON cat_clientes.id_estado = estados.id
INNER JOIN municipios ON cat_clientes.id_municipio = municipios.id
WHERE cat_clientes.id = " . (int)$idCliente;
$datosCliente = $clsConsulta->consultaGeneral($conCliente);
$cliente = $datosCliente[1];

// Obtener conceptos
$conProd = "SELECT * FROM mov_facturas WHERE id_factura = " . (int)$idFactura;
$productos = $clsConsulta->consultaGeneral($conProd);

// Convertir total a letra
$fmt = new NumberFormatter("es", NumberFormatter::SPELLOUT);
$totalLetra = strtoupper($fmt->format($factura['total'])) . " PESOS 00/100 M.N.";

$mpdf = new Mpdf(['format' => 'A4']);
$mpdf->SetTitle("Factura " . $factura['folio']);

$uuid = $factura['uuid'] ?? 'UUID_DE_EJEMPLO';
$fecha_cert = $factura['fecha_timbrado'] ?? $factura['fecha'];
$serie_cert = '00001000000711275824';
$cert_sat = '00001000000709182898';

$html = '
<style>
    body { font-family: sans-serif; font-size: 11px; }
    .titulo { font-size: 16px; font-weight: bold; text-align: center; }
    .tabla { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .tabla th, .tabla td { border: 1px solid #000; padding: 4px; }
    .no-border td { border: none; }
</style>

<table width="100%">
<tr>
    <td width="30%">
        <img src="..//img/logo-inicio.png" width="150">
    </td>
    <td width="70%" style="text-align: right;">
        <div class="titulo">FACTURA</div>
    </td>
</tr>
</table>

<table class="no-border">
<tr><td><b>Empresa:</b> ' . $emisor['razon_social'] . '</td><td><b>RFC:</b> ' . $emisor['rfc'] . '</td></tr>
<tr><td colspan="2"><b>Domicilio:</b> ' . $emisor['calle'] . ' ' . $emisor['num_ext'] . ', Col. ' . $emisor['colonia'] . ', C.P. ' . $emisor['cp'] . ', ' . $emisor['nmunicipio'] . ', ' . $emisor['nestado'] . '</td></tr>
<tr><td><b>Tipo de Comprobante:</b> I - Ingreso</td><td><b>Folio:</b> ' . $factura['folio'] . '</td></tr>
<tr><td><b>Lugar de Expedición:</b> ' . $factura['lugar_expedicion'] . '</td><td><b>Régimen Fiscal:</b> 601 - General de Ley Personas Morales</td></tr>
<tr><td><b>Forma de pago:</b> ' . $factura['forma_pago'] . '</td><td><b>Método de pago:</b> ' . $factura['metodo_pago'] . '</td></tr>
<tr><td><b>Moneda:</b> ' . $factura['moneda'] . '</td><td><b>Fecha:</b> ' . $factura['fecha'] . '</td></tr>
</table>

<br><b>Datos del Cliente</b>
<table class="no-border">
<tr><td><b>Cliente:</b> ' . $cliente['razon_social'] . '</td></tr>
<tr><td><b>RFC:</b> ' . $cliente['rfc'] . '</td></tr>
<tr><td><b>Domicilio:</b> ' . $cliente['calle'] . ' ' . $cliente['num_ext'] . ', Col. ' . $cliente['colonia'] . ', C.P. ' . $cliente['cp'] . ', ' . $cliente['nmunicipio'] . ', ' . $cliente['nestado'] . '</td></tr>
</table>

<br><b>Conceptos</b>
<table class="tabla">
<thead><tr><th>Cantidad</th><th>Unidad</th><th>Clave SAT</th><th>Descripción</th><th>V. Unitario</th><th>Impuestos</th><th>Importe</th></tr></thead>
<tbody>
';

foreach ($productos as $i => $prod) {
    if (!is_numeric($i)) continue;
    $imp = $prod['importe'] ?? ($prod['cantidad'] * $prod['precio']);
    $html .= '<tr>
        <td>' . $prod['cantidad'] . '</td>
        <td>E48</td>
        <td>81112100</td>
        <td>' . $prod['descripcion'] . '</td>
        <td>$' . number_format($prod['precio'], 2) . '</td>
        <td>$' . number_format($imp * 0.16, 2) . '</td>
        <td>$' . number_format($imp, 2) . '</td>
    </tr>';
}

$html .= '
</tbody></table>

<br><b>Importe con letra:</b> ' . $totalLetra . '

<br><br>
<table class="no-border" style="text-align: right;">
<tr><td><b>Subtotal:</b> $' . number_format($factura['subtotal'], 2) . '</td></tr>
<tr><td><b>Impuestos:</b> $' . number_format($factura['total'] - $factura['subtotal'], 2) . '</td></tr>
<tr><td><b>Total:</b> $' . number_format($factura['total'], 2) . '</td></tr>
</table>

<br><br><b>Datos de Certificación</b>
<table class="no-border">
<tr><td><b>Folio fiscal (UUID):</b> ' . $uuid . '</td></tr>
<tr><td><b>Serie del Certificado del Emisor:</b> ' . $serie_cert . '</td></tr>
<tr><td><b>Certificado SAT:</b> ' . $cert_sat . '</td></tr>
<tr><td><b>Fecha de certificación:</b> ' . $fecha_cert . '</td></tr>
</table>

<p style="font-size: 9px; color: #555;">Este documento es una representación impresa de un CFDI</p>
';

$mpdf->WriteHTML($html);
$mpdf->Output("Factura_{$factura['folio']}.pdf", 'I');
?>
