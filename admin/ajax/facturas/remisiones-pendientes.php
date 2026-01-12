<?php
// ajax/facturas/remisiones-pendientes.php
header('Content-Type: application/json; charset=utf-8');

// Evita que warnings/notice rompan el JSON
@ini_set('display_errors', 0);
@ini_set('html_errors', 0);

require_once __DIR__ . '/../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

try {
    $sql = "
        SELECT r.id, r.fecha, r.total, c.razon_social AS cliente
        FROM cab_remisiones r
        JOIN cat_clientes c ON c.id = r.id_cliente
        WHERE r.id_factura IS NULL
        ORDER BY r.fecha DESC
        ";

    $rs = $clsConsulta->consultaGeneral($sql);

    // Asegurar arreglo
    if (!is_array($rs) || empty($rs)) {
        echo json_encode(['success' => true, 'items' => []], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $items = [];
    foreach ($rs as $row) {
        // OJO: ajusta 'folio' si tienes serie/folio en la tabla
        $items[] = [
            'id'      => (int)$row['id'],
            'folio'   => 'REM-' . $row['id'],
            'cliente' => (string)$row['cliente'],
            'fecha'   => (string)$row['fecha'],
            'total'   => (float)$row['total'],
        ];
    }

    echo json_encode(['success' => true, 'items' => $items], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    // Respuesta consistente en caso de excepción
    echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
}
