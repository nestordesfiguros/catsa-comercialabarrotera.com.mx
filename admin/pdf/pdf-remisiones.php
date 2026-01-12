<?php
ob_start(); // Captura cualquier salida accidental

include '../lib/clsConsultas.php';
$clsConsulta = new Consultas();

setlocale(LC_TIME, 'es_MX.UTF-8'); // Para español de México

// Incluir el autoloader de Composer
require '../vendor/autoload.php';
$id = $_GET['id'];
$idCliente = $_GET['idc'];

// Crear una instancia de mPDF
$mpdf = new \Mpdf\Mpdf([
    'default_font' => 'arial',  // Definir Arial como fuente por defecto
]);

$htmlHeader = ''; // Se inicializa para evitar warning
$html = '';

// Estilos CSS para la tabla
$html .= '<style>
    body {
        font-family: Arial, sans-serif;      
        font-size: 11px;    
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        font-size: 11px;
    }
    table th, table td {
        padding: 8px 12px;
        text-align: center;
        border: 1px solid #000000;
    }
    table th {
        border: 1px solid #000000;
        border-bottom: 2px solid #000; 
        font-weight: bold;
    }
    table td {
        font-size: 11px;
    }
    table .text-end {
        text-align: right;
    }
    table .text-start{
         text-align: left;
    }
    table .text-center{
        text-align: center;
    }
    img{
        width:200px;
    }
    table .borderNo{
        border:none
    }
    .footer-remision {
        margin-top: 50px;
        page-break-inside: avoid;
    }
    .border-bottom {
        border-bottom: 1px solid #000;
    }
</style>';

$con = "SELECT
    estados.nombre AS nestado
    , municipios.nombre AS nmunicipio
    , cat_clientes.razon_social
    , cat_clientes.nombre_comercial
    , cat_clientes.calle
    , cat_clientes.num_ext
    , cat_clientes.num_int
    , cat_clientes.colonia
    , cat_clientes.cp
    , cat_clientes.tel1
FROM
    cat_clientes
    INNER JOIN estados 
        ON (cat_clientes.id_estado = estados.id)
    INNER JOIN municipios 
        ON (cat_clientes.id_municipio = municipios.id)
WHERE cat_clientes.id=" . $idCliente;
$resCliente = $clsConsulta->consultaGeneral($con);
foreach ($resCliente as $v => $val) {
    $razonSocialP = $val['razon_social'];
    $calleP = strtoupper($val['calle'] . ' ' . $val['num_ext'] . ' ' . $val['num_int']);
    $domicilioP = strtoupper($val['cp'] . ' ' . $val['colonia'] . ' ' . $val['nmunicipio'] . ' ' . $val['nestado']);
    $tel1P = ($val['tel1'] != '') ? $val['tel1'] : '';
}

$query = "SELECT
    empresa.id
    , empresa.razon_social
    , empresa.calle
    , empresa.num_ext
    , empresa.num_int
    , empresa.cp
    , empresa.colonia
    , empresa.tel1
    , empresa.tel2
    , empresa.tel3
    , estados.nombre AS nestado
    , municipios.nombre AS nmunicipio
FROM
    empresa
    INNER JOIN estados 
        ON (empresa.id_estado = estados.id)
    INNER JOIN municipios 
        ON (empresa.id_municipio = municipios.id) WHERE empresa.id=1";
$res = $clsConsulta->consultaGeneral($query);
foreach ($res as $v => $val) {
    $razonSocial = $val['razon_social'];
    $calle = strtoupper($val['calle'] . ' ' . $val['num_ext'] . ' ' . $val['num_int']);
    $domicilio = strtoupper($val['cp'] . ' ' . $val['colonia'] . ' ' . $val['nmunicipio'] . ' ' . $val['nestado']);
    $tel1 = ($val['tel1'] != '') ? $val['tel1'] : '';
    $tel2 = ($val['tel2'] != '') ? $val['tel2'] : '';
    $tel3 = ($val['tel3'] != '') ? $val['tel3'] : '';
}

setlocale(LC_TIME, 'es_MX.UTF-8');

$con = "SELECT
    cab_remisiones.id AS id_remision
    , cab_remisiones.fecha
    , cab_remisiones.estatus
    , cab_remisiones.total
    , cab_remisiones.direccion_envio
    , CASE 
        WHEN cab_remisiones.id_vendedor > 0 THEN CONCAT(cat_vendedores.nombre, ' ', cat_vendedores.apellido1, ' ', cat_vendedores.apellido2)
        ELSE CONCAT(usuarios.nombre, ' ', usuarios.apellido1, ' ', usuarios.apellido2)
      END AS nombre_vendedor_usuario
    , CASE 
        WHEN cab_remisiones.id_vendedor > 0 THEN CONCAT(cat_vendedores.apellido1, ' ', cat_vendedores.apellido2)
        ELSE CONCAT(usuarios.apellido1, ' ', usuarios.apellido2)
      END AS apellido_vendedor_usuario
FROM
    cab_remisiones
    LEFT JOIN cat_vendedores 
        ON cab_remisiones.id_vendedor = cat_vendedores.id
    LEFT JOIN usuarios 
        ON cab_remisiones.id_usuario = usuarios.id
         WHERE cab_remisiones.id=" . $id;
$res = $clsConsulta->consultaGeneral($con);
foreach ($res as $v => $val) {
    $fecha = $val['fecha'];
    $id_remision = $val['id_remision'];
    $estatus = $val['estatus'];
    $direccion_envio = $val['direccion_envio'];
    $vendedor = $val['nombre_vendedor_usuario'];

    $date = new DateTime($fecha);

    $fmt = new IntlDateFormatter(
        'es_MX',
        IntlDateFormatter::LONG,
        IntlDateFormatter::NONE,
        null,
        IntlDateFormatter::GREGORIAN
    );

    $fechaLarga = $fmt->format($date);
}

$mpdf->SetTopMargin(45);

$htmlHeader .= '<table >
    <tr class="borderNo">
        <td class="borderNo">        
            <table >         
                <tr class="borderNo">
                    <td class="text-start borderNo"><img src="../img/logo-inicio.png" /></td>                    
                </tr>
                <tr class="borderNo">                    
                    <td class="text-start borderNo">
                    ' . $razonSocial . ' <br/>
                    ' . $calle . ' <br>
                    ' . $domicilio . ' <br>
                    ' . $tel1 . ' ' . $tel2 . ' ' . $tel3 . '
                    </td>
                </tr>
            </table>
        </td>
        <td class="text-end borderNo">
            <table > 
                <tr class="borderNo"> 
                    <td class="borderNo text-start">
                    <h4> Solicitado por: </h4> 
                    ' . $razonSocialP . ' 
                    <br/>  
                    <b>Dirección de envío:</b> <br/>
                    ' . $direccion_envio . ' <br/>                    
                    ' . $tel1P . '
                    <br/>  
                    </td>
                </tr>            
            </table>
        </td>
        <td class="text-end borderNo">
            <table > 
                <tr class="borderNo"> 
                    <td class="borderNo text-end">
                    <h2> Remisión No. ' . $id_remision . ' </h2> <br>
                    <h4>' . $fechaLarga . ' </h4>
                    <b>Vendedor:</b> <br/>
                    ' . $vendedor . '   
                    </td>
                </tr>            
            </table>
        </td>
    </tr>
</table>';

$mpdf->SetHTMLHeader($htmlHeader);

$con = "SELECT
    mov_remisiones.cantidad
    , mov_remisiones.precio_unitario
    , cat_productos.clave
    , cat_productos.nombre
FROM
    mov_remisiones
    INNER JOIN cat_productos 
        ON (mov_remisiones.id_producto = cat_productos.id_producto)
        WHERE mov_remisiones.id_remision = " . $id;

$rs = $clsConsulta->consultaGeneral($con);

$html .= '<table id="table">
<thead>
<tr>
    <th>Cantidad</th>
    <th>Clave</th>
    <th>Nombre</th>
    <th>Precio</th>
    <th>Total</th>
</tr>
</thead>
<tbody>';

$total = 0;
if ($clsConsulta->numrows > 0) {
    foreach ($rs as $v => $val) {
        $html .= '<tr>';
        $html .= '<td class="borderNo">' . $val['cantidad'] . '</td>';
        $html .= '<td class="text-start borderNo">' . $val['clave'] . '</td>';
        $html .= '<td class="text-start borderNo">' . $val['nombre'] . '</td>';
        $html .= '<td class="text-end borderNo">$' . number_format($val['precio_unitario'], 2, ".", ",") . '</td>';
        $subtotal = $val['cantidad'] * $val['precio_unitario'];
        $total += $subtotal;
        $html .= '<td class="text-end borderNo">$' . number_format($subtotal, 2, ".", ",") . '</td>';
        $html .= '</tr>';
    }
}

$totalenletras = $clsConsulta->cantidadEnTexto($subtotal);
$html .= '</tbody>
</table>';

// AGREGAR EL FOOTER DIRECTAMENTE AL HTML PRINCIPAL
$html .= '
<div class="footer-remision">
    <div> 
        <table>
            <tr>
                <td width="50%"></td>
                <td class="text-end">
                <table>
                    <tr>
                        <td class="border-bottom text-start"> Subtotal:</td>
                        <td  class="border-bottom">$' . number_format($subtotal, 2, ".", ",") . '</td>
                    </tr>
                    <tr>
                        <td class="border-bottom text-start"> Total:</td>
                        <td class="border-bottom">$' . number_format($subtotal, 2, ".", ",") . '</td>
                    </tr>
                </table>
            </tr>
        </table>
    </div>
    <div>Total: $' . number_format($subtotal, 2, ".", ",") . '</div>
    <div>Debo y pagaré incondicionalmente por este pagaré a la orden: ' . $razonSocial . ' </div> 
    <div class="mt-3">en esta ciudad de: _________________________________________________   en donde se reclama el documento  </div> 
    <div class="mt-3"> la cantidad de $' . number_format($subtotal, 2, ".", ",") . ' (' . $totalenletras . ')</div>
</div>';

$mpdf->SetMargins(5, 5, 55, 45);
// ELIMINAR SetHTMLFooter - ya no se usa
$mpdf->WriteHTML($html);

ob_end_clean(); // Elimina salida previa
$mpdf->Output('remision-' . $id_remision . '.pdf', 'I');
exit;
