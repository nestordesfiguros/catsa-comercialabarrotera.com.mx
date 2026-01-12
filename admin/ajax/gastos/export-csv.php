<?php
// ajax/gastos/export-csv.php
session_start();
require_once '../../lib/clsConsultas.php';

$cls       = new Consultas();
$idEmpresa = intval($_SESSION['id_empresa'] ?? 0);

$fini = $_POST['filtroFechaInicio'] ?? '';
$ffin = $_POST['filtroFechaFin'] ?? '';
$fcat = trim($_POST['filtroCategoria'] ?? '');
$fmet = trim($_POST['filtroMetodo'] ?? '');
$fest = trim($_POST['filtroEstatus'] ?? '');
$search = trim($_POST['search'] ?? '');

$where = " WHERE g.id_empresa = {$idEmpresa} ";
if ($fini !== '' && $ffin !== '') {
    $where .= " AND g.fecha_gasto BETWEEN " . $cls->sanitizar($fini) . " AND " . $cls->sanitizar($ffin) . " ";
}
if ($fcat !== '') {
    $where .= " AND g.id_categoria = " . intval($fcat) . " ";
}
if ($fmet !== '') {
    $where .= " AND g.id_metodo   = " . intval($fmet) . " ";
}
if ($fest !== '') {
    $where .= " AND g.estatus     = " . $cls->sanitizar($fest) . " ";
}
if ($search !== '') {
    $sv = $cls->escape($search);
    $where .= " AND (g.descripcion LIKE '%{$sv}%' OR g.referencia LIKE '%{$sv}%' OR c.nombre LIKE '%{$sv}%' OR m.descripcion LIKE '%{$sv}%') ";
}

$sql = "
  SELECT g.fecha_gasto, g.hora_gasto, c.nombre AS categoria, g.descripcion, m.descripcion AS metodo, g.referencia, g.monto, g.estatus
  FROM mov_gastos g
  INNER JOIN cat_gastos_categorias c ON c.id_categoria = g.id_categoria
  INNER JOIN cat_metodos_pago m      ON m.id = g.id_metodo
  {$where}
  ORDER BY g.fecha_gasto DESC, g.hora_gasto DESC
";

$rs = $cls->aplicaQuery($sql);

// Cabeceras CSV
$filename = "gastos_" . date('Ymd_His') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// BOM UTF-8 (Excel friendly)
echo "\xEF\xBB\xBF";

$fp = fopen('php://output', 'w');
fputcsv($fp, ['Fecha', 'Hora', 'Categoría', 'Descripción', 'Método', 'Referencia', 'Monto', 'Estatus']);

$total = 0.0;
while ($row = mysqli_fetch_assoc($rs)) {
    $total += floatval($row['monto']);
    fputcsv($fp, [
        $row['fecha_gasto'],
        $row['hora_gasto'],
        $row['categoria'],
        $row['descripcion'],
        $row['metodo'],
        $row['referencia'],
        number_format((float)$row['monto'], 2, '.', ''),
        $row['estatus']
    ]);
}
fputcsv($fp, ['', '', '', '', '', 'TOTAL', number_format($total, 2, '.', ''), '']);
fclose($fp);
exit;
