<?php
require '../vendor/autoload.php';

$mpdf = new \Mpdf\Mpdf([
    'default_font' => 'arial',
    'margin_top' => 15,
    'margin_left' => 10,
    'margin_right' => 10,
    'margin_bottom' => 20,
    'format' => 'Letter'
]);

$html = '
<style>
    body { font-family: Arial; font-size: 10px; line-height: 1.1; }
    .header { margin-bottom: 5px; }
    .company-name { font-weight: bold; font-size: 14px; text-align: center; }
    .fiscal-info { text-align: left; margin-bottom: 5px; }
    .document-info { display: flex; justify-content: space-between; margin: 5px 0; }
    .left-col, .right-col { width: 48%; }
    table { width: 100%; border-collapse: collapse; margin: 5px 0; font-size: 9px; }
    th, td { border: 1px solid #000; padding: 2px 3px; }
    th { background-color: #f2f2f2; text-align: center; font-weight: normal; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .totals { text-align: right; margin-top: 5px; }
    .amount-words { margin-top: 10px; font-style: italic; border-top: 1px solid #000; padding-top: 3px; }
    .promissory-note { margin-top: 20px; border-top: 1px solid #000; padding-top: 5px; }
</style>

<div class="header">
    <div class="company-name">H TORRES</div>
    <div class="fiscal-info">
        <strong>Domicilio fiscal:</strong><br>
        <strong>R.F.C.:</strong><br>
        <strong>Lugar de expedición:</strong>
    </div>
</div>

<div class="document-info">
    <div class="left-col">
        <strong>Fecha:</strong> 19/02/2025<br>
        <strong>Forma de pago:</strong> Pago en una sola exhibición<br>
        <strong>Método de pago y Cuenta:</strong> No identificado
    </div>
    <div class="right-col">
        <strong>Série:</strong> STAND.<br>
        <strong>Folio:</strong> S10<br>
        <strong>Áprob. folios:</strong> 1<br>
        <strong>Año aprob.:</strong><br>
        <strong>Rango folio:</strong> 1 al
    </div>
</div>

<div>
    <strong>Vendedor:</strong> ARMANDO RAMIREZ<br>
    <strong>Enviar a:</strong>
</div>

<table>
    <thead>
        <tr>
            <th>Cantidad</th>
            <th>Unidad</th>
            <th>Clave</th>
            <th>Descripción</th>
            <th>% Desc.</th>
            <th>PIU</th>
            <th>Importe</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="text-center">2</td>
            <td class="text-center">CAJAS</td>
            <td class="text-center">148309</td>
            <td>PERSU. 144300M.</td>
            <td class="text-center">0.00</td>
            <td class="text-right">380.000000</td>
            <td class="text-right">760.00</td>
        </tr>
        <tr>
            <td class="text-center">2</td>
            <td class="text-center">CAJAS</td>
            <td class="text-center">46516</td>
            <td>PERSU. ACCION PROFUNDA 4/4.65L</td>
            <td class="text-center">0.00</td>
            <td class="text-right">480.000000</td>
            <td class="text-right">960.00</td>
        </tr>
        <tr>
            <td class="text-center">60</td>
            <td class="text-center">PA</td>
            <td class="text-center">430019</td>
            <td>MAYONESA MCCORMIC 3.4KG</td>
            <td class="text-center">0.00</td>
            <td class="text-right">260.000000</td>
            <td class="text-right">15,500.00</td>
        </tr>
        <tr>
            <td class="text-center">5</td>
            <td class="text-center">CAJAS</td>
            <td class="text-center">072158</td>
            <td>JABON ZEST 72/150g</td>
            <td class="text-center">0.00</td>
            <td class="text-right">972.000000</td>
            <td class="text-right">4,860.00</td>
        </tr>
        <tr>
            <td class="text-center">12</td>
            <td class="text-center">CAJA</td>
            <td class="text-center">00406</td>
            <td>LISTERINE COOL MINT 2/1 LT</td>
            <td class="text-center">0.00</td>
            <td class="text-right">250.000000</td>
            <td class="text-right">3,000.00</td>
        </tr>
    </tbody>
</table>

<div class="totals">
    Subtotal<br>
    25,180.00<br><br>
    Total<br>
    25,180.00
</div>

<div class="amount-words">
    VEINTICINCO MIL CIENTO OCHENTA PESOS 00/100 M.N.
</div>

<div class="promissory-note">
    Debo y pagaré incondicionalmente por este pagaré a la orden:<br>
    ARMANDO RAMIREZ<br><br>
    en esta ciudad de:<br>
    en donde se reclama el documento<br><br>
    la cantidad de: $ 25,180.00<br>
    VEINTICINCO MIL CIENTO OCHENTA PESOS 00/100
</div>';

$mpdf->WriteHTML($html);
$mpdf->Output('Remision_S10.pdf', 'I');