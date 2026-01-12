<?php
// pdf/pdf-remisiones_.php
include '../lib/clsConsultas.php';
$clsConsulta = new Consultas();

setlocale(LC_TIME, 'es_MX.UTF-8'); // Para español de México

// Incluir el autoloader de Composer
require '../vendor/autoload.php';
$id = $_GET['id'];
$idCliente = $_GET['idc'];

// Crear una instancia de mPDF
//$mpdf = new \Mpdf\Mpdf();

$mpdf = new \Mpdf\Mpdf();

// Leer el archivo HTML
$html = '';

// Estilos CSS para la tabla
$html .= '<style>
    body {
        font-family: Arial, sans-serif;      
        font-size: 10px;    
    }
    .footer {
        font-family: Arial, sans-serif;      
  
    }
    table {
        width: 100%;
        border-collapse: separate;  /* Cambiar de collapse a separate */
        border-spacing: 0; /* Eliminar el espacio entre celdas */
        margin-top: 20px;
    }
    table th {
        text-align: center;
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
    .text-end {
        text-align: right;
    }
    .text-start{
        text-align: left;
    }
    .text-center{
        text-align: center;
    }
    .mt-0 {
        margin-top: 0px;
    }
    .mt-1 {
        margin-top: 1px;
    }
    .mt-2 {
        margin-top: 2px;
    }
    .mt-3 {
        margin-top: 10px;
    }
    .mt-5 {
        margin-top: 20px;
    }
    .mb-0 {
        margin-bottom: 0px;
    }
    .top-align {
        vertical-align: top;
    }
    .border-top {
        border-top: 1px solid black; 
    }
    .border-bottom {
        border-bottom: 1px solid black; 
    }

    th {
        border-top: 1px solid black; /* Borde superior en las celdas <th> */
        border-bottom: 1px solid black; /* Borde inferior en las celdas <th> */
    }
    .bottom-text {
        position: fixed;
        bottom: 10mm; /* Ajusta la distancia desde el borde inferior de la página */
        left: 10mm;   /* Ajusta la distancia desde el borde izquierdo de la página */
        width: 100%;
        text-align: left;
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
    $razonSocialCliente = $val['razon_social'];
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
    cab_remisiones.id AS id_remision
    , cab_remisiones.fecha
    , cab_remisiones.estatus
    , cab_remisiones.total
    , cab_remisiones.direccion_envio
    , -- Usamos un CASE para elegir entre vendedor o usuario
      CASE 
        WHEN cab_remisiones.id_vendedor > 0 THEN CONCAT(cat_vendedores.nombre, ' ', cat_vendedores.apellido1, ' ', cat_vendedores.apellido2)
        ELSE CONCAT(usuarios.nombre, ' ', usuarios.apellido1, ' ', usuarios.apellido2)
      END AS nombre_vendedor_usuario
    , -- Similar para el apellido
      CASE 
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



$htmlHeader .= '<h1 class="text-center mb-0">H TORRES</h1> <br><br>';
$htmlHeader .= '<table class="mt-0 top-align" >
    <tr class="borderNo">
        <td class="borderNo">
            <div><img src="../img/logo-inicio.png" /></div>
            <div style="margin-top:20px;"> <br>Facturado a: </div>
            <div>Enviar a ' . $direccion_envio . ' <br>' . $telP . '</div>
            <div>Vendedor:' . $vendedor . ' </div>
        </td>
        <td class="text-start borderNo">
            <div>Domicilio fiscal </div>
            <div>R.F.C. </div>
            <div> Solicitado por: ' . $razonSocialCliente . '  </div>   
        </td>
        <td class="text-end borderNo">
            <table > 
                <tr class="borderNo"> 
                    <td class="borderNo text-end">
                    <div> Serie. STAND  </div>
                    <div> Folio. ' . $id_remision . ' </div>
                    <div> Aprob. folios 1 </div>
                    <div> Rango folios 1 al  </div>
                    <div>' . $fechaLarga . ' </div>  
                    <div> Forma de pago: Pago en una sola exhibición  </div>
                    <div> Método de pago y Cuenta: No Identificado  </div>
                    <div> Régimen fiscal: xxxxxxxxxx </div>
                    </td>
                </tr>            
            </table>
        </td>
    </tr>
</table>';



// Consulta para obtener los datos de la remisión
$con = "SELECT
    mov_remisiones.cantidad
    , mov_remisiones.precio
    , cat_productos.clave
    , cat_productos.nombre
    , unidades_de_medida.nombre_unidad
FROM
    mov_remisiones
    INNER JOIN cat_productos 
        ON (mov_remisiones.id_producto = cat_productos.id_producto)
    INNER JOIN unidades_de_medida
    ON (cat_productos.unidad_medida = unidades_de_medida.id_unidad)
        WHERE mov_remisiones.id_remision = " . $id;

$rs = $clsConsulta->consultaGeneral($con);

// Crear la tabla HTML con los datos obtenidos

$html .= '<table id="table" class="mt-5" >
<thead>
<tr class="border-top border-bottom">
    <th>Cantidad</th>
    <th>Unidad</th>
    <th>Clave</th>
    <th>Descripción</th>
    <th>P/U</th>
    <th>Importe</th>
</tr>
</thead>
<tbody>';

$total = 0;

if ($clsConsulta->numrows > 0) {
    foreach ($rs as $v => $val) {
        $html .= '<tr>';
        $html .= '<td>' . $val['cantidad'] . '</td>';
        $html .= '<td class="text-start">' . $val['nombre_unidad'] . '</td>';
        $html .= '<td class="text-start">' . $val['clave'] . '</td>';
        $html .= '<td class="text-start">' . $val['nombre'] . '</td>';
        $html .= '<td class="text-end">$' . number_format($val['precio'], 2, ".", ",") . '</td>';
        $subtotal = $val['cantidad'] * $val['precio'];
        $total = $total + $subtotal;
        $html .= '<td class="text-end">$' . number_format($subtotal, 2, ".", ",") . '</td>';
        $html .= '</tr>';
    }
}


$html .= '</tbody>
</table>';



$htmlFooter = '
    <hr>
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
    <div>Total:$' . number_format($total, 2, ".", ",") . '</div>
    <div>Debo y pagaré incondicionalemnte por este pagaré a la órden: ' . $razonSocialCliente . ' </div> 
    <div class="mt-3">en esta ciudad de: _________________________________________________   en donde se reclama el documento  </div> 
    <div class="mt-3"> la cantidad de $' . number_format($total, 2, ".", ",") . ' (' . $clsConsulta->cantidadEnTexto($total) . ')</div>';

// Ajustar el margen superior para evitar la superposición
// Establecer márgenes (izquierdo, derecho, superior)
$mpdf->SetMargins(5, 5, 55, 45);

$mpdf->SetHTMLHeader($htmlHeader);
// Configuración del pie de página en mPDF
$mpdf->SetHTMLFooter($htmlFooter, 'O');


// Escribir el HTML en el PDF
$mpdf->WriteHTML($html);



// Generar el archivo PDF (puedes cambiar 'documento.pdf' por el nombre que prefieras)
$mpdf->Output('documento.pdf', 'I'); // 'I' para mostrar el PDF en el navegador

// Si prefieres guardarlo en el servidor, usa 'D' para forzar la descarga:
// $mpdf->Output('documento.pdf', 'D');
