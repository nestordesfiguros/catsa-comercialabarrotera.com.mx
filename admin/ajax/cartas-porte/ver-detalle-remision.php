<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'html' => 'ID inválido']);
    exit;
}

// 1. Obtener cabecera de la remisión
$sqlCab = "
    SELECT 
        CONCAT('R-', LPAD(r.id, 6, '0')) AS folio,
        c.razon_social AS cliente,
        DATE(r.fecha) AS fecha,
        IFNULL(SUM(m.cantidad * m.precio), 0) AS total
    FROM cab_remisiones r
    INNER JOIN cat_clientes c ON r.id_cliente = c.id
    LEFT JOIN mov_remisiones m ON m.id_remision = r.id
    WHERE r.id = $id
    GROUP BY r.id
";

$cab = $clsConsulta->consultaGeneral($sqlCab);
if ($clsConsulta->numrows <= 0) {
    echo json_encode(['success' => false, 'html' => 'No se encontró la remisión']);
    exit;
}

$c = $cab[1];
$cabecera = "Folio: {$c['folio']} | Cliente: {$c['cliente']} | Fecha: {$c['fecha']} | Total: $" . number_format($c['total'], 2);

// 2. Obtener productos
$sqlDet = "
    SELECT 
        p.nombre AS producto,
        m.cantidad,
        m.precio,
        (m.cantidad * m.precio) AS total
    FROM mov_remisiones m
    INNER JOIN cat_productos p ON p.id_producto = m.id_producto
    WHERE m.id_remision = $id
";

$det = $clsConsulta->consultaGeneral($sqlDet);

$rows = '';
if ($clsConsulta->numrows > 0) {
    foreach ($det as $row) {
        $rows .= "
            <tr>
                <td>{$row['producto']}</td>
                <td class='text-center'>{$row['cantidad']}</td>
                <td class='text-end'>$" . number_format($row['precio'], 2) . "</td>
                <td class='text-end'>$" . number_format($row['total'], 2) . "</td>
            </tr>
        ";
    }
} else {
    $rows = "<tr><td colspan='4' class='text-center'>Sin productos</td></tr>";
}

echo json_encode([
    'success' => true,
    'cabecera' => $cabecera,
    'html' => $rows
]);
