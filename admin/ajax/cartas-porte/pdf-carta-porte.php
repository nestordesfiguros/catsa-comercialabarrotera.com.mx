<?php
require '../../lib/clsConsultas.php';
require '../../lib/phpqrcode/qrlib.php';
require '../../vendor/autoload.php';

use Mpdf\Mpdf;

$clsConsulta = new Consultas();

$idCarta = intval($_GET['id'] ?? 0);
if ($idCarta <= 0) {
    die('ID de carta porte inválido');
}

// ==============================
// 1. Obtener datos generales
// ==============================
$sql = "
SELECT cp.*, 
       v.nombre_vehiculo, v.placas, v.marca, v.modelo, v.anio,
       o.nombre AS operador_nombre, o.apellido1, o.apellido2,
       a.almacen AS nombre_origen,
       e.razon_social, e.rfc, e.calle, e.num_ext, e.colonia, e.cp, e.tel1
FROM cartas_porte cp
INNER JOIN cat_vehiculos v ON cp.id_vehiculo = v.id
INNER JOIN cat_operadores o ON cp.id_operador = o.id
INNER JOIN cat_almacenes a ON cp.origen = a.id
INNER JOIN empresa e ON e.id = 1
WHERE cp.id = {$idCarta}
LIMIT 1
";
$carta = $clsConsulta->consultaGeneral($sql);
if ($clsConsulta->numrows <= 0) {
    die('No se encontró la carta porte');
}
$c = $carta[1];

// ==============================
// 2. Obtener remisiones y productos
// ==============================
$sqlRem = "
SELECT cpr.id_remision, r.id AS remision_id, r.fecha, r.total, c.razon_social AS cliente
FROM cartas_porte_remisiones cpr
INNER JOIN cab_remisiones r ON cpr.id_remision = r.id
INNER JOIN cat_clientes c ON r.id_cliente = c.id
WHERE cpr.id_carta_porte = {$idCarta}
ORDER BY r.id ASC
";
$remisiones = $clsConsulta->consultaGeneral($sqlRem);

$remisionesData = [];
$totalGeneral = 0;
$totalProductos = 0;

if ($clsConsulta->numrows > 0) {
    foreach ($remisiones as $k => $rem) {
        $idRem = $rem['id_remision'];
        $rem['productos'] = [];

        $sqlProd = "
        SELECT m.cantidad, m.precio_unitario, m.precio, p.nombre AS producto
        FROM mov_remisiones m
        LEFT JOIN cat_productos p ON m.id_producto = p.id_producto
        WHERE m.id_remision = {$idRem}
        ";
        $productos = $clsConsulta->consultaGeneral($sqlProd);

        $subtotal = 0;
        $countProd = 0;
        if ($clsConsulta->numrows > 0) {
            foreach ($productos as $p) {
                $rem['productos'][] = [
                    'cantidad' => $p['cantidad'],
                    'producto' => $p['producto'],
                    'precio_unitario' => $p['precio_unitario'],
                    'importe' => $p['precio']
                ];
                $subtotal += $p['precio'];
                $countProd += $p['cantidad'];
            }
        }

        $rem['subtotal'] = $subtotal;
        $totalGeneral += $subtotal;
        $totalProductos += $countProd;
        $remisionesData[] = $rem;
    }
}

// ==============================
// 3. Generar QR ficticio
// ==============================
$qrData = "UUID:" . strtoupper(bin2hex(random_bytes(8))) . "\nCarta Porte: " . $c['folio'];
$qrTemp = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
QRcode::png($qrData, $qrTemp, QR_ECLEVEL_L, 4);

// ==============================
// 4. Generar PDF con MPDF
// ==============================
$mpdf = new Mpdf(['format' => 'Letter']);

$html = '
<style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 8.5pt; /* Texto general más compacto */
        color: #000;
    }

    /* Encabezado Empresa */
    .empresa-info {
        font-size: 8.5pt;
        line-height: 12pt;
    }
    .empresa-info strong {
        font-size: 9pt;
    }

    /* Encabezado Carta Porte */
    .carta-porte-header {
        background-color: #f2f2f2;
        padding: 4px;
        font-size: 8.5pt;
        line-height: 12pt;
        margin-bottom: 6px;
    }

    /* Tablas de remisiones */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 8px;
    }
    th {
        background-color: #f2f2f2;
        text-align: left;
        font-size: 8pt; /* Encabezados de tabla */
        padding: 3px;
        border-bottom: 0.3px solid #ccc;
    }
    td {
        font-size: 8pt; /* Datos */
        padding: 3px;
        border-bottom: 0.3px solid #eee;
    }

    /* Totales */
    .total-row td {
        font-weight: bold;
        font-size: 8.2pt;
    }

    /* QR y leyenda SAT */
    .qr-section {
        margin-top: 8px;
        text-align: left;
        font-size: 7pt;
    }
    .qr-section img {
        width: 70px;
    }
    .leyenda-sat {
        font-size: 7pt; /* Pie más pequeño */
        line-height: 10pt;
        margin-top: 3px;
    }
</style>


<table class="header">
<tr>
<td width="60%">
    <img src="../../img/logo.png" style="height:50px;"><br><br>
    <strong>' . $c['razon_social'] . '</strong><br>
    RFC: ' . $c['rfc'] . '<br>
    ' . $c['calle'] . ' ' . $c['num_ext'] . '<br>
    Col. ' . $c['colonia'] . ' C.P. ' . $c['cp'] . '<br>
    Tel: ' . $c['tel1'] . '
</td>
<td width="40%" style="text-align:right; font-size:9pt;">
    <strong>CARTA DE PORTE - TRASLADO</strong><br>
    Tipo CFDI: Traslado<br>
    Certificado Emisor: 3000100000040002336<br>
    Certificado SAT: 3000100000040002495<br>
    Fecha Certificación: ' . date('d/m/Y H:i') . '<br>
    Fecha Expedición: ' . date('d/m/Y H:i') . '<br>
    Lugar Expedición: ' . $c['cp'] . '<br>
    Uso CFDI: G03 - Gastos en general<br>
    Método de Pago: PPD<br>
    Forma de Pago: 02 - Cheque nominativo
</td>
</tr>
</table>

<div class="gray-box" >
<strong>Carta Porte: ' . $c['folio'] . '</strong><br>
Fecha Salida: ' . date('d/m/Y H:i', strtotime($c['fecha_salida'])) . '<br>
Vehículo: ' . $c['nombre_vehiculo'] . ' - ' . $c['placas'] . ' (' . $c['marca'] . ' ' . $c['modelo'] . ' ' . $c['anio'] . ')<br>
Operador: ' . strtoupper($c['operador_nombre'] . ' ' . $c['apellido1'] . ' ' . $c['apellido2']) . '<br>
Origen: ' . $c['nombre_origen'] . '<br>
Destino: ' . $c['destino'] . '<br>
Estatus: ' . $c['estatus'] . '
</div>
';

foreach ($remisionesData as $rem) {
    $html .= '
    <br>
    <strong>Remisión: ' . $rem['remision_id'] . ' - Cliente: ' . strtoupper($rem['cliente']) . '</strong>
    <table class="table">
        <thead>
            <tr>
                <th>Cant</th>
                <th>Producto</th>
                <th>Precio Unitario</th>
                <th>Importe</th>
            </tr>
        </thead>
        <tbody>';
    foreach ($rem['productos'] as $prod) {
        $html .= '
        <tr>
            <td>' . $prod['cantidad'] . '</td>
            <td>' . $prod['producto'] . '</td>
            <td>$' . number_format($prod['precio_unitario'], 2) . '</td>
            <td>$' . number_format($prod['importe'], 2) . '</td>
        </tr>';
    }
    $html .= '
        <tr>
            <td colspan="3" class="total" style="text-align:right;">Total Remisión</td>
            <td class="total">$' . number_format($rem['subtotal'], 2) . '</td>
        </tr>
        </tbody>
    </table>';
}

$html .= '
<br>
<strong>Total Productos:</strong> ' . $totalProductos . '<br>
<strong>Total General:</strong> $' . number_format($totalGeneral, 2) . '<br><br>

<table width="100%">
<tr>
<td width="30%">
    <img src="' . $qrTemp . '" width="120"><br>
    QR Ficticio
</td>
<td width="70%" style="font-size:8pt; text-align:justify;">
UUID: ' . strtoupper(bin2hex(random_bytes(8))) . '<br>
Sello Digital CFDI: ' . strtoupper(bin2hex(random_bytes(20))) . '<br>
Sello SAT: ' . strtoupper(bin2hex(random_bytes(20))) . '<br>
Cadena Original: ' . strtoupper(bin2hex(random_bytes(20))) . '<br><br>
Este documento es una representación impresa de un CFDI de tipo Carta Porte.<br>
La reproducción no autorizada de este documento puede ser sancionada por la ley.<br>
Para verificar la autenticidad de este comprobante, visite el portal del SAT.
</td>
</tr>
</table>
';

$mpdf->WriteHTML($html);
$mpdf->Output('CartaPorte_' . $c['folio'] . '.pdf', 'I');
