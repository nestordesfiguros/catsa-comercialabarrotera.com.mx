<?php
include '../lib/clsConsultas.php';
$clsConsulta = new Consultas();

setlocale(LC_TIME, 'es_MX.UTF-8'); // Para español de México

// Incluir el autoloader de Composer
require '../vendor/autoload.php';
$id = $_GET['id'];
$idCliente = $_GET['idc'];

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
    table .text-center{
    text-align: center;
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
WHERE cat_clientes.id=".$idCliente;
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

// Establecer la localización en español de México
setlocale(LC_TIME, 'es_MX.UTF-8');

$con = "SELECT
    cab_pedidos.num_pedido
    , cab_pedidos.fecha
    , cab_pedidos.estatus
    , cab_pedidos.total
    , cab_pedidos.direccion_envio
    , -- Usamos un CASE para elegir entre vendedor o usuario
      CASE 
        WHEN cab_pedidos.id_vendedor > 0 THEN CONCAT(cat_vendedores.nombre, ' ', cat_vendedores.apellido1, ' ', cat_vendedores.apellido2)
        ELSE CONCAT(usuarios.nombre, ' ', usuarios.apellido1, ' ', usuarios.apellido2)
      END AS nombre_vendedor_usuario
    , -- Similar para el apellido
      CASE 
        WHEN cab_pedidos.id_vendedor > 0 THEN CONCAT(cat_vendedores.apellido1, ' ', cat_vendedores.apellido2)
        ELSE CONCAT(usuarios.apellido1, ' ', usuarios.apellido2)
      END AS apellido_vendedor_usuario
FROM
    cab_pedidos
    LEFT JOIN cat_vendedores 
        ON cab_pedidos.id_vendedor = cat_vendedores.id
    LEFT JOIN usuarios 
        ON cab_pedidos.id_usuario = usuarios.id
         WHERE cab_pedidos.id=".$id;
$res = $clsConsulta->consultaGeneral($con);
foreach ($res as $v => $val) {
    $fecha = $val['fecha'];
    $num_pedido = $val['num_pedido'];
    $estatus = $val['estatus'];
    $direccion_envio = $val['direccion_envio'];
    $vendedor = $val['nombre_vendedor_usuario'];    

    $date = new DateTime($fecha);

    // Crear un formateador de fechas con la configuración regional para México
    $fmt = new IntlDateFormatter(
        'es_MX', // Idioma y región
        IntlDateFormatter::LONG, // Formato largo
        IntlDateFormatter::NONE, // No es necesario formato de hora
        null, // Usar la configuración por defecto de la zona horaria
        IntlDateFormatter::GREGORIAN // Usar el calendario gregoriano
    );

    // Formatear la fecha en formato largo
    $fechaLarga = $fmt->format($date);
}

// Ajustar el margen superior para evitar la superposición
$mpdf->SetTopMargin(45);

$htmlHeader = '<table>
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
                    <h2> Pedido No. ' . $num_pedido . ' </h2> <br>
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

// Consulta para obtener los datos de la orden de compra
$con = "SELECT
    mov_pedidos.cantidad
    , mov_pedidos.precio
    , cat_productos.clave
    , cat_productos.nombre
FROM
    mov_pedidos
    INNER JOIN cat_productos 
        ON (mov_pedidos.id_producto = cat_productos.id_producto)
        WHERE mov_pedidos.id_pedido = " . $id;

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
$totalGlobal=0;
if ($clsConsulta->numrows > 0) {
    foreach ($rs as $v => $val) {
        $html .= '<tr>';
        $html .= '<td>' . $val['cantidad'] . '</td>';
        $html .= '<td class="text-start">' . $val['clave'] . '</td>';
        $html .= '<td class="text-start">' . $val['nombre'] . '</td>';
        $html .= '<td class="text-end">$' . number_format($val['precio'], 2, ".", ",") . '</td>';
        $total = $val['cantidad'] * $val['precio'];
        $html .= '<td class="text-end">$' . number_format($total, 2, ".", ",") . '</td>';
        $totalGlobal=$total+$totalGlobal;
        $html .= '</tr>';
    }
}

$html .= '</tbody>
</table>';
$html .= '<table>
        <tr class="borderNo">
            <td class="text-end" >Total <b>$'.number_format($totalGlobal, 2, ".", ",").'</b></td>
        </tr>
    </table>';

$html .= '<table>
        <tr class="borderNo">
            <td class="borderNo">Autorizado Por: _______________________________________</td>
            <td class="borderNo">Fecha Autorización: _________________________________</td>
        </tr>
    </table>';

// Escribir el HTML en el PDF
$mpdf->WriteHTML($html);

// Generar el archivo PDF (puedes cambiar 'documento.pdf' por el nombre que prefieras)
$mpdf->Output('documento.pdf', 'I'); // 'I' para mostrar el PDF en el navegador

// Si prefieres guardarlo en el servidor, usa 'D' para forzar la descarga:
// $mpdf->Output('documento.pdf', 'D');
?>