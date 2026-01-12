<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

// Captura fechas y filtros
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('monday this week'));
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d', strtotime($fecha_inicio . ' +6 days'));
$draw = intval($_GET['draw'] ?? 1);
$search = trim($_GET['search']['value'] ?? '');
$estatus = $_GET['estatus'] ?? 'pendiente';

// WHERE base
$where = "WHERE r.estatus = 'procesada' AND DATE(r.fecha) BETWEEN '$fecha_inicio' AND '$fecha_fin'";

// Si estatus es 'pendiente', filtra que NO estén en comisiones_detalle
if ($estatus === 'pendiente') {
  $where .= " AND cxc.id_cxc NOT IN (SELECT id_cxc FROM comisiones_detalle)";
} elseif ($estatus === 'pagada') {
  $where .= " AND cxc.id_cxc IN (SELECT id_cxc FROM comisiones_detalle)";
}

// Si hay búsqueda por nombre
if ($search !== '') {
  $where .= " AND (v.nombre LIKE '%$search%' OR v.apellido1 LIKE '%$search%' OR v.apellido2 LIKE '%$search%')";
}

// Consulta principal por vendedor
$sql = "
  SELECT 
    v.id AS id_vendedor,
    CONCAT(v.nombre, ' ', v.apellido1, ' ', v.apellido2) AS vendedor,
    COUNT(DISTINCT r.id) AS total_remisiones,
    SUM(r.total * (v.comision / 100)) AS total_comision
  FROM cab_remisiones r
  INNER JOIN cat_vendedores v ON r.id_vendedor = v.id
  INNER JOIN cab_cxc cxc ON r.id = cxc.id_remision
  $where
  GROUP BY v.id
";

$rs = $clsConsulta->consultaGeneral($sql);

$inicioUrl = urlencode($fecha_inicio);
$finUrl = urlencode($fecha_fin);

$data = [];
if ($clsConsulta->numrows > 0) {
  foreach ($rs as $r) {
    $data[] = [
      'vendedor' => $r['vendedor'],
      'total_remisiones' => $r['total_remisiones'],
      'total_comision' => number_format($r['total_comision'], 2),
      'estatus' => ucfirst($estatus),
      'acciones' => '
    <a href="comisiones-detalle/' . $r['id_vendedor'] . '/' . $inicioUrl . '/' . $finUrl . '" class="btn btn-sm btn-info">
      <i class="fas fa-eye"></i> Ver detalle
    </a>'
    ];
  }
}

// Respuesta para DataTable
echo json_encode([
  "draw" => $draw,
  "recordsTotal" => count($data),
  "recordsFiltered" => count($data),
  "data" => $data
]);
