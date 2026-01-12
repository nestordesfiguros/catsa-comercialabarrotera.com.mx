<?php
require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

// DataTables
$start = $_POST['start'] ?? 0;
$length = $_POST['length'] ?? 10;
$draw = $_POST['draw'] ?? 1;
$estatus = $_POST['estatus'] ?? 'pendiente';

$sql = "SELECT * FROM cab_entradas_almacen WHERE estatus = '" . $clsConsulta->escape($estatus) . "' ORDER BY fecha DESC LIMIT $start, $length";
$datos = $clsConsulta->consultaGeneral($sql);

$sqlTotal = "SELECT COUNT(*) AS total FROM cab_entradas_almacen WHERE estatus = '" . $clsConsulta->escape($estatus) . "'";
$total = $clsConsulta->consultaGeneral($sqlTotal);
$totalRegistros = $total[1]['total'] ?? 0;

$data = [];
if (is_array($datos)) {
    foreach ($datos as $d) {
        $proveedor = '-';
        if (!empty($d['id_proveedor'])) {
            $provData = $clsConsulta->presentaDetalle('cat_proveedores', $d['id_proveedor']);
            $proveedor = $provData[1]['nombre_Comercial'] ?? $provData[1]['razon_social'] ?? '---';
        }

        $referencia = !empty($d['referencia']) ? $d['referencia'] : '(sin ref)';

        $diferencia = '-';
        if ($d['es_incompleta'] === '1') {
            $tooltip = htmlspecialchars($d['incompleto_observaciones'] ?? '', ENT_QUOTES);
            $diferencia = "<span class='text-warning fw-bold' data-bs-toggle='tooltip' title='$tooltip'>Con diferencia</span>";
        }

        $btn = "<a href='recepcion-altas/{$d['id']}' class='btn btn-sm btn-primary'><i class='fa fa-edit'></i> Procesar</a>";

        $data[] = [
            $d['fecha'],
            $referencia,
            $proveedor,
            $diferencia,
            $btn
        ];
    }
}

$respuesta = [
    "draw" => intval($draw),
    "recordsTotal" => $totalRegistros,
    "recordsFiltered" => $totalRegistros,
    "data" => $data
];

header('Content-Type: application/json');
echo json_encode($respuesta);
?>