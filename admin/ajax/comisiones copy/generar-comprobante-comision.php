<?php
require_once '../../vendor/autoload.php'; // mPDF
require_once '../../lib/clsConsultas.php';
$cls = new Consultas();

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    die('ID inválido.');
}

// Obtener el corte pagado por ID
$sqlPago = "SELECT * FROM comisiones WHERE id = $id AND estatus = 'pagado' LIMIT 1";
$registro = $cls->consultaGeneral($sqlPago)[0] ?? null;

if (!$registro) {
    die('No se encontró un corte pagado con ese ID.');
}

// Extraer datos del corte
$id_vendedor  = intval($registro['id_vendedor']);
$fecha_inicio = $registro['fecha_inicio'];
$fecha_fin    = $registro['fecha_fin'];
$fecha_pago   = date('d/m/Y H:i', strtotime($registro['fecha_pago']));

// Datos del vendedor
$sqlVendedor = "SELECT CONCAT(nombre, ' ', apellido1, ' ', IFNULL(apellido2, '')) AS nombre, comision 
                FROM cat_vendedores 
                WHERE id = $id_vendedor LIMIT 1";
$vendedor = $cls->consultaGeneral($sqlVendedor)[0];

// Calcular ventas y comisión
$sqlVentas = "
    SELECT SUM(m.cantidad * m.precio) AS total_ventas
    FROM cab_remisiones r
    INNER JOIN mov_remisiones m ON r.id = m.id_remision
    WHERE r.id_vendedor = $id_vendedor
      AND r.estatus = 'procesada'
      AND DATE(r.fecha) BETWEEN '$fecha_inicio' AND '$fecha_fin'
      AND (
        r.tipo_venta = 'contado'
        OR (
            r.tipo_venta = 'credito'
            AND EXISTS (
                SELECT 1 FROM cab_cxc c 
                WHERE c.id_remision = r.id AND c.estatus = 'Pagada'
            )
        )
    )
";
$ventas = $cls->consultaGeneral($sqlVentas)[0];
$total_ventas = floatval($ventas['total_ventas'] ?? 0);
$porcentaje = floatval($vendedor['comision']);
$total_comision = $total_ventas * $porcentaje / 100;

// Generar PDF
$mpdf = new \Mpdf\Mpdf(['format' => 'Letter']);

$html = '
<style>
body { font-family: Arial, sans-serif; font-size: 12px; }
h2 { text-align: center; }
table { width: 100%; border-collapse: collapse; margin-top: 15px; }
th, td { padding: 8px; border: 1px solid #ccc; }
.text-right { text-align: right; }
</style>

<h2>Comprobante de Comisión Pagada</h2>
<table>
    <tr><th>Vendedor</th><td>' . htmlspecialchars($vendedor['nombre']) . '</td></tr>
    <tr><th>Periodo</th><td>' . $fecha_inicio . ' al ' . $fecha_fin . '</td></tr>
    <tr><th>Fecha de Pago</th><td>' . $fecha_pago . '</td></tr>
    <tr><th>% Comisión</th><td>' . number_format($porcentaje, 2) . '%</td></tr>
</table>

<table style="margin-top:20px">
    <thead>
        <tr>
            <th class="text-right">Total Ventas</th>
            <th class="text-right">Comisión Generada</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="text-right">$' . number_format($total_ventas, 2) . '</td>
            <td class="text-right">$' . number_format($total_comision, 2) . '</td>
        </tr>
    </tbody>
</table>

<p style="margin-top:40px;">Firma del Responsable:</p>
<div style="border-bottom: 1px solid #000; width: 250px; margin-top: 40px;"></div>
';

$mpdf->WriteHTML($html);
$mpdf->Output('comprobante_comision.pdf', 'I');
