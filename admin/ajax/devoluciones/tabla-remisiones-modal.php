<?php
session_start();
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

header('Content-Type: application/json');

// =========================
// 1. Consultar remisiones con total calculado
// =========================
// - Se suma cantidad*precio desde mov_remisiones
// - Si no hay productos, total será 0
$sql = "
    SELECT 
        cr.id,
        CONCAT('R-', LPAD(cr.id, 6, '0')) AS folio,
        COALESCE(cc.razon_social, cc.nombre_comercial, 'SIN CLIENTE') AS cliente,
        DATE_FORMAT(cr.fecha, '%Y-%m-%d') AS fecha,
        IFNULL(SUM(mr.cantidad * mr.precio), 0) AS total
    FROM cab_remisiones cr
    LEFT JOIN cat_clientes cc ON cr.id_cliente = cc.id
    LEFT JOIN mov_remisiones mr ON cr.id = mr.id_remision
    WHERE cr.estatus IN ('pendiente','procesada')
    GROUP BY cr.id
    ORDER BY cr.fecha DESC
";

$rs = $clsConsulta->consultaGeneral($sql);

// =========================
// 2. Armar arreglo para DataTables
// =========================
$data = [];

if ($clsConsulta->numrows > 0) {
  foreach ($rs as $i => $row) {
    if (!is_array($row)) continue; // Tu clase retorna índices desde 1

    $data[] = [
      'id'      => $row['id'],      // Solo para data-id en botones
      'folio'   => $row['folio'],
      'cliente' => $row['cliente'],
      'fecha'   => $row['fecha'],
      'total'   => number_format((float)$row['total'], 2)
    ];
  }
}

// =========================
// 3. Retornar JSON para DataTables
// =========================
echo json_encode([
  'data' => $data
]);
