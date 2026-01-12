<?php
include '../lib/clsConsultas.php';
$clsConsulta = new Consultas();

setlocale(LC_TIME, 'es_MX.UTF-8'); // Para español de México

// Incluir el autoloader de Composer
require '../vendor/autoload.php';
$id = $_GET['id'];
$idp = $_GET['idp'];

// Crear una instancia de mPDF
$mpdf = new \Mpdf\Mpdf();

$mpdf = new \Mpdf\Mpdf([
    'default_font' => 'arial',  // Definir Arial como fuente por defecto
]);

// Leer el archivo HTML
$html = '';

// Estilos CSS para la tabla
$html .= '<style>
    body {
        font-family: Arial, sans-serif;      
        font-size: 8px;    
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        font-size: 8px;
    }
    table th, table td {
        padding: 8px 12px;
        text-align: center;
        border: 1px solid #ddd;
    }
    table th {
        background-color:#0e0e0e;
        color: #fff;
        font-weight: bold;
    }
    table td {
        font-size: 8px;
    }
    table .text-end {
        text-align: right;
    }
    table .text-start{
         text-align: left;
    }
    img{
        width:200px;
    }
    table .borderNo{
        border:none
    }
</style>';

$con = "SELECT
    estados.nombre AS nestado
    , municipios.nombre AS nmunicipio
    , cat_proveedores.razon_social
    , cat_proveedores.nombre_comercial
    , cat_proveedores.calle
    , cat_proveedores.num_ext
    , cat_proveedores.num_int
    , cat_proveedores.colonia
    , cat_proveedores.cp
FROM
    cat_proveedores
    INNER JOIN estados 
        ON (cat_proveedores.id_estado = estados.id)
    INNER JOIN municipios 
        ON (cat_proveedores.id_municipio = municipios.id) 
    WHERE cat_proveedores.id=" . $idp;
$res = $clsConsulta->consultaGeneral($con);
foreach ($res as $v => $val) {
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
        ON (empresa.id_municipio = municipios.id) WHERE empresa.id=" . $idp;
$res = $clsConsulta->consultaGeneral($query);
foreach ($res as $v => $val) {
    $razonSocial = $val['razon_social'];
    $calle = strtoupper($val['calle'] . ' ' . $val['num_ext'] . ' ' . $val['num_int']);
    $domicilio = strtoupper($val['cp'] . ' ' . $val['colonia'] . ' ' . $val['nmunicipio'] . ' ' . $val['nestado']);
    $tel1 = ($val['tel1'] != '') ? $val['tel1'] : '';
    // $tel2 = ($val['tel2'] != '') ? $val['tel2'] : '';
    // $tel3 = ($val['tel3'] != '') ? $val['tel3'] : '';
}

// Establecer la localización en español de México
setlocale(LC_TIME, 'es_MX.UTF-8');

$con = "SELECT * FROM cab_compras WHERE id=" . $id;
$res = $clsConsulta->consultaGeneral($con);
foreach ($res as $v => $val) {
    $fecha = $val['fecha'];
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

// Ajustar el margen superior para evitar la superposición
$mpdf->SetTopMargin(45);

$htmlHeader = '<table >
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
                    ' . $tel . ' ' . $tel2 . ' ' . $tel3 . '
                    </td>
                </tr>
            </table>
        </td>
        <td class="text-end borderNo">
            <table > 
                <tr class="borderNo"> 
                    <td class="borderNo text-end">
                    <h2> Órden de Compra No. ' . $id . ' </h2> <br>
                    <h4>' . $fechaLarga . ' </h4>
                    <br/>
                    ' . $razonSocialP . ' <br/>
                    ' . $calleP . ' <br>
                    ' . $domicilioP . ' <br>
                    ' . $telP . '
                    </td>
                </tr>            
            </table>
        </td>
    </tr>
</table>';

$mpdf->SetHTMLHeader($htmlHeader);

// Consulta para obtener los datos de la orden de compra
$con = "SELECT
    mov_compras.cantidad
    , mov_compras.precio
    , cat_productos.clave
    , cat_productos.nombre
FROM
    mov_compras
    INNER JOIN cat_productos 
        ON (mov_compras.id_producto = cat_productos.id_producto)
        WHERE mov_compras.id_orden_compra = " . $id;

$rs = $clsConsulta->consultaGeneral($con);

// Crear la tabla HTML con los datos obtenidos

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

if ($clsConsulta->numrows > 0) {
    foreach ($rs as $v => $val) {
        $html .= '<tr>';
        $html .= '<td>' . $val['cantidad'] . '</td>';
        $html .= '<td class="text-start">' . $val['clave'] . '</td>';
        $html .= '<td class="text-start">' . $val['nombre'] . '</td>';
        $html .= '<td class="text-end">$' . number_format($val['precio'], 2, ".", ",") . '</td>';
        $total = $val['cantidad'] * $val['precio'];
        $html .= '<td class="text-end">$' . number_format($total, 2, ".", ",") . '</td>';
        $html .= '</tr>';
    }
}

$html .= '</tbody>
</table>';

$html .= '<table>
        <tr class="borderNo">
            <td class="borderNo">Autorizado Por: _______________________________________</td>
            <td class="borderNo">Fecha Autorización: _________________________________</td>
        </tr>
    </table>';

// Establecer márgenes (izquierdo, derecho, superior)
$mpdf->SetMargins(5, 5, 50, 45);
// Escribir el HTML en el PDF
$mpdf->WriteHTML($html);

// Generar el archivo PDF (puedes cambiar 'documento.pdf' por el nombre que prefieras)
$mpdf->Output('documento.pdf', 'I'); // 'I' para mostrar el PDF en el navegador

// Si prefieres guardarlo en el servidor, usa 'D' para forzar la descarga:
// $mpdf->Output('documento.pdf', 'D');
