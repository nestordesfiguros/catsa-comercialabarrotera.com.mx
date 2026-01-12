<?php
// ajax/salidas-almacen/buscar-remisiones.php
require_once '../../lib/clsConsultas.php';
header('Content-Type: application/json');
$clsConsulta = new Consultas();

$term = addslashes($_POST['search'] ?? '');
$id_cliente = intval($_POST['id_cliente'] ?? 0);

$where = "WHERE 1=1";
if ($term !== '') {
    $where .= " AND (r.id LIKE '%$term%' OR c.razon_social LIKE '%$term%')";
}
if ($id_cliente > 0) {
    $where .= " AND r.id_cliente = $id_cliente";
}

$sql = "
 SELECT r.id, r.fecha, COALESCE(c.razon_social,'') AS cliente,
        COALESCE(r.total,0) AS total, r.estatus
 FROM cab_remisiones r
 LEFT JOIN cat_clientes c ON c.id = r.id_cliente
 $where
 ORDER BY r.fecha DESC
 LIMIT 50
";
$rs = $clsConsulta->consultaGeneral($sql);

$out = [];
if ($clsConsulta->numrows > 0) {
    foreach ($rs as $i => $row) {
        if ($i === 0) continue;
        $out[] = [
            'id'      => intval($row['id']),
            'text'    => 'REM ' . $row['id'] . ' | ' . $row['cliente'] . ' | $' . number_format((float)$row['total'], 2),
            'cliente' => $row['cliente'],
            'total'   => (float)$row['total'],
            'estatus' => $row['estatus']
        ];
    }
}
echo json_encode($out);
