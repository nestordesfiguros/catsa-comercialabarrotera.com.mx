<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id_vendedor = intval($_GET['id_vendedor'] ?? 0);
$inicio = $_GET['inicio'] ?? '';
$fin = $_GET['fin'] ?? '';

if ($id_vendedor <= 0 || !$inicio || !$fin) {
  echo json_encode(['data' => []]);
  exit;
}

// Buscar la comisión ya registrada
$sql_comision = "
  SELECT id
  FROM comisiones
  WHERE id_vendedor = $id_vendedor
    AND fecha_inicio = '$inicio'
    AND fecha_fin = '$fin'
  LIMIT 1
";

$rsComision = $clsConsulta->consultaGeneral($sql_comision);

if ($clsConsulta->numrows == 0) {
  echo json_encode(['data' => []]);
  exit;
}

$id_comision = $rsComision[1]['id'];

// Obtener el detalle
$sql_detalle = "
  SELECT 
    id_cxc,
    id_remision,
    monto_venta,
    porcentaje,
    total_comision
  FROM comisiones_detalle
  WHERE id_comision = $id_comision
  ORDER BY id_cxc DESC
";

$datos = $clsConsulta->consultaGeneral($sql_detalle);

$data = [];

if ($clsConsulta->numrows > 0) {
  foreach ($datos as $row) {
    $data[] = [
      'id_cxc' => $row['id_cxc'],
      'id_remision' => $row['id_remision'],
      'monto_venta' => '$' . number_format($row['monto_venta'], 2),
      'porcentaje' => number_format($row['porcentaje'], 2) . '%',
      'total_comision' => '$' . number_format($row['total_comision'], 2)
    ];
  }
}

echo json_encode(['data' => $data]);
