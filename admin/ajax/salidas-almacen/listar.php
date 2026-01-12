<?php
session_start();
require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$start  = intval($_POST['start']  ?? 0);
$length = intval($_POST['length'] ?? 10);
$draw   = intval($_POST['draw']   ?? 1);
$search = trim($_POST['search']['value'] ?? '');
$idRemisionRef = intval($_POST['id_remision'] ?? 0);

$where = "WHERE 1=1";
$joins = " LEFT JOIN rel_salidas_remisiones rs ON rs.id_salida = s.id
           LEFT JOIN cab_remisiones r        ON r.id = rs.id_remision
           LEFT JOIN cat_clientes c          ON c.id = r.id_cliente ";

if ($search !== '') {
  $q = addslashes($search);
  $where .= " AND (
    s.id LIKE '%$q%' OR
    c.razon_social LIKE '%$q%' OR
    s.observaciones LIKE '%$q%'
  )";
}

if ($idRemisionRef > 0) {
  $where .= " AND rs.id_remision = $idRemisionRef ";
}

/* Totales */
$t0 = $clsConsulta->consultaGeneral("SELECT COUNT(*) AS t FROM cab_salidas_almacen");
$recordsTotal = intval($t0[1]['t'] ?? 0);

/* Totales filtrados */
$t1 = $clsConsulta->consultaGeneral("
  SELECT COUNT(DISTINCT s.id) AS t
  FROM cab_salidas_almacen s
  $joins
  $where
");
$recordsFiltered = intval($t1[1]['t'] ?? 0);

/* Página */
$sql = "
  SELECT 
    s.id AS id_salida,
    DATE_FORMAT(s.fecha, '%d/%m/%Y') AS fecha,
    COALESCE(c.razon_social,'') AS cliente,
    s.id_almacen,
    COALESCE(s.total,0) AS total,
    s.estatus,
    GROUP_CONCAT(DISTINCT rs.id_remision ORDER BY rs.id_remision SEPARATOR ',') AS remisiones_rel
  FROM cab_salidas_almacen s
  $joins
  $where
  GROUP BY s.id
  ORDER BY s.fecha DESC, s.id DESC
  LIMIT $start, $length
";
$rs = $clsConsulta->consultaGeneral($sql);

$data = [];
if ($clsConsulta->numrows > 0) {
  foreach ($rs as $i => $r) {
    if ($i === 0) continue;

    $remisionesTxt = $r['remisiones_rel'] ? ('REM ' . str_replace(',', ', REM ', $r['remisiones_rel'])) : '-';

    $acciones = '
      <div class="btn-group">
        <button class="btn btn-sm btn-outline-primary btn-ver-salida" data-id="' . intval($r['id_salida']) . '">VER</button>
        <button class="btn btn-sm btn-outline-danger btn-cancelar-salida" data-id="' . intval($r['id_salida']) . '" ' . ($r['estatus'] == 'cancelada' ? 'disabled' : '') . '>CANCELAR</button>
      </div>';

    $data[] = [
      intval($r['id_salida']),
      $r['fecha'],
      $r['cliente'],
      '#' . intval($r['id_almacen']),
      '$' . number_format((float)$r['total'], 2),
      ucfirst($r['estatus']),
      $remisionesTxt,
      $acciones
    ];
  }
}

echo json_encode([
  'draw'            => $draw,
  'recordsTotal'    => $recordsTotal,
  'recordsFiltered' => $recordsFiltered,
  'data'            => $data
]);
