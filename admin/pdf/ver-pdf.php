<?php
// admin/pdf/ver-pdf.php

ob_start(); // evita que warnings/espacios rompan la salida del PDF

session_start();
include '../lib/clsConsultas.php';
$clsConsulta = new Consultas();

// Composer autoload
require '../vendor/autoload.php';

// IDs (evitar strings)
$idOrden = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$idProveedor = isset($_GET['idp']) ? (int)$_GET['idp'] : 0;

// Empresa desde sesión (multiempresa)
$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;

if ($idOrden <= 0 || $idProveedor <= 0 || $idEmpresa <= 0) {
    ob_end_clean();
    exit('error');
}

// mPDF (solo una instancia)
$mpdf = new \Mpdf\Mpdf([
    'default_font' => 'arial'
]);

// Estilos
$html = '';
$html .= '<style>
    body { font-family: Arial, sans-serif; font-size: 8px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 8px; }
    table th, table td { padding: 8px 12px; text-align: center; border: 1px solid #ddd; }
    table th { background-color:#0e0e0e; color: #fff; font-weight: bold; }
    table td { font-size: 8px; }
    .text-end { text-align: right; }
    .text-start { text-align: left; }
    img { width:200px; }
    .borderNo { border:none; }
</style>';

// ==============================
// PROVEEDOR
// ==============================
$razonSocialP = '';
$calleP = '';
$domicilioP = '';
$telP = '';

$sqlProv = "
    SELECT
        e.nombre AS nestado,
        m.nombre AS nmunicipio,
        p.razon_social,
        p.nombre_Comercial,
        p.calle,
        p.num_ext,
        p.num_int,
        p.colonia,
        p.cp,
        p.tel AS telP
    FROM cat_proveedores p
    INNER JOIN estados e ON (p.id_estado = e.id)
    INNER JOIN municipios m ON (p.id_municipio = m.id)
    WHERE p.id={$idProveedor}
    LIMIT 1
";
$resProv = $clsConsulta->consultaGeneral($sqlProv);
if ($clsConsulta->numrows > 0) {
    $val = $resProv[1];
    $ncomercial = ($val['nombre_Comercial'] != '') ? $val['nombre_Comercial'] : '';
    $rsocialP = ($val['razon_social'] != '') ? ' <br> ( ' . $val['razon_social'] . ' )' : '';
    $razonSocialP = $ncomercial . $rsocialP;
    $calleP = strtoupper(trim($val['calle'] . ' ' . $val['num_ext'] . ' ' . $val['num_int']));
    $domicilioP = strtoupper(trim($val['cp'] . ' ' . $val['colonia'] . ' ' . $val['nmunicipio'] . ' ' . $val['nestado']));
    $telP = ($val['telP'] != '') ? $val['telP'] : '';
}

// ==============================
// EMPRESA
// ==============================
$razonSocial = '';
$calle = '';
$domicilio = '';
$tel1 = '';
$tel2 = '';
$tel3 = '';

$sqlEmp = "
    SELECT
        emp.id,
        emp.razon_social,
        emp.calle,
        emp.num_ext,
        emp.num_int,
        emp.cp,
        emp.colonia,
        emp.tel1,
        emp.tel2,
        emp.tel3,
        e.nombre AS nestado,
        m.nombre AS nmunicipio
    FROM empresa emp
    INNER JOIN estados e ON (emp.id_estado = e.id)
    INNER JOIN municipios m ON (emp.id_municipio = m.id)
    WHERE emp.id={$idEmpresa}
    LIMIT 1
";
$resEmp = $clsConsulta->consultaGeneral($sqlEmp);
if ($clsConsulta->numrows > 0) {
    $val = $resEmp[1];
    $razonSocial = $val['razon_social'];
    $calle = strtoupper(trim($val['calle'] . ' ' . $val['num_ext'] . ' ' . $val['num_int']));
    $domicilio = strtoupper(trim($val['cp'] . ' ' . $val['colonia'] . ' ' . $val['nmunicipio'] . ' ' . $val['nestado']));
    $tel1 = ($val['tel1'] != '') ? $val['tel1'] : '';
    $tel2 = ($val['tel2'] != '') ? $val['tel2'] : '';
    $tel3 = ($val['tel3'] != '') ? $val['tel3'] : '';
}

// ==============================
// CABECERA ORDEN (fecha)
// ==============================
$fechaLarga = '';
$sqlCab = "SELECT fecha FROM cab_compras WHERE id={$idOrden} AND id_empresa={$idEmpresa} LIMIT 1";
$resCab = $clsConsulta->consultaGeneral($sqlCab);
if ($clsConsulta->numrows > 0) {
    $fecha = $resCab[1]['fecha'];
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

// Margen superior para header
$mpdf->SetTopMargin(45);

$htmlHeader = '
<table>
    <tr class="borderNo">
        <td class="borderNo">
            <table>
                <tr class="borderNo">
                    <td class="text-start borderNo"><img src="../img/logo-inicio.png" /></td>
                </tr>
                <tr class="borderNo">
                    <td class="text-start borderNo">
                        ' . $razonSocial . ' <br/>
                        ' . $calle . ' <br/>
                        ' . $domicilio . ' <br/>
                        ' . $tel1 . ' ' . $tel2 . ' ' . $tel3 . '
                    </td>
                </tr>
            </table>
        </td>

        <td class="text-end borderNo">
            <table>
                <tr class="borderNo">
                    <td class="borderNo text-end">
                        <h2>Órden de Compra No. ' . $idOrden . '</h2>
                        <h4>' . $fechaLarga . '</h4>
                        <br/>
                        ' . $razonSocialP . ' <br/>
                        ' . $calleP . ' <br/>
                        ' . $domicilioP . ' <br/>
                        ' . $telP . '
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>';

$mpdf->SetHTMLHeader($htmlHeader);

// ==============================
// DETALLE
// ==============================
$sqlDet = "
    SELECT
        m.cantidad,
        m.precio,
        p.clave,
        p.nombre
    FROM mov_compras m
    INNER JOIN cat_productos p ON (m.id_producto = p.id_producto)
    WHERE m.id_orden_compra={$idOrden}
";
$rs = $clsConsulta->consultaGeneral($sqlDet);

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
        $cantidad = (float)$val['cantidad'];
        $precio = (float)$val['precio'];
        $total = $cantidad * $precio;

        $html .= '<tr>';
        $html .= '<td>' . $val['cantidad'] . '</td>';
        $html .= '<td class="text-start">' . $val['clave'] . '</td>';
        $html .= '<td class="text-start">' . $val['nombre'] . '</td>';
        $html .= '<td class="text-end">$' . number_format($precio, 2, ".", ",") . '</td>';
        $html .= '<td class="text-end">$' . number_format($total, 2, ".", ",") . '</td>';
        $html .= '</tr>';
    }
}

$html .= '</tbody></table>';

$html .= '<table>
    <tr class="borderNo">
        <td class="borderNo">Autorizado Por: _______________________________________</td>
        <td class="borderNo">Fecha Autorización: _________________________________</td>
    </tr>
</table>';

// Márgenes
$mpdf->SetMargins(5, 5, 50, 45);

// Limpiar buffer por seguridad antes de enviar PDF
ob_end_clean();

// Render PDF
$mpdf->WriteHTML($html);
$mpdf->Output('documento.pdf', 'I');
