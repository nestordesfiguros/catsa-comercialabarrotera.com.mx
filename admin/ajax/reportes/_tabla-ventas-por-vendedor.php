<?php
// ajax/reportes/tabla-ventas-por-vendedor.php


header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

// Parámetros DataTables
$start = (int)($_POST['start'] ?? 0);
$length = (int)($_POST['length'] ?? 25);
$draw = (int)($_POST['draw'] ?? 1);

// Filtros desde el FRONT (coinciden con tus inputs)
$fecha_inicio = trim($_POST['fecha_inicio'] ?? '');
$fecha_fin    = trim($_POST['fecha_fin'] ?? '');
$vendedor_id  = trim($_POST['vendedor'] ?? '');

// Helper: verificar si existe una columna (para usar filtros opcionales sin romper)
function columnaExiste(Consultas $db, $tabla, $columna)
{
  // Usamos INFORMATION_SCHEMA con el esquema actual (DATABASE())
  $sql = "
    SELECT COUNT(1) AS c
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = '{$tabla}'
      AND COLUMN_NAME = '{$columna}'
    LIMIT 1
  ";
  $res = $db->consultaGeneral($sql);
  return (int)($res[1]['c'] ?? 0) > 0;
}

// WHERE base (seguro en todos los entornos)
$w = "IFNULL(r.estatus,'') <> 'cancelada'";

// Filtros de fecha (si vienen)
if ($fecha_inicio !== '') {
  $w .= " AND DATE(r.fecha) >= " . $clsConsulta->sanitizar($fecha_inicio, false, false);
}
if ($fecha_fin !== '') {
  $w .= " AND DATE(r.fecha) <= " . $clsConsulta->sanitizar($fecha_fin, false, false);
}

// Filtro vendedor (si viene)
if ($vendedor_id !== '') {
  $w .= " AND r.id_vendedor = " . $clsConsulta->sanitizar($vendedor_id, true, false);
}

// Filtros opcionales: solo se agregan si las columnas existen
if (columnaExiste($clsConsulta, 'cab_remisiones', 'es_devolucion')) {
  $w .= " AND IFNULL(r.es_devolucion,0) = 0";
}
if (columnaExiste($clsConsulta, 'cab_remisiones', 'es_nota_credito')) {
  $w .= " AND IFNULL(r.es_nota_credito,0) = 0";
}

// Consulta base (agregada por vendedor)
$sqlBase = "
  SELECT 
    v.id AS id_vendedor,
    CONCAT_WS(' ', v.nombre, v.apellido1, v.apellido2) AS vendedor,
    COUNT(DISTINCT r.id) AS remisiones,
    SUM(m.cantidad) AS unidades,
    SUM(m.cantidad * m.precio) AS venta_sin_iva,
    IFNULL(v.comision,0) AS pct_comision
  FROM cab_remisiones r
  JOIN mov_remisiones m ON m.id_remision = r.id
  LEFT JOIN cat_vendedores v ON v.id = r.id_vendedor
  WHERE {$w}
  GROUP BY v.id, vendedor, pct_comision
";

// Totales para DataTables
$totRes = $clsConsulta->consultaGeneral("SELECT COUNT(1) AS c FROM ({$sqlBase}) t");
$recordsTotal = (int)($totRes[1]['c'] ?? 0);
$recordsFiltered = $recordsTotal;

// Página
$sqlPage = $sqlBase . " LIMIT {$start}, {$length}";
$rows = $clsConsulta->consultaGeneral($sqlPage);

// Texto de fecha a mostrar en la columna “Fecha”
$fecha_texto = '';
if ($fecha_inicio !== '' && $fecha_fin !== '') {
  $fecha_texto = $fecha_inicio . ' a ' . $fecha_fin;
} elseif ($fecha_inicio !== '') {
  $fecha_texto = 'Desde ' . $fecha_inicio;
} elseif ($fecha_fin !== '') {
  $fecha_texto = 'Hasta ' . $fecha_fin;
}

$data = [];
if ($rows && $clsConsulta->numrows > 0) {
  $i = 1;
  foreach ($rows as $r) {
    // Omitir índice 0 si tu método retorna 1-based
    if (!is_array($r)) {
      $i++;
      continue;
    }

    $venta = (float)($r['venta_sin_iva'] ?? 0);
    // Placeholder hasta definir fuente de cartera
    $ventas_no_cobradas = 0.00;

    $data[] = [
      // Deben coincidir con los "data" definidos en el DataTable del front
      'vendedor'            => $r['vendedor'] ?: 'SIN VENDEDOR',
      'total_ventas'        => '$' . number_format($venta, 2, '.', ','),
      'ventas_no_cobradas'  => '$' . number_format($ventas_no_cobradas, 2, '.', ','),
      'fecha'               => $fecha_texto,
      'acciones'            => '<button type="button" class="btn btn-sm btn-outline-primary ver-detalle" data-vendedor="' . htmlspecialchars($r['id_vendedor'] ?? '') . '">Ver detalle</button>'
    ];
    $i++;
  }
}

echo json_encode([
  'draw' => $draw,
  'recordsTotal' => $recordsTotal,
  'recordsFiltered' => $recordsFiltered,
  'data' => $data
], JSON_UNESCAPED_UNICODE);
