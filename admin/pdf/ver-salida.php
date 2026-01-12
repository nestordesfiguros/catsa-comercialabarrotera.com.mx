<?php
// pdf/ver-salida.php
session_start();

require_once __DIR__ . '/../lib/clsConsultas.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$clsConsulta = new Consultas();

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
$idSalida = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idEmpresa <= 0 || $idSalida <= 0) {
    http_response_code(400);
    echo "Solicitud inválida.";
    exit;
}

// Empresa
$emp = $clsConsulta->consultaGeneral("SELECT * FROM cat_empresas WHERE id = {$idEmpresa} LIMIT 1");
$empresa = ($clsConsulta->numrows > 0) ? $emp[1] : [];

// Cabecera salida
$sqlCab = "
    SELECT
        s.*,
        a.almacen,
        COALESCE(c.razon_social, s.destino_libre, '') AS destino
    FROM cab_salidas_almacen s
    INNER JOIN cat_almacenes a ON a.id = s.id_almacen
    LEFT JOIN cat_clientes c ON c.id = s.id_cliente
    WHERE s.id = {$idSalida}
      AND s.id_empresa = {$idEmpresa}
      AND a.id_empresa = {$idEmpresa}
    LIMIT 1
";
$cab = $clsConsulta->consultaGeneral($sqlCab);
if ($clsConsulta->numrows < 1) {
    http_response_code(404);
    echo "Salida no encontrada.";
    exit;
}
$cab = $cab[1];

// Detectar remisión ligada (si existe)
$idRemision = 0;
$rsRel = $clsConsulta->consultaGeneral("SHOW TABLES LIKE 'salida_remisiones'");
if ($clsConsulta->numrows > 0) {
    $r = $clsConsulta->consultaGeneral("SELECT id_remision FROM salida_remisiones WHERE id_salida = {$idSalida} LIMIT 1");
    if ($clsConsulta->numrows > 0) $idRemision = (int)($r[1]['id_remision'] ?? 0);
} else {
    $rsRel2 = $clsConsulta->consultaGeneral("SHOW TABLES LIKE 'rel_salidas_remisiones'");
    if ($clsConsulta->numrows > 0) {
        $r2 = $clsConsulta->consultaGeneral("SELECT id_remision FROM rel_salidas_remisiones WHERE id_salida = {$idSalida} LIMIT 1");
        if ($clsConsulta->numrows > 0) $idRemision = (int)($r2[1]['id_remision'] ?? 0);
    }
}

// ¿Existe precio_unitario en mov_salidas_almacen?
$hasPrecio = false;
$col = $clsConsulta->consultaGeneral("SHOW COLUMNS FROM mov_salidas_almacen LIKE 'precio_unitario'");
if ($clsConsulta->numrows > 0) $hasPrecio = true;

// Detalle salida
// - Siempre listamos productos desde mov_salidas_almacen
// - Precio: si mov_salidas tiene precio_unitario => usarlo
//          si no, y hay remisión ligada => tomar precio_unitario de mov_remisiones
if ($hasPrecio) {
    $sqlDet = "
        SELECT
            ms.id_producto,
            ms.cantidad,
            COALESCE(ms.precio_unitario, 0) AS precio_unitario,
            p.clave,
            p.nombre,
            p.unidad_medida
        FROM mov_salidas_almacen ms
        INNER JOIN cat_productos p ON p.id_producto = ms.id_producto
        WHERE ms.id_salida = {$idSalida}
          AND (p.id_empresa IS NULL OR p.id_empresa = {$idEmpresa})
        ORDER BY p.nombre ASC
    ";
} else {
    if ($idRemision > 0) {
        $sqlDet = "
            SELECT
                ms.id_producto,
                ms.cantidad,
                COALESCE(mr.precio_unitario, 0) AS precio_unitario,
                p.clave,
                p.nombre,
                p.unidad_medida
            FROM mov_salidas_almacen ms
            INNER JOIN cat_productos p ON p.id_producto = ms.id_producto
            LEFT JOIN mov_remisiones mr
                   ON mr.id_remision = {$idRemision} AND mr.id_producto = ms.id_producto
            WHERE ms.id_salida = {$idSalida}
              AND (p.id_empresa IS NULL OR p.id_empresa = {$idEmpresa})
            ORDER BY p.nombre ASC
        ";
    } else {
        $sqlDet = "
            SELECT
                ms.id_producto,
                ms.cantidad,
                0 AS precio_unitario,
                p.clave,
                p.nombre,
                p.unidad_medida
            FROM mov_salidas_almacen ms
            INNER JOIN cat_productos p ON p.id_producto = ms.id_producto
            WHERE ms.id_salida = {$idSalida}
              AND (p.id_empresa IS NULL OR p.id_empresa = {$idEmpresa})
            ORDER BY p.nombre ASC
        ";
    }
}

$det = $clsConsulta->consultaGeneral($sqlDet);
if (!is_array($det) || $clsConsulta->numrows < 1) $det = [];

$nombreEmpresa = htmlspecialchars($empresa['razon_social'] ?? $empresa['nombre_comercial'] ?? 'Empresa');
$rfcEmpresa = htmlspecialchars($empresa['rfc'] ?? '');
$dirEmpresa = htmlspecialchars(
    trim(
        ($empresa['calle'] ?? '') . ' ' .
            ($empresa['num_ext'] ?? '') . ' ' .
            ($empresa['num_int'] ?? '') . ' ' .
            ($empresa['colonia'] ?? '') . ' CP ' . ($empresa['cp'] ?? '')
    )
);

$folio = (int)$cab['id'];
$fecha = htmlspecialchars($cab['fecha']);
$almacen = htmlspecialchars($cab['almacen']);
$destino = htmlspecialchars($cab['destino']);
$estatus = htmlspecialchars($cab['estatus']);
$tipo = htmlspecialchars($cab['tipo_salida']);
$ref = htmlspecialchars($cab['referencia'] ?? '');
$obs = htmlspecialchars($cab['observaciones'] ?? '');

$totalPzas = 0;
$totalImporte = 0;
$mostrarPrecios = false;

foreach ($det as $r) {
    $totalPzas += (float)($r['cantidad'] ?? 0);
    if ((float)($r['precio_unitario'] ?? 0) > 0) $mostrarPrecios = true;
}

ob_start();
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        .h1 {
            font-size: 18px;
            margin: 0;
        }

        .muted {
            color: #555;
        }

        .box {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
        }

        th {
            background: #f3f3f3;
            text-align: center;
        }

        td.right {
            text-align: right;
        }

        td.center {
            text-align: center;
        }

        .no-border td {
            border: none;
            padding: 2px 0;
        }
    </style>
</head>

<body>

    <div class="box">
        <table class="no-border">
            <tr>
                <td>
                    <div class="h1">SALIDA DE ALMACÉN</div>
                    <div class="muted"><?= $nombreEmpresa ?></div>
                    <?php if ($rfcEmpresa): ?><div class="muted">RFC: <?= $rfcEmpresa ?></div><?php endif; ?>
                    <?php if ($dirEmpresa): ?><div class="muted"><?= $dirEmpresa ?></div><?php endif; ?>
                </td>
                <td class="right">
                    <div><b>Folio:</b> <?= $folio ?></div>
                    <div><b>Fecha:</b> <?= $fecha ?></div>
                    <div><b>Estatus:</b> <?= strtoupper($estatus) ?></div>
                </td>
            </tr>
        </table>
    </div>

    <div class="box">
        <table class="no-border">
            <tr>
                <td><b>Almacén:</b> <?= $almacen ?></td>
            </tr>
            <tr>
                <td><b>Destino:</b> <?= $destino ?></td>
            </tr>
            <tr>
                <td><b>Tipo:</b> <?= $tipo ?></td>
            </tr>
            <?php if ($ref): ?><tr>
                    <td><b>Referencia:</b> <?= $ref ?></td>
                </tr><?php endif; ?>
            <?php if ($idRemision > 0): ?><tr>
                    <td><b>Remisión ligada:</b> #<?= (int)$idRemision ?></td>
                </tr><?php endif; ?>
            <?php if ($obs): ?><tr>
                    <td><b>Observaciones:</b> <?= $obs ?></td>
                </tr><?php endif; ?>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:90px;">Cantidad</th>
                <th style="width:110px;">Clave</th>
                <th>Producto</th>
                <th style="width:80px;">Unidad</th>
                <?php if ($mostrarPrecios): ?>
                    <th style="width:90px;">Precio</th>
                    <th style="width:110px;">Importe</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($det as $r):
                $qty = (float)($r['cantidad'] ?? 0);
                $pu = (float)($r['precio_unitario'] ?? 0);
                $imp = $qty * $pu;
                $totalImporte += $imp;
            ?>
                <tr>
                    <td class="right"><?= number_format($qty, 2, '.', ',') ?></td>
                    <td class="center"><?= htmlspecialchars($r['clave'] ?? '') ?></td>
                    <td><?= htmlspecialchars($r['nombre'] ?? '') ?></td>
                    <td class="center"><?= htmlspecialchars($r['unidad_medida'] ?? 'PZA') ?></td>
                    <?php if ($mostrarPrecios): ?>
                        <td class="right">$<?= number_format($pu, 2, '.', ',') ?></td>
                        <td class="right">$<?= number_format($imp, 2, '.', ',') ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="<?= $mostrarPrecios ? 4 : 4 ?>" class="right">TOTAL PIEZAS</th>
                <th class="right"><?= number_format($totalPzas, 2, '.', ',') ?></th>
                <?php if ($mostrarPrecios): ?>
                    <th class="right">$<?= number_format($totalImporte, 2, '.', ',') ?></th>
                <?php endif; ?>
            </tr>
        </tfoot>
    </table>

    <br><br>
    <table class="no-border">
        <tr>
            <td class="center" style="width:50%;">
                ___________________________<br>Entregó
            </td>
            <td class="center" style="width:50%;">
                ___________________________<br>Recibió
            </td>
        </tr>
    </table>

</body>

</html>
<?php
$html = ob_get_clean();

$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('letter', 'portrait');
$dompdf->render();

$filename = "salida_{$folio}.pdf";
header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=\"{$filename}\"");
echo $dompdf->output();
exit;
