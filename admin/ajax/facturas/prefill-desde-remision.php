<?php
header('Content-Type: application/json; charset=utf-8');
@ini_set('display_errors', 0);
@ini_set('html_errors', 0);

require_once __DIR__ . '/../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'msg' => 'Falta id de remisión']);
    exit;
}

// ⬇️  OJO: ya NO validamos estatus; usamos el vínculo id_factura (opción A)
$sqlRem = "
  SELECT r.*, c.razon_social
  FROM cab_remisiones r
  JOIN cat_clientes c ON c.id = r.id_cliente
  WHERE r.id = {$id} AND r.id_factura IS NULL
  LIMIT 1
";
$r = $clsConsulta->consultaGeneral($sqlRem);
if ($clsConsulta->numrows <= 0) {
    echo json_encode(['success' => false, 'msg' => 'Remisión no disponible']);
    exit;
}
$rem = $r[1];

// Detalle de remisión
$det = $clsConsulta->consultaGeneral("SELECT * FROM mov_remisiones WHERE id_remision = {$id}");
$items = [];
if (is_array($det)) {
    foreach ($det as $d) {
        $idProd = (int)$d['id_producto'];

        // Datos del producto
        $p = $clsConsulta->consultaGeneral("SELECT clave, nombre FROM cat_productos WHERE id_producto = {$idProd} LIMIT 1");
        $clave  = $p[1]['clave']  ?? '';
        $nombre = $p[1]['nombre'] ?? ('Producto ' . $idProd);

        // Usa precio_unitario si existe, si no 'precio'
        $precio = isset($d['precio_unitario']) ? (float)$d['precio_unitario'] : (float)$d['precio'];

        $items[] = [
            'id_producto' => $idProd,
            'clave'       => $clave,
            'nombre'      => $nombre,
            'cantidad'    => (float)$d['cantidad'],
            'precio'      => (float)$precio,
        ];
    }
}

echo json_encode([
    'success' => true,
    'cliente' => ['id' => (int)$rem['id_cliente'], 'razon_social' => (string)$rem['razon_social']],
    'items'   => $items,
    // Si quieres amarrar después la remisión a la factura al guardar:
    'id_remision' => (int)$rem['id'],
]);
