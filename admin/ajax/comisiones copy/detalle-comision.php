<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id_comision = intval($_POST['id_comision'] ?? 0);
$data = [];

if ($id_comision > 0) {
    $sql = "
        SELECT
            id_cxc,
            id_remision,
            monto_venta,
            porcentaje,
            total_comision
        FROM comisiones_detalle
        WHERE id_comision = $id_comision
    ";
    $rs = $clsConsulta->consultaGeneral($sql);
    if ($rs && $clsConsulta->numrows > 0) {
        foreach ($rs as $r) {
            $data[] = [
                'id_cxc'         => $r['id_cxc'],
                'id_remision'    => $r['id_remision'],
                'monto_venta'    => $r['monto_venta'],
                'porcentaje'     => $r['porcentaje'],
                'total_comision' => $r['total_comision']
            ];
        }
    }
}

echo json_encode($data);
