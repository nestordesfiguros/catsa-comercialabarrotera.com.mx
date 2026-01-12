<?php
session_start();
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$datos = [];

// Recuperar remisiones de sesión
$idsSesion = $_SESSION['remisiones_cartaporte'] ?? [];
$idsSesionStr = empty($idsSesion) ? '0' : implode(',', array_map('intval', $idsSesion));

$con = "
    SELECT 
        r.id,
        CONCAT('R-', LPAD(r.id,6,'0')) AS folio,
        c.razon_social AS cliente,
        DATE(r.fecha) AS fecha,
        IFNULL(SUM(m.cantidad*m.precio),0) AS total
    FROM cab_remisiones r
    INNER JOIN cat_clientes c ON r.id_cliente = c.id
    LEFT JOIN mov_remisiones m ON m.id_remision = r.id
    WHERE r.estatus = 'procesada'
      AND r.id NOT IN (SELECT id_remision FROM cartas_porte_remisiones)
      AND r.id NOT IN ($idsSesionStr)
    GROUP BY r.id
    ORDER BY r.id DESC
";

$res = $clsConsulta->consultaGeneral($con);

if ($clsConsulta->numrows > 0) {
  foreach ($res as $row) {
    $btnAgregar = '<button type="button" class="btn btn-success btn-sm btnAgregarRemision" data-id="' . $row['id'] . '"><i class="fa fa-plus"></i></button>';
    $btnDetalle = '<button type="button" class="btn btn-info btn-sm btnDetalleRemision" data-id="' . $row['id'] . '"><i class="fa fa-eye"></i></button>';
    $datos[] = [
      $row['folio'],
      $row['cliente'],
      $row['fecha'],
      '$' . number_format($row['total'], 2),
      $btnAgregar . ' ' . $btnDetalle
    ];
  }
}

echo json_encode(['data' => $datos]);
