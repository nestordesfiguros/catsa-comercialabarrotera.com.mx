<?php
// ajax/inventario/historial.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

try {
    $id_producto = isset($_POST['id_producto']) ? (int)$_POST['id_producto'] : 0;
    $id_almacen  = isset($_POST['id_almacen']) ? (int)$_POST['id_almacen'] : 0;

    if ($id_producto <= 0) {
        echo json_encode(['success' => false, 'message' => 'Producto inválido']);
        exit;
    }

    // ENTRADAS (procesadas)
    $qEntradas = "
    SELECT 
      DATE_FORMAT(c.fecha, '%Y-%m-%d %H:%i:%s') AS fecha,
      'Entrada' AS tipo,
      CONCAT('EA-', c.id) AS documento,
      c.id_almacen,
      a.almacen,
      m.id_producto,
      CAST(m.cantidad AS DECIMAL(18,2)) AS cantidad,
      NULL AS contraparte
    FROM cab_entradas_almacen c
    INNER JOIN mov_entradas_almacen m ON m.id_orden_compra = c.id
    LEFT JOIN cat_almacenes a ON a.id = c.id_almacen
    WHERE c.estatus = 'procesada'
      AND m.id_producto = $id_producto
      " . ($id_almacen > 0 ? "AND c.id_almacen = $id_almacen" : "") . "
  ";

    // SALIDAS (remisiones procesadas)
    $qSalidas = "
    SELECT
      DATE_FORMAT(r.fecha, '%Y-%m-%d %H:%i:%s') AS fecha,
      'Salida' AS tipo,
      CONCAT('REM-', r.id) AS documento,
      r.id_almacen,
      a.almacen,
      m.id_producto,
      CAST(-m.cantidad AS DECIMAL(18,2)) AS cantidad,
      NULL AS contraparte
    FROM cab_remisiones r
    INNER JOIN mov_remisiones m ON m.id_remision = r.id
    LEFT JOIN cat_almacenes a ON a.id = r.id_almacen
    WHERE r.estatus = 'procesada'
      AND m.id_producto = $id_producto
      " . ($id_almacen > 0 ? "AND r.id_almacen = $id_almacen" : "") . "
  ";

    // DEVOLUCIONES (solo de remisión y remisión PROCESADA)
    $qDevs = "
    SELECT
      DATE_FORMAT(d.fecha, '%Y-%m-%d %H:%i:%s') AS fecha,
      'Devolución' AS tipo,
      CONCAT('DEV-', d.id_devolucion) AS documento,
      r.id_almacen,
      a.almacen,
      m.id_producto,
      CAST(m.cantidad AS DECIMAL(18,2)) AS cantidad,
      NULL AS contraparte
    FROM cab_devoluciones d
    INNER JOIN mov_devoluciones m ON m.id_devolucion = d.id_devolucion
    INNER JOIN cab_remisiones r
            ON d.tipo_documento = 'remision'
           AND r.id = d.id_documento
           AND r.estatus = 'procesada'
    LEFT JOIN cat_almacenes a ON a.id = r.id_almacen
    WHERE m.id_producto = $id_producto
      " . ($id_almacen > 0 ? "AND r.id_almacen = $id_almacen" : "") . "
  ";

    // TRASPASOS (salida)
    $qTrSal = "
    SELECT
      DATE_FORMAT(t.fecha, '%Y-%m-%d %H:%i:%s') AS fecha,
      'Traspaso (salida)' AS tipo,
      CONCAT('TR-', t.id_traspaso) AS documento,
      t.id_almacen_origen AS id_almacen,
      ao.almacen,
      m.id_producto,
      CAST(-m.cantidad AS DECIMAL(18,2)) AS cantidad,
      CONCAT('→ ', ad.almacen) AS contraparte
    FROM cab_traspasos_almacen t
    INNER JOIN mov_traspasos_almacen m ON m.id_traspaso = t.id_traspaso
    LEFT JOIN cat_almacenes ao ON ao.id = t.id_almacen_origen
    LEFT JOIN cat_almacenes ad ON ad.id = t.id_almacen_destino
    WHERE t.estatus = 'activo'
      AND m.id_producto = $id_producto
      " . ($id_almacen > 0 ? "AND t.id_almacen_origen = $id_almacen" : "") . "
  ";

    // TRASPASOS (entrada)
    $qTrEnt = "
    SELECT
      DATE_FORMAT(t.fecha, '%Y-%m-%d %H:%i:%s') AS fecha,
      'Traspaso (entrada)' AS tipo,
      CONCAT('TR-', t.id_traspaso) AS documento,
      t.id_almacen_destino AS id_almacen,
      ad.almacen,
      m.id_producto,
      CAST(m.cantidad AS DECIMAL(18,2)) AS cantidad,
      CONCAT('← ', ao.almacen) AS contraparte
    FROM cab_traspasos_almacen t
    INNER JOIN mov_traspasos_almacen m ON m.id_traspaso = t.id_traspaso
    LEFT JOIN cat_almacenes ao ON ao.id = t.id_almacen_origen
    LEFT JOIN cat_almacenes ad ON ad.id = t.id_almacen_destino
    WHERE t.estatus = 'activo'
      AND m.id_producto = $id_producto
      " . ($id_almacen > 0 ? "AND t.id_almacen_destino = $id_almacen" : "") . "
  ";

    $sql = "
    SELECT * FROM (
      $qEntradas
      UNION ALL
      $qSalidas
      UNION ALL
      $qDevs
      UNION ALL
      $qTrSal
      UNION ALL
      $qTrEnt
    ) x
    ORDER BY fecha DESC
    LIMIT 500
  ";

    $rs = $clsConsulta->consultaGeneral($sql);
    $data = [];
    if ($clsConsulta->numrows > 0) {
        foreach ($rs as $i => $r) {
            if ($i === 0) continue;
            $data[] = [
                'fecha'       => $r['fecha'],
                'tipo'        => $r['tipo'],
                'documento'   => $r['documento'],
                'id_almacen'  => (int)$r['id_almacen'],
                'almacen'     => $r['almacen'] ?? '',
                'id_producto' => $id_producto,
                'cantidad'    => (float)$r['cantidad'],
                'contraparte' => $r['contraparte'] ?? ''
            ];
        }
    }

    echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
