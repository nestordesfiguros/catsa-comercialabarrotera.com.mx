<?php
// ajax/facturas/cargar-factura.php
header('Content-Type: application/json; charset=utf-8');
@ini_set('display_errors', 0);
@ini_set('html_errors', 0);

require_once __DIR__ . '/../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    echo json_encode(['success' => false, 'msg' => 'ID inválido']);
    exit;
}

// Cabecera (usa cab_facturas)
$sqlCab = "
  SELECT f.id, f.id_receptor, f.serie, f.folio, DATE(f.fecha) AS fecha,
         f.forma_pago, f.metodo_pago, f.uso_cfdi, f.uuid, IFNULL(f.timbrada,0) AS timbrada,
         c.razon_social
  FROM cab_facturas f
  JOIN cat_clientes c ON c.id = f.id_receptor
  WHERE f.id = {$id}
  LIMIT 1
";
$cab = $clsConsulta->consultaGeneral($sqlCab);
if (!is_array($cab) || $clsConsulta->numrows <= 0) {
    echo json_encode(['success' => false, 'msg' => 'Factura no encontrada']);
    exit;
}
$C = $cab[1];

// Detalle
$sqlDet = "
  SELECT id, id_producto, clave, cantidad, descripcion, precio, importe
  FROM mov_facturas
  WHERE id_factura = {$id} AND IFNULL(activo,1)=1
  ORDER BY id ASC
";
$det = $clsConsulta->consultaGeneral($sqlDet);
$items = [];
if (is_array($det) && $clsConsulta->numrows > 0) {
    foreach ($det as $i => $row) {
        $items[] = [
            'id'          => (int)$row['id'],
            'id_producto' => $row['id_producto'],
            'clave'       => (string)($row['clave'] ?? ''),
            'cantidad'    => (float)($row['cantidad'] ?? 0),
            'descripcion' => (string)($row['descripcion'] ?? ''),
            'precio'      => (float)($row['precio'] ?? 0),
            'importe'     => (float)($row['importe'] ?? 0),
        ];
    }
}

echo json_encode([
    'success' => true,
    'cab' => [
        'id'            => (int)$C['id'],
        'id_receptor'   => (int)$C['id_receptor'],
        'razon_social'  => (string)$C['razon_social'],
        'serie'         => (string)$C['serie'],
        'folio'         => (string)$C['folio'],
        'fecha'         => (string)$C['fecha'],
        'forma_pago'    => (int)($C['forma_pago'] ?? 0),   // id (INT)
        'metodo_pago'   => (string)($C['metodo_pago'] ?? ''), // código SAT
        'uso_cfdi'      => (string)($C['uso_cfdi'] ?? ''),
        'uuid'          => (string)($C['uuid'] ?? ''),
        'timbrada'      => (int)($C['timbrada'] ?? 0),
    ],
    'items' => $items
], JSON_UNESCAPED_UNICODE);
exit;
