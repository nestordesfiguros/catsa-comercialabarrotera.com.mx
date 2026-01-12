<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$fecha_inicio = $_POST['fecha_inicio'] ?? '';
$fecha_fin = $_POST['fecha_fin'] ?? '';
$where = "1";

if ($fecha_inicio && $fecha_fin) {
    $where = "c.fecha_inicio >= '$fecha_inicio' AND c.fecha_fin <= '$fecha_fin'";
}

$sql = "
    SELECT 
        c.id,
        c.id_vendedor,
        c.fecha_inicio,
        c.fecha_fin,
        c.estatus,
        CONCAT(v.nombre, ' ', v.apellido1, ' ', v.apellido2) AS nombre_vendedor,
        IFNULL(SUM(d.total_comision), 0) AS total_comision
    FROM comisiones c
    INNER JOIN cat_vendedores v ON v.id = c.id_vendedor
    LEFT JOIN comisiones_detalle d ON d.id_comision = c.id
    WHERE $where
    GROUP BY c.id
    ORDER BY c.fecha_inicio DESC
";

$rs = $clsConsulta->consultaGeneral($sql);

$data = [];
if ($rs && $clsConsulta->numrows > 0) {
    foreach ($rs as $row) {
        $id_comision = intval($row['id']);
        $vendedor = htmlspecialchars($row['nombre_vendedor']);
        $periodo = date('d/m/Y', strtotime($row['fecha_inicio'])) . ' al ' . date('d/m/Y', strtotime($row['fecha_fin']));
        $estatus = $row['estatus'];
        $total = floatval($row['total_comision']);

        $btnDetalle = '<button class="btn btn-outline-info btn-sm me-1" onclick="verDetalleComision(' . $id_comision . ')"><i class="fas fa-eye"></i></button>';
        $btnPagar = ($estatus === 'pendiente') ? '<button class="btn btn-outline-success btn-sm" onclick="pagarComision(' . $id_comision . ')"><i class="fas fa-check-circle"></i></button>' : '';

        $data[] = [
            'vendedor' => $vendedor,
            'periodo' => $periodo,
            'total_comision' => $total,
            'estatus' => $estatus,
            'acciones' => $btnDetalle . $btnPagar
        ];
    }
}

echo json_encode([
    'data' => $data
]);
