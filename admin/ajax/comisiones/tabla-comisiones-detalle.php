<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

// Parámetros
$id_vendedor = intval($_GET['id_vendedor'] ?? 0);
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('monday this week'));
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d', strtotime($fecha_inicio . ' +6 days'));

// Validación básica
if ($id_vendedor <= 0) {
  echo json_encode([
    'draw' => 0,
    'recordsTotal' => 0,
    'recordsFiltered' => 0,
    'data' => [],
    'error' => 'ID de vendedor inválido.'
  ]);
  exit;
}

// draw para paginación
$draw = intval($_GET['draw'] ?? 1);

// Consulta
$sql = "
  SELECT 
  cd.id,
  cd.id_remision,
  r.fecha,
  cd.monto_venta AS total_remision,
  cd.porcentaje,
  cd.total_comision,
  cd.pagado
  FROM comisiones_detalle cd
  INNER JOIN cab_remisiones r ON cd.id_remision = r.id
  INNER JOIN comisiones c ON cd.id_comision = c.id
  WHERE c.id_vendedor = $id_vendedor
    AND c.fecha_inicio = '$fecha_inicio'
    AND c.fecha_fin = '$fecha_fin'
  ORDER BY r.fecha ASC
";

$res = $clsConsulta->consultaGeneral($sql);
$data = [];

if ($clsConsulta->numrows > 0) {
  foreach ($res as $r) {
    $id = $r['id'];
    $id_remision = $r['id_remision'];
    $fecha = date('d-m-Y', strtotime($r['fecha']));
    $monto_remision = number_format($r['total_remision'], 2);
    $porcentaje = number_format($r['porcentaje'], 2) . '%';
    $comision = number_format($r['total_comision'], 2);
    $pagado = intval($r['pagado']) === 1;

    $checkbox = $pagado
      ? '<i class="fas fa-lock text-muted"></i>'
      : '<input type="checkbox" class="form-check-input checkbox-comision" value="' . $id . '">';

    $acciones = $pagado
      ? '<span class="badge bg-success">Pagado</span>'
      : '<button class="btn btn-success btn-sm" onclick="pagarRemision(' . $id . ')">
           <i class="fas fa-check"></i> Pagar
         </button>';

    $data[] = [
      'checkbox' => $checkbox,
      'remision' => $id_remision,
      'fecha' => $fecha,
      'monto' => '$' . $monto_remision,
      'porcentaje' => $porcentaje,
      'comision' => '$' . $comision,
      'acciones' => $acciones
    ];
  }
}

echo json_encode([
  'draw' => $draw,
  'recordsTotal' => count($data),
  'recordsFiltered' => count($data),
  'data' => $data
]);
