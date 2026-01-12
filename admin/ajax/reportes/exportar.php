<?php
// ajax/reportes/exportar.php
@ini_set('display_errors', 0);
@ini_set('html_errors', 0);

require_once __DIR__ . '/../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

// Composer (PhpSpreadsheet y mPDF)
$autoload = dirname(__DIR__, 3) . '/vendor/autoload.php';
if (!is_file($autoload)) {
    http_response_code(500);
    echo 'Falta vendor/autoload.php';
    exit;
}
require_once $autoload;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Mpdf\Mpdf;

/* ==========
   INPUTS
   ========== */

$rep  = trim($_GET['rep']  ?? $_POST['rep']  ?? '');   // tipo de reporte
$fmt  = strtolower(trim($_GET['fmt'] ?? $_POST['fmt'] ?? 'excel')); // excel | pdf
$fini = trim($_GET['f_ini'] ?? $_POST['f_ini'] ?? '');
$ffin = trim($_GET['f_fin'] ?? $_POST['f_fin'] ?? '');
$vendTxt = trim($_GET['vendedor'] ?? $_POST['vendedor'] ?? '');

// sanear fechas (solo dígitos y guiones)
$fini = preg_replace('/[^0-9\-]/', '', $fini);
$ffin = preg_replace('/[^0-9\-]/', '', $ffin);

/* ==========
   BUILDER: genera encabezados y filas según $rep
   Todos los SQL usan clsConsultas->aplicaQuery/consultaGeneral
   y excluyen canceladas / devoluciones / notas de crédito.
   ========== */
function buildData(Consultas $db, string $rep, string $fini, string $ffin, string $vendTxt = ''): array
{
    $escFini = $fini !== '' ? $db->escape($fini) : '';
    $escFfin = $ffin !== '' ? $db->escape($ffin) : '';
    $escVend = $vendTxt !== '' ? $db->escape($vendTxt) : '';

    $wRem = "IFNULL(r.estatus,'') <> 'cancelada' AND IFNULL(r.es_devolucion,0)=0 AND IFNULL(r.es_nota_credito,0)=0";
    if ($escFini !== '') $wRem .= " AND DATE(r.fecha) >= '{$escFini}'";
    if ($escFfin !== '') $wRem .= " AND DATE(r.fecha) <= '{$escFfin}'";

    $wCom = "IFNULL(c.estatus,'') <> 'cancelada'";
    if ($escFini !== '') $wCom .= " AND DATE(c.fecha) >= '{$escFini}'";
    if ($escFfin !== '') $wCom .= " AND DATE(c.fecha) <= '{$escFfin}'";

    switch ($rep) {
        /* ---------- VENTAS ---------- */
        case 'ventas_vendedor': {
                $sql = "
              SELECT 
                CONCAT_WS(' ', v.nombre, v.apellido1, v.apellido2) AS vendedor,
                COUNT(DISTINCT r.id) AS remisiones,
                SUM(m.cantidad) AS unidades,
                SUM(m.cantidad*m.precio) AS venta_sin_iva,
                IFNULL(v.comision,0) AS pct
              FROM cab_remisiones r
              JOIN mov_remisiones m ON m.id_remision=r.id
              LEFT JOIN cat_vendedores v ON v.id=r.id_vendedor
              WHERE {$wRem}
              GROUP BY v.id, vendedor, pct
              ORDER BY vendedor ASC";
                $rs = $db->consultaGeneral($sql);
                $rows = [];
                if ($rs) foreach ($rs as $i => $r) {
                    $venta = (float)$r['venta_sin_iva'];
                    $comis = $venta * ((float)$r['pct'] / 100);
                    $rows[] = [
                        $r['vendedor'] ?: 'SIN VENDEDOR',
                        (int)$r['remisiones'],
                        (float)$r['unidades'],
                        round($venta, 2),
                        round($comis, 2)
                    ];
                }
                return [
                    'title' => 'Ventas por Vendedor',
                    'headers' => ['Vendedor', '# Remisiones', 'Unidades', 'Venta (sin IVA)', 'Comisión'],
                    'rows' => $rows
                ];
            }
        case 'ventas_producto': {
                $sql = "
              SELECT p.clave, p.nombre,
                     SUM(m.cantidad) AS unidades,
                     SUM(m.cantidad*m.precio) AS venta
              FROM cab_remisiones r
              JOIN mov_remisiones m ON m.id_remision=r.id
              JOIN cat_productos p ON p.id_producto=m.id_producto
              WHERE {$wRem}
              GROUP BY p.id_producto, p.clave, p.nombre
              ORDER BY venta DESC";
                $rs = $db->consultaGeneral($sql);
                $rows = [];
                if ($rs) foreach ($rs as $r) {
                    $rows[] = [$r['clave'], $r['nombre'], (float)$r['unidades'], round((float)$r['venta'], 2)];
                }
                return [
                    'title' => 'Ventas por Producto',
                    'headers' => ['Clave', 'Producto', 'Unidades', 'Venta (sin IVA)'],
                    'rows' => $rows
                ];
            }
        case 'ventas_periodo': {
                $sql = "
              SELECT DATE(r.fecha) AS dia,
                     COUNT(DISTINCT r.id) AS remisiones,
                     SUM(m.cantidad) AS unidades,
                     SUM(m.cantidad*m.precio) AS venta
              FROM cab_remisiones r
              JOIN mov_remisiones m ON m.id_remision=r.id
              WHERE {$wRem}
              GROUP BY DATE(r.fecha)
              ORDER BY dia DESC";
                $rs = $db->consultaGeneral($sql);
                $rows = [];
                if ($rs) foreach ($rs as $r) {
                    $rows[] = [$r['dia'], (int)$r['remisiones'], (float)$r['unidades'], round((float)$r['venta'], 2)];
                }
                return [
                    'title' => 'Ventas Generales por Periodo',
                    'headers' => ['Fecha', '# Remisiones', 'Unidades', 'Venta (sin IVA)'],
                    'rows' => $rows
                ];
            }

            /* ---------- COMPRAS ---------- */
        case 'compras_proveedor': {
                $sql = "
              SELECT pr.razon_social AS proveedor,
                     COUNT(DISTINCT c.id) AS compras,
                     SUM(m.cantidad) AS unidades,
                     SUM(m.cantidad*m.precio) AS importe
              FROM cab_compras c
              JOIN mov_compras m ON m.id_orden_compra = c.id
              LEFT JOIN cat_proveedores pr ON pr.id = c.id_proveedor
              WHERE {$wCom}
              GROUP BY pr.id, pr.razon_social
              ORDER BY importe DESC";
                $rs = $db->consultaGeneral($sql);
                $rows = [];
                if ($rs) foreach ($rs as $r) {
                    $rows[] = [$r['proveedor'] ?: 'SIN PROVEEDOR', (int)$r['compras'], (float)$r['unidades'], round((float)$r['importe'], 2)];
                }
                return [
                    'title' => 'Compras por Proveedor',
                    'headers' => ['Proveedor', '# Compras', 'Unidades', 'Importe (sin IVA)'],
                    'rows' => $rows
                ];
            }
        case 'compras_producto': {
                $sql = "
              SELECT p.clave, p.nombre,
                     SUM(m.cantidad) AS unidades,
                     SUM(m.cantidad*m.precio) AS importe
              FROM cab_compras c
              JOIN mov_compras m ON m.id_orden_compra = c.id
              JOIN cat_productos p ON p.id_producto = m.id_producto
              WHERE {$wCom}
              GROUP BY p.id_producto, p.clave, p.nombre
              ORDER BY importe DESC";
                $rs = $db->consultaGeneral($sql);
                $rows = [];
                if ($rs) foreach ($rs as $r) {
                    $rows[] = [$r['clave'], $r['nombre'], (float)$r['unidades'], round((float)$r['importe'], 2)];
                }
                return [
                    'title' => 'Compras por Producto',
                    'headers' => ['Clave', 'Producto', 'Unidades', 'Importe (sin IVA)'],
                    'rows' => $rows
                ];
            }
        case 'compras_periodo': {
                $sql = "
              SELECT DATE(c.fecha) AS dia,
                     COUNT(DISTINCT c.id) AS compras,
                     SUM(m.cantidad) AS unidades,
                     SUM(m.cantidad*m.precio) AS importe
              FROM cab_compras c
              JOIN mov_compras m ON m.id_orden_compra=c.id
              WHERE {$wCom}
              GROUP BY DATE(c.fecha)
              ORDER BY dia DESC";
                $rs = $db->consultaGeneral($sql);
                $rows = [];
                if ($rs) foreach ($rs as $r) {
                    $rows[] = [$r['dia'], (int)$r['compras'], (float)$r['unidades'], round((float)$r['importe'], 2)];
                }
                return [
                    'title' => 'Compras Generales por Periodo',
                    'headers' => ['Fecha', '# Compras', 'Unidades', 'Importe (sin IVA)'],
                    'rows' => $rows
                ];
            }

            /* ---------- COMISIONES ---------- */
        case 'comisiones_general': {
                $sql = "
              SELECT CONCAT_WS(' ', v.nombre, v.apellido1, v.apellido2) AS vendedor,
                     SUM(m.cantidad*m.precio) AS venta,
                     IFNULL(v.comision,0) AS pct
              FROM cab_remisiones r
              JOIN mov_remisiones m ON m.id_remision=r.id
              LEFT JOIN cat_vendedores v ON v.id=r.id_vendedor
              WHERE {$wRem}
              GROUP BY v.id, vendedor, pct
              ORDER BY vendedor ASC";
                $rs = $db->consultaGeneral($sql);
                $rows = [];
                if ($rs) foreach ($rs as $r) {
                    $venta = (float)$r['venta'];
                    $pct = (float)$r['pct'];
                    $rows[] = [
                        $r['vendedor'] ?: 'SIN VENDEDOR',
                        round($venta, 2),
                        number_format($pct, 2, '.', '') . ' %',
                        round($venta * ($pct / 100), 2)
                    ];
                }
                return [
                    'title' => 'Comisiones (General por Vendedor)',
                    'headers' => ['Vendedor', 'Venta (sin IVA)', '% Comisión', 'Comisión'],
                    'rows' => $rows
                ];
            }
        case 'comisiones_vendedor': {
                if ($vendTxt !== '') $wRem .= " AND CONCAT_WS(' ',v.nombre,v.apellido1,v.apellido2) LIKE '%{$escVend}%'";
                $sql = "
              SELECT DATE(r.fecha) AS fecha,
                     CONCAT(r.serie,r.folio) AS folio,
                     c.razon_social AS cliente,
                     CONCAT_WS(' ', v.nombre, v.apellido1, v.apellido2) AS vendedor,
                     SUM(m.cantidad*m.precio) AS venta,
                     IFNULL(v.comision,0) AS pct
              FROM cab_remisiones r
              JOIN mov_remisiones m ON m.id_remision=r.id
              LEFT JOIN cat_clientes c ON c.id=r.id_cliente
              LEFT JOIN cat_vendedores v ON v.id=r.id_vendedor
              WHERE {$wRem}
              GROUP BY r.id, fecha, folio, cliente, vendedor, pct
              ORDER BY r.fecha DESC, r.id DESC";
                $rs = $db->consultaGeneral($sql);
                $rows = [];
                if ($rs) foreach ($rs as $r) {
                    $venta = (float)$r['venta'];
                    $pct = (float)$r['pct'];
                    $rows[] = [
                        $r['fecha'],
                        $r['folio'],
                        $r['cliente'] ?: '',
                        $r['vendedor'] ?: '',
                        round($venta, 2),
                        number_format($pct, 2, '.', '') . ' %',
                        round($venta * ($pct / 100), 2)
                    ];
                }
                return [
                    'title' => 'Comisiones por Vendedor (Detalle por Remisión)',
                    'headers' => ['Fecha', 'Folio', 'Cliente', 'Vendedor', 'Venta (sin IVA)', '% Comisión', 'Comisión'],
                    'rows' => $rows
                ];
            }

            /* ---------- UTILIDADES ---------- */
        case 'utilidades_producto': {
                $sql = "
              SELECT p.clave, p.nombre,
                     SUM(m.cantidad) AS unidades,
                     SUM(m.cantidad*m.precio) AS venta,
                     SUM(m.cantidad*COALESCE(p.costo_promedio,p.precio_compra)) AS costo,
                     AVG(IFNULL(v.comision,0)) AS pct
              FROM cab_remisiones r
              JOIN mov_remisiones m ON m.id_remision=r.id
              JOIN cat_productos p ON p.id_producto=m.id_producto
              LEFT JOIN cat_vendedores v ON v.id=r.id_vendedor
              WHERE {$wRem}
              GROUP BY p.id_producto, p.clave, p.nombre
              ORDER BY venta DESC";
                $rs = $db->consultaGeneral($sql);
                $rows = [];
                if ($rs) foreach ($rs as $r) {
                    $venta = (float)$r['venta'];
                    $costo = (float)$r['costo'];
                    $bruta = $venta - $costo;
                    $com = $venta * ((float)$r['pct'] / 100);
                    $neta = $bruta - $com;
                    $rows[] = [
                        $r['clave'],
                        $r['nombre'],
                        (float)$r['unidades'],
                        round($venta, 2),
                        round($costo, 2),
                        round($bruta, 2),
                        round($neta, 2)
                    ];
                }
                return [
                    'title' => 'Utilidades por Producto (sin IVA)',
                    'headers' => ['Clave', 'Producto', 'Unidades', 'Venta', 'Costo', 'Utilidad Bruta', 'Utilidad Neta'],
                    'rows' => $rows
                ];
            }
        case 'utilidades_periodo': {
                $sql = "
              SELECT DATE(r.fecha) AS dia,
                     SUM(m.cantidad*m.precio) AS venta,
                     SUM(m.cantidad*COALESCE(p.costo_promedio,p.precio_compra)) AS costo,
                     AVG(IFNULL(v.comision,0)) AS pct
              FROM cab_remisiones r
              JOIN mov_remisiones m ON m.id_remision=r.id
              JOIN cat_productos p ON p.id_producto=m.id_producto
              LEFT JOIN cat_vendedores v ON v.id=r.id_vendedor
              WHERE {$wRem}
              GROUP BY DATE(r.fecha)
              ORDER BY dia DESC";
                $rs = $db->consultaGeneral($sql);
                $rows = [];
                if ($rs) foreach ($rs as $r) {
                    $venta = (float)$r['venta'];
                    $costo = (float)$r['costo'];
                    $bruta = $venta - $costo;
                    $com = $venta * ((float)$r['pct'] / 100);
                    $neta = $bruta - $com;
                    $rows[] = [
                        $r['dia'],
                        round($venta, 2),
                        round($costo, 2),
                        round($bruta, 2),
                        round($com, 2),
                        round($neta, 2)
                    ];
                }
                return [
                    'title' => 'Utilidades por Periodo (sin IVA)',
                    'headers' => ['Fecha', 'Venta', 'Costo', 'Utilidad Bruta', 'Comisiones', 'Utilidad Neta'],
                    'rows' => $rows
                ];
            }
    }

    // Default
    return ['title' => 'Reporte', 'headers' => [], 'rows' => []];
}

/* ==========
   Obtener datos
   ========== */
$pack = buildData($clsConsulta, $rep, $fini, $ffin, $vendTxt);
$title   = $pack['title'];
$headers = $pack['headers'];
$rows    = $pack['rows'];

/* ==========
   Excel
   ========== */
if ($fmt === 'excel' || $fmt === 'xlsx' || $fmt === 'xls') {
    $xlsx = new Spreadsheet();
    $sheet = $xlsx->getActiveSheet();
    $sheet->setTitle(substr($title, 0, 31));

    $col = 1;
    foreach ($headers as $h) {
        $sheet->setCellValueByColumnAndRow($col, 1, $h);
        $col++;
    }
    $r = 2;
    foreach ($rows as $fila) {
        $c = 1;
        foreach ($fila as $val) {
            $sheet->setCellValueByColumnAndRow($c, $r, $val);
            $c++;
        }
        $r++;
    }
    // auto width
    for ($i = 1; $i <= count($headers); $i++) {
        $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
    }

    // salida
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . preg_replace('/\s+/', '_', strtolower($title)) . '.xlsx"');
    $writer = new Xlsx($xlsx);
    $writer->save('php://output');
    exit;
}

/* ==========
   PDF
   ========== */
$pdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4-L']);
$html = '<h3 style="margin:0 0 10px 0;">' . htmlspecialchars($title) . '</h3>';
$html .= '<table border="1" cellspacing="0" cellpadding="6" width="100%"><thead><tr>';
foreach ($headers as $h) $html .= '<th style="background:#f0f0f0;">' . htmlspecialchars($h) . '</th>';
$html .= '</tr></thead><tbody>';
foreach ($rows as $fila) {
    $html .= '<tr>';
    foreach ($fila as $v) {
        $html .= '<td>' . htmlspecialchars((string)$v) . '</td>';
    }
    $html .= '</tr>';
}
$html .= '</tbody></table>';
$pdf->WriteHTML($html);
$pdf->Output(preg_replace('/\s+/', '_', strtolower($title)) . '.pdf', 'I');
exit;
